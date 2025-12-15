<?php

namespace App\Infrastructure\Mappers;

use App\Domain\Aggregates\FootballMatch;
use App\Domain\ValueObjects\Score;
use App\Infrastructure\Repositories\TeamRepository;
use App\Models\FootballMatch as MatchModel;

final class FootballMatchMapper
{
    private TeamRepository $teamRepository;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function toDomainEntity(MatchModel $matchModel): FootballMatch
    {
        $footballMatch = FootballMatch::create(
            $matchModel->id,
            $matchModel->home_team_id,
            $matchModel->away_team_id,
            $matchModel->week
        );

        if ($this->matchIsPlayed($matchModel)) {
            $score = Score::create($matchModel->home_score, $matchModel->away_score);
            $footballMatch->updateResult($score);
        }

        return $footballMatch;
    }

    private function matchIsPlayed(MatchModel $matchModel): bool
    {
        return $matchModel->is_played && 
               $matchModel->home_score !== null && 
               $matchModel->away_score !== null;
    }
} 