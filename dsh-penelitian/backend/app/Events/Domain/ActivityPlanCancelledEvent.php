<?php

namespace App\Events\Domain;

use App\Models\ActivityPlan;

class ActivityPlanCancelledEvent implements DomainEventInterface
{
    public function __construct(
        public ActivityPlan $plan,
        public string $reason = 'Cancelled by staff',
        public ?string $correlationId = null
    ) {}

    public function getEventType(): string
    {
        return 'ActivityPlanCancelled';
    }

    public function getAggregateType(): string
    {
        return 'ActivityPlan';
    }

    public function getAggregateId(): string
    {
        return (string) $this->plan->id;
    }

    public function getCorrelationId(): ?string
    {
        return $this->correlationId;
    }

    public function getPayload(): array
    {
        return [
            'plan_id' => $this->plan->id,
            'patient_id' => $this->plan->patient_id,
            'reason' => $this->reason,
            'cancelled_at' => now()->toIso8601String(),
        ];
    }
}

