<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_admin_dashboard')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!Request::is('admin/dashboard*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('admin/dashboard*') ? 'active' : ''); ?>"
        href="<?php echo e(route('admin.dashboard')); ?>"><?php echo e(__('messages.dashboard')); ?></a>
</li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_staff_dashboard')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!Request::is('staff/dashboard*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('staff/dashboard*') ? 'active' : ''); ?>"
        href="<?php echo e(route('staff.dashboard')); ?>"><?php echo e(__('messages.dashboard')); ?></a>
</li>
<?php endif; ?>
<?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'doctor')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!Request::is('doctors/dashboard*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('doctors/dashboard*') ? 'active' : ''); ?>"
        href="<?php echo e(route('doctors.dashboard')); ?>"><?php echo e(__('messages.dashboard')); ?></a>
</li>
<?php endif; ?>
<?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'patient')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!Request::is('patients/dashboard*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('patients/dashboard*') ? 'active' : ''); ?>"
        href="<?php echo e(route('patients.dashboard')); ?>"><?php echo e(__('messages.dashboard')); ?></a>
</li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_staff')): ?>
<?php if(getLogInUser()->hasRole('clinic_admin')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    <?php echo e(!Request::is('admin/staffs*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('admin/staffs*') ? 'active' : ''); ?>"
        href="<?php echo e(route('staffs.index')); ?>"><?php echo e(__('messages.staffs')); ?></a>
</li>
<?php endif; ?>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_doctors')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    <?php echo e(!(
            (isRole('clinic_admin') && Request::is('admin/doctors*')) ||
            (isRole('staff') && Request::is('staff/doctors*'))
        ) ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e((isRole('clinic_admin') && Request::is('admin/doctors*')) ||
        (isRole('staff') && Request::is('staff/doctors*'))
    ? 'active' : ''); ?>"
        href="<?php echo e(isRole('clinic_admin') ? route('doctors.index') : 
            (isRole('staff') ? route('staff.doctors.index') : route('doctors.index'))); ?>"><?php echo e(__('messages.doctors')); ?></a>
</li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_patients')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    <?php echo e(!(
            (isRole('clinic_admin') && Request::is('admin/patients*')) ||
            (isRole('staff') && Request::is('staff/patients*')) ||
            (isRole('doctor') && Request::is('doctors/patients*'))
        ) ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e((isRole('clinic_admin') && Request::is('admin/patients*')) ||
        (isRole('staff') && Request::is('staff/patients*')) ||
        (isRole('doctor') && Request::is('doctors/patients*'))
    ? 'active' : ''); ?>"
        href="<?php echo e(isRole('clinic_admin') ? route('patients.index') : 
            (isRole('staff') ? route('staff.patients.index') : 
            (isRole('doctor') ? route('doctors.patients.index') : route('patients.index')))); ?>"><?php echo e(__('messages.patients')); ?></a>
</li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_settings')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    <?php echo e(!Request::is('admin/settings*','admin/roles*','admin/countries*','admin/states*','admin/cities*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('admin/settings*') ? 'active' : ''); ?>"
        href="<?php echo e(route('setting.index')); ?>"><?php echo e(__('messages.settings')); ?></a>
</li>

<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_roles')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    <?php echo e(!Request::is('admin/settings*','admin/roles*','admin/countries*','admin/states*','admin/cities*','admin/barangays*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('admin/roles*') ? 'active' : ''); ?>"
        href="<?php echo e(route('roles.index')); ?>"><?php echo e(__('messages.roles')); ?></a>
</li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_countries')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    <?php echo e(!Request::is('admin/settings*','admin/roles*','admin/countries*','admin/states*','admin/cities*','admin/barangays*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('admin/countries*') ? 'active' : ''); ?>"
        href="<?php echo e(route('countries.index')); ?>"><?php echo e(__('messages.countries')); ?></a>
</li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_states')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    <?php echo e(!Request::is('admin/settings*','admin/roles*','admin/countries*','admin/states*','admin/cities*','admin/barangays*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('admin/states*') ? 'active' : ''); ?>"
        href="<?php echo e(route('states.index')); ?>"><?php echo e(__('messages.states')); ?></a>
