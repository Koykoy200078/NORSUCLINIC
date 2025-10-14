@extends('layouts.app')
@section('title')
{{__('messages.appointment.appointments')}}
@endsection
@section('content')
<div class="container-fluid">
    @include('flash::message')
    <div class="d-flex flex-column">
        {{Form::hidden('patientRole',getLogInUser()->hasRole('patient'),['id' => 'patientRole'])}}
        <livewire:appointment-table />
        @include('patient_queues.models.patient-payment-model')
        @include('patient_queues.models.change-payment-status-model')
    </div>
</div>
@endsection