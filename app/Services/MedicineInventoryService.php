<?php

namespace App\Services;

use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineTransaction;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
                $batchNumber = $this->generateBatchNumber('BATCH', $medicineId);
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

            // Adding to an existing batch number must not silently move the expiry of the units
            // already on the shelf. A different expiry means a different batch. M-03.
            if ($batch->exists
                && ! empty($data['expiration_date'])
                && $batch->expiration_date
                && Carbon::parse($data['expiration_date'])->toDateString() !== $batch->expiration_date->toDateString()) {
                throw new RuntimeException(
                    'Batch number ' . $batchNumber . ' already exists for this medicine with expiry '
                    . $batch->expiration_date->toDateString() . '. Use the same expiry date or a different batch number.'
                );
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

            // A doctor switches a prescription off to stop the pharmacy. The flag is checked on the locked
            // row, so a deactivation that happens while someone is pressing "Dispense" still wins. R3-H3.
            if ($prescription->is_active !== null && ! $prescription->is_active) {
                throw new RuntimeException('This prescription has been deactivated by the doctor and cannot be dispensed.');
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

                // Deduct from batches of the PRESCRIBED strength only; without the dosage FEFO
                // could hand out a 250 mg batch for a 500 mg order. M-02.
                $allocationSummary[$line->medicine] = $this->deductStockFefo(
                    (int) $line->medicine,
                    $quantity,
                    $userId,
                    $prescription,
                    'Prescription #' . $prescription->id . ' dispensed',
                    MedicineTransaction::TYPE_DISPENSE,
                    $line->dosage !== null ? (string) $line->dosage : null
                );
            }

            $this->recordDispensedItems($prescription, $allocationSummary);

            $prescription->update([
                'status' => Prescription::DISPENSE_STATUS_DISPENSED,
                'dispensed_at' => now(),
                'dispensed_by' => $userId,
            ]);

            return $allocationSummary;
        });
    }

    /**
     * Replace the placeholder items of the prescription's dispense record with what was actually
     * handed out (one row per dosage/expiry batch), so the dispense history shows the real
     * batches and a later reversal returns stock to the right batch. M-01.
     *
     * @param  array<int, array<int, array<string, mixed>>>  $allocationSummary  medicine id => FEFO allocations
     */
    private function recordDispensedItems(Prescription $prescription, array $allocationSummary): void
    {
        $dispenseRecord = DispenseRecord::whereModelType(Prescription::class)
            ->whereModelId($prescription->id)
            ->first();

        if (! $dispenseRecord) {
            $dispenseRecord = DispenseRecord::create([
                'history_number' => 'HIS' . generateUniqueHistoryNumber(),
                'patient_id' => $prescription->patient_id,
                'doctor_id' => $prescription->doctor_id,
                'model_type' => Prescription::class,
                'model_id' => $prescription->id,
                'bill_date' => now(),
            ]);
        }

        $dispenseRecord->dispenseItems()->delete();

        foreach ($allocationSummary as $medicineId => $allocations) {
            $grouped = collect($allocations)
                ->filter(fn (array $allocation) => (int) ($allocation['deducted'] ?? 0) > 0)
                ->groupBy(fn (array $allocation) => trim((string) ($allocation['dosage'] ?? '')) . '|' . ($allocation['expiration_date'] ?? ''));

            foreach ($grouped as $rows) {
                $first = $rows->first();
                $dosage = trim((string) ($first['dosage'] ?? ''));

                DispenseRecordItem::create([
                    'dispense_id' => $dispenseRecord->id,
                    'medicine_id' => (int) $medicineId,
                    'dosage' => $dosage !== '' ? $dosage : null,
                    'expires_at' => $first['expiration_date'] ?? null,
                    'quantity' => (int) $rows->sum(fn (array $allocation) => (int) $allocation['deducted']),
                ]);
            }
        }
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
                    'batch_number' => $this->generateBatchNumber('RESTORE', $medicineId),
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
     * Give units back to the batches a document (a consultation) originally took them from.
     *
     * Every FEFO deduction is a ledger row that references the document, so the net number of units
     * the document still holds out of each batch is known: dispensed minus already returned. Units go
     * back to those batches, the one handed out last first, EVEN IF the batch has expired since - an
     * expired batch stays expired and is never turned into stock that can be dispensed again.
     *
     * Units with no ledger trail (consultations recorded before stock was tracked per batch) go to the
     * earliest unexpired batch of the same medicine and strength; with none, to a dedicated batch that is
     * already expired, so the quantity is not lost but must be checked before it is used. R3-H1.
     */
    public function restoreStockToReferencedBatches(
        int $medicineId,
        int $quantity,
        Model $reference,
        ?string $dosage = null,
        ?int $userId = null,
        ?string $remarks = null
    ): void {
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use ($medicineId, $quantity, $reference, $dosage, $userId, $remarks) {
            $medicine = Medicine::find($medicineId);
            if (! $medicine) {
                return;
            }

            $remaining = $quantity;

            $ledger = MedicineTransaction::query()
                ->where('reference_type', get_class($reference))
                ->where('reference_id', $reference->getKey())
                ->whereIn('transaction_type', [MedicineTransaction::TYPE_DISPENSE, MedicineTransaction::TYPE_ADJUSTMENT])
                ->whereHas('batch', function ($query) use ($medicineId, $dosage) {
                    $query->where('medicine_id', $medicineId);
                    $this->applyDosageFilter($query, $dosage);
                })
                ->orderBy('id')
                ->get(['id', 'batch_id', 'transaction_type', 'quantity']);

            $netOut = [];
            $lastHandedOut = [];
            foreach ($ledger as $entry) {
                $batchId = (int) $entry->batch_id;
                if ($entry->transaction_type === MedicineTransaction::TYPE_DISPENSE) {
                    $netOut[$batchId] = ($netOut[$batchId] ?? 0) + (int) $entry->quantity;
                    $lastHandedOut[$batchId] = (int) $entry->id;
                } else {
                    $netOut[$batchId] = ($netOut[$batchId] ?? 0) - (int) $entry->quantity;
                }
            }

            $netOut = array_filter($netOut, fn (int $units) => $units > 0);
            uksort($netOut, fn (int $a, int $b) => ($lastHandedOut[$b] ?? 0) <=> ($lastHandedOut[$a] ?? 0));

            foreach ($netOut as $batchId => $units) {
                if ($remaining <= 0) {
                    break;
                }

                $batch = MedicineBatch::lockForUpdate()->find($batchId);
                if (! $batch) {
                    continue;
                }

                $give = min($units, $remaining);
                $batch->quantity = (int) $batch->quantity + $give;
                $batch->save();

                $this->createTransaction(
                    $batch,
                    MedicineTransaction::TYPE_ADJUSTMENT,
                    $give,
                    (int) $batch->quantity,
                    $userId,
                    $reference,
                    $remarks ?? 'Stock returned to the batch it was taken from'
                );

                $remaining -= $give;
            }

            if ($remaining > 0) {
                $this->restoreUntracedUnits($medicine, $remaining, $reference, $dosage, $userId, $remarks);
            }

            $this->syncMedicineTotals($medicineId);
        });
    }

    /**
     * Return units whose original batch is unknown (see restoreStockToReferencedBatches).
     */
    private function restoreUntracedUnits(
        Medicine $medicine,
        int $quantity,
        Model $reference,
        ?string $dosage,
        ?int $userId,
        ?string $remarks
    ): void {
        $query = MedicineBatch::where('medicine_id', $medicine->id)
            ->where(function ($query) {
                $query->whereNull('expiration_date')
                    ->orWhereDate('expiration_date', '>=', Carbon::today()->toDateString());
            });
        $this->applyDosageFilter($query, $dosage);

        $batch = $query->orderByRaw('expiration_date IS NULL')
            ->orderBy('expiration_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->first();

        if (! $batch) {
            $normalizedDosage = trim((string) $dosage);
            $batch = new MedicineBatch([
                'medicine_id' => $medicine->id,
                'batch_number' => $this->generateBatchNumber('RESTORE', (int) $medicine->id),
                'dosage' => $normalizedDosage !== '' ? $normalizedDosage : $medicine->dosage,
                'quantity' => 0,
                // Already expired on purpose: the original expiry is unknown, so these units must be
                // checked by a person before they can be given to a patient.
                'expiration_date' => Carbon::yesterday()->toDateString(),
                'date_received' => Carbon::today()->toDateString(),
                'supplier_name' => 'Stock restoration (original batch unknown)',
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
            ($remarks ?? 'Stock returned') . ' (original batch unknown)'
        );
    }

    /**
     * Restrict a batch query to one strength. An empty strength means "any"; "N/A" means no strength.
     */
    private function applyDosageFilter($query, ?string $dosage): void
    {
        $normalizedDosage = trim((string) $dosage);

        if ($normalizedDosage === '') {
            return;
        }

        if (strcasecmp($normalizedDosage, 'N/A') === 0) {
            $query->where(function ($inner) {
                $inner->whereNull('dosage')->orWhere('dosage', '')->orWhere('dosage', 'N/A');
            });

            return;
        }

        $query->where('dosage', $normalizedDosage);
    }

    /**
     * Take back stock that a stock-in ADDED (deleting a stock-in, lowering a line, removing a
     * line, or changing its medicine / dosage / expiry).
     *
     * The stock is removed from the batch that entry created (preferably by $batchId; otherwise
     * the batch with the same medicine + dosage + expiry), NOT by FEFO across all batches: FEFO
     * emptied the earliest-expiring batch instead, so expired stock looked valid and valid stock
     * looked expired. If that batch no longer holds enough units (they were dispensed), nothing
     * is changed and a RuntimeException explains why. H-04 / H-05.
     *
     * Legacy lines with no identifiable batch fall back to FEFO for the same dosage.
     */
    public function reverseStockIn(
        int $medicineId,
        int $quantity,
        ?int $batchId = null,
        ?string $dosage = null,
        ?string $expirationDate = null,
        ?int $userId = null,
        ?Model $reference = null,
        ?string $remarks = null
    ): void {
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use ($medicineId, $quantity, $batchId, $dosage, $expirationDate, $userId, $reference, $remarks) {
            $batch = null;

            if ($batchId) {
                $batch = MedicineBatch::lockForUpdate()->where('medicine_id', $medicineId)->find($batchId);
            }

            if (! $batch && $expirationDate) {
                $query = MedicineBatch::lockForUpdate()
                    ->where('medicine_id', $medicineId)
                    ->whereDate('expiration_date', Carbon::parse($expirationDate)->toDateString());

                $normalizedDosage = trim((string) $dosage);
                if ($normalizedDosage !== '' && strcasecmp($normalizedDosage, 'N/A') !== 0) {
                    $query->where('dosage', $normalizedDosage);
                }

                $batch = $query->orderBy('id')->first();
            }

            if (! $batch) {
                // Nothing identifies the batch (very old data): keep the previous behaviour.
                $this->deductStockFefo(
                    $medicineId,
                    $quantity,
                    $userId,
                    $reference,
                    $remarks ?? 'Stock-in reversed',
                    MedicineTransaction::TYPE_ADJUSTMENT,
                    $dosage
                );

                return;
            }

            if ((int) $batch->quantity < $quantity) {
                $medicine = Medicine::find($medicineId);
                throw new RuntimeException(
                    'Cannot reverse ' . $quantity . ' unit(s) of ' . ($medicine ? $medicine->display_name : 'this medicine')
                    . ' from batch ' . $batch->batch_number . ': only ' . (int) $batch->quantity
                    . ' remain, the rest were already dispensed.'
                );
            }

            $batch->quantity = (int) $batch->quantity - $quantity;
            $batch->save();

            $this->createTransaction(
                $batch,
                MedicineTransaction::TYPE_ADJUSTMENT,
                $quantity,
                (int) $batch->quantity,
                $userId,
                $reference,
                $remarks ?? 'Stock-in reversed'
            );

            $this->syncMedicineTotals($medicineId);
        });
    }

    /**
     * Add units to a specific existing batch (raising the quantity of a stock-in line).
     */
    public function increaseBatch(
        int $batchId,
        int $quantity,
        ?int $userId = null,
        ?Model $reference = null,
        ?string $remarks = null
    ): MedicineBatch {
        if ($quantity <= 0) {
            throw new RuntimeException('A positive quantity is required.');
        }

        return DB::transaction(function () use ($batchId, $quantity, $userId, $reference, $remarks) {
            $batch = MedicineBatch::lockForUpdate()->findOrFail($batchId);

            $batch->quantity = (int) $batch->quantity + $quantity;
            $batch->save();

            $this->createTransaction(
                $batch,
                MedicineTransaction::TYPE_STOCK_IN,
                $quantity,
                (int) $batch->quantity,
                $userId,
                $reference,
                $remarks ?? 'Stock-in quantity increased'
            );

            $this->syncMedicineTotals((int) $batch->medicine_id);

            return $batch->fresh();
        });
    }

    /**
     * Batch numbers generated by the system carry a random suffix: a timestamp alone collided when
     * two entries were saved within the same second (silent merge, or a unique-key failure that
     * rolled back a whole edit). M-03.
     */
    private function generateBatchNumber(string $prefix, int $medicineId): string
    {
        do {
            $candidate = $prefix . '-' . $medicineId . '-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));
        } while (MedicineBatch::where('medicine_id', $medicineId)->where('batch_number', $candidate)->exists());

        return $candidate;
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
     * Refresh medicines.available_quantity for medicines whose stored figure is out of date because a batch
     * expired (the column is otherwise recalculated only when stock moves, so it kept counting expired units
     * for days). Returns one row per medicine that was, or with $dryRun would be, corrected. R3-H5.
     *
     * @return array<int, array{id: int, name: string, stored: int, actual: int}>
     */
    public function syncExpiredAvailability(bool $dryRun = false): array
    {
        $today = Carbon::today()->toDateString();

        // Unexpired units per medicine, in one query.
        $usable = MedicineBatch::query()
            ->select('medicine_id', DB::raw('SUM(quantity) AS total'))
            ->where('quantity', '>', 0)
            ->where(function ($query) use ($today) {
                $query->whereNull('expiration_date')->orWhereDate('expiration_date', '>=', $today);
            })
            ->groupBy('medicine_id')
            ->pluck('total', 'medicine_id');

        $corrected = [];

        // Only medicines that have batch rows are tracked by batch; the others keep their aggregate figure.
        $batched = MedicineBatch::query()->select('medicine_id')->distinct()->pluck('medicine_id');

        Medicine::query()->whereIn('id', $batched)->select('id', 'name', 'available_quantity')->chunkById(200, function ($medicines) use ($usable, $dryRun, &$corrected) {
            foreach ($medicines as $medicine) {
                $actual = (int) ($usable[$medicine->id] ?? 0);

                if ((int) $medicine->available_quantity === $actual) {
                    continue;
                }

                $corrected[] = ['id' => (int) $medicine->id, 'name' => (string) $medicine->name, 'stored' => (int) $medicine->available_quantity, 'actual' => $actual];

                if (! $dryRun) {
                    $this->syncMedicineTotals((int) $medicine->id);
                }
            }
        });

        return $corrected;
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
