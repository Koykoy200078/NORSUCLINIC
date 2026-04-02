@php $styleCss = 'style' @endphp
<div class="no-record text-center d-none">{{ __('messages.no_matching_records_found') }}</div>

{{-- Dashboard Menu Items - Show appropriate dashboard based on user role --}}
@if(isRole('clinic_admin'))
{{-- Admin Dashboard --}}
@can('manage_admin_dashboard')
<li class="nav-item {{ Request::is('admin/dashboard*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('admin.dashboard') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa fa-digital-tachograph"></i></span>
        <span class="aside-menu-title">{{ __('messages.dashboard') }}</span>
    </a>
</li>
@endcan
@elseif(isRole('staff'))
{{-- Staff Dashboard - Always accessible for staff users --}}
<li class="nav-item {{ Request::is('staff/dashboard*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('staff.dashboard') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa fa-digital-tachograph"></i></span>
        <span class="aside-menu-title">{{ __('messages.dashboard') }}</span>
    </a>
</li>
@elseif(isRole('doctor'))
{{-- Doctor Dashboard - Only for doctor users --}}
<li class="nav-item {{ Request::is('doctors/dashboard*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('doctors.dashboard') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa fa-digital-tachograph"></i></span>
        <span class="aside-menu-title">{{ __('messages.dashboard') }}</span>
    </a>
</li>
@elseif(isRole('patient'))
{{-- Patient Dashboard - Only for patient users --}}
<li class="nav-item {{ Request::is('patients/dashboard*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('patients.dashboard') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa fa-digital-tachograph"></i></span>
        <span class="aside-menu-title">{{ __('messages.dashboard') }}</span>
    </a>
</li>
@endif


@can('manage_staff')
@if(isRole('clinic_admin'))
<li class="nav-item {{ Request::is('admin/staffs*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('staffs.index') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-users"></i></span>
        <span class="aside-menu-title">{{ __('messages.staffs') }}</span>
    </a>
</li>
@endif
@endcan


{{-- Patient Queue - For Doctors --}}
@if(isRole('doctor'))
<li class="nav-item {{ Request::is('doctors/patient-queue*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('doctors.patient-queue.index') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-clipboard-list"></i></span>
        <span class="aside-menu-title">Patient Queue</span>
        @php
        $doctorQueueData = \App\Models\PatientQueue::whereIn('status', ['waiting', 'in_progress'])
        ->selectRaw('COUNT(*) as total, SUM(is_priority) as priority, SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress')
        ->first();
        @endphp
        @if($doctorQueueData && $doctorQueueData->total > 0)
        @if($doctorQueueData->in_progress > 0)
        <span class="badge bg-warning rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="Patient in progress">
            <i class="fas fa-user-clock"></i>
        </span>
        @elseif($doctorQueueData->priority > 0)
        <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="{{ $doctorQueueData->priority }} priority patient(s)">{{ $doctorQueueData->priority }}</span>
        @else
        <span class="badge bg-primary rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;">{{ $doctorQueueData->total }}</span>
        @endif
        @endif
    </a>
</li>
@endif

@can('manage_request_documents')
@if(isRole('doctor'))
<li
    class="nav-item {{ Request::is('doctors/request-documents*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
        href="{{ route('doctors.request-documents.index') }}">
        <span class="aside-menu-icon pe-3">
            <i class="fa-solid fa-file-signature"></i>
        </span>
        <span class="aside-menu-title">Patients Data</span>
        @php
        // Count incomplete consultation forms (missing assessment or plan)
        $incompleteDocsCount = \App\Models\RequestDocuments::where('document_type', 'consultation_form')
        ->where(function($query) {
        $query->whereNull('assessment')
        ->orWhere('assessment', '')
        ->orWhereNull('plan')
        ->orWhere('plan', '');
        })
        ->count();
        @endphp
        @if($incompleteDocsCount > 0)
        <span class="badge bg-warning text-dark rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="{{ $incompleteDocsCount }} consultation form(s) need Assessment/Plan">
            <i class="fas fa-exclamation-triangle me-1" style="font-size: 0.6rem;"></i>{{ $incompleteDocsCount }}
        </span>
        @endif
    </a>
</li>
@endif
@endcan

