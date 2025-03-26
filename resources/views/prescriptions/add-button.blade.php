@php
$createRoute = match (true) {
isRole('doctor') => 'doctors.prescriptions.create',
isRole('patient') => 'patients.prescriptions.create',
default => 'prescriptions.create',
};
@endphp

<a href="{{ route($createRoute, $this->appointMentId) }}" class="btn btn-primary">
    {{ __('messages.prescription.new_prescription') }}
</a>