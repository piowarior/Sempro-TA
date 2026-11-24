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
        $totalSessions = PatientProgressSession::count();
        $activePlans = ActivityPlan::where('status', 'active')->count();
        $totalOutbox = OutboxEvent::count();
        $pendingOutbox = OutboxEvent::where('status', 'pending')->count();
        $activeWorkflows = WorkflowReference::where('status', 'running')->count();

        $recentPatients = Patient::with('sessions')->latest()->take(5)->get();
        $recentPlans = ActivityPlan::with(['patient', 'workflowReference'])->latest()->take(5)->get();
        $recentEvents = OutboxEvent::latest('occurred_at')->take(5)->get();

        return view('admin.dashboard', compact(
            'totalPatients', 'totalSessions', 'activePlans',
            'totalOutbox', 'pendingOutbox', 'activeWorkflows',
            'recentPatients', 'recentPlans', 'recentEvents'
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

    public function createExamination($patientId)
    {
        $patient = Patient::findOrFail($patientId);
        $indicators = ToxinIndicators::all();
        $nextSessionNum = 'SESI-' . str_pad($patient->sessions()->count() + 1, 2, '0', STR_PAD_LEFT) . '-' . date('Ymd');

        return view('admin.examinations.create', compact('patient', 'indicators', 'nextSessionNum'));
    }

    public function storeExamination(Request $request, $patientId)
    {
        $patient = Patient::findOrFail($patientId);

        $request->validate([
            'session_number' => 'required|string',
            'session_date' => 'required|date',
            'staff_notes' => 'nullable|string',
            'examination_summary' => 'nullable|string',
            'indicated_numbers' => 'nullable|array',
        ]);

        DB::transaction(function () use ($request, $patient) {
            $session = PatientProgressSession::create([
                'patient_id' => $patient->id,
                'session_number' => $request->session_number,
                'session_date' => $request->session_date,
                'staff_notes' => $request->staff_notes,
                'examination_summary' => $request->examination_summary,
                'recorded_at' => now(),
            ]);

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

            BloodMeasurement::create([
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

        return view('admin.examinations.charts', compact('patient', 'chartData', 'sessions'));
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

