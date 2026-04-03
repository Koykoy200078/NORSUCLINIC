<?php $styleCss = 'style' ?>
<?php
// -----------------------------------------------------------------------
// Sidebar badge data — computed ONCE per request with caching.
// queue: 30s TTL (near-real-time).  medicine: 300s.  docs: 60s.
// -----------------------------------------------------------------------
$_menuQueueBadge = \Illuminate\Support\Facades\Cache::remember('menu_badge_queue', 30, function () {
    return \App\Models\PatientQueue::whereIn('status', ['waiting', 'in_progress'])
        ->selectRaw('COUNT(*) as total, SUM(is_priority) as priority, SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress')
        ->first();
});

$_menuMedicineBadge = \Illuminate\Support\Facades\Cache::remember('menu_badge_medicine', 300, function () {
    $today = \Carbon\Carbon::now();
    $criticalCount = \App\Models\PurchasedMedicine::whereNotNull('expiry_date')
        ->whereBetween('expiry_date', [$today, $today->copy()->addDays(7)])
        ->whereHas('medicines', fn($q) => $q->where('available_quantity', '>', 0))
        ->distinct('medicine_id')->count('medicine_id');
    $warningCount = \App\Models\PurchasedMedicine::whereNotNull('expiry_date')
        ->whereBetween('expiry_date', [$today->copy()->addDays(8), $today->copy()->addDays(30)])
        ->whereHas('medicines', fn($q) => $q->where('available_quantity', '>', 0))
        ->distinct('medicine_id')->count('medicine_id');
    $lowStockCount = \App\Models\Medicine::where('available_quantity', '>', 0)
        ->where(function ($q) {
            $q->whereRaw('minimum_stock_alert IS NOT NULL AND available_quantity <= minimum_stock_alert')
              ->orWhereRaw('stock_alert_percentage IS NOT NULL AND quantity > 0 AND (available_quantity / quantity * 100) <= stock_alert_percentage');
        })->count();
    return compact('criticalCount', 'warningCount', 'lowStockCount');
});

$_menuIncompleteDocsBadge = \Illuminate\Support\Facades\Cache::remember('menu_badge_incomplete_docs', 60, function () {
    return \App\Models\RequestDocuments::where('document_type', 'consultation_form')
        ->where(function ($q) {
            $q->whereNull('assessment')->orWhere('assessment', '')
              ->orWhereNull('plan')->orWhere('plan', '');
        })->count();
});
?>
<div class="no-record text-center d-none"><?php echo e(__('messages.no_matching_records_found')); ?></div>


<?php if(isRole('clinic_admin')): ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_admin_dashboard')): ?>
<li class="nav-item <?php echo e(Request::is('admin/dashboard*') ? 'active' : ''); ?>">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="<?php echo e(route('admin.dashboard')); ?>">
        <span class="aside-menu-icon pe-3"><i class="fas fa fa-digital-tachograph"></i></span>
        <span class="aside-menu-title"><?php echo e(__('messages.dashboard')); ?></span>
    </a>
</li>
<?php endif; ?>
<?php elseif(isRole('staff')): ?>

<li class="nav-item <?php echo e(Request::is('staff/dashboard*') ? 'active' : ''); ?>">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="<?php echo e(route('staff.dashboard')); ?>">
        <span class="aside-menu-icon pe-3"><i class="fas fa fa-digital-tachograph"></i></span>
        <span class="aside-menu-title"><?php echo e(__('messages.dashboard')); ?></span>
    </a>
</li>
<?php elseif(isRole('doctor')): ?>

<li class="nav-item <?php echo e(Request::is('doctors/dashboard*') ? 'active' : ''); ?>">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="<?php echo e(route('doctors.dashboard')); ?>">
        <span class="aside-menu-icon pe-3"><i class="fas fa fa-digital-tachograph"></i></span>
        <span class="aside-menu-title"><?php echo e(__('messages.dashboard')); ?></span>
    </a>
</li>
<?php elseif(isRole('patient')): ?>

<li class="nav-item <?php echo e(Request::is('patients/dashboard*') ? 'active' : ''); ?>">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="<?php echo e(route('patients.dashboard')); ?>">
        <span class="aside-menu-icon pe-3"><i class="fas fa fa-digital-tachograph"></i></span>
        <span class="aside-menu-title"><?php echo e(__('messages.dashboard')); ?></span>
    </a>
