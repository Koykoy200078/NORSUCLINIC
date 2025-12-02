<div>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="genericOverview" role="tabpanel">
            <div class="card mb-5 mb-xl-10">
                <div>
                    <div class="card-body">
                        <div class="row mb-7">
                            <div class="col-sm-6 d-flex flex-column mb-md-10 mb-5">
                                <label class="pb-2 fs-5 text-gray-600">{{ __('messages.medicine.generic')  }}</label>
                                <span class="fs-5 text-gray-800">{{$generic->name}}</span>
                            </div>
                            <div class="col-sm-6 d-flex flex-column mb-md-10 mb-5">
                                <label class="pb-2 fs-5 text-gray-600">{{ __('messages.web.created_at')  }}</label>
                                <span class="fs-5 text-gray-800" data-placement="top"
                                    data-bs-original-title="{{ \Carbon\Carbon::parse($generic->created_at)->format('jS M, Y') }}">{{ \Carbon\Carbon::parse($generic->created_at)->diffForHumans() }}</span>
                            </div>
                            <div class="col-sm-6 d-flex flex-column mb-md-10 mb-5">
                                <label class="pb-2 fs-5 text-gray-600">{{ __('messages.patient.last_updated')  }}</label>
                                <span class="fs-5 text-gray-800" data-placement="top"
                                    data-bs-original-title="{{ \Carbon\Carbon::parse($generic->updated_at)->format('jS M, Y') }}">{{ \Carbon\Carbon::parse($generic->updated_at)->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-title mb-5">
                <h3 class="pb-1">{{ __('messages.medicine.medicines') }}</h3>
            </div>
            <livewire:medicine-generic-details-table genericDetails="{{$generic->id}}" />
        </div>
    </div>
</div>