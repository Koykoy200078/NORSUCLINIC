<div class="row gx-10 mb-5">
    <div class="col-md-6 mb-5">
        {{ Form::label('display_name', __('messages.common.name').':', ['class' => 'required fs-5 fw-bolder form-label mb-2']) }}
        {{ Form::text('display_name', (isset($selectedPermissions))?$role->display_name:'', ['class' => 'form-control form-control-solid', 'placeholder' => __('messages.role.role'), 'required']) }}
    </div>
</div>
<div class="row">
    <div class="row">
        <div class="col-md-6 mb-5">
            <label class="fs-5 fw-bolder form-label mb-2">{{__('messages.role.role_permissions')}}</label>
        </div>
        <div class="col-md-6 mb-5">
            <span class="fs-5 fw-bolder form-label mb-2"> {{__('messages.role.select_all_permissions')}}</span>
            <label class="form-check form-check-custom form-check-sm form-check-solid float-end">
                <input class="form-check-input allPermissionCheck" type="checkbox" value="" id="checkAllPermission" />
            </label>
        </div>

        @php
        // Define permission groups with hierarchy and detailed descriptions
        $permissionGroups = [
        'Core Management' => [
        'icon' => 'fas fa-cogs',
        'description' => 'Core system administration and dashboard access permissions',
        'permissions' => [
        'manage_admin_dashboard' => 'Access to administrator dashboard with system-wide analytics and management tools',
        'manage_staff_dashboard' => 'Access to staff dashboard with limited management capabilities',
        'manage_settings' => 'Configure system settings, appearance, email templates, and application preferences'
        ]
        ],
        'User Management' => [
        'icon' => 'fas fa-users',
        'description' => 'User account creation, modification, and role management',
        'permissions' => [
        'manage_doctors' => 'Create, edit, and manage doctor profiles, specializations, and schedules',
        'manage_patients' => 'Create, edit, and manage patient profiles, medical records, and appointments',
        'manage_staff' => 'Create, edit, and manage staff accounts and their access levels',
        'manage_roles' => 'Create and modify user roles, assign permissions, and manage access control'
        ]
        ],
        'Medical Operations' => [
        'icon' => 'fas fa-stethoscope',
        'description' => 'Core medical practice management and patient care operations',
        'permissions' => [
        'manage_appointments' => 'Schedule, modify, and cancel patient appointments across the system',
        'manage_patient_visits' => 'Record and manage patient visits, consultations, and medical interactions',
        'manage_doctor_sessions' => 'Configure doctor availability, working hours, and session schedules',
        'manage_request_documents' => 'Handle patient document requests, medical certificates, and official forms'
        ]
        ],
        'Services & Specialties' => [
        'icon' => 'fas fa-medical-kit',
        'description' => 'Medical services, specializations, and pharmaceutical management',
        'permissions' => [
        'manage_services' => 'Create and manage medical services offered by the clinic',
        'manage_specialties' => 'Define and manage medical specializations and expertise areas',
        'manage_medicines' => 'Manage medicine inventory, categories, brands, purchases, and prescriptions'
        ]
        ],
        'Financial Management' => [
        'icon' => 'fas fa-money-bill',
        'description' => 'Financial operations, payments, and transaction management',
        'permissions' => [
        'manage_transactions' => 'View and manage financial transactions, payments, and billing records',
        'manage_currencies' => 'Configure supported currencies and exchange rates for international patients'
        ]
        ],
        'Location Management' => [
        'icon' => 'fas fa-map-marker-alt',
        'description' => 'Geographic data management for patient and clinic locations',
        'permissions' => [
        'manage_countries' => 'Manage country list for patient registration and clinic expansion',
        'manage_states' => 'Manage state/province data for accurate patient addressing',
        'manage_cities' => 'Manage city/municipality data for precise location services'
        ]
        ],
        'Content Management' => [
        'icon' => 'fas fa-edit',
        'description' => 'Website content, marketing materials, and public-facing information',
        'permissions' => [
        'manage_front_cms' => 'Manage website content, pages, sliders, testimonials, and public information',
        'manage_doctors_holiday' => 'Configure doctor holidays, vacation schedules, and unavailable periods'
        ]
        ]
        ];

        // Get permissions with their names for mapping
        $allPermissions = collect($permissions['permissions'])->keyBy('name');

        // Create enhanced permission data with descriptions
        $enhancedPermissions = [];
        foreach($permissionGroups as $groupName => $groupData) {
        foreach($groupData['permissions'] as $permName => $description) {
        if($allPermissions->has($permName)) {
        $enhancedPermissions[$permName] = [
        'permission' => $allPermissions[$permName],
        'description' => $description,
        'group' => $groupName
        ];
        }
        }
        }
        @endphp

        @foreach($permissionGroups as $groupName => $groupData)
        @php
        $groupPermissions = [];
        foreach($groupData['permissions'] as $permName => $description) {
        if($allPermissions->has($permName)) {
        $groupPermissions[$permName] = $description;
        }
        }
        @endphp

        @if(!empty($groupPermissions))
        <div class="col-12 mb-4">
            <div class="card card-flush">
                <div class="card-header">
                    <div class="card-title d-flex align-items-center">
                        <i class="{{ $groupData['icon'] }} text-primary me-2 fs-3"></i>
                        <div>
                            <h4 class="fw-bolder text-gray-800 mb-1">{{ $groupName }}</h4>
                            <p class="text-muted fs-6 mb-0">{{ $groupData['description'] }}</p>
                        </div>
                    </div>
                    <div class="card-toolbar">
                        <label class="form-check form-check-custom form-check-sm form-check-solid">
                            <input class="form-check-input group-permission-check" type="checkbox" value="" data-group="{{ \Illuminate\Support\Str::slug($groupName, '_') }}" />
                            <span class="form-check-label fw-bold text-gray-700">
                                Select All
                            </span>
                        </label>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        @foreach($groupPermissions as $permissionName => $description)
                        @php
                        $permission = $allPermissions[$permissionName];
                        $basePermissionName = str_replace('manage_', '', $permissionName);

                        // Check for CRUD variations of this permission
                        $viewPerm = $allPermissions->get('view_' . $basePermissionName) ?? $allPermissions->get($permissionName . '_view');
                        $editPerm = $allPermissions->get('edit_' . $basePermissionName) ?? $allPermissions->get($permissionName . '_edit');
                        $deletePerm = $allPermissions->get('delete_' . $basePermissionName) ?? $allPermissions->get($permissionName . '_delete');
                        $createPerm = $allPermissions->get('create_' . $basePermissionName) ?? $allPermissions->get($permissionName . '_create');

                        $hasCrudPermissions = $viewPerm || $editPerm || $deletePerm || $createPerm;
                        @endphp
                        <div class="col-lg-12 mb-4">
                            <div class="border border-gray-300 rounded p-4 bg-light">
                                {{-- Main Permission Checkbox with "Select All" functionality --}}
                                <div class="d-flex align-items-start mb-2">
                                    <input class="form-check-input permission group-{{ \Illuminate\Support\Str::slug($groupName, '_') }} main-permission mt-1 me-3"
                                        {{isset($selectedPermissions[$permission->id]) == $permission->id ?'checked':''}}
                                        type="checkbox"
                                        value="{{$permission->id}}"
                                        name="permission_id[]"
                                        data-permission-base="{{ $basePermissionName }}"
                                        id="permission_{{ $permission->id }}"
                                        style="width: 20px; height: 20px;" />
                                    <div class="flex-grow-1">
                                        <label class="form-check-label fw-bold text-gray-900 fs-5 mb-1 d-block" for="permission_{{ $permission->id }}" style="cursor: pointer;">
                                            <i class="fas fa-layer-group text-primary me-2"></i>
                                            {{ $permission->display_name }}
                                            <span class="badge badge-light-primary ms-2">All Access</span>
                                        </label>
                                        <div class="text-muted fs-7 mb-3">
                                            {{ $description }}
                                        </div>

                                        {{-- CRUD Sub-permissions Section --}}
                                        <div class="ms-4 p-3 bg-white rounded border border-dashed border-gray-400">
                                            <div class="d-flex flex-wrap gap-4">
                                                @if($viewPerm)
                                                <div class="form-check form-check-custom form-check-sm">
                                                    <input class="form-check-input sub-permission crud-permission-{{ $basePermissionName }}"
                                                        {{isset($selectedPermissions[$viewPerm->id]) == $viewPerm->id ?'checked':''}}
                                                        type="checkbox"
                                                        value="{{$viewPerm->id}}"
                                                        name="permission_id[]"
                                                        id="permission_{{ $viewPerm->id }}" />
                                                    <label class="form-check-label text-gray-800 fw-semibold" for="permission_{{ $viewPerm->id }}" style="cursor: pointer;">
                                                        <i class="fas fa-eye text-info me-1"></i>
                                                        Can View
                                                    </label>
                                                </div>
                                                @endif

                                                @if($createPerm)
                                                <div class="form-check form-check-custom form-check-sm">
                                                    <input class="form-check-input sub-permission crud-permission-{{ $basePermissionName }}"
                                                        {{isset($selectedPermissions[$createPerm->id]) == $createPerm->id ?'checked':''}}
                                                        type="checkbox"
                                                        value="{{$createPerm->id}}"
                                                        name="permission_id[]"
                                                        id="permission_{{ $createPerm->id }}" />
                                                    <label class="form-check-label text-gray-800 fw-semibold" for="permission_{{ $createPerm->id }}" style="cursor: pointer;">
                                                        <i class="fas fa-plus-circle text-success me-1"></i>
                                                        Can Create
                                                    </label>
                                                </div>
                                                @endif

                                                @if($editPerm)
                                                <div class="form-check form-check-custom form-check-sm">
                                                    <input class="form-check-input sub-permission crud-permission-{{ $basePermissionName }}"
                                                        {{isset($selectedPermissions[$editPerm->id]) == $editPerm->id ?'checked':''}}
                                                        type="checkbox"
                                                        value="{{$editPerm->id}}"
                                                        name="permission_id[]"
                                                        id="permission_{{ $editPerm->id }}" />
                                                    <label class="form-check-label text-gray-800 fw-semibold" for="permission_{{ $editPerm->id }}" style="cursor: pointer;">
                                                        <i class="fas fa-edit text-warning me-1"></i>
                                                        Can Edit
                                                    </label>
                                                </div>
                                                @endif

                                                @if($deletePerm)
                                                <div class="form-check form-check-custom form-check-sm">
                                                    <input class="form-check-input sub-permission crud-permission-{{ $basePermissionName }}"
                                                        {{isset($selectedPermissions[$deletePerm->id]) == $deletePerm->id ?'checked':''}}
                                                        type="checkbox"
                                                        value="{{$deletePerm->id}}"
                                                        name="permission_id[]"
                                                        id="permission_{{ $deletePerm->id }}" />
                                                    <label class="form-check-label text-gray-800 fw-semibold" for="permission_{{ $deletePerm->id }}" style="cursor: pointer;">
                                                        <i class="fas fa-trash-alt text-danger me-1"></i>
                                                        Can Delete
                                                    </label>
                                                </div>
                                                @endif

                                                @if(!$hasCrudPermissions)
                                                <div class="text-muted fst-italic fs-7">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Full access granted (no granular permissions configured)
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endforeach

        {{-- Handle any ungrouped permissions --}}
        @php
        $groupedPermissionNames = [];
        foreach($permissionGroups as $group) {
        $groupedPermissionNames = array_merge($groupedPermissionNames, array_keys($group['permissions']));
        }
        $ungroupedPermissions = $allPermissions->reject(function($permission) use ($groupedPermissionNames) {
        return in_array($permission->name, $groupedPermissionNames);
        });
        @endphp

        @if($ungroupedPermissions->isNotEmpty())
        <div class="col-12 mb-4">
            <div class="card card-flush">
                <div class="card-header">
                    <div class="card-title d-flex align-items-center">
                        <i class="fas fa-list text-secondary me-2 fs-3"></i>
                        <div>
                            <h4 class="fw-bolder text-gray-800 mb-1">Other Permissions</h4>
                            <p class="text-muted fs-6 mb-0">Additional system permissions not categorized above</p>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        @foreach($ungroupedPermissions as $permission)
                        <div class="col-lg-12 mb-3">
                            <div class="form-check form-check-custom form-check-solid p-3 bg-light-secondary rounded">
                                <div class="d-flex align-items-start">
                                    <input class="form-check-input permission mt-1"
                                        {{isset($selectedPermissions[$permission->id]) == $permission->id ?'checked':''}}
                                        type="checkbox"
                                        value="{{$permission->id}}"
                                        name="permission_id[]"
                                        id="permission_{{ $permission->id }}" />
                                    <div class="ms-3 flex-grow-1">
                                        <label class="form-check-label fw-bold text-gray-800 fs-6 mb-1" for="permission_{{ $permission->id }}">
                                            {{ $permission->display_name }}
                                        </label>
                                        <div class="text-muted fs-7">
                                            System permission: {{ $permission->name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div>
        {{ Form::submit(__('messages.common.save'),['class' => 'btn btn-primary me-2 my-1']) }}
        <a href="{{
            isRole('clinic_admin') ? route('roles.index') : 
            (isRole('staff') ? route('staff.roles.index') : 
            (isRole('doctor') ? route('doctors.roles.index') : route('roles.index')))
        }}" type="reset"
            class="btn btn-light btn-active-light-primary my-1">{{__('messages.common.discard')}}</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle "Select All" checkbox
        const selectAllCheckbox = document.getElementById('checkAllPermission');
        const allPermissionCheckboxes = document.querySelectorAll('input.permission');
        const allSubPermissionCheckboxes = document.querySelectorAll('input.sub-permission');
        const groupCheckboxes = document.querySelectorAll('input.group-permission-check');

        // Select/Deselect all permissions including sub-permissions
        selectAllCheckbox.addEventListener('change', function() {
            allPermissionCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            allSubPermissionCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            groupCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Handle group checkboxes
        groupCheckboxes.forEach(groupCheckbox => {
            groupCheckbox.addEventListener('change', function() {
                const groupName = this.getAttribute('data-group');
                const groupPermissions = document.querySelectorAll(`.group-${groupName}`);
                const groupSubPermissions = document.querySelectorAll(`.group-${groupName} ~ .sub-permission`);

                groupPermissions.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });

                // Also check/uncheck all CRUD sub-permissions in this group
                document.querySelectorAll('.sub-permission').forEach(subCheckbox => {
                    const parentMain = subCheckbox.closest('.col-lg-12').querySelector('.main-permission');
                    if (parentMain && parentMain.classList.contains(`group-${groupName}`)) {
                        subCheckbox.checked = this.checked;
                    }
                });

                updateSelectAllState();
            });
        });

        // Handle main permission checkboxes (parent CRUD checkboxes)
        document.querySelectorAll('.main-permission').forEach(mainCheckbox => {
            mainCheckbox.addEventListener('change', function() {
                const basePermission = this.getAttribute('data-permission-base');
                const crudCheckboxes = document.querySelectorAll(`.crud-permission-${basePermission}`);

                // When main permission is checked, check all its CRUD permissions
                crudCheckboxes.forEach(crudCheckbox => {
                    crudCheckbox.checked = this.checked;
                });

                updateGroupCheckboxState(this);
                updateSelectAllState();
            });
        });

        // Handle individual sub-permission checkboxes (CRUD permissions)
        allSubPermissionCheckboxes.forEach(subCheckbox => {
            subCheckbox.addEventListener('change', function() {
                // Find the main permission checkbox for this sub-permission
                const mainCheckbox = this.closest('.col-lg-12').querySelector('.main-permission');
                const basePermission = mainCheckbox.getAttribute('data-permission-base');
                const allCrudCheckboxes = document.querySelectorAll(`.crud-permission-${basePermission}`);
                const checkedCrudCheckboxes = document.querySelectorAll(`.crud-permission-${basePermission}:checked`);

                // If any CRUD permission is checked, check the main permission
                if (checkedCrudCheckboxes.length > 0) {
                    mainCheckbox.checked = true;
                } else {
                    // If no CRUD permissions are checked, uncheck the main permission
                    mainCheckbox.checked = false;
                }

                updateGroupCheckboxState(mainCheckbox);
                updateSelectAllState();
            });
        });

        // Handle individual main permission checkboxes (for permissions without CRUD)
        allPermissionCheckboxes.forEach(checkbox => {
            if (!checkbox.classList.contains('main-permission')) {
                checkbox.addEventListener('change', function() {
                    updateGroupCheckboxState(this);
                    updateSelectAllState();
                });
            }
        });

        function updateGroupCheckboxState(changedCheckbox) {
            const groupClasses = Array.from(changedCheckbox.classList).find(cls => cls.startsWith('group-'));
            if (groupClasses) {
                const groupName = groupClasses.replace('group-', '');
                const groupCheckbox = document.querySelector(`[data-group="${groupName}"]`);
                const groupPermissions = document.querySelectorAll(`.group-${groupName}`);
                const checkedGroupPermissions = document.querySelectorAll(`.group-${groupName}:checked`);

                if (groupCheckbox) {
                    groupCheckbox.checked = groupPermissions.length === checkedGroupPermissions.length;
                }
            }
        }

        function updateSelectAllState() {
            const allCheckboxes = document.querySelectorAll('input.permission, input.sub-permission');
            const checkedCheckboxes = document.querySelectorAll('input.permission:checked, input.sub-permission:checked');
            selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
        }

        // Initialize group states on page load
        groupCheckboxes.forEach(groupCheckbox => {
            const groupName = groupCheckbox.getAttribute('data-group');
            const groupPermissions = document.querySelectorAll(`.group-${groupName}`);
            const checkedGroupPermissions = document.querySelectorAll(`.group-${groupName}:checked`);
            groupCheckbox.checked = groupPermissions.length === checkedGroupPermissions.length;
        });

        // Initialize select all state
        updateSelectAllState();
    });
</script>