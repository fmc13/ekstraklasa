<?php

use App\Models\LeagueStanding;
use App\Models\Team;
use App\Models\TeamCoach;
use App\Models\TeamPlayer;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('teams.show', ['team' => 347]));

    $response->assertRedirect(route('login'));
});

test('authenticated users can view a team page', function () {
    config([
        'services.api_football.league_id' => 106,
        'services.api_football.season' => 2026,
    ]);

    Team::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 347,
        'name' => 'Lech Poznan',
        'code' => 'POZ',
        'country' => 'Poland',
        'founded' => 1922,
        'venue_name' => 'Stadion Miejski',
        'venue_city' => 'Poznań',
        'venue_capacity' => 41664,
    ]);

    LeagueStanding::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 347,
        'rank' => 1,
        'team_name' => 'Lech Poznan',
        'points' => 12,
        'played' => 5,
    ]);

    TeamPlayer::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 347,
        'api_player_id' => 1001,
        'name' => 'Mikael Ishak',
        'number' => 9,
        'position' => 'Attacker',
        'age' => 31,
    ]);

    TeamCoach::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 347,
        'api_coach_id' => 501,
        'name' => 'Niels Frederiksen',
        'nationality' => 'Denmark',
        'age' => 53,
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('teams.show', ['team' => 347]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teams/Show')
            ->where('team.name', 'Lech Poznan')
            ->where('team.code', 'POZ')
            ->where('team.venue.name', 'Stadion Miejski')
            ->where('team.venue.city', 'Poznań')
            ->where('standing.rank', 1)
            ->where('standing.points', 12)
            ->where('league.season', 2026)
            ->has('players', 1)
            ->where('players.0.name', 'Mikael Ishak')
            ->has('coaches', 1)
            ->where('coaches.0.name', 'Niels Frederiksen')
        );
});

test('team page falls back to standing data when team details are missing', function () {
    config([
        'services.api_football.league_id' => 106,
        'services.api_football.season' => 2026,
    ]);

    LeagueStanding::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 339,
        'rank' => 2,
        'team_name' => 'Legia Warszawa',
        'team_logo' => 'https://media.api-sports.io/football/teams/339.png',
        'points' => 9,
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('teams.show', ['team' => 339]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teams/Show')
            ->where('team.name', 'Legia Warszawa')
            ->where('team.logo', 'https://media.api-sports.io/football/teams/339.png')
            ->where('team.venue.name', null)
            ->where('standing.points', 9)
        );
});

test('unknown team returns not found', function () {
    config([
        'services.api_football.league_id' => 106,
        'services.api_football.season' => 2026,
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('teams.show', ['team' => 99999]))->assertNotFound();
});
