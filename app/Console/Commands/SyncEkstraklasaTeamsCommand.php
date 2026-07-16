<?php

namespace App\Console\Commands;

use App\Actions\SyncEkstraklasaTeams;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('football:sync-teams {--season= : Season start year (e.g. 2026 for 2026/2027)}')]
#[Description('Synchronize Ekstraklasa team details from API-Football')]
class SyncEkstraklasaTeamsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SyncEkstraklasaTeams $sync): int
    {
        $seasonOption = $this->option('season');
        $season = is_numeric($seasonOption) ? (int) $seasonOption : null;

        try {
            $result = $sync->handle($season);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Synced {$result['synced']} teams for league {$result['league_id']} season {$result['season']}/".($result['season'] + 1)." (source: {$result['source']}).");

        return self::SUCCESS;
    }
}
