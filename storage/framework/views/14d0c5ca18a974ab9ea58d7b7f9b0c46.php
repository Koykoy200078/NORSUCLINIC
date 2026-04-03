<div>
    <div class="d-flex border-0 pt-5">
        <h3 class="align-items-start flex-column">
            <span class="fw-bolder fs-3 mb-1"><?php echo e(__('messages.admin_dashboard.recent_patients_registration')); ?></span>
        </h3>
        <div class="ms-auto d-sm-block d-none">
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link btn btn-sm btn-color-muted btn-active text-primary btn-active-light-primary fw-bolder px-4 active dayData" data-bs-toggle="tab" href="" id="dayData"><?php echo e(__('messages.admin_dashboard.day')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-sm btn-color-muted btn-active btn-active-light-primary fw-bolder px-4 me-1 weekData" data-bs-toggle="tab" href="" id="weekData"><?php echo e(__('messages.admin_dashboard.week')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-sm btn-color-muted btn-active btn-active-light-primary fw-bolder px-4 me-1 monthData" data-bs-toggle="tab" href="" id="monthData"><?php echo e(__('messages.admin_dashboard.month')); ?></a>
                </li>
            </ul>
        </div>
    </div>
    <div class="d-flex ms-auto d-sm-none d-block mt-2 mb-2 justify-content-end">
        <ul class="nav">
            <li class="nav-item">
                <a class="nav-link btn btn-sm btn-color-muted btn-active text-primary btn-active-light-primary fw-bolder px-4 active dayData" data-bs-toggle="tab" href="" id="dayData"><?php echo e(__('messages.admin_dashboard.day')); ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-sm btn-color-muted btn-active btn-active-light-primary fw-bolder px-4 me-1 weekData" data-bs-toggle="tab" href="" id="weekData"><?php echo e(__('messages.admin_dashboard.week')); ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-sm btn-color-muted btn-active btn-active-light-primary fw-bolder px-4 me-1 monthData" data-bs-toggle="tab" href="" id="monthData"><?php echo e(__('messages.admin_dashboard.month')); ?></a>
            </li>
        </ul>
    </div>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="month">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                            <th class="w-25px text-muted mt-1 fw-bold fs-7">
                                <?php echo e(__('messages.admin_dashboard.name')); ?>

                            </th>
                            <th class="min-w-150px text-muted mt-1 fw-bold fs-7">
                                <?php echo e(__('messages.admin_dashboard.patient_id')); ?>

                            </th>
                            <th class="min-w-150px text-muted mt-1 fw-bold fs-7 text-center">
                                <?php echo e(__('messages.patient.registered_on')); ?>

                            </th>
                        </tr>
                    </thead>
                    <tbody id="monthlyReport" class="text-gray-600 fw-bold">
                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $data['patients']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="image image-circle image-mini me-3">
                                        <img src="<?php echo e($patient['profile']); ?>" alt="user" class="">
                                    </div>
                                    <div class="d-flex flex-column">
                                        <a href="<?php echo e(route('patients.show', $patient['id'])); ?>" class="text-primary-800 mb-1 fs-6 text-decoration-none"><?php echo e($patient['user']['full_name']); ?></a>
                                        <span class="text-muted fw-bold d-block"><?php echo e($patient['user']['email']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-start">
                                <span class="badge bg-light-success"><?php echo e($patient['patient_unique_id']); ?></span>
                            </td>
                            <td class="text-center text-muted fw-bold">
                                <span class="badge bg-light-info">
                                    <?php echo e(\Carbon\Carbon::parse($patient['user']['created_at'])->isoFormat('DD MMM YYYY hh:mm A')); ?>

                                </span>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr class="text-center">
                            <td colspan="3" class="text-center text-muted fw-bold">
                                <?php echo e(__('messages.common.no_data_available')); ?>

                            </td>
                        </tr>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="week">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                            <th class="w-25px text-muted mt-1 fw-bold fs-7">
                                <?php echo e(__('messages.admin_dashboard.name')); ?>

                            </th>
                            <th class="min-w-150px text-muted mt-1 fw-bold fs-7">
                                <?php echo e(__('messages.admin_dashboard.patient_id')); ?>

                            </th>
                            <th class="min-w-150px text-muted mt-1 fw-bold fs-7 text-center">
                                <?php echo e(__('messages.patient.registered_on')); ?>

                            </th>
                        </tr>
                    </thead>
                    <tbody id="weeklyReport" class="text-gray-600 fw-bold">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="day">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                            <th class="w-25px text-muted mt-1 fw-bold fs-7">
                                <?php echo e(__('messages.admin_dashboard.name')); ?>

                            </th>
                            <th class="min-w-150px text-muted mt-1 fw-bold fs-7">
                                <?php echo e(__('messages.admin_dashboard.patient_id')); ?>

                            </th>
                            <th class="min-w-150px text-muted mt-1 fw-bold fs-7 text-center">
                                <?php echo e(__('messages.patient.registered_on')); ?>

                            </th>
                        </tr>
                    </thead>
                    <tbody id="dailyReport" class="text-gray-600 fw-bold">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\Projects\NORSUCLINIC\resources\views/livewire/admin-dash-board-table.blade.php ENDPATH**/ ?>