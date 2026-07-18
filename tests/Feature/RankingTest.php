<?php

use App\Enums\MatchPredictionResult;
use App\Models\Fixture;
use App\Models\MatchPrediction;
use App\Models\User;
use App\Services\PredictionLeaderboardService;

test('guests are redirected to the login page', function () {
    $this->get(route('ranking.index'))->assertRedirect(route('login'));
});

test('authenticated users can view the ranking page', function () {
    $user = User::factory()->create([
        'name' => 'Jan',
        'surname' => 'Kowalski',
    ]);

    $this->actingAs($user)
        ->get(route('ranking.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('ranking/Index')
            ->has('leaderboard', 1)
            ->where('leaderboard.0.name', 'Jan')
            ->where('leaderboard.0.surname', 'Kowalski')
            ->where('leaderboard.0.points', 0)
        );
});

test('leaderboard awards one point for correct prediction and zero for incorrect', function () {
    $user = User::factory()->create([
        'name' => 'Jan',
        'surname' => 'Kowalski',
    ]);

    $correctFixture = Fixture::factory()->create([
        'home_goals' => 2,
        'away_goals' => 1,
        'status_short' => 'FT',
        'kickoff_at' => now()->subDay(),
    ]);

    $incorrectFixture = Fixture::factory()->create([
        'home_goals' => 0,
        'away_goals' => 0,
        'status_short' => 'FT',
        'kickoff_at' => now()->subDays(2),
    ]);

    $unplayedFixture = Fixture::factory()->create([
        'home_goals' => null,
        'away_goals' => null,
        'status_short' => 'NS',
        'kickoff_at' => now()->addDay(),
    ]);

    MatchPrediction::factory()->create([
        'user_id' => $user->id,
        'fixture_id' => $correctFixture->id,
        'result' => MatchPredictionResult::Home,
    ]);

    MatchPrediction::factory()->create([
        'user_id' => $user->id,
        'fixture_id' => $incorrectFixture->id,
        'result' => MatchPredictionResult::Home,
    ]);

    MatchPrediction::factory()->create([
        'user_id' => $user->id,
        'fixture_id' => $unplayedFixture->id,
        'result' => MatchPredictionResult::Away,
    ]);

    $leaderboard = app(PredictionLeaderboardService::class)->leaderboard();

    expect($leaderboard)->toHaveCount(1)
        ->and($leaderboard[0]['name'])->toBe('Jan')
        ->and($leaderboard[0]['surname'])->toBe('Kowalski')
        ->and($leaderboard[0]['predictions_count'])->toBe(3)
        ->and($leaderboard[0]['hits'])->toBe(1)
        ->and($leaderboard[0]['misses'])->toBe(1)
        ->and($leaderboard[0]['accuracy_percent'])->toBe(50.0)
        ->and($leaderboard[0]['points'])->toBe(1)
        ->and($leaderboard[0]['position'])->toBe(1);
});

test('leaderboard ties share the same position', function () {
    $first = User::factory()->create([
        'name' => 'Anna',
        'surname' => 'Nowak',
    ]);

    $second = User::factory()->create([
        'name' => 'Bartek',
        'surname' => 'Kowalski',
    ]);

    $fixture = Fixture::factory()->create([
        'home_goals' => 1,
        'away_goals' => 0,
        'status_short' => 'FT',
        'kickoff_at' => now()->subDay(),
    ]);

    MatchPrediction::factory()->create([
        'user_id' => $first->id,
        'fixture_id' => $fixture->id,
        'result' => MatchPredictionResult::Home,
    ]);

    MatchPrediction::factory()->create([
        'user_id' => $second->id,
        'fixture_id' => $fixture->id,
        'result' => MatchPredictionResult::Home,
    ]);

    $leaderboard = app(PredictionLeaderboardService::class)->leaderboard();

    expect($leaderboard)->toHaveCount(2)
        ->and($leaderboard[0]['points'])->toBe(1)
        ->and($leaderboard[1]['points'])->toBe(1)
        ->and($leaderboard[0]['position'])->toBe(1)
        ->and($leaderboard[1]['position'])->toBe(1);
});
