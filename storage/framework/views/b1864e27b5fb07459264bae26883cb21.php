<?php foreach ((['component', 'tableName']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>
<?php
    $customAttributes = $component->getBulkActionsThAttributes();
    $bulkActionsThCheckboxAttributes = $component->getBulkActionsThCheckboxAttributes();
    $theme = $component->getTheme();
?>

<!--[if BLOCK]><![endif]--><?php if($component->bulkActionsAreEnabled() && $component->hasBulkActions()): ?>
    <?php if (isset($component)) { $__componentOriginal996070e5d95898390de29378c8f7fabb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal996070e5d95898390de29378c8f7fabb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-tables::components.table.th.plain','data' => ['wire:key' => ''.e($tableName).'-thead-bulk-actions','displayMinimisedOnReorder' => true,'customAttributes' => $customAttributes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('livewire-tables::table.th.plain'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:key' => ''.e($tableName).'-thead-bulk-actions','displayMinimisedOnReorder' => true,'customAttributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($customAttributes)]); ?>
        <div
            x-data="{newSelectCount: 0, indeterminateCheckbox: false, bulkActionHeaderChecked: false}"
            x-init="$watch('selectedItems', value => indeterminateCheckbox = (value.length > 0 && value.length < paginationTotalItemCount))"
            x-cloak x-show="currentlyReorderingStatus !== true"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'inline-flex rounded-md shadow-sm' => $theme === 'tailwind',
                'form-check' => $theme === 'bootstrap-5',
            ]); ?>"
        >
            <input
                x-init="$watch('indeterminateCheckbox', value => $el.indeterminate = value); $watch('selectedItems', value => newSelectCount = value.length);"
                x-on:click="if(selectedItems.length == paginationTotalItemCount) { $el.indeterminate = false; $wire.clearSelected(); bulkActionHeaderChecked = false; } else { bulkActionHeaderChecked = true; $el.indeterminate = false; $wire.setAllSelected(); }"
                type="checkbox"
                :checked="selectedItems.length == paginationTotalItemCount"
                <?php echo e($attributes->merge($bulkActionsThCheckboxAttributes)->class([
                        'rounded border-gray-300 text-indigo-600 shadow-sm transition duration-150 ease-in-out focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-900 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:bg-gray-600' => ($theme === 'tailwind') && ($bulkActionsThCheckboxAttributes['default'] ?? true),
                        'form-check-input' => ($theme === 'bootstrap-5') && ($bulkActionsThCheckboxAttributes['default'] ?? true),
                        'except' => 'default',
                    ])); ?>

            />
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal996070e5d95898390de29378c8f7fabb)): ?>
<?php $attributes = $__attributesOriginal996070e5d95898390de29378c8f7fabb; ?>
<?php unset($__attributesOriginal996070e5d95898390de29378c8f7fabb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal996070e5d95898390de29378c8f7fabb)): ?>
<?php $component = $__componentOriginal996070e5d95898390de29378c8f7fabb; ?>
<?php unset($__componentOriginal996070e5d95898390de29378c8f7fabb); ?>
<?php endif; ?>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/vendor/livewire-tables/components/table/th/bulk-actions.blade.php ENDPATH**/ ?>