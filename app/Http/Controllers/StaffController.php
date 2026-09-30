<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Models\ClinicStation;
use App\Models\Role;
use App\Models\StaffDesignation;
use App\Models\User;
use App\Repositories\StaffRepository;
use Illuminate\Support\Facades\Hash;
use Laracasts\Flash\Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class StaffController extends AppBaseController
{
    /** @var StaffRepository */
    private $staffRepository;

    public function __construct(StaffRepository $staffRepo)
    {
        $this->staffRepository = $staffRepo;
    }

    /**
     * Display a listing of the Staff.
     *
     * @return Application|Factory|View
     */
    public function index(): \Illuminate\View\View
    {
        return view('staffs.index');
    }

    /**
     * Show the form for creating a new Staff.
     *
     * @return Application|Factory|View
     */
    public function create(): \Illuminate\View\View
    {
        $roles = $this->staffRepository->getRole();
        $defaultRoleId = Role::whereName('staff')->value('id');

        $staffDesignations = StaffDesignation::pluck('name', 'id');
        $staffDesignationCodes = StaffDesignation::pluck('code', 'id')->toArray();
        $clinicStations = ClinicStation::pluck('name', 'id');

        return view('staffs.create', compact('roles', 'defaultRoleId', 'staffDesignations', 'staffDesignationCodes', 'clinicStations'));
    }

    /**
     * Store a newly created Staff in storage.
     *
     * @return Application|Redirector|RedirectResponse
     */
    public function store(CreateStaffRequest $request): RedirectResponse
    {
        $input = $request->all();
        // Ensure role defaults to staff if not provided
        if (!isset($input['role']) || empty($input['role'])) {
            $input['role'] = Role::whereName('staff')->value('id');
        }
        $this->staffRepository->store($input);

        Flash::success(__('messages.flash.staff_create'));

        return redirect(route('staffs.index'));
    }

    /**
     * @return Application|Factory|View
     */
    public function show(User $staff): \Illuminate\View\View
    {
        $this->assertStaffAccount($staff);

        return view('staffs.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified Staff.
     *
     * @return Application|Factory|View
     */
    public function edit(User $staff): \Illuminate\View\View
    {
        $this->assertStaffAccount($staff);

        $roles = $this->staffRepository->getRole();
        $defaultRoleId = Role::whereName('staff')->value('id');

        $staffDesignations = StaffDesignation::pluck('name', 'id');
        $staffDesignationCodes = StaffDesignation::pluck('code', 'id')->toArray();
        $clinicStations = ClinicStation::pluck('name', 'id');

        return view('staffs.edit', compact('staff', 'roles', 'defaultRoleId', 'staffDesignations', 'staffDesignationCodes', 'clinicStations'));
    }

    /**
     * Update the specified Staff in storage.
     *
     * @return Application|RedirectResponse|Redirector
     */
    public function update(UpdateStaffRequest $request, User $staff): RedirectResponse
    {
        $this->assertStaffAccount($staff);

        $input = $request->all();
        // Ensure role defaults to staff if not provided
        if (!isset($input['role']) || empty($input['role'])) {
            $input['role'] = Role::whereName('staff')->value('id');
        }
        $this->staffRepository->update($input, $staff->id);

        Flash::success(__('messages.flash.staff_update'));

        return redirect(route('staffs.index'));
    }

    /**
     * Remove the specified Staff from storage.
     */
    public function destroy(User $staff)
    {
        $this->assertStaffAccount($staff);
        abort_if((int) $staff->id === (int) auth()->id(), 403, 'You cannot delete your own account.');

        $this->staffRepository->delete($staff->id);

        return $this->sendSuccess(__('messages.flash.staff_delete'));
    }

    /**
     * Reset a staff member's password to the default. Scoped to STAFF accounts only so an
     * admin cannot reset an admin/doctor/patient account through this endpoint. E-CRIT-2.
     */
    public function resetPassword(User $user): JsonResponse
    {
        // The route is staffs/{user}/reset-password: the parameter must be named $user, otherwise
        // implicit binding hands over an empty model and every reset failed (500).
        $staff = $user;
        abort_unless((int) $staff->type === User::STAFF, 403);

        $staff->update(['password' => Hash::make('123456')]);

        return $this->sendSuccess('Password has been reset to default (123456) successfully.');
    }

    /**
     * This controller manages STAFF accounts only. Route model binding resolves any row of the
     * shared users table, so without this an admin could open/edit/delete a doctor, patient or
     * admin account through /admin/staffs/{id} (the update forced type=STAFF and replaced the
     * roles). H-15.
     */
    private function assertStaffAccount(User $staff): void
    {
        abort_unless((int) $staff->type === User::STAFF, 404);
    }
}
