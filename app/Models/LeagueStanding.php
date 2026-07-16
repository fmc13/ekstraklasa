<?php

namespace App\Models;

use Database\Factories\LeagueStandingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $league_id
 * @property int $season
 * @property int $api_team_id
 * @property int $rank
 * @property string $team_name
 * @property string|null $team_logo
 * @property int $points
 * @property int $goals_diff
 * @property string $group_name
 * @property string|null $form
 * @property string|null $status
 * @property string|null $description
 * @property int $played
 * @property int $win
 * @property int $draw
 * @property int $lose
 * @property int $goals_for
 * @property int $goals_against
 * @property Carbon|null $api_updated_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'league_id',
    'season',
    'api_team_id',
    'rank',
    'team_name',
    'team_logo',
    'points',
    'goals_diff',
    'group_name',
    'form',
    'status',
    'description',
    'played',
    'win',
    'draw',
    'lose',
    'goals_for',
    'goals_against',
    'api_updated_at',
])]
class LeagueStanding extends Model
{
    /** @use HasFactory<LeagueStandingFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'league_id' => 'integer',
            'season' => 'integer',
            'api_team_id' => 'integer',
            'rank' => 'integer',
            'points' => 'integer',
            'goals_diff' => 'integer',
            'played' => 'integer',
            'win' => 'integer',
            'draw' => 'integer',
            'lose' => 'integer',
            'goals_for' => 'integer',
            'goals_against' => 'integer',
            'api_updated_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<LeagueStanding>  $query
     * @return Builder<LeagueStanding>
     */
    public function scopeForLeagueSeason(Builder $query, int $leagueId, int $season): Builder
    {
        return $query
            ->where('league_id', $leagueId)
            ->where('season', $season);
    }

    /**
     * @param  Builder<LeagueStanding>  $query
     * @return Builder<LeagueStanding>
     */
    public function scopeOrderedByRank(Builder $query): Builder
    {
        return $query->orderBy('rank');
    }
}
