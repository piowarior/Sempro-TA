<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('patient_number', 50)->unique();
            $table->string('name', 100);
            $table->date('birth_date');
            $table->enum('gender', ['L', 'P']);
            $table->string('phone_number', 25)->nullable();
            $table->text('address')->nullable();
            $table->enum('patient_type', ['umum', 'mhls'])->default('umum');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};

