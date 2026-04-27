@php $dashboardUrl = getDashboardURL(); @endphp
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ Request::is('admin/dashboard*', 'staff/dashboard*', 'doctors/dashboard*', 'patients/dashboard*') ? '' : 'd-none' }}">
    <a class="nav-link p-0 {{ Request::is('admin/dashboard*', 'staff/dashboard*', 'doctors/dashboard*', 'patients/dashboard*') ? 'active' : '' }}"
        href="{{ url($dashboardUrl) }}">{{ __('messages.dashboard') }}</a>
</li>

{{-- ========================================================= --}}
{{-- [ORDER 2] Patient Record Management                        --}}
{{-- ========================================================= --}}
@can('manage_patients')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{
        !(
            (isRole('clinic_admin') && Request::is('admin/patients*')) ||
            (isRole('staff') && Request::is('staff/patients*')) ||
            (isRole('doctor') && Request::is('doctors/patients*'))
        ) ? 'd-none' : ''
    }}">
    <a class="nav-link p-0 {{
        (isRole('clinic_admin') && Request::is('admin/patients*')) ||
        (isRole('staff') && Request::is('staff/patients*')) ||
        (isRole('doctor') && Request::is('doctors/patients*'))
    ? 'active' : '' }}"
        href="{{ getRouteByRole('patients.index') }}">Patients</a>
</li>
@endcan

{{-- ========================================================= --}}
{{-- [ORDER 3] Patient Queue                                    --}}
{{-- ========================================================= --}}
@can('manage_patients')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{
        !(
            (isRole('clinic_admin') && Request::is('admin/patient-queue*')) ||
            (isRole('staff') && Request::is('staff/patient-queue*')) ||
            (isRole('doctor') && Request::is('doctors/patient-queue*'))
        ) ? 'd-none' : ''
    }}">
    <a class="nav-link p-0 {{
        (isRole('clinic_admin') && Request::is('admin/patient-queue*')) ||
        (isRole('staff') && Request::is('staff/patient-queue*')) ||
        (isRole('doctor') && Request::is('doctors/patient-queue*'))
    ? 'active' : '' }}"
        href="{{ getRouteByRole('patient-queue.index') }}">Queue</a>
</li>
@endcan

{{-- ========================================================= --}}
{{-- [ORDER 4] Consultation Management                          --}}
{{-- ========================================================= --}}
@can('manage_request_documents')
@if(isRole('clinic_admin') || isRole('staff') || isRole('doctor') || isRole('nurse'))
@php
$subMenuDocRoute = isRole('clinic_admin') ? route('document-issuances.index') :
    (isRole('staff') || isRole('nurse') ? route('staff.document-issuances.index') :
    route('doctors.document-issuances.index'));

$isConsultSubMenu =
    (isRole('clinic_admin') && Request::is('admin/document-issuances*') && (request()->query('module') === 'consultation' || !request()->query('module'))) ||
    ((isRole('staff') || isRole('nurse')) && Request::is('staff/document-issuances*') && (request()->query('module') === 'consultation' || !request()->query('module'))) ||
    (isRole('doctor') && Request::is('doctors/document-issuances*') && (request()->query('module') === 'consultation' || !request()->query('module')));
@endphp
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !$isConsultSubMenu ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ $isConsultSubMenu ? 'active' : '' }}"
        href="{{ $subMenuDocRoute . '?module=consultation' }}">Consultations</a>
</li>
@endif
@endcan

{{-- ========================================================= --}}
{{-- [ORDER 5] Prescription Management                          --}}
{{-- ========================================================= --}}
@can('manage_request_documents')
@if(isRole('clinic_admin') || isRole('staff') || isRole('doctor') || isRole('nurse'))
@php
$subMenuPrescRoute = isRole('clinic_admin') ? route('prescriptions.index') :
    (isRole('staff') || isRole('nurse') ? route('staff.prescriptions.index') :
    route('doctors.prescriptions.index'));

$isPrescSubMenu =
    (isRole('clinic_admin') && Request::is('admin/prescriptions*', 'admin/prescription-medicine*', 'admin/prescription-pdf*')) ||
    ((isRole('staff') || isRole('nurse')) && Request::is('staff/prescriptions*', 'staff/prescription-medicine*', 'staff/prescription-pdf*')) ||
    (isRole('doctor') && Request::is('doctors/prescriptions*', 'doctors/prescription-medicine*', 'doctors/prescription-pdf*'));
@endphp
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !$isPrescSubMenu ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ $isPrescSubMenu ? 'active' : '' }}"
        href="{{ $subMenuPrescRoute }}">Prescriptions</a>
</li>
@endif
@endcan

