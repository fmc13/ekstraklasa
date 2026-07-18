<?php

use App\Models\Fixture;
use App\Support\MatchPredictionWindow;
use Illuminate\Support\Carbon;

test('prediction window is open before kickoff', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-01 17:59:59', 'UTC'));

    $fixture = new Fixture;
    $fixture->home_goals = null;
    $fixture->away_goals = null;
    $fixture->kickoff_at = Carbon::parse('2026-08-01 18:00:00', 'UTC');

    expect(MatchPredictionWindow::isOpen($fixture))->toBeTrue()
        ->and(MatchPredictionWindow::isClosed($fixture))->toBeFalse();
});

test('prediction window closes at kickoff', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-01 18:00:00', 'UTC'));

    $fixture = new Fixture;
    $fixture->home_goals = null;
    $fixture->away_goals = null;
    $fixture->kickoff_at = Carbon::parse('2026-08-01 18:00:00', 'UTC');

    expect(MatchPredictionWindow::isOpen($fixture))->toBeFalse()
        ->and(MatchPredictionWindow::isClosed($fixture))->toBeTrue();
});

test('prediction window is closed for played fixtures', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-01 10:00:00', 'UTC'));

    $fixture = new Fixture;
    $fixture->home_goals = 1;
    $fixture->away_goals = 0;
    $fixture->kickoff_at = Carbon::parse('2026-08-01 18:00:00', 'UTC');

    expect(MatchPredictionWindow::isOpen($fixture))->toBeFalse();
});

test('prediction window is closed when kickoff is missing', function () {
    $fixture = new Fixture;
    $fixture->home_goals = null;
    $fixture->away_goals = null;
    $fixture->kickoff_at = null;

    expect(MatchPredictionWindow::isOpen($fixture))->toBeFalse();
});
