<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $patient = $request->user()->patient;
        if (!$patient) {
            return response()->json(['message' => 'Profil pasien tidak ditemukan'], 404);
        }

        $visits = Visit::where('patient_id', $patient->id)
            ->orderBy('visit_date', 'desc')
            ->get();

        $activeVisit = Visit::where('patient_id', $patient->id)
            ->whereIn('status', ['confirmed', 'pending_confirmation'])
            ->where('visit_date', '>=', now()->toDateString())
            ->orderBy('visit_date', 'asc')
            ->first();

        $completedCount = Visit::where('patient_id', $patient->id)
            ->where('status', 'completed')
            ->count();

        return response()->json([
            'patient' => [
                'id' => $patient->id,
                'name' => $patient->name,
                'patient_type' => $patient->patient_type,
                'completed_meetings' => $completedCount,
                'total_program_meetings' => 12,
            ],
            'active_visit' => $activeVisit,
            'visits' => $visits,
        ]);
    }

    public function storeBooking(Request $request)
    {
        $patient = $request->user()->patient;
        if (!$patient) {
            return response()->json(['message' => 'Profil pasien tidak ditemukan'], 404);
        }

        if ($patient->patient_type !== 'mhls') {
            return response()->json([
                'message' => 'Pengajuan booking mandiri via aplikasi khusus untuk pasien program MHLS.',
            ], 403);
        }

        $request->validate([
            'visit_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|string|max:50',
            'meeting_number' => 'nullable|integer|min:1|max:12',
            'patient_notes' => 'nullable|string',
        ]);

        $nextMeeting = $request->meeting_number ?: ($patient->sessions()->count() + 1);

        $visit = Visit::create([
            'patient_id' => $patient->id,
            'meeting_number' => min(12, $nextMeeting),
            'visit_date' => $request->visit_date,
            'time_slot' => $request->time_slot,
            'booking_type' => 'mhls_followup',
            'status' => 'pending_confirmation',
            'requested_by' => 'patient_app',
            'patient_notes' => $request->patient_notes,
        ]);

        return response()->json([
            'message' => 'Pengajuan jadwal kunjungan berhasil dikirim dan menunggu konfirmasi klinik.',
            'visit' => $visit,
        ], 201);
    }
}
