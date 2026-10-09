@php
    $readOnly = isset($role) && $role->isReadOnly();
    $descriptions = [
        'manage_patients' => 'Patients and the shared patient queue. Doctors view and edit existing patients; staff can also register and archive them.',
        'manage_request_documents' => 'Consultations, certificates, prescriptions and laboratory requests. Record ownership and clinical rules still apply.',
        'manage_medicines' => 'Medicine inventory, categories, generics, stock-in, dispensing and dispense history.',
        'manage_doctors' => isset($role) && $role->name === 'doctor' ? 'View doctor profiles. Doctors cannot manage other doctor accounts.' : 'Manage doctor accounts and view doctor profiles.',
        'manage_specialties' => 'Manage medical specializations.',
        'manage_staff' => 'Manage Staff (Nurse) accounts.',
        'manage_roles' => 'Manage roles and their applicable permissions.',
        'manage_settings' => 'Configure clinic settings.',
        'manage_front_cms' => 'Manage the clinic public website content.',
        'manage_countries' => 'Manage countries.',
        'manage_states' => 'Manage states.',
        'manage_cities' => 'Manage cities.',
    ];
@endphp
@if($readOnly)
    <h2 class="fs-4">{{ $role->display_name }}</h2>
    <div class="alert alert-info no-auto-hide" role="status">
        @if($role->name === 'clinic_admin')
            Clinic Admin is locked and always retains all permissions, including access to repair role settings.
        @else
            Patients do not sign in. The Patient role is read-only.
        @endif
    </div>
@else
    <div class="mb-5">
        {{ Form::label('display_name', __('messages.common.name').':', ['class' => 'required form-label']) }}
        {{ Form::text('display_name', isset($role) ? $role->display_name : '', ['class' => 'form-control', 'required']) }}
    </div>
    <div class="alert alert-info no-auto-hide">
        Only permissions available in this role's panel are shown. Dashboard, Report Generation and Notifications &amp; Alerts are always available to Staff (Nurse), Doctor and Clinic Admin.
    </div>
    <label class="form-check mb-5">
        <input class="form-check-input" type="checkbox" id="checkAllPermission">
        <span class="form-check-label">{{ __('messages.role.select_all_permissions') }}</span>
    </label>
    @foreach($permissions['permissions'] as $permission)
        <div class="mb-4">
            <label class="form-check">
                <input class="form-check-input role-permission" type="checkbox" name="permission_id[]" value="{{ $permission->id }}" data-permission="{{ $permission->name }}"
                    @checked(in_array($permission->id, old('permission_id', isset($selectedPermissions) ? $selectedPermissions->keys()->all() : [])))>
                <span class="form-check-label fw-bold">{{ $permission->display_name }}</span>
            </label>
            <p class="text-muted ms-6 mb-0">{{ $descriptions[$permission->name] ?? $permission->display_name }}</p>
        </div>
    @endforeach
    <p class="text-muted">Clearing every permission leaves only Dashboard, Report Generation and Notifications &amp; Alerts.</p>
    {{ Form::submit(__('messages.common.save'), ['class' => 'btn btn-primary me-2']) }}
@endif
<a href="{{ getRouteByRole('roles.index') }}" class="btn btn-secondary">{{ __('messages.common.discard') }}</a>
