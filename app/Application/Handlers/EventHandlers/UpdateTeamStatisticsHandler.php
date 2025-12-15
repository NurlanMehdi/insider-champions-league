<?php

namespace App\Application\Handlers\EventHandlers;

use App\Domain\Events\MatchPlayedEvent;
use App\Domain\Repositories\Interfaces\TeamRepositoryInterface;
use App\Domain\ValueObjects\TeamStatistics;

final class UpdateTeamStatisticsHandler
{
    private TeamRepositoryInterface $teamRepository;

    public function __construct(TeamRepositoryInterface $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function handle(MatchPlayedEvent $event): void
    {
        $homeTeam = $this->teamRepository->findById($event->getHomeTeamId());
        $awayTeam = $this->teamRepository->findById($event->getAwayTeamId());

        $this->updateTeamStatisticsFromMatch($homeTeam, $awayTeam, $event);
        
        $this->teamRepository->save($homeTeam);
        $this->teamRepository->save($awayTeam);
    }

    private function updateTeamStatisticsFromMatch($homeTeam, $awayTeam, MatchPlayedEvent $event): void
    {
        $homeGoals = $event->getHomeScore();
        $awayGoals = $event->getAwayScore();
        
        $homeStats = $homeTeam->getStatistics();
        $awayStats = $awayTeam->getStatistics();

        if ($homeGoals > $awayGoals) {
            $newHomeStats = $homeStats->addWin($homeGoals, $awayGoals);
            $newAwayStats = $awayStats->addLoss($awayGoals, $homeGoals);
        } elseif ($awayGoals > $homeGoals) {
            $newHomeStats = $homeStats->addLoss($homeGoals, $awayGoals);
            $newAwayStats = $awayStats->addWin($awayGoals, $homeGoals);
        } else {
            $newHomeStats = $homeStats->addDraw($homeGoals, $awayGoals);
            $newAwayStats = $awayStats->addDraw($awayGoals, $homeGoals);
        }

        $homeTeam->updateStatistics($newHomeStats);
        $awayTeam->updateStatistics($newAwayStats);
    }
} 