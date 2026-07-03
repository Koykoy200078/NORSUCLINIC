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
            'student' => User::STUDENT,
            'faculty' => User::FACULTY,
            'staff'   => User::EMPLOYEE,
            'guest'   => User::GUEST
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

    private function normalizeNullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function resolveCovidVaccinationStatus(array $input): ?string
    {
        $vaccinationId = $input['vaccination_id'] ?? null;

        if ($vaccinationId !== null && $vaccinationId !== '' && is_numeric($vaccinationId)) {
            $status = Vaccination::query()->whereKey((int) $vaccinationId)->value('vaccination_status');

            return $this->normalizeNullableString($status);
        }

        return $this->normalizeNullableString($input['covid_vaccination'] ?? null);
    }

    private function buildSyncedImmunizationRecord(?string $currentRecord, ?string $covidVaccination): ?string
    {
        $normalizedRecord = $this->normalizeNullableString($currentRecord);
        $normalizedVaccination = $this->normalizeNullableString($covidVaccination);

        if ($normalizedVaccination === null) {
            return $normalizedRecord;
        }

        if (in_array(strtolower($normalizedVaccination), ['unknown vaccination', 'unknown', 'n/a', 'na'], true)) {
            return $normalizedRecord;
        }

        $covidLine = 'COVID-19 Vaccination Status: ' . $normalizedVaccination;

        $existingLines = preg_split('/\R+/', (string) ($normalizedRecord ?? '')) ?: [];
        $existingLines = array_values(array_filter(array_map(function ($line) {
            return trim((string) $line);
        }, $existingLines), function ($line) {
            return $line !== '';
        }));

        $updatedLines = [];
        $hasCovidLine = false;

        foreach ($existingLines as $line) {
            if (preg_match('/^covid(?:-19)?\s+vaccination\s+status\s*:/i', $line)) {
                if (! $hasCovidLine) {
                    $updatedLines[] = $covidLine;
                    $hasCovidLine = true;
                }

                continue;
            }

            $updatedLines[] = $line;
        }

        if (! $hasCovidLine) {
            $updatedLines[] = $covidLine;
        }

        return implode(PHP_EOL, $updatedLines);
    }

    private function syncImmunizationFromCovid(array &$input, $patient = null): void
    {
        $patientModel = $patient instanceof Patient ? $patient : null;

        $resolvedCovidVaccination = $this->resolveCovidVaccinationStatus($input);

        if ($resolvedCovidVaccination !== null) {
            $input['covid_vaccination'] = $resolvedCovidVaccination;
        } elseif ($patientModel) {
            $resolvedCovidVaccination = $this->normalizeNullableString($patientModel->covid_vaccination);
        }

        $currentImmunizationRecord = $this->normalizeNullableString($input['immunization_record'] ?? null);
        if ($currentImmunizationRecord === null && $patientModel) {
            $currentImmunizationRecord = $this->normalizeNullableString($patientModel->immunization_record);
        }

        $syncedImmunizationRecord = $this->buildSyncedImmunizationRecord(
            $currentImmunizationRecord,
            $resolvedCovidVaccination
        );

        if ($syncedImmunizationRecord !== null) {
            $input['immunization_record'] = $syncedImmunizationRecord;
        }
    }

    public function store($input): bool
    {

        try {
            DB::beginTransaction();
            $addressInputArray = Arr::only(
                $input,
                ['address1', 'address2', 'city_id', 'barangay_id', 'state_id', 'country_id', 'postal_code']
            );

            $normalizedUniversityIdNumber = Str::upper(trim((string) ($input['university_id_number'] ?? '')));
            $input['university_id_number'] = $normalizedUniversityIdNumber !== '' ? $normalizedUniversityIdNumber : null;
            $input['patient_unique_id'] = $input['university_id_number'] ?: Patient::generatePatientUniqueId();
            $input['email'] = !empty($input['email']) ? setEmailLowerCase($input['email']) : null;
            $input['comorbidities'] = $this->normalizeComorbiditiesInput($input['comorbidities'] ?? null);
            $this->syncImmunizationFromCovid($input);
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
            $input['status'] = 1; // Force active; never accept `status` from raw request input. CRUD-MA.
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
            $normalizedUniversityIdNumber = Str::upper(trim((string) ($input['university_id_number'] ?? ($patient->user->university_id_number ?? ''))));
            $input['university_id_number'] = $normalizedUniversityIdNumber !== '' ? $normalizedUniversityIdNumber : null;

            $existingPatientUniqueId = Str::upper(trim((string) ($patient->patient_unique_id ?? '')));
            $input['patient_unique_id'] = $input['university_id_number']
                ?: ($existingPatientUniqueId !== '' ? $existingPatientUniqueId : Patient::generatePatientUniqueId());
            $input['comorbidities'] = $this->normalizeComorbiditiesInput($input['comorbidities'] ?? null);
            $this->syncImmunizationFromCovid($input, $patient);
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
                // Privilege / non-form user fields — never mass-assign from the patient edit
                // form (prevents a manage_patients user injecting status=0, type=1, etc.). CRUD-MA.
                'status',
                'type',
                'email_verified_at',
                'remember_token',
                'dark_mode',
                'email_notification',
            ]));

            $patient->update($patientInput);

            if ($patient->address()->exists()) {
                $patient->address()->update($addressInputArray);
            } else {
                $patient->address()->create($addressInputArray);
            }

            // Log patient update activity
            self::logPatientUpdate($patient, $patient->user);

            DB::commit();

            // Media filesystem ops AFTER commit — clearMediaCollection physically deletes the
            // old file immediately, so doing it inside the transaction means a rollback would
            // leave the DB referencing a file that is already gone. MEDIA.
            if (isset($input['profile']) && ! empty($input['profile'])) {
                $patient->clearMediaCollection(Patient::PROFILE);
                $patient->addMedia($input['profile'])->toMediaCollection(Patient::PROFILE, config('app.media_disc'));
            }

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
