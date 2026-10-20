@php
$showUrl = getRouteByRole('prescription.medicine.show', ['id' => $row->id]);
$editUrl = getRouteByRole('prescriptions.edit', ['prescription' => $row->id]);
$pdfUrl = getRouteByRole('prescriptions.pdf', ['id' => $row->id]);
$statusUrl = getRouteByRole('prescription.status', ['prescription' => $row->id]);
$isCancelled = $row->status === \App\Models\Prescription::DISPENSE_STATUS_CANCELLED || ! ($row->is_active ?? true);
$isDispensed = $row->status === \App\Models\Prescription::DISPENSE_STATUS_DISPENSED;
$canSwitch = ! $isDispensed && ! isRole('patient');
// A dispensed prescription is a closed record (no edit, no delete) and staff never delete one: draw only what works.
$canEdit = ! $isDispensed;
$canDelete = ! $isDispensed && ! isRole('staff') && ! isRole('patient');
@endphp

<x-row-actions module="prescriptions">
    {{-- Show Button --}}
    <a href="{{ $showUrl }}"
        title="{{ __('messages.common.view') }}"
        class="dropdown-item text-info action-btn" role="menuitem" tabindex="-1">
        <i class="fas fa-eye"></i>
        <span>{{ __('messages.common.view') }}</span>
    </a>

    {{-- Edit Button --}}
    @if ($canEdit)
    <a href="{{ $editUrl }}"
        title="{{ __('messages.common.edit') }}"
        class="dropdown-item text-primary action-btn" role="menuitem" tabindex="-1">
        <i class="fas fa-edit"></i>
        <span>{{ __('messages.common.edit') }}</span>
    </a>
    @endif

    {{-- PDF Button --}}
    <a href="{{ $pdfUrl }}"
        title="{{ __('messages.prescription.download_pdf') }}"
        class="dropdown-item text-secondary action-btn"
        target="_blank" role="menuitem" tabindex="-1">
        <i class="fas fa-file-pdf"></i>
        <span>{{ __('messages.prescription.download_pdf') }}</span>
    </a>

    {{-- Cancel / Reactivate: a cancelled prescription leaves the pharmacy queue and cannot be dispensed.
         A plain form post, so it works without the compiled JavaScript. --}}
    @if ($canSwitch)
    <form action="{{ $statusUrl }}" method="POST" class="d-inline"
        onsubmit="return confirm('{{ $isCancelled ? 'Reactivate this prescription so the pharmacy can dispense it?' : 'Cancel this prescription? The pharmacy will no longer be able to dispense it.' }}')">
        @csrf
        <button type="submit"
            title="{{ $isCancelled ? 'Reactivate prescription' : 'Cancel prescription' }}"
            class="dropdown-item {{ $isCancelled ? 'text-success' : 'text-warning' }} action-btn" role="menuitem" tabindex="-1">
            <i class="fas {{ $isCancelled ? 'fa-rotate-left' : 'fa-ban' }}"></i>
            <span>{{ $isCancelled ? 'Reactivate prescription' : 'Cancel prescription' }}</span>
        </button>
    </form>
    @endif

    {{-- Delete Button --}}
    @if ($canDelete)
    <button type="button"
        title="{{ __('messages.common.delete') }}"
        class="dropdown-item text-danger action-btn delete-prescription-btn"
        data-id="{{ $row->id }}" role="menuitem" tabindex="-1">
        <i class="fas fa-trash"></i>
        <span>{{ __('messages.common.delete') }}</span>
    </button>
    @endif
</x-row-actions>