{{--
    <li
    class="nav-item {{ Request::is('doctors/doctor-schedule-edit*', 'doctors/doctor-sessions/create') ? 'active' : '' }}">
<a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ getLoginDoctorSessionUrl() }}">
    <span class="aside-menu-icon pe-3"><i class="fas fa-calendar"></i></span>
    <span class="aside-menu-title">{{ __('messages.doctor_session.my_schedule') }}</span>
</a>
</li>
--}}

{{--
    <li class="nav-item {{ Request::is('doctors/holidays*') ? 'active' : '' }}">
<a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('doctors.holiday') }}">
    <span class="aside-menu-icon pe-3"><i class="fa-solid fa-calendar-xmark"></i></span>
    <span class="aside-menu-title">{{ __('messages.holiday.holiday') }}</span>
</a>
</li>
--}}

{{-- Request Documents temporarily disabled for patients - route not implemented
@can('manage_request_documents')
@if(isRole('patient'))
<li
    class="nav-item {{ Request::is('patients/request-documents*') ? 'active' : '' }}">
<a class="nav-link d-flex align-items-center py-4" aria-current="page"
    href="{{ route('patients.request-documents.index') }}">
    <span class="aside-menu-icon pe-3">
        <i class="fa-solid fa-file-signature"></i>
    </span>
    <span class="aside-menu-title">Request Documents</span>
</a>
</li>
@endif
@endcan
--}}

{{-- Live Consultations temporarily disabled for patients - route not implemented
@can('manage_live_consultations')
@if(isRole('patient'))
<li class="nav-item {{ Request::is('patients/live-consultation*') ? 'active' : '' }}">
<a class="nav-link d-flex align-items-center py-4" aria-current="page"
    href="{{ route('patients.live-consultations.index') }}">
    <span class="aside-menu-icon pe-3"><i class="fas fa-video"></i></span>
    <span class="aside-menu-title">{{ __('messages.live_consultations') }}</span>
</a>
</li>
@endif
@endcan
--}}
@can('manage_doctors')
<li
    class="nav-item {{ 
        (isRole('clinic_admin') && Request::is('admin/doctors*', 'admin/doctor-sessions*', 'admin/holiday*')) ||
        (isRole('staff') && Request::is('staff/doctors*', 'staff/doctor-sessions*', 'staff/holiday*')) ||
        Request::is('doctors/doctor-sessions*')
    ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ 
        isRole('clinic_admin') ? route('doctors.index') : 
        (isRole('staff') ? route('staff.doctors.index') : route('doctors.index'))
    }}">
        <span class="aside-menu-icon pe-3"><i class="fa-solid fa-user-doctor"></i></span>
        <span class="aside-menu-title">{{ __('messages.doctors') }}</span>
        <span class="d-none">{{ __('messages.doctors') }}</span>
        <span class="d-none">{{ __('messages.doctor_sessions') }}</span>
    </a>
</li>
@endcan
@can('manage_patients')
<li class="nav-item {{ 
    (isRole('clinic_admin') && Request::is('admin/patients*')) ||
    (isRole('staff') && Request::is('staff/patients*')) ||
    (isRole('doctor') && Request::is('doctors/patients*'))
? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ 
        isRole('clinic_admin') ? route('patients.index') : 
        (isRole('staff') ? route('staff.patients.index') : 
        (isRole('doctor') ? route('doctors.patients.index') : route('patients.index')))
    }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-hospital-user"></i></span>
        <span class="aside-menu-title">{{ __('messages.patients') }}</span>
    </a>
</li>

{{-- Patient Queue Management - For Staff (with manage_patients permission) --}}
@if(isRole('staff'))
<li class="nav-item {{ Request::is('staff/patient-queue*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('staff.patient-queue.index') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-users-line"></i></span>
        <span class="aside-menu-title">Patient Queue</span>
        @php
        $queueData = \App\Models\PatientQueue::whereIn('status', ['waiting', 'in_progress'])
        ->selectRaw('COUNT(*) as total, SUM(is_priority) as priority')
        ->first();
        @endphp
        @if($queueData && $queueData->total > 0)
        @if($queueData->priority > 0)
        <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="{{ $queueData->priority }} priority patient(s)">{{ $queueData->priority }}</span>
        @else
        <span class="badge bg-primary rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;">{{ $queueData->total }}</span>
        @endif
        @endif
        <span class="d-none">Queue Management</span>
    </a>
</li>
@endif

