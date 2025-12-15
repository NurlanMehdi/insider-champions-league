<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;

final class PremierLeagueTeamsSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks for MySQL
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing data
        \App\Models\FootballMatch::truncate();
        Team::truncate();
        
        // Re-enable foreign key checks for MySQL
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $premierLeagueTeams = [
            ['id' => 1, 'name' => 'Arsenal', 'strength' => 88],
            ['id' => 2, 'name' => 'Aston Villa', 'strength' => 78],
            ['id' => 3, 'name' => 'Bournemouth', 'strength' => 72],
            ['id' => 4, 'name' => 'Brentford', 'strength' => 74],
            ['id' => 5, 'name' => 'Brighton and Hove Albion', 'strength' => 76],
            ['id' => 6, 'name' => 'Burnley', 'strength' => 68],
            ['id' => 7, 'name' => 'Chelsea', 'strength' => 85],
            ['id' => 8, 'name' => 'Crystal Palace', 'strength' => 70],
            ['id' => 9, 'name' => 'Everton', 'strength' => 69],
            ['id' => 10, 'name' => 'Fulham', 'strength' => 73],
            ['id' => 11, 'name' => 'Leeds United', 'strength' => 71],
            ['id' => 12, 'name' => 'Liverpool', 'strength' => 92],
            ['id' => 13, 'name' => 'Manchester City', 'strength' => 95],
            ['id' => 14, 'name' => 'Manchester United', 'strength' => 82],
            ['id' => 15, 'name' => 'Newcastle United', 'strength' => 79],
            ['id' => 16, 'name' => 'Nottingham Forest', 'strength' => 67],
            ['id' => 17, 'name' => 'Sunderland', 'strength' => 65],
            ['id' => 18, 'name' => 'Tottenham Hotspur', 'strength' => 81],
            ['id' => 19, 'name' => 'West Ham United', 'strength' => 75],
            ['id' => 20, 'name' => 'Wolverhampton Wanderers', 'strength' => 66],
        ];

        foreach ($premierLeagueTeams as $teamData) {
            Team::create([
                'id' => $teamData['id'],
                'name' => $teamData['name'],
                'strength' => $teamData['strength'],
                'logo' => null,
            ]);
        }
    }
} 