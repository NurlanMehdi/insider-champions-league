<?php

namespace App\Domain\Services\Strategies;

use App\Domain\ValueObjects\Score;
use App\Domain\ValueObjects\TeamStrength;

final class RealisticMatchSimulationStrategy implements MatchSimulationStrategyInterface
{
    private const HOME_ADVANTAGE = 5;
    private const MAX_EXPECTED_GOALS = 4;
    private const MIN_EXPECTED_GOALS = 0;
    private const BASE_EXPECTED_GOALS = 1.5;
    private const STRENGTH_DIVISOR = 20;

    public function simulate(TeamStrength $homeStrength, TeamStrength $awayStrength): Score
    {
        $homeStrengthWithAdvantage = $homeStrength->addHomeAdvantage(self::HOME_ADVANTAGE);
        
        $homeGoals = $this->generateGoals($homeStrengthWithAdvantage, $awayStrength);
        $awayGoals = $this->generateGoals($awayStrength, $homeStrengthWithAdvantage);
        
        return Score::create($homeGoals, $awayGoals);
    }

    public function getStrategyName(): string
    {
        return 'realistic_simulation';
    }

    private function generateGoals(TeamStrength $attackingStrength, TeamStrength $defendingStrength): int
    {
        $strengthDifference = $attackingStrength->getDifference($defendingStrength);
        
        $expectedGoals = max(
            self::MIN_EXPECTED_GOALS, 
            min(self::MAX_EXPECTED_GOALS, self::BASE_EXPECTED_GOALS + ($strengthDifference / self::STRENGTH_DIVISOR))
        );
        
        $goals = $this->calculateGoalsFromProbability();
        
        return $this->adjustGoalsBasedOnExpectation($goals, $expectedGoals);
    }

    private function calculateGoalsFromProbability(): int
    {
        $random = mt_rand(1, 100) / 100;
        
        if ($random < 0.15) return 0;
        if ($random < 0.35) return 1;
        if ($random < 0.65) return 2;
        if ($random < 0.85) return 3;
        if ($random < 0.95) return 4;
        
        return 5;
    }

    private function adjustGoalsBasedOnExpectation(int $goals, float $expectedGoals): int
    {
        if ($expectedGoals < 1) {
            return max(0, $goals - 1);
        }
        
        if ($expectedGoals > 2.5) {
            return min(6, $goals + 1);
        }
        
        return $goals;
    }
} 