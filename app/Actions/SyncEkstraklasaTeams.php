<?php

namespace App\Actions;

use App\Models\Team;
use App\Services\ApiFootball\ApiFootballClient;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SyncEkstraklasaTeams
{
    public function __construct(private ApiFootballClient $client) {}

    /**
     * @return array{synced: int, season: int, league_id: int, source: string}
     */
    public function handle(?int $season = null): array
    {
        $leagueId = (int) config('services.api_football.league_id');
        $season ??= (int) config('services.api_football.season');

        [$teams, $source] = $this->resolveTeams($leagueId, $season);

        return DB::transaction(function () use ($teams, $leagueId, $season, $source): array {
            Team::query()->where('league_id', $leagueId)->delete();

            foreach ($teams as $row) {
                $team = $row['team'];
                $venue = $row['venue'] ?? null;

                Team::query()->create([
                    'league_id' => $leagueId,
                    'season' => $season,
                    'api_team_id' => (int) $team['id'],
                    'name' => $team['name'],
                    'code' => $team['code'] ?? null,
                    'country' => $team['country'] ?? null,
                    'founded' => isset($team['founded']) ? (int) $team['founded'] : null,
                    'national' => (bool) ($team['national'] ?? false),
                    'logo' => $team['logo'] ?? null,
                    'api_venue_id' => isset($venue['id']) ? (int) $venue['id'] : null,
                    'venue_name' => $venue['name'] ?? null,
                    'venue_address' => $venue['address'] ?? null,
                    'venue_city' => $venue['city'] ?? null,
                    'venue_capacity' => isset($venue['capacity']) ? (int) $venue['capacity'] : null,
                    'venue_surface' => $venue['surface'] ?? null,
                    'venue_image' => $venue['image'] ?? null,
                ]);
            }

            return [
                'synced' => count($teams),
                'season' => $season,
                'league_id' => $leagueId,
                'source' => $source,
            ];
        });
    }

    /**
     * @return array{0: list<array<string, mixed>>, 1: string}
     */
    private function resolveTeams(int $leagueId, int $season): array
    {
        try {
            $teams = $this->client->teams($leagueId, $season);

            if ($teams !== []) {
                return [$teams, 'api'];
            }
        } catch (RuntimeException $exception) {
            if (! $this->isPlanRestriction($exception) || ! $this->hasBootstrapRoster($season)) {
                throw $exception;
            }

            return [$this->bootstrapTeams(), 'bootstrap'];
        }

        if ($this->hasBootstrapRoster($season)) {
            return [$this->bootstrapTeams(), 'bootstrap'];
        }

        return [[], 'api'];
    }

    private function isPlanRestriction(RuntimeException $exception): bool
    {
        return str_contains($exception->getMessage(), 'Free plans do not have access to this season');
    }

    private function hasBootstrapRoster(int $season): bool
    {
        return $season === 2026 && is_file($this->bootstrapRosterPath());
    }

    private function bootstrapRosterPath(): string
    {
        return database_path('data/ekstraklasa_2026_teams.json');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function bootstrapTeams(): array
    {
        /** @var list<array{api_team_id: int, team_name: string, team_logo: string|null}> $teams */
        $teams = json_decode((string) file_get_contents($this->bootstrapRosterPath()), true, 512, JSON_THROW_ON_ERROR);

        $rows = [];

        foreach ($teams as $team) {
            $rows[] = [
                'team' => [
                    'id' => $team['api_team_id'],
                    'name' => $team['team_name'],
                    'code' => null,
                    'country' => 'Poland',
                    'founded' => null,
                    'national' => false,
                    'logo' => $team['team_logo'] ?? null,
                ],
                'venue' => null,
            ];
        }

        return $rows;
    }
}
