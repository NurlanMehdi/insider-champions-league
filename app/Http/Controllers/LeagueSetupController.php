<?php

namespace App\Http\Controllers;

use App\Application\Services\LeagueSetupService;
use Illuminate\Http\JsonResponse;

final class LeagueSetupController extends Controller
{
    private LeagueSetupService $setupService;

    public function __construct(LeagueSetupService $setupService)
    {
        $this->setupService = $setupService;
    }

    public function resetLeague(): JsonResponse
    {
        try {
            $this->setupService->resetLeague();
            
            return response()->json([
                'data' => [
                    'message' => 'League reset successfully',
                ],
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to reset league: ' . $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    public function initializeLeague(): JsonResponse
    {
        try {
            $this->setupService->generateFixtures();
            
            return response()->json([
                'data' => [
                    'message' => 'League fixtures generated successfully',
                ],
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate fixtures: ' . $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }
} 