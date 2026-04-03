<header class='d-flex align-items-center justify-content-between flex-grow-1 header px-3 px-xl-0'>
    <button type="button" class="btn px-0 aside-menu-container__aside-menubar d-block d-xl-none sidebar-btn">
        <i class="fa-solid fa-bars fs-1"></i>
    </button>
    <nav class="navbar navbar-expand-xl navbar-light <?php echo e((Auth()->user()->dark_mode) ? 'bg-light' : 'bg-white'); ?> top-navbar d-xl-flex d-block px-3 px-xl-0 py-4 py-xl-0 "
        id="nav-header">
        <div class="container-fluid">
            <div class="navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php echo $__env->make('layouts.sub_menu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </ul>
            </div>
        </div>
    </nav>
    <ul class="nav align-items-center">
        <?php if (is_impersonating()) : ?>
        <li class="px-xxl-3 px-2">
            <span class="text-primary">
                <a data-turbo-eval="false" href="<?php echo e(route('impersonate.leave')); ?>">
                    <i class="fas fa-user-check fs-2"></i>
                </a>
            </span>
        </li>
        <?php endif; ?>
        
        <li class="px-sm-3 px-2">
            <?php if(Auth::user()->dark_mode): ?>
            <a href="javascript:void(0)" title="Switch to Light mode"><i
                    class="fa-solid fa-sun text-primary fs-2 apply-dark-mode"></i></a>
            <?php else: ?>
            <a href="javascript:void(0)" title="Switch to Dark mode"><i
                    class="fa-solid fa-moon text-primary fs-2 apply-dark-mode"></i></a>
            <?php endif; ?>
        </li>
        <?php
        $notifications = getNotification();
        ?>
        <?php if(getLogInUser()->hasRole('doctor') || getLogInUser()->hasRole('patient')): ?>
        <li class="px-sm-3 px-2">
            <div class="dropdown custom-dropdown d-flex align-items-center py-4">
                <button class="btn dropdown-toggle hide-arrow ps-2 pe-0" type="button" id="dropdownMenuButton1"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-bell text-primary fs-2"></i>
                    <?php if(count($notifications) != 0): ?>
                    <span class="badge bg-primary rounded-circle position-absolute d-flex mt-2 notification-counter" id="header-notification-counter"><?php echo e(count($notifications)); ?></span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu py-0 my-2" aria-labelledby="dropdownMenuButton1">
                    <div class="text-start border-bottom py-4 px-7">
                        <h3 class="text-gray-900 mb-0"><?php echo e(__('messages.notification.notification')); ?></h3>
                    </div>
                    <div class="px-7 mt-5 inner-scroll height-270">
                        <?php if(count($notifications) > 0): ?>
                        <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="javascript:void(0)" data-id="<?php echo e($notification->id); ?>"
                            class="readNotification text-decoration-none"
                            id="readNotification">
                            <div class="d-flex position-relative mb-5">
                                <span class="me-5 text-primary fs-2 icon-label"><i class="<?php echo e(getNotificationIcon($notification->type)); ?>"></i></span>
                                <div>
                                    <h5 class="text-gray-900 fs-6 mb-2"><?php echo e($notification->title); ?></h5>
                                    <h6 class="text-gray-600 fs-small fw-light mb-0"><?php echo e(\Carbon\Carbon::parse($notification->created_at)->diffForHumans(null, true)); ?></h6>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <div class="empty-state fs-6 text-gray-800 fw-bold text-center mt-5 d-none"
                            data-height="400">
                            <h5 class="text-gray-900 fs-6 mb-2"><?php echo e(__('messages.notification.you_don`t_have_any_new_notification')); ?></h5>
                        </div>
                        <?php else: ?>
                        <div class="empty-state fs-6 text-gray-800 fw-bold text-center mt-5"
                            data-height="400">
                            <h5 class="text-gray-900 fs-6 mb-2"><?php echo e(__('messages.notification.you_don`t_have_any_new_notification')); ?></h5>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-center border-top p-4 <?php echo e(count($notifications) > 0 ? '' : 'd-none'); ?>" id="readAllNotification">
                        <a href="javascript:void(0)" class="text-decoration-none">
                            <h5 class="text-primary mb-0 fs-5"><?php echo e(__('messages.notification.mark_all_as_read')); ?></h5>
                        </a>
                    </div>
                </div>
            </div>
        </li>
        <?php endif; ?>

        <li class="px-sm-3 px-2">
            <div class="dropdown d-flex align-items-center py-4">
                <div class="image image-circle image-mini">
                    <?php if(getLogInUser()->hasRole('patient')): ?>
                    <img class="img-fluid" alt="img-fluid"
                        src="<?php echo e(getLogInUser()->patient->profile); ?>" />
                    <?php elseif(getLogInUser()->hasRole('doctor')): ?>
                    <img class="img-fluid" alt="img-fluid"
                        src="<?php echo e(getLogInUser()->profile_image); ?>" />
                    <?php else: ?>
                    <img class="img-fluid" alt="img-fluid"
                        src="<?php echo e(getLogInUser()->profile_image); ?>" />
                    <?php endif; ?>
                </div>
                <button class="btn dropdown-toggle ps-2 pe-0" type="button" id="menuDropDown"
                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                    <?php echo e(getLogInUser()->full_name); ?>

                </button>
                <div class="dropdown-menu py-7 pb-4 my-2" aria-labelledby="menuDropDown">
                    <div class="text-center border-bottom pb-5">
                        <div class="image image-circle image-tiny mb-5">
                            <?php if(getLogInUser()->hasRole('patient')): ?>
                            <img class="img-fluid" alt="img-fluid"
                                src="<?php echo e(getLogInUser()->patient->profile); ?>" />
                            <?php elseif(getLogInUser()->hasRole('doctor')): ?>
                            <img class="img-fluid" alt="img-fluid"
                                src="<?php echo e(getLogInUser()->profile_image); ?>" />
                            <?php else: ?>
                            <img class="img-fluid" alt="img-fluid"
                                src="<?php echo e(getLogInUser()->profile_image); ?>" />
                            <?php endif; ?>
                        </div>
                        <h3 class="text-gray-900"><?php echo e(getLogInUser()->full_name); ?></h3>
                        <h4 class="mb-0 fw-400 fs-6"><?php echo e(getLogInUser()->email); ?></h4>
                    </div>
                    <ul class="pt-4">
                        <li>
                            <a class="dropdown-item text-gray-900" href="<?php echo e(route('profile.setting')); ?>">
                                <span class="dropdown-icon me-4 text-gray-600">
                                    <i class="fa-solid fa-user"></i>
                                </span>
                                <?php echo e(__('messages.user.account_setting')); ?>

                            </a>
                        </li>
                        <?php if((is_impersonating() === false)): ?>
                        <li>
                            <a class="dropdown-item text-gray-900" id="changePassword" href="javascript:void(0)">
                                <span class="dropdown-icon me-4 text-gray-600">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <?php echo e(__('messages.user.change_password')); ?>

                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- <?php if(getLogInUser()->hasRole('doctor') || getLogInUser()->hasRole('patient')): ?>
                        <li>
                            <a class="dropdown-item text-gray-900" id="emailNotification" href="javascript:void(0)">
                                <span class="dropdown-icon me-4 text-gray-600">
                                    <i class="fa-solid fa-bell"></i>
                                </span>
                                <?php echo e(__('messages.user.email_notification')); ?>

                            </a>
                        </li>
                        <?php endif; ?> -->

                        <?php if(session('impersonated_by')): ?>
                        <li>
                            <a class="dropdown-item text-gray-900" href="<?php echo e(route('impersonate.leave')); ?>">
                                <span class="dropdown-icon me-4 text-gray-600">
                                    <i class="fa-solid fa-user-check"></i>
                                </span>
                                <?php echo e(__('messages.user.return_to_admin')); ?>

                            </a>
                        </li>
                        <?php endif; ?>
                        <!-- <li>
                            <a class="dropdown-item text-gray-900" id="changeLanguage" href="javascript:void(0)">
                                <span class="dropdown-icon me-4 text-gray-600">
                                    <i class="fa-solid fa-globe"></i>
                                </span>
                                <?php echo e(__('messages.user.change_language')); ?>

                            </a>
                        </li> -->
                        <li>
                            <a class="dropdown-item text-gray-900 d-flex" href="javascript:void(0)">
                                <span class="dropdown-icon me-4 text-gray-600">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                </span>
                                <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="post">
                                    <?php echo csrf_field(); ?>
                                </form>
                                <span onclick="event.preventDefault(); localStorage.clear();  document.getElementById('logout-form').submit();">
                                    <?php echo e(__('messages.user.sign_out')); ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </li>
        <li>
            <button type="button" class="btn px-0 d-block d-xl-none header-btn pb-2">
                <i class="fa-solid fa-bars fs-1"></i>
            </button>
        </li>
    </ul>
</header>
<div class="bg-overlay" id="nav-overly"></div><?php /**PATH C:\Projects\NORSUCLINIC\resources\views/layouts/header.blade.php ENDPATH**/ ?>