<?php

namespace App\Events\Domain;

use App\Models\PatientActivityReport;

class PatientActivityReportedEvent implements DomainEventInterface
{
    public function __construct(
        public PatientActivityReport $report,
        public ?string $correlationId = null
    ) {}

    public function getEventType(): string
    {
        return 'PatientActivityReported';
    }

    public function getAggregateType(): string
    {
        return 'PatientActivityReport';
    }

    public function getAggregateId(): string
    {
        return (string) $this->report->id;
    }

    public function getCorrelationId(): ?string
    {
        return $this->correlationId;
    }

    public function getPayload(): array
    {
        return [
            'report_id' => (int) $this->report->id,
            'plan_id' => (int) $this->report->activity_plan_id,
            'schedule_id' => $this->report->activity_schedule_id ? (int) $this->report->activity_schedule_id : null,
            'patient_id' => (int) $this->report->patient_id,
            'activity_date' => $this->report->activity_date->format('Y-m-d'),
            'execution_status' => (string) $this->report->execution_status,
            'patient_notes' => (string) ($this->report->patient_notes ?? ''),
            'report_time' => $this->report->report_time->toIso8601String(),
        ];
    }
}

