<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Score;
use DateTimeImmutable;

final class FootballMatch
{
    private ?Score $score = null;
    private ?DateTimeImmutable $playedAt = null;
    private int $id;
    private Team $homeTeam;
    private Team $awayTeam;
    private int $week;

    public function __construct(int $id, Team $homeTeam, Team $awayTeam, int $week)
    {
        $this->id = $id;
        $this->homeTeam = $homeTeam;
        $this->awayTeam = $awayTeam;
        $this->week = $week;
    }

    public static function create(int $id, Team $homeTeam, Team $awayTeam, int $week): self
    {
        return new self($id, $homeTeam, $awayTeam, $week);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getHomeTeam(): Team
    {
        return $this->homeTeam;
    }

    public function getAwayTeam(): Team
    {
        return $this->awayTeam;
    }

    public function getWeek(): int
    {
        return $this->week;
    }

    public function getScore(): ?Score
    {
        return $this->score;
    }

    public function getPlayedAt(): ?DateTimeImmutable
    {
        return $this->playedAt;
    }

    public function isPlayed(): bool
    {
        return $this->score !== null;
    }

    public function play(Score $score): void
    {
        if ($this->isPlayed()) {
            throw new \DomainException('FootballMatch has already been played');
        }

        $this->score = $score;
        $this->playedAt = new DateTimeImmutable();

        $this->updateTeamStatistics();
    }

    public function updateResult(Score $newScore): void
    {
        $this->score = $newScore;
        $this->playedAt = new DateTimeImmutable();
        $this->updateTeamStatistics();
    }

    public function getWinner(): ?Team
    {
        if (!$this->isPlayed()) {
            return null;
        }

        $result = $this->score->getResult();
        
        switch ($result) {
            case 'home_win':
                return $this->homeTeam;
            case 'away_win':
                return $this->awayTeam;
            default:
                return null;
        }
    }

    public function getResult(): string
    {
        return $this->isPlayed() ? $this->score->toString() : 'vs';
    }

    private function updateTeamStatistics(): void
    {
        if (!$this->score) {
            return;
        }

        $homeGoals = $this->score->getHome();
        $awayGoals = $this->score->getAway();
        $result = $this->score->getResult();

        switch ($result) {
            case 'home_win':
                $this->homeTeam->recordWin($homeGoals, $awayGoals);
                $this->awayTeam->recordLoss($awayGoals, $homeGoals);
                break;
            case 'away_win':
                $this->awayTeam->recordWin($awayGoals, $homeGoals);
                $this->homeTeam->recordLoss($homeGoals, $awayGoals);
                break;
            case 'draw':
                $this->homeTeam->recordDraw($homeGoals, $awayGoals);
                $this->awayTeam->recordDraw($awayGoals, $homeGoals);
                break;
        }
    }
} 