<?php

namespace App\Application\Services;

use App\Application\DTOs\MatchDTO;
use App\Domain\Repositories\Interfaces\MatchRepositoryInterface;
use App\Domain\Repositories\Interfaces\TeamRepositoryInterface;
use App\Domain\ValueObjects\Score;

final class MatchManagementService
{
    private MatchRepositoryInterface $matchRepository;
    private TeamRepositoryInterface $teamRepository;

    public function __construct(
        MatchRepositoryInterface $matchRepository,
        TeamRepositoryInterface $teamRepository
    ) {
        $this->matchRepository = $matchRepository;
        $this->teamRepository = $teamRepository;
    }

    public function getWeeklyResults(int $week): array
    {
        $matches = $this->matchRepository->findByWeek($week);
        
        return array_map(function ($match) {
            $homeTeam = $this->teamRepository->findById($match->getHomeTeamId());
            $awayTeam = $this->teamRepository->findById($match->getAwayTeamId());
            
            return new MatchDTO(
                $match->getId(),
                $homeTeam->getName(),
                $awayTeam->getName(),
                $match->getWeek(),
                $match->getScore()?->getHome(),
                $match->getScore()?->getAway(),
                $match->isPlayed(),
                $match->getResult(),
                $match->getPlayedAt()?->format('Y-m-d H:i:s')
            );
        }, $matches);
    }

    public function updateMatchResult(int $matchId, int $homeScore, int $awayScore): MatchDTO
    {
        $match = $this->matchRepository->findById($matchId);
        $homeTeam = $this->teamRepository->findById($match->getHomeTeamId());
        $awayTeam = $this->teamRepository->findById($match->getAwayTeamId());
        
        $score = Score::create($homeScore, $awayScore);
        
        $match->updateResult($score);
        $this->matchRepository->save($match);
        
        return new MatchDTO(
            $match->getId(),
            $homeTeam->getName(),
            $awayTeam->getName(),
            $match->getWeek(),
            $match->getScore()?->getHome(),
            $match->getScore()?->getAway(),
            $match->isPlayed(),
            $match->getResult(),
            $match->getPlayedAt()?->format('Y-m-d H:i:s')
        );
    }
} 