<?php

namespace App\Http\Controllers;

use App\Models\LeagueStanding;
use App\Models\Team;
use App\Models\TeamPlayer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class PlayerController extends Controller
{
    /**
     * Display Ekstraklasa players filtered by surname letter.
     */
    public function index(Request $request): Response
    {
        $leagueId = (int) config('services.api_football.league_id');
        $season = (int) config('services.api_football.season');

        $validated = $request->validate([
            'letter' => ['sometimes', 'nullable', 'string', 'size:1', 'regex:/^[A-Za-z]$/'],
        ]);

        $teamLookup = $this->teamLookup($leagueId, $season);

        $players = TeamPlayer::query()
            ->forLeagueSeason($leagueId, $season)
            ->get()
            ->sortBy(fn (TeamPlayer $player): array => [
                mb_strtolower($player->surname()),
                mb_strtolower($player->name),
            ])
            ->values();

        $letters = $players
            ->map(fn (TeamPlayer $player): string => $player->surnameLetter())
            ->filter(fn (string $letter): bool => (bool) preg_match('/^[A-Z]$/', $letter))
            ->unique()
            ->sort()
            ->values();

        $requestedLetter = isset($validated['letter'])
            ? strtoupper((string) $validated['letter'])
            : null;

        $letter = $requestedLetter !== null && $letters->contains($requestedLetter)
            ? $requestedLetter
            : ($letters->first() ?? 'A');

        $filteredPlayers = $players
            ->filter(fn (TeamPlayer $player): bool => $player->surnameLetter() === $letter)
            ->map(fn (TeamPlayer $player): array => $this->playerCard($player, $teamLookup))
            ->values();

        return Inertia::render('players/Index', [
            'letter' => $letter,
            'letters' => $letters,
            'players' => $filteredPlayers,
            'league' => [
                'id' => $leagueId,
                'name' => 'Ekstraklasa',
                'season' => $season,
            ],
        ]);
    }

    /**
     * Display a player profile with the API-Sports player widget.
     */
    public function show(int $player): Response
    {
        $leagueId = (int) config('services.api_football.league_id');
        $season = (int) config('services.api_football.season');

        $teamPlayer = TeamPlayer::query()
            ->forLeagueSeason($leagueId, $season)
            ->where('api_player_id', $player)
            ->firstOrFail();

        $teamLookup = $this->teamLookup($leagueId, $season);
        $widgetApiKey = config('services.api_football.key');

        return Inertia::render('players/Show', [
            'player' => $this->playerCard($teamPlayer, $teamLookup),
            'widgetApiKey' => is_string($widgetApiKey) && $widgetApiKey !== '' ? $widgetApiKey : null,
            'league' => [
                'id' => $leagueId,
                'name' => 'Ekstraklasa',
                'season' => $season,
            ],
        ]);
    }

    /**
     * @return Collection<int, array{name: string, logo: string|null}>
     */
    private function teamLookup(int $leagueId, int $season): Collection
    {
        $teams = Team::query()
            ->forLeagueSeason($leagueId, $season)
            ->get(['api_team_id', 'name', 'logo'])
            ->mapWithKeys(fn (Team $team): array => [
                $team->api_team_id => [
                    'name' => $team->name,
                    'logo' => $team->logo,
                ],
            ]);

        if ($teams->isNotEmpty()) {
            return $teams;
        }

        return LeagueStanding::query()
            ->forLeagueSeason($leagueId, $season)
            ->get(['api_team_id', 'team_name', 'team_logo'])
            ->mapWithKeys(fn (LeagueStanding $standing): array => [
                $standing->api_team_id => [
                    'name' => $standing->team_name,
                    'logo' => $standing->team_logo,
                ],
            ]);
    }

    /**
     * @param  Collection<int, array{name: string, logo: string|null}>  $teamLookup
     * @return array{
     *     api_player_id: int,
     *     name: string,
     *     age: int|null,
     *     number: int|null,
     *     position: string|null,
     *     photo: string|null,
     *     team: array{api_team_id: int, name: string|null, logo: string|null}
     * }
     */
    private function playerCard(TeamPlayer $player, Collection $teamLookup): array
    {
        $team = $teamLookup->get($player->api_team_id);

        return [
            'api_player_id' => $player->api_player_id,
            'name' => $player->name,
            'age' => $player->age,
            'number' => $player->number,
            'position' => $player->position,
            'photo' => $player->photo,
            'team' => [
                'api_team_id' => $player->api_team_id,
                'name' => $team['name'] ?? null,
                'logo' => $team['logo'] ?? null,
            ],
        ];
    }
}
