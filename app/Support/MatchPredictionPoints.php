<?php

namespace App\Support;

use App\Models\MatchPrediction;

class MatchPredictionPoints
{
    public const CORRECT = 1;

    public static function forCorrectPrediction(): int
    {
        return self::CORRECT;
    }

    public static function awarded(MatchPrediction $prediction): int
    {
        $actualOutcome = MatchOutcome::fromFixture($prediction->fixture);

        if ($actualOutcome === null || $prediction->result !== $actualOutcome) {
            return 0;
        }

        return self::forCorrectPrediction();
    }
}
