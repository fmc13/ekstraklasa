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

test('guests are redirected from the predictions overview page', function () {
    $this->get(route('typowanie.overview'))->assertRedirect(route('login'));
});

test('authenticated users can view predictions for the nearest round', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 12:00:00', 'UTC'));

    $user = User::factory()->create([
        'name' => 'Jan',
        'surname' => 'Kowalski',
    ]);

    $fixture = Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'round' => 'Regular Season - 1',
        'round_number' => 1,
        'home_team_name' => 'Lech Poznan',
        'away_team_name' => 'Legia Warszawa',
        'home_goals' => null,
        'away_goals' => null,
        'status_short' => 'NS',
        'kickoff_at' => Carbon::parse('2026-07-24 18:00:00', 'UTC'),
    ]);

    Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'round' => 'Regular Season - 2',
        'round_number' => 2,
        'home_goals' => null,
        'away_goals' => null,
        'status_short' => 'NS',
        'kickoff_at' => Carbon::parse('2026-07-31 18:00:00', 'UTC'),
    ]);

    MatchPrediction::factory()->create([
        'user_id' => $user->id,
        'fixture_id' => $fixture->id,
        'result' => MatchPredictionResult::Home,
    ]);

    $this->actingAs($user)
        ->get(route('typowanie.overview'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('typowanie/Overview')
            ->where('round_number', 1)
            ->has('fixtures', 1)
            ->where('fixtures.0.home_team_name', 'Lech Poznan')
            ->has('players', 1)
            ->where('players.0.name', 'Jan')
            ->where('players.0.predictions.0', '1')
        );
});

test('overview skips finished rounds and shows the next open round', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-28 12:00:00', 'UTC'));

    $user = User::factory()->create();

    Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'round_number' => 1,
        'home_goals' => 2,
        'away_goals' => 1,
        'status_short' => 'FT',
        'kickoff_at' => Carbon::parse('2026-07-24 18:00:00', 'UTC'),
    ]);

    Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'round_number' => 2,
        'home_team_name' => 'Pogon Szczecin',
        'away_team_name' => 'Cracovia Krakow',
        'home_goals' => null,
        'away_goals' => null,
        'status_short' => 'NS',
        'kickoff_at' => Carbon::parse('2026-07-31 18:00:00', 'UTC'),
    ]);

    $this->actingAs($user)
        ->get(route('typowanie.overview'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('typowanie/Overview')
            ->where('round_number', 2)
            ->has('fixtures', 1)
            ->where('fixtures.0.home_team_name', 'Pogon Szczecin')
        );
});

test('overview shows dash when a player has no prediction', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 12:00:00', 'UTC'));

    $withTip = User::factory()->create(['name' => 'Ala', 'surname' => 'Nowak']);
    $withoutTip = User::factory()->create(['name' => 'Bartek', 'surname' => 'Zieliński']);

    $fixture = Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'round_number' => 1,
        'home_goals' => null,
        'away_goals' => null,
        'status_short' => 'NS',
        'kickoff_at' => Carbon::parse('2026-07-24 18:00:00', 'UTC'),
    ]);

    MatchPrediction::factory()->create([
        'user_id' => $withTip->id,
        'fixture_id' => $fixture->id,
        'result' => MatchPredictionResult::Draw,
    ]);

    $this->actingAs($withoutTip)
        ->get(route('typowanie.overview'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('typowanie/Overview')
            ->has('players', 2)
            ->where('players.0.predictions.0', 'X')
            ->where('players.1.predictions.0', null)
        );
});
