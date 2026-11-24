<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbox_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id')->unique();
            $table->string('event_type', 100)->index();
            $table->string('aggregate_type', 100)->index();
            $table->string('aggregate_id', 100)->index();
            $table->json('payload');
            $table->uuid('correlation_id')->index();
            $table->integer('event_version')->default(1);
            $table->enum('status', ['pending', 'published', 'failed'])->default('pending')->index();
            $table->integer('retry_count')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbox_events');
    }
};

