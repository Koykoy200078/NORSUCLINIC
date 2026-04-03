<div class="d-flex align-items-center">
    <a href="<?php echo e(route('staffs.show',$row->id)); ?>">
        <div class="image image-circle image-mini me-3">
            <img src="<?php echo e($row->profile_image); ?>" alt="user" class="user-img">
        </div>
    </a>
    <div class="d-flex flex-column">
        <a href="<?php echo e(route('staffs.show',$row->id)); ?>" class="mb-1 text-decoration-none fs-6">

            <?php echo e($row->full_name); ?>

        </a>
        <span class="fs-6"><?php echo e($row->email); ?></span>
    </div>
</div>
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/staffs/components/staff_name.blade.php ENDPATH**/ ?>