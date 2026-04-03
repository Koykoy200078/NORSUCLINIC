
<?php $__env->startSection('title'); ?>
<?php echo e(__('messages.staffs')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('header_toolbar'); ?>
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <h1 class="mb-0 me-1"><?php echo e(__('messages.staff.staff_details')); ?></h1>
        <div class="text-end mt-4 mt-md-0">
            <a href="<?php echo e(route('staffs.edit',$staff->id)); ?>">
                <button type="button" class="btn btn-primary me-4"><?php echo e(__('messages.common.edit')); ?></button>
            </a>
            <a href="<?php echo e(url()->previous()); ?>">
                <button type="button" class="btn btn-outline-primary float-end"><?php echo e(__('messages.common.back')); ?></button>
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex flex-column">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-xxl-5 col-12">
                        <div class="d-sm-flex align-items-center mb-5 mb-xxl-0 text-center text-sm-start">
                            <div class="image image-circle image-lg-small">
                                <img src="<?php echo e($staff->profile_image); ?>" alt="user">
                            </div>
                            <div class="ms-0 ms-md-10 mt-5 mt-sm-0  ">
                                <span class="text-success mb-2 d-block"><?php echo e($staff->role_name); ?></span>
                                <h2><?php echo e($staff->full_name); ?></h2>
                                <a href="mailto:<?php echo e($staff->email); ?>"
                                    class="text-gray-600 text-decoration-none fs-4">
                                    <?php echo e($staff->email); ?>

                                </a><br>
                                <a href="tel:<?php echo e($staff->country_code); ?> <?php echo e($staff->contact); ?>"
                                    class="text-gray-600 text-decoration-none fs-4">
                                    <?php echo e(!empty($staff->contact) ? '+'. $staff->country_code .' '. $staff->contact  : __('messages.common.n/a')); ?>

                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-7 overflow-hidden">
            <ul class="nav nav-tabs mb-sm-7 mb-5 pb-1 overflow-auto flex-nowrap text-nowrap" id="myTab" role="tablist">
                <li class="nav-item position-relative me-7 mb-3" role="presentation">
                    <button class="nav-link active p-0" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview"
                        type="button" role="tab" aria-controls="overview" aria-selected="true">
                        <?php echo e(__('messages.common.overview')); ?>

                    </button>
                </li>
            </ul>
            <div class="card">
                <div class="card-body">
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                            <div class="row">
                                <?php echo $__env->make('staffs.show_fields', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Projects\NORSUCLINIC\resources\views/staffs/show.blade.php ENDPATH**/ ?>