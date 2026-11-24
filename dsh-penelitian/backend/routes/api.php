<?php

use App\Http\Controllers\Admin\ActivityPlanController;
use App\Http\Controllers\Admin\ExaminationController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Patient\ActivityController as PatientActivityController;
use App\Http\Controllers\Patient\DashboardController as PatientDashboardController;
use App\Models\ActivityPlan;
use App\Models\ActivitySchedule;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Auth
Route::post('/auth/login', [AuthController::class, 'login']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Admin Group
    Route::prefix('admin')->group(function () {
        // Patient Management
        Route::get('/patients', [PatientController::class, 'index']);
        Route::post('/patients', [PatientController::class, 'store']);
        Route::get('/patients/{id}', [PatientController::class, 'show']);

        // Examination & Progress
        Route::get('/indicators', [ExaminationController::class, 'getIndicators']);
        Route::post('/patients/{id}/sessions', [ExaminationController::class, 'store']);
        Route::get('/patients/{id}/progress-charts', [ExaminationController::class, 'progressCharts']);

        // Activity Plans (MHLS)
        Route::get('/activity-plans', [ActivityPlanController::class, 'index']);
        Route::post('/activity-plans', [ActivityPlanController::class, 'store']);
        Route::get('/activity-plans/{id}', [ActivityPlanController::class, 'show']);
        Route::put('/activity-plans/{id}', [ActivityPlanController::class, 'update']);
        Route::delete('/activity-plans/{id}', [ActivityPlanController::class, 'cancel']);

        // Monitoring
        Route::get('/outbox-events', [ActivityPlanController::class, 'outboxEvents']);
        Route::get('/workflows', [ActivityPlanController::class, 'workflows']);
        Route::get('/notifications', [ActivityPlanController::class, 'notifications']);
    });

    // Patient Mobile Group
    Route::prefix('patient')->group(function () {
        Route::get('/dashboard', [PatientDashboardController::class, 'index']);
        Route::get('/examinations', [PatientDashboardController::class, 'examinations']);
        Route::get('/activities', [PatientActivityController::class, 'index']);
        Route::get('/activities/{id}', [PatientActivityController::class, 'show']);
        Route::post('/activities/{planId}/report', [PatientActivityController::class, 'report']);
    });
});

// Internal Callback Endpoints for Temporal Worker Activities
Route::prefix('internal')->group(function () {
    Route::post('/schedules/{id}/reminder-sent', function (Request $request, $id) {
        $schedule = ActivitySchedule::with('plan.patient')->findOrFail($id);
        $schedule->update([
            'status' => 'reminder_sent',
            'reminder_sent_at' => now(),
        ]);

        NotificationLog::create([
            'activity_plan_id' => $schedule->activity_plan_id,
            'patient_id' => $schedule->plan->patient_id,
            'notification_type' => 'reminder',
            'recipient' => $schedule->plan->patient->phone_number ?: $schedule->plan->patient->name,
            'message' => $request->input('message', "Pengingat aktivitas: {$schedule->plan->name}"),
            'sent_at' => now(),
            'delivery_status' => 'success',
            'provider_response' => json_encode(['provider' => 'mock_dsh_gateway', 'status' => 'delivered']),
        ]);

        return response()->json(['success' => true]);
    });

    Route::post('/plans/{id}/complete', function ($id) {
        $plan = ActivityPlan::findOrFail($id);
        $plan->update(['status' => 'completed']);
        return response()->json(['success' => true]);
    });
});

