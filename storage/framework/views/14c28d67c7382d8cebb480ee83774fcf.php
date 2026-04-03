<?php foreach ((['component']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['displayMinimisedOnReorder' => false, 'hideUntilReorder' => false, 'customAttributes' => ['default' => true]]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['displayMinimisedOnReorder' => false, 'hideUntilReorder' => false, 'customAttributes' => ['default' => true]]); ?>
<?php foreach (array_filter((['displayMinimisedOnReorder' => false, 'hideUntilReorder' => false, 'customAttributes' => ['default' => true]]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<th x-cloak <?php echo e($attributes); ?> scope="col"
    <?php echo e($attributes->merge($customAttributes)->class([
            'table-cell px-3 py-2 md:px-6 md:py-3 text-center md:text-left bg-gray-50 dark:bg-gray-800 laravel-livewire-tables-reorderingMinimised' => ($component->isTailwind()) && ($customAttributes['default'] ?? true),
            'laravel-livewire-tables-reorderingMinimised' => ($component->isBootstrap()) && ($customAttributes['default'] ?? true),
        ])); ?>

    <?php if($hideUntilReorder): ?> :class="!reorderDisplayColumn && 'w-0 p-0 hidden'" <?php endif; ?>
>
    <?php echo e($slot); ?>

</th>
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/vendor/livewire-tables/components/table/th/plain.blade.php ENDPATH**/ ?>