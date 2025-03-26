@extends('layouts.app')
@section('title')
{{ __('messages.user.profile_details') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <h1>{{ __('messages.user.edit_profile') }}</h1>
    </div>

    <div class="col-12">
        @include('layouts.errors')
        @include('flash::message')
    </div>
    <div class="card">
        <div class="card-body">
            <form id="profileForm" method="POST" action="{{ route('update.profile.setting') }}"
                enctype="multipart/form-data">
                {{ Form::hidden('is_edit', true, ['id' => 'staffProfileIsEdit']) }}
                {{ Form::hidden('is_edit', true, ['id' => 'patientProfileIsEdit']) }}
                {{ Form::hidden(
                        'edit_patient_country_id',
                        isset($patient->address->country_id) ? $patient->address->country_id : null,
                        ['id' => 'editPatientProfileCountryId'],
                    ) }}
                {{ Form::hidden(
                        'edit_patient_state_id',
                        isset($patient->address->state_id) ? $patient->address->state_id : null,
                        ['id' => 'editPatientProfileStateId'],
                    ) }}
                {{ Form::hidden('edit_patient_city_id', isset($patient->address->city_id) ? $patient->address->city_id : null, [
                        'id' => 'editPatientProfileCityId',
                    ]) }}
                @csrf
                @method('PUT')
                <div class="row mb-5">
                    <div class="col-lg-4">
                        <label for="exampleInputImage" class="form-label">{{__('messages.doctor.profile')}}:</label>
                    </div>
                    <div class="col-lg-8">
                        @php $styleCss = 'style' @endphp
                        <div class="mb-3" io-image-input="true">
                            <div class="d-block">
                                <div class="image-picker">
                                    <div class="image previewImage" id="exampleInputImage" {{ $styleCss }}="background-image: url('{{ (getLogInUser()->hasRole('patient')) ? getLogInUser()->patient->profile : $user->profile_image }}')
                                        ">
                                    </div>
                                    <span class="picker-edit rounded-circle text-gray-500 fs-small"
                                        data-bs-toggle="tooltip"
                                        data-bs-original-title="{{ __('messages.user.edit_profile') }}">
                                        <label>
                                            <i class="fa-solid fa-pen" id="profileImageIcon"></i>
                                            <input type="file" id="profilePicture" name="image"
                                                class="image-upload d-none profile-validation" accept="image/*" />
                                        </label>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-5">
                    <label class="col-lg-4 form-label required">{{ __('messages.user.full_name').':' }}</label>
                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-lg-4 mb-2">
                                {{ Form::text('first_name', $user->first_name, ['class'=> 'form-control', 'placeholder' => __('messages.patient.first_name'), 'required']) }}
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-lg-4 mb-2">
                                {{ Form::text('middle_name', $user->middle_name, ['class'=> 'form-control', 'placeholder' =>__('messages.patient.middle_name')]) }}
                            </div>
                            <div class="col-lg-4">
                                {{ Form::text('last_name', $user->last_name, ['class'=> 'form-control', 'placeholder' =>__('messages.patient.last_name'), 'required']) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-5">
                    {{ Form::label('gender', __('messages.staff.gender') . ':', ['class' => 'col-lg-4 form-label required']) }}
                    <div class="col-lg-8">
                        <span class="is-valid">
                            <input class="form-check-input" type="radio" name="gender" value="1" checked
                                {{ !empty($user) && $user->gender === 1 ? 'checked' : '' }}>
                            <label class="form-label">{{ __('messages.staff.male') }}</label>&nbsp;&nbsp;
                            <input class="form-check-input" type="radio" name="gender" value="2"
                                {{ !empty($user) && $user->gender === 2 ? 'checked' : '' }}>
                            <label class="form-label">{{ __('messages.staff.female') }}</label>
                        </span>
                    </div>
                </div>

                <div class="row mb-5">
                    {{ Form::label('dob',__('messages.patient.dob').':' ,['class' => 'col-lg-4 form-label']) }}
                    <div class="col-lg-8">
                        {{ Form::text('dob',!empty($patient->user) ? $patient->user->dob : null,['class' => 'form-control patient-dob','id' => __('messages.patient.dob'), 'placeholder' => __('messages.doctor.select_dob')]) }}
                    </div>
                </div>
                <div class="row mb-5">
                    <label class="form-label col-lg-4">{{ __('messages.patient.blood_type').':' }}</label>
                    <div class="col-lg-8">
                        {{ Form::select('blood_type', $data['bloodGroupList'] ,!empty($patient->user) ? $patient->user->blood_type : null, ['placeholder' => __('messages.patient.select_blood_group'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Blood Type",'data-control'=>'select2']) }}
                    </div>
                </div>
                <div class="row mb-7">
                    {{ Form::label('vaccination_id',__('messages.patient.vaccination_status').':',['class'=>'col-lg-4 form-label']) }}
                    <div class="col-lg-8">
                        {{ Form::select('vaccination_id', $data['vaccination_data'] ,!empty($patient->user) ? $patient->user->vaccination_id : null, ['placeholder' => __('messages.patient.vaccination_status'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Vaccination Status",'data-control'=>'select2']) }}
                    </div>
                </div>

                <div class="row mb-5">
                    <label class="col-lg-4 form-label required">{{ __('messages.user.email').':' }}</label>
                    <div class="col-lg-8">
                        {{ Form::email('email', $user->email, ['class'=> 'form-control', 'placeholder' => __('messages.user.email'), 'required']) }}
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 form-label required">{{ __('messages.user.time_zone').':' }}
                    </label>
                    <div class="col-lg-8">
                        {{ Form::select('time_zone', App\Models\User::TIME_ZONE_ARRAY, $user->time_zone,['class'=> 'form-control io-select2', 'placeholder' => __('messages.user.select_time_zone'), 'required', 'data-control'=>'select2',]) }}
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-lg-4">
                        <label class="col-lg-4 form-label required">{{ __('messages.user.contact_number').':' }}</label>
                    </div>
                    <div class="col-lg-8">
                        {{ Form::tel('contact', $user->contact ? '+' . $user->country_code . $user->contact : null, ['id' => 'phoneNumber', 'class' => 'form-control', 'placeholder' => __('messages.user.contact_number'), 'onkeyup' => 'if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,"")']) }}
                        {{ Form::hidden('country_code', !empty($user->user) ? $user->country_code : null, ['id' => 'prefix_code']) }}
                        <span id="valid-msg"
                            class="text-success d-none fw-400 fs-small mt-2">{{ __('messages.valid_number') }}</span>
                        <span id="error-msg"
                            class="text-danger d-none fw-400 fs-small mt-2">{{ __('messages.invalid_number') }}</span>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-lg-4">
                        <label class="col-lg-4 form-label required">{{ __('messages.patient.emergency_contact_name').':' }}</label>
                    </div>
                    <div class="col-lg-8">
                        {{ Form::text('emergency_contact_name',!empty($patient->user) ? $patient->user->emergency_contact_name : null,['class' => 'form-control','placeholder' => __('messages.patient.emergency_contact_name'),'required']) }}
                    </div>

                    <div class="col-lg-4">
                        <label class="col-lg-4 form-label required">{{ __('messages.patient.emergency_contact_no').':' }}
                    </div>

                    <div class="col-lg-8">
                        {{ Form::text('emergency_contact_no',!empty($patient->user) ? $patient->user->emergency_contact_no : null,['class' => 'form-control','placeholder' => __('messages.patient.emergency_contact_no'),'required']) }}
                    </div>
                </div>

                <!-- Address Information -->
                <div class="row">
                    <div class="fw-bolder fs-3 mb-7 mt-5">{{ __('messages.patient.address_information') }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ Form::label('address1',__('messages.patient.address1').':' ,['class' => 'form-label']) }}
                        {{ Form::text('address1',!empty($patient->address) ? $patient->address->address1 : null,['class' => 'form-control','placeholder' => __('messages.patient.address1')]) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ Form::label('address2',__('messages.patient.address2').':' ,['class' => 'form-label']) }}
                        {{ Form::text('address2',!empty($patient->address) ? $patient->address->address2 : null,['class' => 'form-control','placeholder' => __('messages.patient.address2')]) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ Form::label('country_id',__('messages.country.country').':',['class'=>'form-label']) }}
                        {{ Form::select('country_id', $data['countries'] ,null, ['id' => 'patientCountryId','data-placeholder' => __('messages.country.country'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Country",
        'data-control'=>'select2']) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ Form::label('state_id',__('messages.state.state').':',['class'=>'form-label']) }}
                        {{ Form::select('state_id', $data['provinces'] ,!empty($patient->address) ? $patient->address->state_id : null, ['placeholder' => __('messages.patient.select_state'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Province",'data-control'=>'select2']) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ Form::label('city_id',__('messages.city.city').':',['class'=>'form-label']) }}
                        {{ Form::select('city_id', $data['cities'] ,!empty($patient->address) ? $patient->address->city_id : null, ['placeholder' => __('messages.patient.select_city'),'class' => 'form-select io-select2', 'aria-label'=>"Select a City",'data-control'=>'select2']) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ Form::label('postalCode',__('messages.patient.postal_code').':' ,['class' => 'form-label']) }}
                        {{ Form::text('postal_code',!empty($patient->address) ? $patient->address->postal_code : null,['class' => 'form-control','placeholder' => __('messages.patient.postal_code')]) }}
                    </div>
                </div>

                <!-- Student Information -->
                <div class="fw-bolder fs-3 mb-7 mt-5">{{ __('messages.student.student_information') }}</div>
                <div class="row">
                    <div class="col-md-6 mb-7">
                        {{ Form::label('campus_id',__('messages.student.campus').':',['class'=>'form-label']) }}
                        {{ Form::select('campus_id', $data['campuses'] ,!empty($patient->user) ? $patient->user->campus_id : null, ['placeholder' => __('messages.student.select_campus'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Campus",'data-control'=>'select2']) }}
                    </div>

                    <div class="col-md-6 mb-7">
                        {{ Form::label('college_id',__('messages.student.college').':',['class'=>'form-label']) }}
                        {{ Form::select('college_id', $data['colleges'] ,!empty($patient->user) ? $patient->user->college_id : null, ['placeholder' => __('messages.student.select_college'),'class' => 'form-select io-select2', 'aria-label'=>"Select a College",'data-control'=>'select2']) }}
                    </div>

                    <div class="col-md-6 mb-7">
                        {{ Form::label('course_id',__('messages.student.course').':',['class'=>'form-label']) }}
                        {{ Form::select('course_id', $data['courses'] ,!empty($patient->user) ? $patient->user->course_id : null, ['placeholder' => __('messages.student.select_course'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Course",'data-control'=>'select2']) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ Form::label('year_level_id', __('messages.student.year_level').':',['class'=>'form-label']) }}
                        {{ Form::select('year_level_id', $data['year_levels'], !empty($patient->user) ? $patient->user->year_level_id : null, ['placeholder' => __('messages.student.select_year_level'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Year Level",'data-control'=>'select2']) }}
                    </div>
                </div>

                <div class="d-flex py-6">
                    {{ Form::submit(__('messages.common.save'), ['class' => 'btn btn-primary me-2']) }}
                </div>
            </form>
        </div>
    </div>
</div>
@endsection