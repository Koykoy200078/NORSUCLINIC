@if(isRole('doctor') || isRole('clinic_admin'))
    @php($createRoute = isRole('doctor') ? 'doctors.prescriptions.create' : 'prescriptions.create')
    <a href="{{ route($createRoute, $this->appointMentId) }}" class="btn btn-primary">
        {{ __('messages.prescription.new_prescription') }}
    </a>
@endif