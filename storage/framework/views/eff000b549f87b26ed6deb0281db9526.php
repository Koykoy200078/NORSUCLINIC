
<?php $__env->startSection('title'); ?>
<?php echo e(__('messages.staff.add_staff')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <h1><?php echo e(__('messages.staff.add_staff')); ?></h1>
        <a class="btn btn-outline-primary float-end"
            href="<?php echo e(route('staffs.index')); ?>"><?php echo e(__('messages.common.back')); ?></a>
    </div>

    <div class="col-12">
        <?php echo $__env->make('layouts.errors', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
    <div class="card">
        <div class="card-body">
            <?php echo e(Form::open(['route' => ['staffs.store'], 'method' => 'POST', 'files' => true,'id'=> 'createStaffForm'])); ?>

            <?php echo $__env->make('staffs.fields', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo e(Form::close()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Projects\NORSUCLINIC\resources\views/staffs/create.blade.php ENDPATH**/ ?>