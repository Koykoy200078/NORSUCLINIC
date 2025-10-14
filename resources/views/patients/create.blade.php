@extends('layouts.app')
@section('title')
{{ __('messages.patient.add') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <h1>@yield('title')</h1>
        <a class="btn btn-outline-primary float-end"
            href="{{ 
                   isRole('clinic_admin') ? route('patients.index') : 
                   (isRole('staff') ? route('staff.patients.index') : 
                   (isRole('doctor') ? route('doctors.patients.index') : route('patients.index')))
               }}">{{ __('messages.common.back') }}</a>
    </div>

    <div class="col-12">
        @include('layouts.errors')
    </div>
    <div class="card">
        <div class="card-body">
            {{ Form::open(['route' => 
                isRole('clinic_admin') ? 'patients.store' : 
                (isRole('staff') ? 'staff.patients.store' : 
                (isRole('doctor') ? 'doctors.patients.store' : 'patients.store')),
                'files' => 'true','id' => 'createPatientForm']) }}
            {{ Form::hidden('is_edit', false,['id' => 'patientIsEdit']) }}
            {{ Form::hidden('backgroundImg',asset('web/media/avatars/male.png'),['id' => 'patientBackgroundImg']) }}
            @include('patients.fields')
            {{ Form::close() }}
        </div>
    </div>
</div>
@endsection
