<?php

namespace App\Application\Services;

use App\Domain\Repositories\Interfaces\MatchRepositoryInterface;
use App\Domain\Repositories\Interfaces\TeamRepositoryInterface;
use App\Domain\Services\Interfaces\MatchSimulatorInterface;
use App\Domain\Services\Strategies\MatchSimulationStrategyInterface;

final class MatchSimulationService
{
    private MatchRepositoryInterface $matchRepository;
    private TeamRepositoryInterface $teamRepository;
    private MatchSimulatorInterface $matchSimulator;
    private MatchSimulationStrategyInterface $simulationStrategy;
    
    private array $teamCache = [];

    public function __construct(
        MatchRepositoryInterface $matchRepository,
        TeamRepositoryInterface $teamRepository,
        MatchSimulatorInterface $matchSimulator,
        MatchSimulationStrategyInterface $simulationStrategy
    ) {
        $this->matchRepository = $matchRepository;
        $this->teamRepository = $teamRepository;
        $this->matchSimulator = $matchSimulator;
        $this->simulationStrategy = $simulationStrategy;
    }

    public function simulateWeek(int $week): void
    {
        $matches = $this->matchRepository->findByWeek($week);
        
        foreach ($matches as $match) {
            if (!$match->isPlayed()) {
                $this->matchSimulator->simulateAndPlay($match);
                $this->matchRepository->save($match);
            }
        }
    }

    public function simulateAllRemainingMatches(): void
    {
        // Pre-load all teams to cache for performance
        $this->loadTeamsToCache();
        
        $allMatches = $this->matchRepository->findAll();
        $unplayedMatches = array_filter($allMatches, fn($match) => !$match->isPlayed());
        
        if (empty($unplayedMatches)) {
            return;
        }

        // Process matches in batches for better performance
        $batchSize = 50; // Process 50 matches at a time
        $batches = array_chunk($unplayedMatches, $batchSize);
        
        foreach ($batches as $batch) {
            $this->simulateBatch($batch);
            
            // Small delay to prevent overwhelming the system
            usleep(10000); // 10ms delay between batches
        }
        
        // Clear cache after simulation
        $this->teamCache = [];
    }

    public function simulateAllRemainingMatchesFast(): void
    {
        // Ultra-fast simulation without domain events for bulk operations
        $this->loadTeamsToCache();
        
        $allMatches = $this->matchRepository->findAll();
        $unplayedMatches = array_filter($allMatches, fn($match) => !$match->isPlayed());
        
        if (empty($unplayedMatches)) {
            return;
        }

        // Bulk simulate all matches without individual saves
        foreach ($unplayedMatches as $match) {
            $homeTeam = $this->getCachedTeam($match->getHomeTeamId());
            $awayTeam = $this->getCachedTeam($match->getAwayTeamId());
            
            $score = $this->simulationStrategy->simulate(
                $homeTeam->getStrength(), 
                $awayTeam->getStrength()
            );
            
            $match->play($score);
        }
        
        // Bulk save all matches at once
        $this->bulkSaveMatches($unplayedMatches);
        
        // Update team statistics in bulk
        $this->bulkUpdateTeamStatistics();
        
        $this->teamCache = [];
    }

    private function simulateBatch(array $matches): void
    {
        foreach ($matches as $match) {
            $homeTeam = $this->getCachedTeam($match->getHomeTeamId());
            $awayTeam = $this->getCachedTeam($match->getAwayTeamId());
            
            $score = $this->simulationStrategy->simulate(
                $homeTeam->getStrength(), 
                $awayTeam->getStrength()
            );
            
            $match->play($score);
        }
        
        // Save batch of matches
        foreach ($matches as $match) {
            $this->matchRepository->save($match);
        }
    }

    private function loadTeamsToCache(): void
    {
        if (empty($this->teamCache)) {
            $teams = $this->teamRepository->findAll();
            foreach ($teams as $team) {
                $this->teamCache[$team->getId()] = $team;
            }
        }
    }

    private function getCachedTeam(int $teamId)
    {
        if (!isset($this->teamCache[$teamId])) {
            $this->teamCache[$teamId] = $this->teamRepository->findById($teamId);
        }
        
        return $this->teamCache[$teamId];
    }

    private function bulkSaveMatches(array $matches): void
    {
        // Use raw SQL for bulk updates to improve performance
        $matchData = [];
        
        foreach ($matches as $match) {
            if ($match->isPlayed()) {
                $score = $match->getScore();
                $matchData[] = [
                    'id' => $match->getId(),
                    'home_score' => $score->getHome(),
                    'away_score' => $score->getAway(),
                    'is_played' => 1,
                    'played_at' => $match->getPlayedAt()?->format('Y-m-d H:i:s') ?? now(),
                ];
            }
        }
        
        if (!empty($matchData)) {
            // Use Laravel's bulk update functionality
            foreach ($matchData as $data) {
                \DB::table('matches')
                    ->where('id', $data['id'])
                    ->update([
                        'home_score' => $data['home_score'],
                        'away_score' => $data['away_score'],
                        'is_played' => $data['is_played'],
                        'played_at' => $data['played_at'],
                    ]);
            }
        }
    }

    private function bulkUpdateTeamStatistics(): void
    {
        // Recalculate all team statistics from match results
        $teams = $this->teamRepository->findAll();
        
        foreach ($teams as $team) {
            $this->teamRepository->save($team); // This will recalculate statistics
        }
    }
} 