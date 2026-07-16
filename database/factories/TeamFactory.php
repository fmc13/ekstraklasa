<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
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
        $apiTeamId = fake()->unique()->numberBetween(1, 99999);

        return [
            'league_id' => 106,
            'season' => 2026,
            'api_team_id' => $apiTeamId,
            'name' => fake()->company().' FC',
            'code' => strtoupper(fake()->lexify('???')),
            'country' => 'Poland',
            'founded' => fake()->numberBetween(1900, 2010),
            'national' => false,
            'logo' => 'https://media.api-sports.io/football/teams/'.$apiTeamId.'.png',
            'api_venue_id' => fake()->numberBetween(1000, 9999),
            'venue_name' => fake()->company().' Stadium',
            'venue_address' => fake()->streetAddress(),
            'venue_city' => fake()->city(),
            'venue_capacity' => fake()->numberBetween(5000, 60000),
            'venue_surface' => 'grass',
            'venue_image' => 'https://media.api-sports.io/football/venues/'.fake()->numberBetween(1, 999).'.png',
        ];
    }
}
