<div class="modal fade" id="createBarangayModal" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>{{__('messages.barangay.add_barangay')}}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            {{ Form::open(['id' => 'createBarangayForm']) }}
            <div class="modal-body">
                <div class="mb-5">
                    {{ Form::label('name', __('messages.common.name').':', ['class' => 'required form-label']) }}
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' =>  __('messages.common.name'),'required']) }}
                </div>
                <div>
                    {{ Form::label('city_id', __('messages.barangay.city').':', ['class' => 'required form-label ']) }}
                    {{ Form::select('city_id', $cities, null, ['id' => 'cityBarangay','required','data-control'=>"select2", 'placeholder' => __('messages.barangay.select_city')]) }}
                </div>
            </div>
            <div class="modal-footer pt-0">
                {{ Form::submit(__('messages.common.save'),['class' => 'btn btn-primary m-0','data-turbo' => 'false']) }}
                {{ Form::button(__('messages.common.discard'),['class' => 'btn btn-secondary my-0 ms-5 me-0','data-bs-dismiss'=>'modal']) }}
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
