<?php

namespace Database\Factories;

use App\Models\TeamCoach;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamCoach>
 */
class TeamCoachFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $apiCoachId = fake()->unique()->numberBetween(1, 99999);
        $firstname = fake()->firstName();
        $lastname = fake()->lastName();

        return [
            'league_id' => 106,
            'season' => 2026,
            'api_team_id' => fake()->numberBetween(300, 400),
            'api_coach_id' => $apiCoachId,
            'name' => "{$firstname} {$lastname}",
            'firstname' => $firstname,
            'lastname' => $lastname,
            'age' => fake()->numberBetween(35, 70),
            'nationality' => 'Poland',
            'photo' => 'https://media.api-sports.io/football/coachs/'.$apiCoachId.'.png',
            'career' => [
                [
                    'team' => [
                        'id' => 347,
                        'name' => 'Lech Poznan',
                        'logo' => null,
                    ],
                    'start' => '2024-07-01',
                    'end' => null,
                ],
            ],
        ];
    }
}
