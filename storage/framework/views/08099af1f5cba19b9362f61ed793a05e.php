<?php foreach ((['component']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['rows']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['rows']); ?>
<?php foreach (array_filter((['rows']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<!--[if BLOCK]><![endif]--><?php if($component->hasConfigurableAreaFor('before-pagination')): ?>
    <?php echo $__env->make($component->getConfigurableAreaFor('before-pagination'), $component->getParametersForConfigurableArea('before-pagination'), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->

<!--[if BLOCK]><![endif]--><?php if($component->isTailwind()): ?>
    <div>
        <!--[if BLOCK]><![endif]--><?php if($component->paginationVisibilityIsEnabled()): ?>
            <div class="mt-4 px-4 md:p-0 sm:flex justify-between items-center space-y-4 sm:space-y-0">
                <div>
                    <!--[if BLOCK]><![endif]--><?php if($component->paginationIsEnabled() && $component->isPaginationMethod('standard') && $rows->lastPage() > 1): ?>
                        <p class="paged-pagination-results text-sm text-gray-700 leading-5 dark:text-white">
                            <!--[if BLOCK]><![endif]--><?php if($component->showPaginationDetails()): ?>
                                <span><?php echo app('translator')->get('Showing'); ?></span>
                                <span class="font-medium"><?php echo e($rows->firstItem()); ?></span>
                                <span><?php echo app('translator')->get('to'); ?></span>
                                <span class="font-medium"><?php echo e($rows->lastItem()); ?></span>
                                <span><?php echo app('translator')->get('of'); ?></span>
                                <span class="font-medium"><span x-text="paginationTotalItemCount"></span></span>
                                <span><?php echo app('translator')->get('results'); ?></span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </p>
                    <?php elseif($component->paginationIsEnabled() && $component->isPaginationMethod('simple')): ?>
                        <p class="paged-pagination-results text-sm text-gray-700 leading-5 dark:text-white">
                            <!--[if BLOCK]><![endif]--><?php if($component->showPaginationDetails()): ?>
                                <span><?php echo app('translator')->get('Showing'); ?></span>
                                <span class="font-medium"><?php echo e($rows->firstItem()); ?></span>
                                <span><?php echo app('translator')->get('to'); ?></span>
                                <span class="font-medium"><?php echo e($rows->lastItem()); ?></span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </p>
                    <?php elseif($component->paginationIsEnabled() && $component->isPaginationMethod('cursor')): ?>
                    <?php else: ?>
                        <p class="total-pagination-results text-sm text-gray-700 leading-5 dark:text-white">
                            <?php echo app('translator')->get('Showing'); ?>
                            <span class="font-medium"><?php echo e($rows->count()); ?></span>
                            <?php echo app('translator')->get('results'); ?>
                        </p>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                <?php if($component->paginationIsEnabled()): ?>
                    <?php echo e($rows->links('livewire-tables::specific.tailwind.'.(!$component->isPaginationMethod('standard') ? 'simple-' : '').'pagination')); ?>

                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
<?php elseif($component->isBootstrap4()): ?>
    <div >
        <!--[if BLOCK]><![endif]--><?php if($component->paginationVisibilityIsEnabled()): ?>
            <!--[if BLOCK]><![endif]--><?php if($component->paginationIsEnabled() && $component->isPaginationMethod('standard') && $rows->lastPage() > 1): ?>
                <div class="row mt-3">
                    <div class="col-12 col-md-6 overflow-auto">
                        <?php echo e($rows->links('livewire-tables::specific.bootstrap-4.pagination')); ?>

                    </div>

                    <div class="col-12 col-md-6 text-center text-md-right text-muted">
                        <!--[if BLOCK]><![endif]--><?php if($component->showPaginationDetails()): ?>
                            <span><?php echo app('translator')->get('Showing'); ?></span>
                            <strong><?php echo e($rows->count() ? $rows->firstItem() : 0); ?></strong>
                            <span><?php echo app('translator')->get('to'); ?></span>
                            <strong><?php echo e($rows->count() ? $rows->lastItem() : 0); ?></strong>
                            <span><?php echo app('translator')->get('of'); ?></span>
                            <strong><span x-text="paginationTotalItemCount"></span></strong>
                            <span><?php echo app('translator')->get('results'); ?></span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php elseif($component->paginationIsEnabled() && $component->isPaginationMethod('simple')): ?>
                <div class="row mt-3">
                    <div class="col-12 col-md-6 overflow-auto">
                        <?php echo e($rows->links('livewire-tables::specific.bootstrap-4.simple-pagination')); ?>

                    </div>

                    <div class="col-12 col-md-6 text-center text-md-right text-muted">
                        <!--[if BLOCK]><![endif]--><?php if($component->showPaginationDetails()): ?>
                            <span><?php echo app('translator')->get('Showing'); ?></span>
                            <strong><?php echo e($rows->count() ? $rows->firstItem() : 0); ?></strong>
                            <span><?php echo app('translator')->get('to'); ?></span>
                            <strong><?php echo e($rows->count() ? $rows->lastItem() : 0); ?></strong>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php elseif($component->paginationIsEnabled() && $component->isPaginationMethod('cursor')): ?>
                <div class="row mt-3">
                    <div class="col-12 col-md-6 overflow-auto">
                        <?php echo e($rows->links('livewire-tables::specific.bootstrap-4.simple-pagination')); ?>

                    </div>
                </div>
            <?php else: ?>
                <div class="row mt-3">
                    <div class="col-12 text-muted">
                        <?php echo app('translator')->get('Showing'); ?>
                        <strong><?php echo e($rows->count()); ?></strong>
                        <?php echo app('translator')->get('results'); ?>
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
<?php elseif($component->isBootstrap5()): ?>

    <div >
        <div class="d-flex align-items-center flex-xxl-row flex-column mb-5 mt-3">
            <!--[if BLOCK]><![endif]--><?php if($component->paginationVisibilityIsEnabled()): ?>
                <?php if($component->paginationIsEnabled() && $component->perPageVisibilityIsEnabled()): ?>
                    <div class="mb-xxl-0 mb-xl-5 mb-3 d-flex align-items-center justify-content-sm-start justify-content-center">
                        <span class="me-3 text-gray-600 fs-4 fs-xl-6"><?php echo app('translator')->get('Show'); ?></span>
                        <select wire:model.live="perPage" id="perPage"
                            class="form-select w-auto data-sorting pl-1 pr-5 py-2 border-0">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $component->getPerPageAccepted(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($item); ?>"
                                    wire:key="per-page-<?php echo e($item); ?>-<?php echo e($component->getTableName()); ?>">
                                    <?php echo e($item === -1 ? __('All') : $item); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </select>
                    </div>
                    <?php if($component->paginationIsEnabled() && $rows->lastPage() > 1): ?>
                        <div class="row flex-md-row-reverse flex-column-reverse mx-0 w-100">
                            <div class="col-12 col-xxl-9 col-lg-8 overflow-auto pagination-center ms-auto">
                                <?php echo e($rows->links('livewire-tables::specific.bootstrap-4.pagination')); ?>

                            </div>
                            <div class="col-12 col-xxl-3 col-lg-4 text-center text-lg-end text-gray-600 d-flex align-items-center justify-content-md-start justify-content-center mb-md-0 mb-3 flex-wrap ps-0">
                                <div class="fs-4 fs-xl-6 ms-lg-3">
                                    <span><?php echo app('translator')->get('Showing'); ?></span>
                                    <span class="fw-bold"><?php echo e($rows->count() ? $rows->firstItem() : 0); ?></span>
                                    <span><?php echo app('translator')->get('to'); ?></span>
                                    <span class="fw-bold"><?php echo e($rows->count() ? $rows->lastItem() : 0); ?></span>
                                    <span><?php echo app('translator')->get('of'); ?></span>
                                    <span class="fw-bold"><?php echo e($rows->total()); ?></span>
                                    <span><?php echo app('translator')->get('results'); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-12 ms-3 text-gray-600 fs-4">
                                <?php echo app('translator')->get('Showing'); ?>
                                <strong><?php echo e($rows->count()); ?></strong>
                                <?php echo app('translator')->get('results'); ?>
                            </div>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->

<!--[if BLOCK]><![endif]--><?php if($component->hasConfigurableAreaFor('after-pagination')): ?>
    <?php echo $__env->make($component->getConfigurableAreaFor('after-pagination'), $component->getParametersForConfigurableArea('after-pagination'), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/vendor/livewire-tables/components/pagination.blade.php ENDPATH**/ ?>