<?php

use App\Models\Fixture;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get(route('matches.index'))->assertRedirect(route('login'));
});

test('authenticated users can view matches grouped by round', function () {
    config([
        'services.api_football.league_id' => 106,
        'services.api_football.season' => 2026,
    ]);

    Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_fixture_id' => 1_200_001,
        'round' => 'Regular Season - 1',
        'round_number' => 1,
        'home_team_id' => 347,
        'home_team_name' => 'Lech Poznan',
        'away_team_id' => 339,
        'away_team_name' => 'Legia Warszawa',
        'home_goals' => null,
        'away_goals' => null,
        'status_short' => 'NS',
        'kickoff_at' => now()->addDays(2),
    ]);

    Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_fixture_id' => 1_200_002,
        'round' => 'Regular Season - 2',
        'round_number' => 2,
        'home_team_id' => 339,
        'home_team_name' => 'Legia Warszawa',
        'away_team_id' => 347,
        'away_team_name' => 'Lech Poznan',
        'home_goals' => 1,
        'away_goals' => 0,
        'status_short' => 'FT',
        'kickoff_at' => now()->subDays(5),
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('matches.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('matches/Index')
            ->where('league.season', 2026)
            ->has('rounds', 2)
            ->where('rounds.0.number', 1)
            ->where('rounds.0.fixtures.0.home_team_name', 'Lech Poznan')
            ->where('rounds.1.number', 2)
            ->where('rounds.1.fixtures.0.home_goals', 1)
        );
});
