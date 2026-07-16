<?php

namespace App\Http\Controllers;

use App\Models\LeagueStanding;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the application dashboard with Ekstraklasa standings.
     */
    public function __invoke(): Response
    {
        $leagueId = (int) config('services.api_football.league_id');
        $season = (int) config('services.api_football.season');

        $standings = LeagueStanding::query()
            ->forLeagueSeason($leagueId, $season)
            ->orderedByRank()
            ->get()
            ->map(fn (LeagueStanding $standing): array => [
                'id' => $standing->id,
                'rank' => $standing->rank,
                'team_name' => $standing->team_name,
                'team_logo' => $standing->team_logo,
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
            ]);

        return Inertia::render('Dashboard', [
            'standings' => $standings,
            'league' => [
                'id' => $leagueId,
                'name' => 'Ekstraklasa',
                'season' => $season,
            ],
        ]);
    }
}
