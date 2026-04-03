<?php foreach ((['component', 'tableName']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>

<?php
    $customAttributes = [
        'wrapper' => $this->getTableWrapperAttributes(),
        'table' => $this->getTableAttributes(),
        'thead' => $this->getTheadAttributes(),
        'tbody' => $this->getTbodyAttributes(),
    ];
?>

<!--[if BLOCK]><![endif]--><?php if($component->isTailwind()): ?>
    <div wire:key="<?php echo e($tableName); ?>-twrap"
        <?php echo e($attributes->merge($customAttributes['wrapper'])->class([
                'shadow overflow-y-auto border-b border-gray-200 dark:border-gray-700 sm:rounded-lg' =>
                    $customAttributes['wrapper']['default'] ?? true,
            ])->except('default')); ?>>
        <table wire:key="<?php echo e($tableName); ?>-table"
            <?php echo e($attributes->merge($customAttributes['table'])->class(['min-w-full divide-y divide-gray-200 dark:divide-none' => $customAttributes['table']['default'] ?? true])->except('default')); ?>>
            <thead wire:key="<?php echo e($tableName); ?>-thead"
                <?php echo e($attributes->merge($customAttributes['thead'])->class(['bg-gray-50 dark:bg-gray-800' => $customAttributes['thead']['default'] ?? true])->except('default')); ?>>
                <tr>
                    <?php echo e($thead); ?>

                </tr>
            </thead>

            <tbody wire:key="<?php echo e($tableName); ?>-tbody" id="<?php echo e($tableName); ?>-tbody"
                <?php echo e($attributes->merge($customAttributes['tbody'])->class([
                        'bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-none' =>
                            $customAttributes['tbody']['default'] ?? true,
                    ])->except('default')); ?>>
                <?php echo e($slot); ?>

            </tbody>

            <!--[if BLOCK]><![endif]--><?php if(isset($tfoot)): ?>
                <tfoot wire:key="<?php echo e($tableName); ?>-tfoot">
                    <?php echo e($tfoot); ?>

                </tfoot>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </table>
    </div>
<?php elseif($component->isBootstrap()): ?>
    <div class="d-flex justify-content-between mb-5">
        <!--[if BLOCK]><![endif]--><?php if($component->searchIsEnabled() && $component->searchVisibilityIsEnabled()): ?>
            <?php if (isset($component)) { $__componentOriginala0818fea5a9943294909fb32c8167a66 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala0818fea5a9943294909fb32c8167a66 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-tables::components.tools.toolbar.items.search-field','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('livewire-tables::tools.toolbar.items.search-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala0818fea5a9943294909fb32c8167a66)): ?>
<?php $attributes = $__attributesOriginala0818fea5a9943294909fb32c8167a66; ?>
<?php unset($__attributesOriginala0818fea5a9943294909fb32c8167a66); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala0818fea5a9943294909fb32c8167a66)): ?>
<?php $component = $__componentOriginala0818fea5a9943294909fb32c8167a66; ?>
<?php unset($__componentOriginala0818fea5a9943294909fb32c8167a66); ?>
<?php endif; ?>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <div>
            <!--[if BLOCK]><![endif]--><?php if($component->showFilterOnHeader == true): ?>
                <?php echo $__env->make($component->FilterComponent[0], [
                    'filterHeads' => [
                        $component->FilterComponent[1],
                        isset($component->FilterComponent[2]) ? $component->FilterComponent[2] : '',
                    ],
                ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            <!--[if BLOCK]><![endif]--><?php if($component->showButtonOnHeader): ?>
                <?php echo $__env->make($component->buttonComponent, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>
    <div wire:key="<?php echo e($tableName); ?>-twrap"
        <?php echo e($attributes->merge($customAttributes['wrapper'])->class(['table-responsive' => $customAttributes['wrapper']['default'] ?? true])->except('default')); ?>>
        <table wire:key="<?php echo e($tableName); ?>-table"
            <?php echo e($attributes->merge($customAttributes['table'])->class(['table table-striped' => $customAttributes['table']['default'] ?? true])->except('default')); ?>>
            <thead wire:key="<?php echo e($tableName); ?>-thead"
                <?php echo e($attributes->merge($customAttributes['thead'])->class(['' => $customAttributes['thead']['default'] ?? true])->except('default')); ?>>
                <tr>
                    <?php echo e($thead); ?>

                </tr>
            </thead>

            <tbody wire:key="<?php echo e($tableName); ?>-tbody" id="<?php echo e($tableName); ?>-tbody"
                <?php echo e($attributes->merge($customAttributes['tbody'])->class(['' => $customAttributes['tbody']['default'] ?? true])->except('default')); ?>>
                <?php echo e($slot); ?>

            </tbody>

            <!--[if BLOCK]><![endif]--><?php if(isset($tfoot)): ?>
                <tfoot wire:key="<?php echo e($tableName); ?>-tfoot">
                    <?php echo e($tfoot); ?>

                </tfoot>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </table>
    </div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/vendor/livewire-tables/components/table.blade.php ENDPATH**/ ?>