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
@if(getLogInUser()->hasRole('clinic_admin'))
<li class="nav-item {{ Request::is('admin/staffs*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('staffs.index') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-users"></i></span>
        <span class="aside-menu-title">{{ __('messages.staffs') }}</span>
    </a>
</li>
@endif
@endcan

@role('doctor')
@can('manage_appointments')
<li class="nav-item {{ Request::is('doctors/appointments*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('doctors.appointments') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-calendar-alt"></i></span>
        <span class="aside-menu-title">{{ __('messages.appointment.appointments') }}</span>
        @if(isRole('doctor') && auth()->user()->doctor)
        @php
        $bookedCount = \App\Models\Appointment::where('doctor_id', auth()->user()->doctor->id)
        ->where('status', \App\Models\Appointment::BOOKED)
        ->count();
        @endphp
        @if($bookedCount > 0)
        <span class="badge bg-warning rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;">{{ $bookedCount }}</span>
        @endif
        @endif
        <span class="d-none">{{ __('messages.appointments') }}</span>
        <span class="d-none">{{ __('messages.patients') }}</span>
    </a>
</li>
@endcan

@can('manage_request_documents')
<li
    class="nav-item {{ Request::is('doctors/request-documents*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
        href="{{ route('doctors.request-documents.index') }}">
        <span class="aside-menu-icon pe-3">
            <i class="fa-solid fa-file-signature"></i>
        </span>
        <span class="aside-menu-title">Request Documents</span>
    </a>
</li>
@endcan

@can('manage_transactions')
<li class="nav-item {{ Request::is('doctors/transactions*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('doctors.transactions') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-money-bill-wave"></i></span>
        <span class="aside-menu-title">{{ __('messages.transactions') }}</span>
    </a>
</li>
@endcan
<li
    class="nav-item {{ Request::is('doctors/doctor-schedule-edit*', 'doctors/doctor-sessions/create') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ getLoginDoctorSessionUrl() }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-calendar"></i></span>
        <span class="aside-menu-title">{{ __('messages.doctor_session.my_schedule') }}</span>
    </a>
</li>
<li class="nav-item {{ Request::is('doctors/holidays*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('doctors.holiday') }}">
        <span class="aside-menu-icon pe-3"><i class="fa-solid fa-calendar-xmark"></i></span>
        <span class="aside-menu-title">{{ __('messages.holiday.holiday') }}</span>
    </a>
</li>
@endrole
@role('patient')
<li
    class="nav-item {{ Request::is('patients/appointments*', 'patients/patient-appointments-calendar*', 'patients/doctors*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
        href="{{ route('patients.patient-appointments-index') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-calendar-alt"></i></span>
        <span class="aside-menu-title">{{ __('messages.appointment.appointments') }}</span>
    </a>
</li>

<li class="nav-item {{ Request::is('patients/transactions*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('patients.transactions') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-money-bill-wave"></i></span>
        <span class="aside-menu-title">{{ __('messages.transactions') }}</span>
    </a>
</li>

<!-- <li class="nav-item {{ Request::is('patients/reviews*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
        href="{{ route('patients.reviews.index') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-star"></i></span>
        <span class="aside-menu-title">{{ __('messages.reviews') }}</span>
    </a>
</li> -->

@can('manage_request_documents')
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
@endcan

<!-- <li class="nav-item {{ Request::is('patients/patient-visits*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
        href="{{ route('patients.patient.visits.index') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-procedures"></i></span>
        <span class="aside-menu-title">{{ __('messages.visits') }}</span>
    </a>
</li> -->

<!-- <li class="nav-item {{ Request::is('patients/live-consultation*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
        href="{{ route('patients.live-consultations.index') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-video"></i></span>
        <span class="aside-menu-title">{{ __('messages.live_consultations') }}</span>
    </a>
</li> -->
@endrole
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
@endcan
@if (!isRole('doctor') && !isRole('patient'))
@can('manage_appointments')
<li
    class="nav-item {{ 
        (isRole('clinic_admin') && Request::is('admin/appointments*', 'admin/admin-appointments-calendar*', 'admin/prescriptions*', 'admin/prescription-medicine-show*')) ||
        (isRole('staff') && Request::is('staff/appointments*', 'staff/admin-appointments-calendar*', 'staff/prescriptions*', 'staff/prescription-medicine-show*'))
    ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
        href="{{ 
            isRole('clinic_admin') ? route('appointments.index') : 
            (isRole('staff') ? route('staff.appointments.index') : route('appointments.index'))
        }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-calendar-alt"></i></span>
        <span class="aside-menu-title">{{ __('messages.appointments') }}</span>
    </a>
