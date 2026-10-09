@extends('layouts.app')
@section('title')
Consultation Medicines
@endsection
@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <h1 class="mb-0 me-1"></h1>
        <div class="text-end mt-4 mt-md-0">
            @if ($canOpenConsultation)
            <a class="btn btn-primary"
                href="{{ getRouteByRole('document-issuances.show', [$consultation->id]) }}">View Consultation</a>
            @endif
            <a href="{{ getRouteByRole('medicine-dispensing.index', ['tab' => 'dispense-history']) }}"
                class="btn btn-outline-primary ms-2">{{ __('messages.common.back') }}</a>
        </div>
    </div>
</div>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center pb-10">
                    <img alt="Logo" src="{{ asset(getAppLogo()) }}" height="100px" width="100px">
                </div>
                <div class="m-0">
                    <div class="fs-3 text-gray-800 mb-2">#CONS-{{ $consultation->id }}</div>
                    <div class="mb-8"><span class="badge bg-light-success">Consultation</span></div>
                    <div class="row g-5 mb-11">
                        <div class="col-sm-3">
                            <div class="pb-2 fs-5 text-gray-600">{{ __('Patient Name').':' }}</div>
                            <div class="fs-5 text-gray-800">{{ $patientUser?->full_name ?? $consultation->name ?? __('messages.common.n/a') }}</div>
                        </div>
                        <div class="col-sm-3">
                            <div class="pb-2 fs-5 text-gray-600">{{ __('Patient ').' '.__('auth.email').':' }}</div>
                            <div class="fs-5 text-gray-800">{{ $patientUser?->email ?? __('messages.common.n/a') }}</div>
                        </div>
                        <div class="col-sm-3">
                            <div class="pb-2 fs-5 text-gray-600">{{ __('Patient ').' '.__('messages.user.gender').':' }}</div>
                            <div class="fs-5 text-gray-800">
                                {{ (($patientUser?->gender ?? null) == 1) ? __('messages.staff.male') : ((($patientUser?->gender ?? null) == 2) ? __('messages.staff.female') : __('messages.common.n/a')) }}
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="pb-2 fs-5 text-gray-600">{{ __('Patient ').' '.__('messages.medicine_bills.cell_no').':' }}</div>
                            <div class="fs-5 text-gray-800">{{ !empty($patientUser?->contact) ? $patientUser->contact : __('messages.common.n/a') }}</div>
                        </div>
                    </div>
                    <div class="row g-5 mb-11">
                        <div class="col-sm-3">
                            <div class="pb-2 fs-5 text-gray-600">Recorded At:</div>
                            <div class="fs-5 text-gray-800">{{ $consultation->created_at?->format('jS M, Y g:i A') ?? __('messages.common.n/a') }}</div>
                        </div>
                        <div class="col-sm-3">
                            <div class="pb-2 fs-5 text-gray-600">Recorded By:</div>
                            <div class="fs-5 text-gray-800">{{ $consultation->creator?->full_name ?? __('messages.common.n/a') }}</div>
                        </div>
                    </div>
                    <div class="flex-grow-1 table-responsive">
                        <table class="table border-bottom-2">
                            <thead>
                                <tr class="border-bottom fs-6 fw-bolder text-muted">
                                    <th class="min-w-175px pb-2">{{ __('messages.medicine_bills.item_name') }}</th>
                                    <th class="min-w-120px pb-2">{{ __('messages.medicine.dosage') }}</th>
                                    <th class="min-w-120px pb-2">Used For</th>
                                    <th class="min-w-175px pb-2">Instructions</th>
                                    <th class="min-w-70px text-end pb-2">{{ __('messages.medicine.quantity') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($consultation->consultationMedicines as $line)
                                <tr class="text-gray-700 fs-5">
                                    <td class="pt-6">{{ $line->medicine?->name ?? __('messages.common.n/a') }}</td>
                                    <td class="pt-6">{{ !empty($line->dosage) ? $line->dosage : __('messages.common.n/a') }}</td>
                                    <td class="pt-6">{{ $line->used_for ? ucfirst($line->used_for) : __('messages.common.n/a') }}</td>
                                    <td class="pt-6">{{ !empty($line->dosage_instructions) ? $line->dosage_instructions : __('messages.common.n/a') }}</td>
                                    <td class="pt-6 text-end">{{ $line->quantity }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fs-5 fw-bolder text-gray-800">
                                    <td colspan="4" class="pt-6 text-end">Total</td>
                                    <td class="pt-6 text-end">{{ $consultation->consultationMedicines->sum('quantity') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
