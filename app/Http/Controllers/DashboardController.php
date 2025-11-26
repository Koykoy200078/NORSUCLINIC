<?php

namespace App\Http\Controllers;

use App\Repositories\DashboardRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DashboardController extends AppBaseController
{
    /* @var DashboardRepository */
    private $dashboardRepository;

    /**
     * DashboardController constructor.
     */
    public function __construct(DashboardRepository $dashboardRepo)
    {
        $this->dashboardRepository = $dashboardRepo;
    }

    /**
     * @return Application|Factory|View|JsonResponse
     */
    public function index(Request $request)
    {
        $data = $this->dashboardRepository->getData();
        $appointmentChartData = $this->dashboardRepository->getAppointmentChartData($request->all());
        $clinic_name = Setting::where('key', 'clinic_name')->pluck('value')->first();
        if ($request->ajax()) {
            $appointmentFilterChartData = $this->dashboardRepository->getAppointmentChartData($request->all());

            return $this->sendResponse($appointmentFilterChartData, __('messages.filter_success'));
        }

        return view('dashboard.index', compact('data', 'appointmentChartData', 'clinic_name'));
    }

    /**
     * @param  Request  $request  *
     */
    public function getPatientList(Request $request)
    {
        $input = $request->all();

        $data['patients'] = $this->dashboardRepository->patientData($input);

        return $this->sendResponse($data, __('messages.flash.patients_retrieve'));
    }

    /**
     * @return Application|Factory|View
     */
    public function doctorDashboard(Request $request): \Illuminate\View\View
    {
        $appointments = $this->dashboardRepository->getDoctorData();
        $doctorAllAppointment = $this->dashboardRepository->doctorAllAppointment();
        return view('doctor_dashboard.index', compact('appointments', 'doctorAllAppointment'));
    }

    public function getDoctorAppointment(Request $request): JsonResponse
    {
        $input = $request->all();
        $data['patients'] = $this->dashboardRepository->doctorAppointment($input);

        return $this->sendResponse($data, __('messages.flash.patients_retrieve'));
    }

    /**
     * @return Application|Factory|View
     */
    public function patientDashboard(): \Illuminate\View\View
    {
        $data = $this->dashboardRepository->getPatientData();
        $logo = Setting::where('key', 'logo')->pluck('value');
        $patientAllAppointment = $this->dashboardRepository->patientAllAppointment();
        $hasDefaultPassword = Hash::check('123456', Auth::user()->password);
        return view('patient_dashboard.index', compact('data', 'logo', 'patientAllAppointment', 'hasDefaultPassword'));
    }

    /**
     * Staff Dashboard
     * @return Application|Factory|View|JsonResponse
     */
    public function staffDashboard(Request $request)
    {
        $data = $this->dashboardRepository->getStaffData();
        $appointmentChartData = $this->dashboardRepository->getAppointmentChartData($request->all());
        $clinic_name = Setting::where('key', 'clinic_name')->pluck('value')->first();

        if ($request->ajax()) {
            $appointmentFilterChartData = $this->dashboardRepository->getAppointmentChartData($request->all());
            return $this->sendResponse($appointmentFilterChartData, __('messages.filter_success'));
        }

        return view('staff_dashboard.index', compact('data', 'appointmentChartData', 'clinic_name'));
    }

    /**
     * Change default password for patient
     */
    public function changeDefaultPassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:6|confirmed',
            ]);

            $user = Auth::user();

            // Verify current password is the default password
            if (!Hash::check($request->current_password, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['The current password is incorrect.'],
                ]);
            }

            // Ensure new password is not the same as default
            if ($request->new_password === '123456') {
                throw ValidationException::withMessages([
                    'new_password' => ['Please choose a different password from the default one.'],
                ]);
            }

            // Update password
            $user->password = Hash::make($request->new_password);
            $user->save();

            return $this->sendSuccess('Password changed successfully.');
        } catch (ValidationException $e) {
            return $this->sendError($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->sendError('An error occurred while changing password.', 500);
        }
    }
}
