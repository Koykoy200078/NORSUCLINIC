<?php

namespace App\Services;

use App\Models\DispenseRecordItem;

/**
 * The lines (`sale_medicines`) of a dispense record, written from what the stock engine really took out.
 *
 * One line per strength / expiry date that supplied the units (FEFO can take one request from several batches), or the
 * requested line itself when the engine reports no allocation. It used to be copied in DispenseRecordController and in
 * MedicineBillRepository; Meds Only (2026-10-29) is the third caller.
 */
final class DispenseRecordLines
{
    /**
     * True when two lines of a dispense form name the same medicine, strength and expiry date (one line per batch).
     * Saving and editing a dispense record both check it (re-audit 2026-11-13, DRY: each had a copy).
     *
     * @param  array<string, mixed>  $input  the form: medicine[], dosage[], expiry_date[]
     */
    public static function hasDuplicateLines(array $input): bool
    {
        return collect($input['medicine'])->map(function ($medicineId, $key) use ($input) {
            $dosage = trim((string) ($input['dosage'][$key] ?? ''));
            $expiry = trim((string) ($input['expiry_date'][$key] ?? ''));

            return $medicineId . '|' . $dosage . '|' . $expiry;
        })->duplicates()->isNotEmpty();
    }

    /**
     * @param  array<int, array<string, mixed>>  $allocations  the engine's answer (MedicineInventoryService::deductStockFefo)
     */
    public static function fromAllocations(
        int $dispenseRecordId,
        int $medicineId,
        string $requestedDosage,
        ?string $requestedExpiry,
        int $requestedQuantity,
        array $allocations
    ): void {
        if (empty($allocations)) {
            DispenseRecordItem::create([
                'dispense_id' => $dispenseRecordId,
                'medicine_id' => $medicineId,
                'dosage' => $requestedDosage,
                'expires_at' => $requestedExpiry,
                'quantity' => $requestedQuantity,
            ]);

            return;
        }

        $groupedAllocations = collect($allocations)
            ->filter(function (array $allocation) {
                return (int) ($allocation['deducted'] ?? 0) > 0;
            })
            ->groupBy(function (array $allocation) use ($requestedDosage) {
                $allocationDosage = trim((string) ($allocation['dosage'] ?? $requestedDosage));
                $allocationExpiry = $allocation['expiration_date'] ?? '';

                return $allocationDosage . '|' . (string) $allocationExpiry;
            });

        foreach ($groupedAllocations as $rows) {
            $firstRow = $rows->first();
            $allocationDosage = trim((string) ($firstRow['dosage'] ?? $requestedDosage));
            $allocationExpiry = $firstRow['expiration_date'] ?? $requestedExpiry;
            $deductedQuantity = (int) collect($rows)->sum(function (array $allocation) {
                return (int) ($allocation['deducted'] ?? 0);
            });

            if ($deductedQuantity <= 0) {
                continue;
            }

            DispenseRecordItem::create([
                'dispense_id' => $dispenseRecordId,
                'medicine_id' => $medicineId,
                'dosage' => $allocationDosage !== '' ? $allocationDosage : $requestedDosage,
                'expires_at' => $allocationExpiry,
                'quantity' => $deductedQuantity,
            ]);
        }
    }
}
