<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::with(['user', 'sessions' => function ($q) {
            $q->latest('session_date')->limit(1);
        }]);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('patient_number', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('patient_type')) {
            $query->where('patient_type', $request->patient_type);
        }

        $patients = $query->orderBy('name')->paginate(15);

        return response()->json($patients);
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_number' => 'required|string|unique:patients,patient_number',
            'name' => 'required|string|max:100',
            'birth_date' => 'required|date',
            'gender' => 'required|in:L,P',
            'phone_number' => 'nullable|string|max:25',
            'address' => 'nullable|string',
            'patient_type' => 'required|in:umum,mhls',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
        ]);

        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->username . '@dsh.internal',
                'role' => 'patient',
                'password' => Hash::make($request->password),
            ]);

            $patient = Patient::create([
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

            return response()->json([
                'message' => 'Data pasien berhasil dibuat',
                'patient' => $patient->load('user'),
            ], 201);
        });
    }

    public function show($id)
    {
        $patient = Patient::with([
            'user',
            'sessions.toxinAssessment',
            'sessions.bodyMeasurement',
            'sessions.bloodMeasurement',
            'activityPlans.schedules',
            'activityPlans.workflowReference',
        ])->findOrFail($id);

        return response()->json($patient);
    }
}

