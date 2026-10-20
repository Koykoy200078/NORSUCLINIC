<div class="d-flex align-items-center mt-2">
    @if(isRole('doctor'))
    {{-- For doctors, just show the name without link since they have read-only access --}}
    <span>{{$row->name}}</span>
    @else
    <a href="{{
            getRouteByRole('generics.show', [$row->id])
        }}" class="text-decoration-none">{{$row->name}}</a>
    @endif
</div>