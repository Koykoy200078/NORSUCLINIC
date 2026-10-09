<?php

namespace App\Console\Commands;

use App\Services\OrphanBatchRecovery;
use App\Support\LegacySchemaUpgrade;
use Illuminate\Console\Command;

/**
 * Finds stock batches whose medicine was deleted (the old MyISAM database allowed it) and, with --apply, re-creates the
 * medicine as "Unknown medicine #N" under its old id and writes its stock off through the ledger, so the history stays and
 * the foreign key `medicine_batches.medicine_id` can be added. A dry run unless --apply is given. Take a backup before
 * --apply (php artisan db:backup). Idempotent.
 *
 *   php artisan inventory:recover-orphan-batches
 *   php artisan inventory:recover-orphan-batches --apply
 */
class RecoverOrphanBatches extends Command
{
    protected $signature = 'inventory:recover-orphan-batches {--apply : Re-create the missing medicines and write their stock off (default is a dry run)}';

    protected $description = 'Re-create medicines that were deleted while stock batches still pointed at them (dry run unless --apply)';

    public function handle(OrphanBatchRecovery $recovery): int
    {
        $orphans = $recovery->orphans();

        if ($orphans === []) {
            $this->info('No stock batch points at a missing medicine.');

            return self::SUCCESS;
        }

        $this->table(
            ['Missing medicine id', 'Batches', 'Units in stock', 'Dosage'],
            array_map(fn (array $row): array => [$row['medicine_id'], $row['batches'], $row['units'], $row['dosage'] ?? '(several / none)'], $orphans)
        );

        if (! $this->option('apply')) {
            $this->info('Dry run: '.count($orphans).' medicine(s) would be re-created as "Unknown medicine #N" and their '
                .array_sum(array_column($orphans, 'units')).' unit(s) written off through the ledger. '
                .'Re-run with --apply after a backup (php artisan db:backup).');

            return self::SUCCESS;
        }

        $recovery->recover();
        $this->info('Re-created '.count($orphans).' medicine(s); their stock was written off through the ledger. Rename them in Inventory if you know what they were.');

        // The key these batches were blocking can go in now.
        $report = LegacySchemaUpgrade::restoreForeignKeys();
        $this->info('Added '.count($report['added']).' foreign key(s); '.count($report['present']).' were already in place.');
        foreach ($report['skipped'] as $name => $reason) {
            $this->warn("{$name}: {$reason}");
        }

        return self::SUCCESS;
    }
}
