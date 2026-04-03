<header class="position-relative header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-1 col-4">
                <a href="#!" class="header-logo">
                    <img src="<?php echo e(asset(getAppLogo())); ?>" alt="NORSU LOGO" class="object-cover front-app-logo" loading="lazy" />
                </a>
            </div>
            <div class="col-lg-11 col-8">
                <nav class="navbar navbar-expand-lg navbar-light justify-content-end py-0">
                    <button class="navbar-toggler border-0 p-0" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                        <ul class="navbar-nav align-items-center py-2 py-lg-0">
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(Request::is('/*') ? 'active' : ''); ?>" aria-current="page" href="<?php echo e(url('/')); ?>"><?php echo e(__('messages.web.home')); ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(Request::is('medical-doctors*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('medicalDoctors')); ?>"><?php echo e(__('messages.web.our_team')); ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(Request::is('medical-services*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('medicalServices')); ?>"><?php echo e(__('messages.web.services')); ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(Request::is('medical-about-us*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('medicalAboutUs')); ?>"><?php echo e(__('messages.web.about_us')); ?></a>
                            </li>
                            <!-- <li class="nav-item">
                                <a class="nav-link <?php echo e(Request::is('medical-contact*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('medicalContact')); ?>"><?php echo e(__('messages.web.contact_us')); ?></a>
                            </li> -->
                        </ul>
                        <div class="text-lg-end header-btn-grp ms-xxl-5 ms-lg-3">
                            <?php if(getLogInUser()): ?>
                            <?php if(isRole('doctor')): ?>
                            <a href="<?php echo e(route('doctors.dashboard')); ?>"
                                class="btn btn-outline-primary me-xxl-3 me-2 mb-3 mb-lg-0"><?php echo e(__('messages.dashboard')); ?></a>
                            <?php elseif(isRole('staff')): ?>
                            <a href="<?php echo e(route('staff.dashboard')); ?>"
                                class="btn btn-outline-primary me-xxl-3 me-2 mb-3 mb-lg-0"><?php echo e(__('messages.dashboard')); ?></a>
                            <?php elseif(isRole('patient')): ?>
                            <a href="<?php echo e(route('patients.dashboard')); ?>"
                                class="btn btn-outline-primary me-xxl-3 me-2 mb-3 mb-lg-0"><?php echo e(__('messages.dashboard')); ?></a>
                            <?php else: ?>
                            <a href="<?php echo e(route('admin.dashboard')); ?>"
                                class="btn btn-outline-primary me-xxl-3 me-2 mb-3 mb-lg-0"><?php echo e(__('messages.dashboard')); ?></a>
                            <?php endif; ?>
                            <?php else: ?>
                            <a href="<?php echo e(route('login')); ?>"
                                class="btn btn-outline-primary me-xxl-3 me-2 mb-3 mb-lg-0"><?php echo e(__('messages.login')); ?></a>
                            <?php endif; ?>

                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</header><?php /**PATH C:\Projects\NORSUCLINIC\resources\views/fronts/layouts/header.blade.php ENDPATH**/ ?>