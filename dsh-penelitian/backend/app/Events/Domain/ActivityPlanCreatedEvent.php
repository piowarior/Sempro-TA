<?php

namespace App\Events\Domain;

use App\Models\ActivityPlan;

class ActivityPlanCreatedEvent implements DomainEventInterface
{
    public function __construct(
        public ActivityPlan $plan,
        public ?string $correlationId = null
    ) {}

    public function getEventType(): string
    {
        return 'ActivityPlanCreated';
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
            'plan_id' => (int) $this->plan->id,
            'patient_id' => (int) $this->plan->patient_id,
            'patient_name' => $this->plan->patient->name ?? null,
            'patient_phone' => $this->plan->patient->phone_number ?? null,
            'name' => (string) $this->plan->name,
            'description' => (string) $this->plan->description,
            'start_date' => $this->plan->start_date->format('Y-m-d'),
            'end_date' => $this->plan->end_date->format('Y-m-d'),
            'frequency_type' => $this->plan->frequency_type,
            'schedule_days' => $this->plan->schedule_days,
            'reminder_time' => $this->plan->reminder_time,
            'schedules' => $this->plan->schedules->map(fn($s) => [
                'id' => (int) $s->id,
                'scheduled_date' => $s->scheduled_date->format('Y-m-d'),
                'reminder_time' => (string) $s->reminder_time,
            ])->toArray(),
        ];
    }
}

