<div>
    <div class="d-flex flex-column">
        <div class="card shadow-sm">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h3 class="fw-bolder m-0">Report Generation</h3>
                </div>
                <div class="card-toolbar">
                    <div class="d-flex align-items-center">
                        @if($tab !== 'global_search')
                        <a href="{{ 
                            isRole('clinic_admin') ? route('activity-logs.export', ['tab' => $tab, 'search' => $search, 'date_from' => $date_from, 'date_to' => $date_to, 'user_type' => $user_type, 'action' => $action, 'status' => $status]) : 
                            (isRole('staff') ? route('staff.activity-logs.export', ['tab' => $tab, 'search' => $search, 'date_from' => $date_from, 'date_to' => $date_to, 'user_type' => $user_type, 'action' => $action, 'status' => $status]) : 
                            route('doctors.activity-logs.export', ['tab' => $tab, 'search' => $search, 'date_from' => $date_from, 'date_to' => $date_to, 'user_type' => $user_type, 'action' => $action, 'status' => $status]))
                        }}"
                            class="btn btn-sm btn-outline-primary me-2">
                            <i class="fas fa-file-csv"></i> Export CSV
                        </a>
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
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Search Keywords</label>
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm"
                                placeholder="{{ $tab === 'inventory' ? 'Medicine name, Category...' : 'Name, Description, Keywords...' }}">
                        </div>

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
