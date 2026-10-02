<?php

namespace App\Repositories;

use App\Models\Accountant;
use App\Models\Address;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\MedicineAvailability;
use App\Models\PurchasedMedicine;
use App\Models\StockIn;
use App\Models\User;
use App\Services\MedicineInventoryService;
use App\Traits\LogsActivity;
use Illuminate\Support\Arr;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class MedicineAvailabilityRepository
 *
 * @version February 17, 2020, 5:34 am UTC
 */
class MedicineAvailabilityRepository extends BaseRepository
{
    use LogsActivity;
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'purchase_numeber',
        'purchase_date',
        'history_number',
        'supplier_name',
    ];

    /**
     * Return searchable fields
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return StockIn::class;
    }

    public function getMedicine()
    {
        $data['medicines'] = Medicine::all()->pluck('name', 'id')->toArray();

        return $data;
    }

    public function getMedicineList()
    {
        $result = Medicine::all()->pluck('name', 'id')->toArray();

        $medicines = [];
        foreach ($result as $key => $item) {
            $medicines[] = [
                'key' => $key,
                'value' => $item,
            ];
        }

        return $medicines;
    }

    public function getCategoryList()
    {
        $result = Category::all()->pluck('name', 'id')->toArray();

        $category = [];
        foreach ($result as $key => $item) {
            $medicines[] = [
                'key' => $key,
                'value' => $item,
            ];
        }

        return $category;
    }

    public function getCategory()
    {
        $data['categories'] = Category::all()->pluck('name', 'id')->toArray();

        return $data;
    }

    /**
     * @param  bool  $mail
     */
    public function store(array $input): bool
    {
        try {
            DB::beginTransaction();
            $purchaseMedicineArray = Arr::only($input, $this->model->getFillable());

            \Illuminate\Support\Facades\Log::info('Creating MedicineAvailability with data:', $purchaseMedicineArray);

            $medicineAvailability = MedicineAvailability::create($purchaseMedicineArray);

            foreach ($input['medicine'] as $key => $value) {
                $medicine = Medicine::findOrFail($input['medicine'][$key]);
                $previousAvailable = (int) ($medicine->available_quantity ?? 0);
                // Do NOT manually bump quantity/available_quantity here — recordStockIn()'s
                // syncMedicineTotals() recomputes them from the batch ledger and is the single
                // source of truth. The manual bump fed an inflated value into the monotonic
                // baseline_quantity and skewed low-stock alerts. INV-4.

                $batch = app(MedicineInventoryService::class)->recordStockIn([
                    'medicine_id' => $medicine->id,
                    'quantity' => (int) $input['quantity'][$key],
                    'dosage' => $input['dosage'][$key] ?? null,
                    'batch_number' => ($input['batch_number'][$key] ?? null) ?: null,
                    'manufacturing_date' => $input['manufacturing_date'][$key] ?? null,
                    'expiration_date' => $input['expiry_date'][$key] ?? null,
                    'supplier_name' => $input['supplier_name'] ?? null,
                    'date_received' => now()->toDateString(),
                    'opening_balance_before' => $previousAvailable,
                    'user_id' => getLogInUserId(),
                    'reference' => $medicineAvailability,
                    'remarks' => 'Stock-in from procurement form',
                ]);

                // The line remembers the batch it created, so a later edit / delete reverses
                // exactly that batch. H-04 / H-05.
                PurchasedMedicine::create([
                    'medicine_availabilities_id' => $medicineAvailability->id,
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'dosage' => $input['dosage'][$key] ?? null,
                    'manufacturing_date' => $input['manufacturing_date'][$key] ?? null,
                    'expiry_date' => $input['expiry_date'][$key] ?? null,
                    'quantity' => $input['quantity'][$key],
                ]);

                // Log medicine procurement activity
                self::logMedicineProcurement(
                    $medicine,
                    $input['quantity'][$key],
                    [
                        'batch_no' => $input['manufacturing_date'][$key],
                        'expiry_date' => $input['expiry_date'][$key],
                    ]
                );
            }

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            // A taken stock-in number is not a failure of the stock-in: let the caller draw another number.
            if ($e instanceof \Illuminate\Database\QueryException && (int) ($e->errorInfo[1] ?? 0) === 1062) {
                throw $e;
            }

            \Illuminate\Support\Facades\Log::error('Purchase Medicine Store Error: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Stack Trace: ' . $e->getTraceAsString());
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * Update purchase medicine with quantity adjustment
     */
    public function updatePurchaseMedicine(array $input, int $id): bool
    {
        try {
            DB::beginTransaction();

            $medicineAvailability = MedicineAvailability::findOrFail($id);
            $purchaseMedicineArray = Arr::only($input, $medicineAvailability->getFillable());
            $medicineAvailability->update($purchaseMedicineArray);

            $inventory = app(MedicineInventoryService::class);

            // Get existing purchased medicines
            $existingPurchasedMedicines = PurchasedMedicine::where('medicine_availabilities_id', $id)->get()->keyBy('id');

            // Pass 1 - take back whatever a removed or re-identified line had added, BEFORE
            // anything new is recorded, so a batch that can no longer give the stock back stops
            // the whole edit (nothing is committed) instead of leaving inventory half-changed.
            $incomingIds = collect($input['purchased_medicine_id'] ?? [])->filter()->map(fn ($v) => (int) $v)->all();

            foreach ($existingPurchasedMedicines as $existingId => $line) {
                if (! in_array((int) $existingId, $incomingIds, true)) {
                    $this->reverseLine($inventory, $line, (int) $line->quantity, $medicineAvailability, 'Removed line from procurement update');
                    $line->delete();
                    unset($existingPurchasedMedicines[$existingId]);
                }
            }

            foreach ($input['medicine'] as $key => $value) {
                $purchasedMedicineId = $input['purchased_medicine_id'][$key] ?? null;
                $newQuantity = (int) $input['quantity'][$key];
                $medicineId = (int) $input['medicine'][$key];
                $dosage = $input['dosage'][$key] ?? null;
                $manufacturingDate = $input['manufacturing_date'][$key] ?? null;
                $expiryDate = $input['expiry_date'][$key] ?? null;
                $medicine = Medicine::findOrFail($medicineId);

                $existing = $purchasedMedicineId ? ($existingPurchasedMedicines[$purchasedMedicineId] ?? null) : null;

                if (! $existing) {
                    // New line on an existing procurement.
                    $batch = $inventory->recordStockIn([
                        'medicine_id' => $medicine->id,
                        'quantity' => $newQuantity,
                        'dosage' => $dosage,
                        'batch_number' => ($input['batch_number'][$key] ?? null) ?: null,
                        'manufacturing_date' => $manufacturingDate,
                        'expiration_date' => $expiryDate,
                        'supplier_name' => $input['supplier_name'] ?? null,
                        'date_received' => now()->toDateString(),
                        'opening_balance_before' => (int) ($medicine->available_quantity ?? 0),
                        'user_id' => getLogInUserId(),
                        'reference' => $medicineAvailability,
                        'remarks' => 'New batch from updated procurement',
                    ]);

                    PurchasedMedicine::create([
                        'medicine_availabilities_id' => $medicineAvailability->id,
                        'medicine_id' => $medicine->id,
                        'batch_id' => $batch->id,
                        'dosage' => $dosage,
                        'manufacturing_date' => $manufacturingDate,
                        'expiry_date' => $expiryDate,
                        'quantity' => $newQuantity,
                    ]);

                    self::logMedicineProcurement($medicine, $newQuantity, [
                        'batch_no' => $manufacturingDate,
                        'expiry_date' => $expiryDate,
                    ]);

                    continue;
                }

                $oldQuantity = (int) $existing->quantity;
                $identityChanged = (int) $existing->medicine_id !== $medicineId
                    || trim((string) $existing->dosage) !== trim((string) $dosage)
                    || ! $this->sameDate($existing->expiry_date, $expiryDate);

                if ($identityChanged) {
                    // A different medicine / dosage / expiry is a different batch: take back the
                    // ENTIRE old quantity from the old batch, then record the new line as a fresh
                    // stock-in. Previously only the quantity difference was applied, so the old
                    // medicine kept its stock and the ledger disagreed with the document.
                    $this->reverseLine($inventory, $existing, $oldQuantity, $medicineAvailability, 'Line changed in procurement update');

                    $batch = $inventory->recordStockIn([
                        'medicine_id' => $medicine->id,
                        'quantity' => $newQuantity,
                        'dosage' => $dosage,
                        'batch_number' => ($input['batch_number'][$key] ?? null) ?: null,
                        'manufacturing_date' => $manufacturingDate,
                        'expiration_date' => $expiryDate,
                        'supplier_name' => $input['supplier_name'] ?? null,
                        'date_received' => now()->toDateString(),
                        'opening_balance_before' => (int) ($medicine->available_quantity ?? 0),
                        'user_id' => getLogInUserId(),
                        'reference' => $medicineAvailability,
                        'remarks' => 'Stock-in line changed in updated procurement',
                    ]);

                    $existing->update([
                        'medicine_id' => $medicine->id,
                        'batch_id' => $batch->id,
                        'dosage' => $dosage,
                        'manufacturing_date' => $manufacturingDate,
                        'expiry_date' => $expiryDate,
                        'quantity' => $newQuantity,
                    ]);

                    self::logMedicineUpdate($medicine, $newQuantity - $oldQuantity, [
                        'batch_no' => $manufacturingDate,
                        'expiry_date' => $expiryDate,
                        'action' => 'line changed',
                    ]);

                    continue;
                }

                // Same medicine / dosage / expiry: only the quantity difference moves, in the
                // batch this line created.
                $quantityDifference = $newQuantity - $oldQuantity;

                if ($quantityDifference > 0) {
                    if ($existing->batch_id && \App\Models\MedicineBatch::whereKey($existing->batch_id)->exists()) {
                        $inventory->increaseBatch(
                            (int) $existing->batch_id,
                            $quantityDifference,
                            getLogInUserId(),
                            $medicineAvailability,
                            'Stock-in adjustment from updated procurement'
                        );
                    } else {
                        $batch = $inventory->recordStockIn([
                            'medicine_id' => $medicine->id,
                            'quantity' => $quantityDifference,
                            'dosage' => $dosage,
                            'batch_number' => ($input['batch_number'][$key] ?? null) ?: null,
                            'manufacturing_date' => $manufacturingDate,
                            'expiration_date' => $expiryDate,
                            'supplier_name' => $input['supplier_name'] ?? null,
                            'date_received' => now()->toDateString(),
                            'opening_balance_before' => (int) ($medicine->available_quantity ?? 0),
                            'user_id' => getLogInUserId(),
                            'reference' => $medicineAvailability,
                            'remarks' => 'Stock-in adjustment from updated procurement',
                        ]);
                        $existing->batch_id = $batch->id;
                    }
                } elseif ($quantityDifference < 0) {
                    $this->reverseLine($inventory, $existing, abs($quantityDifference), $medicineAvailability, 'Stock-in quantity reduced in procurement update');
                }

                $existing->update([
                    'manufacturing_date' => $manufacturingDate,
                    'quantity' => $newQuantity,
                ]);

                if ($quantityDifference !== 0) {
                    self::logMedicineUpdate($medicine->fresh(), $quantityDifference, [
                        'batch_no' => $manufacturingDate,
                        'expiry_date' => $expiryDate,
                        'action' => $quantityDifference > 0 ? 'increased' : 'decreased',
                    ]);
                }
            }

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * Take $quantity units of a stock-in line back out of the batch that line created.
     */
    private function reverseLine(MedicineInventoryService $inventory, PurchasedMedicine $line, int $quantity, MedicineAvailability $reference, string $remarks): void
    {
        if (! $line->medicine_id) {
            return;
        }

        $inventory->reverseStockIn(
            (int) $line->medicine_id,
            $quantity,
            $line->batch_id ? (int) $line->batch_id : null,
            $line->dosage,
            $line->expiry_date,
            getLogInUserId(),
            $reference,
            $remarks
        );
    }

    private function sameDate($a, $b): bool
    {
        $left = trim((string) $a);
        $right = trim((string) $b);

        if ($left === $right) {
            return true;
        }

        try {
            return \Carbon\Carbon::parse($left)->toDateString() === \Carbon\Carbon::parse($right)->toDateString();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Log medicine update activity
     */
    protected static function logMedicineUpdate($medicine, $quantityChange, $details = [])
    {
        // logActivity(string $action, string $description, array $details) — the previous call
        // passed the class as $action and the id as $description with no subject_type/subject_id,
        // so every medicine-update log collapsed onto ONE overwriting row. Pass proper args so
        // the unique key is action + Medicine + medicine_id. INV/AUDIT-1.
        self::logActivity(
            'medicine_quantity_updated',
            "Updated stock for {$medicine->name} by {$quantityChange}",
            [
                'subject_type' => 'Medicine',
                'subject_id' => $medicine->id,
                'properties' => [
                    'medicine_name' => $medicine->name,
                    'quantity_change' => $quantityChange,
                    'new_quantity' => $medicine->quantity,
                    'new_available_quantity' => $medicine->available_quantity,
                    'details' => $details,
                ],
            ]
        );
    }

    /**
     * @return bool|Builder|Builder[]|Collection|Model
     */
    public function updateAccountant($accountant, $input)
    {
        try {
            unset($input['password']);

            /** @var User $user */
            $user = User::find($accountant->user->id);
            if (isset($input['image']) && ! empty($input['image'])) {
                $mediaId = updateProfileImage($user, $input['image']);
            }
            if ($input['avatar_remove'] == 1 && isset($input['avatar_remove']) && ! empty($input['avatar_remove'])) {
                removeFile($user, User::COLLECTION_PROFILE_PICTURES);
            }

            /** @var Accountant $accountant */
            $input['phone'] = preparePhoneNumber($input, 'phone');
            $input['dob'] = (! empty($input['dob'])) ? $input['dob'] : null;
            $accountant->user->update($input);
            $accountant->update($input);

            if (! empty($accountant->address)) {
                if (empty($address = Address::prepareAddressArray($input))) {
                    $accountant->address->delete();
                }
                $accountant->address->update($input);
            } else {
                if (! empty($address = Address::prepareAddressArray($input)) && empty($accountant->address)) {
                    $ownerId = $accountant->id;
                    $ownerType = Accountant::class;
                    Address::create(array_merge($address, ['owner_id' => $ownerId, 'owner_type' => $ownerType]));
                }
            }

            return true;
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }
}
