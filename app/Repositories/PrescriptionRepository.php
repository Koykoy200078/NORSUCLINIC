<?php

namespace App\Repositories;

use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

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
        'doctor_id',
        'doctor_license_s2_number',
        'consultation_date',
        'icd10_diagnosis_id',
        'problem_description',
        'advice',
        'next_visit_days',
        'weight_kg',
        'pulse_rate',
        'body_temperature',
        'blood_pressure',
        'height_cm',
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
        return Cache::remember('active_patients_prescription', 600, function () {
            return Patient::with('user:id,first_name,last_name,status')
                ->whereHas('user', function (Builder $query) {
                    $query->where('status', 1);
                })->get()->pluck('user.full_name', 'id')->sort();
        });
    }

    public function getMedicines(): array
    {
        $data['medicines'] = Medicine::where('available_quantity', '>', 0)->pluck('name', 'id')->toArray();

        return $data;
    }

    public function getData($id): array
    {
        $data['prescription'] = Prescription::with([
            'patient.user',
            'doctor.user',
            'doctor.address',
            'diagnosis',
            'getMedicine.medicines.medicineCategory',
            'getMedicine.medicines.generic',
        ])->findOrFail($id);

        return $data;
    }

    public function getMedicineData($id): array
    {
        // Get prescription with all medicine data in one query using eager loading
        $prescription = Prescription::with([
            'getMedicine' => function ($query) {
                $query->with(['medicines' => function ($medicineQuery) {
                    $medicineQuery->select('id', 'name', 'brand_name', 'generic_name', 'category_name', 'dosage', 'uom', 'category_id', 'generic_id', 'salt_composition', 'description', 'side_effects');
                }]);
            }
        ])->findOrFail($id);

        // Return the prescription with loaded medicine data
        // No need to transform - keep the original structure that the view expects
        return [$prescription];
    }

    public function getSettingList(): array
    {
        // Single source of truth for settings (key 'application_settings') so a settings
        // change is reflected everywhere immediately instead of this repo serving a stale
        // 'clinic_settings' copy for up to an hour. CONFIG-3.
        return \App\Services\SettingsService::get();
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