{{-- Patient Queue - For Clinic Admin --}}
@if(isRole('clinic_admin'))
<li class="nav-item {{ Request::is('admin/patient-queue*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('patient-queue.index') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-users-line"></i></span>
        <span class="aside-menu-title">Patient Queue</span>
        @php
        $adminQueueData = \App\Models\PatientQueue::whereIn('status', ['waiting', 'in_progress'])
        ->selectRaw('COUNT(*) as total, SUM(is_priority) as priority')
        ->first();
        @endphp
        @if($adminQueueData && $adminQueueData->total > 0)
        @if($adminQueueData->priority > 0)
        <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="{{ $adminQueueData->priority }} priority patient(s)">{{ $adminQueueData->priority }}</span>
        @else
        <span class="badge bg-primary rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;">{{ $adminQueueData->total }}</span>
        @endif
        @endif
        <span class="d-none">Queue Monitoring</span>
    </a>
</li>
@endif
@endcan
@can('manage_medicines')
<li
    class="nav-item {{ 
        (isRole('clinic_admin') && Request::is('admin/categories*', 'admin/generics*', 'admin/medicines*', 'admin/medicine-availability*', 'admin/used-medicine*', 'admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*', 'staff/generics*', 'staff/medicines*', 'staff/medicine-availability*', 'staff/used-medicine*', 'staff/medicine-history*')) ||
        (isRole('doctor') && Request::is('doctors/categories*', 'doctors/generics*', 'doctors/medicines*', 'doctors/medicine-availability*', 'doctors/used-medicine*', 'doctors/medicine-history*'))
    ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ 
        isRole('clinic_admin') ? route('categories.index') : 
        (isRole('staff') ? route('staff.categories.index') : 
        (isRole('doctor') ? route('doctors.categories.index') : route('categories.index')))
    }}">
        <span class="aside-menu-icon me-3"><i class="fas fa-capsules"></i></span>
        <span class="aside-menu-title">{{ __('messages.medicines') }}</span>
        @php
        // Count medicines expiring within 7 days (Critical - Red badge)
        $sevenDaysFromNow = \Carbon\Carbon::now()->addDays(7);
        $today = \Carbon\Carbon::now();
        $criticalCount = \App\Models\PurchasedMedicine::whereNotNull('expiry_date')
        ->whereBetween('expiry_date', [$today, $sevenDaysFromNow])
        ->whereHas('medicines', function ($query) {
        $query->where('available_quantity', '>', 0);
        })
        ->distinct('medicine_id')
        ->count('medicine_id');

        // Count medicines expiring within 8-30 days (Warning - Yellow badge)
        $eightDaysFromNow = \Carbon\Carbon::now()->addDays(8);
        $oneMonthFromNow = \Carbon\Carbon::now()->addDays(30);
        $warningCount = \App\Models\PurchasedMedicine::whereNotNull('expiry_date')
        ->whereBetween('expiry_date', [$eightDaysFromNow, $oneMonthFromNow])
        ->whereHas('medicines', function ($query) {
        $query->where('available_quantity', '>', 0);
        })
        ->distinct('medicine_id')
        ->count('medicine_id');

        // Count medicines with low stock alerts (based on minimum_stock_alert or stock_alert_percentage)
        $lowStockCount = \App\Models\Medicine::where('available_quantity', '>', 0)
        ->where(function ($query) {
        // Check minimum stock alert
        $query->whereRaw('minimum_stock_alert IS NOT NULL AND available_quantity <= minimum_stock_alert')
            // OR check percentage alert
            ->orWhereRaw('stock_alert_percentage IS NOT NULL AND quantity > 0 AND (available_quantity / quantity * 100) <= stock_alert_percentage');
                })
                ->count();
                @endphp

                <div class="d-flex align-items-center ms-auto gap-1">
                    @if($criticalCount > 0)
                    <span class="badge bg-danger rounded-pill" style="font-size: 0.7rem; min-width: 20px;" title="Critical: {{ $criticalCount }} medicine(s) expiring in 7 days or less">
                        <i class="fas fa-calendar-times me-1" style="font-size: 0.6rem;"></i>{{ $criticalCount }}
                    </span>
                    @elseif($warningCount > 0)
                    <span class="badge bg-warning text-dark rounded-pill" style="font-size: 0.7rem; min-width: 20px;" title="Warning: {{ $warningCount }} medicine(s) expiring within 30 days">
                        <i class="fas fa-calendar-exclamation me-1" style="font-size: 0.6rem;"></i>{{ $warningCount }}
                    </span>
                    @endif

                    @if($lowStockCount > 0)
                    <span class="badge bg-warning text-dark rounded-pill" style="font-size: 0.7rem; min-width: 20px;" title="Low Stock: {{ $lowStockCount }} medicine(s) below alert threshold">
                        <i class="fas fa-box-open me-1" style="font-size: 0.6rem;"></i>{{ $lowStockCount }}
                    </span>
                    @endif
                </div>
                <span class="d-none">{{ __('messages.medicine_categories') }}</span>
                <span class="d-none">{{ __('messages.medicine_brands') }}</span>
                <span class="d-none">{{ __('messages.medicines') }}</span>
                <span class="d-none">{{ __('messages.medicine_availability.medicine_availabilities') }}</span>
                <span class="d-none">{{ __('messages.used_medicine.used_medicines') }}</span>
                <span class="d-none">{{ __('messages.medicine_bills.medicine_bills') }}</span>
    </a>
