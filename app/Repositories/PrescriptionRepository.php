<?php

namespace App\Repositories;

use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;

/**
 * Class PrescriptionRepository
 *
 * @version March 31, 2020, 12:22 pm UTC
 */
class PrescriptionRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'patient_id',
        'food_allergies',
        'tendency_bleed',
        'heart_disease',
        'high_blood_pressure',
        'diabetic',
        'surgery',
        'accident',
        'others',
        'medical_history',
        'current_medication',
        'female_pregnancy',
        'breast_feeding',
        'health_insurance',
        'low_income',
        'reference',
        'status',
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
        return Prescription::class;
    }

    public function getPatients(): \Illuminate\Support\Collection
    {
        $user = Auth::user();
        if ($user && $user->hasRole('Doctor')) {
            $patients = getPatientsList($user->owner_id);
        } else {
            $patients = Cache::remember('active_patients_prescription', 600, function () {
                return Patient::with('user:id,first_name,last_name,status')
                    ->whereHas('user', function (Builder $query) {
                        $query->where('status', 1);
                    })->get()->pluck('user.full_name', 'id')->sort();
            });
        }

        return $patients;
    }

    /**
     * @param  array  $prescription
     * @return bool|Builder|Builder[]|Collection|Model
     */
    //    public function update($prescription, $input)
    //    {
    //        try {
    //            /** @var Prescription $prescription */
    //            $prescription->update($input);
    //
    //            return true;
    //        } catch (Exception $e) {
    //            throw new UnprocessableEntityHttpException($e->getMessage());
    //        }
    //    }

    public function createNotification(array $input)
    {
        try {
            $patient = Patient::with('user')->where('id', $input['patient_id'])->first();

            addNotification([
                Notification::NOTIFICATION_TYPE['Prescription'],
                $patient->user_id,
                Notification::NOTIFICATION_FOR[Notification::PATIENT],
                $patient->user->full_name . ' your prescription has been created.',
            ]);
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function getMedicines(): array
    {
        $data['medicines'] = Medicine::where('available_quantity', '>', 0)->pluck('name', 'id')->toArray();

        return $data;
    }

    public function getMedicinesQuantity()
    {
        $data = Medicine::where('available_quantity', '>', 0)->pluck('quantity')->toArray();
        return $data;
    }

    public function createPrescription(array $input, Model $prescription)
    {
        try {
            DB::beginTransaction();

            $amount = 0;
            $qty = 0;
            if (isset($input['medicine'])) {
                $medicineBill = DispenseRecord::create([
                    'history_number' => 'HIS' . generateUniqueHistoryNumber(),
                    'patient_id' => $input['patient_id'],
                    'doctor_id' => $input['doctor_id'],
                    'model_type' => \App\Models\Prescription::class,
                    'model_id' => $prescription->id,
                    'bill_date' => Carbon::now(),
                    'payment_status' => 1,
                ]);
                foreach ($input['medicine'] as $key => $value) {
                    $PrescriptionItem = [
                        'prescription_id' => $prescription->id,
                        'medicine' => $input['medicine'][$key],
                        'dosage' => $input['dosage'][$key],
                        'day' => $input['day'][$key],
                        'time' => $input['time'][$key],
                        'dose_interval' => $input['dose_interval'][$key],
                        'comment' => $input['comment'][$key],
                    ];
                    $prescriptionMedcine = PrescriptionMedicine::create($PrescriptionItem);
                    $medicine = Medicine::find($input['medicine'][$key]);
                    $amount += $input['day'][$key] * $input['dose_interval'][$key] * $medicine->selling_price;
                    $qty = $input['day'][$key] * $input['dose_interval'][$key];
                    $saleMedicineArray = [
                        'medicine_bill_id' => $medicineBill->id,
                        'medicine_id' => $medicine->id,
                        'sale_quantity' => $qty,
                        'sale_price' => $medicine->selling_price,
                        'tax' => 0,

                    ];
                    DispenseRecordItem::create($saleMedicineArray);
                }
                $medicineBill->update([
                    'net_amount' => $amount,
                    'total' => $amount,
                ]);
            }
            DB::commit();
        } catch (Exception $e) {

            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function prescriptionUpdate($prescription, $input)
    {
        try {
            DB::beginTransaction();
            $prescriptionMedicineArr = Arr::only($input, $this->model->getFillable());
            $prescription->update($prescriptionMedicineArr);
            $medicineBill = DispenseRecord::with('dispenseItems')->whereModelType(\App\Models\Prescription::class)->whereModelId($prescription->id)->first();
            $prescription->getMedicine()->delete();
            $medicineBill->dispenseItems()->delete();
            $amount = 0;
            $qty = 0;

            if (! empty($input['medicine'])) {
                foreach ($input['medicine'] as $key => $value) {
                    $PrescriptionItem = [
                        'prescription_id' => $prescription->id,
                        'medicine' => $input['medicine'][$key],
                        'dosage' => $input['dosage'][$key],
                        'day' => $input['day'][$key],
                        'time' => $input['time'][$key],
                        'dose_interval' => $input['dose_interval'][$key],
                        'comment' => $input['comment'][$key],
                    ];
                    $prescriptionMedcine = PrescriptionMedicine::create($PrescriptionItem);

                    $medicine = Medicine::find($input['medicine'][$key]);
                    $amount += $input['day'][$key] * $input['dose_interval'][$key] * $medicine->selling_price;
                    $qty = $input['day'][$key] * $input['dose_interval'][$key];
                    $saleMedicineArray = [
                        'medicine_bill_id' => $medicineBill->id,
                        'medicine_id' => $medicine->id,
                        'sale_quantity' => $qty,
                        'sale_price' => $medicine->selling_price,
                        'tax' => 0,

                    ];
                    DispenseRecordItem::create($saleMedicineArray);
                }
                $medicineBill->update([
                    'net_amount' => $amount,
                    //  'discount'=>$input['discount'],
                    //  'tax_amount'=>$input['tax'],
                ]);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return $prescription;
    }

    public function getData($id): array
    {
        $data['prescription'] = Prescription::with([
            'patient.user',
            'doctor.user',
            'doctor.address',
            'getMedicine.medicines.category',
            'getMedicine.medicines.brand',
        ])->findOrFail($id);

        return $data;
    }

    public function getMedicineData($id): array
    {
        // Get prescription with all medicine data in one query using eager loading
        $prescription = Prescription::with([
            'getMedicine' => function ($query) {
                $query->with(['medicines' => function ($medicineQuery) {
                    $medicineQuery->select('id', 'name', 'category_id', 'generic_id', 'salt_composition', 'selling_price', 'description', 'side_effects');
                }]);
            }
        ])->findOrFail($id);

        // Return the prescription with loaded medicine data
        // No need to transform - keep the original structure that the view expects
        return [$prescription];
    }

    public function getSettingList(): array
    {
        // Cache settings for 1 hour since they rarely change
        $settings = cache()->remember('clinic_settings', 3600, function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        return $settings;
    }

    public function getDoctors()
    {
        /** @var Doctor $doctors */
        $doctors = Cache::remember('active_doctors_prescription', 600, function () {
            return Doctor::with('doctorUser:id,first_name,last_name,status')
                ->whereHas('doctorUser', function (Builder $query) {
                    $query->where('status', 1);
                })->get()->pluck('doctorUser.full_name', 'id')->sort();
        });

        return $doctors;
    }
}
