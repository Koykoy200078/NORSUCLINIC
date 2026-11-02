<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-modal="true" role="dialog" data-bs-backdrop="{{ isset($hasDefaultPassword) && $hasDefaultPassword ? 'static' : 'true' }}" data-bs-keyboard="{{ isset($hasDefaultPassword) && $hasDefaultPassword ? 'false' : 'true' }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>{{ __('messages.user.change_password') }}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="display: {{ isset($hasDefaultPassword) && $hasDefaultPassword ? 'none' : 'block' }};"></button>
            </div>
            {{html()->form('POST', url()->current())->attribute('accept-charset', 'UTF-8')->attributes(['id'=>'changePasswordForm'])->open()}}
            <div class="modal-body">
                @csrf
                @method('PUT')
                <div class="mb-7">
                    {{html()->label()->for('current_password')->text(__('messages.user.current_password').':')->attributes(['class' => 'required form-label'])}}
                    <div class="mb-3 position-relative">
                        {{html()->password('current_password')->attributes(['class' => 'form-control', 'placeholder' => __('messages.user.current_password'), 'autocomplete' => 'off', 'required', 'aria-label'=>"Password", 'data-toggle'=>"password"])}}
                        <span class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 me-4 input-icon input-password-hide cursor-pointer text-gray-600"> <i class="bi bi-eye-slash-fill"></i> </span>
                    </div>
                </div>
                <div class="mb-7">
                    {{html()->label()->for('new_password')->text(__('messages.user.new_password').':')->attributes(['class' => 'required form-label'])}}
                    <div class="mb-3 position-relative">
                        {{html()->password('new_password')->attributes(['class' => 'form-control', 'placeholder' => __('messages.user.new_password'), 'autocomplete' => 'off', 'required', 'aria-label'=>"Password", 'data-toggle'=>"password"])}}
                        <span class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 me-4 input-icon input-password-hide cursor-pointer text-gray-600"> <i class="bi bi-eye-slash-fill"></i> </span>
                    </div>
                </div>
                <div>
                    {{html()->label()->for('confirm_password')->text(__('messages.user.confirm_password').':')->attributes(['class' => 'required form-label'])}}
                    <div class="mb-3 position-relative">
                        {{html()->password('confirm_password')->attributes(['class' => 'form-control', 'placeholder' => __('messages.user.confirm_password'), 'autocomplete' => 'off', 'required', 'aria-label'=>"Password", 'data-toggle'=>"password"])}}
                        <span class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 me-4 input-icon input-password-hide cursor-pointer text-gray-600"> <i class="bi bi-eye-slash-fill"></i> </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer pt-0">
                {{ html()->button(__('messages.common.save'), 'button')->attributes(['class' => 'btn btn-primary m-0', 'id' => 'passwordChangeBtn']) }}
                @if(!isset($hasDefaultPassword) || !$hasDefaultPassword)
                {{ html()->button(__('messages.common.discard'), 'button')->attributes(['class' => 'btn btn-secondary my-0 ms-5 me-0', 'data-bs-dismiss' => 'modal']) }}
                @endif
            </div>
        </div>
        {{html()->closeModelForm()}}