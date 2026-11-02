@extends('layouts.app')
@section('title')
    {{ __('messages.medicine_bills.medicine_bills') }}
@endsection

@section('content')
    <div class="container-fluid">
        @include('flash::message')
        {{-- {{html()->hidden('billUrl', route('bills.index'))->attributes(['id'=>'indexBillUrl', 'class'=>'billUrl'])}}
        {{html()->hidden('patientUrl', url('patients'))->attributes(['id'=>'indexPatientUrl', 'class'=>'patientUrl'])}}
        {{ html()->hidden('billLang', __('messages.delete.bill'))->attributes(['id' => 'billLang']) }} --}}
        <div class="d-flex flex-column">
            <livewire:medicine-bill-table/>
        </div>
    </div>
@endsection
    {{--   assets/js/bills/bill.js -}}
    {{--   assets/js/custom/new-edit-modal-form.js -}}
    {{--   assets/js/custom/reset_models.js --}}
