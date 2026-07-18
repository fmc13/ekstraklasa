<?php

namespace App\Models;

use Database\Factories\FixtureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $league_id
 * @property int $season
 * @property int $api_fixture_id
 * @property string $round
 * @property int|null $round_number
 * @property Carbon|null $kickoff_at
 * @property string|null $status_short
 * @property string|null $status_long
 * @property int $home_team_id
 * @property string $home_team_name
 * @property string|null $home_team_logo
 * @property int $away_team_id
 * @property string $away_team_name
 * @property string|null $away_team_logo
 * @property int|null $home_goals
 * @property int|null $away_goals
 * @property string|null $venue_name
 * @property string|null $venue_city
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'league_id',
    'season',
    'api_fixture_id',
    'round',
    'round_number',
    'kickoff_at',
    'status_short',
    'status_long',
    'home_team_id',
    'home_team_name',
    'home_team_logo',
    'away_team_id',
    'away_team_name',
    'away_team_logo',
    'home_goals',
    'away_goals',
    'venue_name',
    'venue_city',
])]
class Fixture extends Model
{
    /** @use HasFactory<FixtureFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'league_id' => 'integer',
            'season' => 'integer',
            'api_fixture_id' => 'integer',
            'round_number' => 'integer',
            'kickoff_at' => 'datetime',
            'home_team_id' => 'integer',
            'away_team_id' => 'integer',
            'home_goals' => 'integer',
            'away_goals' => 'integer',
        ];
    }

    /**
     * @param  Builder<Fixture>  $query
     * @return Builder<Fixture>
     */
    public function scopeForLeagueSeason(Builder $query, int $leagueId, int $season): Builder
    {
        return $query
            ->where('league_id', $leagueId)
            ->where('season', $season);
    }

    public static function roundNumberFromLabel(?string $round): ?int
    {
        if ($round === null || $round === '') {
            return null;
        }

        if (preg_match('/(\d+)\s*$/', $round, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }

    public function isPlayed(): bool
    {
        return $this->home_goals !== null && $this->away_goals !== null;
    }
}
