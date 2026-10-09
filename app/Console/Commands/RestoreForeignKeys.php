<?php

namespace App\Console\Commands;

use App\Support\LegacySchemaUpgrade;
use Illuminate\Console\Command;

/**
 * Adds the foreign keys a fresh install has and this database lacks. It is the same step as the upgrade migration
 * 2026_10_08_120000_restore_missing_foreign_keys, for running again once the cause of a skipped key (rows pointing at a
 * record that no longer exists) has been fixed by hand. It never changes a row.
 *
 *   php artisan db:restore-foreign-keys
 */
class RestoreForeignKeys extends Command
{
    protected $signature = 'db:restore-foreign-keys';

    protected $description = 'Add the missing foreign keys that nothing blocks (never changes rows; says why a key is skipped)';

    public function handle(): int
    {
        $report = LegacySchemaUpgrade::restoreForeignKeys();

        $this->info('Added '.count($report['added']).' foreign key(s); '.count($report['present']).' were already in place.');

        foreach ($report['skipped'] as $name => $reason) {
            $this->warn("{$name}: {$reason}");
        }

        return self::SUCCESS;
    }
}
