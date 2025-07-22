<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Team;
use App\Models\FootballMatch;

class PremierLeagueIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'PremierLeagueTeamsSeeder']);
    }

    public function test_complete_premier_league_season_workflow(): void
    {
        // 1. Initialize league
        $initResponse = $this->postJson('/api/league/initialize');
        $initResponse->assertStatus(200);

        // Verify correct number of teams and fixtures
        $this->assertEquals(20, Team::count(), 'Should have 20 Premier League teams');
        $this->assertEquals(380, FootballMatch::count(), 'Should have 380 total matches');

        // 2. Verify fixture distribution
        for ($week = 1; $week <= 38; $week++) {
            $weekMatches = FootballMatch::where('week', $week)->count();
            $this->assertEquals(10, $weekMatches, "Week {$week} should have 10 matches");
        }

        // 3. Simulate first half of season (weeks 1-19)
        for ($week = 1; $week <= 19; $week++) {
            $response = $this->postJson('/api/league/simulate-week', ['week' => $week]);
            $response->assertStatus(200);

            // Verify week was simulated
            $playedMatches = FootballMatch::where('week', $week)->where('is_played', true)->count();
            $this->assertEquals(10, $playedMatches, "All matches in week {$week} should be simulated");
        }

        // 4. Check standings after first half
        $midSeasonResponse = $this->getJson('/api/league');
        $midSeasonResponse->assertStatus(200);
        
        $standings = $midSeasonResponse->json('data.standings');
        $this->assertCount(20, $standings);

        // Verify each team has played 19 matches (half season)
        foreach ($standings as $team) {
            $this->assertEquals(19, $team['played'], "Each team should have played 19 matches after first half");
        }

        // 5. Simulate second half of season (weeks 20-38) using fast method
        $fastSimResponse = $this->postJson('/api/league/simulate-all');
        $fastSimResponse->assertStatus(200);

        // Verify performance metrics
        $fastSimData = $fastSimResponse->json('data');
        $this->assertArrayHasKey('execution_time', $fastSimData);
        $this->assertArrayHasKey('matches_simulated', $fastSimData);
        $this->assertArrayHasKey('average_per_match', $fastSimData);

        // 6. Check final standings
        $finalResponse = $this->getJson('/api/league');
        $finalResponse->assertStatus(200);
        
        $finalStandings = $finalResponse->json('data.standings');

        // Verify complete season
        foreach ($finalStandings as $team) {
            $this->assertEquals(38, $team['played'], "Each team should have played 38 matches");
            $this->assertGreaterThanOrEqual(0, $team['points']);
            $this->assertGreaterThanOrEqual(0, $team['wins']);
            $this->assertGreaterThanOrEqual(0, $team['draws']);
            $this->assertGreaterThanOrEqual(0, $team['losses']);
        }

        // 7. Verify champion and relegation
        $champion = $finalStandings[0];
        $relegated = array_slice($finalStandings, -3); // Bottom 3

        $this->assertGreaterThan(0, $champion['points'], 'Champion should have points');
        
        foreach ($relegated as $team) {
            $this->assertLessThan($champion['points'], $team['points'], 'Relegated teams should have fewer points than champion');
        }

        // 8. Test season completion status
        $progressResponse = $this->getJson('/api/league/progress');
        $progressData = $progressResponse->json('data');
        
        $this->assertTrue($progressData['is_complete'], 'Season should be complete');
        $this->assertEquals(100.0, $progressData['progress_percentage'], 'Progress should be 100%');
        $this->assertEquals(380, $progressData['played_matches'], 'All matches should be played');
        $this->assertEquals(0, $progressData['unplayed_matches'], 'No unplayed matches should remain');
    }

    public function test_week_by_week_progression_maintains_consistency(): void
    {
        $this->postJson('/api/league/initialize');

        $previousStandings = null;
        
        for ($week = 1; $week <= 10; $week++) {
            // Simulate week
            $this->postJson('/api/league/simulate-week', ['week' => $week]);
            
            // Get standings
            $response = $this->getJson('/api/league');
            $standings = $response->json('data.standings');
            
            // Verify consistency
            foreach ($standings as $team) {
                $this->assertEquals($week, $team['played'], "Team should have played {$week} matches");
                $this->assertEquals($team['wins'] * 3 + $team['draws'], $team['points'], 'Points calculation should be correct');
                $this->assertEquals($team['goals_for'] - $team['goals_against'], $team['goal_difference'], 'Goal difference should be correct');
                $this->assertEquals($team['wins'] + $team['draws'] + $team['losses'], $team['played'], 'Match totals should be consistent');
            }
            
            $previousStandings = $standings;
        }
    }

    public function test_reset_functionality_clears_and_regenerates(): void
    {
        // Initialize and simulate some matches
        $this->postJson('/api/league/initialize');
        $this->postJson('/api/league/simulate-week', ['week' => 1]);
        $this->postJson('/api/league/simulate-week', ['week' => 2]);

        // Verify matches are played
        $playedCount = FootballMatch::where('is_played', true)->count();
        $this->assertEquals(20, $playedCount, 'Should have 20 played matches');

        // Reset league
        $resetResponse = $this->postJson('/api/league/reset');
        $resetResponse->assertStatus(200);

        // Verify reset worked
        $this->assertEquals(0, FootballMatch::where('is_played', true)->count(), 'No matches should be played after reset');
        $this->assertEquals(380, FootballMatch::count(), 'All fixtures should be regenerated');

        // Verify fixtures are properly distributed again
        for ($week = 1; $week <= 38; $week++) {
            $weekMatches = FootballMatch::where('week', $week)->count();
            $this->assertEquals(10, $weekMatches, "Week {$week} should have 10 matches after reset");
        }
    }

    public function test_ultra_fast_simulation_performance(): void
    {
        $this->postJson('/api/league/initialize');

        $startTime = microtime(true);
        $response = $this->postJson('/api/league/simulate-all');
        $endTime = microtime(true);

        $response->assertStatus(200);
        
        $executionTime = $endTime - $startTime;
        $this->assertLessThan(2.0, $executionTime, 'Full season simulation should complete in under 2 seconds');

        $data = $response->json('data');
        $this->assertEquals(380, $data['matches_simulated'], 'Should simulate all 380 matches');
        
        // Verify all matches are played
        $this->assertEquals(380, FootballMatch::where('is_played', true)->count());
    }

    public function test_predictions_appear_after_sufficient_matches(): void
    {
        $this->postJson('/api/league/initialize');

        // Simulate first 9 weeks (predictions should not appear)
        for ($week = 1; $week <= 9; $week++) {
            $this->postJson('/api/league/simulate-week', ['week' => $week]);
        }

        $response = $this->getJson('/api/league');
        $predictions = $response->json('data.predictions');
        $this->assertEmpty($predictions, 'Predictions should not appear before week 10');

        // Simulate week 10 (predictions should appear)
        $this->postJson('/api/league/simulate-week', ['week' => 10]);

        $response = $this->getJson('/api/league');
        $predictions = $response->json('data.predictions');
        $this->assertNotEmpty($predictions, 'Predictions should appear after week 10');
        $this->assertCount(20, $predictions, 'Should have predictions for all teams');

        // Verify prediction structure
        foreach ($predictions as $prediction) {
            $this->assertArrayHasKey('team_id', $prediction);
            $this->assertArrayHasKey('team_name', $prediction);
            $this->assertArrayHasKey('current_points', $prediction);
            $this->assertArrayHasKey('predicted_points', $prediction);
            $this->assertArrayHasKey('predicted_goal_difference', $prediction);
            $this->assertArrayHasKey('predicted_goals_for', $prediction);
        }
    }

    public function test_home_and_away_fixture_distribution(): void
    {
        $this->postJson('/api/league/initialize');

        // Count home and away matches for each team
        $homeMatches = [];
        $awayMatches = [];

        $allMatches = FootballMatch::all();
        foreach ($allMatches as $match) {
            $homeMatches[$match->home_team_id] = ($homeMatches[$match->home_team_id] ?? 0) + 1;
            $awayMatches[$match->away_team_id] = ($awayMatches[$match->away_team_id] ?? 0) + 1;
        }

        // Each team should have exactly 19 home and 19 away matches
        foreach (Team::all() as $team) {
            $this->assertEquals(19, $homeMatches[$team->id], "Team {$team->name} should have 19 home matches");
            $this->assertEquals(19, $awayMatches[$team->id], "Team {$team->name} should have 19 away matches");
        }
    }

    public function test_team_plays_every_other_team_exactly_twice(): void
    {
        $this->postJson('/api/league/initialize');

        $matchups = [];
        $allMatches = FootballMatch::all();
        
        foreach ($allMatches as $match) {
            $home = $match->home_team_id;
            $away = $match->away_team_id;
            
            $key = min($home, $away) . '-' . max($home, $away);
            $matchups[$key] = ($matchups[$key] ?? 0) + 1;
        }

        // Each pair of teams should play exactly twice (home and away)
        $teams = Team::pluck('id')->toArray();
        for ($i = 0; $i < count($teams); $i++) {
            for ($j = $i + 1; $j < count($teams); $j++) {
                $key = $teams[$i] . '-' . $teams[$j];
                $this->assertEquals(2, $matchups[$key], "Teams {$teams[$i]} and {$teams[$j]} should play exactly twice");
            }
        }
    }

    public function test_no_team_plays_multiple_matches_in_same_week(): void
    {
        $this->postJson('/api/league/initialize');

        for ($week = 1; $week <= 38; $week++) {
            $weekMatches = FootballMatch::where('week', $week)->get();
            $teamsInWeek = [];

            foreach ($weekMatches as $match) {
                $this->assertNotContains($match->home_team_id, $teamsInWeek, "Team {$match->home_team_id} appears multiple times in week {$week}");
                $this->assertNotContains($match->away_team_id, $teamsInWeek, "Team {$match->away_team_id} appears multiple times in week {$week}");
                
                $teamsInWeek[] = $match->home_team_id;
                $teamsInWeek[] = $match->away_team_id;
            }
        }
    }

    public function test_realistic_team_strengths_affect_results(): void
    {
        $this->postJson('/api/league/initialize');
        $this->postJson('/api/league/simulate-all');

        $standings = $this->getJson('/api/league')->json('data.standings');

        // Get Manchester City (highest strength: 95) and Sunderland (lowest strength: 65)
        $manCity = collect($standings)->firstWhere('name', 'Manchester City');
        $sunderland = collect($standings)->firstWhere('name', 'Sunderland');

        // Higher strength teams should generally perform better
        // (This is probabilistic, so we check reasonable expectations)
        $this->assertGreaterThan($sunderland['points'], $manCity['points'], 'Man City should typically outperform Sunderland');
    }
} 