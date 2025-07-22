<?php

namespace App\Domain\Events;

use DateTimeImmutable;

interface DomainEventInterface
{
    public function getAggregateId(): int;
    
    public function getOccurredAt(): DateTimeImmutable;
    
    public function getEventName(): string;
    
    public function getEventData(): array;
} 