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
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('doctors/appointments*','doctors/prescription-medicine-show*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('doctors/appointments*','doctors/prescription-medicine-show*') ? 'active' : '' }}"
        href="{{ route('doctors.appointments') }}">{{ __('messages.appointments') }}</a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('doctors/doctor-schedule-edit*','doctors/doctor-sessions/create*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('doctors/doctor-schedule-edit*','doctors/doctor-sessions/create*') ? 'active' : '' }}"
        href="{{ getLoginDoctorSessionUrl() }}">{{ __('messages.doctor_session.my_schedule') }}</a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('doctors/transactions*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('doctors/transactions*') ? 'active' : '' }}"
        href="{{ route('doctors.transactions') }}">{{ __('messages.transactions') }}</a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('doctors/holidays*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('doctors/holidays*') ? 'active' : '' }}"
        href="{{ route('doctors.holiday') }}">{{ __('messages.holiday.holiday') }}</a>
</li>
@endrole
@role('patient')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('patients/dashboard*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('patients/dashboard*') ? 'active' : '' }}"
        href="{{ route('patients.dashboard') }}">{{ __('messages.dashboard') }}</a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('patients/appointments*','patients/patient-appointments-calendar*','patients/prescription-medicine-show*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('patients/appointments*','patients/patient-appointments-calendar*','patients/prescription-medicine-show*') ? 'active' : '' }}"
        href="{{ route('patients.patient-appointments-index') }}">{{ __('messages.appointments') }}</a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('patients/transactions*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('patients/transactions*') ? 'active' : '' }}"
        href="{{ route('patients.transactions') }}">{{ __('messages.transactions') }}</a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('patients/reviews*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('patients/reviews*') ? 'active' : '' }}"
        href="{{ route('patients.reviews.index') }}">{{ __('messages.reviews') }}</a>
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
            (isRole('clinic_admin') && Request::is('admin/doctors*', 'admin/doctor-sessions*','admin/holidays*')) ||
            (isRole('staff') && Request::is('staff/doctors*', 'staff/doctor-sessions*','staff/holidays*'))
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
@can('manage_doctor_sessions')
<li
    class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{ 
        !(
            (isRole('clinic_admin') && Request::is('admin/doctors*', 'admin/doctor-sessions*','admin/holidays*')) ||
            (isRole('staff') && Request::is('staff/doctors*', 'staff/doctor-sessions*','staff/holidays*'))
        ) ? 'd-none' : '' 
    }}">
    <a class="nav-link p-0 {{ 
        (isRole('clinic_admin') && Request::is('admin/doctor-sessions*')) ||
        (isRole('staff') && Request::is('staff/doctor-sessions*'))
    ? 'active' : '' }}"
        href="{{ 
            isRole('clinic_admin') ? route('doctor-sessions.index') : 
            (isRole('staff') ? route('staff.doctor-sessions.index') : route('doctor-sessions.index'))
        }}">{{ getLogInUser()->hasRole('doctor') ? __('messages.doctor_session.my_schedule') : __('messages.doctor_sessions') }}</a>
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
    {{ !Request::is('admin/settings*','admin/roles*','admin/currencies*','admin/clinic-schedules*','admin/countries*','admin/states*','admin/cities*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/settings*') ? 'active' : '' }}"
        href="{{ route('setting.index') }}">{{ __('messages.settings') }}</a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{ !Request::is('admin/settings*','admin/roles*','admin/currencies*','admin/clinic-schedules*','admin/countries*','admin/states*','admin/cities*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/clinic-schedules*') ? 'active' : '' }}"
        href="{{ route('clinic-schedules.index') }}">{{ __('messages.clinic_schedules') }}</a>
</li>

@endcan
@can('manage_doctors_holiday')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{ !Request::is('admin/doctors*', 'admin/doctor-sessions*','admin/holidays*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/holidays*') ? 'active' : '' }}"
        href="{{ route('holidays.index') }}">{{ __('messages.holiday.doctor_holiday') }}</a>
</li>
@endcan
@can('manage_roles')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{ !Request::is('admin/settings*','admin/roles*','admin/currencies*','admin/clinic-schedules*','admin/countries*','admin/states*','admin/cities*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/roles*') ? 'active' : '' }}"
        href="{{ route('roles.index') }}">{{ __('messages.roles') }}</a>
</li>
@endcan
@can('manage_countries')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{ !Request::is('admin/settings*','admin/roles*','admin/currencies*','admin/clinic-schedules*','admin/countries*','admin/states*','admin/cities*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/countries*') ? 'active' : '' }}"
        href="{{ route('countries.index') }}">{{ __('messages.countries') }}</a>
</li>
@endcan

@can('manage_states')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{ !Request::is('admin/settings*','admin/roles*','admin/currencies*','admin/clinic-schedules*','admin/countries*','admin/states*','admin/cities*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/states*') ? 'active' : '' }}"
        href="{{ route('states.index') }}">{{ __('messages.states') }}</a>
</li>
@endcan

