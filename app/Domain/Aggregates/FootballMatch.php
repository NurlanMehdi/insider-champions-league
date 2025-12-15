<?php

namespace App\Domain\Aggregates;

use App\Domain\Common\AggregateRoot;
use App\Domain\Events\MatchPlayedEvent;
use App\Domain\ValueObjects\Score;
use DateTimeImmutable;

final class FootballMatch extends AggregateRoot
{
    private ?Score $score = null;
    private ?DateTimeImmutable $playedAt = null;
    private int $id;
    private int $homeTeamId;
    private int $awayTeamId;
    private int $week;

    private function __construct(int $id, int $homeTeamId, int $awayTeamId, int $week)
    {
        $this->id = $id;
        $this->homeTeamId = $homeTeamId;
        $this->awayTeamId = $awayTeamId;
        $this->week = $week;
    }

    public static function create(int $id, int $homeTeamId, int $awayTeamId, int $week): self
    {
        return new self($id, $homeTeamId, $awayTeamId, $week);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getHomeTeamId(): int
    {
        return $this->homeTeamId;
    }

    public function getAwayTeamId(): int
    {
        return $this->awayTeamId;
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

        $this->recordDomainEvent(new MatchPlayedEvent(
            $this->id,
            $this->homeTeamId,
            $this->awayTeamId,
            $score->getHome(),
            $score->getAway(),
            $this->week
        ));
    }

    public function updateResult(Score $newScore): void
    {
        $this->score = $newScore;
        $this->playedAt = new DateTimeImmutable();

        $this->recordDomainEvent(new MatchPlayedEvent(
            $this->id,
            $this->homeTeamId,
            $this->awayTeamId,
            $newScore->getHome(),
            $newScore->getAway(),
            $this->week
        ));
    }

    public function getWinnerId(): ?int
    {
        if (!$this->isPlayed()) {
            return null;
        }

        $result = $this->score->getResult();
        
        switch ($result) {
            case 'home_win':
                return $this->homeTeamId;
            case 'away_win':
                return $this->awayTeamId;
            default:
                return null;
        }
    }

    public function getResult(): string
    {
        return $this->isPlayed() ? $this->score->toString() : 'vs';
    }
} 