</li>
<?php endif; ?>


<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_staff')): ?>
<?php if(isRole('clinic_admin')): ?>
<li class="nav-item <?php echo e(Request::is('admin/staffs*') ? 'active' : ''); ?>">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="<?php echo e(route('staffs.index')); ?>">
        <span class="aside-menu-icon pe-3"><i class="fas fa-users"></i></span>
        <span class="aside-menu-title"><?php echo e(__('messages.staffs')); ?></span>
    </a>
</li>
<?php endif; ?>
<?php endif; ?>



<?php if(isRole('doctor')): ?>
<li class="nav-item <?php echo e(Request::is('doctors/patient-queue*') ? 'active' : ''); ?>">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="<?php echo e(route('doctors.patient-queue.index')); ?>">
        <span class="aside-menu-icon pe-3"><i class="fas fa-clipboard-list"></i></span>
        <span class="aside-menu-title">Patient Queue</span>
        <?php $doctorQueueData = $_menuQueueBadge; ?>
        <?php if($doctorQueueData && $doctorQueueData->total > 0): ?>
        <?php if($doctorQueueData->in_progress > 0): ?>
        <span class="badge bg-warning rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="Patient in progress">
            <i class="fas fa-user-clock"></i>
        </span>
        <?php elseif($doctorQueueData->priority > 0): ?>
        <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="<?php echo e($doctorQueueData->priority); ?> priority patient(s)"><?php echo e($doctorQueueData->priority); ?></span>
        <?php else: ?>
        <span class="badge bg-primary rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;"><?php echo e($doctorQueueData->total); ?></span>
        <?php endif; ?>
        <?php endif; ?>
    </a>
</li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_request_documents')): ?>
<?php if(isRole('doctor')): ?>
<li
    class="nav-item <?php echo e(Request::is('doctors/request-documents*') ? 'active' : ''); ?>">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
        href="<?php echo e(route('doctors.request-documents.index')); ?>">
        <span class="aside-menu-icon pe-3">
            <i class="fa-solid fa-file-signature"></i>
        </span>
        <span class="aside-menu-title">Patients Data</span>
        <?php $incompleteDocsCount = $_menuIncompleteDocsBadge; ?>
        <?php if($incompleteDocsCount > 0): ?>
        <span class="badge bg-warning text-dark rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="<?php echo e($incompleteDocsCount); ?> consultation form(s) need Assessment/Plan">
            <i class="fas fa-exclamation-triangle me-1" style="font-size: 0.6rem;"></i><?php echo e($incompleteDocsCount); ?>

        </span>
        <?php endif; ?>
    </a>
</li>
<?php endif; ?>
<?php endif; ?>




<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_doctors')): ?>
<li
    class="nav-item <?php echo e((isRole('clinic_admin') && Request::is('admin/doctors*')) ||
        (isRole('staff') && Request::is('staff/doctors*'))
    ? 'active' : ''); ?>">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="<?php echo e(isRole('clinic_admin') ? route('doctors.index') : 
        (isRole('staff') ? route('staff.doctors.index') : route('doctors.index'))); ?>">
        <span class="aside-menu-icon pe-3"><i class="fa-solid fa-user-doctor"></i></span>
        <span class="aside-menu-title"><?php echo e(__('messages.doctors')); ?></span>
    </a>
</li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_patients')): ?>
<li class="nav-item <?php echo e((isRole('clinic_admin') && Request::is('admin/patients*')) ||
    (isRole('staff') && Request::is('staff/patients*')) ||
    (isRole('doctor') && Request::is('doctors/patients*'))
? 'active' : ''); ?>">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="<?php echo e(isRole('clinic_admin') ? route('patients.index') : 
        (isRole('staff') ? route('staff.patients.index') : 
        (isRole('doctor') ? route('doctors.patients.index') : route('patients.index')))); ?>">
        <span class="aside-menu-icon pe-3"><i class="fas fa-hospital-user"></i></span>
        <span class="aside-menu-title"><?php echo e(__('messages.patients')); ?></span>
    </a>
</li>


