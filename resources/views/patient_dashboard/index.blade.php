@extends('layouts.app')
@section('title')
{{ __('messages.dashboard') }}
@endsection
@section('content')
<div class="container-fluid">
    {{-- Top Row: Welcome Card and Statistics --}}
    @php
    $hasAppointmentData = ($data['todayAppointmentCount'] ?? 0) > 0 || ($data['upcomingAppointmentCount'] ?? 0) > 0 || ($data['completedAppointmentCount'] ?? 0) > 0;
    @endphp

    <div class="row g-5 g-xl-8">
        {{-- Welcome Card with Profile Summary Section --}}
        <div class="{{ $hasAppointmentData ? 'col-xxl-4' : 'col-xxl-12' }} col-xl-12">
            <livewire:patient-dashboard-table />

            {{-- Profile Summary Card --}}
            <div class="card shadow-sm mt-5">
                <div class="card-header border-0 pt-5 pb-1">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">
                            <i class="fas fa-user-circle text-info me-2"></i>
                            {{ __('messages.user.profile_summary') }}
                        </span>
                    </h3>
                </div>
                <div class="card-body pt-2">
                    @php
                    $patient = getLogInUser()->patient;
                    $address = $patient->address ?? null;
                    @endphp
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-id-card text-muted fs-4 me-3" style="width: 25px;"></i>
                            <div>
                                <span class="text-muted fs-7">{{ __('messages.patient.patient_unique_id') }}</span>
                                <h6 class="mb-0 fw-bold">{{ $patient->patient_unique_id }}</h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-phone text-muted fs-4 me-3" style="width: 25px;"></i>
                            <div>
                                <span class="text-muted fs-7">{{ __('messages.user.phone') }}</span>
                                <h6 class="mb-0 fw-bold">{{ getLogInUser()->contact ?? __('messages.common.n/a') }}</h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-birthday-cake text-muted fs-4 me-3" style="width: 25px;"></i>
                            <div>
                                <span class="text-muted fs-7">{{ __('messages.user.dob') }}</span>
                                <h6 class="mb-0 fw-bold">
                                    {{ getLogInUser()->dob ? \Carbon\Carbon::parse(getLogInUser()->dob)->format('d M Y') : __('messages.common.n/a') }}
                                </h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-venus-mars text-muted fs-4 me-3" style="width: 25px;"></i>
                            <div>
                                <span class="text-muted fs-7">{{ __('messages.user.gender') }}</span>
                                <h6 class="mb-0 fw-bold">
                                    {{ getLogInUser()->gender == 1 ? __('messages.user.male') : (getLogInUser()->gender == 2 ? __('messages.user.female') : __('messages.common.n/a')) }}
                                </h6>
                            </div>
                        </div>
                        @if($address)
                        <div class="d-flex align-items-start">
                            <i class="fas fa-map-marker-alt text-muted fs-4 me-3 mt-1" style="width: 25px;"></i>
                            <div>
                                <span class="text-muted fs-7">{{ __('messages.common.address') }}</span>
                                <p class="mb-0 fw-bold text-gray-700">
                                    @if($address->address1)
                                    {{ $address->address1 }}<br>
                                    @endif
                                    @if($address->address2)
                                    {{ $address->address2 }}<br>
                                    @endif
                                    {{ $address->city }}{{ $address->zip ? ', ' . $address->zip : '' }}
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                    <a href="{{ route('profile.setting') }}" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-edit me-2"></i>{{ __('messages.user.edit_profile') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Statistics Cards Section --}}
        @if($hasAppointmentData)
        <div class="col-xxl-8 col-xl-12">
            <livewire:patient-dashboard-sidebar-table />
        </div>
        @endif
    </div>

    {{-- Second Row: Medicine History and Recent Activity --}}
    <div class="row g-5 g-xl-8 mt-5">
        {{-- Medicine History Card --}}
        <div class="col-xxl-6 col-xl-12">
            <div class="card card-xl-stretch mb-5 mb-xl-8 shadow-sm">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">
                            <i class="fas fa-pills text-primary me-2"></i>
                            Medicine History
                        </span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Medicines Used/Dispensed</span>
                    </h3>
                    <div class="card-toolbar">
                        <form method="GET" action="{{ route('patients.dashboard') }}" class="d-flex align-items-center">
                            <select name="medicine_month" class="form-select form-select-sm w-150px" onchange="this.form.submit()">
                                <option value="">All Time</option>
                                @for($i = 0; $i < 12; $i++)
                                    @php
                                    $month=\Carbon\Carbon::now()->subMonths($i);
                                    $monthValue = $month->format('Y-m');
                                    $monthLabel = $month->format('F Y');
                                    @endphp
                                    <option value="{{ $monthValue }}" {{ request('medicine_month') == $monthValue ? 'selected' : '' }}>
                                        {{ $monthLabel }}
                                    </option>
                                    @endfor
                            </select>
                        </form>
                    </div>
                </div>
                <div class="card-body py-3">
                    @php
                    use App\Models\UsedMedicineView;
                    $patientName = Auth::user()->full_name;
                    $medicineQuery = UsedMedicineView::where('patient_name', $patientName);

                    // Apply month filter if selected
                    if(request('medicine_month')) {
                    $medicineQuery->whereYear('created_at', substr(request('medicine_month'), 0, 4))
                    ->whereMonth('created_at', substr(request('medicine_month'), 5, 2));
                    }

                    $usedMedicines = $medicineQuery->latest('created_at')
                    ->paginate(5, ['*'], 'medicine_page')
                    ->appends(['medicine_month' => request('medicine_month')]);

                    // Debug: Check if data exists
                    // dd($usedMedicines->toArray());
                    @endphp

                    {{-- Debug Info --}}
                    @if(config('app.debug'))
                    <div class="alert alert-info mb-3">
                        <strong>Debug Info:</strong><br>
                        Patient Name: {{ $patientName }}<br>
                        Total Records: {{ $usedMedicines->total() }}<br>
                        Current Page Items: {{ $usedMedicines->count() }}
                    </div>
                    @endif

                    @if($usedMedicines->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th class="min-w-100px">{{ __('messages.common.date') }}</th>
                                    <th class="min-w-150px">Medicine</th>
                                    <th class="min-w-100px">Expiry Date</th>
                                    <th class="min-w-80px">Qty</th>
                                    <th class="min-w-100px">Source</th>
                                    <th class="min-w-100px">Used For</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usedMedicines as $medicine)
                                <tr>
                                    <td>
                                        <span class="text-dark fw-bold fs-7">
                                            {{ \Carbon\Carbon::parse($medicine->created_at)->format('d M Y') }}
                                        </span>
                                        <br>
                                        <span class="text-muted fs-8">
                                            {{ \Carbon\Carbon::parse($medicine->created_at)->format('h:i A') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-bold">
                                            {!! $medicine->medicine_name ?: '<em>N/A</em>' !!}
                                        </span>
                                        @if(config('app.debug'))
                                        <br><small class="text-muted">ID: {{ $medicine->medicine_id }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($medicine->expiry_date)
                                        @php
                                        $expiryDate = \Carbon\Carbon::parse($medicine->expiry_date);
                                        $isExpired = $expiryDate->isPast();
                                        $isExpiringSoon = !$isExpired && $expiryDate->diffInDays(now()) <= 30;
                                            @endphp
                                            @if($isExpired)
                                            <span class="badge bg-danger">
                                            {{ $expiryDate->format('d M Y') }}
                                            <i class="fas fa-exclamation-triangle ms-1"></i>
                                            </span>
                                            @elseif($isExpiringSoon)
                                            <span class="badge bg-warning">
                                                {{ $expiryDate->format('d M Y') }}
                                                <i class="fas fa-clock ms-1"></i>
                                            </span>
                                            @else
                                            <span class="badge bg-info">
                                                {{ $expiryDate->format('d M Y') }}
                                            </span>
                                            @endif
                                            @else
                                            <span class="text-muted">N/A</span>
                                            @endif
                                    </td>
                                    <td>
                                        <span class="text-dark fw-bold">{!! $medicine->quantity ?: '<em>0</em>' !!}</span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-bold">
                                            {!! $medicine->source ?: '<em>N/A</em>' !!}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-semibold fs-7">
                                            {{ $medicine->used_for ?: 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($usedMedicines->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $usedMedicines->links() }}
                    </div>
                    @endif
                    @else
                    <div class="text-center py-10">
                        <i class="fas fa-pills fs-3x text-muted mb-3"></i>
                        <p class="text-muted fw-bold">No medicine history found</p>
                        <p class="text-muted fs-7">Your dispensed medicines will appear here</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Health Records & Vital Signs Card --}}
        <div class="col-xxl-6 col-xl-12">
            <div class="card card-xl-stretch mb-5 mb-xl-8 shadow-sm">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">
                            <i class="fas fa-heartbeat text-danger me-2"></i>
                            Health Records - Vital Signs
                        </span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Recent Measurements</span>
                    </h3>
                    <div class="card-toolbar">
                        <form method="GET" action="{{ route('patients.dashboard') }}" class="d-flex align-items-center">
                            <select name="vital_month" class="form-select form-select-sm w-150px" onchange="this.form.submit()">
                                <option value="">All Time</option>
                                @for($i = 0; $i < 12; $i++)
                                    @php
                                    $month=\Carbon\Carbon::now()->subMonths($i);
                                    $monthValue = $month->format('Y-m');
                                    $monthLabel = $month->format('F Y');
                                    @endphp
                                    <option value="{{ $monthValue }}" {{ request('vital_month') == $monthValue ? 'selected' : '' }}>
                                        {{ $monthLabel }}
                                    </option>
                                    @endfor
                            </select>
                        </form>
                    </div>
                </div>
                <div class="card-body py-3">
                    @php
                    use App\Models\RequestDocuments;
                    $vitalQuery = RequestDocuments::where('user_id', Auth::user()->id)
                    ->whereNotNull('vital_signs_bp');

                    // Apply month filter if selected
                    if(request('vital_month')) {
                    $vitalQuery->whereYear('created_at', substr(request('vital_month'), 0, 4))
                    ->whereMonth('created_at', substr(request('vital_month'), 5, 2));
                    }

                    $recentVitalSigns = $vitalQuery->latest('created_at')
                    ->paginate(5, ['*'], 'vital_signs_page')
                    ->appends(['vital_month' => request('vital_month')]);
                    @endphp

                    @if($recentVitalSigns->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th class="min-w-100px">{{ __('messages.common.date') }}</th>
                                    <th class="min-w-80px">BP</th>
                                    <th class="min-w-60px">PR</th>
                                    <th class="min-w-60px">Temp</th>
                                    <th class="min-w-60px">RR</th>
                                    <th class="min-w-60px">O2 Sat</th>
                                    <th class="min-w-80px">Ht/Wt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentVitalSigns as $vital)
                                <tr>
                                    <td>
                                        <span class="text-dark fw-bold fs-7">
                                            {{ \Carbon\Carbon::parse($vital->created_at)->format('d M Y') }}
                                        </span>
                                        <br>
                                        <span class="text-muted fs-8">
                                            {{ \Carbon\Carbon::parse($vital->created_at)->format('h:i A') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-semibold">
                                            {{ $vital->vital_signs_bp ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-semibold">
                                            {{ $vital->vital_signs_pr ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-semibold">
                                            {{ $vital->vital_signs_temp ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-semibold">
                                            {{ $vital->vital_signs_rr ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-semibold">
                                            {{ $vital->vital_signs_o2_sat ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-semibold fs-8">
                                            @if($vital->vital_signs_height || $vital->vital_signs_weight)
                                            {{ $vital->vital_signs_height ?? 'N/A' }} / {{ $vital->vital_signs_weight ?? 'N/A' }}
                                            @else
                                            N/A
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($recentVitalSigns->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $recentVitalSigns->links() }}
                    </div>
                    @endif
                    @else
                    <div class="text-center py-10">
                        <i class="fas fa-heartbeat fs-3x text-muted mb-3"></i>
                        <p class="text-muted fw-bold">No vital signs recorded yet</p>
                        <p class="text-muted fs-7">Your vital signs will appear here after your consultation</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Appointments Chart Section --}}
    <div class="row g-5 g-xl-8 mt-5">
        <div class="col-12">
            <div class="card card-xl-stretch mb-5 mb-xl-8 shadow-sm">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            {{ __('messages.patient_dashboard.appointment_overview') }}
                        </span>
                        <span class="text-muted mt-1 fw-semibold fs-7">{{ __('messages.patient_dashboard.yearly_appointments') }}</span>
                    </h3>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary"
                            data-bs-toggle="tooltip" title="{{ __('messages.common.view_details') }}">
                            <i class="fas fa-info-circle fs-2"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="patient_appointment_chart" style="min-height: 350px;"></div>
                    {{ Form::hidden('patient_chart_data', json_encode($patientAllAppointment, true), ['id' => 'patientChartData']) }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Check if user has default password
        var hasDefaultPassword = @json($hasDefaultPassword ?? false);

        if (hasDefaultPassword) {
            setTimeout(function() {
                var changePasswordBtn = document.getElementById('changePassword');

                if (changePasswordBtn) {
                    changePasswordBtn.click();
                } else {
                    // Try jQuery fallback
                    if (typeof $ !== 'undefined') {
                        var $btn = $('#changePassword');
                        if ($btn.length > 0) {
                            $btn.click();
                        }
                    }
                }
            }, 1500);
        }
    });
</script>

@push('scripts')
<script>
    $(document).ready(function() {
        // Check if user has default password and trigger header change password modal
        var hasDefaultPassword = @json($hasDefaultPassword ?? false);

        // Trigger the header's change password modal if user has default password
        if (hasDefaultPassword) {
            setTimeout(function() {
                var changePasswordBtn = $('#changePassword');
                if (changePasswordBtn.length > 0) {
                    changePasswordBtn.click();
                }
            }, 1000);
        }
    });
</script>
@endpush

@include('generate_patient_smart_cards/components/show_card')
@endsection