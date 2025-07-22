<?php

namespace App\Domain\Aggregates;

use App\Domain\Common\AggregateRoot;
use App\Domain\Events\TeamStatisticsUpdatedEvent;
use App\Domain\ValueObjects\TeamStrength;
use App\Domain\ValueObjects\TeamStatistics;

final class Team extends AggregateRoot
{
    private TeamStatistics $statistics;
    private int $id;
    private string $name;
    private TeamStrength $strength;
    private ?string $logo;

    private function __construct(int $id, string $name, TeamStrength $strength, ?string $logo = null)
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

        $this->recordDomainEvent(new TeamStatisticsUpdatedEvent(
            $this->id,
            $statistics->getPlayed(),
            $statistics->getWins(),
            $statistics->getDraws(),
            $statistics->getLosses(),
            $statistics->getGoalsFor(),
            $statistics->getGoalsAgainst(),
            $statistics->getPoints()
        ));
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
} 