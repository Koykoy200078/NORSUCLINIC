@extends('layouts.app')
@section('title')
{{__('messages.settings')}}
@endsection
@section('content')
<div class="container-fluid">
    {{ html()->form('POST', route('setting.update'))->attribute('accept-charset', 'UTF-8')->acceptsFiles()->attributes(['id' => 'generalSettingForm'])->open() }}
    <div class="d-flex flex-column">
        @include('setting.setting_menu')
        {{ html()->hidden('sectionName', $sectionName)->forgetAttribute('id') }}
        {{ html()->hidden('setting_country_id', false)->attributes(['id' => 'settingCountryId']) }}
        {{ html()->hidden('setting_state_id', false)->attributes(['id' => 'settingStateId']) }}
        {{ html()->hidden('setting_city_id', false)->attributes(['id' => 'settingCityId']) }}
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-center">
                    <h3 class="m-0">{{__('messages.setting.general_details')}}
                    </h3>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-6">
                    {{ html()->label()->for('clinic_name')->text(__('messages.setting.clinic_name').':')->attributes(['class'=>'col-lg-4 form-label required']) }}
                    <div class="col-lg-8">
                        {{ html()->text('clinic_name', $setting['clinic_name'] ?? null)->attributes(['class' => 'form-control', 'placeholder'=>__('messages.setting.clinic_name'), 'required']) }}
                    </div>
                </div>
                <!-- Landline Number -->
                <div class="row mb-6">
                    {{ html()->label()->for('landline_no')->text(__('Landline No').':')->attributes(['class' => 'col-lg-4 form-label']) }}
                    <div class="col-lg-8">
                        {{ html()->text('landline_no', $setting['landline_no'] ?? null)->attributes(['class' => 'form-control', 'placeholder' => __('Input Landline Number'), 'id' => 'landlineNumber']) }}
                    </div>
                </div>
                <!-- End of landline number -->
                <div class="row mb-6">
                    {{ html()->label()->for('contact_no')->text(__('messages.patient.contact_no').':')->attributes(['class' => 'col-lg-4 form-label required']) }}
                    <div class="col-lg-8">
                        {{ html()->text('contact_no', $setting['contact_no'] ?? null)->attributes(['class' => 'form-control', 'data-ph-phone' => 'true', 'placeholder' => __('messages.patient.contact_no'), 'id'=>'phoneNumber']) }}
                    </div>
                </div>
                <div class="row mb-6">
                    {{ html()->label()->for('default_country_code')->text(__('messages.setting.default_country_code').':')->attributes(['class' => 'col-lg-4 form-label']) }}
                    <div class="col-lg-8">
                        {{-- The clinic works with Philippine numbers only, so the country is fixed. --}}
                        <input type="text" class="form-control" value="{{ __('Philippines') }} (+63)" readonly disabled>
                    </div>
                </div>
                <div class="row mb-6">
                    {{ html()->label()->for('email')->text(__('messages.user.email').':')->attributes(['class'=>'col-lg-4 form-label required']) }}
                    <div class="col-lg-8">
                        {{ html()->email('email', $setting['email'] ?? null)->attributes(['class' => 'form-control ', 'placeholder'=>__('messages.user.email'), 'required']) }}
                    </div>
                </div>
                {{-- Signs the ACCOMPLISHMENT REPORT as "Noted by". --}}
                <div class="row mb-6">
                    {{ html()->label()->for('university_physician_name')->text(__('University Physician').':')->attributes(['class' => 'col-lg-4 form-label']) }}
                    <div class="col-lg-8">
                        {{ html()->text('university_physician_name', $setting['university_physician_name'] ?? null)->attributes(['class' => 'form-control', 'maxlength' => 150, 'placeholder' => __('Full name, e.g. Dr. Juan Dela Cruz')]) }}
                    </div>
                </div>
                <div class="row mb-6">
                    {{ html()->label()->for('university_physician_title')->text(__('University Physician title').':')->attributes(['class' => 'col-lg-4 form-label']) }}
                    <div class="col-lg-8">
                        {{ html()->text('university_physician_title', $setting['university_physician_title'] ?? 'University Physician')->attributes(['class' => 'form-control', 'maxlength' => 100, 'placeholder' => __('University Physician')]) }}
                    </div>
                </div>
                <div class="row mb-6">
                    {{ html()->label()->for('specialties')->text(__('messages.setting.specialties').':')->attributes(['class'=>'col-lg-4 form-label required']) }}
                    <div class="col-lg-8">
                        {{ html()->select('specialties[]', $specialties, old('specialties', json_decode($setting['specialties'] ?? '[]')))->multiple()->forgetAttribute('id')->attributes(['class' => 'form-select', 'aria-label'=>"Select a Country", 'data-control'=>'select2', 'required']) }}
                    </div>
                </div>
                <div class="row mb-6">
                    <label for="appLogoPreview" class="col-lg-4 required form-label">{{ __('messages.setting.logo').':'}}</label>
                    <div class="col-lg-8">
                        <div class="mb-3" io-image-input="true">
                            <div class="d-block">
                                <div class="image-picker">
                                    <div class="image previewImage" id="appLogoPreview"
                                        style="background-image: url('{{(!empty($setting['logo']))?asset(normalizeLocalUrl($setting['logo'])):asset('assets/image/norsu_favicon.ico')}}')">
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
                                        style="background-image: url('{{(!empty($setting['favicon']))?asset(normalizeLocalUrl($setting['favicon'])):asset('assets/image/norsu_logo.png')}}');">
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
                        {{ html()->checkbox('email_verified', $setting['email_verified'] ?? 1, 1)->forgetAttribute('id')->attributes(['class' => 'form-check-input m-0']) }}
                    </div>
                </div>

        <div class="row">
            <!-- Submit Field -->
            <div class="form-group col-sm-12">
                {{ html()->input('submit', null, __('messages.user.save_changes'))->attributes(['class' => 'btn btn-primary', 'id'=>'settingSubmitBtn']) }}
            </div>
        </div>
    </div>
</div>
{{ html()->closeModelForm() }}
</div>
</div>
@endsection