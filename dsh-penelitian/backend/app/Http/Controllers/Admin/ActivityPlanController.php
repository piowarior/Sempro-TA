<?php

namespace App\Http\Controllers\Admin;

use App\Events\Domain\ActivityPlanCancelledEvent;
use App\Events\Domain\ActivityPlanCreatedEvent;
use App\Events\Domain\ActivityPlanUpdatedEvent;
use App\Http\Controllers\Controller;
use App\Models\ActivityPlan;
use App\Models\ActivitySchedule;
use App\Models\NotificationLog;
use App\Models\OutboxEvent;
use App\Models\Patient;
use App\Models\WorkflowReference;
use App\Services\OutboxService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActivityPlanController extends Controller
{
    public function __construct(protected OutboxService $outboxService) {}

    public function index(Request $request)
    {
        $query = ActivityPlan::with(['patient', 'workflowReference', 'schedules', 'reports']);

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $plans = $query->latest()->paginate(15);
        return response()->json($plans);
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'session_id' => 'nullable|exists:patient_progress_sessions,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'frequency_type' => 'required|in:daily,custom_days,weekly',
            'schedule_days' => 'nullable|array',
            'reminder_time' => 'required|date_format:H:i',
            'additional_notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $plan = ActivityPlan::create([
                'patient_id' => $request->patient_id,
                'session_id' => $request->session_id,
                'name' => $request->name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'frequency_type' => $request->frequency_type,
                'schedule_days' => $request->schedule_days ?: ['all'],
                'reminder_time' => $request->reminder_time,
                'additional_notes' => $request->additional_notes,
                'status' => 'active',
                'created_by_user_id' => $request->user()?->id,
            ]);

            // Generate individual schedules
            $this->generateSchedules($plan);

            // Record Domain Event in Transactional Outbox
            $correlationId = (string) Str::uuid();
            $event = new ActivityPlanCreatedEvent($plan->load(['patient', 'schedules']), $correlationId);
            
            $this->outboxService->recordEvent(
                $event->getEventType(),
                $event->getAggregateType(),
                $event->getAggregateId(),
                $event->getPayload(),
                $correlationId
            );

            return response()->json([
                'message' => 'Instruksi aktivitas mandiri berhasil dibuat dan event outbox diterbitkan',
                'plan' => $plan->load(['patient', 'schedules']),
            ], 201);
        });
    }

    public function show($id)
    {
        $plan = ActivityPlan::with([
            'patient',
            'schedules.report',
            'reports',
            'workflowReference',
            'notificationLogs',
        ])->findOrFail($id);

        return response()->json($plan);
    }

    public function update(Request $request, $id)
    {
        $plan = ActivityPlan::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'reminder_time' => 'sometimes|required|date_format:H:i',
            'additional_notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $plan) {
            $plan->update($request->only([
                'name', 'description', 'start_date', 'end_date', 'reminder_time', 'additional_notes'
            ]));

            // Remove future un-reported schedules and regenerate
            $today = Carbon::today()->format('Y-m-d');
            ActivitySchedule::where('activity_plan_id', $plan->id)
                ->where('scheduled_date', '>=', $today)
                ->where('status', 'pending')
                ->delete();

            $this->generateSchedules($plan, $today);

            // Publish Outbox Event
            $correlationId = (string) Str::uuid();
            $event = new ActivityPlanUpdatedEvent($plan->load('schedules'), $correlationId);
            $this->outboxService->recordEvent(
                $event->getEventType(),
                $event->getAggregateType(),
                $event->getAggregateId(),
                $event->getPayload(),
                $correlationId
            );

            return response()->json([
                'message' => 'Instruksi aktivitas berhasil diperbarui dan disinkronisasi ke outbox',
                'plan' => $plan->load('schedules'),
            ]);
        });
    }

    public function cancel(Request $request, $id)
    {
        $plan = ActivityPlan::findOrFail($id);

        $request->validate([
            'reason' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $plan) {
            $plan->update(['status' => 'cancelled']);

            ActivitySchedule::where('activity_plan_id', $plan->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            // Outbox Event
            $correlationId = (string) Str::uuid();
            $reason = $request->input('reason', 'Dibatalkan oleh petugas klinik');
            $event = new ActivityPlanCancelledEvent($plan, $reason, $correlationId);

            $this->outboxService->recordEvent(
                $event->getEventType(),
                $event->getAggregateType(),
                $event->getAggregateId(),
                $event->getPayload(),
                $correlationId
            );

            return response()->json([
                'message' => 'Instruksi aktivitas berhasil dibatalkan',
                'plan' => $plan,
            ]);
        });
    }

    private function generateSchedules(ActivityPlan $plan, ?string $fromDate = null): void
    {
        $start = Carbon::parse($fromDate ?: $plan->start_date);
        $end = Carbon::parse($plan->end_date);

        if ($start->gt($end)) {
            return;
        }

        $period = CarbonPeriod::create($start, $end);
        $customDays = $plan->schedule_days ?: [];

        foreach ($period as $date) {
            $dayName = $date->format('D'); // Mon, Tue, etc.
            $shouldSchedule = false;

            if ($plan->frequency_type === 'daily') {
                $shouldSchedule = true;
            } elseif ($plan->frequency_type === 'custom_days' && in_array($dayName, $customDays)) {
                $shouldSchedule = true;
            } elseif ($plan->frequency_type === 'weekly' && $date->isMonday()) {
                $shouldSchedule = true;
            }

            if ($shouldSchedule) {
                ActivitySchedule::firstOrCreate(
                    [
                        'activity_plan_id' => $plan->id,
                        'scheduled_date' => $date->format('Y-m-d'),
                    ],
                    [
                        'reminder_time' => $plan->reminder_time,
                        'status' => 'pending',
                    ]
                );
            }
        }
    }

    public function outboxEvents(Request $request)
    {
        $events = OutboxEvent::latest('occurred_at')->paginate(20);
        return response()->json($events);
    }

    public function workflows(Request $request)
    {
        $workflows = WorkflowReference::with('plan.patient')->latest()->paginate(20);
        return response()->json($workflows);
    }

    public function notifications(Request $request)
    {
        $notifications = NotificationLog::with('patient')->latest('sent_at')->paginate(20);
        return response()->json($notifications);
    }
}

