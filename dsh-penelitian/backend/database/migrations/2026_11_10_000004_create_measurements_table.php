<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('body_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->unique()->constrained('patient_progress_sessions')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            
            // Optional body parameters - nullable so unmeasured values are NULL, not 0
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('bmi', 4, 2)->nullable();
            $table->decimal('waist_circumference', 5, 2)->nullable();
            $table->decimal('body_fat', 4, 2)->nullable();
            $table->decimal('water', 4, 2)->nullable();
            $table->decimal('muscle_mass', 5, 2)->nullable();
            $table->integer('physical_rating')->nullable();
            $table->integer('cell_age')->nullable();
            $table->decimal('bone_mass', 4, 2)->nullable();
            $table->decimal('visceral_fat', 4, 2)->nullable();
            $table->decimal('body_temperature', 4, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('blood_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->unique()->constrained('patient_progress_sessions')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            
            // Optional blood & ketosis parameters - nullable
            $table->string('blood_pressure', 20)->nullable();
            $table->integer('heart_rate')->nullable();
            $table->decimal('hemoglobin', 4, 2)->nullable();
            $table->decimal('glucose', 5, 2)->nullable();
            $table->decimal('cholesterol', 5, 2)->nullable();
            $table->decimal('uric_acid', 4, 2)->nullable();
            $table->decimal('ketone', 4, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_measurements');
        Schema::dropIfExists('body_measurements');
    }
};

