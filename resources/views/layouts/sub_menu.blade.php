@can('manage_admin_dashboard')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('admin/dashboard*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/dashboard*') ? 'active' : '' }}"
        href="{{ route('admin.dashboard') }}">{{ __('messages.dashboard') }}</a>
</li>
@endcan

@can('manage_staff_dashboard')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('staff/dashboard*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('staff/dashboard*') ? 'active' : '' }}"
        href="{{ route('staff.dashboard') }}">{{ __('messages.dashboard') }}</a>
</li>
@endcan
@role('doctor')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('doctors/dashboard*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('doctors/dashboard*') ? 'active' : '' }}"
        href="{{ route('doctors.dashboard') }}">{{ __('messages.dashboard') }}</a>
</li>
@endrole
@role('patient')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('patients/dashboard*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('patients/dashboard*') ? 'active' : '' }}"
        href="{{ route('patients.dashboard') }}">{{ __('messages.dashboard') }}</a>
</li>
@endrole
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
        href="{{ 
            isRole('clinic_admin') ? route('doctors.index') : 
            (isRole('staff') ? route('staff.doctors.index') : route('doctors.index'))
        }}">{{ __('messages.doctors') }}</a>
</li>
@endcan
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
        href="{{ 
            isRole('clinic_admin') ? route('patients.index') : 
            (isRole('staff') ? route('staff.patients.index') : 
            (isRole('doctor') ? route('doctors.patients.index') : route('patients.index')))
        }}">{{ __('messages.patients') }}</a>
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
        href="{{ 
            isRole('clinic_admin') ? route('specializations.index') : 
            (isRole('staff') ? route('staff.specializations.index') : 
            (isRole('doctor') ? route('doctors.specializations.index') : route('specializations.index')))
        }}">{{ __('messages.specializations') }}</a>
</li>
@endcan

<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('profile/edit*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('profile/edit*') ? 'active' : '' }}"
        href="{{ route('profile.setting') }}">{{ __('messages.user.profile_details') }}</a>
</li>
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
@can('manage_medicines')
@if(isRole('clinic_admin') || isRole('staff'))
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ 
    !(
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/generics*','admin/medicines*','admin/stock-in*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/generics*','staff/medicines*','staff/stock-in*','staff/used-medicine*','staff/medicine-history*'))
    ) ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ 
        (isRole('clinic_admin') && Request::is('admin/medicines*','admin/categories*','admin/generics*','admin/stock-in*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/medicines*','staff/categories*','staff/generics*','staff/stock-in*','staff/medicine-history*'))
        ? 'active' : '' }}"
        href="{{ isRole('clinic_admin') ? route('medicines.index') : route('staff.medicines.index') }}">
        {{ __('messages.medicines') }}
    </a>
</li>
@elseif(isRole('doctor'))
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('doctors/medicines*','doctors/categories*','doctors/generics*','doctors/medicine-history*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('doctors/medicines*','doctors/categories*','doctors/generics*','doctors/medicine-history*') ? 'active' : '' }}"
        href="{{ route('doctors.medicines.index') }}">
        {{ __('messages.medicines') }}
    </a>
</li>
@endif
@endcan