<?php if(isRole('staff')): ?>
<li class="nav-item <?php echo e(Request::is('staff/patient-queue*') ? 'active' : ''); ?>">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="<?php echo e(route('staff.patient-queue.index')); ?>">
        <span class="aside-menu-icon pe-3"><i class="fas fa-users-line"></i></span>
        <span class="aside-menu-title">Patient Queue</span>
        <?php $queueData = $_menuQueueBadge; ?>
        <?php if($queueData && $queueData->total > 0): ?>
        <?php if($queueData->priority > 0): ?>
        <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="<?php echo e($queueData->priority); ?> priority patient(s)"><?php echo e($queueData->priority); ?></span>
        <?php else: ?>
        <span class="badge bg-primary rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;"><?php echo e($queueData->total); ?></span>
        <?php endif; ?>
        <?php endif; ?>
        <span class="d-none">Queue Management</span>
    </a>
</li>
<?php endif; ?>


<?php if(isRole('clinic_admin')): ?>
<li class="nav-item <?php echo e(Request::is('admin/patient-queue*') ? 'active' : ''); ?>">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="<?php echo e(route('patient-queue.index')); ?>">
        <span class="aside-menu-icon pe-3"><i class="fas fa-users-line"></i></span>
        <span class="aside-menu-title">Patient Queue</span>
        <?php $adminQueueData = $_menuQueueBadge; ?>
        <?php if($adminQueueData && $adminQueueData->total > 0): ?>
        <?php if($adminQueueData->priority > 0): ?>
        <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="<?php echo e($adminQueueData->priority); ?> priority patient(s)"><?php echo e($adminQueueData->priority); ?></span>
        <?php else: ?>
        <span class="badge bg-primary rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;"><?php echo e($adminQueueData->total); ?></span>
        <?php endif; ?>
        <?php endif; ?>
        <span class="d-none">Queue Monitoring</span>
    </a>
</li>
<?php endif; ?>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_medicines')): ?>
<li
    class="nav-item <?php echo e((isRole('clinic_admin') && Request::is('admin/categories*', 'admin/generics*', 'admin/medicines*', 'admin/medicine-availability*', 'admin/used-medicine*', 'admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*', 'staff/generics*', 'staff/medicines*', 'staff/medicine-availability*', 'staff/used-medicine*', 'staff/medicine-history*')) ||
        (isRole('doctor') && Request::is('doctors/categories*', 'doctors/generics*', 'doctors/medicines*', 'doctors/medicine-availability*', 'doctors/used-medicine*', 'doctors/medicine-history*'))
    ? 'active' : ''); ?>">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="<?php echo e(isRole('clinic_admin') ? route('categories.index') : 
        (isRole('staff') ? route('staff.categories.index') : 
        (isRole('doctor') ? route('doctors.categories.index') : route('categories.index')))); ?>">
        <span class="aside-menu-icon me-3"><i class="fas fa-capsules"></i></span>
        <span class="aside-menu-title"><?php echo e(__('messages.medicines')); ?></span>
        <?php
        $criticalCount = $_menuMedicineBadge['criticalCount'];
        $warningCount  = $_menuMedicineBadge['warningCount'];
        $lowStockCount = $_menuMedicineBadge['lowStockCount'];
        ?>

                <div class="d-flex align-items-center ms-auto gap-1">
                    <?php if($criticalCount > 0): ?>
                    <span class="badge bg-danger rounded-pill" style="font-size: 0.7rem; min-width: 20px;" title="Critical: <?php echo e($criticalCount); ?> medicine(s) expiring in 7 days or less">
                        <i class="fas fa-calendar-times me-1" style="font-size: 0.6rem;"></i><?php echo e($criticalCount); ?>

                    </span>
                    <?php elseif($warningCount > 0): ?>
                    <span class="badge bg-warning text-dark rounded-pill" style="font-size: 0.7rem; min-width: 20px;" title="Warning: <?php echo e($warningCount); ?> medicine(s) expiring within 30 days">
                        <i class="fas fa-calendar-exclamation me-1" style="font-size: 0.6rem;"></i><?php echo e($warningCount); ?>

                    </span>
                    <?php endif; ?>

                    <?php if($lowStockCount > 0): ?>
                    <span class="badge bg-warning text-dark rounded-pill" style="font-size: 0.7rem; min-width: 20px;" title="Low Stock: <?php echo e($lowStockCount); ?> medicine(s) below alert threshold">
                        <i class="fas fa-box-open me-1" style="font-size: 0.6rem;"></i><?php echo e($lowStockCount); ?>

                    </span>
                    <?php endif; ?>
                </div>
                <span class="d-none"><?php echo e(__('messages.medicine_categories')); ?></span>
                <span class="d-none"><?php echo e(__('messages.medicine_brands')); ?></span>
                <span class="d-none"><?php echo e(__('messages.medicines')); ?></span>
                <span class="d-none"><?php echo e(__('messages.medicine_availability.medicine_availabilities')); ?></span>
                <span class="d-none"><?php echo e(__('messages.used_medicine.used_medicines')); ?></span>
                <span class="d-none"><?php echo e(__('messages.medicine_bills.medicine_bills')); ?></span>
    </a>
