@php
$showRoute = isRole('doctor')
? 'doctors.prescription.medicine.show'
: (isRole('staff') ? 'staff.prescription.medicine.show' : 'prescription.medicine.show');

$dispenseRoute = isRole('doctor')
? 'doctors.prescriptions.dispense'
: (isRole('staff') ? 'staff.prescriptions.dispense' : 'prescriptions.dispense');

$canDispense = (isRole('clinic_admin') || isRole('staff'))
&& $row->status === \App\Models\Prescription::DISPENSE_STATUS_PENDING
&& ($row->is_active ?? true);
@endphp

<div class="d-flex align-items-center gap-2">
    <a href="{{ route($showRoute, $row->id) }}"
        title="Verify Prescription"
        class="btn btn-sm btn-outline-info action-btn">
        <i class="fas fa-eye"></i>
    </a>

    @if($canDispense)
    <form action="{{ route($dispenseRoute, $row->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit"
            title="Dispense Medicine"
            class="btn btn-sm btn-outline-success action-btn"
            onclick="return confirm('Dispense this prescription to the patient and update stock now?')">
            <i class="fas fa-check-circle"></i>
        </button>
    </form>
    @endif
</div>