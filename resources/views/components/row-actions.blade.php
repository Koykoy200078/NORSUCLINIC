@props(['module' => null])

@if(($module === null || canUseModule($module)) && $slot->hasActualContent())
@php($menuId = 'row-actions-'.\Illuminate\Support\Str::uuid())
<div {{ $attributes->class(['dropdown', 'row-actions']) }}>
    <button type="button" class="btn btn-sm btn-icon btn-light" data-row-actions-toggle
        data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true"
        aria-label="{{ __('Row actions') }}" aria-controls="{{ $menuId }}" id="{{ $menuId }}-toggle">
        <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow" role="menu" id="{{ $menuId }}"
        aria-labelledby="{{ $menuId }}-toggle">
        {{ $slot }}
    </div>
</div>
@endif
