<?php

namespace App\Models;

use Database\Factories\TeamPlayerFactory;
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
 * @property int $api_player_id
 * @property string $name
 * @property int|null $age
 * @property int|null $number
 * @property string|null $position
 * @property string|null $photo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'league_id',
    'season',
    'api_team_id',
    'api_player_id',
    'name',
    'age',
    'number',
    'position',
    'photo',
])]
class TeamPlayer extends Model
{
    /** @use HasFactory<TeamPlayerFactory> */
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
            'api_player_id' => 'integer',
            'age' => 'integer',
            'number' => 'integer',
        ];
    }

    /**
     * @param  Builder<TeamPlayer>  $query
     * @return Builder<TeamPlayer>
     */
    public function scopeForTeamSeason(Builder $query, int $leagueId, int $season, int $apiTeamId): Builder
    {
        return $query
            ->where('league_id', $leagueId)
            ->where('season', $season)
            ->where('api_team_id', $apiTeamId);
    }
}