{{-- ========================================================= --}}
{{-- [ORDER 6 & 7] Medicine Inventory & Dispensing              --}}
{{-- ========================================================= --}}
@can('manage_medicines')
@if(isRole('clinic_admin') || isRole('staff') || isRole('nurse'))
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{
    !(
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/generics*','admin/medicines*','admin/stock-in*','admin/medicine-inventory-tracking*')) ||
        ((isRole('staff') || isRole('nurse')) && Request::is('staff/categories*','staff/generics*','staff/medicines*','staff/stock-in*','staff/medicine-inventory-tracking*'))
    ) ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{
        (isRole('clinic_admin') && Request::is('admin/medicines*','admin/categories*','admin/generics*','admin/stock-in*','admin/medicine-inventory-tracking*')) ||
        ((isRole('staff') || isRole('nurse')) && Request::is('staff/medicines*','staff/categories*','staff/generics*','staff/stock-in*','staff/medicine-inventory-tracking*'))
        ? 'active' : '' }}"
        href="{{ getRouteByRole('medicine-inventory.index') }}">
        Inventory
    </a>
</li>

<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{
    !(
        (isRole('clinic_admin') && Request::is('admin/used-medicine*','admin/medicine-history*','admin/dispense-records*','admin/medicine-dispensing-management*')) ||
        ((isRole('staff') || isRole('nurse')) && Request::is('staff/used-medicine*','staff/medicine-history*','staff/dispense-records*','staff/medicine-dispensing-management*'))
    ) ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{
        (isRole('clinic_admin') && Request::is('admin/used-medicine*','admin/medicine-history*','admin/dispense-records*','admin/medicine-dispensing-management*')) ||
        ((isRole('staff') || isRole('nurse')) && Request::is('staff/used-medicine*','staff/medicine-history*','staff/dispense-records*','staff/medicine-dispensing-management*'))
        ? 'active' : '' }}"
        href="{{ getRouteByRole('medicine-dispensing.index') }}">
        Dispensing
    </a>
</li>
@if(isRole('doctor'))
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('doctors/medicines*','doctors/categories*','doctors/generics*','doctors/stock-in*','doctors/medicine-inventory-tracking*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('doctors/medicines*','doctors/categories*','doctors/generics*','doctors/stock-in*','doctors/medicine-inventory-tracking*') ? 'active' : '' }}"
        href="{{ getRouteByRole('medicine-inventory.index') }}">
        Inventory
    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('doctors/used-medicine*','doctors/medicine-history*','doctors/dispense-records*','doctors/medicine-dispensing-management*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('doctors/used-medicine*','doctors/medicine-history*','doctors/dispense-records*','doctors/medicine-dispensing-management*') ? 'active' : '' }}"
        href="{{ getRouteByRole('medicine-dispensing.index') }}">
        Dispensing
    </a>
</li>
@endif
@endif
@endcan

{{-- ========================================================= --}}
{{-- [ORDER 8] Laboratory Requests                              --}}
{{-- ========================================================= --}}
@can('manage_request_documents')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{
        !(
            (isRole('clinic_admin') && Request::is('admin/lab-requests*')) ||
            (isRole('staff') && Request::is('staff/lab-requests*')) ||
            (isRole('doctor') && Request::is('doctors/lab-requests*'))
        ) ? 'd-none' : ''
    }}">
    <a class="nav-link p-0 {{
        (isRole('clinic_admin') && Request::is('admin/lab-requests*')) ||
        (isRole('staff') && Request::is('staff/lab-requests*')) ||
        (isRole('doctor') && Request::is('doctors/lab-requests*'))
    ? 'active' : '' }}"
        href="{{ getRouteByRole('lab-requests.index') }}">Lab Requests</a>
</li>
@endcan

{{-- ========================================================= --}}
{{-- [ORDER 9] Certificate Issuance                             --}}
{{-- ========================================================= --}}
@can('manage_request_documents')
@if(isRole('clinic_admin') || isRole('staff') || isRole('doctor'))
@php
$isCertSubMenu =
    (isRole('clinic_admin') && Request::is('admin/document-issuances*') && request()->query('module') === 'certificate') ||
    (isRole('staff') && Request::is('staff/document-issuances*') && request()->query('module') === 'certificate') ||
    (isRole('doctor') && Request::is('doctors/document-issuances*') && request()->query('module') === 'certificate');
@endphp
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !$isCertSubMenu ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ $isCertSubMenu ? 'active' : '' }}"
        href="{{ $subMenuDocRoute . '?module=certificate' }}">Certificates</a>
</li>
@endif
@endcan

{{-- ========================================================= --}}
{{-- [ORDER 10] Report Generation                               --}}
{{-- ========================================================= --}}
@if(isRole('clinic_admin') || isRole('staff') || isRole('doctor'))
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{
        !(
            (isRole('clinic_admin') && Request::is('admin/activity-logs*')) ||
            (isRole('staff') && Request::is('staff/activity-logs*')) ||
            (isRole('doctor') && Request::is('doctors/activity-logs*'))
        ) ? 'd-none' : ''
    }}">
    <a class="nav-link p-0 {{
        (isRole('clinic_admin') && Request::is('admin/activity-logs*')) ||
        (isRole('staff') && Request::is('staff/activity-logs*')) ||
        (isRole('doctor') && Request::is('doctors/activity-logs*'))
    ? 'active' : '' }}"
        href="{{ getRouteByRole('activity-logs.index') }}">Reports</a>
</li>
@endif

{{-- ========================================================= --}}
{{-- [ORDER 12] Settings & User Management                      --}}
{{-- ========================================================= --}}
@can('manage_staff')
@if(getLogInUser()->hasRole('clinic_admin'))
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{ !Request::is('admin/staffs*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/staffs*') ? 'active' : '' }}"
        href="{{ route('staffs.index') }}">{{ __('messages.staffs') }}</a>
</li>
@endif
@endcan

@can('manage_doctors')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{
        !(
            (isRole('clinic_admin') && Request::is('admin/doctors*')) ||
            (isRole('staff') && Request::is('staff/doctors*'))
        ) ? 'd-none' : ''
    }}">
    <a class="nav-link p-0 {{
        (isRole('clinic_admin') && Request::is('admin/doctors*')) ||
        (isRole('staff') && Request::is('staff/doctors*'))
    ? 'active' : '' }}"
        href="{{ getRouteByRole('doctors.index') }}">{{ __('messages.doctors') }}</a>
</li>
@endcan

@can('manage_settings')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{ !Request::is('admin/settings*','admin/roles*','admin/countries*','admin/states*','admin/cities*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/settings*') ? 'active' : '' }}"
        href="{{ route('setting.index') }}">{{ __('messages.settings') }}</a>
</li>
@endcan

@can('manage_roles')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{ !Request::is('admin/settings*','admin/roles*','admin/countries*','admin/states*','admin/cities*','admin/barangays*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/roles*') ? 'active' : '' }}"
        href="{{ route('roles.index') }}">{{ __('messages.roles') }}</a>
</li>
@endcan

@can('manage_countries')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{ !Request::is('admin/settings*','admin/roles*','admin/countries*','admin/states*','admin/cities*','admin/barangays*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/countries*') ? 'active' : '' }}"
        href="{{ route('countries.index') }}">{{ __('messages.countries') }}</a>
</li>
@endcan

@can('manage_states')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{ !Request::is('admin/settings*','admin/roles*','admin/countries*','admin/states*','admin/cities*','admin/barangays*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/states*') ? 'active' : '' }}"
        href="{{ route('states.index') }}">{{ __('messages.states') }}</a>
</li>
@endcan

@can('manage_cities')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{ !Request::is('admin/settings*','admin/roles*','admin/countries*','admin/states*','admin/cities*','admin/barangays*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/cities*') ? 'active' : '' }}"
        href="{{ route('cities.index') }}">{{ __('messages.cities') }}</a>
</li>
@endcan

@can('manage_cities')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{ !Request::is('admin/settings*','admin/roles*','admin/countries*','admin/states*','admin/cities*','admin/barangays*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/barangays*') ? 'active' : '' }}"
        href="{{ route('barangays.index') }}">{{ __('messages.barangays') }}</a>
</li>
@endcan

@can('manage_specialties')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{
    !(
        (isRole('clinic_admin') && Request::is('admin/specializations*')) ||
        (isRole('staff') && Request::is('staff/specializations*')) ||
        (isRole('doctor') && Request::is('doctors/specializations*'))
    ) ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{
        (isRole('clinic_admin') && Request::is('admin/specializations*')) ||
        (isRole('staff') && Request::is('staff/specializations*')) ||
        (isRole('doctor') && Request::is('doctors/specializations*'))
    ? 'active' : '' }}"
        href="{{ getRouteByRole('specializations.index') }}">{{ __('messages.specializations') }}</a>
</li>
@endcan

@can('manage_front_cms')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('admin/front-services*','admin/front-patient-testimonials*','admin/cms*','admin/banner*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/cms*') ? 'active' : '' }}"
        href="{{ route('cms.index') }}">{{ __('messages.cms.cms') }}</a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('admin/front-services*','admin/front-patient-testimonials*','admin/cms*','admin/banner*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/banner*') ? 'active' : '' }}"
        href="{{ route('banner.index') }}">{{ __('messages.sliders') }}</a>
</li>
@endcan

<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('profile/edit*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('profile/edit*') ? 'active' : '' }}"
        href="{{ route('profile.setting') }}">{{ __('messages.user.profile_details') }}</a>
</li>