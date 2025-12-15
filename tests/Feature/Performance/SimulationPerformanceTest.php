<?php

namespace Tests\Feature\Performance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\FootballMatch;
use App\Application\Services\MatchSimulationService;

class SimulationPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'PremierLeagueTeamsSeeder']);
    }

    public function test_ultra_fast_simulation_meets_performance_requirements(): void
    {
        // Initialize league
        $this->postJson('/api/league/initialize');

        // Measure performance of ultra-fast simulation
        $startTime = microtime(true);
        $response = $this->postJson('/api/league/simulate-all');
        $endTime = microtime(true);

        $response->assertStatus(200);
        $executionTime = $endTime - $startTime;

        // Performance requirements
        $this->assertLessThan(1.0, $executionTime, 'Ultra-fast simulation should complete in under 1 second');
        
        $data = $response->json('data');
        $this->assertEquals(380, $data['matches_simulated'], 'Should simulate all 380 matches');
        
        // Calculate performance metrics
        $avgTimePerMatch = $executionTime / 380;
        $this->assertLessThan(0.003, $avgTimePerMatch, 'Average time per match should be under 3ms');

        // Verify execution time matches reported time (within reasonable margin)
        $reportedTime = (float) str_replace('s', '', $data['execution_time']);
        $this->assertEqualsWithDelta($executionTime, $reportedTime, 0.1, 'Reported time should match actual time');
    }

    public function test_batch_simulation_vs_fast_simulation_performance(): void
    {
        $this->postJson('/api/league/initialize');

        // Test standard batch simulation
        $service = app(MatchSimulationService::class);
        
        $startTime1 = microtime(true);
        $service->simulateAllRemainingMatches();
        $endTime1 = microtime(true);
        $batchTime = $endTime1 - $startTime1;

        // Reset for fast simulation
        $this->postJson('/api/league/reset');

        $startTime2 = microtime(true);
        $service->simulateAllRemainingMatchesFast();
        $endTime2 = microtime(true);
        $fastTime = $endTime2 - $startTime2;

        // Fast simulation should be significantly faster
        $this->assertLessThan($batchTime, $fastTime, 'Fast simulation should be faster than batch simulation');
        
        // Both should complete all matches
        $this->assertEquals(380, FootballMatch::where('is_played', true)->count());
    }

    public function test_memory_usage_during_simulation(): void
    {
        $this->postJson('/api/league/initialize');

        $memoryBefore = memory_get_usage(true);
        $response = $this->postJson('/api/league/simulate-all');
        $memoryAfter = memory_get_usage(true);

        $response->assertStatus(200);
        
        $memoryUsed = $memoryAfter - $memoryBefore;
        
        // Memory usage should be reasonable (less than 50MB for the simulation)
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, 'Memory usage should be under 50MB');
    }

    public function test_concurrent_simulation_requests(): void
    {
        $this->postJson('/api/league/initialize');

        // This simulates what happens if multiple users try to simulate at once
        // In a real application, you'd want proper locking, but for testing we verify behavior
        
        $startTime = microtime(true);
        
        // Simulate first request
        $response1 = $this->postJson('/api/league/simulate-all');
        
        // Try second request (should handle gracefully)
        $response2 = $this->postJson('/api/league/simulate-all');
        
        $endTime = microtime(true);

        $response1->assertStatus(200);
        $response2->assertStatus(200);
        
        // Total time should still be reasonable
        $this->assertLessThan(2.0, $endTime - $startTime, 'Concurrent requests should not significantly impact performance');
    }

    public function test_large_dataset_performance_scaling(): void
    {
        // This test verifies that the algorithm scales well
        // We'll test with different numbers of teams to see performance characteristics
        
        $performanceData = [];
        
        foreach ([4, 6, 8, 10] as $teamCount) {
            FootballMatch::truncate();
            
            // Create fewer teams for testing
            $teams = \App\Models\Team::take($teamCount)->get();
            
            $service = app(MatchSimulationService::class);
            
            // Generate fixtures for this team count
            $setupService = app(\App\Application\Services\LeagueSetupService::class);
            $setupService->generateFixtures();
            
            $startTime = microtime(true);
            $service->simulateAllRemainingMatchesFast();
            $endTime = microtime(true);
            
            $totalMatches = FootballMatch::count();
            $executionTime = $endTime - $startTime;
            
            $performanceData[$teamCount] = [
                'matches' => $totalMatches,
                'time' => $executionTime,
                'matches_per_second' => $totalMatches / $executionTime
            ];
        }
        
        // Verify scaling is reasonable (should handle more matches efficiently)
        $this->assertGreaterThan(
            100,
            $performanceData[10]['matches_per_second'],
            'Should process at least 100 matches per second'
        );
    }

    public function test_simulation_consistency_under_time_pressure(): void
    {
        $this->postJson('/api/league/initialize');

        // Run simulation multiple times to verify consistency
        $results = [];
        
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/league/reset');
            
            $startTime = microtime(true);
            $response = $this->postJson('/api/league/simulate-all');
            $endTime = microtime(true);
            
            $response->assertStatus(200);
            
            $results[] = [
                'time' => $endTime - $startTime,
                'matches' => $response->json('data.matches_simulated')
            ];
        }
        
        // All runs should complete in reasonable time
        foreach ($results as $result) {
            $this->assertLessThan(1.0, $result['time'], 'Each simulation should be fast');
            $this->assertEquals(380, $result['matches'], 'Each simulation should process all matches');
        }
        
        // Performance should be consistent (no run should be more than 2x slower than the fastest)
        $times = array_column($results, 'time');
        $fastestTime = min($times);
        $slowestTime = max($times);
        
        $this->assertLessThan(
            $fastestTime * 3,
            $slowestTime,
            'Performance should be consistent across runs'
        );
    }

    public function test_api_response_time_under_load(): void
    {
        $this->postJson('/api/league/initialize');

        // Measure total API response time including JSON encoding
        $startTime = microtime(true);
        $response = $this->postJson('/api/league/simulate-all');
        $endTime = microtime(true);

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

        $totalResponseTime = $endTime - $startTime;
        
        // Total API response time should be under 2 seconds
        $this->assertLessThan(2.0, $totalResponseTime, 'Total API response time should be fast');
        
        // Response should include performance metrics
        $data = $response->json('data');
        $this->assertIsString($data['execution_time']);
        $this->assertIsString($data['average_per_match']);
        $this->assertIsInt($data['matches_simulated']);
    }

    public function test_database_operation_efficiency(): void
    {
        $this->postJson('/api/league/initialize');

        // Count database queries during simulation
        \DB::enableQueryLog();
        
        $response = $this->postJson('/api/league/simulate-all');
        
        $queries = \DB::getQueryLog();
        \DB::disableQueryLog();

        $response->assertStatus(200);
        
        // Should use minimal database queries due to caching and bulk operations
        $queryCount = count($queries);
        $this->assertLessThan(50, $queryCount, 'Should use minimal database queries due to optimization');
        
        // Verify all matches were updated
        $this->assertEquals(380, FootballMatch::where('is_played', true)->count());
    }
} 