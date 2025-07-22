<?php

namespace App\Domain\Common;

use App\Domain\Events\DomainEventInterface;

abstract class AggregateRoot
{
    private array $domainEvents = [];

    final protected function recordDomainEvent(DomainEventInterface $event): void
    {
        $this->domainEvents[] = $event;
    }

    final public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    final public function clearEvents(): void
    {
        $this->domainEvents = [];
    }

    final public function getUncommittedEvents(): array
    {
        return $this->domainEvents;
    }
} 