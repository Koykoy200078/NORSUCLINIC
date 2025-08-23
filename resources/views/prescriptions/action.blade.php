@php
$medicineBill = App\Models\MedicineBill::whereModelType('App\Models\Prescription')->whereModelId($row->id)->first();
$showRoute = isRole('doctor') ? 'doctors.prescription.medicine.show' : (isRole('patient') ? 'patients.prescription.medicine.show' :'prescription.medicine.show');
$editRoute = isRole('doctor') ? 'doctors.prescriptions.edit' : (isRole('patient') ? 'patients.prescriptions.edit' :'prescriptions.edit');
$pdfRoute = isRole('doctor') ? 'doctors.prescriptions.pdf' : (isRole('patient') ? 'patients.prescriptions.pdf' :'prescriptions.pdf');

$routes = [
'show' => $showRoute,
'pdf' => $pdfRoute
];

// Only add edit route if medicine bill is not paid
if(isset($medicineBill->payment_status) && $medicineBill->payment_status == false) {
$routes['edit'] = $editRoute;
}

$extraActions = [
[
'route' => 'javascript:void(0)',
'icon' => 'fa-solid fa-trash',
'color' => 'danger',
'title' => __('messages.common.delete'),
'class' => 'delete-prescription-btn',
'data-id' => $row->id
]
];
@endphp

<x-crud.action-buttons
    :model="$row"
    :routes="$routes"
    :extra-actions="$extraActions"
    size="sm" />

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle prescription delete
        const deleteButtons = document.querySelectorAll('.delete-prescription-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const prescriptionId = this.dataset.id;
                // Add your delete prescription logic here
                console.log('Delete prescription:', prescriptionId);
            });
        });
    });
</script>
@endpush