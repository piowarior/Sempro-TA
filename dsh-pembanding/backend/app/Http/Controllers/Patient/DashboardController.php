<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\ActivityPlan;
use App\Models\ActivitySchedule;
use App\Models\Patient;
use App\Models\PatientProgressSession;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json(['message' => 'Profil pasien tidak ditemukan untuk akun ini'], 404);
        }

        $latestSession = PatientProgressSession::with(['toxinAssessment', 'bodyMeasurement', 'bloodMeasurement'])
            ->where('patient_id', $patient->id)
            ->latest('session_date')
            ->first();

        // Active activity plans
        $activePlans = ActivityPlan::with(['schedules' => function ($q) {
            $q->orderBy('scheduled_date');
        }])
        ->where('patient_id', $patient->id)
        ->where('status', 'active')
        ->get();

        // Today's activities
        $today = Carbon::today()->format('Y-m-d');
        $todaySchedules = ActivitySchedule::with('plan')
            ->whereHas('plan', function ($q) use ($patient) {
                $q->where('patient_id', $patient->id)->where('status', 'active');
            })
            ->where('scheduled_date', $today)
            ->get();

        return response()->json([
            'patient' => [
                'id' => $patient->id,
                'patient_number' => $patient->patient_number,
                'name' => $patient->name,
                'gender' => $patient->gender,
                'patient_type' => $patient->patient_type,
            ],
            'latest_session' => $latestSession ? [
                'session_number' => $latestSession->session_number,
                'session_date' => $latestSession->session_date->format('d M Y'),
                'staff_notes' => $latestSession->staff_notes,
                'examination_summary' => $latestSession->examination_summary,
                'toxin_level' => $latestSession->toxinAssessment?->level,
                'toxin_total' => $latestSession->toxinAssessment?->total_indicated,
                'weight' => $latestSession->bodyMeasurement?->weight,
                'bmi' => $latestSession->bodyMeasurement?->bmi,
                'blood_pressure' => $latestSession->bloodMeasurement?->blood_pressure,
                'glucose' => $latestSession->bloodMeasurement?->glucose,
                'ketone' => $latestSession->bloodMeasurement?->ketone,
            ] : null,
            'active_plans_count' => $activePlans->count(),
            'active_plans' => $activePlans,
            'today_schedules' => $todaySchedules,
        ]);
    }

    public function examinations(Request $request)
    {
        $patient = $request->user()->patient;
        if (!$patient) {
            return response()->json(['message' => 'Profil pasien tidak ditemukan'], 404);
        }

        $sessions = PatientProgressSession::with([
            'toxinAssessment.answers',
            'bodyMeasurement',
            'bloodMeasurement'
        ])
        ->where('patient_id', $patient->id)
        ->orderBy('session_date', 'desc')
        ->get();

        return response()->json($sessions);
    }
}

