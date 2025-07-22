<?php

namespace App\Domain\Services\Interfaces;

use App\Domain\Aggregates\FootballMatch;

interface MatchSimulatorInterface
{
    public function simulateAndPlay(FootballMatch $match): void;
} 