<?php

namespace App\Application\Services;

use App\Application\DTOs\TeamDTO;
use App\Domain\Repositories\Interfaces\TeamRepositoryInterface;
use App\Domain\Services\LeaguePredictor;

final class LeagueStandingsService
{
    private TeamRepositoryInterface $teamRepository;
    private LeaguePredictor $predictor;

    public function __construct(TeamRepositoryInterface $teamRepository, LeaguePredictor $predictor)
    {
        $this->teamRepository = $teamRepository;
        $this->predictor = $predictor;
    }

    public function getStandings(): array
    {
        $teams = $this->teamRepository->findAll();
        
        $this->sortTeamsByStanding($teams);
        
        return $this->mapToTeamDTOs($teams);
    }

    public function getStandingsWithPredictions(int $currentWeek): array
    {
        $teams = $this->teamRepository->findAll();
        $standings = $this->getStandings();
        $predictions = $this->predictor->predictFinalStandings($teams, $currentWeek);

        return [
            'standings' => $standings,
            'predictions' => $predictions,
        ];
    }

    private function sortTeamsByStanding(array &$teams): void
    {
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
    }

    private function mapToTeamDTOs(array $teams): array
    {
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