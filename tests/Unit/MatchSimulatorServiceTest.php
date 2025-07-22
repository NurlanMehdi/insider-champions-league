<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\MatchSimulatorService;
use App\Models\Team;

class MatchSimulatorServiceTest extends TestCase
{
    private MatchSimulatorService $matchSimulator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matchSimulator = new MatchSimulatorService();
    }

    public function test_simulate_match_returns_valid_scores(): void
    {
        // Arrange
        $homeTeam = new Team(['name' => 'Chelsea', 'strength' => 85]);
        $awayTeam = new Team(['name' => 'Arsenal', 'strength' => 80]);

        // Act
        $result = $this->matchSimulator->simulateMatch($homeTeam, $awayTeam);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('home_score', $result);
        $this->assertArrayHasKey('away_score', $result);
        $this->assertIsInt($result['home_score']);
        $this->assertIsInt($result['away_score']);
        $this->assertGreaterThanOrEqual(0, $result['home_score']);
        $this->assertGreaterThanOrEqual(0, $result['away_score']);
        $this->assertLessThanOrEqual(10, $result['home_score']); // Realistic upper bound
        $this->assertLessThanOrEqual(10, $result['away_score']); // Realistic upper bound
    }

    public function test_stronger_team_tends_to_score_more(): void
    {
        // Arrange
        $strongTeam = new Team(['name' => 'Manchester City', 'strength' => 95]);
        $weakTeam = new Team(['name' => 'Weak Team', 'strength' => 40]);

        $strongerTeamWins = 0;
        $totalMatches = 100;

        // Act - Simulate multiple matches to test statistical tendency
        for ($i = 0; $i < $totalMatches; $i++) {
            $result = $this->matchSimulator->simulateMatch($strongTeam, $weakTeam);
            if ($result['home_score'] > $result['away_score']) {
                $strongerTeamWins++;
            }
        }

        // Assert - Stronger team should win more than 50% of matches
        $winPercentage = ($strongerTeamWins / $totalMatches) * 100;
        $this->assertGreaterThan(60, $winPercentage, 'Stronger team should win more than 60% of matches');
    }

    public function test_equal_strength_teams_produce_varied_results(): void
    {
        // Arrange
        $team1 = new Team(['name' => 'Team A', 'strength' => 75]);
        $team2 = new Team(['name' => 'Team B', 'strength' => 75]);

        $results = [];
        $totalMatches = 50;

        // Act - Simulate matches with teams having equal strength
        for ($i = 0; $i < $totalMatches; $i++) {
            $result = $this->matchSimulator->simulateMatch($team1, $team2);
            $results[] = $result['home_score'] . '-' . $result['away_score'];
        }

        // Assert - Should have some variety in results (not all the same)
        $uniqueResults = array_unique($results);
        $this->assertGreaterThan(5, count($uniqueResults), 'Should have varied results with equal strength teams');
        
        // All scores should be valid
        foreach ($results as $result) {
            $scores = explode('-', $result);
            $this->assertCount(2, $scores, 'Result should have two scores');
            $this->assertTrue(is_numeric($scores[0]), 'Home score should be numeric');
            $this->assertTrue(is_numeric($scores[1]), 'Away score should be numeric');
        }
    }

    public function test_multiple_simulations_produce_varied_results(): void
    {
        // Arrange
        $homeTeam = new Team(['name' => 'Chelsea', 'strength' => 85]);
        $awayTeam = new Team(['name' => 'Arsenal', 'strength' => 80]);
        
        $results = [];

        // Act - Run multiple simulations
        for ($i = 0; $i < 20; $i++) {
            $result = $this->matchSimulator->simulateMatch($homeTeam, $awayTeam);
            $results[] = $result['home_score'] . '-' . $result['away_score'];
        }

        // Assert - Should have some variation in results (not all identical)
        $uniqueResults = array_unique($results);
        $this->assertGreaterThan(3, count($uniqueResults), 'Should have varied match results');
    }

    public function test_score_generation_within_realistic_bounds(): void
    {
        // Arrange
        $team1 = new Team(['name' => 'Team 1', 'strength' => 90]);
        $team2 = new Team(['name' => 'Team 2', 'strength' => 50]);

        // Act & Assert - Run multiple tests to ensure scores stay realistic
        for ($i = 0; $i < 50; $i++) {
            $result = $this->matchSimulator->simulateMatch($team1, $team2);
            
            $this->assertLessThanOrEqual(6, $result['home_score'], 'Home score should be realistic');
            $this->assertLessThanOrEqual(6, $result['away_score'], 'Away score should be realistic');
            $this->assertGreaterThanOrEqual(0, $result['home_score'], 'Home score should be non-negative');
            $this->assertGreaterThanOrEqual(0, $result['away_score'], 'Away score should be non-negative');
        }
    }
}
