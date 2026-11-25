<?php

namespace Database\Seeders;

use App\Models\BloodMeasurement;
use App\Models\BodyMeasurement;
use App\Models\Patient;
use App\Models\PatientProgressSession;
use App\Models\ToxinAssessmentAnswer;
use App\Models\ToxinAssessmentResult;
use App\Models\User;
use App\Models\Visit;
use App\Support\ToxinIndicators;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin Klinik
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Petugas Klinik DSH',
                'email' => 'admin@dsh.clinic',
                'role' => 'admin',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Pasien 1: Budi Santoso (Program MHLS - Telah menyelesaikan 6 Sesi Pertemuan)
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

        // 3. Pasien 2: Hendra Wijaya (Program MHLS - Pasien Baru, Baru Sesi 1)
        $userHendra = User::firstOrCreate(
            ['username' => 'hendra'],
            [
                'name' => 'Hendra Wijaya',
                'email' => 'hendra@example.com',
                'role' => 'patient',
                'password' => Hash::make('pasien123'),
            ]
        );

        $patientHendra = Patient::firstOrCreate(
            ['patient_number' => 'DSH-MHLS-002'],
            [
                'user_id' => $userHendra->id,
                'name' => 'Hendra Wijaya',
                'birth_date' => '1975-04-10',
                'gender' => 'L',
                'phone_number' => '081298765432',
                'address' => 'Jl. Ahmad Yani No. 44, Serang',
                'patient_type' => 'mhls',
                'status' => 'active',
            ]
        );

        // 4. Pasien 3: Siti Rahayu (Pasien Umum / Kunjungan Berkala Acak)
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
                'address' => 'Jl. Melati No. 5, Cilegon',
                'patient_type' => 'umum',
                'status' => 'active',
            ]
        );

        // Seed Sesi Pemeriksaan Budi (6 Pertemuan Bertahap Berdasarkan data.md)
        $this->seedBudiSixSessions($patientBudi);

        // Seed Sesi Pemeriksaan Hendra (Baru 1 Pertemuan Baseline)
        $this->seedHendraOneSession($patientHendra);

        // Seed Jadwal Kunjungan Acak Siti (Ada yang terkonfirmasi, belum diisi pemeriksaan, dan jadwal masa depan)
        $this->seedSitiRandomVisits($patientSiti);
    }

    private function seedBudiSixSessions(Patient $patient): void
    {
        // 6 Pertemuan Nyata Terinspirasi data.md (Nilai Glukosa, Keton, GKI, Toksin, dan BB bertahap membaik)
        $sessionsData = [
            [
                'meeting' => 1,
                'date' => Carbon::now()->subWeeks(5)->format('Y-m-d'),
                'notes' => 'Pertemuan awal MHLS. Toksin tinggi (Level 3), ketosis belum tercapai.',
                'summary' => 'Baseline program. Keluhan sering lemas, berat berlebih, dan tensi tinggi.',
                'eval_activity' => 'Edukasi pengenalan real food, hidrasi minimal 2.5 liter, dan tidur sebelum jam 22.00.',
                'toxin_count' => 18,
                'weight' => 72.6,
                'waist' => 100.0,
                'bmi' => 28.71,
                'body_fat' => 26.4,
                'water' => 53.9,
                'muscle' => 50.6,
                'visceral' => 15.0,
                'temp' => 36.4,
                'bp' => '138/88',
                'systolic' => 138,
                'diastolic' => 88,
                'hr' => 88,
                'glucose' => 101.0,
                'ketone' => 1.78, // GKI = (101/18)/1.78 = 3.15
                'cholesterol' => 212.0,
                'uric_acid' => 7.4,
            ],
            [
                'meeting' => 2,
                'date' => Carbon::now()->subWeeks(4)->format('Y-m-d'),
                'notes' => 'Pertemuan ke-2. Mulai puasa teratur 16 jam.',
                'summary' => 'Penurunan keluhan ngemil dan sakit kepala.',
                'eval_activity' => 'Kepatuhan puasa 85%. Disarankan memperbanyak jalan kaki pagi hari.',
                'toxin_count' => 14,
                'weight' => 71.8,
                'waist' => 98.0,
                'bmi' => 28.39,
                'body_fat' => 25.8,
                'water' => 54.2,
                'muscle' => 50.8,
                'visceral' => 14.0,
                'temp' => 36.5,
                'bp' => '130/84',
                'systolic' => 130,
                'diastolic' => 84,
                'hr' => 82,
                'glucose' => 88.0,
                'ketone' => 0.75, // GKI = (88/18)/0.75 = 6.51
                'cholesterol' => 205.0,
                'uric_acid' => 7.0,
            ],
            [
                'meeting' => 3,
                'date' => Carbon::now()->subWeeks(3)->format('Y-m-d'),
                'notes' => 'Pertemuan ke-3. Adaptasi ketosis ringan berjalan.',
                'summary' => 'Kualitas tidur membaik. Tidak lagi sering terbangun malam hari.',
                'eval_activity' => 'Pasien rutin berjemur pagi dan menghindari makanan berminyak.',
                'toxin_count' => 11,
                'weight' => 70.9,
                'waist' => 96.0,
                'bmi' => 28.04,
                'body_fat' => 25.0,
                'water' => 55.0,
                'muscle' => 51.0,
                'visceral' => 13.0,
                'temp' => 36.5,
                'bp' => '126/80',
                'systolic' => 126,
                'diastolic' => 80,
                'hr' => 78,
                'glucose' => 98.0,
                'ketone' => 1.44, // GKI = (98/18)/1.44 = 3.78
                'cholesterol' => 198.0,
                'uric_acid' => 6.7,
            ],
            [
                'meeting' => 4,
                'date' => Carbon::now()->subWeeks(2)->format('Y-m-d'),
                'notes' => 'Pertemuan ke-4. Indikator toksin masuk Level 2 aman.',
                'summary' => 'Lingkar perut berkurang 6 cm dari awal. Energi stabil sepanjang hari.',
                'eval_activity' => 'Aktivitas fisik ditingkatkan dengan senam peregangan mandiri.',
                'toxin_count' => 8,
                'weight' => 70.0,
                'waist' => 94.0,
                'bmi' => 27.68,
                'body_fat' => 24.2,
                'water' => 55.5,
                'muscle' => 51.2,
                'visceral' => 12.0,
                'temp' => 36.5,
                'bp' => '122/78',
                'systolic' => 122,
                'diastolic' => 78,
                'hr' => 76,
                'glucose' => 91.0,
                'ketone' => 1.61, // GKI = (91/18)/1.61 = 3.14
                'cholesterol' => 190.0,
                'uric_acid' => 6.3,
            ],
            [
                'meeting' => 5,
                'date' => Carbon::now()->subDays(8)->format('Y-m-d'),
                'notes' => 'Pertemuan ke-5. Zona Terapeutik Ketosis Tinggi tercapai.',
                'summary' => 'GKI mencapai 1.25. Tanda inflamasi tubuh mereda drastis.',
                'eval_activity' => 'Disiplin puasa sangat baik. Laporan aplikasi mobile 95% tepat waktu.',
                'toxin_count' => 5,
                'weight' => 68.9,
                'waist' => 91.5,
                'bmi' => 27.25,
                'body_fat' => 23.5,
                'water' => 56.1,
                'muscle' => 51.5,
                'visceral' => 11.0,
                'temp' => 36.6,
                'bp' => '118/74',
                'systolic' => 118,
                'diastolic' => 74,
                'hr' => 74,
                'glucose' => 73.0,
                'ketone' => 3.25, // GKI = (73/18)/3.25 = 1.25
                'cholesterol' => 184.0,
                'uric_acid' => 5.9,
            ],
            [
                'meeting' => 6,
                'date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'notes' => 'Pertemuan ke-6 (Evaluasi Tengah Program 3 Bulan).',
                'summary' => 'Beban toksin Level 1 (3 indikator). Glukosa stabil 62 mg/dL, Keton 2.53 mmol/L (GKI 1.36).',
                'eval_activity' => 'Melanjutkan program fase pemantapan menuju pertemuan ke-7.',
                'toxin_count' => 3,
                'weight' => 68.1,
                'waist' => 89.0, // Masuk batas normal pria < 90 cm!
                'bmi' => 26.93,
                'body_fat' => 22.8,
                'water' => 56.8,
                'muscle' => 51.8,
                'visceral' => 10.0,
                'temp' => 36.6,
                'bp' => '115/72',
                'systolic' => 115,
                'diastolic' => 72,
                'hr' => 72,
                'glucose' => 62.0,
                'ketone' => 2.53, // GKI = (62/18)/2.53 = 1.36
                'cholesterol' => 178.0,
                'uric_acid' => 5.6,
            ],
        ];

        foreach ($sessionsData as $idx => $s) {
            // Tautkan dengan data Kunjungan resmi
            $visit = Visit::firstOrCreate(
                [
                    'patient_id' => $patient->id,
                    'meeting_number' => $s['meeting'],
                ],
                [
                    'visit_date' => $s['date'],
                    'time_slot' => '09:00 - 10:30',
                    'booking_type' => 'mhls_followup',
                    'status' => 'completed',
                    'requested_by' => 'clinic',
                    'staff_notes' => 'Sesi pemeriksaan pertemuan #' . $s['meeting'],
                    'completed_at' => Carbon::parse($s['date'] . ' 10:30:00'),
                ]
            );

            // Buat sesi rekam klinis
            $sessionNumber = 'SESI-' . str_pad($s['meeting'], 2, '0', STR_PAD_LEFT) . '-' . str_replace('-', '', $s['date']);
            $session = PatientProgressSession::firstOrCreate(
                [
                    'patient_id' => $patient->id,
                    'session_number' => $sessionNumber,
                ],
                [
                    'visit_id' => $visit->id,
                    'meeting_number' => $s['meeting'],
                    'session_date' => $s['date'],
                    'staff_notes' => $s['notes'],
                    'examination_summary' => $s['summary'],
                    'activity_evaluation_notes' => $s['eval_activity'],
                    'recorded_at' => Carbon::parse($s['date'] . ' 09:30:00'),
                ]
            );

            // Toksin Assessment
            $toxinResult = ToxinAssessmentResult::firstOrCreate(
                ['session_id' => $session->id],
                [
                    'patient_id' => $patient->id,
                    'total_indicated' => $s['toxin_count'],
                    'level' => ToxinAssessmentResult::calculateLevel($s['toxin_count']),
                    'assessment_date' => $s['date'],
                    'notes' => 'Evaluasi keluhan pertemuan #' . $s['meeting'],
                ]
            );

            // Jawaban 50 Indikator Toksin
            $allInd = ToxinIndicators::all();
            $indicatedKeys = array_slice(array_keys($allInd), 0, $s['toxin_count']);
            foreach ($allInd as $code => $name) {
                ToxinAssessmentAnswer::firstOrCreate(
                    ['result_id' => $toxinResult->id, 'indicator_number' => $code],
                    [
                        'indicator_name' => $name,
                        'is_indicated' => in_array($code, $indicatedKeys),
                    ]
                );
            }

            // Antropometri Tubuh
            BodyMeasurement::firstOrCreate(
                ['session_id' => $session->id],
                [
                    'patient_id' => $patient->id,
                    'weight' => $s['weight'],
                    'bmi' => $s['bmi'],
                    'waist_circumference' => $s['waist'],
                    'body_fat' => $s['body_fat'],
                    'water' => $s['water'],
                    'muscle_mass' => $s['muscle'],
                    'physical_rating' => 3,
                    'cell_age' => 45 - $idx,
                    'bone_mass' => 2.8,
                    'visceral_fat' => $s['visceral'],
                    'body_temperature' => $s['temp'],
                ]
            );

            // Darah, Vital & GKI
            $computedGki = round(($s['glucose'] / 18.0) / $s['ketone'], 2);
            BloodMeasurement::firstOrCreate(
                ['session_id' => $session->id],
                [
                    'patient_id' => $patient->id,
                    'blood_pressure' => $s['bp'],
                    'systolic' => $s['systolic'],
                    'diastolic' => $s['diastolic'],
                    'heart_rate' => $s['hr'],
                    'hemoglobin' => 15.2,
                    'glucose' => $s['glucose'],
                    'cholesterol' => $s['cholesterol'],
                    'uric_acid' => $s['uric_acid'],
                    'ketone' => $s['ketone'],
                    'gki' => $computedGki,
                    'metabolic_notes' => 'Zona Ketosis Terapeutik Terpantau Stabil',
                ]
            );
        }

        // Buat jadwal kunjungan pertemuan #7 Budi yang akan datang (Status: Terkonfirmasi)
        Visit::firstOrCreate(
            [
                'patient_id' => $patient->id,
                'meeting_number' => 7,
            ],
            [
                'visit_date' => Carbon::now()->addDays(6)->format('Y-m-d'),
                'time_slot' => '09:00 - 10:30',
                'booking_type' => 'mhls_followup',
                'status' => 'confirmed',
                'requested_by' => 'patient_app',
                'staff_notes' => 'Jadwal pertemuan ke-7 diajukan pasien via mobile app.',
                'confirmed_at' => Carbon::now()->subDay(),
            ]
        );
    }

    private function seedHendraOneSession(Patient $patient): void
    {
        // 1 Jadwal Kunjungan & Sesi Baseline Hendra
        $visit = Visit::firstOrCreate(
            [
                'patient_id' => $patient->id,
                'meeting_number' => 1,
            ],
            [
                'visit_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'time_slot' => '10:30 - 12:00',
                'booking_type' => 'first_visit',
                'status' => 'completed',
                'requested_by' => 'clinic',
                'staff_notes' => 'Kunjungan pertama evaluasi awal MHLS.',
                'completed_at' => Carbon::now()->subDays(3)->addHours(2),
            ]
        );

        $session = PatientProgressSession::firstOrCreate(
            [
                'patient_id' => $patient->id,
                'session_number' => 'SESI-01-' . date('Ymd', strtotime('-3 days')),
            ],
            [
                'visit_id' => $visit->id,
                'meeting_number' => 1,
                'session_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'staff_notes' => 'Pemeriksaan baseline awal. Pasien mengeluh sering pusing, tengkuk kaku, dan asam urat tinggi.',
                'examination_summary' => 'Toksin tinggi Level 3 (20 keluhan). Rekomendasi detoksifikasi awal.',
                'activity_evaluation_notes' => 'Mulai pembatasan karbohidrat sederhana dan perbanyak minum air hangat.',
                'recorded_at' => Carbon::now()->subDays(3)->format('Y-m-d 11:00:00'),
            ]
        );

        $toxinResult = ToxinAssessmentResult::firstOrCreate(
            ['session_id' => $session->id],
            [
                'patient_id' => $patient->id,
                'total_indicated' => 20,
                'level' => 'Level 3',
                'assessment_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'notes' => 'Toksin tinggi menumpuk',
            ]
        );

        $allInd = ToxinIndicators::all();
        $indicatedKeys = array_slice(array_keys($allInd), 0, 20);
        foreach ($allInd as $code => $name) {
            ToxinAssessmentAnswer::firstOrCreate(
                ['result_id' => $toxinResult->id, 'indicator_number' => $code],
                [
                    'indicator_name' => $name,
                    'is_indicated' => in_array($code, $indicatedKeys),
                ]
            );
        }

        BodyMeasurement::firstOrCreate(
            ['session_id' => $session->id],
            [
                'patient_id' => $patient->id,
                'weight' => 88.5,
                'bmi' => 30.2,
                'waist_circumference' => 104.0,
                'body_fat' => 33.0,
                'water' => 47.0,
                'muscle_mass' => 52.0,
                'physical_rating' => 2,
                'cell_age' => 56,
                'bone_mass' => 3.1,
                'visceral_fat' => 16.0,
                'body_temperature' => 36.7,
            ]
        );

        BloodMeasurement::firstOrCreate(
            ['session_id' => $session->id],
            [
                'patient_id' => $patient->id,
                'blood_pressure' => '145/92',
                'systolic' => 145,
                'diastolic' => 92,
                'heart_rate' => 86,
                'hemoglobin' => 14.1,
                'glucose' => 165.0,
                'cholesterol' => 245.0,
                'uric_acid' => 8.5,
                'ketone' => 0.1,
                'gki' => 91.67,
                'metabolic_notes' => 'Pra-Ketosis / Sindrom Metabolik',
            ]
        );

        // Kunjungan Hendra pertemuan #2 yang belum diisi hasil periksanya (Status: Terkonfirmasi)
        Visit::firstOrCreate(
            [
                'patient_id' => $patient->id,
                'meeting_number' => 2,
            ],
            [
                'visit_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
                'time_slot' => '10:30 - 12:00',
                'booking_type' => 'mhls_followup',
                'status' => 'confirmed',
                'requested_by' => 'clinic',
                'staff_notes' => 'Jadwal kontrol pertemuan ke-2 evaluasi penurunan toksin.',
                'confirmed_at' => Carbon::now(),
            ]
        );
    }

    private function seedSitiRandomVisits(Patient $patient): void
    {
        // 1 Kunjungan Selesai (General Checkup Masa Lalu)
        $visitOld = Visit::firstOrCreate(
            [
                'patient_id' => $patient->id,
                'visit_date' => Carbon::now()->subWeeks(2)->format('Y-m-d'),
            ],
            [
                'meeting_number' => null,
                'time_slot' => '13:00 - 14:30',
                'booking_type' => 'general_checkup',
                'status' => 'completed',
                'requested_by' => 'clinic',
                'staff_notes' => 'Pemeriksaan rutin gula darah dan kolesterol tahunan.',
                'completed_at' => Carbon::now()->subWeeks(2)->addHours(2),
            ]
        );

        // Sesi pemeriksaan terikat
        PatientProgressSession::firstOrCreate(
            [
                'patient_id' => $patient->id,
                'session_number' => 'SESI-UMUM-01-' . date('Ymd', strtotime('-14 days')),
            ],
            [
                'visit_id' => $visitOld->id,
                'meeting_number' => null,
                'session_date' => Carbon::now()->subWeeks(2)->format('Y-m-d'),
                'staff_notes' => 'Cek kesehatan umum tanpa program puasa.',
                'examination_summary' => 'Kondisi kesehatan stabil.',
                'recorded_at' => Carbon::now()->subWeeks(2)->format('Y-m-d 13:30:00'),
            ]
        );

        // 2. Kunjungan yang SUDAH TERJADWAL tapi BELUM DICATAT/BELUM DIISI Pemeriksaannya (Menunggu di Poli)
        Visit::firstOrCreate(
            [
                'patient_id' => $patient->id,
                'visit_date' => Carbon::now()->format('Y-m-d'),
            ],
            [
                'meeting_number' => null,
                'time_slot' => '13:30 - 15:00',
                'booking_type' => 'general_checkup',
                'status' => 'confirmed',
                'requested_by' => 'clinic',
                'staff_notes' => 'Pasien telah datang di klinik, menunggu giliran pemeriksaan fisik dan lab.',
                'confirmed_at' => Carbon::now()->subHours(2),
            ]
        );

        // 3. Kunjungan yang masih butuh konfirmasi petugas klinik (Pengajuan dari aplikasi)
        Visit::firstOrCreate(
            [
                'patient_id' => $patient->id,
                'visit_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            ],
            [
                'meeting_number' => null,
                'time_slot' => '14:00 - 15:30',
                'booking_type' => 'general_checkup',
                'status' => 'pending_confirmation',
                'requested_by' => 'patient_app',
                'staff_notes' => 'Pengajuan booking baru melalui aplikasi.',
            ]
        );
    }
}
