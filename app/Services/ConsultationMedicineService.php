<?php

namespace App\Services;

use App\Models\DocumentIssuance;
use App\Support\ConsultationInput;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Log;

class ConsultationMedicineService
{
    use LogsActivity;

    private MedicineInventoryService $medicineInventoryService;

    public function __construct(MedicineInventoryService $medicineInventoryService)
    {
        $this->medicineInventoryService = $medicineInventoryService;
    }

    /**
     * The medicine rows submitted by the consultation form, validated and normalised.
     *
     * Empty rows are skipped, and Plan rows are ignored for anyone who may not manage them. The same
     * medicine + strength twice in one section is refused: such rows used to collapse into one on the next
     * edit, so the stock and the record drifted apart. R3-H2.
     *
     * @return array<int, array{id: ?int, medicine_id: int, dosage: ?string, quantity: int, used_for: string, dosage_instructions: ?string}>
     */
    private function submittedMedicineRows(array $data, array $savedKeys = []): array
    {
        $canManagePlanMedicines = $this->canCurrentUserManagePlanMedicines();
        $rows = [];
        $seen = [];
        $seenIds = [];

        foreach (($data['medicines'] ?? []) as $usedFor => $medicines) {
            if (! in_array($usedFor, ['plan', 'nursing'], true)) {
                continue;
            }

            if ($usedFor === 'plan' && ! $canManagePlanMedicines) {
                continue;
            }

            if (! is_array($medicines)) {
                continue;
            }

            foreach ($medicines as $medicineData) {
                if (! is_array($medicineData) || empty($medicineData['medicine_id']) || empty($medicineData['quantity'])) {
                    continue;
                }

                $quantity = (int) $medicineData['quantity'];
                if ($quantity <= 0) {
                    continue;
                }

                $medicineId = (int) $medicineData['medicine_id'];
                $dosage = ConsultationInput::nullableString($medicineData['dosage'] ?? null);
                $key = $this->medicineRowKey($medicineId, $dosage, (string) $usedFor);
                $rowId = ! empty($medicineData['id']) ? (int) $medicineData['id'] : null;
                if ($rowId !== null && isset($seenIds[$rowId])) {
                    throw new \RuntimeException('A saved medicine row was submitted more than once.');
                }
                $retained = $rowId !== null && ($savedKeys[$rowId] ?? null) === $key;

                if (isset($seen[$key]) && (! $seen[$key] || ! $retained)) {
                    $name = \App\Models\Medicine::find($medicineId)?->display_name ?? 'A medicine';

                    throw new \RuntimeException(
                        $name . ($dosage !== null ? ' (' . $dosage . ')' : '') . ' is listed more than once under '
                        . ($usedFor === 'plan' ? 'Plan' : 'Nursing Intervention') . '. Combine the quantities into one row.'
                    );
                }
                $seen[$key] = ($seen[$key] ?? true) && $retained;
                if ($rowId !== null) {
                    $seenIds[$rowId] = true;
                }

                $rows[] = [
                    'id' => $rowId,
                    'medicine_id' => $medicineId,
                    'dosage' => $dosage,
                    'quantity' => $quantity,
                    'used_for' => (string) $usedFor,
                    'dosage_instructions' => ConsultationInput::nullableString($medicineData['dosage_instructions'] ?? null),
                ];
            }
        }

        return $rows;
    }

    /**
     * Identity of a medicine row inside one consultation: medicine + strength + section.
     */
    private function medicineRowKey(int $medicineId, ?string $dosage, string $usedFor): string
    {
        return $medicineId . '|' . mb_strtolower(trim((string) $dosage)) . '|' . $usedFor;
    }

