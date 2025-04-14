@extends('layouts.app')
@section('title')
{{ __('messages.request.create_request') }}
@endsection
@section('content')
<div class="p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold">{{ __('messages.request.create_request') }}</h1>
        <a href="{{ route('request-documents.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Back</a>
    </div>

    <div class="form-group mb-5">
        <label for="document_type">Document Type</label>
        <select name="document_type" id="document_type" class="form-control" required>
            <option value="" disabled {{ request('document_type') ? '' : 'selected' }}>Select Document Type</option>
            <option value="medical_certificate" {{ request('document_type') === 'medical_certificate' ? 'selected' : '' }}>Medical Certificate</option>
            <option value="consultation_form" {{ request('document_type') === 'consultation_form' ? 'selected' : '' }}>Consultation Form</option>
        </select>
    </div>

    <div id="form-container" class="flex items-center justify-center">
        @if(request('document_type') === 'medical_certificate')
        @include('requests.forms.medical_certificate', ['data' => $data, 'user' => $user])
        @elseif(request('document_type') === 'consultation_form')
        @include('requests.forms.consultation_form', ['data' => $data, 'user' => $user])
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const documentTypeSelect = document.getElementById('document_type');
        const formContainer = document.getElementById('form-container');

        documentTypeSelect.addEventListener('change', function() {
            const selectedType = this.value;

            // Redirect to the same page with the selected document type as a query parameter
            window.location.href = `?document_type=${selectedType}`;
        });
    });
</script>
@endsection