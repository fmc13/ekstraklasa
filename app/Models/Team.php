<?php

namespace App\Models;

use Database\Factories\TeamFactory;
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
 * @property string $name
 * @property string|null $code
 * @property string|null $country
 * @property int|null $founded
 * @property bool $national
 * @property string|null $logo
 * @property int|null $api_venue_id
 * @property string|null $venue_name
 * @property string|null $venue_address
 * @property string|null $venue_city
 * @property int|null $venue_capacity
 * @property string|null $venue_surface
 * @property string|null $venue_image
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'league_id',
    'season',
    'api_team_id',
    'name',
    'code',
    'country',
    'founded',
    'national',
    'logo',
    'api_venue_id',
    'venue_name',
    'venue_address',
    'venue_city',
    'venue_capacity',
    'venue_surface',
    'venue_image',
])]
class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'national' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'league_id' => 'integer',
            'season' => 'integer',
            'api_team_id' => 'integer',
            'founded' => 'integer',
            'national' => 'boolean',
            'api_venue_id' => 'integer',
            'venue_capacity' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'api_team_id';
    }

    /**
     * @param  Builder<Team>  $query
     * @return Builder<Team>
     */
    public function scopeForLeagueSeason(Builder $query, int $leagueId, int $season): Builder
    {
        return $query
            ->where('league_id', $leagueId)
            ->where('season', $season);
    }
}