</li>
@endcan
@can('manage_specialties')
<li class="nav-item {{ 
    (isRole('clinic_admin') && Request::is('admin/specializations*')) ||
    (isRole('staff') && Request::is('staff/specializations*')) ||
    (isRole('doctor') && Request::is('doctors/specializations*'))
? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
        href="{{ 
            isRole('clinic_admin') ? route('specializations.index') : 
            (isRole('staff') ? route('staff.specializations.index') : 
            (isRole('doctor') ? route('doctors.specializations.index') : route('specializations.index')))
        }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-user-shield"></i></span>
        <span class="aside-menu-title">{{ __('messages.specializations') }}</span>
    </a>
</li>
@endcan
@can('manage_front_cms')
<li
    class="nav-item {{ 
        (isRole('clinic_admin') && Request::is('admin/cms*', 'admin/sliders*', 'admin/front-medical-services*', 'admin/front-patient-testimonials*')) ||
        (isRole('staff') && Request::is('staff/cms*', 'staff/sliders*', 'staff/front-medical-services*', 'staff/front-patient-testimonials*'))
    ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ 
        isRole('clinic_admin') ? route('cms.index') : 
        (isRole('staff') ? route('staff.cms.index') : route('cms.index'))
    }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-tasks"></i></span>
        <span class="aside-menu-title">{{ __('messages.front_cms') }}</span>
        <span class="d-none">{{ __('messages.cms.cms') }}</span>
        <span class="d-none">{{ __('messages.sliders') }}</span>
    </a>
</li>
@endcan
@can('manage_settings')
<li
    class="nav-item {{ 
        (isRole('clinic_admin') && Request::is('admin/settings*', 'admin/roles*', 'admin/clinic-schedules*', 'admin/countries*', 'admin/provinces*', 'admin/cities*')) ||
        (isRole('staff') && Request::is('staff/settings*', 'staff/roles*', 'staff/clinic-schedules*', 'staff/countries*', 'staff/provinces*', 'staff/cities*'))
    ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ 
        isRole('clinic_admin') ? route('setting.index') : 
        (isRole('staff') ? route('staff.setting.index') : route('setting.index'))
    }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-cogs"></i></span>
        <span class="aside-menu-title">{{ __('messages.settings') }}</span>
        <span class="d-none">{{ __('messages.settings') }}</span>
        <span class="d-none">{{ __('messages.clinic_schedules') }}</span>
        <span class="d-none">{{ __('messages.roles') }}</span>
        <span class="d-none">{{ __('messages.countries') }}</span>
        <span class="d-none">{{ __('messages.states') }}</span>
        <span class="d-none">{{ __('messages.cities') }}</span>
        {{-- <span class="d-none">{{ __('messages.holiday.doctor_holiday') }}</span> --}}
    </a>
</li>
@endcan

{{-- Activity Logs - For clinic_admin, staff, and doctor --}}
@if(isRole('clinic_admin') || isRole('staff') || isRole('doctor'))
<li class="nav-item {{ 
    (isRole('clinic_admin') && Request::is('admin/activity-logs*')) ||
    (isRole('staff') && Request::is('staff/activity-logs*')) ||
    (isRole('doctor') && Request::is('doctors/activity-logs*'))
? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ 
        isRole('clinic_admin') ? route('activity-logs.index') : 
        (isRole('staff') ? route('staff.activity-logs.index') : route('doctors.activity-logs.index'))
    }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-clipboard-list"></i></span>
        <span class="aside-menu-title">Activity Logs</span>
    </a>
</li>
@endif