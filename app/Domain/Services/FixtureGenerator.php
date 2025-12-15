<?php

namespace App\Domain\Services;

use App\Domain\Aggregates\Team;
use App\Domain\Services\Interfaces\FixtureGeneratorInterface;

final class FixtureGenerator implements FixtureGeneratorInterface
{
    public function generateRoundRobin(array $teams): array
    {
        $fixtures = [];
        $teamCount = count($teams);
        
        if ($teamCount < 2) {
            return $fixtures;
        }

        // For even number of teams, we need (n-1) rounds
        // For odd number of teams, we need n rounds
        $rounds = ($teamCount % 2 === 0) ? $teamCount - 1 : $teamCount;
        
        // Create team IDs array for easier manipulation
        $teamIds = [];
        foreach ($teams as $team) {
            $teamIds[] = $team->getId();
        }

        // Generate first half of season (home/away assignments)
        $firstHalf = $this->generateFirstHalf($teamIds, $rounds);
        
        // Generate second half (reverse home/away)
        $secondHalf = $this->generateSecondHalf($firstHalf, $rounds);
        
        // Combine both halves using + operator to preserve keys
        return $firstHalf + $secondHalf;
    }

    private function generateFirstHalf(array $teamIds, int $rounds): array
    {
        $fixtures = [];
        $teamCount = count($teamIds);
        
        // For even teams, fix one team and rotate others
        if ($teamCount % 2 === 0) {
            $fixed = array_pop($teamIds); // Fix last team
            
            for ($round = 1; $round <= $rounds; $round++) {
                $matches = [];
                
                // First match: fixed team vs rotating team
                $matches[] = [
                    'home_team' => ($round % 2 === 1) ? $fixed : $teamIds[0],
                    'away_team' => ($round % 2 === 1) ? $teamIds[0] : $fixed,
                ];
                
                // Other matches: pair remaining teams
                for ($i = 1; $i < count($teamIds); $i++) {
                    $j = count($teamIds) - $i;
                    if ($i < $j) {
                        $matches[] = [
                            'home_team' => $teamIds[$i],
                            'away_team' => $teamIds[$j],
                        ];
                    }
                }
                
                $fixtures[$round] = $matches;
                
                // Rotate teams (except first one)
                $first = array_shift($teamIds);
                array_push($teamIds, $first);
            }
        } else {
            // For odd teams, rotate all teams
            for ($round = 1; $round <= $rounds; $round++) {
                $matches = [];
                
                for ($i = 0; $i < count($teamIds); $i++) {
                    $j = (count($teamIds) - 1 - $i + $round - 1) % count($teamIds);
                    if ($i < $j) {
                        $matches[] = [
                            'home_team' => $teamIds[$i],
                            'away_team' => $teamIds[$j],
                        ];
                    }
                }
                
                $fixtures[$round] = $matches;
            }
        }
        
        return $fixtures;
    }

    private function generateSecondHalf(array $firstHalf, int $rounds): array
    {
        $secondHalf = [];
        
        foreach ($firstHalf as $week => $matches) {
            $returnWeek = $week + $rounds;
            $returnMatches = [];
            
            foreach ($matches as $match) {
                // Reverse home/away for return fixtures
                $returnMatches[] = [
                    'home_team' => $match['away_team'],
                    'away_team' => $match['home_team'],
                ];
            }
            
            $secondHalf[$returnWeek] = $returnMatches;
        }
        
        return $secondHalf;
    }
} 