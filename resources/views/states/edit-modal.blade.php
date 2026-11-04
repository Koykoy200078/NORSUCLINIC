<div class="modal fade" id="editStateModal" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>{{ __('messages.state.edit_state') }}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            {{ html()->form('POST', url()->current())->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'editStateForm'])->open() }}
            <div class="modal-body">
                {{ html()->hidden('stateId', null)->attributes(['id'=>'editStateId']) }}
                <div class="mb-5">
                    {{ html()->label()->for('name')->text(__('messages.common.name').':')->attributes(['class' => 'form-label required']) }}
                    {{ html()->text('name', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.common.name'), 'tabindex' => 1, 'required', 'id' => 'editStateName']) }}
                </div>
                <div>
                    {{ html()->label()->for('country_id')->text(__('messages.country.country').':')->attributes(['class' => 'form-label required']) }}
                    {{ html()->select('country_id', $countries, null)->attributes(['class' => 'form-select', 'id' => 'selectCountry', 'required', 'data-control'=>'select2']) }}
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