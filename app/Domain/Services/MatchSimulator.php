<?php

namespace App\Domain\Services;

use App\Domain\Aggregates\FootballMatch;
use App\Domain\Aggregates\Team;
use App\Domain\Services\Interfaces\MatchSimulatorInterface;
use App\Domain\Services\Strategies\MatchSimulationStrategyInterface;
use App\Domain\Repositories\Interfaces\TeamRepositoryInterface;

final class MatchSimulator implements MatchSimulatorInterface
{
    private MatchSimulationStrategyInterface $strategy;
    private TeamRepositoryInterface $teamRepository;

    public function __construct(
        MatchSimulationStrategyInterface $strategy,
        TeamRepositoryInterface $teamRepository
    ) {
        $this->strategy = $strategy;
        $this->teamRepository = $teamRepository;
    }

    public function simulateAndPlay(FootballMatch $match): void
    {
        $homeTeam = $this->teamRepository->findById($match->getHomeTeamId());
        $awayTeam = $this->teamRepository->findById($match->getAwayTeamId());
        
        $score = $this->strategy->simulate($homeTeam->getStrength(), $awayTeam->getStrength());
        
        $match->play($score);
    }
} 