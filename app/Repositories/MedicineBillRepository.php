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

    public function update($dispenseRecord, $input): bool
    {
        try {
            DB::beginTransaction();

            $arr = collect($input['medicine']);
            $duplicateIds = $arr->duplicates();
            if ($duplicateIds->isNotEmpty()) {
                throw new UnprocessableEntityHttpException(__('messages.medicine_bills.duplicate_medicine'));
            }

            $dispenseRecord->update([
                'patient_id'     => $input['patient_id'],
                'note'           => $input['note'] ?? null,
                'bill_date'      => $input['bill_date'],
                'net_amount'     => 0,
                'discount'       => 0,
                'payment_status' => 1,
                'payment_type'   => 0,
                'total'          => 0,
                'tax_amount'     => 0,
            ]);

            $dispenseRecord->dispenseItems()->delete();

            if (! empty($input['category_id'])) {
                foreach ($input['category_id'] as $key => $value) {
                    $medicine = Medicine::find($input['medicine'][$key]);
                    if (! $medicine) {
                        continue;
                    }
                    $unitPrice = (float) ($input['sale_price'][$key] ?? 0);
                    $quantity = (int) ($input['quantity'][$key] ?? 0);
                    DispenseRecordItem::create([
                        'dispense_id'   => $dispenseRecord->id,
                        'medicine_id'   => $medicine->id,
                        'unit_price'    => $unitPrice,
                        'expires_at'    => $input['expiry_date'][$key] ?? null,
                        'quantity'      => $quantity,
                        'charge_amount' => 0,
                        'line_total'    => $unitPrice * $quantity,
                    ]);

                    app(MedicineInventoryService::class)->deductStockFefo(
                        $medicine->id,
                        $quantity,
                        getLogInUserId(),
                        $dispenseRecord,
                        'Manual dispensing update'
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
