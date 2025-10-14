@extends('layouts.app')
@section('title')
{{__('messages.appointment.add_new_appointment')}}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <h1>@yield('title')</h1>
        @role('patient')
        <a href="{{ route('patients.patient-appointments-index') }}"
            class="btn btn-outline-primary float-end">{{ __('messages.common.back') }}</a>
        @else
        <a href="{{ 
            isRole('clinic_admin') ? route('patient-queues.index') : 
            (isRole('staff') ? route('staff.patient-queues.index') : 
            (isRole('doctor') ? route('doctors.patient-queues.index') : route('patient-queues.index')))
        }}"
            class="btn btn-outline-primary float-end">{{ __('messages.common.back') }}</a>
        @endrole
    </div>

    <div class="col-12">
        @include('layouts.errors')
    </div>
    <div class="card">
        <div class="card-body">
            {{ Form::hidden(null, false,['id' => 'appointmentIsEdit']) }}
            {{ Form::hidden(null, \App\Models\PatientQueue::PAYTM,['id' => 'paytmMethod']) }}
            {{ Form::hidden(null, \App\Models\PatientQueue::AUTHORIZE,['id' => 'authorizeMethod']) }}
            {{ Form::hidden(null, \App\Models\PatientQueue::PAYPAL,['id' => 'paypalMethod']) }}
            {{ Form::hidden(null, \App\Models\PatientQueue::MANUALLY,['id' => 'manuallyMethod']) }}
            {{ Form::hidden(null, \App\Models\PatientQueue::STRIPE,['id' => 'stripeMethod']) }}
            @if(getLogInUser()->hasRole('patient') || getLogInUser()->hasRole('doctor') || getLogInUser()->hasRole('staff'))
            @if (getLogInUser()->hasRole('patient'))
            {{ Form::open(['route' => 'patients.appointments.store','id' => 'addAppointmentForm']) }}
            @elseif(getLogInUser()->hasRole('doctor'))
            {{ Form::open(['route' => 'doctors.appointments.store','id' => 'addAppointmentForm']) }}
            @elseif(getLogInUser()->hasRole('staff'))
            {{ Form::open(['route' => 'staff.appointments.store','id' => 'addAppointmentForm']) }}
            @endif
            @else
            {{ Form::open(['route' => 'appointments.store', 'id' => 'addAppointmentForm']) }}
            @endif
            @include('appointments.fields')
            {{ Form::close() }}
        </div>
    </div>
</div>
@endsection