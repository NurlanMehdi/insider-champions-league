<?php

namespace App\Domain\Services\Interfaces;

interface FixtureGeneratorInterface
{
    public function generateRoundRobin(array $teams): array;
} 