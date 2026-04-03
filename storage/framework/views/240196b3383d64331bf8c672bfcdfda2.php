<div>
    <div class="row">
        
        <div class="col-xl-12 col-md-6">
            <div class="dashbord-doctor-card widget mb-5">
                <div class="dashbord-doctor-card-header">
                    <h3><?php echo e(__('messages.welcome_back')); ?></h3>
                    <span><?php echo e(__('messages.dashboard')); ?></span>
                </div>
                <div class="dashbord-doctor-card-body d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center py-3 py-sm-1">
                        <div class="image image-circle image-mini">
                            <!--[if BLOCK]><![endif]--><?php if(getLogInUser()->hasRole('patient')): ?>
                            <img class="img-fluid doctor-card-img" alt="img-fluid"
                                src="<?php echo e(getLogInUser()->patient->profile); ?>" />
                            <?php elseif(getLogInUser()->hasRole('doctor')): ?>
                            <img class="img-fluid doctor-card-img" alt="img-fluid"
                                src="<?php echo e(getLogInUser()->profile_image); ?>" />
                            <?php else: ?>
                            <img class="img-fluid doctor-card-img" alt="img-fluid"
                                src="<?php echo e(getLogInUser()->profile_image); ?>" />
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div class="doctor-data text-center">
                            <h3 class="text-gray-900"><?php echo e(getLogInUser()->full_name); ?></h3>
                            <h4 class="mb-0 fw-400 fs-6"><?php echo e(getLogInUser()->email); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="bg-white rounded-10 shadow-sm mb-5 p-5">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2 fw-semibold fs-6"><?php echo e(__('messages.common.active') . ' ' . __('messages.doctors')); ?></h6>
                        <h2 class="mb-0 fw-bolder text-primary"><?php echo e($totalDoctorCount); ?></h2>
                    </div>
                    <div class="ms-3">
                        <div class="bg-light-primary rounded-circle p-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-user-md fa-lg text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="bg-white rounded-10 shadow-sm mb-5 p-5">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2 fw-semibold fs-6"><?php echo e(__('messages.admin_dashboard.total_patients')); ?></h6>
                        <h2 class="mb-0 fw-bolder text-success"><?php echo e($totalPatientCount); ?></h2>
                    </div>
                    <div class="ms-3">
                        <div class="bg-light-success rounded-circle p-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-users fa-lg text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="bg-white rounded-10 shadow-sm mb-5 p-5">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2 fw-semibold fs-6"><?php echo e(__('Patient Queue Today')); ?></h6>
                        <h2 class="mb-0 fw-bolder text-info"><?php echo e($todayQueueCount); ?></h2>
                    </div>
                    <div class="ms-3">
                        <div class="bg-light-info rounded-circle p-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-users-line fa-lg text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="bg-white rounded-10 shadow-sm mb-5 p-5">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2 fw-semibold fs-6"><?php echo e(__('messages.admin_dashboard.today_registered_patients')); ?></h6>
                        <h2 class="mb-0 fw-bolder text-warning"><?php echo e($totalRegisteredPatientCount); ?></h2>
                    </div>
                    <div class="ms-3">
                        <div class="bg-light-warning rounded-circle p-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-user-plus fa-lg text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\Projects\NORSUCLINIC\resources\views/livewire/dashboard.blade.php ENDPATH**/ ?>