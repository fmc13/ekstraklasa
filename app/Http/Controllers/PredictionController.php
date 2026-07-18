<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMatchPredictionRequest;
use App\Models\Fixture;
use App\Services\MatchPredictionService;
use App\Services\UpcomingRoundPredictionsOverviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class PredictionController extends Controller
{
    /**
     * Display fixtures for tipping, grouped by matchday.
     */
    public function index(Request $request, MatchPredictionService $matchPredictionService): Response
    {
        $user = $request->user();
        $leagueId = (int) config('services.api_football.league_id');
        $season = (int) config('services.api_football.season');

        $fixtures = Fixture::query()
            ->forLeagueSeason($leagueId, $season)
            ->orderByRaw('round_number is null')
            ->orderBy('round_number')
            ->orderBy('kickoff_at')
            ->get();

        $predictionsByFixtureId = $matchPredictionService->predictionsByFixtureIdForUser($user);

        $rounds = $fixtures
            ->groupBy(fn (Fixture $fixture): string => $fixture->round !== ''
                ? $fixture->round
                : 'Bez kolejki')
            ->map(function ($items, string $round) use ($matchPredictionService, $user, $predictionsByFixtureId) {
                /** @var Collection<int, Fixture> $items */
                $first = $items->first();

                return [
                    'name' => $round,
                    'number' => $first?->round_number,
                    'fixtures' => $items
                        ->map(fn (Fixture $fixture): array => $matchPredictionService->formatFixtureForUser(
                            $fixture,
                            $user,
                            $predictionsByFixtureId,
                        ))
                        ->values(),
                ];
            })
            ->values();

        $currentRound = $fixtures
            ->first(fn (Fixture $fixture): bool => $matchPredictionService->canPredict($fixture)
                || in_array($fixture->status_short, ['NS', 'TBD', 'PST'], true)
                || ($fixture->kickoff_at !== null && $fixture->kickoff_at->isFuture()))
            ?->round_number
            ?? $fixtures->max('round_number');

        return Inertia::render('typowanie/Index', [
            'rounds' => $rounds,
            'currentRound' => $currentRound,
            'league' => [
                'id' => $leagueId,
                'name' => 'Ekstraklasa',
                'season' => $season,
            ],
        ]);
    }

    /**
     * Display all players' predictions for the nearest matchday.
     */
    public function overview(UpcomingRoundPredictionsOverviewService $overviewService): Response
    {
        return Inertia::render('typowanie/Overview', $overviewService->overview());
    }

    /**
     * Store or update the user's prediction for a fixture.
     */
    public function store(
        StoreMatchPredictionRequest $request,
        Fixture $fixture,
        MatchPredictionService $matchPredictionService,
    ): RedirectResponse {
        $matchPredictionService->store(
            $request->user(),
            $fixture,
            $request->result(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Typ został zapisany.')]);

        return back();
    }
}
