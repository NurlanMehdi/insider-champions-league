<?php

namespace App\Application\Services;

use App\Domain\Aggregates\FootballMatch;
use App\Domain\Repositories\Interfaces\MatchRepositoryInterface;
use App\Domain\Repositories\Interfaces\TeamRepositoryInterface;
use App\Domain\Services\Interfaces\FixtureGeneratorInterface;

final class LeagueSetupService
{
    private TeamRepositoryInterface $teamRepository;
    private MatchRepositoryInterface $matchRepository;
    private FixtureGeneratorInterface $fixtureGenerator;

    public function __construct(
        TeamRepositoryInterface $teamRepository,
        MatchRepositoryInterface $matchRepository,
        FixtureGeneratorInterface $fixtureGenerator
    ) {
        $this->teamRepository = $teamRepository;
        $this->matchRepository = $matchRepository;
        $this->fixtureGenerator = $fixtureGenerator;
    }

    public function generateFixtures(): void
    {
        $teams = $this->teamRepository->findAll();
        $fixtures = $this->fixtureGenerator->generateRoundRobin($teams);

        $this->createMatchesFromFixtures($fixtures);
    }

    public function resetLeague(): void
    {
        $this->matchRepository->deleteAll();
        $this->generateFixtures();
    }

    private function createMatchesFromFixtures(array $fixtures): void
    {
        $matchId = 1;
        
        foreach ($fixtures as $week => $matches) {
            foreach ($matches as $matchData) {
                $match = FootballMatch::create(
                    $matchId++,
                    $matchData['home_team'],
                    $matchData['away_team'],
                    $week
                );
                
                $this->matchRepository->save($match);
            }
        }
    }
} 