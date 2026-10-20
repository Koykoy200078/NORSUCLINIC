<x-row-actions module="inventory">
    <a href="{{ 
    getRouteByRole('generics.edit', [$row->id])
}}" title="<?php echo __('messages.common.edit') ?>"
        class="dropdown-item px-2 text-primary fs-3" role="menuitem" tabindex="-1">
        <i class="fa-solid fa-pen-to-square"></i>
        <span><?php echo __('messages.common.edit') ?></span>
    </a>
    <a href="javascript:void(0)" title="<?php echo __('messages.common.delete') ?>" data-id="{{$row->id}}" data-name="{{$row->name}}" wire:key="{{$row->id}}"
        class="dropdown-item generic-delete-btn px-2 text-danger fs-3" role="menuitem" tabindex="-1">
        <i class="fa-solid fa-trash"></i>
        <span><?php echo __('messages.common.delete') ?></span>
    </a>
</x-row-actions>
