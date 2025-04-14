@extends('layouts.app')
@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <h1 class="mb-0 me-1">Patient History</h1>
        <div class="text-end mt-4 mt-md-0">
            <a href="/admin/patients">
                <button type="button" class="btn btn-outline-primary float-end">{{ __('messages.common.back') }}</button>
            </a>
        </div>
    </div>
</div>
@endsection
@section('content')
<div class="container">
    <!-- Patient Summary -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Patient Summary</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Name:</strong> {{ $patient->user->first_name }} {{ $patient->user->last_name }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Email:</strong> {{ $patient->user->email }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Date of Birth:</strong>
                        {{ \Carbon\Carbon::parse($patient->user->dob)->format('F j, Y') }}
                        ({{ \Carbon\Carbon::parse($patient->user->dob)->age }} years old)
                    </p>
                </div>

            </div>
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Sex:</strong>
                        @if($patient->user->gender == 1)
                        Male
                        @elseif($patient->user->gender == 2)
                        Female
                        @else
                        N/A
                        @endif</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Contact Number:</strong> {{ $patient->user->contact ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Last Consultation:</strong> {{ \Carbon\Carbon::parse($consultations->last()->created_at)->format('F j, Y (g:i A)') ?? 'No consultations yet' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Consultation History -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Consultation History</h3>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Blood Pressure</th>
                        <th>Heart Rate</th>
                        <th>Temperature</th>
                        <th>Respiratory Rate</th>
                        <th>Oxygen Saturation</th>
                        <th>Height</th>
                        <th>Weight</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($consultations as $consultation)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($consultation->created_at)->format('F j, Y (g:i A)') }}</td>
                        <td>{{ $consultation->vital_signs_bp . ' mmHg' ?? 'N/A' }}</td>
                        <td>{{ $consultation->vital_signs_pr . ' bpm' ?? 'N/A' }}</td>
                        <td>{{ $consultation->vital_signs_temp . ' °C' ?? 'N/A' }}</td>
                        <td>{{ $consultation->vital_signs_rr . ' breaths/min' ?? 'N/A' }}</td>
                        <td>{{ $consultation->vital_signs_o2_sat . '%' ?? 'N/A' }}</td>
                        <td>{{ $consultation->vital_signs_height . 'cm' ?? 'N/A' }}</td>
                        <td>{{ $consultation->vital_signs_weight . 'kg' ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Comparison Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Compare Consultations</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-12">
                        <label for="consultations_to_compare">Select Consultations to Compare</label>
                        <select name="consultations_to_compare[]" id="consultations_to_compare" class="form-control" multiple>
                            @foreach($consultations as $consultation)
                            <option value="{{ $consultation->id }}" {{ in_array($consultation->id, request('consultations_to_compare', [])) ? 'selected' : '' }}>
                                {{ $consultation->created_at }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold down the Ctrl (Windows) or Command (Mac) key to select multiple dates.</small>
                    </div>
                </div>
                <button type="submit" class="btn btn-success mt-3">Compare</button>
            </form>
        </div>
    </div>

    <!-- Comparison Results -->
    @if(request('consultations_to_compare'))
    <div class="card mb-4">
        <div class="card-header">
            <h3>Comparison Results</h3>
        </div>
        <div class="card-body">
            @php
            $selectedConsultations = $consultations->whereIn('id', request('consultations_to_compare'));

            // Define normal ranges for vital signs
            $normalRanges = [
            'vital_signs_bp' => ['min' => 90, 'max' => 120], // Systolic BP range
            'vital_signs_pr' => ['min' => 60, 'max' => 100], // Heart rate range
            'vital_signs_temp' => ['min' => 36.1, 'max' => 37.2], // Temperature range in °C
            'vital_signs_rr' => ['min' => 12, 'max' => 20], // Respiratory rate range
            'vital_signs_o2_sat' => ['min' => 95, 'max' => 100], // Oxygen saturation range
            ];

            // Helper function to determine the status of a vital sign
            function getVitalSignStatus($value, $range) {
            if (is_null($value)) return 'N/A';
            if ($value < $range['min']) return 'below-normal' ;
                if ($value> $range['max']) return 'above-normal';
                return 'normal';
                }
                @endphp

                <!-- Legend -->
                <div class="mt-4">
                    <h5>Vital Legend:</h5>
                    <ul>
                        <li><span class="badge bg-success">Normal</span>: Within the normal range</li>
                        <li><span class="badge bg-warning">Below Normal</span>: Below the normal range</li>
                        <li><span class="badge bg-danger">Above Normal</span>: Above the normal range</li>
                    </ul>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Vital Sign</th>
                            @foreach($selectedConsultations as $consultation)
                            <th>Consultation ({{ \Carbon\Carbon::parse($consultation->created_at)->format('F j, Y (g:i A)') }})</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Blood Pressure (mmHg)</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            @php
                            $bp = explode('/', $consultation->vital_signs_bp ?? '0/0');
                            $systolic = (int) ($bp[0] ?? 0);
                            $bpStatus = getVitalSignStatus($systolic, $normalRanges['vital_signs_bp']);
                            @endphp
                            <td class="{{ $bpStatus }}">{{ $consultation->vital_signs_bp ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Heart Rate (bpm)</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            @php
                            $prStatus = getVitalSignStatus($consultation->vital_signs_pr, $normalRanges['vital_signs_pr']);
                            @endphp
                            <td class="{{ $prStatus }}">{{ $consultation->vital_signs_pr ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Temperature (°C)</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            @php
                            $tempStatus = getVitalSignStatus($consultation->vital_signs_temp, $normalRanges['vital_signs_temp']);
                            @endphp
                            <td class="{{ $tempStatus }}">{{ $consultation->vital_signs_temp ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Respiratory Rate (breaths/min)</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            @php
                            $rrStatus = getVitalSignStatus($consultation->vital_signs_rr, $normalRanges['vital_signs_rr']);
                            @endphp
                            <td class="{{ $rrStatus }}">{{ $consultation->vital_signs_rr ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Oxygen Saturation (%)</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            @php
                            $o2Status = getVitalSignStatus($consultation->vital_signs_o2_sat, $normalRanges['vital_signs_o2_sat']);
                            @endphp
                            <td class="{{ $o2Status }}">{{ $consultation->vital_signs_o2_sat ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>

                <!-- Additional Fields -->
                <table class="table table-bordered mt-4">
                    <thead>
                        <tr>
                            <th>Field</th>
                            @foreach($selectedConsultations as $consultation)
                            <th>Consultation ({{ \Carbon\Carbon::parse($consultation->created_at)->format('F j, Y (g:i A)') }})</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Complaints</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            <td>{{ $consultation->complaints ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Pertinent Exam</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            <td>{{ $consultation->pertinent_exam ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Assessment</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            <td>{{ $consultation->assessment ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Plan</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            <td>{{ $consultation->plan ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
        </div>
    </div>
    @endif
</div>

<style>
    .normal {
        background-color: #d4edda;
        /* Green */
        color: #155724;
    }

    .below-normal {
        background-color: #fff3cd;
        /* Yellow */
        color: #856404;
    }

    .above-normal {
        background-color: #f8d7da;
        /* Red */
        color: #721c24;
    }
</style>
@endsection