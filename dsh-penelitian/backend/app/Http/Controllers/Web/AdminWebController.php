<?php

namespace App\Http\Controllers\Web;

use App\Events\Domain\ActivityPlanCancelledEvent;
use App\Events\Domain\ActivityPlanCreatedEvent;
use App\Events\Domain\PatientProgressRecordedEvent;
use App\Http\Controllers\Controller;
use App\Models\ActivityPlan;
use App\Models\ActivitySchedule;
use App\Models\BloodMeasurement;
use App\Models\BodyMeasurement;
use App\Models\NotificationLog;
use App\Models\OutboxEvent;
use App\Models\Patient;
use App\Models\PatientProgressSession;
use App\Models\ToxinAssessmentAnswer;
use App\Models\ToxinAssessmentResult;
use App\Models\User;
use App\Models\Visit;
use App\Models\WorkflowReference;
use App\Services\OutboxService;
use App\Support\ToxinIndicators;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminWebController extends Controller
{
    public function __construct(protected OutboxService $outboxService) {}

    public function showLogin()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function processLogin(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            if (Auth::user()->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            }
            Auth::logout();
            return back()->withErrors(['username' => 'Akun ini bukan petugas klinik.']);
        }

        return back()->withErrors(['username' => 'Username atau password salah.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function dashboard()
    {
        $totalPatients = Patient::count();
        $totalMhlsPatients = Patient::where('patient_type', 'mhls')->count();
        $totalUmumPatients = Patient::where('patient_type', 'umum')->count();
        $totalSessions = PatientProgressSession::count();
        $totalVisits = Visit::count();
        $pendingVisits = Visit::where('status', 'pending_confirmation')->count();
        $confirmedVisits = Visit::where('status', 'confirmed')->count();
        $activePlans = ActivityPlan::where('status', 'active')->count();

        // Data pasien terbaru beserta sesi
        $recentPatients = Patient::with(['sessions' => function($q) {
            $q->latest('session_date');
        }])->latest()->take(6)->get();

        // Kunjungan mendatang terdekat
        $upcomingVisits = Visit::with('patient')
            ->whereIn('status', ['confirmed', 'pending_confirmation'])
            ->orderBy('visit_date', 'asc')
            ->take(5)
            ->get();

        // Sesi pemeriksaan terbaru
        $recentSessions = PatientProgressSession::with([
            'patient',
            'toxinAssessment',
            'bodyMeasurement',
            'bloodMeasurement'
        ])->orderBy('session_date', 'desc')->take(5)->get();

        // Data statistik agregat untuk grafik tren kunjungan/pemeriksaan
        $sessionTrends = PatientProgressSession::select(
            DB::raw('DATE(session_date) as date'),
            DB::raw('count(*) as total')
        )
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->take(7)
        ->get()
        ->reverse();

        $trendLabels = $sessionTrends->pluck('date')->map(function($d) {
            return Carbon::parse($d)->format('d M');
        })->values();
        $trendCounts = $sessionTrends->pluck('total')->values();

        // Jika data tren sedikit, sediakan fallback minimal untuk render grafik
        if ($trendLabels->isEmpty()) {
            $trendLabels = collect([Carbon::now()->subDays(3)->format('d M'), Carbon::now()->subDays(2)->format('d M'), Carbon::now()->subDays(1)->format('d M'), Carbon::now()->format('d M')]);
            $trendCounts = collect([1, 2, 1, 3]);
        }

        return view('admin.dashboard', compact(
            'totalPatients', 'totalMhlsPatients', 'totalUmumPatients',
            'totalSessions', 'totalVisits', 'pendingVisits', 'confirmedVisits',
            'activePlans', 'recentPatients', 'upcomingVisits', 'recentSessions',
            'trendLabels', 'trendCounts'
        ));
    }

    public function patients(Request $request)
    {
        $query = Patient::with(['user', 'sessions']);
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('patient_number', 'like', "%{$request->search}%");
        }
        $patients = $query->latest()->paginate(10);
        return view('admin.patients.index', compact('patients'));
    }

    public function createPatient()
    {
        return view('admin.patients.create');
    }

    public function storePatient(Request $request)
    {
        $request->validate([
            'patient_number' => 'required|unique:patients,patient_number',
            'name' => 'required|string|max:100',
            'birth_date' => 'required|date',
            'gender' => 'required|in:L,P',
            'phone_number' => 'nullable|string',
            'address' => 'nullable|string',
            'patient_type' => 'required|in:umum,mhls',
            'username' => 'required|unique:users,username',
            'password' => 'required|min:6',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->username . '@dsh.internal',
                'role' => 'patient',
                'password' => Hash::make($request->password),
            ]);

            Patient::create([
                'user_id' => $user->id,
                'patient_number' => $request->patient_number,
                'name' => $request->name,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'patient_type' => $request->patient_type,
                'status' => 'active',
            ]);
        });

        return redirect()->route('admin.patients')->with('success', 'Data pasien berhasil ditambahkan!');
    }

    public function patientDetail($id)
    {
        $patient = Patient::with([
            'sessions.toxinAssessment',
            'sessions.bodyMeasurement',
            'sessions.bloodMeasurement',
            'activityPlans.schedules',
            'activityPlans.workflowReference',
        ])->findOrFail($id);

        return view('admin.patients.detail', compact('patient'));
    }

    public function examinations(Request $request)
    {
        $query = PatientProgressSession::with([
            'patient',
            'toxinAssessment',
            'bodyMeasurement',
            'bloodMeasurement',
            'visit',
        ]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                       ->orWhere('patient_number', 'like', "%{$search}%");
                })->orWhere('session_number', 'like', "%{$search}%");
            });
        }

        $sessions = $query->orderBy('session_date', 'desc')->paginate(15)->withQueryString();
        $patients = Patient::orderBy('name')->get(['id', 'name', 'patient_number', 'patient_type']);

        // Ambil kunjungan yang belum memiliki sesi pemeriksaan (Status: Menunggu Diperiksa / Belum Diisi)
        $pendingExaminations = Visit::with('patient')
            ->whereIn('status', ['confirmed', 'pending_confirmation'])
            ->whereDoesntHave('session')
            ->orderBy('visit_date', 'asc')
            ->get();

        return view('admin.examinations.index', compact('sessions', 'patients', 'pendingExaminations'));
    }

    public function monitoringProgress(Request $request)
    {
        $query = Patient::with([
            'sessions' => function ($q) {
                $q->latest('session_date');
            },
            'sessions.toxinAssessment',
            'sessions.bodyMeasurement',
            'sessions.bloodMeasurement',
            'activityPlans',
        ]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('patient_number', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('patient_type', $type);
        }

        $patients = $query->orderBy('name')->paginate(12)->withQueryString();

        return view('admin.monitoring.progress_index', compact('patients'));
    }

    public function createExamination($patientId)
    {
        $patient = Patient::findOrFail($patientId);
        $indicators = ToxinIndicators::all();
        $nextSessionNum = 'SESI-' . str_pad($patient->sessions()->count() + 1, 2, '0', STR_PAD_LEFT) . '-' . date('Ymd');
        $suggestedMeeting = $patient->patient_type === 'mhls' ? min(12, $patient->sessions()->count() + 1) : null;
        $activeVisits = Visit::where('patient_id', $patientId)->whereIn('status', ['confirmed', 'pending_confirmation'])->orderBy('visit_date')->get();

        return view('admin.examinations.create', compact('patient', 'indicators', 'nextSessionNum', 'suggestedMeeting', 'activeVisits'));
    }

    public function storeExamination(Request $request, $patientId)
    {
        $patient = Patient::findOrFail($patientId);

        $request->validate([
            'session_number' => 'required|string',
            'session_date' => 'required|date',
            'meeting_number' => 'nullable|integer|min:1|max:12',
            'visit_id' => 'nullable|exists:visits,id',
            'staff_notes' => 'nullable|string',
            'examination_summary' => 'nullable|string',
            'activity_evaluation_notes' => 'nullable|string',
            'indicated_numbers' => 'nullable|array',
        ]);

        DB::transaction(function () use ($request, $patient) {
            $session = PatientProgressSession::create([
                'patient_id' => $patient->id,
                'visit_id' => $request->visit_id,
                'meeting_number' => $request->meeting_number,
                'session_number' => $request->session_number,
                'session_date' => $request->session_date,
                'staff_notes' => $request->staff_notes,
                'examination_summary' => $request->examination_summary,
                'activity_evaluation_notes' => $request->activity_evaluation_notes,
                'recorded_at' => now(),
            ]);

            if ($request->visit_id) {
                Visit::where('id', $request->visit_id)->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }

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

            BodyMeasurement::create([
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

            $glucoseVal = $request->glucose ? (float) $request->glucose : null;
            $ketoneVal = $request->ketone ? (float) $request->ketone : null;
            $gkiVal = BloodMeasurement::computeGki($glucoseVal, $ketoneVal);

            BloodMeasurement::create([
                'session_id' => $session->id,
                'patient_id' => $patient->id,
                'blood_pressure' => $request->blood_pressure,
                'systolic' => $request->systolic,
                'diastolic' => $request->diastolic,
                'heart_rate' => $request->heart_rate,
                'hemoglobin' => $request->hemoglobin,
                'glucose' => $glucoseVal,
                'cholesterol' => $request->cholesterol,
                'uric_acid' => $request->uric_acid,
                'ketone' => $ketoneVal,
                'gki' => $gkiVal,
                'metabolic_notes' => $request->metabolic_notes,
            ]);

            // Transactional Outbox Event
            $event = new PatientProgressRecordedEvent($session->load(['toxinAssessment', 'bodyMeasurement', 'bloodMeasurement']));
            $this->outboxService->recordEvent(
                $event->getEventType(),
                $event->getAggregateType(),
                $event->getAggregateId(),
                $event->getPayload(),
                (string) Str::uuid()
            );
        });

        return redirect()->route('admin.patients.detail', $patient->id)->with('success', 'Sesi pemeriksaan berhasil dicatat!');
    }

    public function charts($patientId)
    {
        $patient = Patient::findOrFail($patientId);
        $sessions = PatientProgressSession::with(['toxinAssessment', 'bodyMeasurement', 'bloodMeasurement'])
            ->where('patient_id', $patientId)
            ->orderBy('session_date', 'asc')
            ->get();

        $chartData = [
            'dates' => [],
            'sessions' => [],
            'toxin_counts' => [],
            'toxin_levels' => [],
            'weight' => [],
            'bmi' => [],
            'waist' => [],
            'body_fat' => [],
            'visceral_fat' => [],
            'systolic' => [],
            'diastolic' => [],
            'heart_rate' => [],
            'glucose' => [],
            'cholesterol' => [],
            'uric_acid' => [],
            'ketone' => [],
            'gki' => [],
            'activity_compliance' => [],
        ];

        // Fetch activity plans & execution compliance for MHLS patients
        $plans = ActivityPlan::with(['schedules', 'reports'])
            ->where('patient_id', $patientId)
            ->get();
        $totalSchedules = $plans->flatMap->schedules->count();
        $doneSchedules = $plans->flatMap->reports->where('execution_status', 'done')->count();
        $overallCompliance = $totalSchedules > 0 ? round(($doneSchedules / $totalSchedules) * 100, 1) : null;

        foreach ($sessions as $index => $s) {
            $chartData['dates'][] = $s->session_date->format('d M Y');
            $chartData['sessions'][] = $s->meeting_number ? "Pertemuan #{$s->meeting_number}" : $s->session_number;
            $chartData['toxin_counts'][] = $s->toxinAssessment?->total_indicated;
            $chartData['toxin_levels'][] = $s->toxinAssessment?->level;
            $chartData['weight'][] = $s->bodyMeasurement?->weight ? (float) $s->bodyMeasurement->weight : null;
            $chartData['bmi'][] = $s->bodyMeasurement?->bmi ? (float) $s->bodyMeasurement->bmi : null;
            $chartData['waist'][] = $s->bodyMeasurement?->waist_circumference ? (float) $s->bodyMeasurement->waist_circumference : null;
            $chartData['body_fat'][] = $s->bodyMeasurement?->body_fat ? (float) $s->bodyMeasurement->body_fat : null;
            $chartData['visceral_fat'][] = $s->bodyMeasurement?->visceral_fat ? (float) $s->bodyMeasurement->visceral_fat : null;
            $chartData['systolic'][] = $s->bloodMeasurement?->systolic ? (float) $s->bloodMeasurement->systolic : null;
            $chartData['diastolic'][] = $s->bloodMeasurement?->diastolic ? (float) $s->bloodMeasurement->diastolic : null;
            $chartData['heart_rate'][] = $s->bloodMeasurement?->heart_rate ? (float) $s->bloodMeasurement->heart_rate : null;
            $chartData['glucose'][] = $s->bloodMeasurement?->glucose ? (float) $s->bloodMeasurement->glucose : null;
            $chartData['cholesterol'][] = $s->bloodMeasurement?->cholesterol ? (float) $s->bloodMeasurement->cholesterol : null;
            $chartData['uric_acid'][] = $s->bloodMeasurement?->uric_acid ? (float) $s->bloodMeasurement->uric_acid : null;
            $chartData['ketone'][] = $s->bloodMeasurement?->ketone ? (float) $s->bloodMeasurement->ketone : null;
            $chartData['gki'][] = $s->bloodMeasurement?->gki ? (float) $s->bloodMeasurement->gki : null;
            
            // Progressive compliance simulation per session milestone
            $simulatedCompliance = $overallCompliance !== null 
                ? min(100, round(($overallCompliance * 0.7) + (($index + 1) / max(1, $sessions->count())) * ($overallCompliance * 0.3), 1))
                : null;
            $chartData['activity_compliance'][] = $simulatedCompliance;
        }

        // Compute summary metrics (Delta comparison: first vs latest)
        $summary = [
            'first_session' => $sessions->first(),
            'latest_session' => $sessions->last(),
            'total_sessions' => $sessions->count(),
            'toxin_delta' => null,
            'weight_delta' => null,
            'gki_delta' => null,
            'overall_compliance' => $overallCompliance,
            'active_plans_count' => $plans->where('status', 'active')->count(),
        ];

        if ($sessions->count() >= 2) {
            $first = $sessions->first();
            $last = $sessions->last();
            if ($first->toxinAssessment && $last->toxinAssessment) {
                $summary['toxin_delta'] = $last->toxinAssessment->total_indicated - $first->toxinAssessment->total_indicated;
            }
            if ($first->bodyMeasurement?->weight && $last->bodyMeasurement?->weight) {
                $summary['weight_delta'] = round((float) $last->bodyMeasurement->weight - (float) $first->bodyMeasurement->weight, 2);
            }
            if ($first->bloodMeasurement?->gki && $last->bloodMeasurement?->gki) {
                $summary['gki_delta'] = round((float) $last->bloodMeasurement->gki - (float) $first->bloodMeasurement->gki, 2);
            }
        }

        return view('admin.examinations.charts', compact('patient', 'chartData', 'sessions', 'summary'));
    }

    public function activityPlans(Request $request)
    {
        $query = ActivityPlan::with(['patient', 'workflowReference', 'schedules']);
        if ($request->status) {
            $query->where('status', $request->status);
        }
        $plans = $query->latest()->paginate(10);
        return view('admin.activities.index', compact('plans'));
    }

    public function createActivityPlan()
    {
        $patients = Patient::where('patient_type', 'mhls')->where('status', 'active')->get();
        return view('admin.activities.create', compact('patients'));
    }

    public function storeActivityPlan(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'frequency_type' => 'required|in:daily,custom_days,weekly',
            'schedule_days' => 'nullable|array',
            'reminder_time' => 'required',
            'additional_notes' => 'nullable|string',
        ]);

        $plan = DB::transaction(function () use ($request) {
            $plan = ActivityPlan::create([
                'patient_id' => (int) $request->patient_id,
                'name' => $request->name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'frequency_type' => $request->frequency_type,
                'schedule_days' => $request->schedule_days ?: ['all'],
                'reminder_time' => $request->reminder_time,
                'additional_notes' => $request->additional_notes,
                'status' => 'active',
                'created_by_user_id' => Auth::id(),
            ]);

            // Generate schedules
            $start = Carbon::parse($plan->start_date);
            $end = Carbon::parse($plan->end_date);
            $period = CarbonPeriod::create($start, $end);
            $customDays = $plan->schedule_days ?: [];

            foreach ($period as $date) {
                $dayName = $date->format('D');
                $should = false;
                if ($plan->frequency_type === 'daily') $should = true;
                elseif ($plan->frequency_type === 'custom_days' && in_array($dayName, $customDays)) $should = true;
                elseif ($plan->frequency_type === 'weekly' && $date->isMonday()) $should = true;

                if ($should) {
                    ActivitySchedule::create([
                        'activity_plan_id' => $plan->id,
                        'scheduled_date' => $date->format('Y-m-d'),
                        'reminder_time' => $plan->reminder_time,
                        'status' => 'pending',
                    ]);
                }
            }

            // Publish Transactional Outbox Event
            $correlationId = (string) Str::uuid();
            $event = new ActivityPlanCreatedEvent($plan->load(['patient', 'schedules']), $correlationId);
            $this->outboxService->recordEvent(
                $event->getEventType(),
                $event->getAggregateType(),
                $event->getAggregateId(),
                $event->getPayload(),
                $correlationId
            );

            return $plan;
        });

        return redirect()->route('admin.activities.detail', $plan->id)->with('success', 'Instruksi aktivitas mandiri berhasil dibuat! Event Transactional Outbox telah diterbitkan.');
    }

    public function activityPlanDetail($id)
    {
        $plan = ActivityPlan::with([
            'patient',
            'schedules.report',
            'reports',
            'workflowReference',
            'notificationLogs'
        ])->findOrFail($id);

        $outboxEvents = OutboxEvent::where('aggregate_type', 'ActivityPlan')
            ->where('aggregate_id', (string) $plan->id)
            ->latest()
            ->get();

        return view('admin.activities.detail', compact('plan', 'outboxEvents'));
    }

    public function cancelActivityPlan(Request $request, $id)
    {
        $plan = ActivityPlan::findOrFail($id);

        DB::transaction(function () use ($request, $plan) {
            $plan->update(['status' => 'cancelled']);
            ActivitySchedule::where('activity_plan_id', $plan->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            $correlationId = (string) Str::uuid();
            $reason = $request->input('reason', 'Dibatalkan oleh petugas klinik via Web Admin');
            $event = new ActivityPlanCancelledEvent($plan, $reason, $correlationId);

            $this->outboxService->recordEvent(
                $event->getEventType(),
                $event->getAggregateType(),
                $event->getAggregateId(),
                $event->getPayload(),
                $correlationId
            );
        });

        return back()->with('success', 'Instruksi aktivitas berhasil dibatalkan dan signal pembatalan dikirim ke outbox!');
    }

    public function edaTemporalMonitor()
    {
        $outboxEvents = OutboxEvent::latest('occurred_at')->take(30)->get();
        $workflows = WorkflowReference::with('plan.patient')->latest()->take(30)->get();
        $notifications = NotificationLog::with('patient')->latest('sent_at')->take(30)->get();

        return view('admin.monitoring.index', compact('outboxEvents', 'workflows', 'notifications'));
    }
}

