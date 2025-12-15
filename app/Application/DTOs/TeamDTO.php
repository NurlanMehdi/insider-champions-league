<?php

namespace App\Application\DTOs;

final class TeamDTO
{
    private int $id;
    private string $name;
    private int $strength;
    private ?string $logo;
    private int $played;
    private int $wins;
    private int $draws;
    private int $losses;
    private int $goalsFor;
    private int $goalsAgainst;
    private int $goalDifference;
    private int $points;

    public function __construct(
        int $id,
        string $name,
        int $strength,
        ?string $logo,
        int $played,
        int $wins,
        int $draws,
        int $losses,
        int $goalsFor,
        int $goalsAgainst,
        int $goalDifference,
        int $points
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->strength = $strength;
        $this->logo = $logo;
        $this->played = $played;
        $this->wins = $wins;
        $this->draws = $draws;
        $this->losses = $losses;
        $this->goalsFor = $goalsFor;
        $this->goalsAgainst = $goalsAgainst;
        $this->goalDifference = $goalDifference;
        $this->points = $points;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['strength'],
            $data['logo'] ?? null,
            $data['played'] ?? 0,
            $data['wins'] ?? 0,
            $data['draws'] ?? 0,
            $data['losses'] ?? 0,
            $data['goals_for'] ?? 0,
            $data['goals_against'] ?? 0,
            $data['goal_difference'] ?? 0,
            $data['points'] ?? 0
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'strength' => $this->strength,
            'logo' => $this->logo,
            'played' => $this->played,
            'wins' => $this->wins,
            'draws' => $this->draws,
            'losses' => $this->losses,
            'goals_for' => $this->goalsFor,
            'goals_against' => $this->goalsAgainst,
            'goal_difference' => $this->goalDifference,
            'points' => $this->points,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStrength(): int
    {
        return $this->strength;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
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
        return $this->goalDifference;
    }

    public function getPoints(): int
    {
        return $this->points;
    }
} 