</li>
@endcan
@endif
@can('manage_medicines')
<li
    class="nav-item {{ 
        (isRole('clinic_admin') && Request::is('admin/categories*', 'admin/brands*', 'admin/medicines*', 'admin/medicine-purchase*', 'admin/used-medicine*', 'admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*', 'staff/brands*', 'staff/medicines*', 'staff/medicine-purchase*', 'staff/used-medicine*', 'staff/medicine-history*')) ||
        (isRole('doctor') && Request::is('doctors/categories*', 'doctors/brands*', 'doctors/medicines*', 'doctors/medicine-purchase*', 'doctors/used-medicine*', 'doctors/medicine-history*'))
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
        @endphp
        @if($criticalCount > 0)
        <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="Critical: {{ $criticalCount }} medicine(s) expiring in 7 days or less">{{ $criticalCount }}</span>
        @elseif($warningCount > 0)
        <span class="badge bg-warning text-dark rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="Warning: {{ $warningCount }} medicine(s) expiring within 30 days">{{ $warningCount }}</span>
        @endif
        <span class="d-none">{{ __('messages.medicine_categories') }}</span>
        <span class="d-none">{{ __('messages.medicine_brands') }}</span>
        <span class="d-none">{{ __('messages.medicines') }}</span>
        <span class="d-none">{{ __('messages.purchase_medicine.purchase_medicines') }}</span>
        <span class="d-none">{{ __('messages.used_medicine.used_medicines') }}</span>
        <span class="d-none">{{ __('messages.medicine_bills.medicine_bills') }}</span>
    </a>
</li>
@endcan
@if (!isRole('doctor') && !isRole('patient'))
@can('manage_transactions')
<li class="nav-item {{ 
    (isRole('clinic_admin') && Request::is('admin/transactions*')) ||
    (isRole('staff') && Request::is('staff/transactions*'))
? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ 
        isRole('clinic_admin') ? route('transactions') : 
        (isRole('staff') ? route('staff.transactions') : route('transactions'))
    }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-money-bill-wave"></i></span>
        <span class="aside-menu-title">{{ __('messages.transactions') }}</span>
    </a>
</li>
@endcan
@endif
@if (!isRole('doctor') && !isRole('patient'))
<!-- @can('manage_patient_visits')
<li class="nav-item {{ Request::is('admin/visits*') ? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('visits.index') }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-procedures"></i></span>
        <span class="aside-menu-title">{{ __('messages.visits') }}</span>
    </a>
</li>
@endcan -->
@endif
@can('manage_services')
<li class="nav-item {{ 
    (isRole('clinic_admin') && Request::is('admin/services*', 'admin/service-categories*')) ||
    (isRole('staff') && Request::is('staff/services*', 'staff/service-categories*')) ||
    (isRole('doctor') && Request::is('doctors/services*', 'doctors/service-categories*'))
? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ 
        isRole('clinic_admin') ? route('services.index') : 
        (isRole('staff') ? route('staff.services.index') : 
        (isRole('doctor') ? route('doctors.services.index') : route('services.index')))
    }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-user-cog"></i></span>
        <span class="aside-menu-title">{{ __('messages.services') }}</span>
        <span class="d-none">{{ __('messages.services') }}</span>
        <span class="d-none">{{ __('messages.service_categories') }}</span>
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
<li class="nav-item {{ 
    (isRole('clinic_admin') && Request::is('admin/enquiries*')) ||
    (isRole('staff') && Request::is('staff/enquiries*'))
? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ 
        isRole('clinic_admin') ? route('enquiries.index') : 
        (isRole('staff') ? route('staff.enquiries.index') : route('enquiries.index'))
    }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-question-circle"></i></span>
        <span class="aside-menu-title">{{ __('messages.enquiries') }}</span>
    </a>
</li>
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
        (isRole('clinic_admin') && Request::is('admin/settings*', 'admin/roles*', 'admin/currencies*', 'admin/clinic-schedules*', 'admin/countries*', 'admin/provinces*', 'admin/cities*')) ||
        (isRole('staff') && Request::is('staff/settings*', 'staff/roles*', 'staff/currencies*', 'staff/clinic-schedules*', 'staff/countries*', 'staff/provinces*', 'staff/cities*'))
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
        <span class="d-none">{{ __('messages.currencies') }}</span>
        <span class="d-none">{{ __('messages.countries') }}</span>
        <span class="d-none">{{ __('messages.states') }}</span>
        <span class="d-none">{{ __('messages.cities') }}</span>
        {{-- <span class="d-none">{{ __('messages.holiday.doctor_holiday') }}</span> --}}
    </a>
</li>
@endcan