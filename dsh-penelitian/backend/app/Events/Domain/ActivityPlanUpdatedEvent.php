<?php

namespace App\Events\Domain;

use App\Models\ActivityPlan;

class ActivityPlanUpdatedEvent implements DomainEventInterface
{
    public function __construct(
        public ActivityPlan $plan,
        public ?string $correlationId = null
    ) {}

    public function getEventType(): string
    {
        return 'ActivityPlanUpdated';
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
            'name' => $this->plan->name,
            'description' => $this->plan->description,
            'start_date' => $this->plan->start_date->format('Y-m-d'),
            'end_date' => $this->plan->end_date->format('Y-m-d'),
            'reminder_time' => $this->plan->reminder_time,
            'schedules' => $this->plan->schedules->map(fn($s) => [
                'id' => $s->id,
                'scheduled_date' => $s->scheduled_date->format('Y-m-d'),
                'reminder_time' => $s->reminder_time,
                'status' => $s->status,
            ])->toArray(),
        ];
    }
}

