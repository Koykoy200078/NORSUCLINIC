{{-- Staff and admin record a handout in Meds Only (a registered patient or a typed name, with college / course / year and one or many drugs).
     A doctor keeps the older pop-up here (the owner kept doctors' dispense-record buttons, 2026-11-14) and records Meds Only from its own page. --}}
@if (isRole('doctor'))
<a href="javascript:void(0)" onclick="bootstrap.Modal.getOrCreateInstance(document.getElementById('add_dispense_modal')).show()"
    class="btn btn-primary">Add Dispense Record</a>
@else
<a href="{{ getRouteByRole('meds-only.create') }}" class="btn btn-primary">Add Meds Only record</a>
@endif
