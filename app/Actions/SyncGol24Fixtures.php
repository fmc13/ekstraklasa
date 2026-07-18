<?php

namespace App\Actions;

use App\Models\Fixture;
use App\Services\Gol24\Gol24ScheduleClient;
use App\Services\Gol24\Gol24ScheduleParser;
use App\Support\Gol24TeamMatcher;
use Illuminate\Support\Facades\DB;

class SyncGol24Fixtures
{
    public function __construct(
        private Gol24ScheduleClient $client,
        private Gol24ScheduleParser $parser,
        private Gol24TeamMatcher $teamMatcher,
    ) {}

    /**
     * @return array{
     *     created: int,
     *     updated: int,
     *     unchanged: int,
     *     total: int,
     *     season: int,
     *     league_id: int,
     *     source: string
     * }
     */
    public function handle(?string $html = null): array
    {
        $leagueId = (int) config('services.api_football.league_id');
        $season = (int) config('services.api_football.season');
        $sourceHtml = $html ?? $this->client->fetchHtml();
        $parsed = $this->parser->parse($sourceHtml);

        return DB::transaction(function () use ($parsed, $leagueId, $season): array {
            $created = 0;
            $updated = 0;
            $unchanged = 0;

            foreach ($parsed as $row) {
                $home = $this->teamMatcher->find($row['home_team_name']);
                $away = $this->teamMatcher->find($row['away_team_name']);

                $attributes = [
                    'round' => $row['round'],
                    'round_number' => $row['round_number'],
                    'kickoff_at' => $row['kickoff_at'],
                    'status_short' => $row['status_short'],
                    'status_long' => $row['status_long'],
                    'home_team_id' => $home->api_team_id,
                    'home_team_name' => $home->name,
                    'home_team_logo' => $home->logo ?? $row['home_team_logo'],
                    'away_team_id' => $away->api_team_id,
                    'away_team_name' => $away->name,
                    'away_team_logo' => $away->logo ?? $row['away_team_logo'],
                    'home_goals' => $row['home_goals'],
                    'away_goals' => $row['away_goals'],
                    'venue_name' => $home->venue_name,
                    'venue_city' => $home->venue_city,
                ];

                $existing = Fixture::query()
                    ->where('league_id', $leagueId)
                    ->where('season', $season)
                    ->where('api_fixture_id', $row['external_id'])
                    ->first();

                if ($existing === null) {
                    Fixture::query()->create([
                        'league_id' => $leagueId,
                        'season' => $season,
                        'api_fixture_id' => $row['external_id'],
                        ...$attributes,
                    ]);
                    $created++;

                    continue;
                }

                $existing->fill($attributes);

                if ($existing->isDirty()) {
                    $existing->save();
                    $updated++;
                } else {
                    $unchanged++;
                }
            }

            return [
                'created' => $created,
                'updated' => $updated,
                'unchanged' => $unchanged,
                'total' => count($parsed),
                'season' => $season,
                'league_id' => $leagueId,
                'source' => 'gol24',
            ];
        });
    }
}
