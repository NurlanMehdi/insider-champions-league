<?php

namespace App\Domain\Services\Strategies;

use App\Domain\ValueObjects\Score;
use App\Domain\ValueObjects\TeamStrength;

interface MatchSimulationStrategyInterface
{
    public function simulate(TeamStrength $homeStrength, TeamStrength $awayStrength): Score;
    
    public function getStrategyName(): string;
} 