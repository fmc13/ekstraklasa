<?php

use App\Actions\SyncEkstraklasaFixtures;
use App\Models\Fixture;
use Illuminate\Support\Facades\Http;

test('it syncs ekstraklasa fixtures from api football', function () {
    Http::preventStrayRequests();

    Http::fake([
        'v3.football.api-sports.io/fixtures*' => Http::response([
            'errors' => [],
            'response' => [
                [
                    'fixture' => [
                        'id' => 1_200_001,
                        'date' => '2026-08-02T17:00:00+02:00',
                        'status' => [
                            'long' => 'Not Started',
                            'short' => 'NS',
                        ],
                        'venue' => [
                            'name' => 'Stadion Miejski',
                            'city' => 'Poznań',
                        ],
                    ],
                    'league' => [
                        'id' => 106,
                        'season' => 2026,
                        'round' => 'Regular Season - 1',
                    ],
                    'teams' => [
                        'home' => [
                            'id' => 347,
                            'name' => 'Lech Poznan',
                            'logo' => 'https://media.api-sports.io/football/teams/347.png',
                        ],
                        'away' => [
                            'id' => 339,
                            'name' => 'Legia Warszawa',
                            'logo' => 'https://media.api-sports.io/football/teams/339.png',
                        ],
                    ],
                    'goals' => [
                        'home' => null,
                        'away' => null,
                    ],
                ],
                [
                    'fixture' => [
                        'id' => 1_200_002,
                        'date' => '2026-08-09T17:00:00+02:00',
                        'status' => [
                            'long' => 'Match Finished',
                            'short' => 'FT',
                        ],
                        'venue' => [
                            'name' => 'Stadion Wojska Polskiego',
                            'city' => 'Warszawa',
                        ],
                    ],
                    'league' => [
                        'id' => 106,
                        'season' => 2026,
                        'round' => 'Regular Season - 2',
                    ],
                    'teams' => [
                        'home' => [
                            'id' => 339,
                            'name' => 'Legia Warszawa',
                            'logo' => null,
                        ],
                        'away' => [
                            'id' => 347,
                            'name' => 'Lech Poznan',
                            'logo' => null,
                        ],
                    ],
                    'goals' => [
                        'home' => 2,
                        'away' => 1,
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

    $result = app(SyncEkstraklasaFixtures::class)->handle();

    expect($result)->toMatchArray([
        'synced' => 2,
        'season' => 2026,
        'league_id' => 106,
        'source' => 'api',
    ]);

    expect(Fixture::query()->count())->toBe(2);

    $this->assertDatabaseHas('fixtures', [
        'api_fixture_id' => 1_200_001,
        'round' => 'Regular Season - 1',
        'round_number' => 1,
        'home_team_name' => 'Lech Poznan',
        'away_team_name' => 'Legia Warszawa',
        'status_short' => 'NS',
        'venue_city' => 'Poznań',
    ]);

    $this->assertDatabaseHas('fixtures', [
        'api_fixture_id' => 1_200_002,
        'round_number' => 2,
        'home_goals' => 2,
        'away_goals' => 1,
        'status_short' => 'FT',
    ]);

    Http::assertSent(function ($request): bool {
        return str_contains($request->url(), '/fixtures')
            && $request->hasHeader('x-apisports-key', 'test-key')
            && $request['league'] == 106
            && $request['season'] == 2026;
    });
});

test('it returns empty list when free plan blocks fixtures season', function () {
    Http::preventStrayRequests();

    Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2024,
        'api_fixture_id' => 999,
    ]);

    Http::fake([
        'v3.football.api-sports.io/fixtures*' => Http::response([
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

    $result = app(SyncEkstraklasaFixtures::class)->handle();

    expect($result)->toMatchArray([
        'synced' => 0,
        'source' => 'plan_restricted',
    ]);

    expect(Fixture::query()->count())->toBe(1);
});
