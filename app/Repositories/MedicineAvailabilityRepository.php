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
                $purchasedMedicineArray = [
                    'medicine_availabilities_id' => $medicineAvailability->id,
                    'medicine_id' => $input['medicine'][$key],
                    'dosage' => $input['dosage'][$key] ?? null,
                    'manufacturing_date' => $input['manufacturing_date'][$key],
                    'expiry_date' => $input['expiry_date'][$key] ?? null,
                    'quantity' => $input['quantity'][$key],
                ];

                \Illuminate\Support\Facades\Log::info('Creating PurchasedMedicine with data:', $purchasedMedicineArray);

                PurchasedMedicine::create($purchasedMedicineArray);
                $medicine = Medicine::find($input['medicine'][$key]);
                $previousAvailable = (int) ($medicine->available_quantity ?? 0);
                // Do NOT manually bump quantity/available_quantity here — recordStockIn()'s
                // syncMedicineTotals() recomputes them from the batch ledger and is the single
                // source of truth. The manual bump fed an inflated value into the monotonic
                // baseline_quantity and skewed low-stock alerts. INV-4.

                app(MedicineInventoryService::class)->recordStockIn([
                    'medicine_id' => $medicine->id,
                    'quantity' => (int) $input['quantity'][$key],
                    'dosage' => $input['dosage'][$key] ?? null,
                    'batch_number' => ($input['batch_number'][$key] ?? null)
                        ?: ($medicineAvailability->availability_no . '-' . $medicine->id . '-' . ($key + 1)),
                    'manufacturing_date' => $input['manufacturing_date'][$key] ?? null,
                    'expiration_date' => $input['expiry_date'][$key] ?? null,
                    'supplier_name' => $input['supplier_name'] ?? null,
                    'date_received' => now()->toDateString(),
                    'opening_balance_before' => $previousAvailable,
                    'user_id' => getLogInUserId(),
                    'reference' => $medicineAvailability,
                    'remarks' => 'Stock-in from procurement form',
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

            // Get existing purchased medicines
            $existingPurchasedMedicines = PurchasedMedicine::where('medicine_availabilities_id', $id)->get()->keyBy('id');

            foreach ($input['medicine'] as $key => $value) {
                $purchasedMedicineId = $input['purchased_medicine_id'][$key] ?? null;
                $newQuantity = $input['quantity'][$key];
                $medicineId = $input['medicine'][$key];

                if ($purchasedMedicineId && isset($existingPurchasedMedicines[$purchasedMedicineId])) {
                    // Update existing purchased medicine
                    $existingPurchasedMedicine = $existingPurchasedMedicines[$purchasedMedicineId];
                    $oldQuantity = $existingPurchasedMedicine->quantity;
                    $quantityDifference = $newQuantity - $oldQuantity;

                    $purchasedMedicineArray = [
                        'medicine_id' => $medicineId,
                        'dosage' => $input['dosage'][$key] ?? null,
                        'manufacturing_date' => $input['manufacturing_date'][$key],
                        'expiry_date' => $input['expiry_date'][$key],
                        'quantity' => $newQuantity,
                    ];

                    $existingPurchasedMedicine->update($purchasedMedicineArray);

                    // Update medicine quantities
                    $medicine = Medicine::find($medicineId);
                    if ($medicine) {
                        $previousAvailable = (int) ($medicine->available_quantity ?? 0);
                        // No manual quantity bump — the inventory service's syncMedicineTotals()
                        // recomputes totals from the batch ledger authoritatively. INV-4.

                        if ($quantityDifference > 0) {
                            app(MedicineInventoryService::class)->recordStockIn([
                                'medicine_id' => $medicine->id,
                                'quantity' => (int) $quantityDifference,
                                'dosage' => $input['dosage'][$key] ?? null,
                                'batch_number' => ($input['batch_number'][$key] ?? null)
                                    ?: ($medicineAvailability->availability_no . '-' . $medicine->id . '-' . ($key + 1)),
                                'manufacturing_date' => $input['manufacturing_date'][$key] ?? null,
                                'expiration_date' => $input['expiry_date'][$key] ?? null,
                                'supplier_name' => $input['supplier_name'] ?? null,
                                'date_received' => now()->toDateString(),
                                'opening_balance_before' => $previousAvailable,
                                'user_id' => getLogInUserId(),
                                'reference' => $medicineAvailability,
                                'remarks' => 'Stock-in adjustment from updated procurement',
                            ]);
                        } elseif ($quantityDifference < 0) {
                            app(MedicineInventoryService::class)->deductStockFefo(
                                $medicine->id,
                                abs((int) $quantityDifference),
                                getLogInUserId(),
                                $medicineAvailability,
                                'Stock-out adjustment from updated procurement',
                                \App\Models\MedicineTransaction::TYPE_ADJUSTMENT
                            );
                        }

                        // Log the update
                        if ($quantityDifference != 0) {
                            self::logMedicineUpdate(
                                $medicine,
                                $quantityDifference,
                                [
                                    'batch_no' => $input['manufacturing_date'][$key],
                                    'expiry_date' => $input['expiry_date'][$key],
                                    'action' => $quantityDifference > 0 ? 'increased' : 'decreased',
                                ]
                            );
                        }
                    }

                    unset($existingPurchasedMedicines[$purchasedMedicineId]);
                } else {
                    // Create new purchased medicine entry
                    $purchasedMedicineArray = [
                        'medicine_availabilities_id' => $medicineAvailability->id,
                        'medicine_id' => $medicineId,
                        'dosage' => $input['dosage'][$key] ?? null,
                        'manufacturing_date' => $input['manufacturing_date'][$key],
                        'expiry_date' => $input['expiry_date'][$key],
                        'quantity' => $newQuantity,
                    ];

                    PurchasedMedicine::create($purchasedMedicineArray);

                    // Add to medicine quantity
                    $medicine = Medicine::find($medicineId);
                    if ($medicine) {
                        $previousAvailable = (int) ($medicine->available_quantity ?? 0);
                        $medicineQtyArray = [
                            'quantity' => $medicine->quantity + $newQuantity,
                            'available_quantity' => $medicine->available_quantity + $newQuantity,
                        ];
                        $medicine->update($medicineQtyArray);

                        app(MedicineInventoryService::class)->recordStockIn([
                            'medicine_id' => $medicine->id,
                            'quantity' => (int) $newQuantity,
                            'dosage' => $input['dosage'][$key] ?? null,
                            'batch_number' => ($input['batch_number'][$key] ?? null)
                                ?: ($medicineAvailability->availability_no . '-' . $medicine->id . '-' . ($key + 1)),
                            'manufacturing_date' => $input['manufacturing_date'][$key] ?? null,
                            'expiration_date' => $input['expiry_date'][$key] ?? null,
                            'supplier_name' => $input['supplier_name'] ?? null,
                            'date_received' => now()->toDateString(),
                            'opening_balance_before' => $previousAvailable,
                            'user_id' => getLogInUserId(),
                            'reference' => $medicineAvailability,
                            'remarks' => 'New batch from updated procurement',
                        ]);

                        // Log medicine procurement
                        self::logMedicineProcurement(
                            $medicine,
                            $newQuantity,
                            [
                                'batch_no' => $input['manufacturing_date'][$key],
                                'expiry_date' => $input['expiry_date'][$key],
                            ]
                        );
                    }
                }
            }

            // Remove deleted medicines (subtract their quantities)
            foreach ($existingPurchasedMedicines as $deletedMedicine) {
                $medicine = Medicine::find($deletedMedicine->medicine_id);
                if ($medicine) {
                    $medicineQtyArray = [
                        'quantity' => max(0, $medicine->quantity - $deletedMedicine->quantity),
                        'available_quantity' => max(0, $medicine->available_quantity - $deletedMedicine->quantity),
                    ];
                    $medicine->update($medicineQtyArray);

                    app(MedicineInventoryService::class)->deductStockFefo(
                        $medicine->id,
                        (int) $deletedMedicine->quantity,
                        getLogInUserId(),
                        $medicineAvailability,
                        'Deleted batch adjustment from procurement update',
                        \App\Models\MedicineTransaction::TYPE_ADJUSTMENT
                    );
                }
                $deletedMedicine->delete();
            }

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
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
