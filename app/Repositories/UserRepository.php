<?php

namespace App\Repositories;

use App\DataTable\UserDataTable;
use App\Models\Appointment;
use App\Models\Campus;
use App\Models\City;
use App\Models\College;
use App\Models\Country;
use App\Models\Course;
use App\Models\Department;
use App\Models\Diagnose;
use App\Models\Doctor;
use App\Models\DoctorSession;
use App\Models\Office;
use App\Models\Patient;
use App\Models\Qualification;
use App\Models\Specialization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Yajra\DataTables\DataTables;
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
        $data['patientUniqueId'] = mb_strtoupper(Patient::generatePatientUniqueId());
        $data['countries'] = Country::toBase()->pluck('name', 'id');
        $data['bloodGroupList'] = Patient::BLOOD_TYPE_ARRAY;

        $data['provinces'] = State::toBase()->pluck('name', 'id');
        $data['cities'] = City::toBase()->pluck('name', 'id');
        $data['campuses'] = Campus::toBase()->pluck('campus_name', 'id');
        $data['colleges'] = College::toBase()->pluck('college_name', 'id');
        $data['courses'] = Course::toBase()->pluck('course_name', 'id');
        $data['year_levels'] = YearLevel::toBase()->pluck('year_level_name', 'id');
        $data['departments'] = Department::toBase()->pluck('department_name', 'id');
        $data['offices'] = Office::toBase()->pluck('office_name', 'id');

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
            ['address1', 'address2', 'country_id', 'city_id', 'state_id', 'postal_code']
        );
        $doctorArray = Arr::only($input, ['experience', 'twitter_url', 'linkedin_url', 'instagram_url']);
        $specialization = $input['specializations'];
        try {
            DB::beginTransaction();
            $input['email'] = setEmailLowerCase($input['email']);
            $input['status'] = (isset($input['status'])) ? 1 : 0;
            $input['password'] = Hash::make($input['password']);
            $input['type'] = User::DOCTOR;
            $input['language'] = Setting::where('key', 'language')->get()->toArray()[0]['value'];
            // Set email as verified with Philippine time
            $input['email_verified_at'] = now()->setTimezone('Asia/Manila')->toDateTimeString();
            $doctor = User::create($input);
            $doctor->assignRole('doctor');
            $doctor->address()->create($addressInputArray);
            $createDoctor = $doctor->doctor()->create($doctorArray);
            $createDoctor->specializations()->sync($specialization);
            if (isset($input['profile']) && ! empty('profile')) {
                $doctor->addMedia($input['profile'])->toMediaCollection(User::PROFILE, config('app.media_disc'));
            }
            // $doctor->sendEmailVerificationNotification();

            DB::commit();

            return $doctor;
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function update($input, $doctor)
    {
        $addressInputArray = Arr::only(
            $input,
            ['address1', 'address2', 'city_id', 'state_id', 'country_id', 'postal_code']
        );
        $doctorArray = Arr::only($input, ['experience', 'twitter_url', 'linkedin_url', 'instagram_url']);
        $qualificationArray = json_decode($input['qualifications'], true);
        $specialization = $input['specializations'];
        try {
            DB::beginTransaction();
            $input['email'] = setEmailLowerCase($input['email']);
            $input['status'] = (isset($input['status'])) ? 1 : 0;
            $input['type'] = User::DOCTOR;
            $doctor->user->update($input);
            $doctor->user->address()->update($addressInputArray);
            $doctor->update($doctorArray);
            $doctor->specializations()->sync($specialization);

            if (count($qualificationArray) >= 0) {
                if (isset($input['deletedQualifications'])) {
                    Qualification::whereIn('id', explode(',', $input['deletedQualifications']))->delete();
                }

                foreach ($qualificationArray as $qualifications) {
                    if ($qualifications == null) {
                        continue;
                    }
                    if (isset($qualifications['id'])) {
                        $doctor->user->qualifications()->where('id', $qualifications['id'])->update($qualifications);
                    } else {
                        unset($qualifications['id']);
                        $doctor->user->qualifications()->create($qualifications);
                    }
                }
            }

            if (isset($input['profile']) && ! empty('profile')) {
                $doctor->user->clearMediaCollection(User::PROFILE);
                $doctor->user->media()->delete();
                $doctor->user->addMedia($input['profile'])->toMediaCollection(User::PROFILE, config('app.media_disc'));
            }
            DB::commit();

            return $doctor;
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function updateProfile(array $userInput): bool
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();

            \Log::info('UserRepository updateProfile - User Input:', $userInput);

            $addressInputArray = Arr::only(
                $userInput,
                ['address1', 'address2', 'city_id', 'state_id', 'country_id', 'postal_code']
            );

            if ($user->hasRole('clinic_admin')) {
                $user->update($userInput);

                if ((! empty($userInput['image']))) {
                    $user->clearMediaCollection(User::PROFILE);
                    $user->media()->delete();
                    $user->addMedia($userInput['image'])->toMediaCollection(User::PROFILE, config('app.media_disc'));
                }

                if (isset($user->address)) {
                    $user->address()->update($addressInputArray);
                } else {
                    $user->address()->create($addressInputArray);
                }
            } elseif ($user->hasRole('patient')) {
                $patient =  Patient::where('user_id', $user->id)->first();


                $userInput['type'] = User::PATIENT;
                $userInput['email'] = setEmailLowerCase($userInput['email']);

                /** @var Patient $patient */
                $patient->user()->update(Arr::except($userInput, [
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
                    'image',
                    'is_employee',
                    'is_guest',
                    'position_type',
                    'all_year_levels'
                ]));

                if (isset($patient->address)) {
                    $patient->address()->update($addressInputArray);
                } else {
                    $patient->address()->create($addressInputArray);
                }

                if (! empty($userInput['image'])) {
                    $user->clearMediaCollection(Patient::PROFILE);
                    $user->patient->media()->delete();
                    $user->patient->addMedia($userInput['image'])->toMediaCollection(
                        Patient::PROFILE,
                        config('app.media_disc')
                    );
                }
            } elseif ($user->hasRole('doctor')) {
                $doctor =  Doctor::where('user_id', $user->id)->first();
                $userInput['type'] = User::DOCTOR;
                $userInput['email'] = setEmailLowerCase($userInput['email']);

                /** @var Patient $patient */
                $doctor->user()->update(Arr::except($userInput, [
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
                    'image'
                ]));

                if (isset($doctor->address)) {
                    $doctor->address()->update($addressInputArray);
                } else {
                    $doctor->address()->create($addressInputArray);
                }

                if (! empty($userInput['image'])) {
                    $user->clearMediaCollection(User::PROFILE);
                    $user->media()->delete();
                    $user->addMedia($userInput['image'])->toMediaCollection(
                        User::PROFILE,
                        config('app.media_disc')
                    );
                }
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
        $todayDate = Carbon::now()->format('Y-m-d');
        $doctor['data'] = Doctor::with(['user.address', 'specializations', 'appointments.patient.user'])->whereId($input->id)->first();
        $doctor['doctorSession'] = DoctorSession::whereDoctorId($input->id)->get();
        //        $doctor['appointments'] = DataTables::of((new UserDataTable())->getAppointment($input->id))->make(true);
        $doctor['appointmentStatus'] = Appointment::ALL_STATUS;
        $doctor['totalAppointmentCount'] = Appointment::whereDoctorId($input->id)->count();
        $doctor['todayAppointmentCount'] = Appointment::whereDoctorId($input->id)->where(
            'date',
            '=',
            $todayDate
        )->count();
        $doctor['upcomingAppointmentCount'] = Appointment::whereDoctorId($input->id)->where(
            'date',
            '>',
            $todayDate
        )->count();

        return $doctor;
    }
}
