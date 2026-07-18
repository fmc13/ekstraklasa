<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class FixtureController extends Controller
{
    /**
     * Display league fixtures grouped by matchday.
     */
    public function index(): Response
    {
        $leagueId = (int) config('services.api_football.league_id');
        $season = (int) config('services.api_football.season');

        $fixtures = Fixture::query()
            ->forLeagueSeason($leagueId, $season)
            ->orderByRaw('round_number is null')
            ->orderBy('round_number')
            ->orderBy('kickoff_at')
            ->get();

        $rounds = $fixtures
            ->groupBy(fn (Fixture $fixture): string => $fixture->round !== ''
                ? $fixture->round
                : 'Bez kolejki')
            ->map(function ($items, string $round) {
                /** @var Collection<int, Fixture> $items */
                $first = $items->first();

                return [
                    'name' => $round,
                    'number' => $first?->round_number,
                    'fixtures' => $items->map(fn (Fixture $fixture): array => [
                        'id' => $fixture->id,
                        'api_fixture_id' => $fixture->api_fixture_id,
                        'kickoff_at' => $fixture->kickoff_at?->toIso8601String(),
                        'status_short' => $fixture->status_short,
                        'status_long' => $fixture->status_long,
                        'home_team_id' => $fixture->home_team_id,
                        'home_team_name' => $fixture->home_team_name,
                        'home_team_logo' => $fixture->home_team_logo,
                        'away_team_id' => $fixture->away_team_id,
                        'away_team_name' => $fixture->away_team_name,
                        'away_team_logo' => $fixture->away_team_logo,
                        'home_goals' => $fixture->home_goals,
                        'away_goals' => $fixture->away_goals,
                        'venue_name' => $fixture->venue_name,
                        'venue_city' => $fixture->venue_city,
                    ])->values(),
                ];
            })
            ->values();

        $currentRound = $fixtures
            ->first(fn (Fixture $fixture): bool => in_array($fixture->status_short, ['NS', 'TBD', 'PST'], true)
                || ($fixture->kickoff_at !== null && $fixture->kickoff_at->isFuture()))
            ?->round_number
            ?? $fixtures->max('round_number');

        return Inertia::render('matches/Index', [
            'rounds' => $rounds,
            'currentRound' => $currentRound,
            'league' => [
                'id' => $leagueId,
                'name' => 'Ekstraklasa',
                'season' => $season,
            ],
        ]);
    }
}
