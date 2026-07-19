<?php

use App\Models\Team;
use App\Models\TeamPlayer;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get(route('players.index'))->assertRedirect(route('login'));
});

test('authenticated users can browse players by surname letter', function () {
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

    TeamPlayer::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 347,
        'api_player_id' => 1001,
        'name' => 'Mikael Ishak',
        'position' => 'Attacker',
        'photo' => 'https://media.api-sports.io/football/players/1001.png',
    ]);

    TeamPlayer::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 347,
        'api_player_id' => 1002,
        'name' => 'Joel Pereira',
        'position' => 'Goalkeeper',
        'photo' => 'https://media.api-sports.io/football/players/1002.png',
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('players.index', ['letter' => 'I']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('players/Index')
            ->where('letter', 'I')
            ->where('letters', ['I', 'P'])
            ->has('players', 1)
            ->where('players.0.name', 'Mikael Ishak')
            ->where('players.0.team.name', 'Lech Poznan')
            ->where('players.0.position', 'Attacker')
        );
});

test('authenticated users can view a player profile page', function () {
    config([
        'services.api_football.league_id' => 106,
        'services.api_football.season' => 2026,
        'services.api_football.key' => 'test-widget-key',
    ]);

    Team::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 347,
        'name' => 'Lech Poznan',
        'logo' => 'https://media.api-sports.io/football/teams/347.png',
    ]);

    TeamPlayer::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 347,
        'api_player_id' => 1001,
        'name' => 'Mikael Ishak',
        'position' => 'Attacker',
        'age' => 31,
        'number' => 9,
        'photo' => 'https://media.api-sports.io/football/players/1001.png',
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('players.show', ['player' => 1001]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('players/Show')
            ->where('player.api_player_id', 1001)
            ->where('player.name', 'Mikael Ishak')
            ->where('player.team.name', 'Lech Poznan')
            ->where('widgetApiKey', 'test-widget-key')
            ->where('league.season', 2026)
        );
});

test('missing players return not found', function () {
    config([
        'services.api_football.league_id' => 106,
        'services.api_football.season' => 2026,
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('players.show', ['player' => 999999]))->assertNotFound();
});
