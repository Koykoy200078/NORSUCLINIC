<div class="d-flex justify-content-center">
    <a href="<?php echo e(route('staffs.edit', $row->id)); ?>" title="<?php echo e(__('messages.common.edit')); ?>"
       class="btn px-1 text-primary fs-3 ps-0">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
    <a href="javascript:void(0)" title="<?php echo e(__('messages.common.delete')); ?>" data-id="<?php echo e($row->id); ?>" wire:key="<?php echo e($row->id); ?>"
       class="staff-delete-btn btn px-1 text-danger fs-3 ps-0">
        <i class="fa-solid fa-trash"></i>
    </a>
</div>

<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/staffs/components/action.blade.php ENDPATH**/ ?>