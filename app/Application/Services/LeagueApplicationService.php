<?php

namespace App\Application\Services;

use App\Application\DTOs\MatchDTO;
use App\Application\Services\Interfaces\LeagueApplicationServiceInterface;

final class LeagueApplicationService implements LeagueApplicationServiceInterface
{
    private LeagueStandingsService $standingsService;
    private MatchManagementService $matchManagementService;
    private MatchSimulationService $simulationService;
    private WeekCalculationService $weekCalculationService;

    public function __construct(
        LeagueStandingsService $standingsService,
        MatchManagementService $matchManagementService,
        MatchSimulationService $simulationService,
        WeekCalculationService $weekCalculationService
    ) {
        $this->standingsService = $standingsService;
        $this->matchManagementService = $matchManagementService;
        $this->simulationService = $simulationService;
        $this->weekCalculationService = $weekCalculationService;
    }

    public function getLeagueStandings(): array
    {
        $currentWeek = $this->weekCalculationService->getCurrentWeek();
        return $this->standingsService->getStandingsWithPredictions($currentWeek);
    }

    public function getWeeklyResults(int $week): array
    {
        return $this->matchManagementService->getWeeklyResults($week);
    }

    public function simulateWeek(int $week): void
    {
        $this->simulationService->simulateWeek($week);
    }

    public function simulateAllMatches(): void
    {
        $this->simulationService->simulateAllRemainingMatches();
    }

    public function updateMatchResult(int $matchId, int $homeScore, int $awayScore): MatchDTO
    {
        return $this->matchManagementService->updateMatchResult($matchId, $homeScore, $awayScore);
    }

    public function resetLeague(): void
    {
    }

    public function generateFixtures(): void
    {
    }

    public function getCurrentWeek(): int
    {
        return $this->weekCalculationService->getCurrentWeek();
    }
} 