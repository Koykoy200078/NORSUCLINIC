@extends('layouts.app')

@section('title')
Activity Logs
@endsection

@section('content')
<div class="container-fluid">
    @include('flash::message')

    <div class="d-flex flex-column">
        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h3 class="fw-bolder m-0">Activity Logs</h3>
                </div>
                <div class="card-toolbar">
                    <a href="{{ 
                        isRole('clinic_admin') ? route('activity-logs.export', request()->query()) : 
                        (isRole('staff') ? route('staff.activity-logs.export', request()->query()) : 
                        route('doctors.activity-logs.export', request()->query()))
                    }}"
                        class="btn btn-sm btn-primary me-2">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                </div>
            </div>

            <div class="card-body pt-0">
                <!-- Filter Form -->
                <form method="GET" action="{{ 
                    isRole('clinic_admin') ? route('activity-logs.index') : 
                    (isRole('staff') ? route('staff.activity-logs.index') : 
                    route('doctors.activity-logs.index'))
                }}" class="mb-5">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                placeholder="Patient name, user, description..."
                                value="{{ request('search') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">User Type</label>
                            <select name="user_type" class="form-select form-select-sm">
                                <option value="all" {{ request('user_type') == 'all' ? 'selected' : '' }}>All</option>
                                <option value="admin" {{ request('user_type') == 'admin' ? 'selected' : '' }}>Clinic Admin</option>
                                <option value="doctor" {{ request('user_type') == 'doctor' ? 'selected' : '' }}>Doctor</option>
                                <option value="staff" {{ request('user_type') == 'staff' ? 'selected' : '' }}>Staff</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Action</label>
                            <select name="action" class="form-select form-select-sm">
                                <option value="all" {{ request('action') == 'all' ? 'selected' : '' }}>All Actions</option>
                                @foreach($actions as $action)
                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $action)) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm"
                                value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm"
                                value="{{ request('date_to') }}">
                        </div>

                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Activity Logs Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-rounded table-striped border gy-5 gs-7">
                        <thead>
                            <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                                <th>Date</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>College</th>
                                <th>Address</th>
                                <th>Contact Number</th>
                                <th>Complaints</th>
                                <th>Diagnose</th>
                                <th>Informant</th>
                                <th>Consult Mode</th>
                                <th>Course/Section</th>
                                <th>User</th>
                                <th>Action</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activityLogs as $log)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $log->date ? $log->date->format('M d, Y') : $log->created_at->format('M d, Y') }}</div>
                                    <div class="text-muted fs-7">{{ $log->created_at->format('h:i A') }}</div>
                                </td>
                                <td>{{ $log->patient_name ?? '-' }}</td>
                                <td>{{ $log->patient_age ?? '-' }}</td>
                                <td>{{ $log->patient_gender ?? '-' }}</td>
                                <td>{{ $log->college ?? '-' }}</td>
                                <td>
                                    <div class="text-truncate" style="max-width: 150px;" title="{{ $log->address }}">
                                        {{ $log->address ?? '-' }}
                                    </div>
                                </td>
                                <td>{{ $log->contact_number ?? '-' }}</td>
                                <td>
                                    <div class="text-truncate" style="max-width: 150px;" title="{{ $log->complaints }}">
                                        {{ $log->complaints ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 150px;" title="{{ $log->diagnosis }}">
                                        {{ $log->diagnosis ?? '-' }}
                                    </div>
                                </td>
                                <td>{{ $log->informant ?? '-' }}</td>
                                <td>{{ $log->consult_mode ?? '-' }}</td>
                                <td>{{ $log->course_section ?? '-' }}</td>
                                <td>
                                    <div class="fw-bold">{{ $log->user_name }}</div>
                                    <div class="text-muted fs-7">
                                        <span class="badge bg-{{ $log->user_type == 'admin' ? 'danger' : ($log->user_type == 'doctor' ? 'primary' : 'info') }}">
                                            {{ $log->formatted_user_type }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ $log->formatted_action }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ 
                                            isRole('clinic_admin') ? route('activity-logs.show', $log->id) : 
                                            (isRole('staff') ? route('staff.activity-logs.show', $log->id) : 
                                            route('doctors.activity-logs.show', $log->id))
                                        }}"
                                        class="btn btn-sm btn-light-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="15" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fs-3x mb-3"></i>
                                    <div>No activity logs found</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-5">
                    <div class="text-muted">
                        Showing {{ $activityLogs->firstItem() ?? 0 }} to {{ $activityLogs->lastItem() ?? 0 }}
                        of {{ $activityLogs->total() }} entries
                    </div>
                    <div>
                        {{ $activityLogs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection