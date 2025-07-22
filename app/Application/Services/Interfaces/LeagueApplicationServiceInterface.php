<?php

namespace App\Application\Services\Interfaces;

use App\Application\DTOs\MatchDTO;

interface LeagueApplicationServiceInterface
{
    public function getLeagueStandings(): array;
    
    public function getWeeklyResults(int $week): array;
    
    public function simulateWeek(int $week): void;
    
    public function simulateAllMatches(): void;
    
    public function updateMatchResult(int $matchId, int $homeScore, int $awayScore): MatchDTO;
    
    public function resetLeague(): void;
    
    public function generateFixtures(): void;
    
    public function getCurrentWeek(): int;
} 