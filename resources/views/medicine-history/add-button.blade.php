{{-- Staff and admin record a handout in Meds Only (a registered patient or a typed name, with college / course / year and one or many drugs).
     A doctor cannot use that form, so keeps the older pop-up. --}}
@if (isRole('doctor'))
<a href="javascript:void(0)" onclick="bootstrap.Modal.getOrCreateInstance(document.getElementById('add_dispense_modal')).show()"
    class="btn btn-primary">Add Dispense Record</a>
@else
<a href="{{ getRouteByRole('meds-only.create') }}" class="btn btn-primary">Add Meds Only record</a>
@endif
