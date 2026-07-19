<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FixtureController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function (): RedirectResponse {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('mecze', [FixtureController::class, 'index'])->name('matches.index');
    Route::get('typowanie', [PredictionController::class, 'index'])->name('typowanie.index');
    Route::get('typowanie/przeglad', [PredictionController::class, 'overview'])->name('typowanie.overview');
    Route::post('typowanie/{fixture}', [PredictionController::class, 'store'])
        ->whereNumber('fixture')
        ->name('typowanie.store');
    Route::get('ranking', RankingController::class)->name('ranking.index');
    Route::get('teams', [TeamController::class, 'index'])->name('teams.index');
    Route::get('teams/{team}', [TeamController::class, 'show'])
        ->whereNumber('team')
        ->name('teams.show');
    Route::get('players', [PlayerController::class, 'index'])->name('players.index');
    Route::get('players/{player}', [PlayerController::class, 'show'])
        ->whereNumber('player')
        ->name('players.show');

    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });
});

require __DIR__.'/settings.php';
