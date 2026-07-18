<?php

use App\Enums\MatchPredictionResult;
use App\Models\Fixture;
use App\Models\MatchPrediction;
use App\Support\MatchOutcome;
use App\Support\MatchPredictionPoints;

test('match outcome resolves home draw and away', function () {
    expect(MatchOutcome::fromScore(2, 1))->toBe(MatchPredictionResult::Home)
        ->and(MatchOutcome::fromScore(1, 1))->toBe(MatchPredictionResult::Draw)
        ->and(MatchOutcome::fromScore(0, 3))->toBe(MatchPredictionResult::Away);
});

test('match outcome is null when fixture has no score', function () {
    $fixture = new Fixture;
    $fixture->home_goals = null;
    $fixture->away_goals = null;

    expect(MatchOutcome::fromFixture($fixture))->toBeNull();
});

test('correct prediction awards one point', function () {
    $fixture = new Fixture;
    $fixture->home_goals = 2;
    $fixture->away_goals = 1;

    $prediction = new MatchPrediction;
    $prediction->result = MatchPredictionResult::Home;
    $prediction->setRelation('fixture', $fixture);

    expect(MatchPredictionPoints::awarded($prediction))->toBe(1);
});

test('incorrect prediction awards zero points', function () {
    $fixture = new Fixture;
    $fixture->home_goals = 0;
    $fixture->away_goals = 0;

    $prediction = new MatchPrediction;
    $prediction->result = MatchPredictionResult::Home;
    $prediction->setRelation('fixture', $fixture);

    expect(MatchPredictionPoints::awarded($prediction))->toBe(0);
});
