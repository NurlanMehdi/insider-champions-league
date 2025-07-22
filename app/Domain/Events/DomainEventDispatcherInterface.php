<?php

namespace App\Domain\Events;

interface DomainEventDispatcherInterface
{
    public function dispatch(DomainEventInterface $event): void;
    
    public function dispatchAll(array $events): void;
} 