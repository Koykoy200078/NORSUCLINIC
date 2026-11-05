<?php

namespace App\Console\Commands;

use App\Services\MonthOnlyExpiryCorrection;
use Illuminate\Console\Command;

/**
 * Stock entered with the stock-in form's "Month Only" switch before the owner's decision of 2026-11-05 sits on the 1st of
 * the month; a month-only expiry now means the LAST day. This moves those batches (only the ones whose stock-in lines all
 * say the same month-only value) and recalculates the available quantity of their medicines. A dry run unless --apply is
 * given. Take a backup before --apply (php artisan db:backup). Idempotent.
 *
 *   php artisan inventory:month-only-expiry
 *   php artisan inventory:month-only-expiry --apply
 */
class MonthOnlyExpiry extends Command
{
    protected $signature = 'inventory:month-only-expiry {--apply : Move the batches to the last day of their month (default is a dry run)}';

    protected $description = 'Move stock entered as a month-only expiry from the 1st to the last day of that month (dry run unless --apply)';

    private const LIST_LIMIT = 200;

    public function handle(MonthOnlyExpiryCorrection $correction): int
    {
        $plan = $correction->plan();
        $moves = $plan['moves'];

        if ($moves !== []) {
            $this->table(
                ['Batch', 'Medicine', 'Batch number', 'Units', 'Now', 'Would be', 'Expired today, usable after'],
                array_map(fn (array $row): array => [
                    $row['batch_id'], $row['medicine'], $row['batch_number'], $row['quantity'], $row['from'], $row['to'],
                    $row['usable_again'] > 0 ? $row['usable_again'] : '',
                ], array_slice($moves, 0, self::LIST_LIMIT))
            );

            if (count($moves) > self::LIST_LIMIT) {
                $this->line('... and '.(count($moves) - self::LIST_LIMIT).' more.');
            }
        }

        if ($plan['unlinked'] > 0) {
            $this->warn($plan['unlinked'].' batch(es) on the 1st of a month have no stock-in line (opening balance or imported stock): not touched, nobody can tell how their date was typed.');
        }
        if ($plan['disagreeing'] > 0) {
            $this->warn($plan['disagreeing'].' batch(es) have lines that disagree (a full date, another month, or both): not touched.');
        }

        if ($moves === []) {
            $this->info('No batch needs correcting.');

            return self::SUCCESS;
        }

        $units = array_sum(array_column($moves, 'quantity'));
        $usable = array_sum(array_column($moves, 'usable_again'));
        $apply = (bool) $this->option('apply');
        $usableNote = $usable > 0
            ? $usable.' unit(s) '.($apply ? 'that counted as expired are usable again.' : 'now counted as expired would be usable again after the move.')
            : '';

        if (! $apply) {
            $this->info(trim('Dry run: '.count($moves).' batch(es) ('.$units.' unit(s)) would move from the 1st to the last day of their month. '
                .$usableNote).' Re-run with --apply after a backup (php artisan db:backup).');

            return self::SUCCESS;
        }

        $moved = $correction->apply();
        $medicines = count(array_unique(array_column($moved, 'medicine_id')));
        $this->info(trim('Moved '.count($moved).' batch(es) to the last day of their month; the available quantity of '.$medicines.' medicine(s) was recalculated. '
            .$usableNote.' Each move is in the log (storage/logs).'));

        return self::SUCCESS;
    }
}
