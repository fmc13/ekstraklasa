<?php

use App\Actions\SyncGol24Fixtures;
use App\Models\Fixture;
use App\Models\Team;
use App\Services\Gol24\Gol24ScheduleParser;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.api_football.league_id' => 106,
        'services.api_football.season' => 2026,
        'services.gol24.terminarz_url' => 'https://gol24.pl/ekstraklasa/terminarz/',
    ]);
});

function seedGol24Teams(): void
{
    $teams = [
        [4248, 'Radomiak Radom'],
        [17115, 'Wieczysta Kraków'],
        [347, 'Lech Poznan'],
        [350, 'Cracovia Krakow'],
        [348, 'Pogon Szczecin'],
        [339, 'Legia Warszawa'],
    ];

    foreach ($teams as [$apiTeamId, $name]) {
        Team::factory()->create([
            'league_id' => 106,
            'season' => 2026,
            'api_team_id' => $apiTeamId,
            'name' => $name,
            'logo' => "https://media.api-sports.io/football/teams/{$apiTeamId}.png",
            'venue_name' => "Stadion {$name}",
            'venue_city' => 'Polska',
        ]);
    }
}

test('parser extracts fixtures rounds dates and scores from gol24 html', function () {
    $html = file_get_contents(base_path('tests/Fixtures/gol24-terminarz-sample.html'));
    expect($html)->not->toBeFalse();

    $fixtures = app(Gol24ScheduleParser::class)->parse($html);

    expect($fixtures)->toHaveCount(3)
        ->and($fixtures[0]['external_id'])->toBe(8053627075)
        ->and($fixtures[0]['round_number'])->toBe(1)
        ->and($fixtures[0]['home_team_name'])->toBe('Radomiak Radom')
        ->and($fixtures[0]['away_team_name'])->toBe('Wieczysta Kraków')
        ->and($fixtures[0]['home_goals'])->toBeNull()
        ->and($fixtures[0]['status_short'])->toBe('NS')
        ->and($fixtures[0]['kickoff_at']?->timezone('Europe/Warsaw')->format('Y-m-d H:i'))->toBe('2026-07-24 18:00')
        ->and($fixtures[1]['home_goals'])->toBe(2)
        ->and($fixtures[1]['away_goals'])->toBe(1)
        ->and($fixtures[1]['status_short'])->toBe('FT')
        ->and($fixtures[2]['round_number'])->toBe(2)
        ->and($fixtures[2]['status_short'])->toBe('TBD')
        ->and($fixtures[2]['kickoff_at']?->timezone('Europe/Warsaw')->format('Y-m-d H:i'))->toBe('2026-07-31 00:00');
});

test('it creates fixtures from gol24 html without wiping existing ones', function () {
    seedGol24Teams();

    Fixture::factory()->create([
        'league_id' => 106,
        'season' => 2026,
        'api_fixture_id' => 9_999_999,
        'home_team_name' => 'Existing',
        'away_team_name' => 'Keep Me',
    ]);

    $html = file_get_contents(base_path('tests/Fixtures/gol24-terminarz-sample.html'));
    $result = app(SyncGol24Fixtures::class)->handle($html);

    expect($result['created'])->toBe(3)
        ->and($result['updated'])->toBe(0)
        ->and(Fixture::query()->count())->toBe(4)
        ->and(Fixture::query()->where('api_fixture_id', 8053627075)->exists())->toBeTrue()
        ->and(Fixture::query()->where('api_fixture_id', 9_999_999)->exists())->toBeTrue();

    $lech = Fixture::query()->where('api_fixture_id', 8053627071)->first();

    expect($lech?->home_team_id)->toBe(347)
        ->and($lech?->home_team_name)->toBe('Lech Poznan')
        ->and($lech?->away_team_name)->toBe('Cracovia Krakow')
        ->and($lech?->home_goals)->toBe(2)
        ->and($lech?->away_goals)->toBe(1)
        ->and($lech?->status_short)->toBe('FT');
});

test('it updates only changed fixture fields on subsequent sync', function () {
    seedGol24Teams();

    $html = file_get_contents(base_path('tests/Fixtures/gol24-terminarz-sample.html'));
    app(SyncGol24Fixtures::class)->handle($html);

    $updatedHtml = str_replace(
        'https://gol24.pl/radomiak-radom-wieczysta-krakow/rs/8053627075">-:-</a>',
        'https://gol24.pl/radomiak-radom-wieczysta-krakow/rs/8053627075">1:0</a>',
        $html,
    );

    $result = app(SyncGol24Fixtures::class)->handle($updatedHtml);

    expect($result['created'])->toBe(0)
        ->and($result['updated'])->toBe(1)
        ->and($result['unchanged'])->toBe(2);

    $fixture = Fixture::query()->where('api_fixture_id', 8053627075)->first();

    expect($fixture?->home_goals)->toBe(1)
        ->and($fixture?->away_goals)->toBe(0)
        ->and($fixture?->status_short)->toBe('FT');
});

test('command fetches gol24 page and syncs fixtures', function () {
    seedGol24Teams();
    Http::preventStrayRequests();

    $html = file_get_contents(base_path('tests/Fixtures/gol24-terminarz-sample.html'));

    Http::fake([
        'gol24.pl/ekstraklasa/terminarz/*' => Http::response($html, 200),
    ]);

    $this->artisan('gol24:sync-fixtures')
        ->assertSuccessful();

    expect(Fixture::query()->count())->toBe(3);
});
