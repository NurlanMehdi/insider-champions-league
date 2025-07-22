<?php

namespace App\Application\DTOs;

final class MatchDTO
{
    private int $id;
    private string $homeTeamName;
    private string $awayTeamName;
    private int $week;
    private ?int $homeScore;
    private ?int $awayScore;
    private bool $isPlayed;
    private string $result;
    private ?string $playedAt;

    public function __construct(
        int $id,
        string $homeTeamName,
        string $awayTeamName,
        int $week,
        ?int $homeScore,
        ?int $awayScore,
        bool $isPlayed,
        string $result,
        ?string $playedAt
    ) {
        $this->id = $id;
        $this->homeTeamName = $homeTeamName;
        $this->awayTeamName = $awayTeamName;
        $this->week = $week;
        $this->homeScore = $homeScore;
        $this->awayScore = $awayScore;
        $this->isPlayed = $isPlayed;
        $this->result = $result;
        $this->playedAt = $playedAt;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['home_team_name'],
            $data['away_team_name'],
            $data['week'],
            $data['home_score'] ?? null,
            $data['away_score'] ?? null,
            $data['is_played'] ?? false,
            $data['result'] ?? 'vs',
            $data['played_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'home_team' => $this->homeTeamName,
            'away_team' => $this->awayTeamName,
            'week' => $this->week,
            'home_score' => $this->homeScore,
            'away_score' => $this->awayScore,
            'is_played' => $this->isPlayed,
            'result' => $this->result,
            'played_at' => $this->playedAt,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getHomeTeamName(): string
    {
        return $this->homeTeamName;
    }

    public function getAwayTeamName(): string
    {
        return $this->awayTeamName;
    }

    public function getWeek(): int
    {
        return $this->week;
    }

    public function getHomeScore(): ?int
    {
        return $this->homeScore;
    }

    public function getAwayScore(): ?int
    {
        return $this->awayScore;
    }

    public function isPlayed(): bool
    {
        return $this->isPlayed;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function getPlayedAt(): ?string
    {
        return $this->playedAt;
    }
} 