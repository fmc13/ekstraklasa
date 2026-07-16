<?php

namespace App\Console\Commands;

use App\Actions\SyncEkstraklasaSquads;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('football:sync-squads {--season= : Season start year (e.g. 2026 for 2026/2027)}')]
#[Description('Synchronize Ekstraklasa squads and coaches from API-Football')]
class SyncEkstraklasaSquadsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SyncEkstraklasaSquads $sync): int
    {
        $seasonOption = $this->option('season');
        $season = is_numeric($seasonOption) ? (int) $seasonOption : null;

        try {
            $result = $sync->handle($season);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Synced {$result['players']} players and {$result['coaches']} coaches for {$result['teams']} teams (league {$result['league_id']}, season {$result['season']}/".($result['season'] + 1).').');

        return self::SUCCESS;
    }
}
