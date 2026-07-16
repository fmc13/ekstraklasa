<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('football:sync-standings')
    ->dailyAt('23:00')
    ->withoutOverlapping();

Schedule::command('football:sync-teams')
    ->dailyAt('23:00')
    ->withoutOverlapping();

Schedule::command('football:sync-squads')
    ->weeklyOn(3, '23:00')
    ->withoutOverlapping();
