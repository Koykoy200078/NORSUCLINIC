@extends('layouts.app')
@section('title')
{{__('messages.staff.add_staff')}}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <h1>{{ __('messages.staff.add_staff') }}</h1>
        <a class="btn btn-outline-primary float-end"
            href="{{ route('staffs.index') }}">{{ __('messages.common.back') }}</a>
    </div>

    <div class="col-12">
        @include('layouts.errors')
    </div>
    <div class="card">
        <div class="card-body">
            {{ html()->form('POST', route('staffs.store'))->attribute('accept-charset', 'UTF-8')->acceptsFiles()->attributes(['id'=> 'createStaffForm'])->open() }}
            @include('staffs.fields')
            {{ html()->closeModelForm() }}
        </div>
    </div>
</div>
@endsection