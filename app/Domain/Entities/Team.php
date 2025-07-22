<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\TeamStrength;
use App\Domain\ValueObjects\TeamStatistics;

final class Team
{
    private TeamStatistics $statistics;
    private int $id;
    private string $name;
    private TeamStrength $strength;
    private ?string $logo;

    public function __construct(int $id, string $name, TeamStrength $strength, ?string $logo = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->strength = $strength;
        $this->logo = $logo;
        $this->statistics = TeamStatistics::empty();
    }

    public static function create(int $id, string $name, TeamStrength $strength, ?string $logo = null): self
    {
        return new self($id, $name, $strength, $logo);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStrength(): TeamStrength
    {
        return $this->strength;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function getStatistics(): TeamStatistics
    {
        return $this->statistics;
    }

    public function updateStatistics(TeamStatistics $statistics): void
    {
        $this->statistics = $statistics;
    }

    public function changeName(string $newName): void
    {
        if (empty(trim($newName))) {
            throw new \InvalidArgumentException('Team name cannot be empty');
        }
        
        $this->name = $newName;
    }

    public function updateStrength(TeamStrength $newStrength): void
    {
        $this->strength = $newStrength;
    }

    public function recordWin(int $goalsFor, int $goalsAgainst): void
    {
        $this->statistics = $this->statistics->addWin($goalsFor, $goalsAgainst);
    }

    public function recordDraw(int $goalsFor, int $goalsAgainst): void
    {
        $this->statistics = $this->statistics->addDraw($goalsFor, $goalsAgainst);
    }

    public function recordLoss(int $goalsFor, int $goalsAgainst): void
    {
        $this->statistics = $this->statistics->addLoss($goalsFor, $goalsAgainst);
    }
} 