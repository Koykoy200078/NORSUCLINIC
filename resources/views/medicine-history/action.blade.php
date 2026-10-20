<x-row-actions module="dispensing">
<a
{{--  href="{{ route('bills.edit',$row->id) }}"   --}}
title="<?php echo __('messages.common.edit') ?>"
   class="dropdown-item px-1 text-primary fs-3 ps-0 bill-edit-btn" role="menuitem" href="javascript:void(0)" tabindex="-1">
    <i class="fa-solid fa-pen-to-square"></i>
    <span><?php echo __('messages.common.edit') ?></span>
</a>
<a href="javascript:void(0)" title="<?php echo __('messages.common.delete') ?>"
     {{--  data-id="{{$row->id}}"  --}}
   class="dropdown-item bill-delete-btn px-1 text-danger fs-3 pe-0"
   {{--  wire:key="{{$row->id}}"  --}}
    role="menuitem" tabindex="-1">
    <i class="fa-solid fa-trash"></i>
    <span><?php echo __('messages.common.delete') ?></span>
</a>


</x-row-actions>
