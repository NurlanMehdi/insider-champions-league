<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

final class WeeklyResultsResponse
{
    private int $week;
    private array $matches;

    public function __construct(int $week, array $matches)
    {
        $this->week = $week;
        $this->matches = $matches;
    }

    public function toJsonResponse(): JsonResponse
    {
        return response()->json([
            'data' => [
                'week' => $this->week,
                'matches' => $this->matches,
            ],
            'status' => 'success',
        ]);
    }
} 