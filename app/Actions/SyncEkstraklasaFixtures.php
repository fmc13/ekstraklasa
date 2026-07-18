<?php

namespace App\Actions;

use App\Models\Fixture;
use App\Services\ApiFootball\ApiFootballClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SyncEkstraklasaFixtures
{
    public function __construct(private ApiFootballClient $client) {}

    /**
     * @return array{synced: int, season: int, league_id: int, source: string}
     */
    public function handle(?int $season = null): array
    {
        $leagueId = (int) config('services.api_football.league_id');
        $season ??= (int) config('services.api_football.season');

        [$fixtures, $source] = $this->resolveFixtures($leagueId, $season);

        return DB::transaction(function () use ($fixtures, $leagueId, $season, $source): array {
            if ($fixtures === []) {
                return [
                    'synced' => 0,
                    'season' => $season,
                    'league_id' => $leagueId,
                    'source' => $source,
                ];
            }

            Fixture::query()->where('league_id', $leagueId)->where('season', $season)->delete();

            foreach ($fixtures as $row) {
                $round = $row['league']['round'] ?? '';

                Fixture::query()->create([
                    'league_id' => $leagueId,
                    'season' => $season,
                    'api_fixture_id' => (int) $row['fixture']['id'],
                    'round' => $round,
                    'round_number' => Fixture::roundNumberFromLabel($round),
                    'kickoff_at' => isset($row['fixture']['date'])
                        ? Carbon::parse($row['fixture']['date'])
                        : null,
                    'status_short' => $row['fixture']['status']['short'] ?? null,
                    'status_long' => $row['fixture']['status']['long'] ?? null,
                    'home_team_id' => (int) $row['teams']['home']['id'],
                    'home_team_name' => $row['teams']['home']['name'],
                    'home_team_logo' => $row['teams']['home']['logo'] ?? null,
                    'away_team_id' => (int) $row['teams']['away']['id'],
                    'away_team_name' => $row['teams']['away']['name'],
                    'away_team_logo' => $row['teams']['away']['logo'] ?? null,
                    'home_goals' => $row['goals']['home'] ?? null,
                    'away_goals' => $row['goals']['away'] ?? null,
                    'venue_name' => $row['fixture']['venue']['name'] ?? null,
                    'venue_city' => $row['fixture']['venue']['city'] ?? null,
                ]);
            }

            return [
                'synced' => count($fixtures),
                'season' => $season,
                'league_id' => $leagueId,
                'source' => $source,
            ];
        });
    }

    /**
     * @return array{0: list<array<string, mixed>>, 1: string}
     */
    private function resolveFixtures(int $leagueId, int $season): array
    {
        try {
            $fixtures = $this->client->fixtures($leagueId, $season);

            return [$fixtures, 'api'];
        } catch (RuntimeException $exception) {
            if ($this->isPlanRestriction($exception)) {
                return [[], 'plan_restricted'];
            }

            throw $exception;
        }
    }

    private function isPlanRestriction(RuntimeException $exception): bool
    {
        return str_contains($exception->getMessage(), 'Free plans do not have access to this season');
    }
}
