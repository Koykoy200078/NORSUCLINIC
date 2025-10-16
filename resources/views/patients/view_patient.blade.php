@extends('layouts.app')
@section('title')
Patient Data
@endsection
@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <h1 class="mb-0 me-1">Patient Data</h1>
        <div class="text-end mt-4 mt-md-0">
            <a href="{{ 
                isRole('clinic_admin') ? route('request-documents.create', ['user_id' => $patient->user_id, 'document_type' => 'medical_certificate']) : 
                (isRole('staff') ? route('staff.request-documents.create', ['user_id' => $patient->user_id, 'document_type' => 'medical_certificate']) : 
                (isRole('doctor') ? route('doctors.request-documents.create', ['user_id' => $patient->user_id, 'document_type' => 'medical_certificate']) : '#'))
            }}" class="btn btn-success me-2">
                <i class="fa-solid fa-file-medical"></i> Create Medical Certificate
            </a>
            <a href="{{ 
                isRole('clinic_admin') ? route('request-documents.create', ['user_id' => $patient->user_id, 'document_type' => 'consultation_form']) : 
                (isRole('staff') ? route('staff.request-documents.create', ['user_id' => $patient->user_id, 'document_type' => 'consultation_form']) : 
                (isRole('doctor') ? route('doctors.request-documents.create', ['user_id' => $patient->user_id, 'document_type' => 'consultation_form']) : '#'))
            }}" class="btn btn-primary me-2">
                <i class="fa-solid fa-notes-medical"></i> Create Consultation Form
            </a>
            <a href="{{ 
                isRole('clinic_admin') ? route('patients.index') : 
                (isRole('staff') ? route('staff.patients.index') : 
                (isRole('doctor') ? route('doctors.patients.index') : route('patients.index')))
            }}">
                <button type="button" class="btn btn-outline-primary">{{ __('messages.common.back') }}</button>
            </a>
        </div>
    </div>
