<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\Patient;
// MedicineBill / SaleMedicine kept for backward compat — use DispenseRecord/DispenseRecordItem instead
use App\Models\Setting;
use App\Services\MedicineInventoryService;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Illuminate\Support\Facades\Cache;

/**
 * Class DoctorRepository
 *
 * @version February 13, 2020, 8:55 am UTC
 */
class MedicineBillRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'to',
        'subject',
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
        return DispenseRecord::class;
    }

    /**
     * Update a dispense record and recreate its item rows.
     *
     * @param  DispenseRecord  $dispenseRecord
     * @param  array<string, mixed>  $input
     */
    public function update($dispenseRecord, $input): bool
    {
        try {
            DB::beginTransaction();

            $lineIdentifiers = collect($input['medicine'])->map(function ($medicineId, $key) use ($input) {
                $dosage = trim((string) ($input['dosage'][$key] ?? ''));
                $expiry = trim((string) ($input['expiry_date'][$key] ?? ''));

                return $medicineId . '|' . $dosage . '|' . $expiry;
            });

            if ($lineIdentifiers->duplicates()->isNotEmpty()) {
                throw new UnprocessableEntityHttpException(__('messages.medicine_bills.duplicate_medicine'));
            }

            $dispenseRecord->update([
                'patient_id'     => $input['patient_id'],
                'note'           => $input['note'] ?? null,
                'bill_date'      => $input['bill_date'],
            ]);

            $dispenseRecord->dispenseItems()->delete();

            if (! empty($input['category_id'])) {
                foreach ($input['category_id'] as $key => $value) {
                    $medicine = Medicine::find($input['medicine'][$key]);
                    if (! $medicine) {
                        continue;
                    }

                    $quantity = (int) ($input['quantity'][$key] ?? 0);
                    $dosage = trim((string) ($input['dosage'][$key] ?? ''));

                    $allocations = app(MedicineInventoryService::class)->deductStockFefo(
                        $medicine->id,
                        $quantity,
                        getLogInUserId(),
                        $dispenseRecord,
                        'Manual dispensing update',
                        \App\Models\MedicineTransaction::TYPE_DISPENSE,
                        $dosage
                    );

                    $this->storeDispenseItemsFromAllocations(
                        $dispenseRecord->id,
                        $medicine->id,
                        $dosage,
                        $input['expiry_date'][$key] ?? null,
                        $quantity,
                        $allocations
                    );
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return true;
    }

    /**
     * Persist one or more dispense rows from FEFO allocation payload.
     */
    private function storeDispenseItemsFromAllocations(
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

    public function getPatients(): Collection
    {
        $patients = Cache::remember('active_patients_medicine_bill', 600, function () {
            return Patient::with('patientUser:id,first_name,last_name,status')
                ->whereHas('patientUser', function (Builder $query) {
                    $query->where('status', 1);
                })->get()->pluck('patientUser.full_name', 'id')->sort();
        });

        return $patients;
    }

    public function getMedicines()
    {
        $data['medicines'] = Cache::remember('medicines_list', 600, function () {
            return Medicine::pluck('name', 'id')->toArray();
        });

        return $data;
    }

    public function getSettingList(): array
    {
        $settings = Cache::remember('app_settings', 3600, function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        return $settings;
    }

    public function getDoctors(): Doctor
    {
        /** @var Doctor $doctors */
        $doctors = Cache::remember('active_doctors_medicine_bill', 600, function () {
            return Doctor::with('doctorUser:id,first_name,last_name,status')
                ->whereHas('doctorUser', function (Builder $query) {
                    $query->where('status', 1);
                })->get()->pluck('doctorUser.full_name', 'id')->sort();
        });

        return $doctors;
    }

    public function getMedicinesCategoriesData(): Collection
    {
        return Cache::remember('active_medicine_categories', 600, function () {
            return Category::where('is_active', '=', 1)->pluck('name', 'id');
        });
    }

    public function getMedicineCategoriesList(): array
    {
        $result = Category::where('is_active', '=', 1)->pluck('name', 'id')->toArray();

        $medicineCategories = [];
        foreach ($result as $key => $item) {
            $medicineCategories[] = [
                'key' => $key,
                'value' => $item,
            ];
        }

        return $medicineCategories;
    }
}
