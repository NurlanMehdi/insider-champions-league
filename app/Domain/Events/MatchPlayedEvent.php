<?php

namespace App\Domain\Events;

use DateTimeImmutable;

final class MatchPlayedEvent implements DomainEventInterface
{
    private int $matchId;
    private int $homeTeamId;
    private int $awayTeamId;
    private int $homeScore;
    private int $awayScore;
    private int $week;
    private DateTimeImmutable $occurredAt;

    public function __construct(
        int $matchId,
        int $homeTeamId,
        int $awayTeamId,
        int $homeScore,
        int $awayScore,
        int $week
    ) {
        $this->matchId = $matchId;
        $this->homeTeamId = $homeTeamId;
        $this->awayTeamId = $awayTeamId;
        $this->homeScore = $homeScore;
        $this->awayScore = $awayScore;
        $this->week = $week;
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->matchId;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getEventName(): string
    {
        return 'match.played';
    }

    public function getEventData(): array
    {
        return [
            'match_id' => $this->matchId,
            'home_team_id' => $this->homeTeamId,
            'away_team_id' => $this->awayTeamId,
            'home_score' => $this->homeScore,
            'away_score' => $this->awayScore,
            'week' => $this->week,
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s'),
        ];
    }

    public function getHomeTeamId(): int
    {
        return $this->homeTeamId;
    }

    public function getAwayTeamId(): int
    {
        return $this->awayTeamId;
    }

    public function getHomeScore(): int
    {
        return $this->homeScore;
    }

    public function getAwayScore(): int
    {
        return $this->awayScore;
    }

    public function getWeek(): int
    {
        return $this->week;
    }
} 