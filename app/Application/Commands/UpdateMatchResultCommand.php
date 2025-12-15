<?php

namespace App\Application\Commands;

final class UpdateMatchResultCommand
{
    private int $matchId;
    private int $homeScore;
    private int $awayScore;

    public function __construct(int $matchId, int $homeScore, int $awayScore)
    {
        $this->matchId = $matchId;
        $this->homeScore = $homeScore;
        $this->awayScore = $awayScore;
    }

    public function getMatchId(): int
    {
        return $this->matchId;
    }

    public function getHomeScore(): int
    {
        return $this->homeScore;
    }

    public function getAwayScore(): int
    {
        return $this->awayScore;
    }
} 