</li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_cities')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    <?php echo e(!Request::is('admin/settings*','admin/roles*','admin/countries*','admin/states*','admin/cities*','admin/barangays*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('admin/cities*') ? 'active' : ''); ?>"
        href="<?php echo e(route('cities.index')); ?>"><?php echo e(__('messages.cities')); ?></a>
</li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_cities')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0
    <?php echo e(!Request::is('admin/settings*','admin/roles*','admin/countries*','admin/states*','admin/cities*','admin/barangays*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('admin/barangays*') ? 'active' : ''); ?>"
        href="<?php echo e(route('barangays.index')); ?>"><?php echo e(__('messages.barangays')); ?></a>
</li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_specialties')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!(
        (isRole('clinic_admin') && Request::is('admin/specializations*')) ||
        (isRole('staff') && Request::is('staff/specializations*')) ||
        (isRole('doctor') && Request::is('doctors/specializations*'))
    ) ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e((isRole('clinic_admin') && Request::is('admin/specializations*')) ||
        (isRole('staff') && Request::is('staff/specializations*')) ||
        (isRole('doctor') && Request::is('doctors/specializations*'))
    ? 'active' : ''); ?>"
        href="<?php echo e(isRole('clinic_admin') ? route('specializations.index') : 
            (isRole('staff') ? route('staff.specializations.index') : 
            (isRole('doctor') ? route('doctors.specializations.index') : route('specializations.index')))); ?>"><?php echo e(__('messages.specializations')); ?></a>
</li>
<?php endif; ?>

