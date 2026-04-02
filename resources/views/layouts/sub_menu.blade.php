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
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/generics*','admin/medicines*','admin/medicine-availability*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/generics*','staff/medicines*','staff/medicine-availability*','staff/used-medicine*','staff/medicine-history*'))
    ) ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ 
        (isRole('clinic_admin') && Request::is('admin/categories*')) ||
        (isRole('staff') && Request::is('staff/categories*'))
    ? 'active' : '' }}"
        href="{{ 
            isRole('clinic_admin') ? route('categories.index') : 
            (isRole('staff') ? route('staff.categories.index') : route('categories.index'))
        }}">
        {{ __('messages.medicine_categories') }}
    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ 
    !(
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/generics*','admin/medicines*','admin/medicine-availability*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/generics*','staff/medicines*','staff/medicine-availability*','staff/used-medicine*','staff/medicine-history*'))
    ) ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ 
        (isRole('clinic_admin') && Request::is('admin/generics*')) ||
        (isRole('staff') && Request::is('staff/generics*'))
    ? 'active' : '' }}"
        href="{{ 
            isRole('clinic_admin') ? route('generics.index') : 
            (isRole('staff') ? route('staff.generics.index') : route('generics.index'))
        }}">
        {{ __('messages.medicine_generics') }}
    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ 
    !(
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/generics*','admin/medicines*','admin/medicine-availability*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/generics*','staff/medicines*','staff/medicine-availability*','staff/used-medicine*','staff/medicine-history*'))
    ) ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ 
        (isRole('clinic_admin') && Request::is('admin/medicines*')) ||
        (isRole('staff') && Request::is('staff/medicines*'))
    ? 'active' : '' }}"
        href="{{ 
            isRole('clinic_admin') ? route('medicines.index') : 
            (isRole('staff') ? route('staff.medicines.index') : route('medicines.index'))
        }}">
        {{ __('messages.medicines') }}
    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ 
    !(
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/generics*','admin/medicines*','admin/medicine-availability*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/generics*','staff/medicines*','staff/medicine-availability*','staff/used-medicine*','staff/medicine-history*'))
    ) ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ 
        (isRole('clinic_admin') && Request::is('admin/medicine-availability*')) ||
        (isRole('staff') && Request::is('staff/medicine-availability*'))
    ? 'active' : '' }}"
        href="{{ 
            isRole('clinic_admin') ? route('medicine-availability.index') : 
            (isRole('staff') ? route('staff.medicine-availability.index') : route('medicine-availability.index'))
        }}">
        {{ __('messages.medicine_availability.medicine_availabilities') }}
    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ 
    !(
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/generics*','admin/medicines*','admin/medicine-availability*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/generics*','staff/medicines*','staff/medicine-availability*','staff/used-medicine*','staff/medicine-history*'))
    ) ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ 
        (isRole('clinic_admin') && Request::is('admin/used-medicine*')) ||
        (isRole('staff') && Request::is('staff/used-medicine*'))
    ? 'active' : '' }}"
        href="{{ 
            isRole('clinic_admin') ? route('used-medicine.index') : 
            (isRole('staff') ? route('staff.used-medicine.index') : route('used-medicine.index'))
        }}">
        {{ __('messages.used_medicine.used_medicines') }}
    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ 
    !(
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/generics*','admin/medicines*','admin/medicine-availability*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/generics*','staff/medicines*','staff/medicine-availability*','staff/used-medicine*','staff/medicine-history*'))
    ) ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ 
        (isRole('clinic_admin') && Request::is('admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/medicine-history*'))
    ? 'active' : '' }}"
        href="{{ 
            isRole('clinic_admin') ? route('medicine-history.index') : 
            (isRole('staff') ? route('staff.medicine-history.index') : route('medicine-history.index'))
        }}">
        {{ __('messages.medicine_bills.medicine_bills') }}
    </a>
</li>
@elseif(isRole('doctor'))
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('doctors/categories*','doctors/generics*','doctors/medicines*','doctors/medicine-history*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('doctors/categories*') ? 'active' : '' }}"
        href="{{ route('doctors.categories.index') }}">
        {{ __('messages.medicine_categories') }}
    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('doctors/categories*','doctors/generics*','doctors/medicines*','doctors/medicine-history*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('doctors/generics*') ? 'active' : '' }}"
        href="{{ route('doctors.generics.index') }}">
        {{ __('messages.medicine_generics') }}
    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('doctors/categories*','doctors/generics*','doctors/medicines*','doctors/medicine-history*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('doctors/medicines*') ? 'active' : '' }}"
        href="{{ route('doctors.medicines.index') }}">
        {{ __('messages.medicines') }}
    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('doctors/categories*','doctors/generics*','doctors/medicines*','doctors/medicine-history*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('doctors/medicine-history*') ? 'active' : '' }}"
        href="{{ route('doctors.medicine-history.index') }}">
        History
    </a>
</li>
@endif
@endcan