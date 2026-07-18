<?php

namespace Database\Factories;

use App\Models\Fixture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fixture>
 */
class FixtureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roundNumber = fake()->numberBetween(1, 34);
        $homeGoals = fake()->optional(0.6)->numberBetween(0, 4);
        $awayGoals = $homeGoals === null ? null : fake()->numberBetween(0, 4);

        return [
            'league_id' => 106,
            'season' => 2026,
            'api_fixture_id' => fake()->unique()->numberBetween(1_000_000, 9_999_999),
            'round' => "Regular Season - {$roundNumber}",
            'round_number' => $roundNumber,
            'kickoff_at' => fake()->dateTimeBetween('2026-07-01', '2027-05-31'),
            'status_short' => $homeGoals === null ? 'NS' : 'FT',
            'status_long' => $homeGoals === null ? 'Not Started' : 'Match Finished',
            'home_team_id' => fake()->numberBetween(300, 400),
            'home_team_name' => fake()->company().' FC',
            'home_team_logo' => 'https://media.api-sports.io/football/teams/'.fake()->numberBetween(1, 999).'.png',
            'away_team_id' => fake()->numberBetween(300, 400),
            'away_team_name' => fake()->company().' United',
            'away_team_logo' => 'https://media.api-sports.io/football/teams/'.fake()->numberBetween(1, 999).'.png',
            'home_goals' => $homeGoals,
            'away_goals' => $awayGoals,
            'venue_name' => fake()->company().' Stadium',
            'venue_city' => fake()->city(),
        ];
    }
}
