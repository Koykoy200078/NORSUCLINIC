<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Support\ExpiryDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Stock entered with the stock-in form's "Month Only" switch before the owner's decision of 2026-11-05 (a month-only expiry
 * is the LAST day of that month) sits on the 1st of the month. This finds those batches and moves them to the last day.
 *
 * A batch is moved only when it is certain how its date was typed: it sits on the 1st of a month AND every stock-in line
 * that created or topped it up (`purchased_medicines.batch_id`) says the same month-only value ("2027-02"). Anything else
 * is left alone and counted: a batch with no line (opening balance, imported stock: nobody can tell), and a batch whose
 * lines disagree (a full date typed on purpose, another month, or both).
 *
 * Only `medicine_batches.expiration_date` changes (and the derived `medicines.available_quantity`): no quantity moves, so
 * the stock ledger gets no row. Every move is logged with its old and new date.
 */
final class MonthOnlyExpiryCorrection
{
    public function __construct(private readonly MedicineInventoryService $inventory)
    {
    }

    /**
     * @return array{
     *     moves: list<array{batch_id: int, medicine_id: int, medicine: string, batch_number: string, quantity: int, from: string, to: string, usable_again: int}>,
     *     unlinked: int,
     *     disagreeing: int
     * } usable_again is the units that count as expired today and would not after the move
     */
    public function plan(): array
    {
        $today = Carbon::today()->toDateString();

        $batches = MedicineBatch::query()
            ->whereNotNull('expiration_date')
            ->whereRaw('DAY(expiration_date) = 1')
            ->orderBy('id')
            ->get(['id', 'medicine_id', 'batch_number', 'quantity', 'expiration_date']);

        $lines = DB::table('purchased_medicines')
            ->whereNotNull('batch_id')
            ->get(['batch_id', 'expiry_date'])
            ->groupBy('batch_id');

        $names = DB::table('medicines')->whereIn('id', $batches->pluck('medicine_id')->unique())->pluck('name', 'id');

        $moves = [];
        $unlinked = 0;
        $disagreeing = 0;

        foreach ($batches as $batch) {
            $from = $batch->expiration_date->toDateString();
            $month = substr($from, 0, 7);
            $batchLines = $lines->get($batch->id, collect());

            if ($batchLines->isEmpty()) {
                $unlinked++;

                continue;
            }

            $monthOnly = $batchLines->filter(fn ($line): bool => ExpiryDate::isMonthOnly($line->expiry_date))->count();
            $agreeing = $batchLines->filter(fn ($line): bool => ExpiryDate::isMonthOnly($line->expiry_date) && trim((string) $line->expiry_date) === $month)->count();

            if ($agreeing !== $batchLines->count()) {
                // Every line typed in full: someone chose the 1st. Nothing to say about it. Some month-only evidence that
                // does not add up is worth a look.
                if ($monthOnly > 0) {
                    $disagreeing++;
                }

                continue;
            }

            $to = ExpiryDate::toDateString($month);
            $moves[] = [
                'batch_id' => (int) $batch->id,
                'medicine_id' => (int) $batch->medicine_id,
                'medicine' => (string) ($names[$batch->medicine_id] ?? '(missing medicine)'),
                'batch_number' => (string) $batch->batch_number,
                'quantity' => (int) $batch->quantity,
                'from' => $from,
                'to' => $to,
                'usable_again' => $from < $today && $to >= $today ? (int) $batch->quantity : 0,
            ];
        }

        return ['moves' => $moves, 'unlinked' => $unlinked, 'disagreeing' => $disagreeing];
    }

    /**
     * Move every batch of the plan to the last day of its month, one medicine at a time under that medicine's stock lock
     * (the order the stock service takes its locks in), then recalculate the medicine's available quantity. All or nothing.
     *
     * @return list<array{batch_id: int, medicine_id: int, medicine: string, batch_number: string, quantity: int, from: string, to: string, usable_again: int}> what was moved
     */
    public function apply(): array
    {
        $moved = [];

        DB::transaction(function () use (&$moved): void {
            // Read the plan again inside the transaction: the screen may have been open for a while.
            $byMedicine = collect($this->plan()['moves'])->groupBy('medicine_id')->sortKeys();

            foreach ($byMedicine as $medicineId => $moves) {
                Medicine::withMerged()->lockForUpdate()->find($medicineId);

                foreach ($moves as $move) {
                    $batch = MedicineBatch::lockForUpdate()->find($move['batch_id']);

                    if (! $batch || $batch->expiration_date?->toDateString() !== $move['from']) {
                        continue;
                    }

                    // Eloquent, not a bare UPDATE: saving the batch drops the cached "expiring" count of the menu badge.
                    $batch->expiration_date = $move['to'];
                    $batch->save();

                    Log::info('Stock batch moved: month-only expiry now on the last day of its month', [
                        'batch_id' => $move['batch_id'],
                        'medicine_id' => $move['medicine_id'],
                        'batch_number' => $move['batch_number'],
                        'quantity' => $move['quantity'],
                        'from' => $move['from'],
                        'to' => $move['to'],
                    ]);

                    $moved[] = $move;
                }

                $this->inventory->syncMedicineTotals((int) $medicineId);
            }
        });

        return $moved;
    }
}
