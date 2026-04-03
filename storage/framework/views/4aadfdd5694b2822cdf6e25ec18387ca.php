<?php foreach ((['component', 'tableName']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>
<div class="mb-3 mb-sm-0">
    <form class="d-flex position-relative">
        <div class="position-relative d-flex width-320">
          <span
                  class="position-absolute d-flex align-items-center top-0 bottom-0 left-0 text-gray-600 ms-3">
            <i class="fa-solid fa-magnifying-glass"></i>
        </span>
            <input class="form-control search-box ps-8" wire:model.live<?php echo e($component->getSearchOptions()); ?>="search" type="search" placeholder="<?php echo e(__('messages.common.search')); ?>" aria-label="Search">
        </div>
    </form>
    <!--[if BLOCK]><![endif]--><?php if(isset($filters['search']) && strlen($filters['search'])): ?>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/vendor/livewire-tables/components/tools/toolbar/items/search-field.blade.php ENDPATH**/ ?>