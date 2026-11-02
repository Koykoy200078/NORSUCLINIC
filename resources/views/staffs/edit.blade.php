@extends('layouts.app')
@section('title')
    {{__('messages.staff.edit_staff')}}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <h1>@yield('title')</h1>
            <a class="btn btn-outline-primary float-end"
               href="{{ route('staffs.index') }}">{{ __('messages.common.back') }}</a>
        </div>

        <div class="col-12">
            @include('layouts.errors')
        </div>
        <div class="card">
            <div class="card-body">
                {{ html()->form('put', route('staffs.update', [$staff->id]))->attribute('accept-charset', 'UTF-8')->acceptsFiles()->attributes(['id' => 'editStaffForm'])->open() }}
                {{ html()->hidden('is_edit', true)->attributes(['id' => 'staffIsEdit']) }}
                    @include('staffs.fields')
                {{ html()->closeModelForm() }}
            </div>
        </div>
    </div>
@endsection
