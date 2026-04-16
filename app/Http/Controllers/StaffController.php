<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Models\ClinicStation;
use App\Models\Role;
use App\Models\StaffDesignation;
use App\Models\User;
use App\Repositories\StaffRepository;
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
        $clinicStations = ClinicStation::pluck('name', 'id');

        return view('staffs.create', compact('roles', 'defaultRoleId', 'staffDesignations', 'clinicStations'));
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
        $staff = User::whereType(User::STAFF)->findOrFail($staff['id']);

        return view('staffs.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified Staff.
     *
     * @return Application|Factory|View
     */
    public function edit(User $staff): \Illuminate\View\View
    {
        $roles = $this->staffRepository->getRole();
        $defaultRoleId = Role::whereName('staff')->value('id');

        $staffDesignations = StaffDesignation::pluck('name', 'id');
        $clinicStations = ClinicStation::pluck('name', 'id');

        return view('staffs.edit', compact('staff', 'roles', 'defaultRoleId', 'staffDesignations', 'clinicStations'));
    }

    /**
     * Update the specified Staff in storage.
     *
     * @return Application|RedirectResponse|Redirector
     */
    public function update(UpdateStaffRequest $request, User $staff): RedirectResponse
    {
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
        $staff->delete();

        return $this->sendSuccess(__('messages.flash.staff_delete'));
    }
}
