<?php

namespace App\Http\Controllers;

use App\Services\PredictionLeaderboardService;
use Inertia\Inertia;
use Inertia\Response;

class RankingController extends Controller
{
    /**
     * Display the tipping leaderboard.
     */
    public function __invoke(PredictionLeaderboardService $leaderboardService): Response
    {
        return Inertia::render('ranking/Index', [
            'leaderboard' => $leaderboardService->leaderboard(),
        ]);
    }
}
