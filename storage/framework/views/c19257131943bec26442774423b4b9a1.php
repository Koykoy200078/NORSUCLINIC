<?php foreach ((['component', 'tableName']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['row', 'rowIndex']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['row', 'rowIndex']); ?>
<?php foreach (array_filter((['row', 'rowIndex']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $customAttributes = $component->getBulkActionsTdAttributes();
    $bulkActionsTdCheckboxAttributes = $component->getBulkActionsTdCheckboxAttributes();
    $theme = $component->getTheme();
?>

<!--[if BLOCK]><![endif]--><?php if($component->bulkActionsAreEnabled() && $component->hasBulkActions()): ?>
    <?php if (isset($component)) { $__componentOriginalbaa855bb6e405acd6dcbf114ebb44614 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbaa855bb6e405acd6dcbf114ebb44614 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-tables::components.table.td.plain','data' => ['wire:key' => ''.e($tableName).'-tbody-td-bulk-actions-td-'.e($row->{$this->getPrimaryKey()}).'','displayMinimisedOnReorder' => true,'customAttributes' => $customAttributes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('livewire-tables::table.td.plain'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:key' => ''.e($tableName).'-tbody-td-bulk-actions-td-'.e($row->{$this->getPrimaryKey()}).'','displayMinimisedOnReorder' => true,'customAttributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($customAttributes)]); ?>
        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'inline-flex rounded-md shadow-sm' => $theme === 'tailwind',
            'form-check' => $theme === 'bootstrap-5',
        ]); ?>">
            <input
                x-cloak x-show="!currentlyReorderingStatus"
                x-model="selectedItems"
                wire:key="<?php echo e($tableName . 'selectedItems-'.$row->{$this->getPrimaryKey()}); ?>"
                wire:loading.attr.delay="disabled"
                value="<?php echo e($row->{$this->getPrimaryKey()}); ?>"
                type="checkbox"
                <?php echo e($attributes->merge($bulkActionsTdCheckboxAttributes)->class([
                        'rounded border-gray-300 text-indigo-600 shadow-sm transition duration-150 ease-in-out focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-900 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:bg-gray-600' => ($theme === 'tailwind') && ($bulkActionsTdCheckboxAttributes['default'] ?? true),
                        'form-check-input' => ($theme === 'bootstrap-5') && ($bulkActionsTdCheckboxAttributes['default'] ?? true),
                        'except' => 'default',
                    ])); ?>

            />
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbaa855bb6e405acd6dcbf114ebb44614)): ?>
<?php $attributes = $__attributesOriginalbaa855bb6e405acd6dcbf114ebb44614; ?>
<?php unset($__attributesOriginalbaa855bb6e405acd6dcbf114ebb44614); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbaa855bb6e405acd6dcbf114ebb44614)): ?>
<?php $component = $__componentOriginalbaa855bb6e405acd6dcbf114ebb44614; ?>
<?php unset($__componentOriginalbaa855bb6e405acd6dcbf114ebb44614); ?>
<?php endif; ?>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/vendor/livewire-tables/components/table/td/bulk-actions.blade.php ENDPATH**/ ?>