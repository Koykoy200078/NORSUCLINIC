@extends('layouts.app')
@section('title')
    {{__('messages.role.edit_role')}}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <h1>@yield('title')</h1>
            <a class="btn btn-outline-primary float-end"
               href="{{ route('roles.index') }}">{{ __('messages.common.back') }}</a>
        </div>

        <div class="col-12">
            @include('layouts.errors')
        </div>
        <div class="card">
            <div class="card-body">
                {{ html()->form('put', route('roles.update', [$role->id]))->attribute('accept-charset', 'UTF-8')->open() }}
                    @include('roles.fields')
                    {{ html()->closeModelForm() }}
            </div>
        </div>
    </div>
@endsection
