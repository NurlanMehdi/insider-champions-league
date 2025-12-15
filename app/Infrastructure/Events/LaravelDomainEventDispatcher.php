<?php

namespace App\Infrastructure\Events;

use App\Domain\Events\DomainEventDispatcherInterface;
use App\Domain\Events\DomainEventInterface;
use Illuminate\Events\Dispatcher;

final class LaravelDomainEventDispatcher implements DomainEventDispatcherInterface
{
    private Dispatcher $eventDispatcher;

    public function __construct(Dispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function dispatch(DomainEventInterface $event): void
    {
        $this->eventDispatcher->dispatch($event->getEventName(), $event);
    }

    public function dispatchAll(array $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }
} 