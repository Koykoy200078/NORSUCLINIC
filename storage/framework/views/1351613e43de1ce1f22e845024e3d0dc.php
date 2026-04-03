<?php foreach ((['component', 'tableName']) as $__key => $__value) {
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

<?php if($component->bulkActionsAreEnabled() && $component->hasBulkActions()): ?>
    <?php
        $colspan = $component->getColspanCount();
        $selectAll = $component->selectAllIsEnabled();
        $simplePagination = $component->isPaginationMethod('simple');
    ?>

    <?php if($component->isTailwind()): ?>
        <?php if (isset($component)) { $__componentOriginal5d33d754447d8bc6578e0c6484d601be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5d33d754447d8bc6578e0c6484d601be = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-tables::components.table.tr.plain','data' => ['xCloak' => true,'xShow' => 'selectedItems.length > 0 && !currentlyReorderingStatus','wire:key' => ''.e($tableName).'-bulk-select-message','class' => 'bg-indigo-50 dark:bg-gray-900 dark:text-white']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('livewire-tables::table.tr.plain'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-cloak' => true,'x-show' => 'selectedItems.length > 0 && !currentlyReorderingStatus','wire:key' => ''.e($tableName).'-bulk-select-message','class' => 'bg-indigo-50 dark:bg-gray-900 dark:text-white']); ?>
            <?php if (isset($component)) { $__componentOriginalbaa855bb6e405acd6dcbf114ebb44614 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbaa855bb6e405acd6dcbf114ebb44614 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-tables::components.table.td.plain','data' => ['colspan' => $colspan]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('livewire-tables::table.td.plain'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($colspan)]); ?>
                <template x-if="selectedItems.length == paginationTotalItemCount">
                    <div wire:key="<?php echo e($tableName); ?>-all-selected">
                        <span>
                            <?php echo app('translator')->get('You are currently selecting all'); ?>
                            <!--[if BLOCK]><![endif]--><?php if(!$simplePagination): ?> <strong><span x-text="paginationTotalItemCount"></span></strong> <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <?php echo app('translator')->get('rows'); ?>.
                        </span>

                        <button
                            x-on:click="clearSelected"
                            wire:loading.attr="disabled"
                            type="button"
                            class="ml-1 text-blue-600 underline text-gray-700 text-sm leading-5 font-medium focus:outline-none focus:text-gray-800 focus:underline transition duration-150 ease-in-out dark:text-white dark:hover:text-gray-400"
                        >
                            <?php echo app('translator')->get('Deselect All'); ?>
                        </button>
                    </div>
                </template>

                <template x-if="selectedItems.length !== paginationTotalItemCount">
                    <div wire:key="<?php echo e($tableName); ?>-some-selected">
                        <span>
                            <?php echo app('translator')->get('You have selected'); ?>
                            <strong><span x-text="selectedItems.length"></span></strong>
                            <?php echo app('translator')->get('rows, do you want to select all'); ?>
                            <!--[if BLOCK]><![endif]--><?php if(!$simplePagination): ?> <strong><span x-text="paginationTotalItemCount"></span></strong> <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </span>

                        <button
                            x-on:click="selectAllOnPage()"
                            wire:loading.attr="disabled"
                            type="button"
                            class="ml-1 text-blue-600 underline text-gray-700 text-sm leading-5 font-medium focus:outline-none focus:text-gray-800 focus:underline transition duration-150 ease-in-out dark:text-white dark:hover:text-gray-400"
                        >
                            <?php echo app('translator')->get('Select All On Page'); ?>
                        </button>&nbsp;

                        <button
                            x-on:click="setAllSelected"
                            wire:loading.attr="disabled"
                            type="button"
                            class="ml-1 text-blue-600 underline text-gray-700 text-sm leading-5 font-medium focus:outline-none focus:text-gray-800 focus:underline transition duration-150 ease-in-out dark:text-white dark:hover:text-gray-400"
                        >
                            <?php echo app('translator')->get('Select All'); ?>
                        </button>

                        <button
                            x-on:click="clearSelected"
                            wire:loading.attr="disabled"
                            type="button"
                            class="ml-1 text-blue-600 underline text-gray-700 text-sm leading-5 font-medium focus:outline-none focus:text-gray-800 focus:underline transition duration-150 ease-in-out dark:text-white dark:hover:text-gray-400"
                        >
                            <?php echo app('translator')->get('Deselect All'); ?>
                        </button>
                    </div>
                </template>
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
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5d33d754447d8bc6578e0c6484d601be)): ?>
<?php $attributes = $__attributesOriginal5d33d754447d8bc6578e0c6484d601be; ?>
<?php unset($__attributesOriginal5d33d754447d8bc6578e0c6484d601be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5d33d754447d8bc6578e0c6484d601be)): ?>
<?php $component = $__componentOriginal5d33d754447d8bc6578e0c6484d601be; ?>
<?php unset($__componentOriginal5d33d754447d8bc6578e0c6484d601be); ?>
<?php endif; ?>
    <?php elseif($component->isBootstrap()): ?>
        <?php if (isset($component)) { $__componentOriginal5d33d754447d8bc6578e0c6484d601be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5d33d754447d8bc6578e0c6484d601be = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-tables::components.table.tr.plain','data' => ['xCloak' => true,'xShow' => 'selectedItems.length > 0 && !currentlyReorderingStatus','wire:key' => ''.e($tableName).'-bulk-select-message']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('livewire-tables::table.tr.plain'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-cloak' => true,'x-show' => 'selectedItems.length > 0 && !currentlyReorderingStatus','wire:key' => ''.e($tableName).'-bulk-select-message']); ?>
            <?php if (isset($component)) { $__componentOriginalbaa855bb6e405acd6dcbf114ebb44614 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbaa855bb6e405acd6dcbf114ebb44614 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-tables::components.table.td.plain','data' => ['colspan' => $colspan]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('livewire-tables::table.td.plain'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($colspan)]); ?>
                <template x-if="selectedItems.length == paginationTotalItemCount">
                    <div wire:key="<?php echo e($tableName); ?>-all-selected">
                        <span>
                            <?php echo app('translator')->get('You are currently selecting all'); ?>
                            <!--[if BLOCK]><![endif]--><?php if(!$simplePagination): ?> <strong><span x-text="paginationTotalItemCount"></span></strong> <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <?php echo app('translator')->get('rows'); ?>.
                        </span>

                        <button
                            x-on:click="clearSelected"
                            wire:loading.attr="disabled"
                            type="button"
                            class="btn btn-primary btn-sm"
                        >
                            <?php echo app('translator')->get('Deselect All'); ?>
                        </button>
                    </div>
                </template>

                <template x-if="selectedItems.length !== paginationTotalItemCount">
                    <div wire:key="<?php echo e($tableName); ?>-some-selected">
                        <span>
                            <?php echo app('translator')->get('You have selected'); ?>
                            <strong><span x-text="selectedItems.length"></span></strong>
                            <?php echo app('translator')->get('rows, do you want to select all'); ?>
                            <!--[if BLOCK]><![endif]--><?php if(!$simplePagination): ?> <strong><span x-text="paginationTotalItemCount"></span></strong> <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </span>

                        <button
                            x-on:click="selectAllOnPage"
                            wire:loading.attr="disabled"
                            type="button"
                            class="btn btn-primary btn-sm"
                        >
                            <?php echo app('translator')->get('Select All On Page'); ?>
                        </button>&nbsp;

                        <button
                            x-on:click="setAllSelected"
                            wire:loading.attr="disabled"
                            type="button"
                            class="btn btn-primary btn-sm"
                        >
                            <?php echo app('translator')->get('Select All'); ?>
                        </button>

                        <button
                            x-on:click="clearSelected"
                            wire:loading.attr="disabled"
                            type="button"
                            class="btn btn-primary btn-sm"
                        >
                            <?php echo app('translator')->get('Deselect All'); ?>
                        </button>
                    </div>
                </template>
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
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5d33d754447d8bc6578e0c6484d601be)): ?>
<?php $attributes = $__attributesOriginal5d33d754447d8bc6578e0c6484d601be; ?>
<?php unset($__attributesOriginal5d33d754447d8bc6578e0c6484d601be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5d33d754447d8bc6578e0c6484d601be)): ?>
<?php $component = $__componentOriginal5d33d754447d8bc6578e0c6484d601be; ?>
<?php unset($__componentOriginal5d33d754447d8bc6578e0c6484d601be); ?>
<?php endif; ?>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/vendor/livewire-tables/components/table/tr/bulk-actions.blade.php ENDPATH**/ ?>