@extends('layouts.app')
@section('title')
{{ __('messages.medicine_availability.medicine_availability')}}
@endsection
@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        @include('layouts.errors')
        <h1 class="mb-0 me-1">{{__('messages.medicine_availability.medicine_availability_details')}}</h1>
        <div class="text-end mt-4 mt-md-0">
            @unless(isRole('doctor'))
<a href="{{ 
                getRouteByRole('stock-in.edit', [$medicineAvailability->id])
            }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>{{ __('messages.common.edit') }}
            </a>
@endunless
            <a href="{{ 
                    getRouteByRole('stock-in.index')
                }}" class="btn btn-outline-primary ms-2">
                {{ __('messages.common.back') }}
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column">
        <div class="row">
            <div class="col-12">
                @include('flash::message')
            </div>
        </div>
        @include('medicine-availabilities.show_fields')
    </div>
</div>
@endsection