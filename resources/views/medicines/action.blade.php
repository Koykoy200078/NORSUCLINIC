<x-row-actions module="inventory">
<a href="{{ getRouteByRole('medicines.edit', [$row->id]) }}" title="<?php echo __('messages.common.edit') ?>"
   class="dropdown-item px-1 text-primary fs-3 ps-0" role="menuitem" tabindex="-1">
   <i class="fa-solid fa-pen-to-square"></i>
    <span><?php echo __('messages.common.edit') ?></span>
</a>
@if(! $row->hasHistory())
<a title="<?php echo __('messages.common.delete') ?>" data-id="{{$row->id}}" wire:key="{{$row->id}}"
   class="dropdown-item deleteMedicineBtn px-1 text-danger fs-3 ps-0" role="menuitem" href="javascript:void(0)" tabindex="-1">
   <i class="fa-solid fa-trash"></i>
    <span><?php echo __('messages.common.delete') ?></span>
</a>
@endif
</x-row-actions>
