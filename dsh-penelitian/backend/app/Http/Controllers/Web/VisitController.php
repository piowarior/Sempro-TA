<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $query = Visit::with(['patient', 'progressSession']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->patient_id) {
            $query->where('patient_id', $request->patient_id);
        }

        $visits = $query->orderBy('visit_date', 'desc')->paginate(15);
        $pendingCount = Visit::where('status', 'pending_confirmation')->count();
        $confirmedCount = Visit::where('status', 'confirmed')->count();
        $completedCount = Visit::where('status', 'completed')->count();

        return view('admin.visits.index', compact('visits', 'pendingCount', 'confirmedCount', 'completedCount'));
    }

    public function create()
    {
        $patients = Patient::where('status', 'active')->orderBy('name')->get();
        return view('admin.visits.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'visit_date' => 'required|date',
            'time_slot' => 'required|string|max:50',
            'booking_type' => 'required|in:first_visit,mhls_followup,general_checkup',
            'meeting_number' => 'nullable|integer|min:1|max:12',
            'staff_notes' => 'nullable|string',
        ]);

        $patient = Patient::findOrFail($request->patient_id);

        Visit::create([
            'patient_id' => $patient->id,
            'meeting_number' => $request->meeting_number,
            'visit_date' => $request->visit_date,
            'time_slot' => $request->time_slot,
            'booking_type' => $request->booking_type,
            'status' => 'confirmed',
            'requested_by' => 'clinic',
            'staff_notes' => $request->staff_notes,
            'confirmed_at' => now(),
        ]);

        return redirect()->route('admin.visits')->with('success', 'Jadwal kunjungan berhasil dibuat!');
    }

    public function confirm($id)
    {
        $visit = Visit::findOrFail($id);
        $visit->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan jadwal kunjungan berhasil dikonfirmasi!');
    }

    public function cancel(Request $request, $id)
    {
        $visit = Visit::findOrFail($id);
        $visit->update([
            'status' => 'cancelled',
            'staff_notes' => ($visit->staff_notes ? $visit->staff_notes . "\n" : '') . "Dibatalkan: " . ($request->reason ?? 'Dibatalkan pihak klinik'),
        ]);

        return back()->with('success', 'Jadwal kunjungan berhasil dibatalkan.');
    }
}
