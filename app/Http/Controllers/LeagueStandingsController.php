<?php

namespace App\Http\Controllers;

use App\Application\Services\LeagueStandingsService;
use App\Application\Services\WeekCalculationService;
use App\Http\Responses\LeagueStandingsResponse;
use Illuminate\Http\JsonResponse;

final class LeagueStandingsController extends Controller
{
    private LeagueStandingsService $standingsService;
    private WeekCalculationService $weekCalculationService;

    public function __construct(
        LeagueStandingsService $standingsService,
        WeekCalculationService $weekCalculationService
    ) {
        $this->standingsService = $standingsService;
        $this->weekCalculationService = $weekCalculationService;
    }

    public function index(): JsonResponse
    {
        $currentWeek = $this->weekCalculationService->getCurrentWeek();
        $data = $this->standingsService->getStandingsWithPredictions($currentWeek);
        
        $standingsArray = array_map(fn($team) => $team->toArray(), $data['standings']);
        
        $response = new LeagueStandingsResponse(
            $standingsArray,
            $currentWeek,
            $data['predictions']
        );
        
        return $response->toJsonResponse();
    }
} 