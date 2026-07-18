<?php

namespace App\Console\Commands;

use App\Actions\SyncGol24Fixtures;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('gol24:sync-fixtures')]
#[Description('Synchronize Ekstraklasa fixtures from gol24.pl terminarz (upsert only)')]
class SyncGol24FixturesCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SyncGol24Fixtures $sync): int
    {
        try {
            $result = $sync->handle();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Gol24 fixtures: %d total · created %d · updated %d · unchanged %d (league %d, season %d/%d).',
            $result['total'],
            $result['created'],
            $result['updated'],
            $result['unchanged'],
            $result['league_id'],
            $result['season'],
            $result['season'] + 1,
        ));

        return self::SUCCESS;
    }
}