</div>
@endsection
@section('content')
<div class="container">
    <!-- Patient Summary -->
    <div class="card mb-4">
        <div class="card-header" style="margin-left: -5px;">
            <h3>Patient Summary</h3>
        </div>
        <div class="card-body" style="margin-top: -35px;">
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
                    <p><strong>Last Consultation:</strong>
                        @if($consultations->isNotEmpty())
                        {{ \Carbon\Carbon::parse($consultations->last()->created_at)->format('F j, Y (g:i A)') }}
                        @else
                        No consultations yet
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Consultation History -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Consultation History</h3>
        </div>
        <div class="card-body p-1" style="margin-top: -20px;">
            @if($consultations->isEmpty())
            <p class="text-muted" style="margin-left: 25px;">No consultation records found.</p>
            @else
            @php
            // Define normal ranges for vital signs
            $normalRanges = [
            'vital_signs_bp' => ['min' => 90, 'max' => 120], // Systolic BP range
            'vital_signs_pr' => ['min' => 60, 'max' => 100], // Heart rate range
            'vital_signs_temp' => ['min' => 36.1, 'max' => 37.2], // Temperature range in °C
            'vital_signs_rr' => ['min' => 12, 'max' => 20], // Respiratory rate range
            'vital_signs_o2_sat' => ['min' => 95, 'max' => 100], // Oxygen saturation range
            ];

            // Helper function to determine the status of a vital sign
            if (!function_exists('getVitalSignStatus')) {
            function getVitalSignStatus($value, $range) {
            if (is_null($value)) return '';
            if ($value < $range['min']) return 'below-normal' ;
                if ($value> $range['max']) return 'above-normal';
                return 'normal';
                }
                }
                @endphp
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($consultations as $consultation)
                        @php
                        // Blood Pressure
                        $bp = explode('/', $consultation->vital_signs_bp ?? '0/0');
                        $systolic = (int) ($bp[0] ?? 0);
                        $bpStatus = getVitalSignStatus($systolic, $normalRanges['vital_signs_bp']);

                        // Heart Rate
                        $prStatus = getVitalSignStatus($consultation->vital_signs_pr, $normalRanges['vital_signs_pr']);

                        // Temperature
                        $tempStatus = getVitalSignStatus($consultation->vital_signs_temp, $normalRanges['vital_signs_temp']);

                        // Respiratory Rate
                        $rrStatus = getVitalSignStatus($consultation->vital_signs_rr, $normalRanges['vital_signs_rr']);

                        // Oxygen Saturation
                        $o2Status = getVitalSignStatus($consultation->vital_signs_o2_sat, $normalRanges['vital_signs_o2_sat']);
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($consultation->created_at)->format('F j, Y (g:i A)') }}</td>
                            <td>{{ $consultation->vital_signs_bp ? $consultation->vital_signs_bp . ' mmHg' : 'N/A' }}</td>
                            <td>{{ $consultation->vital_signs_pr ? $consultation->vital_signs_pr . ' bpm' : 'N/A' }}</td>
                            <td>{{ $consultation->vital_signs_temp ? $consultation->vital_signs_temp . ' °C' : 'N/A' }}</td>
                            <td>{{ $consultation->vital_signs_rr ? $consultation->vital_signs_rr . ' breaths/min' : 'N/A' }}</td>
                            <td>{{ $consultation->vital_signs_o2_sat ? $consultation->vital_signs_o2_sat . '%' : 'N/A' }}</td>
                            <td>{{ $consultation->vital_signs_height ? $consultation->vital_signs_height . 'cm' : 'N/A' }}</td>
                            <td>{{ $consultation->vital_signs_weight ? $consultation->vital_signs_weight . 'kg' : 'N/A' }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    @if(isRole('clinic_admin'))
                                    <a href="{{ route('request-documents.edit', ['request_document' => $consultation->id, 'patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('request-documents.destroy', $consultation->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="redirect_patient_id" value="{{ $patient->id }}">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this consultation record?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @elseif(isRole('staff'))
                                    <a href="{{ route('staff.request-documents.edit', ['request_document' => $consultation->id, 'patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('staff.request-documents.destroy', $consultation->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="redirect_patient_id" value="{{ $patient->id }}">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this consultation record?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @elseif(isRole('doctor'))
                                    <a href="{{ route('doctors.request-documents.edit', ['request_document' => $consultation->id, 'patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('doctors.request-documents.destroy', $consultation->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="redirect_patient_id" value="{{ $patient->id }}">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this consultation record?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
        </div>
    </div>

    <!-- Medical Certificate History -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Medical Certificate History</h3>
        </div>
        <div class="card-body p-1" style="margin-top: -20px;">
            @if($medicalCertificates->isEmpty())
            <p class="text-muted" style="margin-left: 25px;">No medical certificates found.</p>
            @else
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date Issued</th>
                        <th>Examined On</th>
                        <th>Request Of</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($medicalCertificates as $certificate)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($certificate->created_at)->format('F j, Y (g:i A)') }}</td>
                        <td>{{ $certificate->examined_on ? formatExaminedOnForPDF($certificate->examined_on) : 'N/A' }}</td>

                        <td>{{ $certificate->request_of ?? 'N/A' }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <!-- Edit Button -->
                                <a href="{{ 
                                    isRole('clinic_admin') ? route('request-documents.edit', $certificate->id) . '?patient_id=' . $patient->id : 
                                    (isRole('staff') ? route('staff.request-documents.edit', $certificate->id) . '?patient_id=' . $patient->id : 
                                    (isRole('doctor') ? route('doctors.request-documents.edit', $certificate->id) . '?patient_id=' . $patient->id : '#'))
                                }}" class="btn btn-sm btn-info" title="Edit Certificate">
                                    <i class="fa-solid fa-edit"></i>
                                </a>

                                <!-- Export PDF Button -->
                                <a href="{{ 
                                    isRole('clinic_admin') ? route('request-documents.export-pdf', $certificate->id) : 
                                    (isRole('staff') ? route('staff.request-documents.export-pdf', $certificate->id) : 
                                    (isRole('doctor') ? route('doctors.request-documents.export-pdf', $certificate->id) : '#'))
                                }}" class="btn btn-sm btn-primary" title="Download PDF">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>

                                <!-- Print Button -->
                                <button type="button" class="btn btn-sm btn-success print-certificate"
                                    data-certificate-id="{{ $certificate->id }}"
                                    title="Print Certificate">
                                    <i class="fa-solid fa-print"></i>
                                </button>

                                <!-- Delete Button -->
                                <button type="button" class="btn btn-sm btn-danger delete-certificate"
                                    data-certificate-id="{{ $certificate->id }}"
                                    data-patient-id="{{ $patient->id }}"
                                    data-delete-url="{{ 
                                        isRole('clinic_admin') ? route('request-documents.destroy', $certificate->id) : 
                                        (isRole('staff') ? route('staff.request-documents.destroy', $certificate->id) : 
                                        (isRole('doctor') ? route('doctors.request-documents.destroy', $certificate->id) : '#'))
                                    }}"
                                    title="Delete Certificate">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    <!-- Comparison Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Compare Consultations</h3>
        </div>
        <div class="card-body" style="margin-top: -30px; margin-left: 5px; min-height: fit-content;">
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
                <button type="submit" class="btn btn-success mt-3">Select</button>
            </form>
        </div>
    </div>

    <!-- Comparison Results -->
    @if(request('consultations_to_compare'))
    <div class="card mb-4">
        <div class="card-header">
            <h3>Comparison Results</h3>
        </div>
        <div class="card-body p-0">
            @php
            $selectedConsultations = $consultations->whereIn('id', request('consultations_to_compare'));

            // Ensure normal ranges are defined
            if (!isset($normalRanges)) {
            $normalRanges = [
            'vital_signs_bp' => ['min' => 90, 'max' => 120],
            'vital_signs_pr' => ['min' => 60, 'max' => 100],
            'vital_signs_temp' => ['min' => 36.1, 'max' => 37.2],
            'vital_signs_rr' => ['min' => 12, 'max' => 20],
            'vital_signs_o2_sat' => ['min' => 95, 'max' => 100],
            ];
            }
            @endphp

            <!-- Legend -->
            <div class="px-6 pt-0">
                <h5>Vital Legend:</h5>
                <ul>
                    <li><span class="badge bg-success">Normal</span>: Within the normal range</li>
                    <li><span class="badge bg-warning">Below Normal</span>: Below the normal range</li>
                    <li><span class="badge bg-danger">Above Normal</span>: Above the normal range</li>
                </ul>
            </div>

            <!-- Single Scrollable Container for Both Tables -->
            <div class="table-responsive px-3 pb-3">
                <!-- Vital Signs Table -->
                <table class="table table-bordered table-hover mb-3">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th style="min-width: 200px; position: sticky; left: 0; z-index: 10; background-color: #f8f9fa;">Vital Sign</th>
                            @foreach($selectedConsultations as $consultation)
                            <th style="min-width: 250px;">Consultation ({{ \Carbon\Carbon::parse($consultation->created_at)->format('F j, Y (g:i A)') }})</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="position: sticky; left: 0; background-color: white; font-weight: bold; z-index: 5;"><strong>Blood Pressure (mmHg)</strong></td>
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
                            <td style="position: sticky; left: 0; background-color: white; font-weight: bold; z-index: 5;"><strong>Heart Rate (bpm)</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            @php
                            $prStatus = getVitalSignStatus($consultation->vital_signs_pr, $normalRanges['vital_signs_pr']);
                            @endphp
                            <td class="{{ $prStatus }}">{{ $consultation->vital_signs_pr ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td style="position: sticky; left: 0; background-color: white; font-weight: bold; z-index: 5;"><strong>Temperature (°C)</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            @php
                            $tempStatus = getVitalSignStatus($consultation->vital_signs_temp, $normalRanges['vital_signs_temp']);
                            @endphp
                            <td class="{{ $tempStatus }}">{{ $consultation->vital_signs_temp ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td style="position: sticky; left: 0; background-color: white; font-weight: bold; z-index: 5;"><strong>Respiratory Rate (breaths/min)</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            @php
                            $rrStatus = getVitalSignStatus($consultation->vital_signs_rr, $normalRanges['vital_signs_rr']);
                            @endphp
                            <td class="{{ $rrStatus }}">{{ $consultation->vital_signs_rr ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td style="position: sticky; left: 0; background-color: white; font-weight: bold; z-index: 5;"><strong>Oxygen Saturation (%)</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            @php
                            $o2Status = getVitalSignStatus($consultation->vital_signs_o2_sat, $normalRanges['vital_signs_o2_sat']);
                            @endphp
                            <td class="{{ $o2Status }}">{{ $consultation->vital_signs_o2_sat ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>

                <!-- Additional Fields Table (in same scroll container) -->
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light sticky-top" style="top: 0;">
                        <tr>
                            <th style="min-width: 200px; position: sticky; left: 0; z-index: 10; background-color: #f8f9fa;">Field</th>
                            @foreach($selectedConsultations as $consultation)
                            <th style="min-width: 250px;">Consultation ({{ \Carbon\Carbon::parse($consultation->created_at)->format('F j, Y (g:i A)') }})</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="position: sticky; left: 0; background-color: white; font-weight: bold; z-index: 5;"><strong>Complaints</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            <td>{{ $consultation->complaints ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td style="position: sticky; left: 0; background-color: white; font-weight: bold; z-index: 5;"><strong>Pertinent Exam</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            <td>{{ $consultation->pertinent_exam ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td style="position: sticky; left: 0; background-color: white; font-weight: bold; z-index: 5;"><strong>Assessment</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            <td>{{ $consultation->assessment ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td style="position: sticky; left: 0; background-color: white; font-weight: bold; z-index: 5;"><strong>Plan</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            <td>{{ $consultation->plan ?? 'N/A' }}</td>
                            @endforeach
                        </tr>

                        <tr>
                            <td style="position: sticky; left: 0; background-color: white; font-weight: bold; z-index: 5;"><strong>Nursing In-Charge</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            <td>
                                @if($consultation->nursing_incharged_id)
                                @php
                                $nursingStaff = \App\Models\User::find($consultation->nursing_incharged_id);
                                @endphp
                                {{ $nursingStaff ? $nursingStaff->first_name . ' ' . $nursingStaff->last_name : 'N/A' }}
                                @else
                                N/A
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td style="position: sticky; left: 0; background-color: white; font-weight: bold; z-index: 5;"><strong>Attached Images</strong></td>
                            @foreach($selectedConsultations as $consultation)
                            <td>
                                @php
                                $images = $consultation->consultation_images;
                                // Decode if it's a JSON string
                                if (is_string($images)) {
                                $images = json_decode($images, true);
                                }
                                @endphp

                                @if($images && is_array($images) && count($images) > 0)
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($images as $index => $image)

                                    @if(isset($image['path']))
                                    @php
                                    // Images are stored in public/uploads/consultation_images/
                                    // Path in DB: consultation_images/PatientName/Timestamp/filename.jpg
                                    // Physical path: public/uploads/consultation_images/PatientName/Timestamp/filename.jpg
                                    // Public URL: http://domain/uploads/consultation_images/PatientName/Timestamp/filename.jpg
                                    $imagePath = public_path('uploads/' . $image['path']);
                                    $imageUrl = asset('uploads/' . $image['path']);
                                    @endphp

                                    @if(file_exists($imagePath))
                                    <a href="{{ $imageUrl }}"
                                        target="_blank"
                                        class="image-thumbnail"
                                        data-image-url="{{ $imageUrl }}"
                                        title="Click to view full size: {{ $image['name'] ?? 'Image' }}">
                                        <img src="{{ $imageUrl }}"
                                            alt="{{ $image['name'] ?? 'Consultation Image' }}"
                                            style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; cursor: pointer;"
                                            onerror="this.parentElement.style.display='none'">
                                    </a>
                                    @else
                                    <span class="text-danger" style="font-size: 10px;">Missing: {{ $image['name'] }}</span>
                                    @endif
                                    @endif
                                    @endforeach
                                </div>
                                <small class="text-muted">{{ count($images) }} image(s)</small>
                                @else
                                <span class="text-muted">No images</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Image Modal for Full Size View -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Consultation Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Full Size Image" style="max-width: 100%; height: auto;">
            </div>
            <div class="modal-footer">
                <a id="modalImageLink" href="" target="_blank" class="btn btn-primary">Open in New Tab</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .normal {
        background-color: #d4edda !important;
        /* Green background */
    }

    .below-normal {
        background-color: #fff3cd !important;
        /* Yellow background */
    }

    .above-normal {
        background-color: #f8d7da !important;
        /* Red background */
    }

    /* Override Bootstrap table striped rows */
    .table-striped tbody tr td.normal,
    .table-bordered tbody tr td.normal {
        background-color: #d4edda !important;
    }

    .table-striped tbody tr td.below-normal,
    .table-bordered tbody tr td.below-normal {
        background-color: #fff3cd !important;
    }

    .table-striped tbody tr td.above-normal,
    .table-bordered tbody tr td.above-normal {
        background-color: #f8d7da !important;
    }

    /* Scrollable table styles */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Sticky column styling */
    .table-bordered th[style*="position: sticky"],
    .table-bordered td[style*="position: sticky"] {
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    }

    /* Ensure table headers stay on top when scrolling */
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 9;
    }

    /* Better table borders for sticky columns */
    .table-bordered td[style*="position: sticky"]::after {
        content: '';
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 1px;
        background-color: #dee2e6;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Image thumbnail click handler for modal
        const imageThumbnails = document.querySelectorAll('.image-thumbnail');
        const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
        const modalImage = document.getElementById('modalImage');
        const modalImageLink = document.getElementById('modalImageLink');

        imageThumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function(e) {
                e.preventDefault();
                const imageUrl = this.getAttribute('data-image-url');
                modalImage.src = imageUrl;
                modalImageLink.href = imageUrl;
                imageModal.show();
            });
        });

        // Print certificate functionality
        const printButtons = document.querySelectorAll('.print-certificate');

        printButtons.forEach(button => {
            button.addEventListener('click', function() {
                const certificateId = this.getAttribute('data-certificate-id');

                // Determine the role-based route for PDF export with print action
                @if(isRole('clinic_admin'))
                const pdfUrl = '{{ route("request-documents.export-pdf", ":id") }}'.replace(':id', certificateId) + '?action=print';
                @elseif(isRole('staff'))
                const pdfUrl = '{{ route("staff.request-documents.export-pdf", ":id") }}'.replace(':id', certificateId) + '?action=print';
                @elseif(isRole('doctor'))
                const pdfUrl = '{{ route("doctors.request-documents.export-pdf", ":id") }}'.replace(':id', certificateId) + '?action=print';
                @else
                const pdfUrl = '#';
                @endif

                // Open PDF in new window for printing
                const printWindow = window.open(pdfUrl, '_blank', 'width=800,height=600');

                if (printWindow) {
                    // Wait for PDF to load, then trigger print dialog
                    printWindow.onload = function() {
                        setTimeout(function() {
                            printWindow.print();
                        }, 1000);
                    };
                } else {
                    alert('Please allow pop-ups to print the certificate.');
                }
            });
        });

        // Delete certificate functionality
        const deleteButtons = document.querySelectorAll('.delete-certificate');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const certificateId = this.getAttribute('data-certificate-id');
                const deleteUrl = this.getAttribute('data-delete-url');
                const patientId = this.getAttribute('data-patient-id');

                console.log('Delete clicked:', {
                    certificateId,
                    deleteUrl,
                    patientId
                });

                // Confirm before deleting
                if (confirm('Are you sure you want to delete this medical certificate? This action cannot be undone.')) {
                    // Create form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = deleteUrl;

                    // CSRF token
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = '{{ csrf_token() }}';
                    form.appendChild(csrfInput);

                    // Method spoofing for DELETE
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    // Add patient_id for redirect
                    const patientIdInput = document.createElement('input');
                    patientIdInput.type = 'hidden';
                    patientIdInput.name = 'redirect_patient_id';
                    patientIdInput.value = patientId;
                    form.appendChild(patientIdInput);

                    console.log('Form action:', form.action);
                    console.log('Form method:', form.method);
                    console.log('Form inputs:', {
                        token: csrfInput.value,
                        method: methodInput.value,
                        patientId: patientIdInput.value
                    });

                    // Append form to body and submit
                    document.body.appendChild(form);
                    console.log('Submitting form...');
                    form.submit();
                }
            });
        });
    });
</script>

@endsection