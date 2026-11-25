<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Modul 2: Visits / Appointments (Booking Kunjungan & 12 Pertemuan MHLS)
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->unsignedTinyInteger('meeting_number')->nullable()->comment('Pertemuan ke 1-12 untuk MHLS');
            $table->date('visit_date');
            $table->string('time_slot', 50)->default('09:00 - 11:00');
            $table->enum('booking_type', ['first_visit', 'mhls_followup', 'general_checkup'])->default('mhls_followup');
            $table->enum('status', ['pending_confirmation', 'confirmed', 'completed', 'cancelled'])->default('confirmed');
            $table->enum('requested_by', ['clinic', 'patient_app'])->default('clinic');
            $table->text('staff_notes')->nullable();
            $table->text('patient_notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'visit_date']);
            $table->index(['status', 'visit_date']);
        });

        // 2. Modul 3: Enhance patient_progress_sessions with visit link and activity evaluation
        Schema::table('patient_progress_sessions', function (Blueprint $table) {
            $table->foreignId('visit_id')->nullable()->after('patient_id')->constrained('visits')->nullOnDelete();
            $table->unsignedTinyInteger('meeting_number')->nullable()->after('session_number')->comment('Pertemuan ke-1 s/d 12 MHLS');
            $table->text('activity_evaluation_notes')->nullable()->after('examination_summary')->comment('Evaluasi/kuis aktivitas mandiri periode sebelumnya saat pasien datang kembali');
        });

        // 3. Modul 3: Enhance blood_measurements with GKI and metabolic parameters
        Schema::table('blood_measurements', function (Blueprint $table) {
            $table->decimal('gki', 6, 2)->nullable()->after('ketone')->comment('Glucose Ketone Index = (Glucose / 18) / Ketone');
            $table->unsignedSmallInteger('systolic')->nullable()->after('blood_pressure');
            $table->unsignedSmallInteger('diastolic')->nullable()->after('systolic');
            $table->text('metabolic_notes')->nullable()->after('gki')->comment('Catatan parameter metabolik khusus DSH');
        });
    }

    public function down(): void
    {
        Schema::table('blood_measurements', function (Blueprint $table) {
            $table->dropColumn(['gki', 'systolic', 'diastolic', 'metabolic_notes']);
        });

        Schema::table('patient_progress_sessions', function (Blueprint $table) {
            $table->dropForeign(['visit_id']);
            $table->dropColumn(['visit_id', 'meeting_number', 'activity_evaluation_notes']);
        });

        Schema::dropIfExists('visits');
    }
};
