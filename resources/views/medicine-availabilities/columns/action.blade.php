<x-row-actions module="inventory">
    <a href="{{ 
        getRouteByRole('stock-in.show', [$row->id])
    }}" class='dropdown-item px-2 text-primary fs-3 ps-0' role="menuitem" tabindex="-1">
        <i class="fas fa-eye text-success"></i>
        <span>{{ __('messages.common.view') }}</span>
    </a>
    @unless(isRole('doctor'))
    <a title="<?php echo __('messages.common.delete'); ?>" data-id="{{ $row->id }}" class="dropdown-item purchaseMedicineDelete px-2 text-danger fs-3 ps-0" role="menuitem" href="javascript:void(0)" tabindex="-1">
        <i class="fa-solid fa-trash"></i>
        <span><?php echo __('messages.common.delete'); ?></span>
    </a>
    @endunless
</x-row-actions>