<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

final class LeagueStandingsResponse
{
    private array $standings;
    private int $currentWeek;
    private array $predictions;

    public function __construct(array $standings, int $currentWeek, array $predictions = [])
    {
        $this->standings = $standings;
        $this->currentWeek = $currentWeek;
        $this->predictions = $predictions;
    }

    public function toJsonResponse(): JsonResponse
    {
        return response()->json([
            'data' => [
                'standings' => $this->standings,
                'current_week' => $this->currentWeek,
                'predictions' => $this->predictions,
            ],
            'status' => 'success',
        ]);
    }
} 