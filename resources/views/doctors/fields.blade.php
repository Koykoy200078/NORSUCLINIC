<div class="row">
    <div class="col-md-6">
        <div class="mb-5">
            {{ html()->label()->for('First Name')->text(__('messages.doctor.first_name').':')->attributes(['class' => 'form-label required']) }}
            {{ html()->text('first_name', null)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.doctor.first_name'), 'required']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ html()->label()->for('Last Name')->text(__('messages.doctor.last_name').':')->attributes(['class' => 'form-label required']) }}
            {{ html()->text('last_name', null)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.doctor.last_name'), 'required']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ html()->label()->for('Email')->text(__('messages.user.email').':')->attributes(['class' => 'form-label required']) }}
            {{ html()->email('email', null)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.user.email')]) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ html()->label()->for('employee_id')->text(__('Employee ID').':')->attributes(['class' => 'form-label required']) }}
            {{ html()->text('employee_id', null)->attributes(['class' => 'form-control', 'placeholder' => __('Employee ID'), 'required']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ html()->label()->for('Contact')->text(__('messages.user.contact_number').':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('contact', null)->forgetAttribute('id')->attributes(['class' => 'form-control', 'data-ph-phone' => 'true', 'placeholder' => __('messages.user.contact_number')]) }}
        </div>
    </div>
    <div class="col-md-6 mb-5">
        <div class="mb-1">
            {{ html()->label()->for('password')->text(__('messages.staff.password').':')->attributes(['class' => 'form-label required']) }}
            <span data-bs-toggle="tooltip" title="{{ __('messages.flash.user_8_or') }}">
                <i class="fa fa-question-circle"></i>
            </span>
            <div class="mb-3 position-relative">
                {{html()->password('password')->attributes(['class' => 'form-control', 'placeholder' => __('messages.staff.password'), 'autocomplete' => 'off', 'required', 'aria-label'=>"Password", 'data-toggle'=>"password"])}}
                <span class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 me-4 input-icon input-password-hide cursor-pointer text-gray-600"> <i class="bi bi-eye-slash-fill"></i> </span>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-5">
        <div class="mb-1">
            {{ html()->label()->for('Confirm Password')->text(__('messages.user.confirm_password').':')->attributes(['class' => 'form-label required']) }}
            <span data-bs-toggle="tooltip" title="{{ __('messages.flash.user_8_or') }}">
                <i class="fa fa-question-circle"></i>
            </span>
            <div class="mb-3 position-relative">
                {{html()->password('password_confirmation')->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.user.confirm_password'), 'autocomplete' => 'off', 'required', 'aria-label'=>"Password", 'data-toggle'=>"password"])}}
                <span class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 me-4 input-icon input-password-hide cursor-pointer text-gray-600"> <i class="bi bi-eye-slash-fill"></i> </span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ html()->label()->for('DOB')->text(__('messages.doctor.dob').':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('dob', null)->attributes(['class' => 'form-control doctor-dob', 'placeholder' => __('messages.doctor.dob'), 'id'=>'dob']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ html()->label()->for('Specialization')->text(__('messages.doctor.specialization').':')->attributes(['class' => 'form-label required']) }}
            {{ html()->select('specializations[]', $specializations, old('specializations', null))->multiple()->forgetAttribute('id')->attributes(['class' => 'io-select2 form-select', 'data-control'=>"select2", 'data-placeholder' => __('messages.doctor.specialization')]) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ html()->label()->for('Experience')->text(__('messages.doctor.experience').':')->attributes(['class' => 'form-label']) }}
            {{ html()->number('experience', null)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.doctor.experience'), 'step'=>'any', 'min'=>'0']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ html()->label()->for('prc_license_number')->text(__('PRC/Medical License Number').':')->attributes(['class' => 'form-label required']) }}
            {{ html()->text('prc_license_number', null)->attributes(['class' => 'form-control', 'placeholder' => __('PRC/Medical License Number'), 'required']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ html()->label()->for('ptr_number')->text(__('PTR Number').':')->attributes(['class' => 'form-label required']) }}
            {{ html()->text('ptr_number', null)->attributes(['class' => 'form-control', 'placeholder' => __('Professional Tax Receipt Number'), 'required']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ html()->label()->for('s2_license_number')->text(__('S2 License Number').':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('s2_license_number', null)->attributes(['class' => 'form-control', 'placeholder' => __('S2 License Number (if applicable)')]) }}
        </div>
    </div>
    <div class="col-md-12">
        <div class="mb-5">
            {{ html()->label()->for('consultation_hours')->text(__('Consultation Hours').':')->attributes(['class' => 'form-label required']) }}
            {{ html()->textarea('consultation_hours')->text(html()->value('consultation_hours', null))->attributes(['cols' => 50])->attributes(['class' => 'form-control', 'rows' => 3, 'placeholder' => __('e.g. Mon-Wed 8:00AM-12:00PM, Thu-Fri 1:00PM-5:00PM'), 'required']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            <label class="form-label required">
                {{__('messages.doctor.select_gender')}}
                :
            </label>
            <span class="is-valid">
                <div class="mt-2">
                    <input class="form-check-input" type="radio" checked name="gender" value="1">
                    <label class="form-label mr-3">{{__('messages.doctor.male')}}</label>
                    <input class="form-check-input ms-2" type="radio" name="gender" value="2">
                    <label class="form-label mr-3">{{__('messages.doctor.female')}}</label>
                </div>
            </span>
        </div>
    </div>
    <div class="col-md-6 mb-5">
        <label class="form-label">{{ __('messages.patient.blood_type').':' }}</label>
        {{ html()->select('blood_type', $bloodGroup, null)->placeholder(__('messages.patient.blood_type'))->forgetAttribute('id')->attributes(['class' => 'io-select2 form-select', 'data-control'=>"select2"]) }}
    </div>
    <div class="col-lg-6 d-none">
        <div class="mb-5">
            <div class="mb-3" io-image-input="true">
                <label for="exampleInputImage" class="form-label">{{__('messages.doctor.profile')}}:</label>
                <div class="d-block">
                    <div class="image-picker">
                        <div class="image previewImage" id="exampleInputImage" style="background-image: url({{ asset('web/media/avatars/male.png') }})">
                        </div>
                        <span class="picker-edit rounded-circle text-gray-500 fs-small" data-bs-toggle="tooltip"
                            data-placement="top" data-bs-original-title="{{ __('messages.user.edit_profile') }}">
                            <label>
                                <i class="fa-solid fa-pen" id="profileImageIcon"></i>
                                <input type="file" id="profilePicture" name="profile" class="image-upload d-none profile-validation" accept="image/*" />
                            </label>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-5 d-none">
        <label class="form-label">{{__('messages.doctor.status')}}:</label>
        <div class="col-lg-8">
            <div class="form-check form-check-solid form-switch">
                <input tabindex="12" name="status" value="0" class="form-check-input" type="checkbox"
                    id="allowmarketing" checked="checked">
                <label class="form-check-label" for="allowmarketing"></label>
            </div>
        </div>
    </div>
    <div class="fw-bolder fs-3 rotate collapsible mb-7">
        {{__('messages.doctor.address_information')}}
    </div>
    <div class="row gx-10 mb-5">
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('Address1')->text(__('messages.doctor.address1').':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('address1', null)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.doctor.address1')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('Address2')->text(__('messages.doctor.address2').':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('address2', null)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.doctor.address2')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('Country')->text(__('messages.doctor.country').':')->attributes(['class' => 'form-label']) }}
            {{ html()->select('country_id', $country, $defaultCountryId ?? null)->placeholder(__('messages.doctor.country'))->attributes(['class' => 'io-select2 form-select', 'data-control'=>"select2", 'id'=>'editDoctorCountryId']) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('State')->text(__('messages.doctor.province').':')->attributes(['class' => 'form-label']) }}
            {{ html()->select('state_id', [], null)->placeholder(__('messages.doctor.province'))->attributes(['class' => 'io-select2 form-select', 'data-control'=>"select2", 'id'=> 'editDoctorStateId']) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('City')->text(__('City/Municipality').':')->attributes(['class' => 'form-label']) }}
            {{ html()->select('city_id', [], null)->placeholder(__('City/Municipality'))->attributes(['class' => 'io-select2 form-select', 'data-control'=>'select2', 'id'=> 'editDoctorCityId']) }}
        </div>
        <div class="col-md-6 mb-5">
            <label class="form-label">{{__('messages.doctor.postal_code')}}:</label>
            {{ html()->text('postal_code', null)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.doctor.postal_code')]) }}
        </div>
    </div>
    <div class="d-flex">
        {{ html()->input('submit', null, __('messages.common.save'))->attributes(['class' => 'btn btn-primary me-2']) }}
        <a href="{{ getRouteByRole('doctors.index') }}" type="reset"
            class="btn btn-secondary">{{__('messages.common.discard')}}</a>
    </div>