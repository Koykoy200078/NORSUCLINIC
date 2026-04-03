<?php foreach ((['component', 'tableName']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['rowID', 'rowIndex']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['rowID', 'rowIndex']); ?>
<?php foreach (array_filter((['rowID', 'rowIndex']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php ($theme = $component->getTheme()); ?>

<?php if (isset($component)) { $__componentOriginalbaa855bb6e405acd6dcbf114ebb44614 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbaa855bb6e405acd6dcbf114ebb44614 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-tables::components.table.td.plain','data' => ['xCloak' => true,'xShow' => 'currentlyReorderingStatus','wire:key' => ''.e($tableName).'-tbody-reorder-'.e($rowID).'','displayMinimisedOnReorder' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('livewire-tables::table.td.plain'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-cloak' => true,'x-show' => 'currentlyReorderingStatus','wire:key' => ''.e($tableName).'-tbody-reorder-'.e($rowID).'','displayMinimisedOnReorder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
    <svg
        x-cloak x-show="currentlyReorderingStatus"
        xmlns="http://www.w3.org/2000/svg"
        fill="none" stroke="currentColor"
        viewBox="0 0 24 24"
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'inline w-4 h-4' => $theme === 'tailwind',
            'd-inline' => $theme === 'bootstrap-4' || $theme === 'bootstrap-5',
        ]); ?>"
        style="<?php echo \Illuminate\Support\Arr::toCssStyles([
            'width:1em; height:1em;' => $theme === 'bootstrap-4' || $theme === 'bootstrap-5',
        ]) ?>"
    >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
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
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/vendor/livewire-tables/components/table/td/reorder.blade.php ENDPATH**/ ?>