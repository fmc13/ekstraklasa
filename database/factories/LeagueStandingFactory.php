<?php

namespace Database\Factories;

use App\Models\LeagueStanding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeagueStanding>
 */
class LeagueStandingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $played = fake()->numberBetween(10, 34);
        $win = fake()->numberBetween(0, $played);
        $draw = fake()->numberBetween(0, $played - $win);
        $lose = $played - $win - $draw;
        $goalsFor = fake()->numberBetween(5, 70);
        $goalsAgainst = fake()->numberBetween(5, 60);

        return [
            'league_id' => 106,
            'season' => 2026,
            'api_team_id' => fake()->unique()->numberBetween(1, 99999),
            'rank' => fake()->numberBetween(1, 18),
            'team_name' => fake()->company().' FC',
            'team_logo' => 'https://media.api-sports.io/football/teams/'.fake()->numberBetween(1, 999).'.png',
            'points' => ($win * 3) + $draw,
            'goals_diff' => $goalsFor - $goalsAgainst,
            'group_name' => 'Ekstraklasa',
            'form' => fake()->regexify('[WDL]{5}'),
            'status' => 'same',
            'description' => null,
            'played' => $played,
            'win' => $win,
            'draw' => $draw,
            'lose' => $lose,
            'goals_for' => $goalsFor,
            'goals_against' => $goalsAgainst,
            'api_updated_at' => now(),
        ];
    }
}
