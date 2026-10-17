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

<div class="d-flex align-items-center gap-2">
    {{-- Show Button --}}
    <a href="{{ $showUrl }}"
        title="{{ __('messages.common.view') }}"
        class="btn btn-sm btn-outline-info action-btn">
        <i class="fas fa-eye"></i>
    </a>

    {{-- Edit Button --}}
    @if ($canEdit)
    <a href="{{ $editUrl }}"
        title="{{ __('messages.common.edit') }}"
        class="btn btn-sm btn-outline-primary action-btn">
        <i class="fas fa-edit"></i>
    </a>
    @endif

    {{-- PDF Button --}}
    <a href="{{ $pdfUrl }}"
        title="{{ __('messages.prescription.download_pdf') }}"
        class="btn btn-sm btn-outline-secondary action-btn"
        target="_blank">
        <i class="fas fa-file-pdf"></i>
    </a>

    {{-- Cancel / Reactivate: a cancelled prescription leaves the pharmacy queue and cannot be dispensed.
         A plain form post, so it works without the compiled JavaScript. --}}
    @if ($canSwitch)
    <form action="{{ $statusUrl }}" method="POST" class="d-inline"
        onsubmit="return confirm('{{ $isCancelled ? 'Reactivate this prescription so the pharmacy can dispense it?' : 'Cancel this prescription? The pharmacy will no longer be able to dispense it.' }}')">
        @csrf
        <button type="submit"
            title="{{ $isCancelled ? 'Reactivate prescription' : 'Cancel prescription' }}"
            class="btn btn-sm {{ $isCancelled ? 'btn-outline-success' : 'btn-outline-warning' }} action-btn">
            <i class="fas {{ $isCancelled ? 'fa-rotate-left' : 'fa-ban' }}"></i>
        </button>
    </form>
    @endif

    {{-- Delete Button --}}
    @if ($canDelete)
    <button type="button"
        title="{{ __('messages.common.delete') }}"
        class="btn btn-sm btn-outline-danger action-btn delete-prescription-btn"
        data-id="{{ $row->id }}">
        <i class="fas fa-trash"></i>
    </button>
    @endif
</div>