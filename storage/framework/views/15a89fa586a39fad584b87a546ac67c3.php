<?php foreach ((['component', 'tableName']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>

<!--[if BLOCK]><![endif]--><?php if($component->filtersAreEnabled() && $component->filterPillsAreEnabled() && $component->hasAppliedVisibleFiltersForPills()): ?>
    <div>
        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'mb-4 px-4 md:p-0' => $component->isTailwind(),
            'mb-3' => $component->isBootstrap(),
        ]); ?>" x-cloak x-show="!currentlyReorderingStatus">
            <small class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'text-gray-700 dark:text-white' => $component->isTailwind(),
                '' =>  $component->isBootstrap(),
            ]); ?>">
                <?php echo app('translator')->get('Applied Filters'); ?>:
            </small>

            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $component->getAppliedFiltersWithValues(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filterSelectName => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php ($filter = $component->getFilterByKey($filterSelectName)); ?>

                <?php if(is_null($filter)) continue; ?>
                <?php if($filter->isHiddenFromPills()) continue; ?>

                <!--[if BLOCK]><![endif]--><?php if($filter->hasCustomPillBlade()): ?>
                    <?php echo $__env->make($filter->getCustomPillBlade(), ['filter' => $filter], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php else: ?>
                    <span
                        wire:key="<?php echo e($tableName); ?>-filter-pill-<?php echo e($filter->getKey()); ?>"
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium leading-4 bg-indigo-100 text-indigo-800 capitalize dark:bg-indigo-200 dark:text-indigo-900' => $component->isTailwind(),
                            'badge badge-pill badge-info d-inline-flex align-items-center' => $component->isBootstrap4(),
                            'badge rounded-pill bg-info d-inline-flex align-items-center' => $component->isBootstrap5(),
                        ]); ?>"
                    >
                        <?php echo e($filter->getFilterPillTitle()); ?>: <?php echo e($filter->getFilterPillValue($value)); ?>


                        <!--[if BLOCK]><![endif]--><?php if($component->isTailwind()): ?>
                            <button
                                wire:click="resetFilter('<?php echo e($filter->getKey()); ?>')"
                                type="button"
                                class="flex-shrink-0 ml-0.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-indigo-400 hover:bg-indigo-200 hover:text-indigo-500 focus:outline-none focus:bg-indigo-500 focus:text-white"
                            >
                                <span class="sr-only"><?php echo app('translator')->get('Remove filter option'); ?></span>
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('heroicon-m-x-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(BladeUI\Icons\Components\Svg::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-2 w-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                            </button>
                        <?php else: ?>
                            <a
                                href="#"
                                wire:click="resetFilter('<?php echo e($filter->getKey()); ?>')"
                                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'text-white ml-2' => ($component->isBootstrap()),
                                ]); ?>"
                            >
                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'sr-only' => $component->isBootstrap4(),
                                    'visually-hidden' => $component->isBootstrap5(),
                                ]); ?>">
                                    <?php echo app('translator')->get('Remove filter option'); ?>
                                </span>
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('heroicon-m-x-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(BladeUI\Icons\Components\Svg::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['style' => 'width:.5em;height:.5em']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                            </a>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </span>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

            <!--[if BLOCK]><![endif]--><?php if($component->isTailwind()): ?>
                <button
                    wire:click.prevent="setFilterDefaults"
                    class="focus:outline-none active:outline-none"
                >
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-200 dark:text-gray-900">
                        <?php echo app('translator')->get('Clear'); ?>
                    </span>
                </button>
            <?php else: ?>
                <a
                    href="#"
                    wire:click.prevent="setFilterDefaults"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'badge badge-pill badge-light' => $component->isBootstrap4(),
                        'badge rounded-pill bg-light text-dark text-decoration-none' => $component->isBootstrap5(),
                    ]); ?>"
                >
                    <?php echo app('translator')->get('Clear'); ?>
                </a>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->

<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/vendor/livewire-tables/components/tools/filter-pills.blade.php ENDPATH**/ ?>