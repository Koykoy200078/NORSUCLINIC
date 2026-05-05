{{-- <div>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="poverview" role="tabpanel">
            <div class="card mb-5 mb-xl-10">
                <div>
                    <div class="card-body  border-top p-9">
                        <div class="row mb-7">
                            <div class="col-lg-6 d-flex flex-column">
                                <label class="fw-bold text-muted py-3">{{ __('messages.medicine_availability.availability_number')  }}</label>
<span class="fw-bold fs-6 text-gray-800"><span class="badge bg-light-primary ">#{{$medicineAvailability->availability_no}}</span></span>
</div>
<div class="col-lg-6 d-flex flex-column">
    <label class="fw-bold text-muted py-3">{{ __('messages.common.created_on')  }}</label>
    <span class="fw-bold fs-6 text-gray-800" data-toggle="tooltip" data-placement="right" title="{{ \Carbon\Carbon::parse($medicineAvailability->created_at)->translatedFormat('jS M, Y') }}">{{ \Carbon\Carbon::parse($medicineAvailability->created_at)->diffForHumans() }}</span>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
--}}





<div>
    <div class="d-flex overflow-auto h-55px">
        <ul class="nav nav-tabs mb-5 pb-1 overflow-auto flex-nowrap text-nowrap">
            <li class="nav-item position-relative me-7 mb-3" role="presentation">
                <button class="nav-link active p-0" id="overview-tab" data-bs-toggle="tab"
                    data-bs-target="#overview"
                    type="button" role="tab" aria-controls="overview" aria-selected="true">
                    {{ __('messages.medicine_availability.medicine_availability_overview') }}
                </button>
            </li>
        </ul>
    </div>
    {{-- @endif--}}
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="">
                <div class="d-flex flex-column">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xxl-9">
                                    <div class="row">
                                        <div class="col-lg-4 d-flex flex-column">
                                            <label class="fw-bold text-muted py-3">{{ __('messages.medicine_availability.availability_number')  }} <span class="fw-bold fs-6 text-gray-800"><span class="badge bg-light-primary ">#{{$medicineAvailability->availability_no}}</span></span></label>

                                        </div>
                                        <div class="col-12 overflow-auto">
                                            <table class="table table-striped box-shadow-none mt-4">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">{{ __('messages.medicines') }}</th>
                                                        <th scope="col">{{ __('messages.medicine_availability.dosage') }}</th>
                                                        <th scope="col">{{ __('messages.medicine_availability.manufacturing_date') }}</th>
                                                        <th scope="col">{{ __('messages.medicine_availability.expiry_date') }}</th>
                                                        <th scope="col">{{ __('messages.medicine_availability.quantity') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    @foreach($medicineAvailability->purchasedMedcines as $purchasedMedcine)
                                                    <tr>
                                                        <td class="py-4">{{ isset($purchasedMedcine->medicines->name) == true ? $purchasedMedcine->medicines->name : __('messages.common.n/a')  }}</td>
                                                        <td class="py-4">{{ $purchasedMedcine->dosage ?? __('messages.common.n/a') }}</td>
                                                        <td class="py-4">{{ $purchasedMedcine->manufacturing_date ?? __('messages.common.n/a') }}</td>
                                                        <td class="py-4">
                                                            @if($purchasedMedcine->expiry_date == null)
                                                            {{ __('messages.common.n/a') }}
                                                            @else
                                                            @php
                                                            // Check if expiry date is in Y-m format (7 chars) or Y-m-d format (10 chars)
                                                            $expiryDate = $purchasedMedcine->expiry_date;
                                                            if (strlen($expiryDate) === 7 && substr_count($expiryDate, '-') === 1) {
                                                            // Month-only format (Y-m): Display as "May 2026"
                                                            echo \Carbon\Carbon::parse($expiryDate . '-01')->format('F Y');
                                                            } else {
                                                            // Full date format (Y-m-d): Display as "2026-05-06"
                                                            echo \Carbon\Carbon::parse($expiryDate)->format('Y-m-d');
                                                            }
                                                            @endphp
                                                            @endif
                                                        </td>
                                                        <td class="py-4">{{ $purchasedMedcine->quantity }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3">
                                    <div class="bg-gray-100 rounded-15 p-md-7 p-5 h-100 mt-xxl-0 mt-5 col-xxl-9 ms-xxl-auto w-100">
                                        <h3 class="mb-5">{{ __('messages.medicine_availability.other_details') }}</h3>
                                        <div class="row">
                                            <div class="col-xxl-12 col-lg-4 col-sm-6 d-flex flex-column mb-xxl-7 mb-lg-0 mb-4">
                                                <label for="name"
                                                    class="pb-2 fs-4 text-gray-600">{{ __('messages.medicine_availability.total_medicines') }}</label>
                                                <span class="fw-bold fs-6 text-gray-800">{{ $medicineAvailability->purchasedMedcines->count() }}</span>
                                            </div>

                                            <div class="col-xxl-12 col-lg-4 col-sm-6 d-flex flex-column mb-xxl-7 mb-lg-0 mb-4">
                                                <label for="name"
                                                    class="pb-2 fs-4 text-gray-600">{{ __('messages.medicine_availability.note') }}</label>
                                                <span class="fw-bold fs-6 text-gray-800">{!! !empty($medicineAvailability->note)?nl2br(e($medicineAvailability->note)):'N/A' !!}</span>

                                            </div>

                                            <div class="col-xxl-12 col-lg-4 col-sm-6 d-flex flex-column mb-xxl-7 mb-lg-0 mb-4">
                                                <label for="name"
                                                    class="pb-2 fs-4 text-gray-600">{{ __('messages.web.created_at') }}</label>
                                                {{ \Carbon\Carbon::parse($medicineAvailability->created_at)->diffForHumans() }}
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>