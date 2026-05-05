<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineTransaction;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MedicineInventoryService
{
    /**
     * Record stock-in movement for a medicine batch.
     */
    public function recordStockIn(array $data): MedicineBatch
    {
        return DB::transaction(function () use ($data) {
            $medicineId = (int) ($data['medicine_id'] ?? 0);
            $quantity = max(0, (int) ($data['quantity'] ?? 0));
            $openingBalanceBefore = array_key_exists('opening_balance_before', $data)
                ? (int) $data['opening_balance_before']
                : null;

            if ($medicineId <= 0 || $quantity <= 0) {
                throw new RuntimeException('Valid medicine and quantity are required for stock-in.');
            }

            $medicine = Medicine::find($medicineId);
            if (! $medicine) {
                throw new RuntimeException('Medicine not found for stock-in.');
            }

            $this->ensureLegacyOpeningBatch($medicineId, $openingBalanceBefore);

            $batchNumber = trim((string) ($data['batch_number'] ?? ''));
            if ($batchNumber === '') {
                $batchNumber = 'BATCH-' . $medicineId . '-' . now()->format('YmdHis');
            }

            $batch = MedicineBatch::lockForUpdate()
                ->where('medicine_id', $medicineId)
                ->where('batch_number', $batchNumber)
                ->first();

            if (! $batch) {
                $batch = new MedicineBatch([
                    'medicine_id' => $medicineId,
                    'batch_number' => $batchNumber,
                    'quantity' => 0,
                ]);
            }

            $incomingDosage = trim((string) ($data['dosage'] ?? ''));
            $resolvedDosage = $incomingDosage !== ''
                ? $incomingDosage
                : trim((string) ($medicine->dosage ?? ''));

            $resolvedExpirationDate = $data['expiration_date'] ?? $batch->expiration_date;
            if (empty($resolvedExpirationDate)) {
                throw new RuntimeException('Expiration date is required for stock-in.');
            }

            $resolvedDateReceived = $data['date_received'] ?? $batch->date_received ?? Carbon::today()->toDateString();

            $batch->manufacturing_date = $data['manufacturing_date'] ?? $batch->manufacturing_date;
            $batch->expiration_date = Carbon::parse($resolvedExpirationDate)->toDateString();
            $batch->supplier_name = $data['supplier_name'] ?? $batch->supplier_name;
            $batch->unit_cost = isset($data['unit_cost']) ? (float) $data['unit_cost'] : $batch->unit_cost;
            $batch->date_received = Carbon::parse($resolvedDateReceived)->toDateString();
            if ($resolvedDosage !== '') {
                $batch->dosage = $resolvedDosage;
            }
            $batch->quantity = (int) $batch->quantity + $quantity;
            $batch->save();

            $this->createTransaction(
                $batch,
                MedicineTransaction::TYPE_STOCK_IN,
                $quantity,
                (int) $batch->quantity,
                $data['user_id'] ?? null,
                $data['reference'] ?? null,
                $data['remarks'] ?? 'Stock-in entry'
            );

            $this->syncMedicineTotals($medicineId);

            return $batch->fresh();
        });
    }

    /**
     * Dispense prescription medicines using FEFO deduction.
     */
    public function dispensePrescription(Prescription $prescription, ?int $userId = null): array
    {
        return DB::transaction(function () use ($prescription, $userId) {
            $prescription->refresh();

            $currentStatus = $prescription->status;
            if ($currentStatus === true || $currentStatus === 1 || $currentStatus === '1') {
                $currentStatus = Prescription::DISPENSE_STATUS_PENDING;
            }

            if ($currentStatus !== Prescription::DISPENSE_STATUS_PENDING) {
                throw new RuntimeException('Only pending prescriptions can be dispensed.');
            }

            $prescription->loadMissing('getMedicine');

            $allocationSummary = [];
            /** @var PrescriptionMedicine $line */
            foreach ($prescription->getMedicine as $line) {
                $quantity = (int) ($line->total_quantity ?: 0);
                if ($quantity <= 0) {
                    $fallbackDuration = max(1, (int) ($line->duration_value ?: $line->day ?: 1));
                    $fallbackFrequency = max(1, (int) ($line->frequency ?: $line->dose_interval ?: 1));
                    $quantity = $fallbackDuration * $fallbackFrequency;
                }

                $allocationSummary[$line->medicine] = $this->deductStockFefo(
                    (int) $line->medicine,
                    $quantity,
                    $userId,
                    $prescription,
                    'Prescription #' . $prescription->id . ' dispensed',
                    MedicineTransaction::TYPE_DISPENSE
                );
            }

            $prescription->update([
                'status' => Prescription::DISPENSE_STATUS_DISPENSED,
                'dispensed_at' => now(),
                'dispensed_by' => $userId,
            ]);

            return $allocationSummary;
        });
    }

    /**
     * Deduct stock using First-Expire-First-Out strategy.
     */
    public function deductStockFefo(
        int $medicineId,
        int $quantity,
        ?int $userId = null,
        ?Model $reference = null,
        ?string $remarks = null,
        string $transactionType = MedicineTransaction::TYPE_DISPENSE,
        ?string $dosage = null
    ): array {
        if ($quantity <= 0) {
            return [];
        }

        return DB::transaction(function () use ($medicineId, $quantity, $userId, $reference, $remarks, $transactionType, $dosage) {
            $this->ensureLegacyOpeningBatch($medicineId);

            $remaining = $quantity;
            $allocations = [];

            $normalizedDosage = trim((string) $dosage);

            $batchesQuery = MedicineBatch::where('medicine_id', $medicineId)
                ->where('quantity', '>', 0);

            if ($transactionType === MedicineTransaction::TYPE_DISPENSE) {
                $batchesQuery->where(function ($query) {
                    $query->whereNull('expiration_date')
                        ->orWhereDate('expiration_date', '>=', Carbon::today()->toDateString());
                });
            }

            if ($normalizedDosage !== '') {
                if (strcasecmp($normalizedDosage, 'N/A') === 0) {
                    $batchesQuery->where(function ($query) {
                        $query->whereNull('dosage')
                            ->orWhere('dosage', '')
                            ->orWhere('dosage', 'N/A');
                    });
                } else {
                    $batchesQuery->where('dosage', $normalizedDosage);
                }
            }

            $batches = $batchesQuery
                ->orderByRaw('expiration_date IS NULL')
                ->orderBy('expiration_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $deducted = min((int) $batch->quantity, $remaining);
                if ($deducted <= 0) {
                    continue;
                }

                $batch->quantity = (int) $batch->quantity - $deducted;
                $batch->save();

                $this->createTransaction(
                    $batch,
                    $transactionType,
                    $deducted,
                    (int) $batch->quantity,
                    $userId,
                    $reference,
                    $remarks ?? 'FEFO stock deduction'
                );

                $allocations[] = [
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'dosage' => $batch->dosage,
                    'deducted' => $deducted,
                    'balance_after' => (int) $batch->quantity,
                    'expiration_date' => optional($batch->expiration_date)->toDateString(),
                ];

                $remaining -= $deducted;
            }

            if ($remaining > 0) {
                $medicine = Medicine::find($medicineId);
                $name = $medicine ? $medicine->display_name : 'Unknown medicine';
                $dosageSuffix = $normalizedDosage !== '' ? ' (' . $normalizedDosage . ')' : '';
                throw new RuntimeException('Insufficient stock for ' . $name . $dosageSuffix . '. Remaining quantity: ' . $remaining . '.');
            }

            $this->syncMedicineTotals($medicineId);

            return $allocations;
        });
    }

    /**
     * Keep legacy medicine totals in sync with batch-level quantities.
     */
    public function syncMedicineTotals(int $medicineId): void
    {
        $total = (int) MedicineBatch::where('medicine_id', $medicineId)->sum('quantity');

        $available = (int) MedicineBatch::where('medicine_id', $medicineId)
            ->where('quantity', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expiration_date')
                    ->orWhereDate('expiration_date', '>=', Carbon::today()->toDateString());
            })
            ->sum('quantity');

        $medicine = Medicine::find($medicineId);
        if (! $medicine) {
            return;
        }

        $currentBaseline = (int) ($medicine->baseline_quantity ?? 0);
        $newBaseline = max(
            $currentBaseline,
            $total,
            (int) $medicine->quantity,
            (int) $medicine->available_quantity
        );

        $medicine->update([
            'quantity' => $total,
            'available_quantity' => $available,
            'baseline_quantity' => $newBaseline,
        ]);
    }

    /**
     * Create a single opening batch for medicines that still rely on aggregate stock.
     */
    private function ensureLegacyOpeningBatch(int $medicineId, ?int $openingBalanceOverride = null): void
    {
        $hasAnyBatch = MedicineBatch::where('medicine_id', $medicineId)->exists();
        if ($hasAnyBatch) {
            return;
        }

        $medicine = Medicine::find($medicineId);
        if (! $medicine) {
            return;
        }

        $openingQty = $openingBalanceOverride;
        if ($openingQty === null || $openingQty < 0) {
            $openingQty = max(0, (int) $medicine->available_quantity);
        }

        if ($openingQty <= 0) {
            return;
        }

        $batch = MedicineBatch::create([
            'medicine_id' => $medicineId,
            'batch_number' => 'OPENING-' . $medicineId,
            'dosage' => $medicine->dosage,
            'quantity' => $openingQty,
            'expiration_date' => Carbon::today()->toDateString(),
            'date_received' => Carbon::today()->toDateString(),
            'supplier_name' => 'Legacy opening balance',
        ]);

        $this->createTransaction(
            $batch,
            MedicineTransaction::TYPE_ADJUSTMENT,
            $openingQty,
            $openingQty,
            null,
            $medicine,
            'Initialized from legacy available_quantity'
        );
    }

    private function createTransaction(
        MedicineBatch $batch,
        string $type,
        int $quantity,
        int $balanceAfter,
        ?int $userId = null,
        ?Model $reference = null,
        ?string $remarks = null
    ): MedicineTransaction {
        return MedicineTransaction::create([
            'batch_id' => $batch->id,
            'user_id' => $userId,
            'transaction_type' => $type,
            'quantity' => $quantity,
            'balance_after' => $balanceAfter,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference ? $reference->getKey() : null,
            'remarks' => $remarks,
        ]);
    }
}
