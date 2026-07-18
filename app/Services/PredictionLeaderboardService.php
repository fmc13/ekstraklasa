<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\MatchPrediction;
use App\Models\User;
use App\Support\MatchOutcome;
use App\Support\MatchPredictionPoints;
use Illuminate\Support\Collection;

class PredictionLeaderboardService
{
    /**
     * @return list<array{
     *     position: int,
     *     name: string,
     *     surname: string,
     *     predictions_count: int,
     *     hits: int,
     *     misses: int,
     *     accuracy_percent: float,
     *     points: int,
     * }>
     */
    public function leaderboard(): array
    {
        $fixturesById = Fixture::query()
            ->whereNotNull('home_goals')
            ->whereNotNull('away_goals')
            ->get()
            ->keyBy('id');

        $playedFixturesCount = $fixturesById->count();

        $predictionsByUser = MatchPrediction::query()
            ->get()
            ->groupBy('user_id');

        $users = User::query()
            ->orderBy('surname')
            ->orderBy('name')
            ->get();

        $entries = [];

        foreach ($users as $user) {
            /** @var Collection<int, MatchPrediction> $userPredictions */
            $userPredictions = $predictionsByUser->get($user->id, collect());

            $points = 0;
            $hits = 0;
            $misses = 0;

            foreach ($userPredictions as $prediction) {
                $fixture = $fixturesById->get($prediction->fixture_id);

                if ($fixture === null) {
                    continue;
                }

                $actualOutcome = MatchOutcome::fromFixture($fixture);

                if ($actualOutcome === null) {
                    continue;
                }

                if ($prediction->result === $actualOutcome) {
                    $points += MatchPredictionPoints::forCorrectPrediction();
                    $hits++;
                } else {
                    $misses++;
                }
            }

            $resolved = $hits + $misses;

            $entries[] = [
                'user_id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'predictions_count' => $userPredictions->count(),
                'hits' => $hits,
                'misses' => $misses,
                'accuracy_percent' => $resolved > 0
                    ? round(($hits / $resolved) * 100, 1)
                    : 0.0,
                'points' => $points,
                'played_fixtures_count' => $playedFixturesCount,
            ];
        }

        usort($entries, function (array $left, array $right): int {
            return $right['points'] <=> $left['points']
                ?: strcasecmp($left['surname'], $right['surname'])
                ?: strcasecmp($left['name'], $right['name']);
        });

        return $this->assignPositions($entries);
    }

    /**
     * @return array<int, int>
     */
    public function pointsByUserId(): array
    {
        $fixturesById = Fixture::query()
            ->whereNotNull('home_goals')
            ->whereNotNull('away_goals')
            ->get()
            ->keyBy('id');

        $predictionsByUser = MatchPrediction::query()
            ->get()
            ->groupBy('user_id');

        $points = [];

        foreach (User::query()->pluck('id') as $userId) {
            $userPredictions = $predictionsByUser->get($userId, collect());
            $userPoints = 0;

            foreach ($userPredictions as $prediction) {
                $fixture = $fixturesById->get($prediction->fixture_id);

                if ($fixture === null) {
                    continue;
                }

                $actualOutcome = MatchOutcome::fromFixture($fixture);

                if ($actualOutcome !== null && $prediction->result === $actualOutcome) {
                    $userPoints += MatchPredictionPoints::forCorrectPrediction();
                }
            }

            $points[$userId] = $userPoints;
        }

        return $points;
    }

    /**
     * @param  list<array{
     *     user_id: int,
     *     name: string,
     *     surname: string,
     *     predictions_count: int,
     *     hits: int,
     *     misses: int,
     *     accuracy_percent: float,
     *     points: int,
     *     played_fixtures_count: int,
     * }>  $entries
     * @return list<array{
     *     position: int,
     *     name: string,
     *     surname: string,
     *     predictions_count: int,
     *     hits: int,
     *     misses: int,
     *     accuracy_percent: float,
     *     points: int,
     * }>
     */
    private function assignPositions(array $entries): array
    {
        $ranked = [];
        $previousPoints = null;
        $position = 0;

        foreach ($entries as $index => $entry) {
            if ($previousPoints === null || $entry['points'] !== $previousPoints) {
                $position = $index + 1;
                $previousPoints = $entry['points'];
            }

            $ranked[] = [
                'position' => $position,
                'name' => $entry['name'],
                'surname' => $entry['surname'],
                'predictions_count' => $entry['predictions_count'],
                'hits' => $entry['hits'],
                'misses' => $entry['misses'],
                'accuracy_percent' => $entry['accuracy_percent'],
                'points' => $entry['points'],
            ];
        }

        return $ranked;
    }
}
