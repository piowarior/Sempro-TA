<?php

namespace App\Http\Controllers\Admin;

use App\Events\Domain\PatientProgressRecordedEvent;
use App\Http\Controllers\Controller;
use App\Models\BloodMeasurement;
use App\Models\BodyMeasurement;
use App\Models\Patient;
use App\Models\PatientProgressSession;
use App\Models\ToxinAssessmentAnswer;
use App\Models\ToxinAssessmentResult;
use App\Services\OutboxService;
use App\Support\ToxinIndicators;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExaminationController extends Controller
{
    public function __construct(protected OutboxService $outboxService) {}

    public function getIndicators()
    {
        return response()->json([
            'indicators' => ToxinIndicators::all(),
        ]);
    }

    public function store(Request $request, $patientId)
    {
        $patient = Patient::findOrFail($patientId);

        $request->validate([
            'session_number' => 'required|string|max:50',
            'session_date' => 'required|date',
            'staff_notes' => 'nullable|string',
            'examination_summary' => 'nullable|string',
            
            // Toxin Assessment (array of indicator numbers indicated, or key-value)
            'indicated_numbers' => 'nullable|array',
            'indicated_numbers.*' => 'integer|min:1|max:50',
            'toxin_notes' => 'nullable|string',

            // Body parameters
            'weight' => 'nullable|numeric|min:20|max:300',
            'bmi' => 'nullable|numeric|min:10|max:60',
            'waist_circumference' => 'nullable|numeric|min:30|max:200',
            'body_fat' => 'nullable|numeric|min:0|max:100',
            'water' => 'nullable|numeric|min:0|max:100',
            'muscle_mass' => 'nullable|numeric|min:0|max:150',
            'physical_rating' => 'nullable|integer|min:1|max:10',
            'cell_age' => 'nullable|integer|min:1|max:120',
            'bone_mass' => 'nullable|numeric|min:0|max:20',
            'visceral_fat' => 'nullable|numeric|min:0|max:50',
            'body_temperature' => 'nullable|numeric|min:30|max:45',

            // Blood parameters
            'blood_pressure' => 'nullable|string|max:20',
            'heart_rate' => 'nullable|integer|min:30|max:220',
            'hemoglobin' => 'nullable|numeric|min:0|max:30',
            'glucose' => 'nullable|numeric|min:0|max:1000',
            'cholesterol' => 'nullable|numeric|min:0|max:1000',
            'uric_acid' => 'nullable|numeric|min:0|max:30',
            'ketone' => 'nullable|numeric|min:0|max:20',
        ]);

        return DB::transaction(function () use ($request, $patient) {
            // 1. Create Session
            $session = PatientProgressSession::create([
                'patient_id' => $patient->id,
                'session_number' => $request->session_number,
                'session_date' => $request->session_date,
                'staff_notes' => $request->staff_notes,
                'examination_summary' => $request->examination_summary,
                'recorded_at' => now(),
            ]);

            // 2. Toxin Assessment
            $indicated = $request->input('indicated_numbers', []);
            $totalIndicated = count($indicated);
            $level = ToxinAssessmentResult::calculateLevel($totalIndicated);

            $toxinResult = ToxinAssessmentResult::create([
                'session_id' => $session->id,
                'patient_id' => $patient->id,
                'total_indicated' => $totalIndicated,
                'level' => $level,
                'assessment_date' => $request->session_date,
                'notes' => $request->toxin_notes,
            ]);

            foreach (ToxinIndicators::all() as $num => $name) {
                ToxinAssessmentAnswer::create([
                    'result_id' => $toxinResult->id,
                    'indicator_number' => $num,
                    'indicator_name' => $name,
                    'is_indicated' => in_array($num, $indicated),
                    'created_at' => now(),
                ]);
            }

            // 3. Body Measurements (nullable values preserved)
            $body = BodyMeasurement::create([
                'session_id' => $session->id,
                'patient_id' => $patient->id,
                'weight' => $request->weight,
                'bmi' => $request->bmi,
                'waist_circumference' => $request->waist_circumference,
                'body_fat' => $request->body_fat,
                'water' => $request->water,
                'muscle_mass' => $request->muscle_mass,
                'physical_rating' => $request->physical_rating,
                'cell_age' => $request->cell_age,
                'bone_mass' => $request->bone_mass,
                'visceral_fat' => $request->visceral_fat,
                'body_temperature' => $request->body_temperature,
            ]);

            // 4. Blood Measurements (nullable values preserved)
            $blood = BloodMeasurement::create([
                'session_id' => $session->id,
                'patient_id' => $patient->id,
                'blood_pressure' => $request->blood_pressure,
                'heart_rate' => $request->heart_rate,
                'hemoglobin' => $request->hemoglobin,
                'glucose' => $request->glucose,
                'cholesterol' => $request->cholesterol,
                'uric_acid' => $request->uric_acid,
                'ketone' => $request->ketone,
            ]);

            // 5. Transactional Outbox Event
            $event = new PatientProgressRecordedEvent($session->load(['toxinAssessment', 'bodyMeasurement', 'bloodMeasurement']));
            $this->outboxService->recordEvent(
                $event->getEventType(),
                $event->getAggregateType(),
                $event->getAggregateId(),
                $event->getPayload(),
                (string) Str::uuid()
            );

            return response()->json([
                'message' => 'Sesi pemeriksaan berhasil dicatat',
                'session' => $session->load(['toxinAssessment.answers', 'bodyMeasurement', 'bloodMeasurement']),
            ], 201);
        });
    }

    public function progressCharts($patientId)
    {
        $patient = Patient::findOrFail($patientId);

        $sessions = PatientProgressSession::with(['toxinAssessment', 'bodyMeasurement', 'bloodMeasurement'])
            ->where('patient_id', $patientId)
            ->orderBy('session_date', 'asc')
            ->get();

        $chartData = [
            'dates' => [],
            'toxin_counts' => [],
            'weight' => [],
            'bmi' => [],
            'waist' => [],
            'glucose' => [],
            'cholesterol' => [],
            'uric_acid' => [],
            'ketone' => [],
        ];

        foreach ($sessions as $s) {
            $chartData['dates'][] = $s->session_date->format('d M Y');
            $chartData['toxin_counts'][] = $s->toxinAssessment?->total_indicated;
            $chartData['weight'][] = $s->bodyMeasurement?->weight ? (float) $s->bodyMeasurement->weight : null;
            $chartData['bmi'][] = $s->bodyMeasurement?->bmi ? (float) $s->bodyMeasurement->bmi : null;
            $chartData['waist'][] = $s->bodyMeasurement?->waist_circumference ? (float) $s->bodyMeasurement->waist_circumference : null;
            $chartData['glucose'][] = $s->bloodMeasurement?->glucose ? (float) $s->bloodMeasurement->glucose : null;
            $chartData['cholesterol'][] = $s->bloodMeasurement?->cholesterol ? (float) $s->bloodMeasurement->cholesterol : null;
            $chartData['uric_acid'][] = $s->bloodMeasurement?->uric_acid ? (float) $s->bloodMeasurement->uric_acid : null;
            $chartData['ketone'][] = $s->bloodMeasurement?->ketone ? (float) $s->bloodMeasurement->ketone : null;
        }

        return response()->json([
            'patient' => $patient,
            'charts' => $chartData,
            'sessions' => $sessions,
        ]);
    }
}

