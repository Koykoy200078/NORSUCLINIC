<div class="modal fade" id="editBarangayModal" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>{{__('messages.barangay.edit_barangay')}}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            {{ Form::open(['id' => 'editBarangayForm']) }}
            <div class="modal-body">
                {{ Form::hidden('id',null,['id' => 'barangayID']) }}
                <div class="mb-5">
                    {{ Form::label('name', __('messages.common.name').':', ['class' => 'required']) }}
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' =>  __('messages.web.name'),'required','id'=>'editBarangayName']) }}
                </div>
                <div>
                    {{ Form::label('city_id', __('messages.barangay.city').':', ['class' => 'required']) }}
                    {{ Form::select('city_id', $cities, null, ['id' => 'editBarangayCityId','required','data-control'=>"select2"]) }}
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
