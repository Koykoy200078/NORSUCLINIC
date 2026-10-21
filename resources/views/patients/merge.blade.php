@extends('layouts.app')
@section('title', 'Merge duplicate patients')
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
        <h1 class="fs-3">Merge duplicate patients</h1>
        <a href="{{ route('patients.index') }}" class="btn btn-secondary">Back to patients</a>
    </div>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    @if($previewError)<div class="alert alert-warning" role="alert">{{ $previewError }}</div>@endif
    @unless($ready)
        <div class="alert alert-warning">Apply the patient merge database update before using this tool.</div>
    @else
        <div class="card mb-4"><div class="card-body">
            <p>The record you keep retains its identity, current address, profile picture and medical summary. The duplicate is archived with its original details available for review. Clinical documents keep their saved text, authors, dates and images. Medicine quantities do not change.</p>
            <p>Confirm that these records belong to the same person. A shared phone or similar name is only a suggestion.</p>
            <form action="{{ route('patient-merge.index') }}" method="get">
                <div class="row g-3">
                @foreach(['source_id' => 'Duplicate to archive', 'target_id' => 'Patient to keep'] as $field => $label)
                    <div class="col-md-6"><label for="{{ $field }}" class="form-label">{{ $label }}</label>
                        <select name="{{ $field }}" id="{{ $field }}" class="form-select" required>
                            <option value="">Select a patient</option>
                            @foreach($records as $record)<option value="{{ $record->id }}" @selected((string) request($field) === (string) $record->id)>{{ $record->user->full_name }} (#{{ $record->id }}) · {{ $record->patient_unique_id }}</option>@endforeach
                        </select>
                    </div>
                @endforeach
                </div>
                <button type="submit" class="btn btn-primary mt-3">Preview merge</button>
            </form>
        </div></div>
        @if($preview)
            <section class="card mb-4" aria-labelledby="preview-title"><div class="card-body">
                <h2 class="fs-4" id="preview-title">Merge preview</h2>
                @foreach($preview['blockers'] as $blocker)<div class="alert alert-warning" role="alert">{{ $blocker }}</div>@endforeach
                <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Details</th><th>Duplicate to archive #{{ $preview['source']->id }}</th><th>Patient to keep #{{ $preview['target']->id }}</th></tr></thead><tbody>
                    @foreach(['full_name' => 'Name', 'dob' => 'Birth date', 'gender' => 'Sex', 'university_id_number' => 'University ID', 'contact' => 'Phone'] as $field => $label)
                        <tr><th scope="row">{{ $label }}</th>@foreach(['source', 'target'] as $side)<td>{{ $field === 'gender' ? (\App\Models\User::GENDER[$preview[$side]->user->$field] ?? 'Not recorded') : ($preview[$side]->user->$field ?? 'Not recorded') }}</td>@endforeach</tr>
                    @endforeach
                    @foreach(['allergies' => 'Allergies', 'comorbidities' => 'Comorbidities', 'maintenance' => 'Maintenance', 'admissions_surgeries' => 'Admissions / surgeries', 'covid_vaccination' => 'COVID vaccination', 'immunization_record' => 'Immunization record'] as $field => $label)
                        <tr><th scope="row">{{ $label }}</th><td>{{ $preview['source']->$field ?: 'Not recorded' }}</td><td>{{ $preview['target']->$field ?: 'Not recorded' }}</td></tr>
                    @endforeach
                </tbody></table></div>
                <div class="table-responsive"><table class="table"><thead><tr><th>History to move (includes archived records)</th><th>Records</th></tr></thead><tbody>
                    @foreach(['document_issuances' => 'Consultations, certificates and excuse slips', 'lab_requests' => 'Lab requests', 'prescriptions' => 'Prescriptions', 'medicine_bills' => 'Dispense records', 'patient_queues' => 'Queue entries', 'addresses' => 'Preserved addresses', 'media' => 'Preserved profile files', 'consultation_images' => 'Consultation images (original document links retained)'] as $key => $label)<tr><td>{{ $label }}</td><td>{{ $preview['counts'][$key] }}</td></tr>@endforeach
                </tbody></table></div>
                @if($preview['token'])
                    <form action="{{ route('patient-merge.store') }}" method="post">
                        @csrf
                        <input type="hidden" name="source_id" value="{{ $preview['source']->id }}">
                        <input type="hidden" name="target_id" value="{{ $preview['target']->id }}">
                        <input type="hidden" name="preview_token" value="{{ $preview['token'] }}">
                        <label class="form-check mb-3"><input class="form-check-input" type="checkbox" name="confirmed" value="1" required><span class="form-check-label">I verified these are the same person and reviewed which details will be retained.</span></label>
                        <p>Preview expires in 15 minutes. Changed records require a new preview. The archived duplicate cannot be restored independently.</p>
                        <button type="submit" class="btn btn-warning">Merge and archive duplicate</button>
                    </form>
                @endif
            </div></section>
        @endif
        @if($archive)
            <section class="card mb-4"><div class="card-body">
                <h2 class="fs-4">Archived source #{{ $archive->id }} — Merged into #{{ $archive->merged_into_id }}</h2>
                <p>{{ $archive->user?->full_name }} · {{ $archive->patient_unique_id }}. <a href="{{ route('patients.show', $archive->merged_into_id) }}">Open retained patient</a></p>
                <p>Preserved source details for clinical review. Copy corrections to the retained patient only after checking them.</p>
                <details class="mb-3"><summary>Original identity, contact and affiliation details</summary>
                    <dl class="row mt-3">
                        @foreach(\App\Models\User::RECORD_FIELDS as $field)<dt class="col-sm-4 text-break">{{ \Illuminate\Support\Str::headline($field) }}</dt><dd class="col-sm-8 text-break">{{ $field === 'gender' ? (\App\Models\User::GENDER[$archive->user?->$field] ?? 'Not recorded') : ($archive->user?->$field ?? 'Not recorded') }}</dd>@endforeach
                    </dl>
                </details>
                <dl class="row">
                    @foreach(['allergies' => 'Allergies', 'comorbidities' => 'Comorbidities', 'maintenance' => 'Maintenance', 'admissions_surgeries' => 'Admissions / surgeries', 'covid_vaccination' => 'COVID vaccination', 'immunization_record' => 'Immunization record'] as $field => $label)<dt class="col-sm-4">{{ $label }}</dt><dd class="col-sm-8 text-break">{{ $archive->$field ?: 'Not recorded' }}</dd>@endforeach
                </dl>
                <details class="mb-3"><summary>Original patient type, insurance and primary care details</summary>
                    <dl class="row mt-3">
                        @foreach(['patient_type_id', 'insurance_provider_id', 'insurance_policy_number', 'primary_care_physician_name', 'primary_care_physician_contact', 'primary_care_physician_email'] as $field)<dt class="col-sm-4 text-break">{{ \Illuminate\Support\Str::headline($field) }}</dt><dd class="col-sm-8 text-break">{{ $archive->$field ?? 'Not recorded' }}</dd>@endforeach
                    </dl>
                </details>
                <h3 class="fs-5">Preserved addresses</h3>
                @forelse($archiveAddresses as $address)<p>{{ $address->address1 }} {{ $address->address2 }} {{ $address->full_address }}</p>@empty<p>No addresses were moved.</p>@endforelse
                <h3 class="fs-5">Preserved profile files</h3>
                @forelse($archiveMedia as $media)<p><a href="{{ normalizeLocalUrl($media->getFullUrl()) }}">{{ $media->file_name }}</a></p>@empty<p>No profile files were moved.</p>@endforelse
            </div></section>
        @endif
        <section class="card mb-4"><div class="card-body">
            <h2 class="fs-4">Suggested duplicates (up to 100 pairs)</h2>
            <div class="table-responsive"><table class="table"><thead><tr><th>Patient</th><th>Possible match</th><th>Reason</th><th>Review</th></tr></thead><tbody>
                @forelse($candidates as $pair)<tr><td>{{ $pair['source']->user->full_name }} (#{{ $pair['source']->id }})</td><td>{{ $pair['target']->user->full_name }} (#{{ $pair['target']->id }})</td><td>{{ implode('; ', $pair['reasons']) }}</td><td><a href="{{ route('patient-merge.index', ['source_id' => $pair['source']->id, 'target_id' => $pair['target']->id]) }}">Preview pair #{{ $pair['source']->id }} / #{{ $pair['target']->id }}</a></td></tr>@empty<tr><td colspan="4">No duplicate candidates found.</td></tr>@endforelse
            </tbody></table></div>
        </div></section>
        <section class="card"><div class="card-body">
            <h2 class="fs-4">Recent merges</h2>
            <div class="table-responsive"><table class="table"><thead><tr><th>Archived patient</th><th>Retained patient</th><th>By</th><th>When</th></tr></thead><tbody>
                @foreach($history as $entry)<tr><td><a href="{{ route('patient-merge.index', ['archive_id' => $entry->properties['source']['id']]) }}">#{{ $entry->properties['source']['id'] }} — review source</a></td><td>#{{ $entry->properties['target']['id'] }}</td><td>{{ $entry->user_name }}</td><td>{{ $entry->created_at->format('Y-m-d H:i') }}</td></tr>@endforeach
            </tbody></table></div>
        </div></section>
    @endunless
</div>
@endsection
