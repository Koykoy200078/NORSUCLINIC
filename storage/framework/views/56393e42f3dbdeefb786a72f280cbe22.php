<div class="row">
    <div class="col-lg-6">
        <div class="mb-5">
            <?php echo e(Form::label('first_name', __('messages.staff.first_name').':', ['class' => 'form-label required'])); ?>

            <?php echo e(Form::text('first_name', old('first_name', isset($staff) ? $staff->first_name : null), ['class' => 'form-control', 'placeholder' => __('messages.patient.first_name'), 'required'])); ?>

        </div>
    </div>

    <div class="col-lg-6">
        <div class="mb-5">
            <?php echo e(Form::label('last_name', __('messages.staff.last_name').':', ['class' => 'form-label required'])); ?>

            <?php echo e(Form::text('last_name', old('last_name', isset($staff) ? $staff->last_name : null), ['class' => 'form-control', 'placeholder' => __('messages.patient.last_name'), 'required'])); ?>

        </div>
    </div>

    <div class="col-lg-6">
        <div class="mb-5">
            <?php echo e(Form::label('email', __('messages.staff.email').':', ['class' => 'form-label required'])); ?>

            <?php echo e(Form::email('email', old('email', isset($staff) ? $staff->email : null), ['class' => 'form-control', 'placeholder' => __('messages.patient.email'), 'required'])); ?>

        </div>
    </div>

    <div class="col-lg-6">
        <div class="mb-5">
            <?php echo e(Form::label('contact', __('messages.staff.contact_no').':', ['class' => 'form-label'])); ?>

            <br>
            <?php echo e(Form::tel('contact', old('contact', isset($staff) && $staff->contact ? '+'.$staff->country_code.$staff->contact : null), ['class' => 'form-control', 'placeholder' => __('messages.patient.contact_no'), 'onkeyup' => 'if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,"")','id'=>'phoneNumber'])); ?>

            <?php echo e(Form::hidden('country_code', old('country_code', isset($staff) ? $staff->country_code : null), ['id'=>'prefix_code'])); ?>

            <span id="valid-msg" class="text-success d-none fw-400 fs-small mt-2"><?php echo e(__('messages.valid_number')); ?></span>
            <span id="error-msg" class="text-danger d-none fw-400 fs-small mt-2"></span>
        </div>
    </div>

    <div class="col-md-6 mb-5">
        <div class="mb-1">
            <?php echo e(Form::label('password',__('messages.staff.password').':' ,['class' => 'form-label'])); ?>

            <span class="text-danger"><?php echo e(isset($staff) ? null : '*'); ?></span>
            <span data-bs-toggle="tooltip" title="<?php echo e(__('messages.flash.user_8_or')); ?>">
                <i class="fa fa-question-circle"></i></span>
            <div class="mb-3 position-relative">
                <?php echo e(Form::password('password',['class' => 'form-control','placeholder' => __('messages.patient.password'),'autocomplete' => 'off','aria-label'=>"Password",'data-toggle'=>"password"])); ?>

                <span class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 me-4 input-icon input-password-hide cursor-pointer text-gray-600"> <i class="bi bi-eye-slash-fill"></i> </span>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-5">
        <div class="fv-row">
            <div class="mb-1">
                <?php echo e(Form::label('confirmPassword',__('messages.staff.confirm_password').':' ,['class' => 'form-label'])); ?>

                <span class="text-danger"><?php echo e(isset($staff) ? null : '*'); ?></span>
                <span data-bs-toggle="tooltip"
                    title="<?php echo e(__('messages.flash.user_8_or')); ?>">
                    <i class="fa fa-question-circle"></i></span>
                <div class="mb-3 position-relative">
                    <?php echo e(Form::password('password_confirmation',['class' => 'form-control','placeholder' => __('messages.user.confirm_password'),'autocomplete' => 'off','aria-label'=>"Password",'data-toggle'=>"password"])); ?>

                    <span class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 me-4 input-icon input-password-hide cursor-pointer text-gray-600"> <i class="bi bi-eye-slash-fill"></i> </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="mb-5">
            <?php echo e(Form::label('role', __('messages.staff.role').':', ['class' => 'form-label'])); ?>

            
            <?php echo e(Form::hidden('role', isset($staff) ? $staff->roles->first()->id : $defaultRoleId)); ?>

            
            <?php echo e(Form::select('role_display', $roles, isset($staff) ? $staff->roles->first()->id : $defaultRoleId, ['class' => 'form-select io-select2', 'data-control'=>'select2','placeholder' => __('messages.staff.select_role'), 'readonly' => true, 'disabled' => true])); ?>

        </div>
    </div>


    <div class="col-lg-6">
        <div class="mb-5">
            <?php echo e(Form::label('gender', __('messages.staff.gender').':', ['class' => 'form-label required'])); ?>

            <span class="is-valid">
                <div class="mt-2">
                    <input class="form-check-input" type="radio" name="gender" value="1"
                        <?php echo e(old('gender', isset($staff) ? $staff->gender : 1) == 1 ? 'checked' : ''); ?> required>
                    <label class="form-label mr-3"><?php echo e(__('messages.staff.male')); ?></label>

                    <input class="form-check-input ms-2" type="radio" name="gender" value="2"
                        <?php echo e(old('gender', isset($staff) ? $staff->gender : 1) == 2 ? 'checked' : ''); ?> required>
                    <label class="form-label mr-3"><?php echo e(__('messages.staff.female')); ?></label>
                </div>
            </span>
        </div>
    </div>

    <div class="col-lg-6 mb-7">
        <div class="mb-3" io-image-input="true">
            <label for="exampleInputImage" class="form-label"><?php echo e(__('messages.patient.profile')); ?>:</label>
            <div class="d-block">
                <div class="image-picker">
                    <div class="image previewImage" id="exampleInputImage" style="background-image: url(<?php echo e(!empty($staff->profile_image) ? $staff->profile_image : asset('web/media/avatars/male.png')); ?>)">
                    </div>
                    <span class="picker-edit rounded-circle text-gray-500 fs-small" data-bs-toggle="tooltip"
                        data-placement="top" data-bs-original-title="<?php echo e(__('messages.user.edit_profile')); ?>">
                        <label>
                            <i class="fa-solid fa-pen" id="profileImageIcon"></i>
                            <input type="file" name="profile" class="image-upload d-none profile-validation" accept="image/*" />
                        </label>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex">
        <?php echo e(Form::submit(__('messages.common.save'),['class' => 'btn btn-primary me-2'])); ?>

        <a href="<?php echo e(route('staffs.index')); ?>" type="reset"
            class="btn btn-secondary"><?php echo e(__('messages.common.discard')); ?></a>
    </div>
</div><?php /**PATH C:\Projects\NORSUCLINIC\resources\views/staffs/fields.blade.php ENDPATH**/ ?>