<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    /**
     * Display fixtures in a monthly calendar.
     */
    public function index(Request $request): Response
    {
        $leagueId = (int) config('services.api_football.league_id');
        $season = (int) config('services.api_football.season');

        $validated = $request->validate([
            'year' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'month' => ['sometimes', 'integer', 'min:1', 'max:12'],
        ]);

        $year = (int) ($validated['year'] ?? now()->year);
        $month = (int) ($validated['month'] ?? now()->month);

        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $previousMonth = $monthStart->copy()->subMonthNoOverflow();
        $nextMonth = $monthStart->copy()->addMonthNoOverflow();

        $fixtures = Fixture::query()
            ->forLeagueSeason($leagueId, $season)
            ->whereNotNull('kickoff_at')
            ->whereBetween('kickoff_at', [$monthStart, $monthEnd])
            ->orderBy('kickoff_at')
            ->get()
            ->map(fn (Fixture $fixture): array => [
                'id' => $fixture->id,
                'date' => $fixture->kickoff_at?->toDateString(),
                'kickoff_at' => $fixture->kickoff_at?->toIso8601String(),
                'status_short' => $fixture->status_short,
                'home_team_id' => $fixture->home_team_id,
                'home_team_name' => $fixture->home_team_name,
                'home_team_logo' => $fixture->home_team_logo,
                'away_team_id' => $fixture->away_team_id,
                'away_team_name' => $fixture->away_team_name,
                'away_team_logo' => $fixture->away_team_logo,
                'home_goals' => $fixture->home_goals,
                'away_goals' => $fixture->away_goals,
            ])
            ->values();

        return Inertia::render('calendar/Index', [
            'year' => $year,
            'month' => $month,
            'previous' => [
                'year' => $previousMonth->year,
                'month' => $previousMonth->month,
            ],
            'next' => [
                'year' => $nextMonth->year,
                'month' => $nextMonth->month,
            ],
            'fixtures' => $fixtures,
            'league' => [
                'id' => $leagueId,
                'name' => 'Ekstraklasa',
                'season' => $season,
            ],
        ]);
    }
}
