@extends('layouts.app')
@section('title')
{{__('messages.doctor.edit')}}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <h1>@yield('title')</h1>
        <a class="btn btn-outline-primary float-end"
            href="{{ 
                   getRouteByRole('doctors.index')
               }}">{{ __('messages.common.back') }}</a>
    </div>

    <div class="col-12">
        @include('layouts.errors')
    </div>
    <div class="card">
        {{html()->hidden('qualification', $qualifications)->attributes(['id' => 'qualificationData'])}}
        <div class="card-body">
            {{ html()->form('Patch', route(getRouteNameByRole('doctors.update'), [$user->doctor->id]))->attribute('accept-charset', 'UTF-8')->acceptsFiles()->attributes(['id'=> 'editDoctorForm'])->open() }}
            {{ html()->hidden('user_id', $doctor->id)->attributes(['id' => 'editDoctorId']) }}
            {{ html()->hidden('is_edit', true)->attributes(['id' => 'doctorIsEdit']) }}
            {{ html()->hidden('edit_country_id', isset($user->address->country_id) ? $user->address->country_id:null)->attributes(['id' => 'doctorCountryId']) }}
            {{ html()->hidden('edit_state_id', isset($user->address->state_id) ? $user->address->state_id:null)->attributes(['id' => 'doctorStateId']) }}
            {{ html()->hidden('edit_city_id', isset($user->address->city_id) ? $user->address->city_id:null)->attributes(['id' => 'doctorCityId']) }}
            {{ html()->hidden('backgroundImg', asset('web/media/avatars/male.png'))->attributes(['id' => 'doctorBackgroundImg']) }}
            {{-- Add redirect URL based on user role --}}
            {{ html()->hidden('doctorIndexRedirectUrl', getRouteByRole('doctors.index'))->attributes(['id' => 'doctorIndexRedirectUrl']) }}
            @include('doctors.edit-fields')
            {{ html()->closeModelForm() }}
            @include('doctors.templates.templates')
        </div>
    </div>
</div>
@endsection