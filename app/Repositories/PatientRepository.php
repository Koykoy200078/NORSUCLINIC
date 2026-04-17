<?php

namespace App\Repositories;

use App\Mail\PatientRegistrationMail;
use App\Models\Campus;
use App\Models\City;
use App\Models\College;
use App\Models\Country;
use App\Models\Course;
use App\Models\Diagnose;
use App\Models\InsuranceProvider;
use App\Models\Patient;
use App\Models\PatientType;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Illuminate\Support\Facades\Session;
use App\Models\Setting;
use App\Models\State;
use App\Models\Vaccination;
use App\Models\YearLevel;

/**
 * Class PatientRepository
 *
 * @version July 29, 2021, 11:37 am UTC
 */
class PatientRepository extends BaseRepository
{
    use LogsActivity;
    /**
     * @var array
     */
    protected $fieldSearchable = [];

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
        return Patient::class;
    }

    public function getData(): array
    {
        $data['countries'] = Country::toBase()->pluck('name', 'id');
        $data['bloodGroupList'] = Patient::BLOOD_TYPE_ARRAY;
        $data['provinces'] = State::toBase()->pluck('name', 'id');
        $data['cities'] = [];  // Loaded dynamically via AJAX based on province selection
        $data['barangays'] = [];  // Loaded dynamically via AJAX based on city selection

        $data['campuses'] = Campus::toBase()->pluck('campus_name', 'id');
        $data['colleges'] = College::toBase()->pluck('college_name', 'id');
        $data['courses'] = Course::toBase()->pluck('course_name', 'id');
        $data['year_levels'] = YearLevel::toBase()->pluck('year_level_name', 'id');
        $data['offices'] = \App\Models\Office::toBase()->pluck('office_name', 'id');
        $data['departments'] = \App\Models\Department::toBase()->pluck('department_name', 'id');

        $data['vaccination_data'] = Vaccination::toBase()->pluck('vaccination_status', 'id');
        $data['comorbidities'] = Diagnose::toBase()->pluck('diagnoses', 'id');
        $patientTypeSortOrder = [
            'student' => 1,
            'staff' => 2,
            'faculty' => 3,
            'guest' => 4,
            'dependent' => 4,
        ];

        $data['patient_types'] = PatientType::query()
            ->get(['id', 'code', 'name'])
            ->sortBy(function ($patientType) use ($patientTypeSortOrder) {
                $normalizedCode = strtolower(trim((string) $patientType->code));

                return $patientTypeSortOrder[$normalizedCode] ?? 99;
            })
            ->mapWithKeys(function ($patientType) {
                $normalizedCode = strtolower(trim((string) $patientType->code));
                $normalizedName = strtolower(trim((string) $patientType->name));
                $displayName = ($normalizedCode === 'dependent' || $normalizedName === 'dependent') ? 'Guest' : $patientType->name;

                return [$patientType->id => $displayName];
            });
        $data['insurance_providers'] = InsuranceProvider::toBase()->pluck('name', 'id');

        return $data;
    }

    private function normalizeComorbiditiesInput($value): ?string
    {
        if (is_array($value)) {
            $items = array_values(array_unique(array_filter(array_map(function ($item) {
                return trim((string) $item);
            }, $value))));

            return empty($items) ? null : implode(', ', $items);
        }

        if (is_string($value)) {
            $value = trim($value);

            return $value === '' ? null : $value;
        }

        return null;
    }

    public function store($input): bool
    {

        try {
            DB::beginTransaction();
            $addressInputArray = Arr::only(
                $input,
                ['address1', 'address2', 'city_id', 'barangay_id', 'state_id', 'country_id', 'postal_code']
            );

            $input['patient_unique_id'] = Str::upper((string) ($input['university_id_number'] ?? ''));
            $input['email'] = !empty($input['email']) ? setEmailLowerCase($input['email']) : null;
            $input['comorbidities'] = $this->normalizeComorbiditiesInput($input['comorbidities'] ?? null);
            $patientArray = Arr::only($input, [
                'patient_unique_id',
                'patient_type_id',
                'allergies',
                'comorbidities',
                'admissions_surgeries',
                'maintenance',
                'covid_vaccination',
                'immunization_record',
                'insurance_provider_id',
                'insurance_policy_number',
                'primary_care_physician_name',
                'primary_care_physician_contact',
                'primary_care_physician_email',
            ]);
            $input['type'] = User::PATIENT;
            $input['language'] = 'en';

            // Set email as verified with Philippine time
            $input['email_verified_at'] = now()->setTimezone('Asia/Manila')->toDateTimeString();

            // Remove non-database fields before creating user
            $userInput = Arr::except($input, [
                'address1',
                'address2',
                'city_id',
                'state_id',
                'country_id',
                'postal_code',
                'patient_unique_id',
                'profile',
                'patient_type_id',
                'allergies',
                'comorbidities',
                'admissions_surgeries',
                'maintenance',
                'covid_vaccination',
                'immunization_record',
                'insurance_provider_id',
                'insurance_policy_number',
                'primary_care_physician_name',
                'primary_care_physician_contact',
                'primary_care_physician_email',
                'is_employee',
                'is_guest',
                'position_type',
                'all_year_levels',
                'patient_type_lookup',
            ]);

            $userInput['password'] = Hash::make(!empty($input['password']) ? $input['password'] : '123456');
            $user = User::create($userInput);

            $patient = $user->patient()->create($patientArray);
            $address = $patient->address()->create($addressInputArray);
            $user->assignRole('patient');
            if (isset($input['profile']) && ! empty($input['profile'])) {
                $patient->addMedia($input['profile'])->toMediaCollection(Patient::PROFILE, config('app.media_disc'));
            }

            // Send welcome email to the newly registered patient
            try {
                Mail::to($user->email)->send(new PatientRegistrationMail($user, $patient));
            } catch (\Exception $mailException) {
                // Log the email error but don't fail the registration
                Log::warning('Failed to send registration email to patient: ' . $user->email, [
                    'error' => $mailException->getMessage(),
                    'patient_id' => $user->university_id_number ?? $patient->id,
                ]);
            }

            // $user->sendEmailVerificationNotification();

            // Log patient creation activity
            self::logPatientCreation($patient, $user);

            DB::commit();

            // Invalidate dashboard caches so today's registered count reflects immediately
            $todayKey = now()->format('Y-m-d');
            Cache::forget('livewire_admin_dashboard_' . $todayKey);
            Cache::forget('livewire_staff_dashboard_' . $todayKey);
            Cache::forget('admin_dashboard_data_' . $todayKey);
            Cache::forget('staff_dashboard_data_' . $todayKey);
            Cache::forget('admin_dashboard_today_patients_' . $todayKey);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function update($input, $patient): bool
    {
        try {
            DB::beginTransaction();

            $addressInputArray = Arr::only(
                $input,
                ['address1', 'address2', 'city_id', 'barangay_id', 'state_id', 'country_id', 'postal_code']
            );
            $input['type'] = User::PATIENT;
            $input['email'] = ! empty($input['email']) ? setEmailLowerCase($input['email']) : null;
            $input['patient_unique_id'] = Str::upper((string) ($input['university_id_number'] ?? ($patient->user->university_id_number ?? '')));
            $input['comorbidities'] = $this->normalizeComorbiditiesInput($input['comorbidities'] ?? null);
            $patientInput = Arr::only($input, [
                'patient_unique_id',
                'patient_type_id',
                'allergies',
                'comorbidities',
                'admissions_surgeries',
                'maintenance',
                'covid_vaccination',
                'immunization_record',
                'insurance_provider_id',
                'insurance_policy_number',
                'primary_care_physician_name',
                'primary_care_physician_contact',
                'primary_care_physician_email',
            ]);
            /** @var Patient $patient */
            $patient->user()->update(Arr::except($input, [
                'address1',
                'address2',
                'city_id',
                'barangay_id',
                'state_id',
                'country_id',
                'postal_code',
                'patient_unique_id',
                'avatar_remove',
                'profile',
                'patient_type_id',
                'allergies',
                'comorbidities',
                'admissions_surgeries',
                'maintenance',
                'covid_vaccination',
                'immunization_record',
                'insurance_provider_id',
                'insurance_policy_number',
                'primary_care_physician_name',
                'primary_care_physician_contact',
                'primary_care_physician_email',
                'is_edit',
                'edit_patient_country_id',
                'edit_patient_state_id',
                'edit_patient_city_id',
                'edit_patient_barangay_id',
                'backgroundImg',
                // Form-only fields that don't exist in users table
                'is_employee',
                'is_guest',
                'position_type',
                'all_year_levels',
                'patient_type_lookup',
            ]));

            $patient->update($patientInput);

            if ($patient->address()->exists()) {
                $patient->address()->update($addressInputArray);
            } else {
                $patient->address()->create($addressInputArray);
            }

            if (isset($input['profile']) && ! empty($input['profile'])) {
                $patient->clearMediaCollection(Patient::PROFILE);
                $patient->addMedia($input['profile'])->toMediaCollection(Patient::PROFILE, config('app.media_disc'));
            }

            // Log patient update activity
            self::logPatientUpdate($patient, $patient->user);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PatientRepository::update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'patient_id' => $patient->id ?? 'unknown',
                'input' => array_diff_key($input, array_flip(['profile', 'password'])),
            ]);
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function getPatientData($input)
    {
        $patient = Patient::with(['user.address', 'address'])->findOrFail($input['id']);

        return $patient;
    }
}
