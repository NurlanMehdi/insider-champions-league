<?php

namespace Tests\Unit\Domain\Services;

use Tests\TestCase;
use App\Domain\Services\FixtureGenerator;
use App\Domain\Aggregates\Team;
use App\Domain\ValueObjects\TeamStrength;

class FixtureGeneratorTest extends TestCase
{
    private FixtureGenerator $fixtureGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureGenerator = new FixtureGenerator();
    }

    public function test_generates_correct_number_of_fixtures_for_premier_league(): void
    {
        $teams = $this->createTeams(20);
        
        $fixtures = $this->fixtureGenerator->generateRoundRobin($teams);
        
        // Should have 38 weeks for 20 teams
        $this->assertCount(38, $fixtures);
        
        // Each week should have 10 matches (20 teams / 2)
        foreach ($fixtures as $week => $matches) {
            $this->assertCount(10, $matches, "Week {$week} should have 10 matches");
        }
        
        // Total matches should be 380 (20 teams * 19 opponents * 2 for home/away)
        $totalMatches = array_sum(array_map('count', $fixtures));
        $this->assertEquals(380, $totalMatches);
    }

    public function test_weeks_are_numbered_from_1_to_38(): void
    {
        $teams = $this->createTeams(20);
        
        $fixtures = $this->fixtureGenerator->generateRoundRobin($teams);
        
        $weeks = array_keys($fixtures);
        $this->assertEquals(1, min($weeks), 'First week should be 1');
        $this->assertEquals(38, max($weeks), 'Last week should be 38');
        
        // Should have consecutive weeks 1-38
        $expectedWeeks = range(1, 38);
        sort($weeks);
        $this->assertEquals($expectedWeeks, $weeks);
    }

    public function test_each_team_plays_every_other_team_twice(): void
    {
        $teams = $this->createTeams(4); // Smaller test case
        
        $fixtures = $this->fixtureGenerator->generateRoundRobin($teams);
        
        // Track all matchups
        $matchups = [];
        foreach ($fixtures as $matches) {
            foreach ($matches as $match) {
                $home = $match['home_team'];
                $away = $match['away_team'];
                
                $matchups[] = "{$home}-{$away}";
            }
        }
        
        // Each team should play every other team exactly twice (home and away)
        for ($i = 1; $i <= 4; $i++) {
            for ($j = 1; $j <= 4; $j++) {
                if ($i !== $j) {
                    $homeMatch = "{$i}-{$j}";
                    $awayMatch = "{$j}-{$i}";
                    
                    $this->assertContains($homeMatch, $matchups, "Team {$i} should play at home vs Team {$j}");
                    $this->assertContains($awayMatch, $matchups, "Team {$i} should play away vs Team {$j}");
                }
            }
        }
    }

    public function test_no_team_plays_against_itself(): void
    {
        $teams = $this->createTeams(10);
        
        $fixtures = $this->fixtureGenerator->generateRoundRobin($teams);
        
        foreach ($fixtures as $matches) {
            foreach ($matches as $match) {
                $this->assertNotEquals(
                    $match['home_team'], 
                    $match['away_team'],
                    'A team should never play against itself'
                );
            }
        }
    }

    public function test_returns_empty_array_for_insufficient_teams(): void
    {
        $noTeams = [];
        $oneTeam = $this->createTeams(1);
        
        $this->assertEmpty($this->fixtureGenerator->generateRoundRobin($noTeams));
        $this->assertEmpty($this->fixtureGenerator->generateRoundRobin($oneTeam));
    }

    public function test_each_team_has_equal_home_and_away_matches(): void
    {
        $teams = $this->createTeams(6);
        
        $fixtures = $this->fixtureGenerator->generateRoundRobin($teams);
        
        $homeMatches = [];
        $awayMatches = [];
        
        foreach ($fixtures as $matches) {
            foreach ($matches as $match) {
                $home = $match['home_team'];
                $away = $match['away_team'];
                
                $homeMatches[$home] = ($homeMatches[$home] ?? 0) + 1;
                $awayMatches[$away] = ($awayMatches[$away] ?? 0) + 1;
            }
        }
        
        // Each team should have same number of home and away matches
        for ($i = 1; $i <= 6; $i++) {
            $this->assertEquals(
                $homeMatches[$i] ?? 0,
                $awayMatches[$i] ?? 0,
                "Team {$i} should have equal home and away matches"
            );
        }
    }

    public function test_fixture_structure_is_valid(): void
    {
        $teams = $this->createTeams(8);
        
        $fixtures = $this->fixtureGenerator->generateRoundRobin($teams);
        
        foreach ($fixtures as $week => $matches) {
            $this->assertIsInt($week, 'Week should be an integer');
            $this->assertGreaterThan(0, $week, 'Week should be positive');
            $this->assertIsArray($matches, 'Matches should be an array');
            
            foreach ($matches as $match) {
                $this->assertArrayHasKey('home_team', $match);
                $this->assertArrayHasKey('away_team', $match);
                $this->assertIsInt($match['home_team']);
                $this->assertIsInt($match['away_team']);
                $this->assertGreaterThan(0, $match['home_team']);
                $this->assertGreaterThan(0, $match['away_team']);
            }
        }
    }

    public function test_premier_league_specific_requirements(): void
    {
        $teams = $this->createTeams(20);
        
        $fixtures = $this->fixtureGenerator->generateRoundRobin($teams);
        
        // Premier League specific checks
        $this->assertCount(38, $fixtures, 'Should have 38 weeks');
        
        // First 19 weeks should be first half of season
        // Last 19 weeks should be return fixtures
        $firstHalfMatches = [];
        $secondHalfMatches = [];
        
        for ($week = 1; $week <= 19; $week++) {
            foreach ($fixtures[$week] as $match) {
                $firstHalfMatches[] = $match['home_team'] . '-' . $match['away_team'];
            }
        }
        
        for ($week = 20; $week <= 38; $week++) {
            foreach ($fixtures[$week] as $match) {
                $secondHalfMatches[] = $match['away_team'] . '-' . $match['home_team']; // Reversed
            }
        }
        
        // Second half should contain all first half fixtures but with reversed home/away
        sort($firstHalfMatches);
        sort($secondHalfMatches);
        $this->assertEquals($firstHalfMatches, $secondHalfMatches, 'Second half should mirror first half with reversed fixtures');
    }

    public function test_no_duplicate_fixtures_in_same_week(): void
    {
        $teams = $this->createTeams(12);
        
        $fixtures = $this->fixtureGenerator->generateRoundRobin($teams);
        
        foreach ($fixtures as $week => $matches) {
            $matchStrings = [];
            foreach ($matches as $match) {
                $matchString = $match['home_team'] . 'vs' . $match['away_team'];
                $this->assertNotContains($matchString, $matchStrings, "Week {$week} has duplicate fixture");
                $matchStrings[] = $matchString;
            }
        }
    }

    private function createTeams(int $count): array
    {
        $teams = [];
        for ($i = 1; $i <= $count; $i++) {
            $strength = TeamStrength::fromValue(60 + ($i % 40)); // Keep strength between 60-99
            $teams[] = Team::create($i, "Team {$i}", $strength);
        }
        return $teams;
    }
} 