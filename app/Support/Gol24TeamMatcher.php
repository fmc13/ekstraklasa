<?php

namespace App\Support;

use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class Gol24TeamMatcher
{
    /**
     * Explicit Gol24 display names → API-Football team names in DB.
     *
     * @var array<string, string>
     */
    private const ALIASES = [
        'cracovia' => 'Cracovia Krakow',
        'jagiellonia bialystok' => 'Jagiellonia',
        'jagiellonia' => 'Jagiellonia',
        'lech poznan' => 'Lech Poznan',
        'gornik zabrze' => 'Gornik Zabrze',
        'slask wroclaw' => 'Slask Wroclaw',
        'pogon szczecin' => 'Pogon Szczecin',
        'wisla krakow' => 'Wisla Krakow',
        'wisla plock' => 'Wisla Plock',
        'zaglebie lubin' => 'Zaglebie Lubin',
        'rakow czestochowa' => 'Raków Częstochowa',
        'widzew lodz' => 'Widzew Łódź',
        'wieczysta krakow' => 'Wieczysta Kraków',
    ];

    /** @var Collection<string, Team>|null */
    private ?Collection $teamsByNormalizedName = null;

    public function find(string $gol24Name): Team
    {
        $teams = $this->teamsByNormalizedName();
        $normalized = $this->normalize($gol24Name);

        $aliasTarget = self::ALIASES[$normalized] ?? null;

        if ($aliasTarget !== null) {
            $team = $teams->get($this->normalize($aliasTarget));

            if ($team !== null) {
                return $team;
            }
        }

        $team = $teams->get($normalized);

        if ($team !== null) {
            return $team;
        }

        foreach ($teams as $candidateNormalized => $candidate) {
            if (str_contains($candidateNormalized, $normalized) || str_contains($normalized, $candidateNormalized)) {
                return $candidate;
            }
        }

        throw new RuntimeException("Nie znaleziono drużyny dla nazwy Gol24: {$gol24Name}");
    }

    /**
     * @return Collection<string, Team>
     */
    private function teamsByNormalizedName(): Collection
    {
        if ($this->teamsByNormalizedName !== null) {
            return $this->teamsByNormalizedName;
        }

        $leagueId = (int) config('services.api_football.league_id');
        $season = (int) config('services.api_football.season');

        $this->teamsByNormalizedName = Team::query()
            ->forLeagueSeason($leagueId, $season)
            ->get()
            ->keyBy(fn (Team $team): string => $this->normalize($team->name));

        return $this->teamsByNormalizedName;
    }

    public function normalize(string $name): string
    {
        $ascii = Str::ascii(mb_strtolower(trim($name)));

        return preg_replace('/\s+/', ' ', $ascii) ?? $ascii;
    }
}
