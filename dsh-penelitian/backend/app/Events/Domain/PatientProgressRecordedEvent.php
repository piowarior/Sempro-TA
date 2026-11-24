<?php

namespace App\Events\Domain;

use App\Models\PatientProgressSession;

class PatientProgressRecordedEvent implements DomainEventInterface
{
    public function __construct(
        public PatientProgressSession $session,
        public ?string $correlationId = null
    ) {}

    public function getEventType(): string
    {
        return 'PatientProgressRecorded';
    }

    public function getAggregateType(): string
    {
        return 'PatientProgressSession';
    }

    public function getAggregateId(): string
    {
        return (string) $this->session->id;
    }

    public function getCorrelationId(): ?string
    {
        return $this->correlationId;
    }

    public function getPayload(): array
    {
        return [
            'session_id' => $this->session->id,
            'patient_id' => $this->session->patient_id,
            'session_number' => $this->session->session_number,
            'session_date' => $this->session->session_date->format('Y-m-d'),
            'toxin_level' => $this->session->toxinAssessment?->level,
            'total_toxin_indicated' => $this->session->toxinAssessment?->total_indicated,
            'weight' => $this->session->bodyMeasurement?->weight,
            'glucose' => $this->session->bloodMeasurement?->glucose,
            'blood_pressure' => $this->session->bloodMeasurement?->blood_pressure,
            'recorded_at' => $this->session->recorded_at->toIso8601String(),
        ];
    }
}

