<div class="modal fade" tabindex="-1" id="emailNotificationModal" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{{ __('messages.user.email_notification') }}</h2>

                <!--begin::Close-->
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                {{ html()->form('POST', url()->current())->attribute('accept-charset', 'UTF-8')->attributes(['id'=>'emailNotificationForm'])->open() }}
                <div class="form-check form-check-custom form-check-solid d-flex">
                    <div class="fv-row d-flex align-items-center">
                        {{ html()->checkbox('email_notification', getLogInUser()->email_notification, 1)->forgetAttribute('id')->attributes(['class' => 'form-check-input  me-5']) }}
                    </div>
                    <label class="col-form-label fw-bold fs-6">
                        <span> {{__('messages.user.email_notification')}}</span>
                    </label>
                </div>
                <div class="pt-5">
                    {{ html()->button(__('messages.common.save'), 'button')->attributes(['class' => 'btn btn-primary mr-2', 'id'=>'emailNotificationChange']) }}
                    {{ html()->button(__('messages.common.discard'), 'button')->attributes(['class' => 'btn btn-secondary ms-3', 'data-bs-dismiss' => 'modal']) }}
                </div>
                {{ html()->closeModelForm() }}
            </div>

        </div>
    </div>
</div>