<?php

use App\Models\LeagueStanding;
use App\Models\Team;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('teams.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can view the clubs index', function () {
    config([
        'services.api_football.league_id' => 106,
        'services.api_football.season' => 2026,
    ]);

    Team::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 347,
        'name' => 'Lech Poznan',
        'logo' => 'https://media.api-sports.io/football/teams/347.png',
    ]);

    Team::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 339,
        'name' => 'Legia Warszawa',
        'logo' => 'https://media.api-sports.io/football/teams/339.png',
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('teams.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teams/Index')
            ->where('league.season', 2026)
            ->has('teams', 2)
            ->where('teams.0.name', 'Lech Poznan')
            ->where('teams.0.api_team_id', 347)
            ->where('teams.1.name', 'Legia Warszawa')
        );
});

test('clubs index falls back to standings when teams are missing', function () {
    config([
        'services.api_football.league_id' => 106,
        'services.api_football.season' => 2026,
    ]);

    LeagueStanding::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 339,
        'rank' => 1,
        'team_name' => 'Legia Warszawa',
        'team_logo' => 'https://media.api-sports.io/football/teams/339.png',
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('teams.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teams/Index')
            ->has('teams', 1)
            ->where('teams.0.name', 'Legia Warszawa')
            ->where('teams.0.api_team_id', 339)
        );
});
