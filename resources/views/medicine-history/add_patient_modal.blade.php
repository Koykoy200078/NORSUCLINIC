<div id="addPatientModal" class="modal fade" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h2>{{ __('messages.patient.add') }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            {{ html()->form('POST', url()->current())->attribute('accept-charset', 'UTF-8')->attributes(['id'=>'addPatientForm'])->open() }}
            <div class="modal-body">
                <div class="alert alert-danger d-none hide" id="patientErrorsBox"></div>
                <div class="row">
                    <div class="form-group col-sm-6 mb-5">
                        {{ html()->label()->for('first_name')->text(__('messages.staff.first_name').':')->attributes(['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ html()->text('first_name', null)->attributes(['class' => 'form-control', 'required', 'id' => 'patientFirstName', 'tabindex' => '1', 'placeholder' => __('messages.staff.first_name')]) }}
                    </div>
                    <div class="form-group col-sm-6 mb-5">
                        {{ html()->label()->for('last_name')->text(__('messages.staff.last_name').':')->attributes(['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ html()->text('last_name', null)->attributes(['class' => 'form-control', 'required', 'tabindex' => '2', 'placeholder' => __('messages.staff.last_name')]) }}
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-6 mb-5">
                        {{ html()->label()->for('email')->text(__('messages.user.email').':')->attributes(['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ html()->text('email', null)->attributes(['class' => 'form-control', 'required', 'placeholder' => __('messages.user.email')]) }}
                    </div>
                    <div class="form-group col-sm-6 mb-5">
                        {{ html()->label()->for('contact')->text(__('messages.patient.contact_no').':')->attributes(['class' => 'form-label']) }}<br>
                        {{ html()->text('contact', null)->attributes(['class' => 'form-control', 'tabindex' => '5', 'data-ph-phone' => 'true', 'placeholder' => __('messages.patient.contact_no')]) }}
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-6 mb-5">
                        {{ html()->label()->for('gender')->text(__('messages.user.gender').':')->attributes(['class' => 'form-label']) }}
                        <span
                            class="required"></span> &nbsp;<br>
                        <span class="is-valid">
                            <label class="form-label">{{ __('messages.staff.male') }}</label>&nbsp;&nbsp;
                            {{ html()->radio('gender', true, '0')->attributes(['class' => 'form-check-input', 'tabindex' => '6', 'id'=>'patientMale']) }} &nbsp;
                            <label class="form-label">{{ __('messages.staff.female') }}</label>
                            {{ html()->radio('gender', false, '1')->attributes(['class' => 'form-check-input', 'tabindex' => '7', 'id'=>'patientFemale']) }}
                        </span>
                    </div>
                    <div class="form-group col-sm-6 mb-5">
                        {{ html()->label()->for('status')->text(__('messages.web.status').':')->attributes(['class' => 'form-label']) }}
                        <div class="form-check form-switch form-check-custom">
                            <input class="form-check-input w-35px h-20px is-active" name="status" type="checkbox" value="1"
                                tabindex="8" checked>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 mb-5">
                        <div>{{ html()->label()->for('password')->text(__('auth.password') . ':')->attributes(['class' => 'form-label']) }}<span
                                class="required"></span></div>
                        <div class="form-group col-12 mb-5 position-relative d-flex align-items-center">
                            <div class="position-relative w-100">
                                {{ html()->password('password')->attributes(['class' => 'form-control', 'placeholder' => __('messages.patient.password'), 'autocomplete' => 'off', 'aria-label' => __('auth.password'), 'data-toggle' => 'password', 'minlength' => '6', 'maxlength' => '255', 'tabindex' => '10']) }}
                                <span
                                    class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 me-4 input-icon input-password-hide cursor-pointer text-gray-600">
                                    <i class="bi bi-eye-slash-fill"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-5">
                        <div>
                            {{ html()->label()->for('password_confirmation')->text(__('auth.confirm_password') . ':')->attributes(['class' => 'form-label']) }}<span
                                class="required"></span>
                        </div>
                        <div class="form-group col-12 mb-5 position-relative d-flex align-items-center">
                            <div class="position-relative w-100">
                                {{ html()->password('password_confirmation')->attributes(['class' => 'form-control', 'placeholder' => __('messages.user.confirm_password'), 'minlength' => '6', 'maxlength' => '255', 'tabindex' => '11', 'aria-label' => __('auth.confirm_password'), 'data-toggle' => 'password']) }}
                                <span
                                    class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 me-4 input-icon input-password-hide cursor-pointer text-gray-600">
                                    <i class="bi bi-eye-slash-fill"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-0">
                    {{ html()->button(__('messages.common.save'), 'button')->attributes(['type'=>'submit', 'class' => 'btn btn-primary m-0', 'id'=>'patientBtnSave', 'data-loading-text'=>"<span class='spinner-border spinner-border-sm'></span> Processing..."]) }}
                    <button type="button" aria-label="Close" class="btn btn-secondary"
                        data-bs-dismiss="modal">{!! __('messages.common.cancel') !!}
                    </button>
                </div>
            </div>
            {{ html()->closeModelForm() }}
        </div>
    </div>
</div>