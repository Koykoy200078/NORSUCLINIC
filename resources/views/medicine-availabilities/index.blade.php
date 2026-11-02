@extends('layouts.app')
@section('title')
{{ __('messages.medicine_availability.medicine_availability') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column">
        @include('flash::message')
        {{html()->hidden('medicineUrl', route('medicines.index'))->attributes(['id'=>'indexMedicineUrl'])}}
        {{ html()->hidden('medicines-show-modal', url('medicines-show-modal'))->attributes(['id'=>'medicinesShowModal']) }}
        {{ html()->hidden('medicineLang', __('messages.delete.medicine'))->attributes(['id' => 'medicineLang']) }}
        <livewire:stock-in-table :lazy="false" />
        @include('medicines.show_modal')
    </div>
</div>
@endsection