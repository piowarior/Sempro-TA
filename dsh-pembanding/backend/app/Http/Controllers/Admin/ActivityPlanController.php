<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityPlan;
use App\Models\ActivitySchedule;
use App\Models\NotificationLog;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityPlan::with(['patient', 'schedules', 'reports']);

        if ($request->patient_id) {
            $query->where('patient_id', $request->patient_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $plans = $query->latest('created_at')->paginate(15);
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

        $plan = DB::transaction(function () use ($request) {
            $plan = ActivityPlan::create([
                'patient_id' => $request->patient_id,
                'session_id' => $request->session_id,
                'name' => $request->name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'frequency_type' => $request->frequency_type,
                'schedule_days' => $request->schedule_days ?? ['all'],
                'reminder_time' => $request->reminder_time,
                'additional_notes' => $request->additional_notes,
                'status' => 'active',
                'created_by_user_id' => $request->user()->id,
            ]);

            // Generate daily / scheduled slots
            $start = Carbon::parse($plan->start_date);
            $end = Carbon::parse($plan->end_date);
            $period = CarbonPeriod::create($start, $end);
            $customDays = $plan->schedule_days ?? [];

            foreach ($period as $date) {
                $dayName = $date->format('D');
                $shouldCreate = false;

                if ($plan->frequency_type === 'daily') {
                    $shouldCreate = true;
                } elseif ($plan->frequency_type === 'custom_days' && in_array($dayName, $customDays)) {
                    $shouldCreate = true;
                } elseif ($plan->frequency_type === 'weekly' && $date->isMonday()) {
                    $shouldCreate = true;
                }

                if ($shouldCreate) {
                    ActivitySchedule::create([
                        'activity_plan_id' => $plan->id,
                        'scheduled_date' => $date->format('Y-m-d'),
                        'reminder_time' => $plan->reminder_time,
                        'status' => 'pending',
                    ]);
                }
            }

            return $plan;
        });

        return response()->json([
            'message' => 'Instruksi aktivitas mandiri berhasil dibuat secara langsung (CRUD Konvensional)',
            'plan' => $plan->load(['patient', 'schedules']),
        ], 201);
    }

    public function show($id)
    {
        $plan = ActivityPlan::with(['patient', 'schedules.report', 'reports'])
            ->findOrFail($id);
        return response()->json($plan);
    }

    public function update(Request $request, $id)
    {
        $plan = ActivityPlan::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'reminder_time' => 'sometimes|date_format:H:i',
            'additional_notes' => 'nullable|string',
        ]);

        $plan->update($request->only(['name', 'description', 'reminder_time', 'additional_notes']));

        return response()->json([
            'message' => 'Instruksi aktivitas berhasil diperbarui langsung di database',
            'plan' => $plan,
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $plan = ActivityPlan::findOrFail($id);

        DB::transaction(function () use ($plan, $request) {
            $plan->update([
                'status' => 'cancelled',
                'additional_notes' => ($plan->additional_notes ? $plan->additional_notes . "\n" : '') .
                    "Dibatalkan pada " . Carbon::now()->toIso8601String() . ": " . ($request->reason ?? 'Instruksi dihentikan petugas'),
            ]);

            // Cancel all upcoming pending schedules directly
            ActivitySchedule::where('activity_plan_id', $plan->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);
        });

        return response()->json([
            'message' => 'Instruksi aktivitas berhasil dibatalkan secara langsung di database',
            'plan' => $plan,
        ]);
    }

    public function notifications(Request $request)
    {
        $logs = NotificationLog::with('patient')->latest('sent_at')->paginate(20);
        return response()->json($logs);
    }
}
