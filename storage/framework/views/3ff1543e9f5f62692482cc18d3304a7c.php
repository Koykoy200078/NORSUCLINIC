<?php foreach ((['component']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>

<?php ($attributes = $attributes->merge(['wire:key' => 'empty-message-' . $component->getId()])); ?>

<!--[if BLOCK]><![endif]--><?php if($component->isTailwind()): ?>
    <tr <?php echo e($attributes); ?>>
        <td colspan="<?php echo e($component->getColspanCount()); ?>">
            <div class="flex justify-center items-center space-x-2 dark:bg-gray-800">
                <span
                    class="font-medium py-8 text-gray-400 text-lg dark:text-white"><?php echo e($component->getEmptyMessage()); ?></span>
            </div>
        </td>
    </tr>
<?php elseif($component->isBootstrap()): ?>
    <tr <?php echo e($attributes); ?>>
        <td colspan="<?php echo e($component->getColspanCount()); ?>">
            <div class="text-center">
                <?php echo e($component->getEmptyMessage()); ?>

            </div>
        </td>
    </tr>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/vendor/livewire-tables/components/table/empty.blade.php ENDPATH**/ ?>