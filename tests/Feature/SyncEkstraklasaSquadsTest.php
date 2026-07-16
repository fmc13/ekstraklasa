<?php

use App\Actions\SyncEkstraklasaSquads;
use App\Models\Team;
use App\Models\TeamCoach;
use App\Models\TeamPlayer;
use Illuminate\Support\Facades\Http;

test('it syncs squads and coaches from api football', function () {
    Http::preventStrayRequests();

    Team::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 347,
        'name' => 'Lech Poznan',
    ]);

    Http::fake([
        'v3.football.api-sports.io/players/squads*' => Http::response([
            'errors' => [],
            'response' => [
                [
                    'team' => [
                        'id' => 347,
                        'name' => 'Lech Poznan',
                    ],
                    'players' => [
                        [
                            'id' => 1001,
                            'name' => 'Mikael Ishak',
                            'age' => 31,
                            'number' => 9,
                            'position' => 'Attacker',
                            'photo' => 'https://media.api-sports.io/football/players/1001.png',
                        ],
                        [
                            'id' => 1002,
                            'name' => 'Bartosz Mrozek',
                            'age' => 24,
                            'number' => 1,
                            'position' => 'Goalkeeper',
                            'photo' => null,
                        ],
                    ],
                ],
            ],
        ]),
        'v3.football.api-sports.io/coachs*' => Http::response([
            'errors' => [],
            'response' => [
                [
                    'id' => 501,
                    'name' => 'Niels Frederiksen',
                    'firstname' => 'Niels',
                    'lastname' => 'Frederiksen',
                    'age' => 53,
                    'nationality' => 'Denmark',
                    'photo' => 'https://media.api-sports.io/football/coachs/501.png',
                    'career' => [
                        [
                            'team' => [
                                'id' => 347,
                                'name' => 'Lech Poznan',
                                'logo' => null,
                            ],
                            'start' => '2024-07-01',
                            'end' => null,
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    config([
        'services.api_football.key' => 'test-key',
        'services.api_football.league_id' => 106,
        'services.api_football.season' => 2026,
    ]);

    $result = app(SyncEkstraklasaSquads::class)->handle();

    expect($result)->toMatchArray([
        'players' => 2,
        'coaches' => 1,
        'teams' => 1,
        'season' => 2026,
        'league_id' => 106,
    ]);

    $this->assertDatabaseHas('team_players', [
        'api_team_id' => 347,
        'api_player_id' => 1001,
        'name' => 'Mikael Ishak',
        'number' => 9,
        'position' => 'Attacker',
        'season' => 2026,
    ]);

    $this->assertDatabaseHas('team_coaches', [
        'api_team_id' => 347,
        'api_coach_id' => 501,
        'name' => 'Niels Frederiksen',
        'nationality' => 'Denmark',
        'season' => 2026,
    ]);

    Http::assertSent(fn ($request): bool => str_contains($request->url(), '/players/squads')
        && $request['team'] == 347);

    Http::assertSent(fn ($request): bool => str_contains($request->url(), '/coachs')
        && $request['team'] == 347);
});

test('it replaces previous squad data during sync', function () {
    Http::preventStrayRequests();

    Team::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 347,
        'name' => 'Lech Poznan',
    ]);

    TeamPlayer::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 347,
        'api_player_id' => 1,
        'name' => 'Old Player',
    ]);

    TeamCoach::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_team_id' => 347,
        'api_coach_id' => 1,
        'name' => 'Old Coach',
    ]);

    Http::fake([
        'v3.football.api-sports.io/players/squads*' => Http::response([
            'errors' => [],
            'response' => [
                [
                    'players' => [
                        [
                            'id' => 2001,
                            'name' => 'New Player',
                            'age' => 22,
                            'number' => 7,
                            'position' => 'Midfielder',
                            'photo' => null,
                        ],
                    ],
                ],
            ],
        ]),
        'v3.football.api-sports.io/coachs*' => Http::response([
            'errors' => [],
            'response' => [
                [
                    'id' => 900,
                    'name' => 'New Coach',
                    'firstname' => 'New',
                    'lastname' => 'Coach',
                    'age' => 45,
                    'nationality' => 'Poland',
                    'photo' => null,
                    'career' => null,
                ],
            ],
        ]),
    ]);

    config([
        'services.api_football.key' => 'test-key',
        'services.api_football.league_id' => 106,
        'services.api_football.season' => 2026,
    ]);

    app(SyncEkstraklasaSquads::class)->handle();

    expect(TeamPlayer::query()->count())->toBe(1);
    expect(TeamCoach::query()->count())->toBe(1);
    $this->assertDatabaseMissing('team_players', ['name' => 'Old Player']);
    $this->assertDatabaseHas('team_players', ['name' => 'New Player']);
    $this->assertDatabaseMissing('team_coaches', ['name' => 'Old Coach']);
    $this->assertDatabaseHas('team_coaches', ['name' => 'New Coach']);
});
