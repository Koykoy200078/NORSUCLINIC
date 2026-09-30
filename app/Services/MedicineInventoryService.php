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
     * Sentinel expiry for stock with no known/real expiration date (legacy opening balance
     * and reversed dispenses). medicine_batches.expiration_date is NOT NULL, so we cannot use
     * NULL; a far-future date means "never expires" — it stays available and sorts LAST in FEFO.
     */
    private const NO_EXPIRY_SENTINEL = '2099-12-31';

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
            // Lock the prescription row so two concurrent dispense requests (double-click or
            // two staff) cannot both pass the pending check and deduct stock twice. DISP-3.
            $locked = Prescription::whereKey($prescription->getKey())->lockForUpdate()->first();
            if (! $locked) {
                throw new RuntimeException('Prescription not found for dispensing.');
            }
            $prescription = $locked;

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
                    // Fallback must honor the duration unit (week/month), matching the write
                    // path (PrescriptionController::resolveTotalQuantity); otherwise a
                    // "2 week, 3x/day" script deducts 6 instead of 42. DISP-4.
                    $durationValue = max(1, (int) ($line->duration_value ?: $line->day ?: 1));
                    $unit = strtolower((string) ($line->duration_unit ?: 'day'));
                    $days = match ($unit) {
                        'week', 'weeks' => $durationValue * 7,
                        'month', 'months' => $durationValue * 30,
                        default => $durationValue,
                    };
                    $fallbackFrequency = max(1, (int) ($line->frequency ?: $line->dose_interval ?: 1));
                    $quantity = $days * $fallbackFrequency;
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
                // $remaining is the shortfall, not what is left on the shelf; say both clearly.
                $available = $quantity - $remaining;
                throw new RuntimeException('Insufficient stock for ' . $name . $dosageSuffix . ': requested ' . $quantity
                    . ', only ' . $available . ' available' . ($transactionType === MedicineTransaction::TYPE_DISPENSE ? ' (unexpired)' : '') . '.');
            }

            $this->syncMedicineTotals($medicineId);

            return $allocations;
        });
    }

    /**
     * Restore previously-deducted stock back into inventory (reversal of a dispense).
     *
     * Adds the quantity back to the batch that matches the given dosage + expiration date
     * when one still exists, otherwise creates a dedicated restoration batch, and records a
     * positive ADJUSTMENT transaction so the ledger and aggregate totals stay consistent.
     * Used when a dispense record / consultation medicine is edited or deleted so the stock
     * that was taken out is returned instead of being silently lost.
     */
    public function restoreStock(
        int $medicineId,
        int $quantity,
        ?int $userId = null,
        ?Model $reference = null,
        ?string $remarks = null,
        ?string $dosage = null,
        ?string $expirationDate = null
    ): void {
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use ($medicineId, $quantity, $userId, $reference, $remarks, $dosage, $expirationDate) {
            $medicine = Medicine::find($medicineId);
            if (! $medicine) {
                return;
            }

            $normalizedDosage = trim((string) $dosage);
            // expiration_date is NOT NULL in the batch schema; when the reversed item has no
            // recorded expiry, fall back to the "never expires" sentinel so the restore batch
            // is valid and stays available (matches the legacy opening-balance behavior).
            $normalizedExpiry = ! empty($expirationDate)
                ? Carbon::parse($expirationDate)->toDateString()
                : self::NO_EXPIRY_SENTINEL;

            $batchQuery = MedicineBatch::where('medicine_id', $medicineId)
                ->whereDate('expiration_date', $normalizedExpiry);

            if ($normalizedDosage !== '' && strcasecmp($normalizedDosage, 'N/A') !== 0) {
                $batchQuery->where('dosage', $normalizedDosage);
            } else {
                $batchQuery->where(function ($query) {
                    $query->whereNull('dosage')
                        ->orWhere('dosage', '')
                        ->orWhere('dosage', 'N/A');
                });
            }

            $batch = $batchQuery->orderBy('id')->lockForUpdate()->first();

            if (! $batch) {
                $batch = new MedicineBatch([
                    'medicine_id' => $medicineId,
                    'batch_number' => 'RESTORE-' . $medicineId . '-' . now()->format('YmdHis'),
                    'dosage' => $normalizedDosage !== '' ? $normalizedDosage : $medicine->dosage,
                    'quantity' => 0,
                    'expiration_date' => $normalizedExpiry,
                    'date_received' => Carbon::today()->toDateString(),
                    'supplier_name' => 'Stock restoration',
                ]);
            }

            $batch->quantity = (int) $batch->quantity + $quantity;
            $batch->save();

            $this->createTransaction(
                $batch,
                MedicineTransaction::TYPE_ADJUSTMENT,
                $quantity,
                (int) $batch->quantity,
                $userId,
                $reference,
                $remarks ?? 'Stock restored from reversed dispense'
            );

            $this->syncMedicineTotals($medicineId);
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
            // Legacy opening stock has no known expiry. expiration_date is NOT NULL in the
            // batch schema, so use the far-future "never expires" sentinel: it stays
            // dispensable/available and sorts last in FEFO, instead of expiring next day.
            'expiration_date' => self::NO_EXPIRY_SENTINEL,
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