</li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_specialties')): ?>
<li class="nav-item <?php echo e((isRole('clinic_admin') && Request::is('admin/specializations*')) ||
    (isRole('staff') && Request::is('staff/specializations*')) ||
    (isRole('doctor') && Request::is('doctors/specializations*'))
? 'active' : ''); ?>">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
        href="<?php echo e(isRole('clinic_admin') ? route('specializations.index') : 
            (isRole('staff') ? route('staff.specializations.index') : 
            (isRole('doctor') ? route('doctors.specializations.index') : route('specializations.index')))); ?>">
        <span class="aside-menu-icon pe-3"><i class="fas fa-user-shield"></i></span>
        <span class="aside-menu-title"><?php echo e(__('messages.specializations')); ?></span>
    </a>
</li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_front_cms')): ?>
<li
    class="nav-item <?php echo e((isRole('clinic_admin') && Request::is('admin/cms*', 'admin/sliders*', 'admin/front-medical-services*', 'admin/front-patient-testimonials*')) ||
        (isRole('staff') && Request::is('staff/cms*', 'staff/sliders*', 'staff/front-medical-services*', 'staff/front-patient-testimonials*'))
    ? 'active' : ''); ?>">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="<?php echo e(isRole('clinic_admin') ? route('cms.index') : 
        (isRole('staff') ? route('staff.cms.index') : route('cms.index'))); ?>">
        <span class="aside-menu-icon pe-3"><i class="fas fa-tasks"></i></span>
        <span class="aside-menu-title"><?php echo e(__('messages.front_cms')); ?></span>
        <span class="d-none"><?php echo e(__('messages.cms.cms')); ?></span>
        <span class="d-none"><?php echo e(__('messages.sliders')); ?></span>
    </a>
</li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_settings')): ?>
<li
    class="nav-item <?php echo e((isRole('clinic_admin') && Request::is('admin/settings*', 'admin/roles*', 'admin/countries*', 'admin/provinces*', 'admin/cities*')) ||
        (isRole('staff') && Request::is('staff/settings*', 'staff/roles*', 'staff/countries*', 'staff/provinces*', 'staff/cities*'))
    ? 'active' : ''); ?>">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="<?php echo e(isRole('clinic_admin') ? route('setting.index') : 
        (isRole('staff') ? route('staff.setting.index') : route('setting.index'))); ?>">
        <span class="aside-menu-icon pe-3"><i class="fas fa-cogs"></i></span>
        <span class="aside-menu-title"><?php echo e(__('messages.settings')); ?></span>
        <span class="d-none"><?php echo e(__('messages.settings')); ?></span>
        <span class="d-none"><?php echo e(__('messages.roles')); ?></span>
        <span class="d-none"><?php echo e(__('messages.countries')); ?></span>
        <span class="d-none"><?php echo e(__('messages.states')); ?></span>
        <span class="d-none"><?php echo e(__('messages.cities')); ?></span>
        
    </a>
</li>
<?php endif; ?>


<?php if(isRole('clinic_admin') || isRole('staff') || isRole('doctor')): ?>
<li class="nav-item <?php echo e((isRole('clinic_admin') && Request::is('admin/activity-logs*')) ||
    (isRole('staff') && Request::is('staff/activity-logs*')) ||
    (isRole('doctor') && Request::is('doctors/activity-logs*'))
? 'active' : ''); ?>">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="<?php echo e(isRole('clinic_admin') ? route('activity-logs.index') : 
        (isRole('staff') ? route('staff.activity-logs.index') : route('doctors.activity-logs.index'))); ?>">
        <span class="aside-menu-icon pe-3"><i class="fas fa-clipboard-list"></i></span>
        <span class="aside-menu-title">Activity Logs</span>
    </a>
</li>
<?php endif; ?><?php /**PATH C:\Projects\NORSUCLINIC\resources\views/layouts/menu.blade.php ENDPATH**/ ?>