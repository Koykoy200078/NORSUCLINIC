<div class="modal show fade" tabindex="-1" id="emailNotificationModal"  aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><?php echo e(__('messages.user.email_notification')); ?></h2>

                <!--begin::Close-->
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <?php echo e(Form::open(['id'=>'emailNotificationForm'])); ?>

                <div class="form-check form-check-custom form-check-solid d-flex">
                    <div class="fv-row d-flex align-items-center">
                        <?php echo e(Form::checkbox('email_notification', 1, getLogInUser()->email_notification,['class' => 'form-check-input  me-5'])); ?>

                    </div>
                    <label class="col-form-label fw-bold fs-6">
                        <span>  <?php echo e(__('messages.user.email_notification')); ?></span>
                    </label>
                </div>
                <div class="pt-5">
                    <?php echo e(Form::button(__('messages.common.save'),['class' => 'btn btn-primary mr-2','id'=>'emailNotificationChange'])); ?>

                    <?php echo e(Form::button(__('messages.common.discard'),['class' => 'btn btn-secondary ms-3','data-bs-dismiss' => 'modal'])); ?>

                </div>
                <?php echo e(Form::close()); ?>

            </div>
            
        </div>
    </div>
</div>
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/profile/email_notification.blade.php ENDPATH**/ ?>