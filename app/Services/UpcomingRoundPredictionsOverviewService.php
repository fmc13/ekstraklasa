<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\MatchPrediction;
use App\Models\User;
use Illuminate\Support\Collection;

class UpcomingRoundPredictionsOverviewService
{
    public function __construct(
        private MatchPredictionService $matchPredictionService,
        private PredictionLeaderboardService $leaderboardService,
    ) {}

    /**
     * @return array{
     *     round_number: int|null,
     *     fixtures: list<array{
     *         id: int,
     *         home_team_name: string,
     *         home_team_logo: string|null,
     *         away_team_name: string,
     *         away_team_logo: string|null,
     *         kickoff_at_label: string,
     *         score_label: string,
     *         is_played: bool,
     *         actual_result: string|null,
     *     }>,
     *     players: list<array{
     *         id: int,
     *         name: string,
     *         surname: string,
     *         points: int,
     *         predictions: list<string|null>,
     *     }>,
     * }
     */
    public function overview(): array
    {
        $fixtures = $this->nearestRoundFixtures();

        if ($fixtures->isEmpty()) {
            return [
                'round_number' => null,
                'fixtures' => [],
                'players' => [],
            ];
        }

        $fixtureIds = $fixtures->pluck('id');

        $predictionsByUserAndFixture = MatchPrediction::query()
            ->whereIn('fixture_id', $fixtureIds)
            ->get(['user_id', 'fixture_id', 'result'])
            ->groupBy('user_id')
            ->map(fn (Collection $userPredictions): Collection => $userPredictions->keyBy('fixture_id'));

        $pointsByUserId = $this->leaderboardService->pointsByUserId();

        $players = User::query()
            ->orderBy('surname')
            ->orderBy('name')
            ->get(['id', 'name', 'surname'])
            ->map(function (User $user) use ($fixtures, $predictionsByUserAndFixture, $pointsByUserId): array {
                $userPredictions = $predictionsByUserAndFixture->get($user->id, collect());

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'surname' => $user->surname,
                    'points' => $pointsByUserId[$user->id] ?? 0,
                    'predictions' => $fixtures
                        ->map(fn (Fixture $fixture): ?string => $userPredictions
                            ->get($fixture->id)
                            ?->result
                            ->value)
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        return [
            'round_number' => $fixtures->first()?->round_number,
            'fixtures' => $fixtures
                ->map(fn (Fixture $fixture): array => $this->matchPredictionService->formatFixtureForOverview($fixture))
                ->values()
                ->all(),
            'players' => $players,
        ];
    }

    /**
     * @return Collection<int, Fixture>
     */
    private function nearestRoundFixtures(): Collection
    {
        $leagueId = (int) config('services.api_football.league_id');
        $season = (int) config('services.api_football.season');

        $fixtures = Fixture::query()
            ->forLeagueSeason($leagueId, $season)
            ->orderByRaw('round_number is null')
            ->orderBy('round_number')
            ->orderBy('kickoff_at')
            ->get();

        $roundNumber = $fixtures
            ->first(fn (Fixture $fixture): bool => $this->matchPredictionService->canPredict($fixture)
                || in_array($fixture->status_short, ['NS', 'TBD', 'PST'], true)
                || ($fixture->kickoff_at !== null && $fixture->kickoff_at->isFuture()))
            ?->round_number
            ?? $fixtures->max('round_number');

        if ($roundNumber === null) {
            return collect();
        }

        return $fixtures
            ->where('round_number', $roundNumber)
            ->values();
    }
}
