<?php

namespace Database\Factories;

use App\Models\TeamPlayer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamPlayer>
 */
class TeamPlayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $apiPlayerId = fake()->unique()->numberBetween(1, 999999);

        return [
            'league_id' => 106,
            'season' => 2026,
            'api_team_id' => fake()->numberBetween(300, 400),
            'api_player_id' => $apiPlayerId,
            'name' => fake()->name(),
            'age' => fake()->numberBetween(17, 38),
            'number' => fake()->optional()->numberBetween(1, 99),
            'position' => fake()->randomElement(['Goalkeeper', 'Defender', 'Midfielder', 'Attacker']),
            'photo' => 'https://media.api-sports.io/football/players/'.$apiPlayerId.'.png',
        ];
    }
}
