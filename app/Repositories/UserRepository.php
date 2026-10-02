<?php

namespace App\Repositories;

use App\Models\Campus;
use App\Models\City;
use App\Models\College;
use App\Models\Country;
use App\Models\Course;
use App\Models\Department;
use App\Models\Diagnose;
use App\Models\Doctor;
use App\Models\Office;
use App\Models\Patient;
use App\Models\PatientType;
use App\Models\Qualification;
use App\Models\Specialization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Illuminate\Support\Facades\Session;
use App\Models\Setting;
use App\Models\State;
use App\Models\Vaccination;
use App\Models\YearLevel;

/**
 * Class UserRepository
 */
class UserRepository extends BaseRepository
{
    public $fieldSearchable = [
        'first_name',
        'last_name',
        'email',
        'contact',
        'dob',
        'specialization',
        'experience',
        'gender',
        'status',
        'password',

    ];

    /**
     * {@inheritDoc}
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * {@inheritDoc}
     */
    public function model()
    {
        return User::class;
    }

    public function getData(): array
    {
        $data['countries'] = Country::toBase()->pluck('name', 'id');
        $data['bloodGroupList'] = Patient::BLOOD_TYPE_ARRAY;

        $data['provinces'] = State::toBase()->pluck('name', 'id');
        $data['cities'] = City::toBase()->pluck('name', 'id');
        $data['barangays'] = [];  // Loaded dynamically via AJAX based on city selection
        $data['campuses'] = Campus::toBase()->pluck('campus_name', 'id');
        $data['colleges'] = College::toBase()->pluck('college_name', 'id');
        $data['courses'] = Course::toBase()->pluck('course_name', 'id');
        $data['year_levels'] = YearLevel::toBase()->pluck('year_level_name', 'id');
        $data['departments'] = Department::toBase()->pluck('department_name', 'id');
        $data['offices'] = Office::toBase()->pluck('office_name', 'id');

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

        $data['vaccination_data'] = Vaccination::toBase()->pluck('vaccination_status', 'id');
        $data['comorbidities'] = Diagnose::toBase()->pluck('diagnoses', 'id');

        return $data;
    }

