<?php foreach ((['component', 'tableName']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>

<?php if (isset($component)) { $__componentOriginal996070e5d95898390de29378c8f7fabb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal996070e5d95898390de29378c8f7fabb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-tables::components.table.th.plain','data' => ['xCloak' => true,'xShow' => 'currentlyReorderingStatus','wire:key' => ''.e($tableName).'-thead-reorder','displayMinimisedOnReorder' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('livewire-tables::table.th.plain'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-cloak' => true,'x-show' => 'currentlyReorderingStatus','wire:key' => ''.e($tableName).'-thead-reorder','displayMinimisedOnReorder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
    <div x-cloak x-show="currentlyReorderingStatus"></div>
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
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/vendor/livewire-tables/components/table/th/reorder.blade.php ENDPATH**/ ?>