@can('manage_cities')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    {{ !Request::is('admin/settings*','admin/roles*','admin/currencies*','admin/clinic-schedules*','admin/countries*','admin/states*','admin/cities*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/cities*') ? 'active' : '' }}"
        href="{{ route('cities.index') }}">{{ __('messages.cities') }}</a>
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
@can('manage_services')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ 
    !(
        (isRole('clinic_admin') && Request::is('admin/services*','admin/service-categories*')) ||
        (isRole('staff') && Request::is('staff/services*','staff/service-categories*')) ||
        (isRole('doctor') && Request::is('doctors/services*','doctors/service-categories*'))
    ) ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ 
        (isRole('clinic_admin') && Request::is('admin/services*')) ||
        (isRole('staff') && Request::is('staff/services*')) ||
        (isRole('doctor') && Request::is('doctors/services*'))
    ? 'active' : '' }}"
        href="{{ 
            isRole('clinic_admin') ? route('services.index') : 
            (isRole('staff') ? route('staff.services.index') : 
            (isRole('doctor') ? route('doctors.services.index') : route('services.index')))
        }}">{{ __('messages.services') }}</a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ 
    !(
        (isRole('clinic_admin') && Request::is('admin/services*','admin/service-categories*')) ||
        (isRole('staff') && Request::is('staff/services*','staff/service-categories*')) ||
        (isRole('doctor') && Request::is('doctors/services*','doctors/service-categories*'))
    ) ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ 
        (isRole('clinic_admin') && Request::is('admin/service-categories*')) ||
        (isRole('staff') && Request::is('staff/service-categories*')) ||
        (isRole('doctor') && Request::is('doctors/service-categories*'))
    ? 'active' : '' }}"
        href="{{ 
            isRole('clinic_admin') ? route('service-categories.index') : 
            (isRole('staff') && route('staff.service-categories.index')) ||
            (isRole('doctor') && route('doctors.service-categories.index')) ||
            route('service-categories.index')
        }}">{{ __('messages.service_categories') }}</a>
</li>
@endcan
@can('manage_appointments')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('admin/appointments*','admin/admin-appointments-calendar*','admin/prescriptions*', 'admin/prescription-medicine-show*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/appointments*','admin/admin-appointments-calendar*','admin/prescriptions*', 'admin/prescription-medicine-show*') ? 'active' : '' }}"
        href="{{ route('appointments.index') }}">{{ __('messages.appointments') }}</a>
</li>
@endcan
<!-- @can('manage_patient_visits')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('admin/visits*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/visits*') ? 'active' : '' }}"
        href="{{ route('visits.index') }}">{{ __('messages.visits') }}</a>
</li>
@endcan -->
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
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('admin/enquiries*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/enquiries*') ? 'active' : '' }}"
        href="{{ route('enquiries.index') }}">{{ __('messages.enquiries') }}</a>
</li>
@endcan
@can('manage_transactions')
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('admin/transactions*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('admin/transactions*') ? 'active' : '' }}"
        href="{{ route('transactions') }}">{{ __('messages.transactions') }}</a>
</li>
@endcan
@can('manage_medicines')
@if(isRole('clinic_admin') || isRole('staff'))
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ 
    !(
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/brands*','admin/medicines*','admin/medicine-purchase*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/brands*','staff/medicines*','staff/medicine-purchase*','staff/used-medicine*','staff/medicine-history*'))
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
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/brands*','admin/medicines*','admin/medicine-purchase*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/brands*','staff/medicines*','staff/medicine-purchase*','staff/used-medicine*','staff/medicine-history*'))
    ) ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ 
        (isRole('clinic_admin') && Request::is('admin/brands*')) ||
        (isRole('staff') && Request::is('staff/brands*'))
    ? 'active' : '' }}"
        href="{{ 
            isRole('clinic_admin') ? route('brands.index') : 
            (isRole('staff') ? route('staff.brands.index') : route('brands.index'))
        }}">
        {{ __('messages.medicine_brands') }}
    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ 
    !(
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/brands*','admin/medicines*','admin/medicine-purchase*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/brands*','staff/medicines*','staff/medicine-purchase*','staff/used-medicine*','staff/medicine-history*'))
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
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/brands*','admin/medicines*','admin/medicine-purchase*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/brands*','staff/medicines*','staff/medicine-purchase*','staff/used-medicine*','staff/medicine-history*'))
    ) ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ 
        (isRole('clinic_admin') && Request::is('admin/medicine-purchase*')) ||
        (isRole('staff') && Request::is('staff/medicine-purchase*'))
    ? 'active' : '' }}"
        href="{{ 
            isRole('clinic_admin') ? route('medicine-purchase.index') : 
            (isRole('staff') ? route('staff.medicine-purchase.index') : route('medicine-purchase.index'))
        }}">
        {{ __('messages.purchase_medicine.purchase_medicines') }}
    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ 
    !(
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/brands*','admin/medicines*','admin/medicine-purchase*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/brands*','staff/medicines*','staff/medicine-purchase*','staff/used-medicine*','staff/medicine-history*'))
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
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/brands*','admin/medicines*','admin/medicine-purchase*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/brands*','staff/medicines*','staff/medicine-purchase*','staff/used-medicine*','staff/medicine-history*'))
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
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('doctors/categories*','doctors/brands*','doctors/medicines*','doctors/medicine-history*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('doctors/categories*') ? 'active' : '' }}"
        href="{{ route('doctors.categories.index') }}">
        {{ __('messages.medicine_categories') }}
    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('doctors/categories*','doctors/brands*','doctors/medicines*','doctors/medicine-history*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('doctors/brands*') ? 'active' : '' }}"
        href="{{ route('doctors.brands.index') }}">
        {{ __('messages.medicine_brands') }}
    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('doctors/categories*','doctors/brands*','doctors/medicines*','doctors/medicine-history*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('doctors/medicines*') ? 'active' : '' }}"
        href="{{ route('doctors.medicines.index') }}">
        {{ __('messages.medicines') }}
    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('doctors/categories*','doctors/brands*','doctors/medicines*','doctors/medicine-history*') ? 'd-none' : '' }}">
    <a class="nav-link p-0 {{ Request::is('doctors/medicine-history*') ? 'active' : '' }}"
        href="{{ route('doctors.medicine-history.index') }}">
        History
    </a>
</li>
@endif
@endcan