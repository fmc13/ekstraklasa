<?php

namespace App\Actions;

use App\Models\LeagueStanding;
use App\Services\ApiFootball\ApiFootballClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SyncEkstraklasaStandings
{
    public function __construct(private ApiFootballClient $client) {}

    /**
     * @return array{synced: int, season: int, league_id: int, source: string}
     */
    public function handle(?int $season = null): array
    {
        $leagueId = (int) config('services.api_football.league_id');
        $season ??= (int) config('services.api_football.season');

        [$standings, $source] = $this->resolveStandings($leagueId, $season);

        return DB::transaction(function () use ($standings, $leagueId, $season, $source): array {
            LeagueStanding::query()->where('league_id', $leagueId)->delete();

            foreach ($standings as $standing) {
                LeagueStanding::query()->create([
                    'league_id' => $leagueId,
                    'season' => $season,
                    'api_team_id' => (int) $standing['team']['id'],
                    'group_name' => $standing['group'] ?? '',
                    'rank' => (int) $standing['rank'],
                    'team_name' => $standing['team']['name'],
                    'team_logo' => $standing['team']['logo'] ?? null,
                    'points' => (int) $standing['points'],
                    'goals_diff' => (int) $standing['goalsDiff'],
                    'form' => $standing['form'] ?? null,
                    'status' => $standing['status'] ?? null,
                    'description' => $standing['description'] ?? null,
                    'played' => (int) $standing['all']['played'],
                    'win' => (int) $standing['all']['win'],
                    'draw' => (int) $standing['all']['draw'],
                    'lose' => (int) $standing['all']['lose'],
                    'goals_for' => (int) $standing['all']['goals']['for'],
                    'goals_against' => (int) $standing['all']['goals']['against'],
                    'api_updated_at' => isset($standing['update'])
                        ? Carbon::parse($standing['update'])
                        : null,
                ]);
            }

            return [
                'synced' => count($standings),
                'season' => $season,
                'league_id' => $leagueId,
                'source' => $source,
            ];
        });
    }

    /**
     * @return array{0: list<array<string, mixed>>, 1: string}
     */
    private function resolveStandings(int $leagueId, int $season): array
    {
        try {
            $standings = $this->client->standings($leagueId, $season);

            if ($standings !== []) {
                return [$standings, 'api'];
            }
        } catch (RuntimeException $exception) {
            if (! $this->isPlanRestriction($exception) || ! $this->hasBootstrapRoster($season)) {
                throw $exception;
            }

            return [$this->bootstrapStandings(), 'bootstrap'];
        }

        if ($this->hasBootstrapRoster($season)) {
            return [$this->bootstrapStandings(), 'bootstrap'];
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
    private function bootstrapStandings(): array
    {
        /** @var list<array{api_team_id: int, team_name: string, team_logo: string|null}> $teams */
        $teams = json_decode((string) file_get_contents($this->bootstrapRosterPath()), true, 512, JSON_THROW_ON_ERROR);

        $standings = [];

        foreach (array_values($teams) as $index => $team) {
            $standings[] = [
                'rank' => $index + 1,
                'team' => [
                    'id' => $team['api_team_id'],
                    'name' => $team['team_name'],
                    'logo' => $team['team_logo'] ?? null,
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
                'update' => now()->toIso8601String(),
            ];
        }

        return $standings;
    }
}
