<?php

namespace Database\Seeders;

use App\Models\BloodMeasurement;
use App\Models\BodyMeasurement;
use App\Models\Patient;
use App\Models\PatientProgressSession;
use App\Models\ToxinAssessmentAnswer;
use App\Models\ToxinAssessmentResult;
use App\Models\User;
use App\Support\ToxinIndicators;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin User
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Petugas Klinik DSH',
                'email' => 'admin@dsh.clinic',
                'role' => 'admin',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Create MHLS Patient User & Profile (Budi Santoso)
        $userBudi = User::firstOrCreate(
            ['username' => 'budi'],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'role' => 'patient',
                'password' => Hash::make('pasien123'),
            ]
        );

        $patientBudi = Patient::firstOrCreate(
            ['patient_number' => 'DSH-MHLS-001'],
            [
                'user_id' => $userBudi->id,
                'name' => 'Budi Santoso',
                'birth_date' => '1988-06-15',
                'gender' => 'L',
                'phone_number' => '081234567890',
                'address' => 'Jl. Merdeka No. 12, Surabaya',
                'patient_type' => 'mhls',
                'status' => 'active',
            ]
        );

        // 3. Create Umum Patient User & Profile (Siti Rahayu)
        $userSiti = User::firstOrCreate(
            ['username' => 'siti'],
            [
                'name' => 'Siti Rahayu',
                'email' => 'siti@example.com',
                'role' => 'patient',
                'password' => Hash::make('pasien123'),
            ]
        );

        $patientSiti = Patient::firstOrCreate(
            ['patient_number' => 'DSH-UMUM-001'],
            [
                'user_id' => $userSiti->id,
                'name' => 'Siti Rahayu',
                'birth_date' => '1992-09-22',
                'gender' => 'P',
                'phone_number' => '085678901234',
                'address' => 'Jl. Melati No. 5, Sidoarjo',
                'patient_type' => 'umum',
                'status' => 'active',
            ]
        );

        // 4. Seed initial examination sessions for Budi
        $this->seedInitialExaminationsForBudi($patientBudi);
    }

    private function seedInitialExaminationsForBudi(Patient $patient): void
    {
        // Session 1: Baseline
        $session1 = PatientProgressSession::firstOrCreate(
            ['patient_id' => $patient->id, 'session_number' => 'SESI-01-20261010'],
            [
                'session_date' => '2026-10-10',
                'staff_notes' => 'Pemeriksaan awal program MHLS. Keluhan utama sering lemas dan gula darah puasa tinggi.',
                'examination_summary' => 'Toxin assessment menunjukkan level 2 (11 indikator). Ketosis belum tercapai.',
                'recorded_at' => '2026-10-10 09:30:00',
            ]
        );

        // Toxin Assessment 1 (11 indicators -> Level 2)
        $indicatedIdsSession1 = [1, 2, 3, 7, 8, 14, 15, 17, 20, 33, 35];
        $total1 = count($indicatedIdsSession1);
        $toxinResult1 = ToxinAssessmentResult::firstOrCreate(
            ['session_id' => $session1->id],
            [
                'patient_id' => $patient->id,
                'total_indicated' => $total1,
                'level' => ToxinAssessmentResult::calculateLevel($total1),
                'assessment_date' => '2026-10-10',
                'notes' => 'Indikasi pencernaan dan metabolisme dominan',
            ]
        );

        foreach (ToxinIndicators::all() as $num => $name) {
            ToxinAssessmentAnswer::firstOrCreate(
                ['result_id' => $toxinResult1->id, 'indicator_number' => $num],
                [
                    'indicator_name' => $name,
                    'is_indicated' => in_array($num, $indicatedIdsSession1),
                    'created_at' => now(),
                ]
            );
        }

        BodyMeasurement::firstOrCreate(
            ['session_id' => $session1->id],
            [
                'patient_id' => $patient->id,
                'weight' => 84.5,
                'bmi' => 28.2,
                'waist_circumference' => 96.0,
                'body_fat' => 31.5,
                'water' => 48.2,
                'muscle_mass' => 54.8,
                'physical_rating' => 3,
                'cell_age' => 46,
                'bone_mass' => 2.8,
                'visceral_fat' => 12.0,
                'body_temperature' => 36.6,
            ]
        );

        BloodMeasurement::firstOrCreate(
            ['session_id' => $session1->id],
            [
                'patient_id' => $patient->id,
                'blood_pressure' => '135/85',
                'heart_rate' => 78,
                'hemoglobin' => 14.5,
                'glucose' => 145.0,
                'cholesterol' => 220.0,
                'uric_acid' => 7.2,
                'ketone' => 0.2,
            ]
        );

        // Session 2: Follow-up progress
        $session2 = PatientProgressSession::firstOrCreate(
            ['patient_id' => $patient->id, 'session_number' => 'SESI-02-20261105'],
            [
                'session_date' => '2026-11-05',
                'staff_notes' => 'Evaluasi setelah 3 minggu menerapkan pola istirahat dan real food.',
                'examination_summary' => 'Penurunan indikator toksin menjadi 5 indikator (Level 1). Glukosa membaik.',
                'recorded_at' => '2026-11-05 10:15:00',
            ]
        );

        $indicatedIdsSession2 = [2, 7, 17, 33, 35];
        $total2 = count($indicatedIdsSession2);
        $toxinResult2 = ToxinAssessmentResult::firstOrCreate(
            ['session_id' => $session2->id],
            [
                'patient_id' => $patient->id,
                'total_indicated' => $total2,
                'level' => ToxinAssessmentResult::calculateLevel($total2),
                'assessment_date' => '2026-11-05',
                'notes' => 'Penurunan signifikan pada keluhan pencernaan',
            ]
        );

        foreach (ToxinIndicators::all() as $num => $name) {
            ToxinAssessmentAnswer::firstOrCreate(
                ['result_id' => $toxinResult2->id, 'indicator_number' => $num],
                [
                    'indicator_name' => $name,
                    'is_indicated' => in_array($num, $indicatedIdsSession2),
                    'created_at' => now(),
                ]
            );
        }

        BodyMeasurement::firstOrCreate(
            ['session_id' => $session2->id],
            [
                'patient_id' => $patient->id,
                'weight' => 81.2,
                'bmi' => 27.1,
                'waist_circumference' => 92.5,
                'body_fat' => 29.8,
                'water' => 50.1,
                'muscle_mass' => 54.3,
                'physical_rating' => 4,
                'cell_age' => 43,
                'bone_mass' => 2.8,
                'visceral_fat' => 10.5,
                'body_temperature' => 36.5,
            ]
        );

        BloodMeasurement::firstOrCreate(
            ['session_id' => $session2->id],
            [
                'patient_id' => $patient->id,
                'blood_pressure' => '125/80',
                'heart_rate' => 74,
                'hemoglobin' => 14.8,
                'glucose' => 110.0,
                'cholesterol' => 195.0,
                'uric_acid' => 6.4,
                'ketone' => 0.8,
            ]
        );
    }
}
