
<?php $__env->startSection('front-title'); ?>
<?php echo e(__('messages.web.medical')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('front-content'); ?>
<?php
$styleCss = 'style';
?>
<div class="home-page">
    <!-- start hero section -->
    <section class="hero-section p-t-100 p-b-100">
        <div class="container p-t-120">
            <div class="row align-items-center flex-column-reverse flex-lg-row">
                <div class="col-lg-6 text-lg-end text-center">
                    <div class="hero-content mt-5 mt-lg-0">
                        <p class="text-primary fs-5 fw-bold"><?php echo e($sliders->title); ?></p>
                        <h1 class="mb-5">
                            <?php echo e($sliders->short_description); ?>

                        </h1>
                        <!-- <?php if(!getLogInUser()): ?>
                        <a href="<?php echo e(route('register')); ?>"
                            class="btn btn-primary"><?php echo e(__('messages.web.sign_up')); ?></a>
                        <?php endif; ?> -->
                    </div>
                </div>
                <div class="col-lg-6 text-lg-end text-center">
                    <img src="<?php echo e($sliders->slider_image); ?>" alt="NORSU LOGO" class="img-fluid object-image-cover" loading="lazy" />
                </div>
            </div>
        </div>
    </section>
    <!-- end hero section -->

    <!-- start about section -->
    <!-- <section class="about-section p-b-100">
        <div class="container">
            <div class="row align-items-center flex-column-reverse flex-xl-row">
                <div class="col-xxl-6 col-xl-5 after-rectangle-shape position-relative about-left-content left-shape">
                    <div class="row position-relative z-index-1">
                        <div class="col-xl-6 col-md-3 about-block">
                            <div class="about-image-box rounded-20 bg-white">
                                <img src="<?php echo e(getSettingValue('about_image_2')); ?>" alt="About" class="rounded-20" loading="lazy" />
                            </div>
                        </div>
                        <div class="col-xl-6 col-md-3 about-block">
                            <div class="about-image-box rounded-20 bg-white">
                                <img src="<?php echo e(getSettingValue('about_image_1')); ?>" alt="About" class="rounded-20" loading="lazy" />
                            </div>
                        </div>
                        <div class="col-xl-6 col-md-3 about-block">
                            <div
                                class="about-content-box rounded-20 bg-white d-flex align-items-center justify-content-center h-100">
                                <div class="text-center">
                                    <h2 class="number-big text-primary"><?php echo e($aboutExperience->value); ?></h2>
                                    <p class="mb-0"><?php echo e(__('messages.web.year_experience')); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-md-3 about-block">
                            <div class="about-image-box bg-white rounded-20">
                                <img src="<?php echo e(getSettingValue('about_image_3')); ?>" alt="About" class="rounded-20" loading="lazy" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-6 col-xl-7">
                    <div class="about-right-content mb-md-5 mb-4 mb-xl-0 text-center text-xl-start">
                        <h5 class="text-primary top-heading fs-6 mb-3"><?php echo e(__('messages.web.about_us')); ?></h5>
                        <h2 class="pb-2"><?php echo e(getSettingValue('about_title')); ?></h2>
                        <p class="paragraph pb-1">
                            <?php echo e(getSettingValue('about_short_description')); ?>

                        </p>
                        <ul class="d-flex ps-0 mb-4 pb-2 justify-content-center justify-content-xl-start flex-wrap">
                            <li class="mb-2"><?php echo e(__('messages.web.emergency_help')); ?></li>
                            <li class="mb-2"><?php echo e(__('messages.web.qualified_doctors')); ?></li>
                            <li class="mb-2"><?php echo e(__('messages.web.best_professionals')); ?></li>
                            <li class="mb-2"><?php echo e(__('messages.web.medical_treatment')); ?></li>
                        </ul>
                        <a href="<?php echo e(route('medicalContact')); ?>"
                            class="btn btn-primary "><?php echo e(__('messages.web.contact_us')); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </section> -->
    <!-- end about section -->

    <!-- start how-it-work section -->
    <!-- <section class="how-work-section p-t-100 p-b-100">
        <div class="container">
            <div class="text-center mb-lg-5 mb-4">
                <h5 class="text-primary top-heading fs-6 mb-3"><?php echo e(__('messages.web.working_process')); ?></h5>
                <h2 class="pb-2"><?php echo e(__('messages.web.how_we_works')); ?>?</h2>
            </div>
            <div class="row justify-content-center">
                <div class="col-xl-4 col-md-6">
                    <div class="card mx-lg-2 h-100 text-md-start text-center">
                        <div class="card-body">
                            <h3 class="card-number mb-4 pb-3">
                                1
                            </h3>
                            <h4 class="card-title fs-5">
                                <?php echo e(__('messages.web.registration')); ?>

                            </h4>
                            <p class="paragraph mb-0">
                                <?php echo e(__('messages.web.patient_can_do_registration___')); ?>

                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mt-md-0 mt-4">
                    <div class="card mx-lg-2 h-100 text-md-start text-center">
                        <div class="card-body">
                            <h3 class="card-number mb-4 pb-3">
                                2
                            </h3>
                            <h4 class="card-title fs-5">
                                <?php echo e(__('messages.web.make_appointment')); ?>

                            </h4>
                            <p class="paragraph mb-0">
                                <?php echo e(__('messages.web.patient_can_book_an_appointment___')); ?>

                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mt-xl-0 mt-4 pt-xl-0 pt-lg-3">
                    <div class="card mx-lg-2 h-100 text-md-start text-center">
                        <div class="card-body">
                            <h3 class="card-number mb-4 pb-3">
                                3
                            </h3>
                            <h4 class="card-title fs-5">
                                <?php echo e(__('messages.web.take_treatment')); ?>

                            </h4>
                            <p class="paragraph mb-0">
                                <?php echo e(__('messages.web.doctors_can_interact___')); ?>

                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> -->
    <!-- end how-it-work section -->

    

    
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('fronts.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Projects\NORSUCLINIC\resources\views/fronts/medicals/index.blade.php ENDPATH**/ ?>