    /**
     * Handle medicine updates during consultation form edit.
     *
     * The submitted list is compared with the saved rows (matched by row id, or by medicine + strength +
     * section for forms that sent no ids):
     *  - saved rows that are no longer submitted, or whose medicine / strength changed, give their stock back;
     *  - rows with a lower quantity give the difference back;
     *  - rows with a higher quantity, and new rows, take stock out.
     * Stock is returned first, so replacing one medicine by another never fails for lack of stock.
     */
    public function update(DocumentIssuance $requestDocument, array $data): void
    {
        $canManagePlanMedicines = $this->canCurrentUserManagePlanMedicines();

        // A nurse cannot see or change Plan medicines, so those saved rows are left exactly as they are.
        $existing = $requestDocument->consultationMedicines()->get()
            ->filter(fn ($saved) => $canManagePlanMedicines || $saved->used_for !== 'plan')
            ->keyBy('id');
        $savedKeys = $existing->map(fn ($line) => $this->medicineRowKey($line->medicine_id, $line->dosage, (string) $line->used_for))->all();
        // A merge may make two saved identities equal. Retain their IDs and instructions; refuse new duplicates.
        $rows = $this->submittedMedicineRows($data, $savedKeys);

        $paired = []; // submitted row index => saved row
        $claimed = []; // saved row id => true

        foreach ($rows as $index => $row) {
            if ($row['id'] !== null && $existing->has($row['id']) && ! isset($claimed[$row['id']])) {
                $paired[$index] = $existing[$row['id']];
                $claimed[$row['id']] = true;
            }
        }

        $unclaimedByKey = [];
        foreach ($existing as $saved) {
            if (! isset($claimed[$saved->id])) {
                $unclaimedByKey[$this->medicineRowKey((int) $saved->medicine_id, $saved->dosage, (string) $saved->used_for)][] = $saved;
            }
        }

        foreach ($rows as $index => $row) {
            if (isset($paired[$index])) {
                continue;
            }

            $key = $this->medicineRowKey($row['medicine_id'], $row['dosage'], $row['used_for']);
            if (! empty($unclaimedByKey[$key])) {
                $saved = array_shift($unclaimedByKey[$key]);
                $paired[$index] = $saved;
                $claimed[$saved->id] = true;
            }
        }

        $toRemove = $existing->reject(fn ($saved) => isset($claimed[$saved->id]))->values();
        $adjust = [];
        $add = [];

        foreach ($rows as $index => $row) {
            $saved = $paired[$index] ?? null;

            if ($saved && $this->medicineRowKey((int) $saved->medicine_id, $saved->dosage, (string) $saved->used_for)
                === $this->medicineRowKey($row['medicine_id'], $row['dosage'], $row['used_for'])) {
                $adjust[] = [$saved, $row];

                continue;
            }

            if ($saved) {
                // The same row now holds a different medicine / strength / section: replace it.
                $toRemove->push($saved);
            }

            $add[] = $row;
        }

        // STEP 1: stock goes back first - removed rows and lowered quantities.
        foreach ($toRemove as $saved) {
            $this->restore($saved, $requestDocument);
            $saved->delete();

            Log::info('Medicine removed and stock restored during edit', [
                'consultation_id' => $requestDocument->id,
                'medicine_id' => $saved->medicine_id,
                'dosage' => $saved->dosage,
                'quantity_restored' => $saved->quantity,
                'used_for' => $saved->used_for,
            ]);
        }

        foreach ($adjust as [$saved, $row]) {
            $difference = $row['quantity'] - (int) $saved->quantity;

            if ($difference < 0) {
                $this->restoreMedicineStockAmount($saved->medicine_id, $saved->dosage, abs($difference), $requestDocument);
            }
        }

        // STEP 2: stock goes out - raised quantities, then new rows. Instruction-only edits are saved too.
        foreach ($adjust as [$saved, $row]) {
            $difference = $row['quantity'] - (int) $saved->quantity;

            if ($difference > 0) {
                $this->deductMedicineStock($saved->medicine_id, $saved->dosage, $difference, $requestDocument);
            }

            if ($difference !== 0 || (string) $saved->dosage_instructions !== (string) $row['dosage_instructions']) {
                $oldQuantity = (int) $saved->quantity;
                $saved->quantity = $row['quantity'];
                $saved->dosage_instructions = $row['dosage_instructions'];
                $saved->save();

                if ($difference !== 0) {
                    Log::info('Medicine quantity updated during edit', [
                        'consultation_id' => $requestDocument->id,
                        'medicine_id' => $saved->medicine_id,
                        'dosage' => $saved->dosage,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $row['quantity'],
                    ]);
                }
            }
        }

        foreach ($add as $row) {
            $this->deductMedicineStock($row['medicine_id'], $row['dosage'], $row['quantity'], $requestDocument);

            \App\Models\ConsultationMedicine::create([
                'request_document_id' => $requestDocument->id,
                'medicine_id' => $row['medicine_id'],
                'quantity' => $row['quantity'],
                'dosage' => $row['dosage'],
                'used_for' => $row['used_for'],
                'dosage_instructions' => $row['dosage_instructions'],
            ]);

            Log::info('New medicine added during edit', [
                'consultation_id' => $requestDocument->id,
                'medicine_id' => $row['medicine_id'],
                'dosage' => $row['dosage'],
                'quantity_deducted' => $row['quantity'],
                'used_for' => $row['used_for'],
            ]);
        }
    }

