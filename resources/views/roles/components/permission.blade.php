<div class="d-flex flex-wrap gap-1">
    @forelse($row->permissions as $key => $permission)
        <span class="badge bg-{{ getBadgeColor($key) }} fs-7">{{$permission->display_name}}</span>
    @empty
        {{__('messages.common.n/a')}}
    @endforelse
</div>
