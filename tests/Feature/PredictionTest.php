<?php

use App\Enums\MatchPredictionResult;
use App\Models\Fixture;
use App\Models\MatchPrediction;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    config([
        'services.api_football.league_id' => 106,
        'services.api_football.season' => 2026,
    ]);
});

test('guests are redirected to the login page', function () {
    $this->get(route('typowanie.index'))->assertRedirect(route('login'));
});

test('authenticated users can view typowanie page with fixtures by round', function () {
    Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_fixture_id' => 1_300_001,
        'round' => 'Regular Season - 1',
        'round_number' => 1,
        'home_goals' => null,
        'away_goals' => null,
        'status_short' => 'NS',
        'kickoff_at' => now()->addDays(2),
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('typowanie.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('typowanie/Index')
            ->has('rounds', 1)
            ->where('rounds.0.number', 1)
            ->where('rounds.0.fixtures.0.can_predict', true)
            ->where('rounds.0.fixtures.0.prediction', null)
        );
});

test('users can save a prediction for an upcoming fixture', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-01 10:00:00', 'UTC'));

    $user = User::factory()->create();
    $fixture = Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'home_goals' => null,
        'away_goals' => null,
        'status_short' => 'NS',
        'kickoff_at' => Carbon::parse('2026-08-01 18:00:00', 'UTC'),
    ]);

    $this->actingAs($user)
        ->from(route('typowanie.index'))
        ->post(route('typowanie.store', $fixture), [
            'result' => MatchPredictionResult::Draw->value,
        ])
        ->assertRedirect(route('typowanie.index'));

    expect(MatchPrediction::query()
        ->where('user_id', $user->id)
        ->where('fixture_id', $fixture->id)
        ->where('result', MatchPredictionResult::Draw->value)
        ->exists())->toBeTrue();
});

test('users can update an existing prediction before kickoff', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-01 10:00:00', 'UTC'));

    $user = User::factory()->create();
    $fixture = Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'home_goals' => null,
        'away_goals' => null,
        'status_short' => 'NS',
        'kickoff_at' => Carbon::parse('2026-08-01 18:00:00', 'UTC'),
    ]);

    MatchPrediction::factory()->create([
        'user_id' => $user->id,
        'fixture_id' => $fixture->id,
        'result' => MatchPredictionResult::Home,
    ]);

    $this->actingAs($user)
        ->from(route('typowanie.index'))
        ->post(route('typowanie.store', $fixture), [
            'result' => MatchPredictionResult::Away->value,
        ])
        ->assertRedirect(route('typowanie.index'));

    $prediction = MatchPrediction::query()
        ->where('user_id', $user->id)
        ->where('fixture_id', $fixture->id)
        ->first();

    expect($prediction?->result)->toBe(MatchPredictionResult::Away);
});

test('users cannot save a prediction at or after kickoff', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-01 18:00:00', 'UTC'));

    $user = User::factory()->create();
    $fixture = Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'home_goals' => null,
        'away_goals' => null,
        'status_short' => 'NS',
        'kickoff_at' => Carbon::parse('2026-08-01 18:00:00', 'UTC'),
    ]);

    $this->actingAs($user)
        ->post(route('typowanie.store', $fixture), [
            'result' => MatchPredictionResult::Draw->value,
        ])
        ->assertForbidden();

    expect(MatchPrediction::query()->where('user_id', $user->id)->where('fixture_id', $fixture->id)->exists())
        ->toBeFalse();
});

test('users cannot save a prediction for a finished fixture', function () {
    $user = User::factory()->create();
    $fixture = Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'home_goals' => 2,
        'away_goals' => 1,
        'status_short' => 'FT',
        'kickoff_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->post(route('typowanie.store', $fixture), [
            'result' => MatchPredictionResult::Home->value,
        ])
        ->assertForbidden();
});

test('typowanie page marks fixtures at kickoff as closed', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-01 18:00:00', 'UTC'));

    Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'home_goals' => null,
        'away_goals' => null,
        'status_short' => 'NS',
        'kickoff_at' => Carbon::parse('2026-08-01 18:00:00', 'UTC'),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('typowanie.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('typowanie/Index')
            ->where('rounds.0.fixtures.0.can_predict', false));
});
