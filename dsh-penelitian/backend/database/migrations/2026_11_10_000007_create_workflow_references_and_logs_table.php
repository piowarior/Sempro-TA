<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_plan_id')->constrained('activity_plans')->cascadeOnDelete();
            $table->string('workflow_id', 100)->unique();
            $table->string('run_id', 100)->nullable();
            $table->string('workflow_type', 100)->default('ActivityPlanWorkflow');
            $table->enum('status', ['running', 'completed', 'failed', 'terminated', 'cancelled'])->default('running');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamps();

            $table->index(['activity_plan_id', 'status']);
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_plan_id')->nullable()->constrained('activity_plans')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('notification_type', 50)->default('reminder');
            $table->string('recipient', 100);
            $table->text('message');
            $table->timestamp('sent_at')->useCurrent();
            $table->enum('delivery_status', ['success', 'failed'])->default('success');
            $table->text('provider_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('workflow_references');
    }
};

