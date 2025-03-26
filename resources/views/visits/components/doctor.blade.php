<div class="d-flex align-items-center">
    <a href="{{route('doctors.show', $row->id)}}">
        <div class="image image-circle image-mini me-3">
            <img src="{{$row->visitDoctor->user->profile_image}}" alt="user" class="user-img">
        </div>
    </a>
    <div class="d-flex flex-column">
        <div class="d-inline-block align-top">
            <div class="d-inline-block align-self-center d-flex">
                <a href="{{route('doctors.show', $row->doctor_id)}}" class="mb-1 text-decoration-none fs-6">
                    {{$row->visitDoctor->user->full_name}}
                </a>
            </div>
        </div>
        <span class="fs-6">{{$row->visitDoctor->user->email}}</span>
    </div>
</div>