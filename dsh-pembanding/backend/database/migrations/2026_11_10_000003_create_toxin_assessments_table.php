<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toxin_assessment_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->unique()->constrained('patient_progress_sessions')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->integer('total_indicated')->default(0);
            $table->enum('level', ['Level 1', 'Level 2', 'Level 3'])->default('Level 1');
            $table->date('assessment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('toxin_assessment_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained('toxin_assessment_results')->cascadeOnDelete();
            $table->integer('indicator_number');
            $table->string('indicator_name', 255);
            $table->boolean('is_indicated')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['result_id', 'indicator_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toxin_assessment_answers');
        Schema::dropIfExists('toxin_assessment_results');
    }
};

