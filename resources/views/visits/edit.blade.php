@extends('layouts.app')
@section('title')
{{__('messages.visit.edit_visit')}}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <h1>@yield('title')</h1>
        <a href="{{ isRole('clinic_admin') ? route('visits.index') : (isRole('staff') ? route('staff.visits.index') : route('doctors.visits.index')) }}"
            class="btn btn-outline-primary float-end">{{ __('messages.common.back') }}</a>
    </div>

    <div class="col-12">
        @include('layouts.errors')
    </div>
    <div class="card">
        <div class="card-body">
            {{ Form::model($visit,['route' => [isRole('clinic_admin') ? 'visits.update' : (isRole('staff') ? 'staff.visits.update' : 'doctors.visits.update'), $visit->id], 'method' => 'patch','id' => 'saveForm']) }}
            @include('visits.fields')
            {{ Form::close() }}
        </div>
    </div>
</div>
@endsection