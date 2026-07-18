<?php

namespace App\Support;

use App\Enums\MatchPredictionResult;
use App\Models\Fixture;

class MatchOutcome
{
    public static function fromFixture(Fixture $fixture): ?MatchPredictionResult
    {
        if (! $fixture->isPlayed()) {
            return null;
        }

        return self::fromScore((int) $fixture->home_goals, (int) $fixture->away_goals);
    }

    public static function fromScore(int $homeGoals, int $awayGoals): MatchPredictionResult
    {
        if ($homeGoals > $awayGoals) {
            return MatchPredictionResult::Home;
        }

        if ($homeGoals < $awayGoals) {
            return MatchPredictionResult::Away;
        }

        return MatchPredictionResult::Draw;
    }
}
