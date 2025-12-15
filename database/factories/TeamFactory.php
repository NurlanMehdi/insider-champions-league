<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $teamNames = [
            'Chelsea',
            'Arsenal', 
            'Manchester City',
            'Liverpool',
            'Manchester United',
            'Tottenham',
            'Newcastle',
            'Brighton'
        ];

        return [
            'name' => $this->faker->unique()->randomElement($teamNames),
            'strength' => $this->faker->numberBetween(60, 95),
            'logo' => null,
        ];
    }

    /**
     * Create a team with specific strength
     */
    public function withStrength(int $strength): static
    {
        return $this->state(fn (array $attributes) => [
            'strength' => $strength,
        ]);
    }

    /**
     * Create a team with specific name
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }
}
