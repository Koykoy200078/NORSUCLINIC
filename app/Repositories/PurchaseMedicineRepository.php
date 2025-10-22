<?php

namespace App\Repositories;

use App\Models\Accountant;
use App\Models\Address;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\PurchasedMedicine;
use App\Models\PurchaseMedicine;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Support\Arr;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class AccountantRepository
 *
 * @version February 17, 2020, 5:34 am UTC
 */
class PurchaseMedicineRepository extends BaseRepository
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
        return PurchaseMedicine::class;
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
            $purchaseMedicine = PurchaseMedicine::create($purchaseMedicineArray);

            foreach ($input['medicine'] as $key => $value) {
                $purchasedMedicineArray = [
                    'purchase_medicines_id' => $purchaseMedicine->id,
                    'medicine_id' => $input['medicine'][$key],
                    'dosage' => $input['dosage'][$key] ?? null,
                    'manufacturing_date' => $input['manufacturing_date'][$key],
                    'tax' => $input['tax_medicine'][$key] ?? 0,
                    'expiry_date' => $input['expiry_date'][$key],
                    'quantity' => $input['quantity'][$key],
                    'amount' => $input['amount'][$key],
                    'tenant_id',
                ];

                PurchasedMedicine::create($purchasedMedicineArray);
                $medicine = Medicine::find($input['medicine'][$key]);
                $medicineQtyArray = [
                    'quantity' => $input['quantity'][$key] + $medicine->quantity,
                    'available_quantity' => $input['quantity'][$key] + $medicine->available_quantity,
                ];
                $medicine->update($medicineQtyArray);

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

            $purchaseMedicine = PurchaseMedicine::findOrFail($id);
            $purchaseMedicineArray = Arr::only($input, $purchaseMedicine->getFillable());
            $purchaseMedicine->update($purchaseMedicineArray);

            // Get existing purchased medicines
            $existingPurchasedMedicines = PurchasedMedicine::where('purchase_medicines_id', $id)->get()->keyBy('id');

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
                        'tax' => $input['tax_medicine'][$key] ?? 0,
                        'expiry_date' => $input['expiry_date'][$key],
                        'quantity' => $newQuantity,
                        'amount' => $input['amount'][$key],
                    ];

                    $existingPurchasedMedicine->update($purchasedMedicineArray);

                    // Update medicine quantities
                    $medicine = Medicine::find($medicineId);
                    if ($medicine) {
                        $medicineQtyArray = [
                            'quantity' => $medicine->quantity + $quantityDifference,
                            'available_quantity' => $medicine->available_quantity + $quantityDifference,
                        ];
                        $medicine->update($medicineQtyArray);

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
                        'purchase_medicines_id' => $purchaseMedicine->id,
                        'medicine_id' => $medicineId,
                        'dosage' => $input['dosage'][$key] ?? null,
                        'manufacturing_date' => $input['manufacturing_date'][$key],
                        'tax' => $input['tax_medicine'][$key] ?? 0,
                        'expiry_date' => $input['expiry_date'][$key],
                        'quantity' => $newQuantity,
                        'amount' => $input['amount'][$key],
                    ];

                    PurchasedMedicine::create($purchasedMedicineArray);

                    // Add to medicine quantity
                    $medicine = Medicine::find($medicineId);
                    if ($medicine) {
                        $medicineQtyArray = [
                            'quantity' => $medicine->quantity + $newQuantity,
                            'available_quantity' => $medicine->available_quantity + $newQuantity,
                        ];
                        $medicine->update($medicineQtyArray);

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
        self::logActivity(
            Medicine::class,
            $medicine->id,
            [
                'action' => 'medicine_quantity_updated',
                'medicine_name' => $medicine->name,
                'quantity_change' => $quantityChange,
                'new_quantity' => $medicine->quantity,
                'new_available_quantity' => $medicine->available_quantity,
                'details' => $details,
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
