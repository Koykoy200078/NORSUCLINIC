<div class="modal fade" id="qualificationModal" aria-modal="true" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>{{__('messages.doctor.add_qualification')}}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            @php
            $styleCss = 'style'
            @endphp
            <div class="modal-body">
                <div class="alert alert-danger fs-4 text-white d-flex align-items-center d-none" role="alert" id="createCityValidationErrorsBox">
                    <i class="fa-solid fa-face-frown me-5"></i>
                </div>
                {{ html()->form('POST', url()->current())->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'qualificationForm'])->open() }}
                {{ html()->hidden('id', null)->attributes(['id' => 'qualificationID']) }}
                <div class="mb-5">
                    {{ html()->label()->for('Degree')->text(__('messages.doctor.degree').':')->attributes(['class' => 'form-label required']) }}
                    {{ html()->text('degree', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.doctor.degree'), 'id'=>'degree']) }}
                </div>
                <div class="mb-5">
                    {{ html()->label()->for('university')->text(__('messages.doctor.university').':')->attributes(['class' => 'form-label required']) }}
                    {{ html()->text('university', null)->attributes(['class' => 'form-control', 'placeholder' =>  __('messages.doctor.university'), 'id'=>'university']) }}
                </div>
                <div>
                    {{ html()->label()->for('Degree')->text(__('messages.doctor.year').':')->attributes(['class' => 'form-label required']) }}
                    {{ html()->select('year', $years, !empty($qualifications->year) ? $qualifications->year : null)->placeholder(__('messages.doctor.select_year'))->attributes(['class' => 'io-select2 form-select', 'data-control'=>"select2", 'id'=> 'year', 'data-dropdown-parent'=>'#qualificationModal']) }}
                </div>
            </div>
            <div class="modal-footer pt-0">
                {{ html()->button(__('messages.common.save'), 'button')->attributes(['class' => 'btn btn-primary m-0', 'id'=>'qualificationSaveBtn']) }}
                {{ html()->button(__('messages.common.discard'), 'button')->attributes(['class' => 'btn btn-secondary my-0 ms-5 me-0', 'data-bs-dismiss'=>'modal']) }}
            </div>
            {{ html()->closeModelForm() }}
        </div>
    </div>
</div>