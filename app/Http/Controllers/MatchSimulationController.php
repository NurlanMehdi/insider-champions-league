<?php

namespace App\Http\Controllers;

use App\Application\Services\MatchSimulationService;
use App\Application\Services\WeekCalculationService;
use App\Http\Requests\SimulateWeekRequest;
use Illuminate\Http\JsonResponse;

final class MatchSimulationController extends Controller
{
    private MatchSimulationService $simulationService;
    private WeekCalculationService $weekCalculationService;

    public function __construct(
        MatchSimulationService $simulationService,
        WeekCalculationService $weekCalculationService
    ) {
        $this->simulationService = $simulationService;
        $this->weekCalculationService = $weekCalculationService;
    }

    public function simulateWeek(SimulateWeekRequest $request): JsonResponse
    {
        $week = $request->getWeek() ?: $this->weekCalculationService->getCurrentWeek();
        
        try {
            $startTime = microtime(true);
            $this->simulationService->simulateWeek($week);
            $endTime = microtime(true);
            
            return response()->json([
                'data' => [
                    'message' => "Week {$week} simulated successfully",
                    'week' => $week,
                    'execution_time' => round($endTime - $startTime, 2) . 's',
                ],
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to simulate week: ' . $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    public function simulateAllMatches(): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            // Count total unplayed matches for progress
            $totalMatches = $this->getTotalUnplayedMatches();
            
            if ($totalMatches === 0) {
                return response()->json([
                    'data' => [
                        'message' => 'All matches have already been played',
                    ],
                    'status' => 'success',
                ]);
            }

            // Use the optimized fast simulation method
            $this->simulationService->simulateAllRemainingMatchesFast();
            
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            
            return response()->json([
                'data' => [
                    'message' => "All {$totalMatches} matches simulated successfully",
                    'matches_simulated' => $totalMatches,
                    'execution_time' => $executionTime . 's',
                    'average_per_match' => round(($executionTime / $totalMatches) * 1000, 2) . 'ms',
                ],
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to simulate all matches: ' . $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    public function getSimulationProgress(): JsonResponse
    {
        try {
            $totalMatches = 380; // Total Premier League matches
            $playedMatches = $this->getPlayedMatchesCount();
            $unplayedMatches = $totalMatches - $playedMatches;
            $progressPercentage = round(($playedMatches / $totalMatches) * 100, 1);
            $currentWeek = $this->weekCalculationService->getCurrentWeek();
            
            return response()->json([
                'data' => [
                    'total_matches' => $totalMatches,
                    'played_matches' => $playedMatches,
                    'unplayed_matches' => $unplayedMatches,
                    'progress_percentage' => $progressPercentage,
                    'current_week' => $currentWeek,
                    'total_weeks' => 38,
                    'is_complete' => $unplayedMatches === 0,
                ],
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get simulation progress: ' . $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    private function getTotalUnplayedMatches(): int
    {
        return \App\Models\FootballMatch::where('is_played', false)->count();
    }

    private function getPlayedMatchesCount(): int
    {
        return \App\Models\FootballMatch::where('is_played', true)->count();
    }
} 