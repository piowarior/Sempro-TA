<?php

namespace App\Http\Controllers\Patient;

use App\Events\Domain\PatientActivityReportedEvent;
use App\Http\Controllers\Controller;
use App\Models\ActivityPlan;
use App\Models\ActivitySchedule;
use App\Models\PatientActivityReport;
use App\Services\OutboxService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActivityController extends Controller
{
    public function __construct(protected OutboxService $outboxService) {}

    public function index(Request $request)
    {
        $patient = $request->user()->patient;
        if (!$patient) {
            return response()->json(['message' => 'Profil pasien tidak ditemukan'], 404);
        }

        $plans = ActivityPlan::with(['schedules.report', 'reports'])
            ->where('patient_id', $patient->id)
            ->latest('start_date')
            ->get();

        return response()->json($plans);
    }

    public function show(Request $request, $id)
    {
        $patient = $request->user()->patient;
        $plan = ActivityPlan::with(['schedules.report', 'reports'])
            ->where('patient_id', $patient->id)
            ->findOrFail($id);

        return response()->json($plan);
    }

    public function report(Request $request, $planId)
    {
        $patient = $request->user()->patient;
        $plan = ActivityPlan::where('patient_id', $patient->id)->findOrFail($planId);

        $request->validate([
            'activity_date' => 'required|date',
            'execution_status' => 'required|in:dilaksanakan,tidak_dilaksanakan',
            'patient_notes' => 'nullable|string',
            'activity_schedule_id' => 'nullable|exists:activity_schedules,id',
        ]);

        return DB::transaction(function () use ($request, $patient, $plan) {
            // Find schedule for that date if not provided
            $scheduleId = $request->activity_schedule_id;
            if (!$scheduleId) {
                $schedule = ActivitySchedule::where('activity_plan_id', $plan->id)
                    ->where('scheduled_date', $request->activity_date)
                    ->whereDate('scheduled_date', $request->activity_date)
                    ->first();
                $scheduleId = $schedule?->id;
            }

            // 1. Save patient report to database FIRST
            // 1. Create Patient Activity Report directly
            $report = PatientActivityReport::create([
                'activity_plan_id' => $plan->id,
                'activity_schedule_id' => $scheduleId,
                'patient_id' => $patient->id,
                'activity_date' => $request->activity_date,
                'report_time' => now(),
                'report_time' => Carbon::now(),
                'execution_status' => $request->execution_status,
                'patient_notes' => $request->patient_notes,
            ]);

            // Update schedule status if matching
            // 2. Update schedule status directly
            if ($scheduleId) {
                ActivitySchedule::where('id', $scheduleId)->update(['status' => 'reported']);
                ActivitySchedule::where('id', $scheduleId)->update([
                    'status' => $request->execution_status === 'dilaksanakan' ? 'completed' : 'missed',
                ]);
            }

            // 2. Publish PatientActivityReported event to transactional outbox
            $correlationId = (string) Str::uuid();
            $event = new PatientActivityReportedEvent($report, $correlationId);

            $this->outboxService->recordEvent(
                $event->getEventType(),
                $event->getAggregateType(),
                $event->getAggregateId(),
                $event->getPayload(),
                $correlationId
            );

            return response()->json([
                'message' => 'Laporan aktivitas berhasil disimpan dan event diterbitkan',
                'message' => 'Laporan aktivitas berhasil disimpan secara langsung (CRUD Konvensional)',
                'report' => $report,
            ], 201);
        });
    }
}

