@if(!isRole('doctor'))
<a href="{{ isRole('clinic_admin') ? route('medicine-history.create') : (isRole('staff') ? route('staff.medicine-history.create') : route('doctors.medicine-history.create')) }}" class="btn btn-primary">{{__('messages.medicine_bills.add_medicine_bill')}}</a>
@endif