<?php

namespace App\Actions;

use App\Models\LeagueStanding;
use App\Models\Team;
use App\Models\TeamCoach;
use App\Models\TeamPlayer;
use App\Services\ApiFootball\ApiFootballClient;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncEkstraklasaSquads
{
    public function __construct(private ApiFootballClient $client) {}

    /**
     * @return array{players: int, coaches: int, teams: int, season: int, league_id: int}
     */
    public function handle(?int $season = null): array
    {
        $leagueId = (int) config('services.api_football.league_id');
        $season ??= (int) config('services.api_football.season');
        $teamIds = $this->resolveTeamIds($leagueId, $season);

        $playersSynced = 0;
        $coachesSynced = 0;

        foreach ($teamIds as $apiTeamId) {
            $players = $this->fetchSquad($apiTeamId);
            $coaches = $this->fetchCoaches($apiTeamId);

            DB::transaction(function () use ($leagueId, $season, $apiTeamId, $players, $coaches, &$playersSynced, &$coachesSynced): void {
                TeamPlayer::query()
                    ->where('league_id', $leagueId)
                    ->where('season', $season)
                    ->where('api_team_id', $apiTeamId)
                    ->delete();

                TeamCoach::query()
                    ->where('league_id', $leagueId)
                    ->where('season', $season)
                    ->where('api_team_id', $apiTeamId)
                    ->delete();

                foreach ($players as $player) {
                    TeamPlayer::query()->create([
                        'league_id' => $leagueId,
                        'season' => $season,
                        'api_team_id' => $apiTeamId,
                        'api_player_id' => (int) $player['id'],
                        'name' => $player['name'],
                        'age' => isset($player['age']) ? (int) $player['age'] : null,
                        'number' => isset($player['number']) ? (int) $player['number'] : null,
                        'position' => $player['position'] ?? null,
                        'photo' => $player['photo'] ?? null,
                    ]);
                    $playersSynced++;
                }

                foreach ($coaches as $coach) {
                    TeamCoach::query()->create([
                        'league_id' => $leagueId,
                        'season' => $season,
                        'api_team_id' => $apiTeamId,
                        'api_coach_id' => (int) $coach['id'],
                        'name' => $coach['name'],
                        'firstname' => $coach['firstname'] ?? null,
                        'lastname' => $coach['lastname'] ?? null,
                        'age' => isset($coach['age']) ? (int) $coach['age'] : null,
                        'nationality' => $coach['nationality'] ?? null,
                        'photo' => $coach['photo'] ?? null,
                        'career' => $coach['career'] ?? null,
                    ]);
                    $coachesSynced++;
                }
            });
        }

        return [
            'players' => $playersSynced,
            'coaches' => $coachesSynced,
            'teams' => count($teamIds),
            'season' => $season,
            'league_id' => $leagueId,
        ];
    }

    /**
     * @return list<int>
     */
    private function resolveTeamIds(int $leagueId, int $season): array
    {
        $teamIds = Team::query()
            ->forLeagueSeason($leagueId, $season)
            ->orderBy('name')
            ->pluck('api_team_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        if ($teamIds !== []) {
            return $teamIds;
        }

        return LeagueStanding::query()
            ->forLeagueSeason($leagueId, $season)
            ->orderedByRank()
            ->pluck('api_team_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchSquad(int $apiTeamId): array
    {
        try {
            return $this->client->squad($apiTeamId);
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchCoaches(int $apiTeamId): array
    {
        try {
            return $this->client->coaches($apiTeamId);
        } catch (Throwable) {
            return [];
        }
    }
}
