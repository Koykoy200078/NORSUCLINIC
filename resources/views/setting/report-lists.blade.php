@extends('layouts.app')
@section('title')
{{ __('messages.settings') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column">
        @include('setting.setting_menu')

        <div class="card mb-6">
            <div class="card-header">
                <h3 class="m-0">Illnesses (by body system)</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    These are the illnesses a nurse or doctor picks on a consultation and the rows of the Accomplishment Report.
                    A line is never deleted, because consultations already point at it: switch it off to stop offering it
                    (its past counts stay in the report). The "Others" line of each body system takes free text and cannot be renamed.
                </p>

                @foreach($systems as $system)
                    <details class="border rounded mb-3" {{ $loop->first ? 'open' : '' }}>
                        <summary class="px-3 py-2 fw-semibold" style="cursor:pointer;">
                            {{ $system->name }}
                            <span class="text-muted fw-normal">({{ $system->illnesses->where('is_active', true)->count() }} on the form)</span>
                        </summary>
                        <div class="px-3 pb-3">
                            @foreach($system->illnesses as $illness)
                                <form method="POST" action="{{ route('report-lists.illnesses.update', $illness) }}" class="row g-2 align-items-center mt-1">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-md-4">
                                        <input type="text" name="name" value="{{ $illness->name }}" maxlength="150" class="form-control form-control-sm" {{ $illness->is_other ? 'readonly' : 'required' }} aria-label="Illness name">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="group_label" value="{{ $illness->group_label }}" maxlength="120" class="form-control form-control-sm" placeholder="Group heading (optional)" aria-label="Group heading">
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-check">
                                            <input type="checkbox" name="is_active" value="1" id="ill_active_{{ $illness->id }}" class="form-check-input" {{ $illness->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ill_active_{{ $illness->id }}">On the form</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2"><button type="submit" class="btn btn-sm btn-light-primary w-100">Save</button></div>
                                </form>
                            @endforeach

                            <form method="POST" action="{{ route('report-lists.illnesses.store') }}" class="row g-2 align-items-center mt-3 pt-3 border-top">
                                @csrf
                                <input type="hidden" name="illness_system_id" value="{{ $system->id }}">
                                <div class="col-md-4"><input type="text" name="name" maxlength="150" required class="form-control form-control-sm" placeholder="New illness in {{ $system->name }}" aria-label="New illness name"></div>
                                <div class="col-md-4"><input type="text" name="group_label" maxlength="120" class="form-control form-control-sm" placeholder="Group heading (optional)" aria-label="Group heading"></div>
                                <div class="col-md-2"></div>
                                <div class="col-md-2"><button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-plus"></i> Add</button></div>
                            </form>
                        </div>
                    </details>
                @endforeach
            </div>
        </div>

        <div class="card mb-6">
            <div class="card-header">
                <h3 class="m-0">Services rendered</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    The "Other services" rows of the report. Lines marked <span class="badge bg-light text-muted border">auto</span> are also
                    counted by the system from the visit's own records, so their names are fixed; you can still switch them off.
                </p>

                @foreach(\App\Models\ServiceType::CATEGORY_LABELS as $category => $categoryLabel)
                    <div class="fw-semibold mt-3">{{ $categoryLabel }}</div>
                    @foreach(($serviceGroups[$category] ?? collect()) as $service)
                        <form method="POST" action="{{ route('report-lists.services.update', $service) }}" class="row g-2 align-items-center mt-1">
                            @csrf
                            @method('PUT')
                            <div class="col-md-6">
                                <input type="text" name="name" value="{{ $service->name }}" maxlength="150" class="form-control form-control-sm" {{ ($service->is_other || $service->auto_rule) ? 'readonly' : 'required' }} aria-label="Service name">
                            </div>
                            <div class="col-md-2">
                                @if($service->auto_rule)<span class="badge bg-light text-muted border">auto</span>@endif
                            </div>
                            <div class="col-md-2">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" value="1" id="svc_active_{{ $service->id }}" class="form-check-input" {{ $service->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="svc_active_{{ $service->id }}">On the form</label>
                                </div>
                            </div>
                            <div class="col-md-2"><button type="submit" class="btn btn-sm btn-light-primary w-100">Save</button></div>
                        </form>
                    @endforeach

                    <form method="POST" action="{{ route('report-lists.services.store') }}" class="row g-2 align-items-center mt-3 pt-3 border-top">
                        @csrf
                        <input type="hidden" name="category" value="{{ $category }}">
                        <div class="col-md-6"><input type="text" name="name" maxlength="150" required class="form-control form-control-sm" placeholder="New service under {{ $categoryLabel }}" aria-label="New service name"></div>
                        <div class="col-md-4"></div>
                        <div class="col-md-2"><button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-plus"></i> Add</button></div>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
