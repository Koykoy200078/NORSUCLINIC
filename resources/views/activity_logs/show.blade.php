@extends('layouts.app')

@section('title')
Report Details
@endsection

@section('content')
<div class="container-fluid">
    @include('flash::message')

    <div class="d-flex flex-column">
        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h3 class="fw-bolder m-0">Report Details</h3>
                </div>
                <div class="card-toolbar">
                    <a href="{{ 
                        isRole('clinic_admin') ? route('activity-logs.index') : 
                        (isRole('staff') ? route('staff.activity-logs.index') : 
                        route('doctors.activity-logs.index'))
                    }}" class="btn btn-sm btn-light-primary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="row g-5">
                    <!-- General Information -->
                    <div class="col-md-6">
                        <div class="card card-flush h-100">
                            <div class="card-header">
                                <h4 class="card-title">General Information</h4>
                            </div>
                            <div class="card-body pt-0">
                                <table class="table table-row-bordered">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold" width="40%">Date</td>
                                            <td>{{ $activityLog->date ? $activityLog->date->format('F d, Y') : $activityLog->created_at->format('F d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Time</td>
                                            <td>{{ $activityLog->created_at->format('h:i:s A') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">User</td>
                                            <td>{{ $activityLog->user_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">User Type</td>
                                            <td>
                                                <span class="badge bg-{{ $activityLog->user_type == 'admin' ? 'danger' : ($activityLog->user_type == 'doctor' ? 'primary' : 'info') }}">
                                                    {{ $activityLog->formatted_user_type }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Action</td>
                                            <td>
                                                <span class="badge bg-success">{{ $activityLog->formatted_action }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Description</td>
                                            <td>{{ $activityLog->description }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">IP Address</td>
                                            <td>{{ $activityLog->ip_address ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Patient/Document Information -->
                    <div class="col-md-6">
                        <div class="card card-flush h-100">
                            <div class="card-header">
                                <h4 class="card-title">Patient/Document Information</h4>
                            </div>
                            <div class="card-body pt-0">
                                <table class="table table-row-bordered">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold" width="40%">Patient Name</td>
                                            <td>{{ $activityLog->patient_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Age</td>
                                            <td>{{ $activityLog->patient_age ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Gender</td>
                                            <td>{{ $activityLog->patient_gender ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">College</td>
                                            <td>{{ $activityLog->college ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Course/Section</td>
                                            <td>{{ $activityLog->course_section ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Address</td>
                                            <td>{{ $activityLog->address ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Contact Number</td>
                                            <td>{{ $activityLog->contact_number ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Information -->
                    @if($activityLog->complaints || $activityLog->diagnosis || $activityLog->informant || $activityLog->consult_mode)
                    <div class="col-md-12">
                        <div class="card card-flush">
                            <div class="card-header">
                                <h4 class="card-title">Medical Information</h4>
                            </div>
                            <div class="card-body pt-0">
                                <table class="table table-row-bordered">
                                    <tbody>
                                        @if($activityLog->complaints)
                                        <tr>
                                            <td class="fw-bold" width="20%">Complaints</td>
                                            <td>{{ $activityLog->complaints }}</td>
                                        </tr>
                                        @endif

                                        @if($activityLog->diagnosis)
                                        <tr>
                                            <td class="fw-bold">Diagnosis</td>
                                            <td>{{ $activityLog->diagnosis }}</td>
                                        </tr>
                                        @endif

                                        @if($activityLog->informant)
                                        <tr>
                                            <td class="fw-bold">Informant</td>
                                            <td>{{ $activityLog->informant }}</td>
                                        </tr>
                                        @endif

                                        @if($activityLog->consult_mode)
                                        <tr>
                                            <td class="fw-bold">Consult Mode</td>
                                            <td>{{ $activityLog->consult_mode }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Additional Properties -->
                    @if($activityLog->properties)
                    <div class="col-md-12">
                        <div class="card card-flush">
                            <div class="card-header">
                                <h4 class="card-title">Additional Information</h4>
                            </div>
                            <div class="card-body pt-0">
                                <table class="table table-row-bordered">
                                    <tbody>
                                        @foreach($activityLog->properties as $key => $value)
                                        <tr>
                                            <td class="fw-bold" width="20%">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                            <td>
                                                @if(is_array($value))
                                                {{ json_encode($value, JSON_PRETTY_PRINT) }}
                                                @else
                                                {{ $value }}
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection