@php
$dashboardUrl = getDashboardURL();
@endphp

{{-- [ORDER 1] Dashboard --}}
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !isModuleActive('dashboard') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ isModuleActive('dashboard') ? 'active' : '' }}"
        href="{{ $dashboardUrl }}">{{ __('messages.dashboard') }}</a>
</li>

{{-- [ORDER 2] Patients --}}
@can('manage_patients')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !isModuleActive('patients') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ isModuleActive('patients') ? 'active' : '' }}"
        href="{{ getRouteByRole('patients.index') }}">Patients</a>
</li>

{{-- [ORDER 3] Queue --}}
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !isModuleActive('patient-queue') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ isModuleActive('patient-queue') ? 'active' : '' }}"
        href="{{ getRouteByRole('patient-queue.index') }}">Queue</a>
</li>
@endcan

{{-- [ORDER 4] Consultations --}}
@can('manage_request_documents')
@php $documentIssuancesRouteName = getRouteNameByRole('document-issuances.index'); @endphp
@if(\Illuminate\Support\Facades\Route::has($documentIssuancesRouteName))
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !isModuleActive('consultations') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ isModuleActive('consultations') ? 'active' : '' }}"
        href="{{ route($documentIssuancesRouteName) . '?module=consultation' }}">Consultations</a>
</li>
@endif

{{-- [ORDER 5] Prescriptions --}}
@php $prescriptionsRouteName = getRouteNameByRole('prescriptions.index'); @endphp
@if(\Illuminate\Support\Facades\Route::has($prescriptionsRouteName))
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !isModuleActive('prescriptions') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ isModuleActive('prescriptions') ? 'active' : '' }}"
        href="{{ route($prescriptionsRouteName) }}">Prescriptions</a>
</li>
@endif
@endcan

{{-- [ORDER 6 & 7] Inventory & Dispensing --}}
@can('manage_medicines')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !isModuleActive('inventory') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ isModuleActive('inventory') ? 'active' : '' }}"
        href="{{ getRouteByRole('medicine-inventory.index') }}">Inventory</a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !isModuleActive('dispensing') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ isModuleActive('dispensing') ? 'active' : '' }}"
        href="{{ getRouteByRole('medicine-dispensing.index') }}">Dispensing</a>
</li>
@endcan

{{-- [ORDER 8] Laboratory Requests --}}
@can('manage_request_documents')
@php $labRequestsRouteName = getRouteNameByRole('lab-requests.index'); @endphp
@if(\Illuminate\Support\Facades\Route::has($labRequestsRouteName))
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !isModuleActive('lab-requests') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ isModuleActive('lab-requests') ? 'active' : '' }}"
        href="{{ route($labRequestsRouteName) }}">Lab Requests</a>
</li>
@endif
@endcan

{{-- [ORDER 9] Certificate Issuance --}}
@can('manage_request_documents')
@if(\Illuminate\Support\Facades\Route::has($documentIssuancesRouteName))
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !isModuleActive('certificates') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ isModuleActive('certificates') ? 'active' : '' }}"
        href="{{ route($documentIssuancesRouteName) . '?module=certificate' }}">Certificates</a>
</li>
@endif
@endcan

{{-- [ORDER 10] Reports --}}
@if(isRole('clinic_admin') || isRole('staff') || isRole('doctor'))
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !isModuleActive('reports') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ isModuleActive('reports') ? 'active' : '' }}"
        href="{{ getRouteByRole('activity-logs.index') }}">Reports</a>
</li>
@endif

{{-- [ORDER 12] Settings & User Management --}}
@can('manage_staff')
@if(isRole('clinic_admin'))
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('*/staffs*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('*/staffs*') ? 'active' : '' }}"
        href="{{ getRouteByRole('staffs.index') }}">{{ __('messages.staffs') }}</a>
</li>
@endif
@endcan

@can('manage_doctors')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !isModuleActive('doctors') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ isModuleActive('doctors') ? 'active' : '' }}"
        href="{{ getRouteByRole('doctors.index') }}">{{ __('messages.doctors') }}</a>
</li>
@endcan

@can('manage_settings')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('*/settings*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('*/settings*') ? 'active' : '' }}"
        href="{{ getRouteByRole('setting.index') }}">{{ __('messages.settings') }}</a>
</li>
@endcan

@can('manage_roles')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('*/roles*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('*/roles*') ? 'active' : '' }}"
        href="{{ getRouteByRole('roles.index') }}">{{ __('messages.roles') }}</a>
</li>
@endcan

@can('manage_countries')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('*/countries*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('*/countries*') ? 'active' : '' }}"
        href="{{ getRouteByRole('countries.index') }}">{{ __('messages.countries') }}</a>
</li>
@endcan

@can('manage_states')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('*/states*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('*/states*') ? 'active' : '' }}"
        href="{{ getRouteByRole('states.index') }}">{{ __('messages.states') }}</a>
</li>
@endcan

@can('manage_cities')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('*/cities*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('*/cities*') ? 'active' : '' }}"
        href="{{ getRouteByRole('cities.index') }}">{{ __('messages.cities') }}</a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('*/barangays*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('*/barangays*') ? 'active' : '' }}"
        href="{{ getRouteByRole('barangays.index') }}">{{ __('messages.barangays') }}</a>
</li>
@endcan

@can('manage_specialties')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !isModuleActive('specializations') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ isModuleActive('specializations') ? 'active' : '' }}"
        href="{{ getRouteByRole('specializations.index') }}">{{ __('messages.specializations') }}</a>
</li>
@endcan

@can('manage_front_cms')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('*/cms*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('*/cms*') ? 'active' : '' }}"
        href="{{ getRouteByRole('cms.index') }}">{{ __('messages.cms.cms') }}</a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('*/banner*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('*/banner*') ? 'active' : '' }}"
        href="{{ getRouteByRole('banner.index') }}">{{ __('messages.sliders') }}</a>
</li>
@endcan

<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('profile/edit*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('profile/edit*') ? 'active' : '' }}"
        href="{{ route('profile.setting') }}">{{ __('messages.user.profile_details') }}</a>
</li>