    /**
     * Restore medicine stock when medicine is removed from consultation
     */
    public function restore(\App\Models\ConsultationMedicine $consultationMedicine, DocumentIssuance $requestDocument): void
    {
        $this->restoreMedicineStockAmount(
            (int) $consultationMedicine->medicine_id,
            $consultationMedicine->dosage,
            (int) $consultationMedicine->quantity,
            $requestDocument
        );
    }

    /**
     * Give a specific amount of medicine back to the batches this consultation took it from. R3-H1.
     */
    private function restoreMedicineStockAmount($medicineId, $dosage, $quantity, DocumentIssuance $requestDocument)
    {
        $quantity = (int) $quantity;
        if ($quantity <= 0) {
            return;
        }

        $this->medicineInventoryService->restoreStockToReferencedBatches(
            (int) $medicineId,
            $quantity,
            $requestDocument,
            ConsultationInput::nullableString($dosage),
            auth()->id(),
            'Consultation #' . $requestDocument->id . ' medicine returned'
        );

        cache()->forget('medicine_' . (int) $medicineId);
        cache()->forget('medicines_list');
    }

    /**
     * Deduct medicine stock from inventory
     */
    private function deductMedicineStock($medicineId, $dosage, $quantity, DocumentIssuance $requestDocument)
    {
        $medicine = \App\Models\Medicine::find($medicineId);

        if (! $medicine) {
            throw new \Exception("Medicine not found (ID: {$medicineId})");
        }

        $normalizedDosage = trim((string) ($dosage ?? ''));
        $dosageFilter = $normalizedDosage !== '' ? $normalizedDosage : null;

        $this->medicineInventoryService->deductStockFefo(
            (int) $medicineId,
            (int) $quantity,
            auth()->id(),
            $requestDocument,
            'Consultation #' . $requestDocument->id . ' medicine deduction',
            \App\Models\MedicineTransaction::TYPE_DISPENSE,
            $dosageFilter
        );

        // Clear medicine cache
        cache()->forget('medicine_' . (int) $medicineId);
        cache()->forget('medicines_list');

        // Log medicine usage activity
        self::logMedicineUsage($medicine, $quantity, $requestDocument->name, $requestDocument);
    }

    /**
     * Handle medicine deduction from inventory
     */
    public function store(DocumentIssuance $requestDocument, array $data): void
    {
        // Process medicines from both Plan and Nursing Intervention. The list is validated as a whole
        // (a duplicated row throws) before the first unit leaves stock.
        foreach ($this->submittedMedicineRows($data) as $row) {
            // Deduct stock via the current FEFO batch inventory service.
            $this->deductMedicineStock($row['medicine_id'], $row['dosage'], $row['quantity'], $requestDocument);

            // Record the medicine usage in consultation_medicines table
            \App\Models\ConsultationMedicine::create([
                'request_document_id' => $requestDocument->id,
                'medicine_id' => $row['medicine_id'],
                'quantity' => $row['quantity'],
                'dosage' => $row['dosage'],
                'used_for' => $row['used_for'], // 'plan' or 'nursing'
                'dosage_instructions' => $row['dosage_instructions'],
            ]);

            $medicine = \App\Models\Medicine::find($row['medicine_id']);

            Log::info('Medicine deducted from inventory', [
                'consultation_id' => $requestDocument->id,
                'medicine_id' => $row['medicine_id'],
                'medicine_name' => $medicine?->name,
                'dosage' => $row['dosage'],
                'quantity_used' => $row['quantity'],
                'remaining_stock' => $medicine?->available_quantity,
                'used_for' => $row['used_for'],
            ]);
        }
    }

    /**
     * Only doctors can add/edit medicines under Plan.
     */
    private function canCurrentUserManagePlanMedicines(): bool
    {
        return isRole('doctor');
    }
}
