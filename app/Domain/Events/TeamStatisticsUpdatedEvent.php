<?php

namespace App\Domain\Events;

use DateTimeImmutable;

final class TeamStatisticsUpdatedEvent implements DomainEventInterface
{
    private int $teamId;
    private int $played;
    private int $wins;
    private int $draws;
    private int $losses;
    private int $goalsFor;
    private int $goalsAgainst;
    private int $points;
    private DateTimeImmutable $occurredAt;

    public function __construct(
        int $teamId,
        int $played,
        int $wins,
        int $draws,
        int $losses,
        int $goalsFor,
        int $goalsAgainst,
        int $points
    ) {
        $this->teamId = $teamId;
        $this->played = $played;
        $this->wins = $wins;
        $this->draws = $draws;
        $this->losses = $losses;
        $this->goalsFor = $goalsFor;
        $this->goalsAgainst = $goalsAgainst;
        $this->points = $points;
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->teamId;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getEventName(): string
    {
        return 'team.statistics.updated';
    }

    public function getEventData(): array
    {
        return [
            'team_id' => $this->teamId,
            'played' => $this->played,
            'wins' => $this->wins,
            'draws' => $this->draws,
            'losses' => $this->losses,
            'goals_for' => $this->goalsFor,
            'goals_against' => $this->goalsAgainst,
            'points' => $this->points,
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s'),
        ];
    }
} 