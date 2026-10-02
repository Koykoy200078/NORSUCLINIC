<?php

namespace App\Console\Commands;

use App\Services\MedicineInventoryService;
use Illuminate\Console\Command;

/**
 * Refreshes the "available" figure of every medicine after batches expire.
 *
 * medicines.available_quantity counts unexpired units only, but it used to be recalculated only when stock
 * moved. A batch that expired overnight therefore stayed in the available figure (and in the medicine
 * pick-lists and reports) until someone happened to stock in or dispense that medicine. This runs from the
 * scheduler every night just after midnight; it changes only that derived figure, never a batch or the ledger.
 *
 *   php artisan inventory:sync-expiry            apply
 *   php artisan inventory:sync-expiry --dry-run  only list what would change
 */
class SyncInventoryExpiry extends Command
{
    protected $signature = 'inventory:sync-expiry {--dry-run : List the medicines that would change without changing them}';

    protected $description = 'Recalculate the available quantity of medicines whose batches have expired';

    public function handle(MedicineInventoryService $inventory): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $corrected = $inventory->syncExpiredAvailability($dryRun);

        if (empty($corrected)) {
            $this->info('Every medicine\'s available quantity is already up to date.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Medicine', 'Stored available', 'Unexpired units'],
            array_map(fn (array $row) => [$row['id'], $row['name'], $row['stored'], $row['actual']], array_slice($corrected, 0, 100))
        );

        $this->{$dryRun ? 'warn' : 'info'}(
            count($corrected) . ($dryRun ? ' medicine(s) would be corrected (dry run, nothing changed).' : ' medicine(s) corrected.')
        );

        return self::SUCCESS;
    }
}
