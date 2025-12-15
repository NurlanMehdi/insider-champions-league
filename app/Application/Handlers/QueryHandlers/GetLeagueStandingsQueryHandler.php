<?php

namespace App\Application\Handlers\QueryHandlers;

use App\Application\Queries\GetLeagueStandingsQuery;
use App\Application\DTOs\TeamDTO;
use App\Domain\Repositories\Interfaces\TeamRepositoryInterface;

final class GetLeagueStandingsQueryHandler
{
    private TeamRepositoryInterface $teamRepository;

    public function __construct(TeamRepositoryInterface $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function handle(GetLeagueStandingsQuery $query): array
    {
        $teams = $this->teamRepository->findAll();
        
        usort($teams, function ($a, $b) {
            $aStats = $a->getStatistics();
            $bStats = $b->getStatistics();
            
            if ($aStats->getPoints() !== $bStats->getPoints()) {
                return $bStats->getPoints() <=> $aStats->getPoints();
            }
            
            if ($aStats->getGoalDifference() !== $bStats->getGoalDifference()) {
                return $bStats->getGoalDifference() <=> $aStats->getGoalDifference();
            }
            
            return $bStats->getGoalsFor() <=> $aStats->getGoalsFor();
        });

        return array_map(function ($team) {
            $stats = $team->getStatistics();
            return new TeamDTO(
                $team->getId(),
                $team->getName(),
                $team->getStrength()->getValue(),
                $team->getLogo(),
                $stats->getPlayed(),
                $stats->getWins(),
                $stats->getDraws(),
                $stats->getLosses(),
                $stats->getGoalsFor(),
                $stats->getGoalsAgainst(),
                $stats->getGoalDifference(),
                $stats->getPoints()
            );
        }, $teams);
    }
} 