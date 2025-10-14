<div class="d-flex align-items-center mt-2">
    @if(isRole('doctor'))
    {{-- For doctors, just show the name without link since they have read-only access --}}
    <span>{{$row->name}}</span>
    @else
    <a href="{{
            isRole('clinic_admin') ? route('brands.show', $row->id) : 
            (isRole('staff') ? route('staff.brands.show', $row->id) : route('brands.show', $row->id))
        }}" class="text-decoration-none">{{$row->name}}</a>
    @endif
</div>
