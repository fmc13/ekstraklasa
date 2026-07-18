<?php

namespace App\Services;

use App\Enums\MatchPredictionResult;
use App\Models\Fixture;
use App\Models\MatchPrediction;
use App\Models\User;
use App\Support\MatchOutcome;
use App\Support\MatchPredictionPoints;
use App\Support\MatchPredictionWindow;

class MatchPredictionService
{
    public function canPredict(Fixture $fixture): bool
    {
        return MatchPredictionWindow::isOpen($fixture);
    }

    public function store(User $user, Fixture $fixture, MatchPredictionResult $result): MatchPrediction
    {
        return MatchPrediction::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'fixture_id' => $fixture->id,
            ],
            [
                'result' => $result,
            ],
        );
    }

    /**
     * @param  array<int, MatchPrediction>  $predictionsByFixtureId
     * @return array{
     *     id: int,
     *     home_team_id: int,
     *     home_team_name: string,
     *     home_team_logo: string|null,
     *     away_team_id: int,
     *     away_team_name: string,
     *     away_team_logo: string|null,
     *     round: string,
     *     round_number: int|null,
     *     kickoff_at: string|null,
     *     kickoff_at_label: string,
     *     can_predict: bool,
     *     prediction: string|null,
     *     actual_result: string|null,
     *     is_correct: bool|null,
     *     awarded_points: int,
     *     is_played: bool,
     *     score_label: string,
     * }
     */
    public function formatFixtureForUser(
        Fixture $fixture,
        User $user,
        array $predictionsByFixtureId = [],
    ): array {
        $prediction = $predictionsByFixtureId[$fixture->id]
            ?? MatchPrediction::query()
                ->where('user_id', $user->id)
                ->where('fixture_id', $fixture->id)
                ->first();

        $actualOutcome = MatchOutcome::fromFixture($fixture);
        $isCorrect = $prediction && $actualOutcome
            ? $prediction->result === $actualOutcome
            : null;
        $awardedPoints = $isCorrect
            ? MatchPredictionPoints::forCorrectPrediction()
            : 0;

        return [
            'id' => $fixture->id,
            'home_team_id' => $fixture->home_team_id,
            'home_team_name' => $fixture->home_team_name,
            'home_team_logo' => $fixture->home_team_logo,
            'away_team_id' => $fixture->away_team_id,
            'away_team_name' => $fixture->away_team_name,
            'away_team_logo' => $fixture->away_team_logo,
            'round' => $fixture->round,
            'round_number' => $fixture->round_number,
            'kickoff_at' => $fixture->kickoff_at?->toIso8601String(),
            'kickoff_at_label' => $fixture->kickoff_at
                ? $fixture->kickoff_at
                    ->timezone('Europe/Warsaw')
                    ->format('d.m.Y H:i')
                : 'Termin do ustalenia',
            'can_predict' => $this->canPredict($fixture),
            'prediction' => $prediction?->result->value,
            'actual_result' => $actualOutcome?->value,
            'is_correct' => $isCorrect,
            'awarded_points' => $awardedPoints,
            'is_played' => $fixture->isPlayed(),
            'score_label' => $fixture->isPlayed()
                ? "{$fixture->home_goals} : {$fixture->away_goals}"
                : 'vs',
        ];
    }

    /**
     * @return array{
     *     id: int,
     *     home_team_name: string,
     *     home_team_logo: string|null,
     *     away_team_name: string,
     *     away_team_logo: string|null,
     *     kickoff_at_label: string,
     *     score_label: string,
     *     is_played: bool,
     *     actual_result: string|null,
     * }
     */
    public function formatFixtureForOverview(Fixture $fixture): array
    {
        $actualOutcome = MatchOutcome::fromFixture($fixture);

        return [
            'id' => $fixture->id,
            'home_team_name' => $fixture->home_team_name,
            'home_team_logo' => $fixture->home_team_logo,
            'away_team_name' => $fixture->away_team_name,
            'away_team_logo' => $fixture->away_team_logo,
            'kickoff_at_label' => $fixture->kickoff_at
                ? $fixture->kickoff_at
                    ->timezone('Europe/Warsaw')
                    ->format('d.m.Y H:i')
                : 'Termin do ustalenia',
            'score_label' => $fixture->isPlayed()
                ? "{$fixture->home_goals} : {$fixture->away_goals}"
                : 'vs',
            'is_played' => $fixture->isPlayed(),
            'actual_result' => $actualOutcome?->value,
        ];
    }

    /**
     * @return array<int, MatchPrediction>
     */
    public function predictionsByFixtureIdForUser(User $user): array
    {
        return MatchPrediction::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('fixture_id')
            ->all();
    }
}
