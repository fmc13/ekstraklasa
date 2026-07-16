<?php

use App\Actions\SyncEkstraklasaTeams;
use App\Models\Team;
use Illuminate\Support\Facades\Http;

test('it syncs ekstraklasa teams from api football', function () {
    Http::preventStrayRequests();

    Http::fake([
        'v3.football.api-sports.io/teams*' => Http::response([
            'errors' => [],
            'response' => [
                [
                    'team' => [
                        'id' => 347,
                        'name' => 'Lech Poznan',
                        'code' => 'POZ',
                        'country' => 'Poland',
                        'founded' => 1922,
                        'national' => false,
                        'logo' => 'https://media.api-sports.io/football/teams/347.png',
                    ],
                    'venue' => [
                        'id' => 1380,
                        'name' => 'Stadion Miejski',
                        'address' => 'ul. Bułgarska 17',
                        'city' => 'Poznań',
                        'capacity' => 41664,
                        'surface' => 'grass',
                        'image' => 'https://media.api-sports.io/football/venues/1380.png',
                    ],
                ],
                [
                    'team' => [
                        'id' => 339,
                        'name' => 'Legia Warszawa',
                        'code' => 'LEG',
                        'country' => 'Poland',
                        'founded' => 1916,
                        'national' => false,
                        'logo' => 'https://media.api-sports.io/football/teams/339.png',
                    ],
                    'venue' => [
                        'id' => 1379,
                        'name' => 'Stadion Wojska Polskiego',
                        'address' => 'ul. Łazienkowska 3',
                        'city' => 'Warszawa',
                        'capacity' => 31800,
                        'surface' => 'grass',
                        'image' => 'https://media.api-sports.io/football/venues/1379.png',
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

    $result = app(SyncEkstraklasaTeams::class)->handle();

    expect($result)->toMatchArray([
        'synced' => 2,
        'season' => 2026,
        'league_id' => 106,
        'source' => 'api',
    ]);

    expect(Team::query()->count())->toBe(2);

    $this->assertDatabaseHas('teams', [
        'api_team_id' => 347,
        'name' => 'Lech Poznan',
        'code' => 'POZ',
        'country' => 'Poland',
        'founded' => 1922,
        'venue_name' => 'Stadion Miejski',
        'venue_city' => 'Poznań',
        'venue_capacity' => 41664,
        'season' => 2026,
    ]);

    Http::assertSent(function ($request): bool {
        return str_contains($request->url(), '/teams')
            && $request->hasHeader('x-apisports-key', 'test-key')
            && $request['league'] == 106
            && $request['season'] == 2026;
    });
});

test('it removes stale seasons during team sync', function () {
    Http::preventStrayRequests();

    Team::factory()->create([
        'league_id' => 106,
        'season' => 2024,
        'api_team_id' => 999,
        'name' => 'Old Club',
    ]);

    Http::fake([
        'v3.football.api-sports.io/teams*' => Http::response([
            'errors' => [],
            'response' => [
                [
                    'team' => [
                        'id' => 347,
                        'name' => 'Lech Poznan',
                        'code' => 'POZ',
                        'country' => 'Poland',
                        'founded' => 1922,
                        'national' => false,
                        'logo' => null,
                    ],
                    'venue' => null,
                ],
            ],
        ]),
    ]);

    config([
        'services.api_football.key' => 'test-key',
        'services.api_football.league_id' => 106,
        'services.api_football.season' => 2026,
    ]);

    app(SyncEkstraklasaTeams::class)->handle();

    expect(Team::query()->count())->toBe(1);
    $this->assertDatabaseMissing('teams', ['season' => 2024]);
    $this->assertDatabaseHas('teams', [
        'api_team_id' => 347,
        'season' => 2026,
    ]);
});

test('it bootstraps season 2026 teams when free plan blocks api', function () {
    Http::preventStrayRequests();

    Team::factory()->create([
        'league_id' => 106,
        'season' => 2024,
        'api_team_id' => 1,
        'name' => 'Legacy Team',
    ]);

    Http::fake([
        'v3.football.api-sports.io/teams*' => Http::response([
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

    $result = app(SyncEkstraklasaTeams::class)->handle();

    expect($result)->toMatchArray([
        'synced' => 18,
        'season' => 2026,
        'league_id' => 106,
        'source' => 'bootstrap',
    ]);

    expect(Team::query()->where('season', 2026)->count())->toBe(18);
    $this->assertDatabaseMissing('teams', ['season' => 2024]);
    $this->assertDatabaseHas('teams', [
        'season' => 2026,
        'name' => 'Wieczysta Kraków',
        'country' => 'Poland',
    ]);
});
