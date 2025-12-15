<?php

namespace App\Http\Controllers;

use App\Application\Services\MatchManagementService;
use App\Http\Requests\UpdateMatchResultRequest;
use App\Http\Responses\WeeklyResultsResponse;
use Illuminate\Http\JsonResponse;

final class MatchManagementController extends Controller
{
    private MatchManagementService $matchManagementService;

    public function __construct(MatchManagementService $matchManagementService)
    {
        $this->matchManagementService = $matchManagementService;
    }

    public function getWeeklyResults(int $week): JsonResponse
    {
        $matches = $this->matchManagementService->getWeeklyResults($week);
        $matchesArray = array_map(fn($match) => $match->toArray(), $matches);
        
        $response = new WeeklyResultsResponse($week, $matchesArray);
        return $response->toJsonResponse();
    }

    public function updateMatchResult(UpdateMatchResultRequest $request, int $matchId): JsonResponse
    {
        try {
            $matchDTO = $this->matchManagementService->updateMatchResult(
                $matchId,
                $request->getHomeScore(),
                $request->getAwayScore()
            );

            return response()->json([
                'data' => [
                    'message' => 'Match result updated successfully',
                    'match' => $matchDTO->toArray(),
                ],
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update match: ' . $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }
} 