<div>
    <div class="d-flex flex-column">
        <div class="card shadow-sm">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h3 class="fw-bolder m-0">Report Generation</h3>
                </div>
                <div class="card-toolbar">
                    <div class="d-flex align-items-center">
                        @if($tab !== 'global_search' && $tab !== 'accomplishment')
                        <a href="{{ 
                            isRole('clinic_admin') ? route('activity-logs.export', $exportQuery) : 
                            (isRole('staff') ? route('staff.activity-logs.export', $exportQuery) : 
                            route('doctors.activity-logs.export', $exportQuery))
                        }}"
                            class="btn btn-sm btn-outline-primary me-2">
                            <i class="fas fa-file-csv"></i> Export CSV
                        </a>
                        @endif
                        @if($tab !== 'global_search')
                        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body pt-0" wire:poll.30s>
                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                    <li class="nav-item">
                        <a class="nav-link text-active-primary py-4 {{ $tab === 'logs' ? 'active' : '' }}" 
                           href="javascript:void(0)" wire:click="setTab('logs')">
                           <i class="fas fa-list-ul me-2"></i>Activity Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary py-4 {{ $tab === 'visits' ? 'active' : '' }}" 
                           href="javascript:void(0)" wire:click="setTab('visits')">
                           <i class="fas fa-user-nurse me-2"></i>Patient Visits
                        </a>
                    </li>
                    @if(canViewActivityLogTab('accomplishment'))
                    <li class="nav-item">
                        <a class="nav-link text-active-primary py-4 {{ $tab === 'accomplishment' ? 'active' : '' }}"
                           href="javascript:void(0)" wire:click="setTab('accomplishment')">
                           <i class="fas fa-clipboard-list me-2"></i>Accomplishment Report
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link text-active-primary py-4 {{ $tab === 'inventory' ? 'active' : '' }}" 
                           href="javascript:void(0)" wire:click="setTab('inventory')">
                           <i class="fas fa-pills me-2"></i>Medicine Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary py-4 {{ $tab === 'dispensing' ? 'active' : '' }}" 
                           href="javascript:void(0)" wire:click="setTab('dispensing')">
                           <i class="fas fa-prescription-bottle-medical me-2"></i>Dispensing Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary py-4 {{ $tab === 'appointments' ? 'active' : '' }}" 
                           href="javascript:void(0)" wire:click="setTab('appointments')">
                           <i class="fas fa-calendar-check me-2"></i>Schedules
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary py-4 {{ $tab === 'global_search' ? 'active' : '' }}" 
                           href="javascript:void(0)" wire:click="setTab('global_search')">
                           <i class="fas fa-search-plus me-2"></i>Global Search
                        </a>
                    </li>
                </ul>

                <!-- Filter Form -->
                <div class="mb-8 p-5 bg-light rounded border">
                    <div class="row g-3">
                        @if($tab !== 'accomplishment')
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Search Keywords</label>
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm"
                                placeholder="{{ $tab === 'inventory' ? 'Medicine name, Category...' : ($tab === 'visits' ? 'Patient name, complaint, illness...' : 'Name, Description, Keywords...') }}">
                        </div>
                        @endif

                        @if($tab === 'logs')
                        <div class="col-md-2">
                            <label class="form-label fw-bold">User Type</label>
                            <select wire:model.live="user_type" class="form-select form-select-sm">
                                <option value="all">All</option>
                                <option value="admin">Admin</option>
                                <option value="doctor">Doctor</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Action</label>
                            <select wire:model.live="action" class="form-select form-select-sm">
                                <option value="all">All Actions</option>
                                @foreach($actions as $act)
                                <option value="{{ $act }}">
                                    {{ ucwords(str_replace('_', ' ', $act)) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        @if($tab === 'inventory')
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Stock Status</label>
                            <select wire:model.live="status" class="form-select form-select-sm">
                                <option value="all">All Items</option>
                                <option value="low_stock">Low Stock</option>
                            </select>
                        </div>
                        @endif

                        @if($tab !== 'inventory' && $tab !== 'global_search')
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Date From</label>
                            <input type="date" wire:model.live="date_from" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">Date To</label>
                            <input type="date" wire:model.live="date_to" class="form-control form-control-sm">
                        </div>
                        @endif

                        <div class="col-md-1 d-flex align-items-end">
                            <button wire:click="resetFilters" class="btn btn-sm btn-light w-100">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>

                        @if(in_array($tab, ['visits', 'accomplishment'], true))
                        @php
                            $selects = [
                                ['campus_id', 'Campus', $choices['campuses'], 'All campuses'],
                                ['college_id', 'College', $choices['colleges'], 'All colleges'],
                                ['course_id', 'Course', $choices['courses'], 'All courses'],
                                ['year_level_id', 'Year level', $choices['yearLevels'], 'All year levels'],
                                ['department_id', 'Department (faculty)', $choices['departments'], 'All departments'],
                                ['office_id', 'Office (staff)', $choices['offices'], 'All offices'],
                                ['patient_type_id', 'Patient type', $choices['patientTypes'], 'All types'],
                                ['medicine_id', 'Medicine given', $choices['medicines'], 'Any medicine'],
                                ['staff_id', 'Nurse in charge / encoder', $choices['staffMembers'], 'Anyone'],
                            ];
                        @endphp
                        <div class="col-12" x-data="{ open: false }">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="fw-bold me-1">Period:</span>
                                <button type="button" wire:click="setPeriod('this_month')" class="btn btn-sm btn-light">This month</button>
                                <button type="button" wire:click="setPeriod('last_month')" class="btn btn-sm btn-light">Last month</button>
                                <button type="button" wire:click="setPeriod('this_year')" class="btn btn-sm btn-light">This year</button>
                                <button type="button" wire:click="setPeriod('last_year')" class="btn btn-sm btn-light">Last year</button>
                                <input type="month" wire:model.live="month" class="form-control form-control-sm w-auto" title="Pick a month" aria-label="Pick a month">
                                <button type="button" class="btn btn-sm btn-light-primary ms-md-auto" @click="open = !open">
                                    <i class="fas fa-filter"></i> Filters
                                    @if($activeFilters > 0)<span class="badge bg-primary ms-1">{{ $activeFilters }}</span>@endif
                                </button>
                            </div>

                            <div x-show="open" x-cloak class="row g-3 mt-1">
                                @foreach($selects as [$model, $label, $options, $any])
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label fw-bold fs-7">{{ $label }}</label>
                                    <select wire:model.live="{{ $model }}" class="form-select form-select-sm">
                                        <option value="">{{ $any }}</option>
                                        @foreach($options as $optionId => $optionName)
                                        <option value="{{ $optionId }}">{{ $optionName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endforeach

                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label fw-bold fs-7">Gender</label>
                                    <select wire:model.live="gender" class="form-select form-select-sm">
                                        <option value="">Any gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label fw-bold fs-7">Consult mode</label>
                                    <select wire:model.live="consult_mode" class="form-select form-select-sm">
                                        <option value="all">All</option>
                                        <option value="physical">Walk-in</option>
                                        <option value="virtual">Virtual</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label fw-bold fs-7">Age group</label>
                                    <select wire:model.live="age_group" class="form-select form-select-sm">
                                        <option value="">Any age</option>
                                        @foreach($choices['ageGroups'] as $ageKey => $ageGroup)
                                        <option value="{{ $ageKey }}">{{ $ageGroup[0] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label fw-bold fs-7">Body system</label>
                                    <select wire:model.live="illness_system_id" class="form-select form-select-sm">
                                        <option value="">All body systems</option>
                                        @foreach($choices['illnessSystems'] as $system)
                                        <option value="{{ $system->id }}">{{ $system->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label fw-bold fs-7">Illness</label>
                                    <select wire:model.live="illness_id" class="form-select form-select-sm">
                                        <option value="">Any illness</option>
                                        <option value="none">Not classified yet</option>
                                        @foreach($choices['illnessSystems'] as $system)
                                        <optgroup label="{{ $system->name }}">
                                            @foreach($system->illnesses as $illness)
                                            <option value="{{ $illness->id }}">{{ $illness->name }}{{ $illness->is_active ? '' : ' (off)' }}</option>
                                            @endforeach
                                        </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label fw-bold fs-7">Service rendered</label>
                                    <select wire:model.live="service_id" class="form-select form-select-sm">
                                        <option value="">Any service</option>
                                        @foreach($choices['serviceGroups'] as $groupLabel => $services)
                                        <optgroup label="{{ $groupLabel }}">
                                            @foreach($services as $service)
                                            <option value="{{ $service->id }}">{{ $service->name }}{{ $service->is_active ? '' : ' (off)' }}</option>
                                            @endforeach
                                        </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label fw-bold fs-7">Pregnancy</label>
                                    <select wire:model.live="pregnancy" class="form-select form-select-sm">
                                        <option value="all">Any</option>
                                        <option value="pregnant">Pregnant</option>
                                        <option value="not_pregnant">Not pregnant</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label fw-bold fs-7">Chronic condition</label>
                                    <select wire:model.live="chronic" class="form-select form-select-sm">
                                        <option value="all">Any</option>
                                        <option value="with">Has a chronic condition</option>
                                        <option value="none">No chronic condition</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label fw-bold fs-7">Chronic condition contains</label>
                                    <input type="text" wire:model.live.debounce.400ms="chronic_text" class="form-control form-control-sm" placeholder="e.g. hypertension" maxlength="100">
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="tab-content" id="reportTabsContent">
                    <div wire:loading class="w-100 text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="mt-2 text-muted">Updating report data...</div>
                    </div>

                    <div wire:loading.remove>
                        @if($tab === 'logs')
                            <div class="table-responsive">
                                <table class="table table-hover table-rounded table-striped border gy-5 gs-7">
                                    <thead>
                                        <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                                            <th>Date</th>
                                            <th>Patient/Details</th>
                                            <th>User</th>
                                            <th>Activity</th>
                                            <th class="text-end">Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($activityLogs as $log)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-gray-800">{{ $log->date ? $log->date->format('M d, Y') : $log->created_at->format('M d, Y') }}</div>
                                                <div class="text-gray-600 fs-7">{{ $log->created_at->format('h:i A') }}</div>
                                            </td>
                                            <td>
                                                @if($log->patient_name)
                                                    <div class="fw-bold text-gray-900">{{ $log->patient_name }}</div>
                                                @endif
                                                <div class="text-gray-700 fs-7 text-truncate" style="max-width: 300px;" title="{{ $log->description }}">
                                                    {{ $log->description }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-gray-900">{{ $log->user_name }}</div>
                                                <div class="badge bg-{{ $log->user_type == 'admin' ? 'danger' : ($log->user_type == 'doctor' ? 'primary' : 'info') }} text-white fs-8">
                                                    {{ $log->formatted_user_type }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-success text-white fw-bold">{{ $log->formatted_action }}</span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ isRole('clinic_admin') ? route('activity-logs.show', $log->id) : (isRole('staff') ? route('staff.activity-logs.show', $log->id) : route('doctors.activity-logs.show', $log->id)) }}"
                                                   class="btn btn-sm btn-icon btn-light-primary btn-active-primary shadow-sm" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="5" class="text-center py-5">No activity logs found</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-5">{{ $activityLogs->links() }}</div>
                        @else
                            @include('activity_logs.reports.' . $tab)
                            @if(isset($reports) && method_exists($reports, 'links'))
                                <div class="mt-5">{{ $reports->links() }}</div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