<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!Request::is('profile/edit*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('profile/edit*') ? 'active' : ''); ?>"
        href="<?php echo e(route('profile.setting')); ?>"><?php echo e(__('messages.user.profile_details')); ?></a>
</li>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_front_cms')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!Request::is('admin/front-services*','admin/front-patient-testimonials*','admin/cms*','admin/banner*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('admin/cms*') ? 'active' : ''); ?>"
        href="<?php echo e(route('cms.index')); ?>"><?php echo e(__('messages.cms.cms')); ?></a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!Request::is('admin/front-services*','admin/front-patient-testimonials*','admin/cms*','admin/banner*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('admin/banner*') ? 'active' : ''); ?>"
        href="<?php echo e(route('banner.index')); ?>"><?php echo e(__('messages.sliders')); ?></a>
</li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_medicines')): ?>
<?php if(isRole('clinic_admin') || isRole('staff')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!(
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/generics*','admin/medicines*','admin/medicine-availability*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/generics*','staff/medicines*','staff/medicine-availability*','staff/used-medicine*','staff/medicine-history*'))
    ) ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e((isRole('clinic_admin') && Request::is('admin/categories*')) ||
        (isRole('staff') && Request::is('staff/categories*'))
    ? 'active' : ''); ?>"
        href="<?php echo e(isRole('clinic_admin') ? route('categories.index') : 
            (isRole('staff') ? route('staff.categories.index') : route('categories.index'))); ?>">
        <?php echo e(__('messages.medicine_categories')); ?>

    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!(
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/generics*','admin/medicines*','admin/medicine-availability*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/generics*','staff/medicines*','staff/medicine-availability*','staff/used-medicine*','staff/medicine-history*'))
    ) ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e((isRole('clinic_admin') && Request::is('admin/generics*')) ||
        (isRole('staff') && Request::is('staff/generics*'))
    ? 'active' : ''); ?>"
        href="<?php echo e(isRole('clinic_admin') ? route('generics.index') : 
            (isRole('staff') ? route('staff.generics.index') : route('generics.index'))); ?>">
        <?php echo e(__('messages.medicine_generics')); ?>

    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!(
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/generics*','admin/medicines*','admin/medicine-availability*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/generics*','staff/medicines*','staff/medicine-availability*','staff/used-medicine*','staff/medicine-history*'))
    ) ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e((isRole('clinic_admin') && Request::is('admin/medicines*')) ||
        (isRole('staff') && Request::is('staff/medicines*'))
    ? 'active' : ''); ?>"
        href="<?php echo e(isRole('clinic_admin') ? route('medicines.index') : 
            (isRole('staff') ? route('staff.medicines.index') : route('medicines.index'))); ?>">
        <?php echo e(__('messages.medicines')); ?>

    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!(
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/generics*','admin/medicines*','admin/medicine-availability*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/generics*','staff/medicines*','staff/medicine-availability*','staff/used-medicine*','staff/medicine-history*'))
    ) ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e((isRole('clinic_admin') && Request::is('admin/medicine-availability*')) ||
        (isRole('staff') && Request::is('staff/medicine-availability*'))
    ? 'active' : ''); ?>"
        href="<?php echo e(isRole('clinic_admin') ? route('medicine-availability.index') : 
            (isRole('staff') ? route('staff.medicine-availability.index') : route('medicine-availability.index'))); ?>">
        <?php echo e(__('messages.medicine_availability.medicine_availabilities')); ?>

    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!(
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/generics*','admin/medicines*','admin/medicine-availability*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/generics*','staff/medicines*','staff/medicine-availability*','staff/used-medicine*','staff/medicine-history*'))
    ) ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e((isRole('clinic_admin') && Request::is('admin/used-medicine*')) ||
        (isRole('staff') && Request::is('staff/used-medicine*'))
    ? 'active' : ''); ?>"
        href="<?php echo e(isRole('clinic_admin') ? route('used-medicine.index') : 
            (isRole('staff') ? route('staff.used-medicine.index') : route('used-medicine.index'))); ?>">
        <?php echo e(__('messages.used_medicine.used_medicines')); ?>

    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!(
        (isRole('clinic_admin') && Request::is('admin/categories*','admin/generics*','admin/medicines*','admin/medicine-availability*','admin/used-medicine*','admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*','staff/generics*','staff/medicines*','staff/medicine-availability*','staff/used-medicine*','staff/medicine-history*'))
    ) ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e((isRole('clinic_admin') && Request::is('admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/medicine-history*'))
    ? 'active' : ''); ?>"
        href="<?php echo e(isRole('clinic_admin') ? route('medicine-history.index') : 
            (isRole('staff') ? route('staff.medicine-history.index') : route('medicine-history.index'))); ?>">
        <?php echo e(__('messages.medicine_bills.medicine_bills')); ?>

    </a>
</li>
<?php elseif(isRole('doctor')): ?>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!Request::is('doctors/categories*','doctors/generics*','doctors/medicines*','doctors/medicine-history*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('doctors/categories*') ? 'active' : ''); ?>"
        href="<?php echo e(route('doctors.categories.index')); ?>">
        <?php echo e(__('messages.medicine_categories')); ?>

    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!Request::is('doctors/categories*','doctors/generics*','doctors/medicines*','doctors/medicine-history*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('doctors/generics*') ? 'active' : ''); ?>"
        href="<?php echo e(route('doctors.generics.index')); ?>">
        <?php echo e(__('messages.medicine_generics')); ?>

    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!Request::is('doctors/categories*','doctors/generics*','doctors/medicines*','doctors/medicine-history*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('doctors/medicines*') ? 'active' : ''); ?>"
        href="<?php echo e(route('doctors.medicines.index')); ?>">
        <?php echo e(__('messages.medicines')); ?>

    </a>
</li>
<li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 <?php echo e(!Request::is('doctors/categories*','doctors/generics*','doctors/medicines*','doctors/medicine-history*') ? 'd-none' : ''); ?>">
    <a class="nav-link p-0 <?php echo e(Request::is('doctors/medicine-history*') ? 'active' : ''); ?>"
        href="<?php echo e(route('doctors.medicine-history.index')); ?>">
        History
    </a>
</li>
<?php endif; ?>
<?php endif; ?><?php /**PATH C:\Projects\NORSUCLINIC\resources\views/layouts/sub_menu.blade.php ENDPATH**/ ?>