<?php

namespace App\Support;

use App\Models\Fixture;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class MatchPredictionWindow
{
    public static function closesAt(Fixture $fixture): ?CarbonInterface
    {
        if ($fixture->kickoff_at === null) {
            return null;
        }

        return Carbon::parse($fixture->kickoff_at);
    }

    public static function isOpen(Fixture $fixture): bool
    {
        if ($fixture->isPlayed()) {
            return false;
        }

        $closesAt = self::closesAt($fixture);

        if ($closesAt === null) {
            return false;
        }

        return now()->lt($closesAt);
    }

    public static function isClosed(Fixture $fixture): bool
    {
        return ! self::isOpen($fixture);
    }
}
