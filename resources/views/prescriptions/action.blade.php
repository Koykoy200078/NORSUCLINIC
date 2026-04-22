@php
$medicineBill = App\Models\MedicineBill::whereModelType('App\Models\Prescription')->whereModelId($row->id)->first();
$showUrl = getRouteByRole('prescription.medicine.show', ['id' => $row->id]);
$editUrl = getRouteByRole('prescriptions.edit', ['prescription' => $row->id]);
$pdfUrl = getRouteByRole('prescriptions.pdf', ['id' => $row->id]);

$canEdit = isset($medicineBill->payment_status) && $medicineBill->payment_status == false;
@endphp

<div class="d-flex align-items-center gap-2">
    {{-- Show Button --}}
    <a href="{{ $showUrl }}"
        title="{{ __('messages.common.view') }}"
        class="btn btn-sm btn-outline-info action-btn">
        <i class="fas fa-eye"></i>
    </a>

    {{-- Edit Button - Only show if medicine bill is not paid --}}
    @if($canEdit)
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

    {{-- Delete Button --}}
    <button type="button"
        title="{{ __('messages.common.delete') }}"
        class="btn btn-sm btn-outline-danger action-btn delete-prescription-btn"
        data-id="{{ $row->id }}">
        <i class="fas fa-trash"></i>
    </button>
</div>