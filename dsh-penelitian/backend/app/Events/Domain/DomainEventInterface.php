<?php

namespace App\Events\Domain;

interface DomainEventInterface
{
    public function getEventType(): string;
    public function getAggregateType(): string;
    public function getAggregateId(): string;
    public function getPayload(): array;
    public function getCorrelationId(): ?string;
}

