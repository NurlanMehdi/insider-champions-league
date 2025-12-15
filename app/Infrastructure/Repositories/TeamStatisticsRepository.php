<?php

namespace App\Infrastructure\Repositories;

use App\Domain\ValueObjects\TeamStatistics;
use App\Models\Team as TeamModel;

final class TeamStatisticsRepository
{
    public function calculateStatistics(TeamModel $teamModel): TeamStatistics
    {
        $homeMatches = $teamModel->homeMatches()->where('is_played', true)->get();
        $awayMatches = $teamModel->awayMatches()->where('is_played', true)->get();

        $played = $homeMatches->count() + $awayMatches->count();
        $wins = 0;
        $draws = 0;
        $losses = 0;
        $goalsFor = 0;
        $goalsAgainst = 0;

        foreach ($homeMatches as $match) {
            $goalsFor += $match->home_score;
            $goalsAgainst += $match->away_score;
            
            if ($match->home_score > $match->away_score) {
                $wins++;
            } elseif ($match->home_score < $match->away_score) {
                $losses++;
            } else {
                $draws++;
            }
        }

        foreach ($awayMatches as $match) {
            $goalsFor += $match->away_score;
            $goalsAgainst += $match->home_score;
            
            if ($match->away_score > $match->home_score) {
                $wins++;
            } elseif ($match->away_score < $match->home_score) {
                $losses++;
            } else {
                $draws++;
            }
        }

        return new TeamStatistics($played, $wins, $draws, $losses, $goalsFor, $goalsAgainst);
    }
} 