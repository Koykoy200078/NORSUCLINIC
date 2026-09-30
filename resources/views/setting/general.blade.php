@extends('layouts.app')
@section('title')
{{__('messages.settings')}}
@endsection
@section('content')
<div class="container-fluid">
    {{ Form::open(['route' => 'setting.update', 'files' => true,'id' => 'generalSettingForm']) }}
    <div class="d-flex flex-column">
        @include('setting.setting_menu')
        {{ Form::hidden('sectionName', $sectionName) }}
        {{ Form::hidden('setting_country_id', false,['id' => 'settingCountryId']) }}
        {{ Form::hidden('setting_state_id', false,['id' => 'settingStateId']) }}
        {{ Form::hidden('setting_city_id', false,['id' => 'settingCityId']) }}
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-center">
                    <h3 class="m-0">{{__('messages.setting.general_details')}}
                    </h3>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-6">
                    {{ Form::label('clinic_name',__('messages.setting.clinic_name').':',
                                     ['class'=>'col-lg-4 form-label required']) }}
                    <div class="col-lg-8">
                        {{ Form::text('clinic_name', $setting['clinic_name'] ?? null, ['class' => 'form-control','placeholder'=>__('messages.setting.clinic_name'),'required']) }}
                    </div>
                </div>
                <!-- Landline Number -->
                <div class="row mb-6">
                    {{ Form::label('landline_no', __('Landline No').':', ['class' => 'col-lg-4 form-label']) }}
                    <div class="col-lg-8">
                        {{ Form::text('landline_no', $setting['landline_no'] ?? null, ['class' => 'form-control', 'placeholder' => __('Input Landline Number'), 'id' => 'landlineNumber']) }}
                    </div>
                </div>
                <!-- End of landline number -->
                <div class="row mb-6">
                    {{ Form::label('contact_no', __('messages.patient.contact_no').':', ['class' => 'col-lg-4 form-label required']) }}
                    <div class="col-lg-8">
                        {{ Form::text('contact_no', $setting['contact_no'] ?? null, ['class' => 'form-control', 'data-ph-phone' => 'true', 'placeholder' => __('messages.patient.contact_no'), 'id'=>'phoneNumber']) }}
                    </div>
                </div>
                <div class="row mb-6">
                    {{ Form::label('default_country_code', __('messages.setting.default_country_code').':', ['class' => 'col-lg-4 form-label']) }}
                    <div class="col-lg-8">
                        {{-- The clinic works with Philippine numbers only, so the country is fixed. --}}
                        <input type="text" class="form-control" value="{{ __('Philippines') }} (+63)" readonly disabled>
                    </div>
                </div>
                <div class="row mb-6">
                    {{ Form::label('email',__('messages.user.email').':',['class'=>'col-lg-4 form-label required']) }}
                    <div class="col-lg-8">
                        {{ Form::email('email', $setting['email'] ?? null, ['class' => 'form-control ','placeholder'=>__('messages.user.email'),'required']) }}
                    </div>
                </div>
                <div class="row mb-6">
                    {{ Form::label('specialties',__('messages.setting.specialties').':', ['class'=>'col-lg-4 form-label required']) }}
                    <div class="col-lg-8">
                        {{ Form::select('specialties[]', $specialties, json_decode($setting['specialties'] ?? '[]'), ['multiple', 'class' => 'form-select', 'aria-label'=>"Select a Country", 'data-control'=>'select2','required']) }}
                    </div>
                </div>
                <div class="row mb-6">
                    <label for="appLogoPreview" class="col-lg-4 required form-label">{{ __('messages.setting.logo').':'}}</label>
                    <div class="col-lg-8">
                        <div class="mb-3" io-image-input="true">
                            <div class="d-block">
                                <div class="image-picker">
                                    <div class="image previewImage" id="appLogoPreview"
                                        style="background-image: url('{{(!empty($setting['logo']))?asset($setting['logo']):asset('assets/image/norsu_favicon.ico')}}')">
                                    </div>
                                    <span class="picker-edit rounded-circle text-gray-500 fs-small"
                                        data-bs-toggle="tooltip"
                                        data-placement="top" data-bs-original-title="{{ __('messages.setting.change_app_logo') }}">
                                        <label>
                                            <i class="fa-solid fa-pen" id="profileImageIcon"></i>
                                            <input type="file" id="logo" name="logo" class="image-upload d-none" accept="image/*" />
                                        </label>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-6">
                    <label for="faviconPreview"
                        class="col-lg-4 required form-label"> {{__('messages.setting.favicon'). ':'}}</label>
                    <div class="col-lg-8">
                        <div class="mb-3" io-image-input="true">
                            <div class="d-block">
                                <div class="image-picker">
                                    <div class="image previewImage" id="faviconPreview"
                                        style="background-image: url('{{(!empty($setting['favicon']))?asset($setting['favicon']):asset('assets/image/norsu_logo.png')}}');">
                                    </div>
                                    <span class="picker-edit rounded-circle text-gray-500 fs-small" data-bs-toggle="tooltip"
                                        data-placement="top" data-bs-original-title="{{ __('messages.setting.change_favicon') }}">
                                        <label>
                                            <i class="fa-solid fa-pen" id="profileImageIcon"></i>
                                            <input type="file" id="favicon" name="favicon" class="image-upload d-none" accept="image/*" />
                                        </label>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-6">
                    <label class="col-lg-4 form-label required">
                        <span>{{__('messages.setting.do_not_allow_to_login_without_email_verification')}}:</span>
                        <span data-bs-toggle="tooltip"
                            data-placement="top"
                            data-bs-original-title="{{ __('messages.setting.when_checkbox_disable') }}">
                            <i class="fas fa-question-circle ml-1 mt-1 general-question-mark"></i>
                        </span>
                    </label>
                    <div class="col-lg-8">
                        {{ Form::checkbox('email_verified', 1, $setting['email_verified'] ?? 1, ['class' => 'form-check-input m-0']) }}
                    </div>
                </div>

        <div class="row">
            <!-- Submit Field -->
            <div class="form-group col-sm-12">
                {{ Form::submit(__('messages.user.save_changes'),['class' => 'btn btn-primary','id'=>'settingSubmitBtn']) }}
            </div>
        </div>
    </div>
</div>
{{ Form::close() }}
</div>
</div>
@endsection