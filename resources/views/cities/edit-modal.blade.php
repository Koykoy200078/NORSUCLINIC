<div class="modal fade" id="editCityModal" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>{{__('messages.city.edit_city')}}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            {{ html()->form('POST', url()->current())->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'editCityForm'])->open() }}
            <div class="modal-body">
                {{ html()->hidden('id', null)->attributes(['id' => 'cityID']) }}
                <div class="mb-5">
                    {{ html()->label()->for('name')->text(__('messages.common.name').':')->attributes(['class' => 'required']) }}
                    {{ html()->text('name', null)->attributes(['class' => 'form-control', 'placeholder' =>  __('messages.web.name'), 'required', 'id'=>'editCityName']) }}
                </div>
                <div class="mb-5">
                    {{ html()->label()->for('type')->text('Type:')->attributes(['class' => 'required']) }}
                    {{ html()->select('type', $cityTypes, null)->attributes(['id' => 'editCityType', 'required', 'data-control'=>"select2"]) }}
                </div>
                <div>
                    {{ html()->label()->for('state_id')->text(__('messages.city.province').':')->attributes(['class' => 'required']) }}
                    {{ html()->select('state_id', $states, null)->attributes(['id' => 'editCityStateId', 'required', 'data-control'=>"select2"]) }}
                </div>
            </div>
            <div class="modal-footer pt-0">
                {{ html()->input('submit', null, __('messages.common.save'))->attributes(['class' => 'btn btn-primary m-0', 'data-turbo' => 'false']) }}
                {{ html()->button(__('messages.common.discard'), 'button')->attributes(['class' => 'btn btn-secondary my-0 ms-5 me-0', 'data-bs-dismiss'=>'modal']) }}
            </div>
            {{ html()->closeModelForm() }}
        </div>
    </div>
</div>