{{-- Full CRUD access for all roles including doctors --}}
<a href="{{ isRole('clinic_admin') ? route('medicines.edit',$row->id) : (isRole('staff') ? route('staff.medicines.edit',$row->id) : route('doctors.medicines.edit',$row->id)) }}" title="<?php echo __('messages.common.edit') ?>"
   class=" btn px-1 text-primary fs-3 ps-0">
   <i class="fa-solid fa-pen-to-square"></i>
</a>
<a title="<?php echo __('messages.common.delete') ?>" data-id="{{$row->id}}" wire:key="{{$row->id}}"
   class="deleteMedicineBtn  btn px-1 text-danger fs-3 ps-0">
   <i class="fa-solid fa-trash"></i>
</a>