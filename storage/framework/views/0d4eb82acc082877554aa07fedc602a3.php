<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['component', 'tableName']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['component', 'tableName']); ?>
<?php foreach (array_filter((['component', 'tableName']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div wire:key="<?php echo e($tableName); ?>-wrapper" x-data="tableWrapper($wire, <?php echo e($component->showBulkActionsDropdownAlpine()); ?>)">
    <div <?php echo e($attributes->merge($this->getComponentWrapperAttributes())); ?>

        <?php if($component->hasRefresh()): ?> wire:poll<?php echo e($component->getRefreshOptions()); ?> <?php endif; ?>
        <?php if($component->isFilterLayoutSlideDown()): ?> wire:ignore.self <?php endif; ?>>

        <div x-data="reorderFunction($wire, '<?php echo e($component->getTableAttributes()['id']); ?>', '<?php echo e($component->getPrimaryKey()); ?>')">
            <?php echo $__env->make('livewire-tables::includes.debug', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->make('livewire-tables::includes.offline', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <?php echo e($slot); ?>

        </div>
    </div>
</div>
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/vendor/livewire-tables/components/wrapper.blade.php ENDPATH**/ ?>