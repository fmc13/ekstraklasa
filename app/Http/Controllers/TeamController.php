<?php

namespace App\Http\Controllers;

use App\Models\LeagueStanding;
use App\Models\Team;
use App\Models\TeamCoach;
use App\Models\TeamPlayer;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    /**
     * Display clubs for the configured Ekstraklasa season.
     */
    public function index(): Response
    {
        $leagueId = (int) config('services.api_football.league_id');
        $season = (int) config('services.api_football.season');

        $teams = Team::query()
            ->forLeagueSeason($leagueId, $season)
            ->orderBy('name')
            ->get()
            ->map(fn (Team $team): array => [
                'api_team_id' => $team->api_team_id,
                'name' => $team->name,
                'logo' => $team->logo,
            ]);

        if ($teams->isEmpty()) {
            $teams = LeagueStanding::query()
                ->forLeagueSeason($leagueId, $season)
                ->orderedByRank()
                ->get()
                ->map(fn (LeagueStanding $standing): array => [
                    'api_team_id' => $standing->api_team_id,
                    'name' => $standing->team_name,
                    'logo' => $standing->team_logo,
                ]);
        }

        return Inertia::render('teams/Index', [
            'teams' => $teams,
            'league' => [
                'id' => $leagueId,
                'name' => 'Ekstraklasa',
                'season' => $season,
            ],
        ]);
    }

    /**
     * Display team details for the configured Ekstraklasa season.
     */
    public function show(int $team): Response
    {
        $leagueId = (int) config('services.api_football.league_id');
        $season = (int) config('services.api_football.season');

        $teamModel = Team::query()
            ->forLeagueSeason($leagueId, $season)
            ->where('api_team_id', $team)
            ->first();

        $standing = LeagueStanding::query()
            ->forLeagueSeason($leagueId, $season)
            ->where('api_team_id', $team)
            ->first();

        if ($teamModel === null && $standing === null) {
            abort(404);
        }

        $players = TeamPlayer::query()
            ->forTeamSeason($leagueId, $season, $team)
            ->orderByRaw("CASE position WHEN 'Goalkeeper' THEN 1 WHEN 'Defender' THEN 2 WHEN 'Midfielder' THEN 3 WHEN 'Attacker' THEN 4 ELSE 5 END")
            ->orderBy('number')
            ->orderBy('name')
            ->get()
            ->map(fn (TeamPlayer $player): array => [
                'api_player_id' => $player->api_player_id,
                'name' => $player->name,
                'age' => $player->age,
                'number' => $player->number,
                'position' => $player->position,
                'photo' => $player->photo,
            ]);

        $coaches = TeamCoach::query()
            ->forTeamSeason($leagueId, $season, $team)
            ->orderBy('name')
            ->get()
            ->map(fn (TeamCoach $coach): array => [
                'api_coach_id' => $coach->api_coach_id,
                'name' => $coach->name,
                'firstname' => $coach->firstname,
                'lastname' => $coach->lastname,
                'age' => $coach->age,
                'nationality' => $coach->nationality,
                'photo' => $coach->photo,
            ]);

        return Inertia::render('teams/Show', [
            'team' => [
                'api_team_id' => $team,
                'name' => $teamModel?->name ?? $standing?->team_name,
                'code' => $teamModel?->code,
                'country' => $teamModel?->country,
                'founded' => $teamModel?->founded,
                'national' => $teamModel?->national ?? false,
                'logo' => $teamModel?->logo ?? $standing?->team_logo,
                'venue' => [
                    'name' => $teamModel?->venue_name,
                    'address' => $teamModel?->venue_address,
                    'city' => $teamModel?->venue_city,
                    'capacity' => $teamModel?->venue_capacity,
                    'surface' => $teamModel?->venue_surface,
                    'image' => $teamModel?->venue_image,
                ],
            ],
            'standing' => $standing === null ? null : [
                'rank' => $standing->rank,
                'played' => $standing->played,
                'win' => $standing->win,
                'draw' => $standing->draw,
                'lose' => $standing->lose,
                'goals_for' => $standing->goals_for,
                'goals_against' => $standing->goals_against,
                'goals_diff' => $standing->goals_diff,
                'points' => $standing->points,
                'form' => $standing->form,
                'description' => $standing->description,
            ],
            'players' => $players,
            'coaches' => $coaches,
            'league' => [
                'id' => $leagueId,
                'name' => 'Ekstraklasa',
                'season' => $season,
            ],
        ]);
    }
}
