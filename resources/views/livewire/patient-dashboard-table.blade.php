<div>
    <div class="col-xl-12 col-md-6">
        <div class="dashbord-patient-card  widget mb-5">
            <div class="dashbord-patient-card-header">
                <h3>{{ __('messages.welcome_back') }}</h3>
                <span>{{ __('messages.dashboard') }}</span>
                <img src="" alt="">
            </div>
            <div class="dashbord-patient-card-body d-flex align-items-center justify-content-center">
                <div class="d-flex flex-column align-items-center py-3 py-sm-1">
                    <div class="image image-circle image-mini">
                        @if(getLogInUser()->hasRole('patient'))
                        <img class="img-fluid patient-card-img" alt="img-fluid"
                            src="{{ getLogInUser()->patient->profile }}" />
                        @elseif(getLogInUser()->hasRole('doctor'))
                        <img class="img-fluid patient-card-img" alt="img-fluid"
                            src="{{ getLogInUser()->profile_image }}" />
                        @else
                        <img class="img-fluid patient-card-img" alt="img-fluid"
                            src="{{ getLogInUser()->profile_image }}" />
                        @endif
                    </div>
                    <div class="patient-data text-center">
                        <h3 class="text-gray-900">{{ getLogInUser()->full_name }}</h3>
                        <h4 class="mb-0 fw-400 fs-6">{{ getLogInUser()->email }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>