
<?php $__env->startSection('title'); ?>
<?php echo e(__('messages.login')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex flex-column flex-column-fluid align-items-center justify-content-center p-4">
    <div class="col-12 text-center">
        <a href="<?php echo e(route('medical')); ?>" class="image mb-7 mb-sm-10">
            <img alt="Logo" src="<?php echo e(asset(getAppLogo())); ?>" class="img-fluid" style="width:90px;" loading="lazy">
        </a>
    </div>
    <div class="width-540">
        <?php echo $__env->make('flash::message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('layouts.errors', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
    <div class="bg-white rounded-15 shadow-md width-540 px-5 px-sm-7 py-10 mx-auto">
        <h1 class="text-center mb-7"><?php echo e(__('auth.sign_in')); ?></h1>
        <form method="POST" action="<?php echo e(route('login')); ?>">
            <?php echo csrf_field(); ?>
            <div class="mb-sm-7 mb-4">
                <label for="email" class="form-label">
                    <?php echo e(__('messages.patient.email').':'); ?><span class="required"></span>
                </label>
                <input name="email" type="email" class="form-control" id="email" aria-describedby="emailHelp" required placeholder="<?php echo e(__('messages.patient.enter_email')); ?>">
            </div>

            <div class="mb-sm-7 mb-4 position-relative">
                <div class="d-flex justify-content-between">
                    <label for="password" class="form-label"><?php echo e(__('messages.patient.password') .':'); ?><span
                            class="required"></span></label>
                    <?php if(Route::has('password.request')): ?>
                    <a href="<?php echo e(route('password.request')); ?>" class="link-info fs-6 text-decoration-none">
                        <?php echo e(__('messages.common.forgot_your_password').'?'); ?>

                    </a>
                    <?php endif; ?>
                </div>
                <input name="password" type="password" class="form-control" id="password" required placeholder="<?php echo e(__('messages.patient.enter_password')); ?>">
                <span class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 mt-7 me-4 input-icon input-password-hide cursor-pointer text-gray-600 change-type">
                    <i class="fas fa-eye-slash"></i>
                </span>
            </div>

            <div class="mb-sm-7 mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="remember_me">
                <label class="form-check-label" for="remember_me"><?php echo e(__('messages.common.remember_me')); ?></label>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary"><?php echo e(__('messages.login')); ?></button>
            </div>

            <!-- <div class="d-flex align-items-center mb-10 mt-4">
                    <span class="text-gray-700 me-2"><?php echo e(__('messages.web.new_here').'?'); ?></span>
                    <a href="<?php echo e(route('register')); ?>" class="link-info fs-6 text-decoration-none">
                        <?php echo e(__('messages.web.create_an_account')); ?>

                    </a>
                </div> -->
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Projects\NORSUCLINIC\resources\views/auth/login.blade.php ENDPATH**/ ?>