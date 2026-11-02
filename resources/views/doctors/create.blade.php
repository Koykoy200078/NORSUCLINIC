@extends('layouts.app')
@section('title')
{{__('messages.doctor.add')}}
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
        <div class="card-body">
            {{ html()->form('POST', route(getRouteNameByRole('doctors.store')))->attribute('accept-charset', 'UTF-8')->acceptsFiles()->attributes(['id'=> 'createDoctorForm'])->open() }}
            @include('doctors.fields')
            {{ html()->closeModelForm() }}
        </div>
    </div>
</div>
@endsection