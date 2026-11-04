<div class="modal fade" id="createCityModal" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>{{__('Add City/Municipalities')}}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            {{ html()->form('POST', url()->current())->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'createCityForm'])->open() }}
            <div class="modal-body">
                <div class="mb-5">
                    {{ html()->label()->for('name')->text(__('messages.common.name').':')->attributes(['class' => 'required form-label']) }}
                    {{ html()->text('name', null)->attributes(['class' => 'form-control', 'placeholder' =>  __('messages.common.name'), 'required']) }}
                </div>
                <div class="mb-5">
                    {{ html()->label()->for('type')->text('Type:')->attributes(['class' => 'required form-label']) }}
                    {{ html()->select('type', $cityTypes, null)->placeholder('Select Type')->attributes(['id' => 'cityType', 'required', 'data-control'=>"select2"]) }}
                </div>
                <div>
                    {{ html()->label()->for('state_id')->text(__('messages.city.province').':')->attributes(['class' => 'required form-label ']) }}
                    {{ html()->select('state_id', $states, null)->placeholder(__('messages.city.province'))->attributes(['id' => 'stateCity', 'required', 'data-control'=>"select2"]) }}
                </div>
            </div>
            <div class="modal-footer pt-0">
                {{ html()->input('submit', null, __('messages.common.save'))->attributes(['class' => 'btn btn-primary m-0']) }}
                {{ html()->button(__('messages.common.discard'), 'button')->attributes(['class' => 'btn btn-secondary my-0 ms-5 me-0', 'data-bs-dismiss'=>'modal']) }}
            </div>
            {{ html()->closeModelForm() }}
        </div>
    </div>
</div>