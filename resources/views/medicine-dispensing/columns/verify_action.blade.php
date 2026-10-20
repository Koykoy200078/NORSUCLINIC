@php
$showRoute = getRouteNameByRole('prescription.medicine.show');

$dispenseRoute = getRouteNameByRole('prescriptions.dispense');

$canDispense = (isRole('clinic_admin') || isRole('staff')) && canUseModule('dispensing') && canUseModule('prescriptions')
&& $row->status === \App\Models\Prescription::DISPENSE_STATUS_PENDING
&& ($row->is_active ?? true);
@endphp

<x-row-actions module="dispensing">
    @if(canUseModule('prescriptions'))
    <a href="{{ route($showRoute, $row->id) }}"
        title="Verify Prescription"
        class="dropdown-item text-info action-btn" role="menuitem" tabindex="-1">
        <i class="fas fa-eye"></i>
        <span>Verify Prescription</span>
    </a>
    @endif

    @if($canDispense)
    <form action="{{ route($dispenseRoute, $row->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit"
            title="Dispense Medicine"
            class="dropdown-item text-success action-btn"
            onclick="return confirm('Dispense this prescription to the patient and update stock now?')" role="menuitem" tabindex="-1">
            <i class="fas fa-check-circle"></i>
            <span>Dispense Medicine</span>
        </button>
    </form>
    @endif
</x-row-actions>
