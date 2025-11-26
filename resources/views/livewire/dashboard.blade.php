<div>
    <div class="row">
        {{-- Welcome Card --}}
        <div class="col-xl-12 col-md-6">
            <div class="dashbord-doctor-card widget mb-5">
                <div class="dashbord-doctor-card-header">
                    <h3>{{ __('messages.welcome_back') }}</h3>
                    <span>{{ __('messages.dashboard') }}</span>
                </div>
                <div class="dashbord-doctor-card-body d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center py-3 py-sm-1">
                        <div class="image image-circle image-mini">
                            @if(getLogInUser()->hasRole('patient'))
                            <img class="img-fluid doctor-card-img" alt="img-fluid"
                                src="{{ getLogInUser()->patient->profile }}" />
                            @elseif(getLogInUser()->hasRole('doctor'))
                            <img class="img-fluid doctor-card-img" alt="img-fluid"
                                src="{{ getLogInUser()->profile_image }}" />
                            @else
                            <img class="img-fluid doctor-card-img" alt="img-fluid"
                                src="{{ getLogInUser()->profile_image }}" />
                            @endif
                        </div>
                        <div class="doctor-data text-center">
                            <h3 class="text-gray-900">{{ getLogInUser()->full_name }}</h3>
                            <h4 class="mb-0 fw-400 fs-6">{{ getLogInUser()->email }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="bg-white rounded-10 shadow-sm mb-5 p-5">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2 fw-semibold fs-6">{{ __('messages.purchase_medicine.total') . ' ' . __('messages.common.active') . ' ' . __('messages.doctors') }}</h6>
                        <h2 class="mb-0 fw-bolder text-primary">{{ $totalDoctorCount }}</h2>
                    </div>
                    <div class="ms-3">
                        <div class="bg-light-primary rounded-circle p-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-user-md fa-lg text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="bg-white rounded-10 shadow-sm mb-5 p-5">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2 fw-semibold fs-6">{{ __('messages.admin_dashboard.total_patients') }}</h6>
                        <h2 class="mb-0 fw-bolder text-success">{{ $totalPatientCount }}</h2>
                    </div>
                    <div class="ms-3">
                        <div class="bg-light-success rounded-circle p-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-users fa-lg text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="bg-white rounded-10 shadow-sm mb-5 p-5">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2 fw-semibold fs-6">{{ __('messages.admin_dashboard.today_appointments') }}</h6>
                        <h2 class="mb-0 fw-bolder text-info">{{ $todayAppointmentCount }}</h2>
                    </div>
                    <div class="ms-3">
                        <div class="bg-light-info rounded-circle p-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-calendar-check fa-lg text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="bg-white rounded-10 shadow-sm mb-5 p-5">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2 fw-semibold fs-6">{{ __('messages.admin_dashboard.today_registered_patients') }}</h6>
                        <h2 class="mb-0 fw-bolder text-warning">{{ $totalRegisteredPatientCount }}</h2>
                    </div>
                    <div class="ms-3">
                        <div class="bg-light-warning rounded-circle p-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-user-plus fa-lg text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>