<?php

namespace App\Http\Controllers;

use App\Models\LeagueStanding;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
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
            'league' => [
                'id' => $leagueId,
                'name' => 'Ekstraklasa',
                'season' => $season,
            ],
        ]);
    }
}
