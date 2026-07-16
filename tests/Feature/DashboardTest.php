<?php

use App\Models\LeagueStanding;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard includes ekstraklasa standings from the database', function () {
    config([
        'services.api_football.league_id' => 106,
        'services.api_football.season' => 2026,
    ]);

    LeagueStanding::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 347,
        'rank' => 1,
        'team_name' => 'Lech Poznan',
        'points' => 0,
        'played' => 0,
        'win' => 0,
        'draw' => 0,
        'lose' => 0,
        'goals_for' => 0,
        'goals_against' => 0,
        'goals_diff' => 0,
        'form' => null,
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('league.name', 'Ekstraklasa')
            ->where('league.season', 2026)
            ->has('standings', 1)
            ->where('standings.0.team_name', 'Lech Poznan')
            ->where('standings.0.points', 0)
            ->where('standings.0.rank', 1)
        );
});
