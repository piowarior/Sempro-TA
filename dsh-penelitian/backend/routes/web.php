<?php

use App\Http\Controllers\Web\AdminWebController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Auth Routes
Route::get('/login', [AdminWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AdminWebController::class, 'processLogin'])->name('login.post');
Route::post('/logout', [AdminWebController::class, 'logout'])->name('logout');

// Protected Admin Routes
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminWebController::class, 'dashboard'])->name('dashboard');

    // Patients
    Route::get('/patients', [AdminWebController::class, 'patients'])->name('patients');
    Route::get('/patients/create', [AdminWebController::class, 'createPatient'])->name('patients.create');
    Route::post('/patients', [AdminWebController::class, 'storePatient'])->name('patients.store');
    Route::get('/patients/{id}', [AdminWebController::class, 'patientDetail'])->name('patients.detail');

    // Examination & Progress
    Route::get('/patients/{id}/examinations/create', [AdminWebController::class, 'createExamination'])->name('examinations.create');
    Route::post('/patients/{id}/examinations', [AdminWebController::class, 'storeExamination'])->name('examinations.store');
    Route::get('/patients/{id}/charts', [AdminWebController::class, 'charts'])->name('examinations.charts');

    // Activity Plans (MHLS)
    Route::get('/activities', [AdminWebController::class, 'activityPlans'])->name('activities');
    Route::get('/activities/create', [AdminWebController::class, 'createActivityPlan'])->name('activities.create');
    Route::post('/activities', [AdminWebController::class, 'storeActivityPlan'])->name('activities.store');
    Route::get('/activities/{id}', [AdminWebController::class, 'activityPlanDetail'])->name('activities.detail');
    Route::post('/activities/{id}/cancel', [AdminWebController::class, 'cancelActivityPlan'])->name('activities.cancel');

    // Real-time EDA & Temporal Workflow Monitor
    Route::get('/eda-temporal-monitor', [AdminWebController::class, 'edaTemporalMonitor'])->name('monitoring');
});
