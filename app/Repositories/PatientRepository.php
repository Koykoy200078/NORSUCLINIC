<?php

namespace App\Repositories;

use App\Mail\PatientRegistrationMail;
use App\Models\Campus;
use App\Models\City;
use App\Models\College;
use App\Models\Country;
use App\Models\Course;
use App\Models\Diagnose;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Arr;
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
        $data['patientUniqueId'] = mb_strtoupper(Patient::generatePatientUniqueId());
        $data['countries'] = Country::toBase()->pluck('name', 'id');
        $data['bloodGroupList'] = Patient::BLOOD_TYPE_ARRAY;
        $data['provinces'] = State::toBase()->pluck('name', 'id');
        $data['cities'] = City::toBase()->pluck('name', 'id');

        $data['campuses'] = Campus::toBase()->pluck('campus_name', 'id');
        $data['colleges'] = College::toBase()->pluck('college_name', 'id');
        $data['courses'] = Course::toBase()->pluck('course_name', 'id');
        $data['year_levels'] = YearLevel::toBase()->pluck('year_level_name', 'id');
        $data['offices'] = \App\Models\Office::toBase()->pluck('office_name', 'id');
        $data['departments'] = \App\Models\Department::toBase()->pluck('department_name', 'id');

        $data['vaccination_data'] = Vaccination::toBase()->pluck('vaccination_status', 'id');
        $data['comorbidities'] = Diagnose::toBase()->pluck('diagnoses', 'id');

        return $data;
    }

    public function store($input): bool
    {

        try {
            DB::beginTransaction();
            $addressInputArray = Arr::only(
                $input,
                ['address1', 'address2', 'city_id', 'state_id', 'country_id', 'postal_code']
            );

            $input['patient_unique_id'] = Str::upper($input['patient_unique_id']);
            $input['email'] = !empty($input['email']) ? setEmailLowerCase($input['email']) : null;
            $patientArray = Arr::only($input, ['patient_unique_id']);
            $input['type'] = User::PATIENT;
            $languageSetting = Setting::where('key', 'language')->first();
            $input['language'] = $languageSetting ? $languageSetting->value : 'en';

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
                'is_employee',
                'is_guest',
                'position_type',
                'all_year_levels',
            ]);

            // $input['password'] = Hash::make($input['password']);
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
                    'patient_id' => $patient->patient_unique_id
                ]);
            }

            // $user->sendEmailVerificationNotification();

            DB::commit();

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
                ['address1', 'address2', 'city_id', 'state_id', 'country_id', 'postal_code']
            );
            $input['type'] = User::PATIENT;
            $input['email'] = setEmailLowerCase($input['email']);
            /** @var Patient $patient */
            $patient->user()->update(Arr::except($input, [
                'address1',
                'address2',
                'city_id',
                'state_id',
                'country_id',
                'postal_code',
                'patient_unique_id',
                'avatar_remove',
                'profile',
                'is_edit',
                'edit_patient_country_id',
                'edit_patient_state_id',
                'edit_patient_city_id',
                'backgroundImg',
                // Employee-related fields that don't exist in users table
                'is_employee',
                'campus_id',
                'college_id',
                'course_id',
                'year_level_id',
                'all_year_levels',
            ]));

            if ($patient->address()->exists()) {
                $patient->address()->update($addressInputArray);
            } else {
                $patient->address()->create($addressInputArray);
            }

            if (isset($input['profile']) && ! empty($input['profile'])) {
                $patient->clearMediaCollection(Patient::PROFILE);
                $patient->addMedia($input['profile'])->toMediaCollection(Patient::PROFILE, config('app.media_disc'));
            }

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
        $patient = Patient::with(['user.address', 'appointments', 'address'])->findOrFail($input['id']);

        return $patient;
    }
}
