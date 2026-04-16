@extends('layouts.app')
@section('title')
{{ __('messages.request.create_request') }}
@endsection
@section('content')
<div class="p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold">
            @if(request('document_type') === 'medical_certificate')
            Create Medical Certificate
            @elseif(request('document_type') === 'consultation_form')
            Create Consultation Form
            @else
            {{ __('messages.request.create_request') }}
            @endif
        </h1>
        <div class="flex gap-2">
            @if(request('document_type') === 'consultation_form')
            <button type="button" id="load_past_data_btn" class="text-white px-4 py-2 rounded" style="background-color: #9333ea;">
                <i class="fas fa-history"></i> Load Past Data
            </button>
            @endif
            <a href="{{ 
                request('user_id') && isset($patient) ? 
                    (isRole('clinic_admin') ? route('patients.showMyHistory', ['patient' => $patient->id]) : 
                    (isRole('staff') ? route('staff.patients.showMyHistory', ['patient' => $patient->id]) : 
                    (isRole('doctor') ? route('doctors.patients.showMyHistory', ['patient' => $patient->id]) : 
                    route('patients.showMyHistory', ['patient' => $patient->id])))) :
                    (isRole('clinic_admin') ? route('document-issuances.index') : 
                    (isRole('staff') ? route('staff.document-issuances.index') : 
                    (isRole('doctor') ? route('doctors.document-issuances.index') : 
                    route('document-issuances.index'))))
            }}" class="bg-blue-500 text-white px-4 py-2 rounded">Back</a>
        </div>
    </div>

    @if(!request('document_type'))
    <!-- Show document type selector only if not pre-selected -->
    <div class="form-group mb-5">
        <label for="document_type">Document Type</label>
        <select name="document_type" id="document_type" class="form-control" required>
            <option value="" disabled selected>Select Document Type</option>
            <option value="medical_certificate">Medical Certificate</option>
            <option value="consultation_form">Consultation Form</option>
        </select>
    </div>
    @endif

    <div id="form-container" class="flex items-center justify-center">
        @if(request('document_type') === 'medical_certificate')
        @include('requests.forms.medical_certificate', ['data' => $data, 'user' => $user, 'patient' => $patient ?? null])
        @elseif(request('document_type') === 'consultation_form')
        @include('requests.forms.consultation_form', ['data' => $data, 'user' => $user, 'patient' => $patient ?? null])
        @elseif(!request('document_type'))
        <p class="text-gray-500">Please select a document type to continue.</p>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const documentTypeSelect = document.getElementById('document_type');

        if (documentTypeSelect) {
            documentTypeSelect.addEventListener('change', function() {
                const selectedType = this.value;
                const currentUrl = new URL(window.location.href);

                // Preserve existing query parameters (like user_id)
                currentUrl.searchParams.set('document_type', selectedType);

                // Redirect to the same page with the selected document type
                window.location.href = currentUrl.toString();
            });
        }

        // Add hover effect for Load Past Data button
        const loadPastDataBtn = document.getElementById('load_past_data_btn');
        if (loadPastDataBtn) {
            loadPastDataBtn.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#7c3aed'; // darker purple on hover
            });
            loadPastDataBtn.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '#9333ea'; // original purple
            });
        }
    });
</script>
@endsection