    /**
     * @return mixed
     */
    public function store(array $input)
    {
        $addressInputArray = Arr::only(
            $input,
            ['address1', 'address2', 'country_id', 'city_id', 'barangay_id', 'state_id', 'postal_code']
        );
        $doctorArray = Arr::only($input, [
            'experience',
            'prc_license_number',
            'ptr_number',
            's2_license_number',
            'consultation_hours',
        ]);
        $specialization = $input['specializations'];
        try {
            DB::beginTransaction();
            $input['email'] = setEmailLowerCase($input['email']);
            $input['status'] = (isset($input['status'])) ? 1 : 0;
            $input['password'] = Hash::make($input['password']);
            $input['type'] = User::DOCTOR;
            $input['language'] = 'en';
            // Set email as verified with Philippine time
            $input['email_verified_at'] = now()->setTimezone('Asia/Manila')->toDateTimeString();
            $doctor = User::create($input);
            $doctor->assignRole('doctor');
            $doctor->address()->create($addressInputArray);
            $createDoctor = $doctor->doctor()->create($doctorArray);
            $createDoctor->specializations()->sync($specialization);
            if (! empty($input['profile'])) {
                $doctor->addMedia($input['profile'])->toMediaCollection(User::PROFILE, config('app.media_disc'));
            }
            // $doctor->sendEmailVerificationNotification();

            DB::commit();

            return $doctor;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function update($input, $doctor)
    {
        $addressInputArray = Arr::only(
            $input,
            ['address1', 'address2', 'city_id', 'barangay_id', 'state_id', 'country_id', 'postal_code']
        );
        $doctorArray = Arr::only($input, [
            'experience',
            'prc_license_number',
            'ptr_number',
            's2_license_number',
            'consultation_hours',
        ]);
        $qualificationArray = json_decode($input['qualifications'] ?? '[]', true) ?? [];
        $specialization = $input['specializations'];
        try {
            DB::beginTransaction();
            $input['email'] = setEmailLowerCase($input['email']);
            $input['status'] = (isset($input['status'])) ? 1 : 0;
            $input['type'] = User::DOCTOR;
            // Only the columns the doctor form has. The whole request used to be mass-assigned, so a crafted request
            // could also set password, email_verified_at, dark_mode ... R3-L1.
            $doctor->user->update(Arr::only($input, array_merge(User::RECORD_FIELDS, ['status', 'type'])));
            $doctor->user->address()->updateOrCreate([], $addressInputArray);
            $doctor->update($doctorArray);
            $doctor->specializations()->sync($specialization);

            if (count($qualificationArray) >= 0) {
                if (isset($input['deletedQualifications']) && !empty($input['deletedQualifications'])) {
                    // Only this doctor's own qualifications (any id used to be deletable). R3-L1.
                    $doctor->user->qualifications()
                        ->whereIn('id', array_filter(array_map('intval', explode(',', (string) $input['deletedQualifications']))))
                        ->delete();
                }

                foreach ($qualificationArray as $qualifications) {
                    if ($qualifications == null) {
                        continue;
                    }
                    if (isset($qualifications['id'])) {
                        $doctor->user->qualifications()->where('id', $qualifications['id'])->update(Arr::only($qualifications, ['degree', 'university', 'year']));
                    } else {
                        unset($qualifications['id']);
                        $doctor->user->qualifications()->create(Arr::only($qualifications, ['degree', 'university', 'year']));
                    }
                }
            }

            if (! empty($input['profile'])) {
                // clearMediaCollection removes the files AND rows of this collection only.
                $doctor->user->clearMediaCollection(User::PROFILE);
                $doctor->user->addMedia($input['profile'])->toMediaCollection(User::PROFILE, config('app.media_disc'));
            }
            DB::commit();

            return $doctor;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function updateProfile(array $userInput): bool
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();

            $addressInputArray = Arr::only(
                $userInput,
                ['address1', 'address2', 'city_id', 'barangay_id', 'state_id', 'country_id', 'postal_code']
            );

            // Staff / nurse accounts had no branch here, so their profile form saved nothing while the
            // controller still reported success. R3-M2.
            if ($user->hasRole('clinic_admin') || $user->hasRole('staff') || $user->hasRole('nurse')) {
                $user->fill(Arr::only($userInput, User::SELF_PROFILE_FIELDS))->save();

                if ((! empty($userInput['image']))) {
                    $user->clearMediaCollection(User::PROFILE);
                    $user->addMedia($userInput['image'])->toMediaCollection(User::PROFILE, config('app.media_disc'));
                }

                if (isset($user->address)) {
                    $user->address()->update($addressInputArray);
                } else {
                    $user->address()->create($addressInputArray);
                }
            } elseif ($user->hasRole('patient')) {
                $patient =  Patient::where('user_id', $user->id)->first();

                $selectedPatientTypeId = $userInput['patient_type_id'] ?? $patient->patient_type_id;
                $selectedPatientType = $selectedPatientTypeId ? PatientType::find($selectedPatientTypeId) : null;
                $selectedPatientTypeCode = strtolower(trim((string) ($selectedPatientType->code ?? $selectedPatientType->name ?? '')));
                if ($selectedPatientTypeCode === 'dependent') {
                    $selectedPatientTypeCode = 'guest';
                }

                if ($selectedPatientTypeCode === 'faculty') {
                    $userInput['campus_id'] = null;
                    $userInput['course_id'] = null;
                    $userInput['office_id'] = null;
                    $userInput['year_level_id'] = YearLevel::query()->where('year_level_name', 'LIKE', '%Faculty%')->value('id');
                } elseif ($selectedPatientTypeCode === 'staff') {
                    $userInput['campus_id'] = null;
                    $userInput['college_id'] = null;
                    $userInput['course_id'] = null;
                    $userInput['department_id'] = null;
                    $userInput['year_level_id'] = YearLevel::query()->where('year_level_name', 'LIKE', '%Staff%')->value('id');
                } elseif ($selectedPatientTypeCode === 'guest') {
                    $userInput['campus_id'] = null;
                    $userInput['college_id'] = null;
                    $userInput['course_id'] = null;
                    $userInput['department_id'] = null;
                    $userInput['office_id'] = null;
                    $userInput['year_level_id'] = YearLevel::query()->where('year_level_name', 'LIKE', '%Guest%')->value('id');
                } else {
                    $userInput['department_id'] = null;
                    $userInput['office_id'] = null;
                }


                $userInput['type'] = User::PATIENT;
                $userInput['email'] = setEmailLowerCase($userInput['email']);

                // Allow-list + save(): see H-14 (the query-builder update bypassed $fillable / casts).
                $user->fill(Arr::only($userInput, User::SELF_PROFILE_FIELDS))->save();

                $patient->update([
                    'patient_type_id' => $selectedPatientTypeId,
                ]);

                if (isset($patient->address)) {
                    $patient->address()->update($addressInputArray);
                } else {
                    $patient->address()->create($addressInputArray);
                }

                if (! empty($userInput['image'])) {
                    $user->patient->clearMediaCollection(Patient::PROFILE);
                    $user->patient->addMedia($userInput['image'])->toMediaCollection(
                        Patient::PROFILE,
                        config('app.media_disc')
                    );
                }
            } elseif ($user->hasRole('doctor')) {
                $doctor =  Doctor::where('user_id', $user->id)->first();
                $userInput['type'] = User::DOCTOR;
                $userInput['email'] = setEmailLowerCase($userInput['email']);

                $user->fill(Arr::only($userInput, User::SELF_PROFILE_FIELDS))->save();

                if (isset($doctor->address)) {
                    $doctor->address()->update($addressInputArray);
                } else {
                    $doctor->address()->create($addressInputArray);
                }

                if (! empty($userInput['image'])) {
                    $user->clearMediaCollection(User::PROFILE);
                    $user->addMedia($userInput['image'])->toMediaCollection(
                        User::PROFILE,
                        config('app.media_disc')
                    );
                }
            } else {
                // Never report success for a save that did not happen.
                throw new \RuntimeException('This account has no editable profile.');
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function getSpecializationsData($doctor)
    {
        $data['specializations'] = Specialization::pluck('name', 'id')->toArray();
        $data['doctorSpecializations'] = $doctor->specializations()->pluck('specialization_id')->toArray();
        $data['countryId'] = $doctor->user->address()->pluck('country_id');
        $data['stateId'] = $doctor->user->address()->pluck('state_id');

        return $data;
    }

    /**
     * @return mixed
     */
    public function getCountries()
    {
        $countries = Country::pluck('name', 'id');

        return $countries;
    }

    public function addQualification($input)
    {
        $input['user_id'] = $input['id'];
        $qualification = Qualification::create($input);

        return $qualification;
    }

    /**
     * @throws \Exception
     */
    public function doctorDetail($input): array
    {
        $doctor['data'] = Doctor::with(['user.address', 'specializations'])->whereId($input->id)->first();

        return $doctor;
    }
}
