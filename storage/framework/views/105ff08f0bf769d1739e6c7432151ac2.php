<?php foreach ((['component']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>

<div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
    'flex-col' => $component->isTailwind(),
    'd-flex flex-column ' => ($component->isBootstrap()),
]); ?>">
    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/vendor/livewire-tables/components/tools.blade.php ENDPATH**/ ?>