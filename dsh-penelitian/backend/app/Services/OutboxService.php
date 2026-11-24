<?php

namespace App\Services;

use App\Models\OutboxEvent;
use Illuminate\Support\Str;

class OutboxService
{
    /**
     * Store a domain event into the transactional outbox table.
     * This method must be called within an active DB transaction.
     */
    public function recordEvent(
        string $eventType,
        string $aggregateType,
        string $aggregateId,
        array $payload,
        ?string $correlationId = null,
        int $version = 1
    ): OutboxEvent {
        return OutboxEvent::create([
            'event_id' => (string) Str::uuid(),
            'event_type' => $eventType,
            'aggregate_type' => $aggregateType,
            'aggregate_id' => $aggregateId,
            'payload' => $payload,
            'correlation_id' => $correlationId ?: (string) Str::uuid(),
            'event_version' => $version,
            'status' => 'pending',
            'retry_count' => 0,
            'occurred_at' => now(),
        ]);
    }
}

