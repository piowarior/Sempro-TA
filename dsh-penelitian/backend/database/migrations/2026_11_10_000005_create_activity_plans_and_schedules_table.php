<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('patient_progress_sessions')->nullOnDelete();
            $table->string('name', 255);
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->json('schedule_days')->nullable(); // e.g. ["Mon", "Thu"] or ["daily"]
            $table->string('frequency_type', 50)->default('daily'); // 'daily', 'custom_days', 'weekly'
            $table->time('reminder_time');
            $table->text('additional_notes')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled', 'updated'])->default('active');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('activity_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_plan_id')->constrained('activity_plans')->cascadeOnDelete();
            $table->date('scheduled_date');
            $table->time('reminder_time');
            $table->enum('status', ['pending', 'reminder_sent', 'reported', 'overdue', 'cancelled'])->default('pending');
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();

            $table->index(['activity_plan_id', 'scheduled_date']);
        });

        Schema::create('patient_activity_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_plan_id')->constrained('activity_plans')->cascadeOnDelete();
            $table->foreignId('activity_schedule_id')->nullable()->constrained('activity_schedules')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->date('activity_date');
            $table->timestamp('report_time')->useCurrent();
            $table->enum('execution_status', ['dilaksanakan', 'tidak_dilaksanakan']);
            $table->text('patient_notes')->nullable();
            $table->timestamps();

            $table->index(['activity_plan_id', 'activity_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_activity_reports');
        Schema::dropIfExists('activity_schedules');
        Schema::dropIfExists('activity_plans');
    }
};

