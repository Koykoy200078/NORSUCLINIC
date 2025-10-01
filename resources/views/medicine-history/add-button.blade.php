@if(!isRole('doctor'))
<a href="{{ route('medicine-history.create') }}" class="btn btn-primary">{{__('messages.medicine_bills.add_medicine_bill')}}</a>
@endif