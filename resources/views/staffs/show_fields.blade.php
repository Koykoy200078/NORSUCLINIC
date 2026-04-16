<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('messages.staff.role')  }}</label>
    <span class="fs-4 text-gray-800">{{ $staff->role_name }}</span>
</div>
<div class="col-md-6 mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('messages.role.permissions')  }}</label>
    <br>
    @foreach($staff->getAllPermissions() as $permission)
    <span class="badge my-1 me-1 bg-{{ getBadgeColor($loop->index) }}">{{ $permission->display_name }}</span>
    @endforeach
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('messages.user.gender')  }}</label>
    <span
        class="fs-4 text-gray-800">{{ ($staff->gender == 1) ? __('messages.doctor.male') : __('messages.doctor.female') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('Institutional Email') }}</label>
    <span class="fs-4 text-gray-800">{{ !empty($staff->institutional_email) ? $staff->institutional_email : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('Employee ID') }}</label>
    <span class="fs-4 text-gray-800">{{ !empty($staff->employee_id) ? $staff->employee_id : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('Pager / Extension Number') }}</label>
    <span class="fs-4 text-gray-800">{{ !empty($staff->pager_extension) ? $staff->pager_extension : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('Role Designation') }}</label>
    <span class="fs-4 text-gray-800">{{ !empty(optional($staff->staffProfile)->roleDesignation) ? $staff->staffProfile->roleDesignation->name : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('Assigned Station') }}</label>
    <span class="fs-4 text-gray-800">{{ !empty(optional($staff->staffProfile)->assignedStation) ? $staff->staffProfile->assignedStation->name : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-12 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('Shift Schedule') }}</label>
    <span class="fs-4 text-gray-800">{{ !empty(optional($staff->staffProfile)->shift_schedule) ? $staff->staffProfile->shift_schedule : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('messages.patient.registered_on') }}</label>
    <span class="fs-4 text-gray-800">{{$staff->created_at->diffForHumans()}}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('messages.patient.last_updated') }}</label>
    <span class="fs-4 text-gray-800">{{$staff->updated_at->diffForHumans()}}</span>
</div>