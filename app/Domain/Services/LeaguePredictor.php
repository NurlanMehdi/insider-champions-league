<?php

namespace App\Domain\Services;

use App\Domain\Aggregates\Team;
use App\Domain\Specifications\PremierLeagueRulesSpecification;

final class LeaguePredictor
{
    private PremierLeagueRulesSpecification $rules;

    public function __construct(PremierLeagueRulesSpecification $rules)
    {
        $this->rules = $rules;
    }

    public function predictFinalStandings(array $teams, int $currentWeek): array
    {
        $predictions = [];
        
        foreach ($teams as $team) {
            $currentStats = $team->getStatistics();
            $currentPoints = $currentStats->getPoints();
            $remainingMatches = $this->rules->getMatchesPerTeam() - $currentStats->getPlayed();
            
            $predictedPoints = $this->calculatePredictedPoints($team, $currentPoints, $remainingMatches);
            $predictedGoalDifference = $this->calculatePredictedGoalDifference($team, $remainingMatches);
            $predictedGoalsFor = $currentStats->getGoalsFor() + $this->calculateExpectedGoalsFor($team, $remainingMatches);
            
            $predictions[] = [
                'team_id' => $team->getId(),
                'team_name' => $team->getName(),
                'current_points' => $currentPoints,
                'predicted_points' => $predictedPoints,
                'predicted_goal_difference' => $currentStats->getGoalDifference() + $predictedGoalDifference,
                'predicted_goals_for' => $predictedGoalsFor,
            ];
        }
        
        usort($predictions, function ($a, $b) {
            if ($a['predicted_points'] !== $b['predicted_points']) {
                return $b['predicted_points'] <=> $a['predicted_points'];
            }
            return $b['predicted_goal_difference'] <=> $a['predicted_goal_difference'];
        });
        
        return $predictions;
    }
    
    private function calculatePredictedPoints(Team $team, int $currentPoints, int $remainingMatches): int
    {
        if ($remainingMatches <= 0) {
            return $currentPoints;
        }
        
        $strength = $team->getStrength()->getValue();
        $averagePointsPerMatch = $this->calculateAveragePointsPerMatch($strength);
        
        return $currentPoints + (int) round($averagePointsPerMatch * $remainingMatches);
    }
    
    private function calculatePredictedGoalDifference(Team $team, int $remainingMatches): int
    {
        if ($remainingMatches <= 0) {
            return 0;
        }
        
        $strength = $team->getStrength()->getValue();
        $averageGoalDiffPerMatch = ($strength - 75) / 15; // More conservative for 38-match season
        
        return (int) round($averageGoalDiffPerMatch * $remainingMatches);
    }
    
    private function calculateExpectedGoalsFor(Team $team, int $remainingMatches): int
    {
        if ($remainingMatches <= 0) {
            return 0;
        }
        
        $strength = $team->getStrength()->getValue();
        $averageGoalsPerMatch = 1 + ($strength / 80); // Adjusted for realistic Premier League averages
        
        return (int) round($averageGoalsPerMatch * $remainingMatches);
    }
    
    private function calculateAveragePointsPerMatch(int $strength): float
    {
        // Adjusted for Premier League realism - top teams get around 2.2-2.5 points per game
        if ($strength >= 95) { // Manchester City level
            return 2.4;
        } elseif ($strength >= 90) { // Liverpool level
            return 2.2;
        } elseif ($strength >= 85) { // Top 6 teams
            return 1.9;
        } elseif ($strength >= 80) { // Mid-table competitive
            return 1.5;
        } elseif ($strength >= 75) { // Mid-table
            return 1.3;
        } elseif ($strength >= 70) { // Lower mid-table
            return 1.1;
        } else { // Relegation candidates
            return 0.9;
        }
    }
} 