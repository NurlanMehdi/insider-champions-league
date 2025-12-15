<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Team;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = [
            [
                'name' => 'Chelsea',
                'strength' => 85,
            ],
            [
                'name' => 'Arsenal', 
                'strength' => 80,
            ],
            [
                'name' => 'Manchester City',
                'strength' => 90,
            ],
            [
                'name' => 'Liverpool',
                'strength' => 88,
            ]
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }
    }
}
