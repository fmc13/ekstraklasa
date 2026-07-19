<?php

use App\Models\Fixture;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get(route('calendar.index'))->assertRedirect(route('login'));
});

test('authenticated users can view the monthly calendar', function () {
    config([
        'services.api_football.league_id' => 106,
        'services.api_football.season' => 2026,
    ]);

    Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_fixture_id' => 1_300_001,
        'home_team_name' => 'Lech Poznan',
        'away_team_name' => 'Legia Warszawa',
        'home_goals' => null,
        'away_goals' => null,
        'status_short' => 'NS',
        'kickoff_at' => '2026-08-15 18:00:00',
    ]);

    Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_fixture_id' => 1_300_002,
        'home_team_name' => 'Wisla Krakow',
        'away_team_name' => 'Cracovia',
        'home_goals' => 2,
        'away_goals' => 1,
        'status_short' => 'FT',
        'kickoff_at' => '2026-09-01 17:00:00',
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('calendar.index', ['year' => 2026, 'month' => 8]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('calendar/Index')
            ->where('year', 2026)
            ->where('month', 8)
            ->where('previous.year', 2026)
            ->where('previous.month', 7)
            ->where('next.year', 2026)
            ->where('next.month', 9)
            ->has('fixtures', 1)
            ->where('fixtures.0.home_team_name', 'Lech Poznan')
            ->where('league.season', 2026)
        );
});

test('calendar rejects invalid month values', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('calendar.index', ['year' => 2026, 'month' => 13]))
        ->assertSessionHasErrors('month');
});
