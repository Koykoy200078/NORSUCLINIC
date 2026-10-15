<?php

namespace App\Http\Controllers;

use App\Repositories\DashboardRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        if ($request->ajax()) {
            return $this->sendResponse([], __('messages.filter_success'));
        }

        $hasDefaultPassword = Hash::check('123456', Auth::user()->password);

        return view('dashboard.index', compact('hasDefaultPassword'));
    }

    /**
     * The Day / Week / Month tabs of the admin dashboard (?day=day, ?week=week or ?month=month).
     */
    public function getPatientList(Request $request)
    {
        return $this->recentPatientsResponse($request, 'admin');
    }

    /**
     * The same tabs on the staff dashboard (open to every staff member, like the dashboard itself).
     */
    public function getStaffPatientList(Request $request)
    {
        return $this->recentPatientsResponse($request, 'staff');
    }

    private function recentPatientsResponse(Request $request, string $panel): JsonResponse
    {
        $range = collect(['day', 'week', 'month'])->first(fn (string $key) => $request->has($key));

        $data['patients'] = $this->dashboardRepository->recentPatients($range, $panel);

        return $this->sendResponse($data, __('messages.flash.patients_retrieve'));
    }

    /**
     * @return Application|Factory|View
     */
    public function doctorDashboard(Request $request): \Illuminate\View\View
    {
        $hasDefaultPassword = Hash::check('123456', Auth::user()->password);

        return view('doctor_dashboard.index', compact('hasDefaultPassword'));
    }

    /**
     * @return Application|Factory|View
     */
    public function patientDashboard(): \Illuminate\View\View
    {
        $logo = getSettingValue('logo');
        $hasDefaultPassword = Hash::check('123456', Auth::user()->password);
        return view('patient_dashboard.index', compact('logo', 'hasDefaultPassword'));
    }

    /**
     * Staff Dashboard
     * @return Application|Factory|View|JsonResponse
     */
    public function staffDashboard(Request $request)
    {
        if ($request->ajax()) {
            return $this->sendResponse([], __('messages.filter_success'));
        }

        $hasDefaultPassword = Hash::check('123456', Auth::user()->password);

        return view('staff_dashboard.index', compact('hasDefaultPassword'));
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
