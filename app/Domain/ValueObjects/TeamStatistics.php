<?php

namespace App\Domain\ValueObjects;

final class TeamStatistics
{
    private int $played;
    private int $wins;
    private int $draws;
    private int $losses;
    private int $goalsFor;
    private int $goalsAgainst;

    public function __construct(
        int $played,
        int $wins,
        int $draws,
        int $losses,
        int $goalsFor,
        int $goalsAgainst
    ) {
        $this->played = $played;
        $this->wins = $wins;
        $this->draws = $draws;
        $this->losses = $losses;
        $this->goalsFor = $goalsFor;
        $this->goalsAgainst = $goalsAgainst;
    }

    public static function empty(): self
    {
        return new self(0, 0, 0, 0, 0, 0);
    }

    public function getPlayed(): int
    {
        return $this->played;
    }

    public function getWins(): int
    {
        return $this->wins;
    }

    public function getDraws(): int
    {
        return $this->draws;
    }

    public function getLosses(): int
    {
        return $this->losses;
    }

    public function getGoalsFor(): int
    {
        return $this->goalsFor;
    }

    public function getGoalsAgainst(): int
    {
        return $this->goalsAgainst;
    }

    public function getGoalDifference(): int
    {
        return $this->goalsFor - $this->goalsAgainst;
    }

    public function getPoints(): int
    {
        return ($this->wins * 3) + ($this->draws * 1);
    }

    public function addWin(int $goalsFor, int $goalsAgainst): self
    {
        return new self(
            $this->played + 1,
            $this->wins + 1,
            $this->draws,
            $this->losses,
            $this->goalsFor + $goalsFor,
            $this->goalsAgainst + $goalsAgainst
        );
    }

    public function addDraw(int $goalsFor, int $goalsAgainst): self
    {
        return new self(
            $this->played + 1,
            $this->wins,
            $this->draws + 1,
            $this->losses,
            $this->goalsFor + $goalsFor,
            $this->goalsAgainst + $goalsAgainst
        );
    }

    public function addLoss(int $goalsFor, int $goalsAgainst): self
    {
        return new self(
            $this->played + 1,
            $this->wins,
            $this->draws,
            $this->losses + 1,
            $this->goalsFor + $goalsFor,
            $this->goalsAgainst + $goalsAgainst
        );
    }
} 