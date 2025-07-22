<?php

namespace App\Domain\Factories;

use App\Domain\Aggregates\Team;
use App\Domain\ValueObjects\TeamStrength;
use App\Domain\Specifications\PremierLeagueRulesSpecification;

final class TeamAggregateFactory
{
    private PremierLeagueRulesSpecification $rulesSpecification;

    public function __construct(PremierLeagueRulesSpecification $rulesSpecification)
    {
        $this->rulesSpecification = $rulesSpecification;
    }

    public function createTeam(int $id, string $name, int $strengthValue, ?string $logo = null): Team
    {
        $this->validateTeamCreation($name, $strengthValue);
        
        $strength = TeamStrength::fromValue($strengthValue);
        return Team::create($id, $name, $strength, $logo);
    }

    public function createTeamsFromArray(array $teamsData): array
    {
        $teams = [];
        
        foreach ($teamsData as $teamData) {
            $teams[] = $this->createTeam(
                $teamData['id'],
                $teamData['name'],
                $teamData['strength'],
                $teamData['logo'] ?? null
            );
        }

        $this->validateTeamCollection($teams);
        
        return $teams;
    }

    public function createDefaultLeagueTeams(): array
    {
        $defaultTeams = [
            ['id' => 1, 'name' => 'Manchester City', 'strength' => 90, 'logo' => null],
            ['id' => 2, 'name' => 'Liverpool FC', 'strength' => 88, 'logo' => null],
            ['id' => 3, 'name' => 'Chelsea FC', 'strength' => 82, 'logo' => null],
            ['id' => 4, 'name' => 'Arsenal FC', 'strength' => 78, 'logo' => null],
        ];

        return $this->createTeamsFromArray($defaultTeams);
    }

    private function validateTeamCreation(string $name, int $strengthValue): void
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Team name cannot be empty');
        }

        if ($strengthValue < 1 || $strengthValue > 100) {
            throw new \InvalidArgumentException('Team strength must be between 1 and 100');
        }
    }

    private function validateTeamCollection(array $teams): void
    {
        if (!$this->rulesSpecification->isSatisfiedBy($teams)) {
            throw new \DomainException('Team collection does not satisfy Premier League rules');
        }
    }
} 