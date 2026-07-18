<?php

namespace Database\Factories;

use App\Enums\MatchPredictionResult;
use App\Models\Fixture;
use App\Models\MatchPrediction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MatchPrediction>
 */
class MatchPredictionFactory extends Factory
{
    protected $model = MatchPrediction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fixture_id' => Fixture::factory(),
            'result' => fake()->randomElement(MatchPredictionResult::cases()),
        ];
    }
}
