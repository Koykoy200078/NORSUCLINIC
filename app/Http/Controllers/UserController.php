<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateQualificationRequest;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateChangePasswordRequest;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialization;
use App\Models\User;
use App\Models\Visit;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Laracasts\Flash\Flash;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class UserController extends AppBaseController
{
    /**
     * @var UserRepository
     */
    public $userRepo;

    /**
     * UserController constructor.
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepo = $userRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     *
     * @throws Exception
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $years = [];
        $currentYear = Carbon::now()->format('Y');
        for ($year = 1960; $year <= $currentYear; $year++) {
            $years[$year] = $year;
        }

        $status = User::STATUS;

        return view('doctors.index', compact('years', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(): \Illuminate\View\View
    {
        $specializations = Specialization::pluck('name', 'id')->toArray();
        $country = $this->userRepo->getCountries();
        $bloodGroup = Doctor::BLOOD_TYPE_ARRAY;

        return view('doctors.create', compact('specializations', 'country', 'bloodGroup'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Application|RedirectResponse|Redirector
     */
    public function store(CreateUserRequest $request): RedirectResponse
    {
        $input = $request->all();
        $this->userRepo->store($input);

        Flash::success(__('messages.flash.doctor_create'));

        $indexRoute = isRole('clinic_admin') ? 'doctors.index' : (isRole('staff') ? 'staff.doctors.index' : 'doctors.index');

        return redirect(route($indexRoute));
    }

    /**
     * @return Application|Factory|View|RedirectResponse
     *
     * @throws Exception
     */
    public function show(Doctor $doctor)
    {
        $doctorDetailData = $this->userRepo->doctorDetail($doctor);

        return view('doctors.show', compact('doctor', 'doctorDetailData'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Application|Factory|View
     */
    public function edit(Doctor $doctor): \Illuminate\View\View
    {
        $user = $doctor->user()->first();
        $qualifications = $user->qualifications()->get();
        $data = $this->userRepo->getSpecializationsData($doctor);
        $bloodGroup = Doctor::BLOOD_TYPE_ARRAY;
        $countries = $this->userRepo->getCountries();
        $state = $cities = null;
        $years = [];
        $currentYear = Carbon::now()->format('Y');
        for ($year = 1960; $year <= $currentYear; $year++) {
            $years[$year] = $year;
        }
        if (isset($countryId)) {
            $state = getStates($data['countryId']->toArray());
        }
        if (isset($stateId)) {
            $cities = getCities($data['stateId']->toArray());
        }

        return view(
            'doctors.edit',
            compact('user', 'qualifications', 'data', 'doctor', 'countries', 'state', 'cities', 'years', 'bloodGroup')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, Doctor $doctor): JsonResponse
    {
        $input = $request->all();
        $this->userRepo->update($input, $doctor);

        Flash::success(__('messages.flash.doctor_update'));

        return $this->sendSuccess(__('messages.flash.doctor_update'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor): JsonResponse
    {
        $existVisit = Visit::whereDoctorId($doctor->id)->exists();

        if ($existVisit) {
            return $this->sendError(__('messages.flash.doctor_use'));
        }

        try {
            DB::beginTransaction();

            // Store user reference
            $user = $doctor->user;

            // Delete related records first
            if ($user->media) {
                $user->media()->delete();
            }

            if ($user->address) {
                $user->address()->delete();
            }

            // Delete doctor record
            $doctor->delete();

            // Finally delete user
            $user->delete();

            DB::commit();

            return $this->sendSuccess(__('messages.flash.doctor_delete'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting doctor: ' . $e->getMessage());
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @return Application|Factory|View
     */
    public function editProfile(): \Illuminate\View\View
    {
        $user = Auth::user();
        $roleName = $user->getRoleNames()->first();

        if ($roleName == 'clinic_admin') {
            $patient = $user;
        } elseif ($roleName == 'doctor') {
            $patient = Doctor::with(['user', 'address'])->where('user_id', $user->id)->first();
        } else {
            $patient = Patient::with(['user', 'address'])->where('user_id', $user->id)->first();
        }

        $data = $this->userRepo->getData();

        // Load states and cities if address exists with values
        // The JavaScript will also handle loading via AJAX when dropdowns change
        if (!empty($patient->address)) {
            \Log::info('Address exists', [
                'country_id' => $patient->address->country_id,
                'state_id' => $patient->address->state_id,
                'city_id' => $patient->address->city_id
            ]);

            if (!empty($patient->address->country_id)) {
                $data['states'] = getStates($patient->address->country_id);
                \Log::info('Loaded states', ['count' => count($data['states'])]);
            } else {
                $data['states'] = []; // Empty array if no country selected
                \Log::info('No country_id - states array empty');
            }

            if (!empty($patient->address->state_id)) {
                $data['cities'] = getCities($patient->address->state_id);
                \Log::info('Loaded cities', ['count' => count($data['cities'])]);
            } else {
                $data['cities'] = []; // Empty array if no state selected
                \Log::info('No state_id - cities array empty');
            }

            if (!empty($patient->address->city_id)) {
                $data['barangays'] = getBarangays($patient->address->city_id);
                \Log::info('Loaded barangays', ['count' => count($data['barangays'])]);
            } else {
                $data['barangays'] = []; // Empty array if no city selected
                \Log::info('No city_id - barangays array empty');
            }
        } else {
            // No address exists yet - initialize empty arrays
            $data['states'] = [];
            $data['cities'] = [];
            $data['barangays'] = [];
            \Log::info('No address - all arrays empty');
        }

        // DEEP VERIFICATION: Log all critical values before passing to view
        \Log::info('=== DEEP DATA VERIFICATION BEFORE VIEW ===');
        \Log::info('User Object Data:', [
            'id' => $user->id,
            'email' => $user->email,
            'department_id' => $user->department_id,
            'department_id_type' => gettype($user->department_id),
            'department_id_is_null' => is_null($user->department_id),
            'department_id_is_empty' => empty($user->department_id),
            'office_id' => $user->office_id,
            'office_id_type' => gettype($user->office_id),
            'year_level_id' => $user->year_level_id,
            'college_id' => $user->college_id,
        ]);

        \Log::info('Patient Data:', [
            'patient_exists' => !is_null($patient),
            'patient_id' => $patient->id ?? 'NULL',
            'patient_user_id' => $patient->user_id ?? 'NULL',
        ]);

        \Log::info('Patient Address Data:', [
            'address_exists' => !empty($patient->address),
            'country_id' => $patient->address->country_id ?? 'NULL',
            'state_id' => $patient->address->state_id ?? 'NULL',
            'state_id_type' => isset($patient->address->state_id) ? gettype($patient->address->state_id) : 'NULL',
            'city_id' => $patient->address->city_id ?? 'NULL',
        ]);

        \Log::info('Dropdown Arrays Count:', [
            'departments' => count($data['departments'] ?? []),
            'offices' => count($data['offices'] ?? []),
            'states' => count($data['states'] ?? []),
            'cities' => count($data['cities'] ?? []),
            'barangays' => count($data['barangays'] ?? []),
        ]);

        return view('profile.index', compact('user', 'data', 'patient'));
    }

    public function updateProfile(UpdateUserProfileRequest $request): RedirectResponse
    {
        $array = $request->all();

        $data = Arr::except($array, ['_token', '_method', 'all_year_levels', 'is_edit', 'edit_patient_country_id', 'edit_patient_state_id', 'edit_patient_city_id']);

        if (getLogInUser()->is_default) {
            Flash::error(__('messages.common.error_default_records'));

            return redirect()->back();
        }

        $this->userRepo->updateProfile($data);
        Flash::success(__('messages.flash.user_profile_update'));

        return redirect(route('profile.setting'));
    }

    public function changePassword(UpdateChangePasswordRequest $request): JsonResponse
    {
        $input = $request->all();

        try {
            /** @var User $user */
            $user = Auth::user();
            if (!Hash::check($input['current_password'], $user->password)) {
                return $this->sendError(__('messages.flash.current_invalid'));
            }
            $input['password'] = Hash::make($input['new_password']);
            $user->update($input);

            return $this->sendSuccess(__('messages.flash.password_update'));
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function getStates(Request $request): JsonResponse
    {
        $countryId = $request->data;
        $states = getStates($countryId);

        return $this->sendResponse($states, __('messages.flash.retrieve'));
    }

    public function getCity(Request $request): JsonResponse
    {
        $state = $request->state;
        $cities = getCities($state);

        return $this->sendResponse($cities, __('messages.flash.retrieve'));
    }

    public function getBarangays(Request $request): JsonResponse
    {
        $cityId = $request->city;
        $barangays = getBarangays($cityId);

        return $this->sendResponse($barangays, __('messages.flash.retrieve'));
    }

    public function addQualification(CreateQualificationRequest $request, Doctor $doctor)
    {
        $this->userRepo->addQualification($request->all());

        return $this->sendSuccess(__('messages.flash.qualification_create'));
    }

    public function changeDoctorStatus(Request $request): JsonResponse
    {
        $doctor = User::findOrFail($request->id);
        $doctor->update(['status' => !$doctor->status]);

        return $this->sendResponse($doctor, __('messages.flash.status_update'));
    }

    public function updateLanguage(Request $request): JsonResponse
    {
        $language = $request->get('language');

        $user = getLogInUser();
        $user->update(['language' => $language]);

        return $this->sendSuccess(__('messages.flash.language_update'));
    }

    public function impersonate(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        getLogInUser()->impersonate($user);
        if ($user->hasRole('doctor')) {
            return redirect()->route('doctors.dashboard');
        } elseif ($user->hasRole('patient')) {
            return redirect()->route('patients.dashboard');
        }

        return redirect()->route('admin.dashboard');
    }

    public function impersonateLeave(): RedirectResponse
    {
        getLogInUser()->leaveImpersonation();

        return redirect()->route('admin.dashboard');
    }

    public function emailVerified(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->id);
        if ($request->value) {
            $user->update([
                'email_verified_at' => Carbon::now(),
            ]);
        } else {
            $user->update([
                'email_verified_at' => null,
            ]);
        }

        return $this->sendResponse($user, __('messages.flash.verified_email'));
    }

    public function emailNotification(Request $request): JsonResponse
    {
        $input = $request->all();
        $user = getLogInUser();
        $user->update([
            'email_notification' => isset($input['email_notification']) ? $input['email_notification'] : 0,
        ]);

        return $this->sendResponse($user, __('messages.flash.email_notification'));
    }

    public function resendEmailVerification($userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        if ($user->hasVerifiedEmail()) {
            return $this->sendError(__('messages.flash.user_already_verified'));
        }

        $user->sendEmailVerificationNotification();

        return $this->sendSuccess(__('messages.flash.notification_send'));
    }

    public function updateDarkMode(): JsonResponse
    {
        $user = Auth::user();
        app()->setLocale($user->language);
        $darkEnabled = $user->dark_mode == true;
        $user->update([
            'dark_mode' => !$darkEnabled,
        ]);

        return $this->sendSuccess(__('messages.flash.theme_change'));
    }
}
