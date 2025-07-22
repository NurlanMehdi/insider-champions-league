<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Team;
use App\Models\FootballMatch;

class LeagueApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'PremierLeagueTeamsSeeder']);
    }

    public function test_can_get_league_standings(): void
    {
        $response = $this->getJson('/api/league');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'standings' => [
                             '*' => [
                                 'id',
                                 'name',
                                 'points',
                                 'played',
                                 'wins',
                                 'draws',
                                 'losses',
                                 'goals_for',
                                 'goals_against',
                                 'goal_difference'
                             ]
                         ],
                         'current_week',
                         'predictions'
                     ],
                     'status'
                 ]);

        $data = $response->json('data');
        $this->assertCount(20, $data['standings'], 'Should have 20 Premier League teams');
        $this->assertEquals('success', $response->json('status'));
    }

    public function test_can_get_weekly_results(): void
    {
        $week = 1;
        
        $response = $this->getJson("/api/league/week/{$week}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'week',
                         'matches' => [
                             '*' => [
                                 'id',
                                 'home_team',
                                 'away_team',
                                 'home_score',
                                 'away_score',
                                 'is_played',
                                 'result',
                                 'played_at'
                             ]
                         ]
                     ],
                     'status'
                 ]);

        $data = $response->json('data');
        $this->assertEquals($week, $data['week']);
        $this->assertCount(10, $data['matches'], 'Week should have 10 matches');
    }

    public function test_can_initialize_league(): void
    {
        // Clear existing fixtures
        FootballMatch::truncate();

        $response = $this->postJson('/api/league/initialize');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'data' => [
                         'message' => 'League fixtures generated successfully'
                     ]
                 ]);

        // Verify fixtures were created
        $this->assertEquals(380, FootballMatch::count(), 'Should generate 380 matches');
        $this->assertEquals(1, FootballMatch::min('week'), 'First week should be 1');
        $this->assertEquals(38, FootballMatch::max('week'), 'Last week should be 38');
    }

    public function test_can_simulate_specific_week(): void
    {
        $week = 5;

        $response = $this->postJson('/api/league/simulate-week', ['week' => $week]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'message',
                         'week',
                         'execution_time'
                     ],
                     'status'
                 ]);

        $data = $response->json('data');
        $this->assertEquals($week, $data['week']);
        $this->assertStringContains("Week {$week} simulated successfully", $data['message']);

        // Verify matches were simulated
        $simulatedMatches = FootballMatch::where('week', $week)->where('is_played', true)->count();
        $this->assertEquals(10, $simulatedMatches, 'All 10 matches in the week should be simulated');
    }

    public function test_can_simulate_all_matches(): void
    {
        $response = $this->postJson('/api/league/simulate-all');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'message',
                         'matches_simulated',
                         'execution_time',
                         'average_per_match'
                     ],
                     'status'
                 ]);

        $data = $response->json('data');
        $this->assertGreaterThan(0, $data['matches_simulated']);
        $this->assertStringContains('matches simulated successfully', $data['message']);

        // Verify all matches are now played
        $totalMatches = FootballMatch::count();
        $playedMatches = FootballMatch::where('is_played', true)->count();
        $this->assertEquals($totalMatches, $playedMatches, 'All matches should be simulated');
    }

    public function test_can_reset_league(): void
    {
        // First simulate some matches
        $this->postJson('/api/league/simulate-week', ['week' => 1]);
        
        $response = $this->postJson('/api/league/reset');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'data' => [
                         'message' => 'League reset successfully'
                     ]
                 ]);

        // Verify league was reset
        $playedMatches = FootballMatch::where('is_played', true)->count();
        $this->assertEquals(0, $playedMatches, 'No matches should be played after reset');
        $this->assertEquals(380, FootballMatch::count(), 'All fixtures should be regenerated');
    }

    public function test_get_simulation_progress(): void
    {
        $response = $this->getJson('/api/league/progress');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'total_matches',
                         'played_matches',
                         'unplayed_matches',
                         'progress_percentage',
                         'current_week',
                         'total_weeks',
                         'is_complete'
                     ],
                     'status'
                 ]);

        $data = $response->json('data');
        $this->assertEquals(380, $data['total_matches']);
        $this->assertEquals(38, $data['total_weeks']);
        $this->assertIsNumeric($data['progress_percentage']);
        $this->assertIsBool($data['is_complete']);
    }

    public function test_week_validation_rejects_invalid_weeks(): void
    {
        $response = $this->postJson('/api/league/simulate-week', ['week' => 0]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['week']);

        $response = $this->postJson('/api/league/simulate-week', ['week' => 39]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['week']);

        $response = $this->postJson('/api/league/simulate-week', ['week' => 'invalid']);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['week']);
    }

    public function test_simulate_all_matches_returns_performance_metrics(): void
    {
        $response = $this->postJson('/api/league/simulate-all');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertArrayHasKey('execution_time', $data);
        $this->assertArrayHasKey('average_per_match', $data);
        $this->assertArrayHasKey('matches_simulated', $data);

        // Verify performance is reasonable
        $executionTime = (float) str_replace('s', '', $data['execution_time']);
        $this->assertLessThan(5.0, $executionTime, 'Simulation should be fast');
    }

    public function test_week_endpoint_returns_empty_for_invalid_week(): void
    {
        $response = $this->getJson('/api/league/week/100');

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'week' => 100,
                         'matches' => []
                     ]
                 ]);
    }

    public function test_simulate_already_played_week_does_nothing(): void
    {
        $week = 3;

        // Simulate week first time
        $response1 = $this->postJson('/api/league/simulate-week', ['week' => $week]);
        $response1->assertStatus(200);

        // Simulate same week again
        $response2 = $this->postJson('/api/league/simulate-week', ['week' => $week]);
        $response2->assertStatus(200);

        // Should still report success but not change results
        $playedCount = FootballMatch::where('week', $week)->where('is_played', true)->count();
        $this->assertEquals(10, $playedCount);
    }

    public function test_league_standings_are_sorted_correctly(): void
    {
        // Simulate several weeks to get varied standings
        for ($week = 1; $week <= 5; $week++) {
            $this->postJson('/api/league/simulate-week', ['week' => $week]);
        }

        $response = $this->getJson('/api/league');
        $standings = $response->json('data.standings');

        // Verify standings are sorted by points (descending), then goal difference
        for ($i = 0; $i < count($standings) - 1; $i++) {
            $current = $standings[$i];
            $next = $standings[$i + 1];

            if ($current['points'] === $next['points']) {
                $this->assertGreaterThanOrEqual(
                    $next['goal_difference'],
                    $current['goal_difference'],
                    'Teams with same points should be sorted by goal difference'
                );
            } else {
                $this->assertGreaterThan(
                    $next['points'],
                    $current['points'],
                    'Teams should be sorted by points descending'
                );
            }
        }
    }

    public function test_api_returns_consistent_json_structure(): void
    {
        $endpoints = [
            '/api/league',
            '/api/league/week/1',
            '/api/league/progress'
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            
            $response->assertStatus(200)
                     ->assertJsonStructure([
                         'data',
                         'status'
                     ]);
            
            $this->assertEquals('success', $response->json('status'));
        }
    }

    public function test_simulation_endpoints_handle_errors_gracefully(): void
    {
        // Test with malformed request
        $response = $this->postJson('/api/league/simulate-week', ['invalid' => 'data']);
        
        // Should default to current week and still work
        $response->assertStatus(200);
    }
} 