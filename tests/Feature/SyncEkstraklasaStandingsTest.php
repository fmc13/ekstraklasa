<?php

use App\Actions\SyncEkstraklasaStandings;
use App\Models\LeagueStanding;
use Illuminate\Support\Facades\Http;

test('it syncs ekstraklasa standings from api football', function () {
    Http::preventStrayRequests();

    Http::fake([
        'v3.football.api-sports.io/standings*' => Http::response([
            'errors' => [],
            'response' => [
                [
                    'league' => [
                        'id' => 106,
                        'name' => 'Ekstraklasa',
                        'season' => 2026,
                        'standings' => [
                            [
                                [
                                    'rank' => 1,
                                    'team' => [
                                        'id' => 347,
                                        'name' => 'Lech Poznan',
                                        'logo' => 'https://media.api-sports.io/football/teams/347.png',
                                    ],
                                    'points' => 10,
                                    'goalsDiff' => 5,
                                    'group' => 'Ekstraklasa',
                                    'form' => 'WDWWD',
                                    'status' => 'same',
                                    'description' => null,
                                    'all' => [
                                        'played' => 4,
                                        'win' => 3,
                                        'draw' => 1,
                                        'lose' => 0,
                                        'goals' => [
                                            'for' => 8,
                                            'against' => 3,
                                        ],
                                    ],
                                    'update' => '2026-08-01T00:00:00+00:00',
                                ],
                                [
                                    'rank' => 2,
                                    'team' => [
                                        'id' => 339,
                                        'name' => 'Legia Warszawa',
                                        'logo' => 'https://media.api-sports.io/football/teams/339.png',
                                    ],
                                    'points' => 7,
                                    'goalsDiff' => 2,
                                    'group' => 'Ekstraklasa',
                                    'form' => 'WWDLW',
                                    'status' => 'same',
                                    'description' => null,
                                    'all' => [
                                        'played' => 4,
                                        'win' => 2,
                                        'draw' => 1,
                                        'lose' => 1,
                                        'goals' => [
                                            'for' => 5,
                                            'against' => 3,
                                        ],
                                    ],
                                    'update' => '2026-08-01T00:00:00+00:00',
                                ],
                            ],
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

    $result = app(SyncEkstraklasaStandings::class)->handle();

    expect($result)->toMatchArray([
        'synced' => 2,
        'season' => 2026,
        'league_id' => 106,
        'source' => 'api',
    ]);

    expect(LeagueStanding::query()->count())->toBe(2);

    $this->assertDatabaseHas('league_standings', [
        'api_team_id' => 347,
        'team_name' => 'Lech Poznan',
        'season' => 2026,
        'rank' => 1,
        'points' => 10,
        'played' => 4,
        'win' => 3,
    ]);

    Http::assertSent(function ($request): bool {
        return str_contains($request->url(), '/standings')
            && $request->hasHeader('x-apisports-key', 'test-key')
            && $request['league'] == 106
            && $request['season'] == 2026;
    });
});

test('it removes stale seasons during sync', function () {
    Http::preventStrayRequests();

    LeagueStanding::factory()->create([
        'league_id' => 106,
        'season' => 2024,
        'api_team_id' => 999,
        'team_name' => 'Old Club',
        'rank' => 18,
    ]);

    Http::fake([
        'v3.football.api-sports.io/standings*' => Http::response([
            'errors' => [],
            'response' => [
                [
                    'league' => [
                        'id' => 106,
                        'season' => 2026,
                        'standings' => [
                            [
                                [
                                    'rank' => 1,
                                    'team' => [
                                        'id' => 347,
                                        'name' => 'Lech Poznan',
                                        'logo' => null,
                                    ],
                                    'points' => 0,
                                    'goalsDiff' => 0,
                                    'group' => 'Ekstraklasa',
                                    'form' => null,
                                    'status' => 'same',
                                    'description' => null,
                                    'all' => [
                                        'played' => 0,
                                        'win' => 0,
                                        'draw' => 0,
                                        'lose' => 0,
                                        'goals' => [
                                            'for' => 0,
                                            'against' => 0,
                                        ],
                                    ],
                                    'update' => '2026-07-16T00:00:00+00:00',
                                ],
                            ],
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

    app(SyncEkstraklasaStandings::class)->handle();

    expect(LeagueStanding::query()->count())->toBe(1);
    $this->assertDatabaseMissing('league_standings', ['season' => 2024]);
    $this->assertDatabaseHas('league_standings', [
        'api_team_id' => 347,
        'season' => 2026,
    ]);
});

test('it bootstraps season 2026 roster when free plan blocks api', function () {
    Http::preventStrayRequests();

    LeagueStanding::factory()->create([
        'league_id' => 106,
        'season' => 2024,
        'api_team_id' => 1,
        'team_name' => 'Legacy Team',
        'rank' => 1,
    ]);

    Http::fake([
        'v3.football.api-sports.io/standings*' => Http::response([
            'errors' => [
                'plan' => 'Free plans do not have access to this season, try from 2022 to 2024.',
            ],
            'response' => [],
        ]),
    ]);

    config([
        'services.api_football.key' => 'test-key',
        'services.api_football.league_id' => 106,
        'services.api_football.season' => 2026,
    ]);

    $result = app(SyncEkstraklasaStandings::class)->handle();

    expect($result)->toMatchArray([
        'synced' => 18,
        'season' => 2026,
        'league_id' => 106,
        'source' => 'bootstrap',
    ]);

    expect(LeagueStanding::query()->where('season', 2026)->count())->toBe(18);
    $this->assertDatabaseMissing('league_standings', ['season' => 2024]);
    $this->assertDatabaseHas('league_standings', [
        'season' => 2026,
        'team_name' => 'Wieczysta Kraków',
        'points' => 0,
        'played' => 0,
    ]);
});
