@extends('layouts.app')
@section('title')
{{($setting['clinic_name'] ?? null)}}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column">
        @include('setting.setting_menu')
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-center">
                    <h3 class="m-0">{{__('messages.setting.contact_information')}}
                    </h3>
                </div>
            </div>
            <div>
                {{ html()->form('POST', route('setting.update'))->attribute('accept-charset', 'UTF-8')->acceptsFiles()->open() }}
                {{ html()->hidden('sectionName', $sectionName)->forgetAttribute('id') }}
                <div class="card-body">
                    <div class="row mb-6">
                        {{ html()->label()->for('address_one')->text(__('messages.setting.address').' 1:')->attributes(['class'=>'col-lg-4 required form-label']) }}
                        <div class="col-lg-8">
                            {{ html()->text('address_one', ($setting['address_one'] ?? null))->attributes(['class' => 'form-control', 'placeholder'=>__('messages.setting.address').' 1', 'required']) }}
                        </div>
                    </div>
                    <div class="row mb-6">
                        {{ html()->label()->for('address_two')->text(__('messages.setting.address').' 2:')->attributes(['class'=>'col-lg-4 required form-label']) }}
                        <div class="col-lg-8">
                            {{ html()->text('address_two', ($setting['address_two'] ?? null))->attributes(['class' => 'form-control', 'placeholder'=>__('messages.setting.address').' 2', 'required']) }}
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 form-label">
                            {{ html()->label()->for('country_id')->text(__('messages.country.country').':')->attributes(['class'=>'col-lg-4 form-label required']) }}
                        </label>
                        <div class="col-lg-8">
                            {{ html()->select('country_id', $countries, ($setting['country_id'] ?? null))->placeholder(__('messages.country.country'))->attributes(['id' => 'settingCountryId', 'class' => 'form-select', 'aria-label'=>"Select a Country", 'data-control'=>'select2', 'required']) }}
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 form-label">
                            {{ html()->label()->for('state_id')->text(__('messages.common.province').':')->attributes(['class'=>'col-lg-4 required form-label']) }}
                        </label>
                        <div class="col-lg-8">
                            {{ html()->select('state_id', (isset($states) && $states!=null ? $states : []), isset($setting['state_id']) ? ($setting['state_id'] ?? null) : null)->placeholder(__('messages.common.province'))->attributes(['id' => 'settingStateId', 'class' => 'form-select', 'data-control'=>'select2', 'required']) }}
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 form-label">
                            {{ html()->label()->for('city_id')->text(__('messages.city.city').':')->attributes(['class'=>'col-lg-4 required form-label']) }}
                        </label>
                        <div class="col-lg-8">
                            {{ html()->select('city_id', $cities?? [], $setting['city_id'] ?? null)->attributes(['id' => 'settingCityId', 'class' => 'form-select', 'data-control'=>'select2', 'required']) }}
                        </div>
                    </div>
                    <div class="row mb-6">
                        {{ html()->label()->for('postal_code')->text(__('messages.setting.postal_code').':')->attributes(['class'=>'col-lg-4 required form-label']) }}
                        <div class="col-lg-8">
                            {{ html()->text('postal_code', ($setting['postal_code'] ?? null))->attributes(['class' => 'form-control', 'placeholder'=>__('messages.setting.postal_code'), 'required']) }}
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    {{ html()->input('submit', null, __('messages.user.save_changes'))->attributes(['class' => 'btn btn-primary']) }}
                </div>
                {{ html()->closeModelForm() }}
            </div>
        </div>
    </div>
</div>
@endsection