<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600"><?php echo e(__('messages.staff.role')); ?></label>
    <span class="fs-4 text-gray-800"><?php echo e($staff->role_name); ?></span>
</div>
<div class="col-md-6 mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600"><?php echo e(__('messages.role.permissions')); ?></label>
    <br>
    <?php $__currentLoopData = $staff->getAllPermissions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <span class="badge my-1 me-1 bg-<?php echo e(getBadgeColor($loop->index)); ?>"><?php echo e($permission->display_name); ?></span>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600"><?php echo e(__('messages.user.gender')); ?></label>
    <span
        class="fs-4 text-gray-800"><?php echo e(($staff->gender == 1) ? __('messages.doctor.male') : __('messages.doctor.female')); ?></span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600"><?php echo e(__('messages.patient.registered_on')); ?></label>
    <span class="fs-4 text-gray-800"><?php echo e($staff->created_at->diffForHumans()); ?></span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600"><?php echo e(__('messages.patient.last_updated')); ?></label>
    <span class="fs-4 text-gray-800"><?php echo e($staff->updated_at->diffForHumans()); ?></span>
</div>
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/staffs/show_fields.blade.php ENDPATH**/ ?>