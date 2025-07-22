<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeagueStandingsController;
use App\Http\Controllers\MatchManagementController;
use App\Http\Controllers\MatchSimulationController;
use App\Http\Controllers\LeagueSetupController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('league')->group(function () {
    Route::get('/', [LeagueStandingsController::class, 'index']);
    Route::get('/week/{week}', [MatchManagementController::class, 'getWeeklyResults']);
    Route::get('/progress', [MatchSimulationController::class, 'getSimulationProgress']);
    Route::post('/simulate-week', [MatchSimulationController::class, 'simulateWeek']);
    Route::post('/simulate-all', [MatchSimulationController::class, 'simulateAllMatches']);
    Route::put('/match/{matchId}', [MatchManagementController::class, 'updateMatchResult']);
    Route::post('/reset', [LeagueSetupController::class, 'resetLeague']);
    Route::post('/initialize', [LeagueSetupController::class, 'initializeLeague']);
}); 