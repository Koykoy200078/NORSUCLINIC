<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Stock batches whose medicine no longer exists (the old MyISAM database let a medicine with batches be deleted). They
 * block the foreign key `medicine_batches.medicine_id`. The owner's decision (2026-10-09) is to keep the history: the
 * medicine is re-created under its old id as "Unknown medicine #N", and its stock is written off through the ledger
 * (`disposal` rows). A medicine has no inactive flag and every picker lists medicines with available stock, so a
 * placeholder that still held stock could be dispensed; with its stock written off it is out of every picker.
 */
final class OrphanBatchRecovery
{
    public function __construct(private readonly MedicineInventoryService $inventory)
    {
    }

    /**
     * @return list<array{medicine_id: int, batches: int, units: int, dosage: ?string}> one row per missing medicine;
     *                                                                                   dosage is set when all its batches share one
     */
    public function orphans(): array
    {
        $rows = DB::select(
            "SELECT b.medicine_id, COUNT(*) AS batches, SUM(b.quantity) AS units,
                    COUNT(DISTINCT NULLIF(b.dosage, '')) AS dosages, MIN(NULLIF(b.dosage, '')) AS dosage
             FROM medicine_batches b LEFT JOIN medicines m ON m.id = b.medicine_id
             WHERE m.id IS NULL
             GROUP BY b.medicine_id
             ORDER BY b.medicine_id",
            [],
            false
        );

        return array_map(fn ($row): array => [
            'medicine_id' => (int) $row->medicine_id,
            'batches' => (int) $row->batches,
            'units' => (int) $row->units,
            'dosage' => (int) $row->dosages === 1 ? $row->dosage : null,
        ], $rows);
    }

    /**
     * Re-create every missing medicine and write its stock off. All or nothing.
     *
     * @return list<array{medicine_id: int, batches: int, units: int, dosage: ?string}> what was recovered
     */
    public function recover(): array
    {
        $orphans = $this->orphans();

        DB::transaction(function () use ($orphans) {
            foreach ($orphans as $orphan) {
                $id = $orphan['medicine_id'];

                $medicine = new Medicine();
                $medicine->forceFill([
                    'id' => $id,
                    'name' => "Unknown medicine #{$id}",
                    'category' => 'Recovered (deleted medicine)',
                    'dosage' => $orphan['dosage'],
                    'description' => 'Re-created by the upgrade: stock batches still pointed at a medicine that had been deleted. '
                        .'Rename it or leave it as the record of that stock; its stock was written off.',
                    'quantity' => 0,
                    'available_quantity' => 0,
                ])->save();

                // Totals from the batches, then the write-off through the ledger (disposal ignores expiry dates).
                $this->inventory->syncMedicineTotals($id);
                $units = (int) $medicine->fresh()->quantity;
                if ($units > 0) {
                    $this->inventory->deductStockFefo(
                        $id,
                        $units,
                        null,
                        $medicine,
                        'Stock written off: its medicine record had been deleted before the upgrade; the medicine was re-created as a placeholder.',
                        MedicineTransaction::TYPE_DISPOSAL
                    );
                }

                // syncMedicineTotals() only raises the baseline; a medicine with nothing in stock has none.
                Medicine::whereKey($id)->update(['baseline_quantity' => 0]);
            }
        });

        return $orphans;
    }
}
