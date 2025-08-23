<div class="d-flex align-items-center">
    <a href="{{ 
        isRole('clinic_admin') ? route('patients.show', $row->id) : 
        (isRole('staff') ? route('staff.patients.show', $row->id) : 
        (isRole('doctor') ? route('doctors.patients.show', $row->id) : route('patients.show', $row->id)))
    }}">
        <div class="image image-circle image-mini me-3">
            <img src="{{$row->profile}}" alt="user" class="user-img">
        </div>
    </a>
    <div class="d-flex flex-column">
        <a href="{{ 
            isRole('clinic_admin') ? route('patients.show', $row->id) : 
            (isRole('staff') ? route('staff.patients.show', $row->id) : 
            (isRole('doctor') ? route('doctors.patients.show', $row->id) : route('patients.show', $row->id)))
        }}" class="mb-1 text-decoration-none fs-6">
            {{$row->user->first_name.' '.$row->user->last_name}}
        </a>
        <span class="fs-6">{{$row->user->email}}</span>
    </div>
</div>