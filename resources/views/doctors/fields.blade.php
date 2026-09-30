<div class="row">
    <div class="col-md-6">
        <div class="mb-5">
            {{ Form::label('First Name',__('messages.doctor.first_name').':' ,['class' => 'form-label required']) }}
            {{ Form::text('first_name', null,['class' => 'form-control','placeholder' => __('messages.doctor.first_name'),'required']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ Form::label('Last Name',__('messages.doctor.last_name').':' ,['class' => 'form-label required']) }}
            {{ Form::text('last_name', null,['class' => 'form-control','placeholder' => __('messages.doctor.last_name'),'required']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ Form::label('Email',__('messages.user.email').':' ,['class' => 'form-label required']) }}
            {{ Form::email('email', null,['class' => 'form-control','placeholder' => __('messages.user.email')]) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ Form::label('employee_id', __('Employee ID').':' ,['class' => 'form-label required']) }}
            {{ Form::text('employee_id', null,['class' => 'form-control','placeholder' => __('Employee ID'),'required']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ Form::label('Contact',__('messages.user.contact_number').':' ,['class' => 'form-label']) }}
            {{ Form::text('contact', null,['class' => 'form-control','data-ph-phone' => 'true','placeholder' => __('messages.user.contact_number')]) }}
        </div>
    </div>
    <div class="col-md-6 mb-5">
        <div class="mb-1">
            {{ Form::label('password',__('messages.staff.password').':' ,['class' => 'form-label required']) }}
            <span data-bs-toggle="tooltip" title="{{ __('messages.flash.user_8_or') }}">
                <i class="fa fa-question-circle"></i>
            </span>
            <div class="mb-3 position-relative">
                {{Form::password('password',['class' => 'form-control','placeholder' => __('messages.staff.password'),'autocomplete' => 'off','required','aria-label'=>"Password",'data-toggle'=>"password"])}}
                <span class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 me-4 input-icon input-password-hide cursor-pointer text-gray-600"> <i class="bi bi-eye-slash-fill"></i> </span>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-5">
        <div class="mb-1">
            {{ Form::label('Confirm Password',__('messages.user.confirm_password').':' ,['class' => 'form-label required']) }}
            <span data-bs-toggle="tooltip" title="{{ __('messages.flash.user_8_or') }}">
                <i class="fa fa-question-circle"></i>
            </span>
            <div class="mb-3 position-relative">
                {{Form::password('password_confirmation',['class' => 'form-control','placeholder' => __('messages.user.confirm_password'),'autocomplete' => 'off','required','aria-label'=>"Password",'data-toggle'=>"password"])}}
                <span class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 me-4 input-icon input-password-hide cursor-pointer text-gray-600"> <i class="bi bi-eye-slash-fill"></i> </span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ Form::label('DOB',__('messages.doctor.dob').':' ,['class' => 'form-label']) }}
            {{ Form::text('dob', null,['class' => 'form-control doctor-dob','placeholder' => __('messages.doctor.dob'), 'id'=>'dob']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ Form::label('Specialization',__('messages.doctor.specialization').':' ,['class' => 'form-label required']) }}
            {{ Form::select('specializations[]',$specializations, null,['class' => 'io-select2 form-select', 'data-control'=>"select2", 'multiple', 'data-placeholder' => __('messages.doctor.specialization')]) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ Form::label('Experience',__('messages.doctor.experience').':' ,['class' => 'form-label']) }}
            {{ Form::number('experience', null,['class' => 'form-control','placeholder' => __('messages.doctor.experience'),'step'=>'any','min'=>'0']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ Form::label('prc_license_number', __('PRC/Medical License Number').':' ,['class' => 'form-label required']) }}
            {{ Form::text('prc_license_number', null,['class' => 'form-control','placeholder' => __('PRC/Medical License Number'),'required']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ Form::label('ptr_number', __('PTR Number').':' ,['class' => 'form-label required']) }}
            {{ Form::text('ptr_number', null,['class' => 'form-control','placeholder' => __('Professional Tax Receipt Number'),'required']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-5">
            {{ Form::label('s2_license_number', __('S2 License Number').':' ,['class' => 'form-label']) }}
            {{ Form::text('s2_license_number', null,['class' => 'form-control','placeholder' => __('S2 License Number (if applicable)')]) }}
        </div>
    </div>
    <div class="col-md-12">
        <div class="mb-5">
            {{ Form::label('consultation_hours', __('Consultation Hours').':' ,['class' => 'form-label required']) }}
            {{ Form::textarea('consultation_hours', null,['class' => 'form-control','rows' => 3,'placeholder' => __('e.g. Mon-Wed 8:00AM-12:00PM, Thu-Fri 1:00PM-5:00PM'),'required']) }}
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
        {{ Form::select('blood_type', $bloodGroup , null, ['class' => 'io-select2 form-select', 'data-control'=>"select2",'placeholder' => __('messages.patient.blood_type')]) }}
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
            {{ Form::label('Address1',__('messages.doctor.address1').':' ,['class' => 'form-label']) }}
            {{ Form::text('address1', null,['class' => 'form-control','placeholder' => __('messages.doctor.address1')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('Address2',__('messages.doctor.address2').':' ,['class' => 'form-label']) }}
            {{ Form::text('address2', null,['class' => 'form-control','placeholder' => __('messages.doctor.address2')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('Country',__('messages.doctor.country').':' ,['class' => 'form-label']) }}
            {{ Form::select('country_id', $country, $defaultCountryId ?? null,['class' => 'io-select2 form-select', 'data-control'=>"select2", 'id'=>'editDoctorCountryId','placeholder' => __('messages.doctor.country')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('State',__('messages.doctor.province').':' ,['class' => 'form-label']) }}
            {{ Form::select('state_id', [], null,['class' => 'io-select2 form-select', 'data-control'=>"select2", 'id'=> 'editDoctorStateId','placeholder' => __('messages.doctor.province')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('City',__('City/Municipality').':' ,['class' => 'form-label']) }}
            {{ Form::select('city_id', [], null,['class' => 'io-select2 form-select', 'data-control'=>'select2', 'id'=> 'editDoctorCityId','placeholder' => __('City/Municipality')]) }}
        </div>
        <div class="col-md-6 mb-5">
            <label class="form-label">{{__('messages.doctor.postal_code')}}:</label>
            {{ Form::text('postal_code',null,['class' => 'form-control','placeholder' => __('messages.doctor.postal_code')]) }}
        </div>
    </div>
    <div class="d-flex">
        {{ Form::submit(__('messages.common.save'),['class' => 'btn btn-primary me-2']) }}
        <a href="{{route('doctors.index')}}" type="reset"
            class="btn btn-secondary">{{__('messages.common.discard')}}</a>
    </div>