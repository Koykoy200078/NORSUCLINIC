@php
$medicineBill = App\Models\MedicineBill::whereModelType('App\Models\Prescription')->whereModelId($row->id)->first();
$showRoute = isRole('doctor')
? 'doctors.prescription.medicine.show'
: (isRole('staff') ? 'staff.prescription.medicine.show' : (isRole('patient') ? 'patients.prescription.medicine.show' : 'prescription.medicine.show'));
$editRoute = isRole('doctor')
? 'doctors.prescriptions.edit'
: (isRole('staff') ? 'staff.prescriptions.edit' : (isRole('patient') ? 'patients.prescriptions.edit' : 'prescriptions.edit'));
$pdfRoute = isRole('doctor')
? 'doctors.prescriptions.pdf'
: (isRole('staff') ? 'staff.prescriptions.pdf' : (isRole('patient') ? 'patients.prescriptions.pdf' : 'prescriptions.pdf'));
$dispenseRoute = isRole('doctor')
? 'doctors.prescriptions.dispense'
: (isRole('staff') ? 'staff.prescriptions.dispense' : (isRole('patient') ? '' : 'prescriptions.dispense'));

$canEdit = isset($medicineBill->payment_status) && $medicineBill->payment_status == false;
$canDispense = (isRole('clinic_admin') || isRole('staff'))
&& $row->status === \App\Models\Prescription::DISPENSE_STATUS_PENDING;
@endphp

<div class="d-flex align-items-center gap-2">
    {{-- Show Button --}}
    <a href="{{ route($showRoute, $row->id) }}"
        title="{{ __('messages.common.view') }}"
        class="btn btn-sm btn-outline-info action-btn">
        <i class="fas fa-eye"></i>
    </a>

    {{-- Edit Button - Only show if medicine bill is not paid --}}
    @if($canEdit)
    <a href="{{ route($editRoute, $row->id) }}"
        title="{{ __('messages.common.edit') }}"
        class="btn btn-sm btn-outline-primary action-btn">
        <i class="fas fa-edit"></i>
    </a>
    @endif

    {{-- PDF Button --}}
    <a href="{{ route($pdfRoute, $row->id) }}"
        title="{{ __('messages.common.download_pdf') }}"
        class="btn btn-sm btn-outline-secondary action-btn"
        target="_blank">
        <i class="fas fa-file-pdf"></i>
    </a>

    @if($canDispense)
    <form action="{{ route($dispenseRoute, $row->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit"
            title="Dispense"
            class="btn btn-sm btn-outline-success action-btn"
            onclick="return confirm('Dispense this prescription and deduct stock using FEFO?')">
            <i class="fas fa-check-circle"></i>
        </button>
    </form>
    @endif

    {{-- Delete Button --}}
    <button type="button"
        title="{{ __('messages.common.delete') }}"
        class="btn btn-sm btn-outline-danger action-btn delete-prescription-btn"
        data-id="{{ $row->id }}">
        <i class="fas fa-trash"></i>
    </button>
</div>