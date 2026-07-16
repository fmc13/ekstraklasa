<?php

namespace App\Models;

use Database\Factories\TeamCoachFactory;
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
 * @property int $api_coach_id
 * @property string $name
 * @property string|null $firstname
 * @property string|null $lastname
 * @property int|null $age
 * @property string|null $nationality
 * @property string|null $photo
 * @property list<array<string, mixed>>|null $career
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'league_id',
    'season',
    'api_team_id',
    'api_coach_id',
    'name',
    'firstname',
    'lastname',
    'age',
    'nationality',
    'photo',
    'career',
])]
class TeamCoach extends Model
{
    /** @use HasFactory<TeamCoachFactory> */
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
            'api_coach_id' => 'integer',
            'age' => 'integer',
            'career' => 'array',
        ];
    }

    /**
     * @param  Builder<TeamCoach>  $query
     * @return Builder<TeamCoach>
     */
    public function scopeForTeamSeason(Builder $query, int $leagueId, int $season, int $apiTeamId): Builder
    {
        return $query
            ->where('league_id', $leagueId)
            ->where('season', $season)
            ->where('api_team_id', $apiTeamId);
    }
}
