<?php

namespace App\Console\Commands;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\PurchasedMedicine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Read-only consistency report for the medicine inventory. Run it after upgrading past the
 * 2026-09 stock-in / dispensing fixes (older versions could leave stock in the wrong batch or
 * create phantom stock), and any time the numbers on screen look wrong. It never changes data.
 *
 *   php artisan inventory:reconcile
 */
class ReconcileInventory extends Command
{
    protected $signature = 'inventory:reconcile';

    protected $description = 'Report medicines whose totals, batches or ledger disagree (read-only)';

    public function handle(): int
    {
        $problems = 0;

        // 1. medicines.quantity must equal the sum of its batches.
        $totals = MedicineBatch::query()
            ->select('medicine_id', DB::raw('SUM(quantity) AS total'))
            ->groupBy('medicine_id')
            ->pluck('total', 'medicine_id');

        $mismatch = [];
        foreach (Medicine::query()->select('id', 'name', 'quantity')->cursor() as $medicine) {
            $batchTotal = (int) ($totals[$medicine->id] ?? 0);
            if ((int) $medicine->quantity !== $batchTotal) {
                $mismatch[] = [$medicine->id, $medicine->name, (int) $medicine->quantity, $batchTotal];
            }
        }
        $problems += count($mismatch);
        $this->section('Medicines whose total differs from the sum of their batches', ['ID', 'Medicine', 'medicines.quantity', 'Sum of batches'], $mismatch);

        // 2. Each batch's quantity must equal the balance after its last ledger entry.
        $lastBalance = DB::table('medicine_transactions as t')
            ->join(DB::raw('(SELECT batch_id, MAX(id) AS last_id FROM medicine_transactions GROUP BY batch_id) l'), 'l.last_id', '=', 't.id')
            ->pluck('t.balance_after', 't.batch_id');

        $ledgerMismatch = [];
        foreach (MedicineBatch::query()->with('medicine:id,name')->cursor() as $batch) {
            if (isset($lastBalance[$batch->id]) && (int) $lastBalance[$batch->id] !== (int) $batch->quantity) {
                $ledgerMismatch[] = [$batch->id, $batch->medicine->name ?? '#' . $batch->medicine_id, $batch->batch_number, (int) $batch->quantity, (int) $lastBalance[$batch->id]];
            }
        }
        $problems += count($ledgerMismatch);
        $this->section('Batches whose quantity differs from the last ledger balance', ['Batch', 'Medicine', 'Batch no.', 'Quantity', 'Ledger balance'], $ledgerMismatch);

        // 3. "Never expires" placeholder batches (created by legacy opening balances and by restoring
        //    a dispense that had no recorded expiry) - real stock with an unknown expiry.
        $sentinel = MedicineBatch::query()->with('medicine:id,name')->whereDate('expiration_date', '2099-12-31')->where('quantity', '>', 0)->get();
        $this->section(
            'Batches with the 2099-12-31 "no expiry" placeholder (check the real expiry on the shelf)',
            ['Batch', 'Medicine', 'Batch no.', 'Quantity'],
            $sentinel->map(fn ($b) => [$b->id, $b->medicine->name ?? '#' . $b->medicine_id, $b->batch_number, (int) $b->quantity])->all()
        );
        $problems += $sentinel->count();

        // 4. Stock-in lines that are not linked to a batch (older data; edits fall back to matching
        //    by dosage + expiry).
        $unlinked = PurchasedMedicine::query()->whereNull('batch_id')->whereNotNull('medicine_id')->count();
        $this->line("Stock-in lines not linked to a batch: {$unlinked}" . ($unlinked ? ' (informational)' : ''));

        $this->newLine();
        $problems === 0
            ? $this->info('Inventory is consistent.')
            : $this->warn("{$problems} item(s) need attention. Nothing was changed.");

        return self::SUCCESS;
    }

    private function section(string $title, array $headers, array $rows): void
    {
        $this->newLine();
        $this->line("<options=bold>{$title}: " . count($rows) . '</>');

        if (! empty($rows)) {
            $this->table($headers, array_slice($rows, 0, 100));
            if (count($rows) > 100) {
                $this->line('... and ' . (count($rows) - 100) . ' more');
            }
        }
    }
}
