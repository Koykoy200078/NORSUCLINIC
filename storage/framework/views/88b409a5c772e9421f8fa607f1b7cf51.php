<?php foreach ((['component', 'tableName']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>

<!--[if BLOCK]><![endif]--><?php if($component->collapsingColumnsAreEnabled() && $component->hasCollapsedColumns()): ?>
    <!--[if BLOCK]><![endif]--><?php if($component->isTailwind()): ?>
        <th
            scope="col"
            <?php echo e($attributes
                    ->merge(['class' => 'table-cell dark:bg-gray-800 laravel-livewire-tables-reorderingMinimised'])
                    ->class(['sm:hidden' => !$component->shouldCollapseOnTablet() && !$component->shouldCollapseAlways()])
                    ->class(['md:hidden' => !$component->shouldCollapseOnMobile() && !$component->shouldCollapseOnTablet() && !$component->shouldCollapseAlways()])
                    ->class(['lg:hidden' => !$component->shouldCollapseAlways()])); ?>

            :class="{ 'laravel-livewire-tables-reorderingMinimised': ! currentlyReorderingStatus }"
        ></th>
    <?php elseif($component->isBootstrap()): ?>
        <th
            scope="col"
            <?php echo e($attributes
                    ->merge(['class' => 'd-table-cell laravel-livewire-tables-reorderingMinimised'])
                    ->class(['d-sm-none' => !$component->shouldCollapseOnTablet() && !$component->shouldCollapseAlways()])
                    ->class(['d-md-none' => !$component->shouldCollapseOnMobile() && !$component->shouldCollapseOnTablet() && !$component->shouldCollapseAlways()])
                    ->class(['d-lg-none' => !$component->shouldCollapseAlways()])); ?>                    
            :class="{ 'laravel-livewire-tables-reorderingMinimised': ! currentlyReorderingStatus }"
        ></th>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/vendor/livewire-tables/components/table/th/collapsed-columns.blade.php ENDPATH**/ ?>