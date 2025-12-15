<?php

namespace App\Domain\Specifications;

use App\Domain\Aggregates\Team;

final class PremierLeagueRulesSpecification
{
    private const REQUIRED_TEAMS = 20;
    private const MATCHES_PER_TEAM = 38;
    private const TOTAL_WEEKS = 38;
    private const POINTS_FOR_WIN = 3;
    private const POINTS_FOR_DRAW = 1;
    private const POINTS_FOR_LOSS = 0;

    public function isSatisfiedBy(array $teams): bool
    {
        return $this->hasCorrectNumberOfTeams($teams) 
            && $this->hasValidTeamStrengths($teams)
            && $this->hasUniqueTeamNames($teams);
    }

    public function calculatePoints(int $wins, int $draws, int $losses): int
    {
        return ($wins * self::POINTS_FOR_WIN) 
             + ($draws * self::POINTS_FOR_DRAW) 
             + ($losses * self::POINTS_FOR_LOSS);
    }

    public function getRequiredTeamsCount(): int
    {
        return self::REQUIRED_TEAMS;
    }

    public function getTotalWeeks(): int
    {
        return self::TOTAL_WEEKS;
    }

    public function getMatchesPerTeam(): int
    {
        return self::MATCHES_PER_TEAM;
    }

    public function getMatchesPerWeek(): int
    {
        return self::REQUIRED_TEAMS / 2;
    }

    private function hasCorrectNumberOfTeams(array $teams): bool
    {
        return count($teams) === self::REQUIRED_TEAMS;
    }

    private function hasValidTeamStrengths(array $teams): bool
    {
        foreach ($teams as $team) {
            if (!$team instanceof Team) {
                return false;
            }
            
            $strength = $team->getStrength()->getValue();
            if ($strength < 1 || $strength > 100) {
                return false;
            }
        }

        return true;
    }

    private function hasUniqueTeamNames(array $teams): bool
    {
        $names = [];
        
        foreach ($teams as $team) {
            if (!$team instanceof Team) {
                return false;
            }
            
            $name = strtolower(trim($team->getName()));
            
            if (in_array($name, $names)) {
                return false;
            }
            
            $names[] = $name;
        }

        return true;
    }
} 