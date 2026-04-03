<div class="modal fade" id="changeLanguageModal" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo e(__('messages.user.change_language')); ?></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <?php echo e(Form::open(['id'=>'changeLanguageForm'])); ?>

                <?php echo csrf_field(); ?>
                <?php echo method_field('POST'); ?>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="editLanguageValidationErrorsBox"></div>
                    <div>
                            <?php
                                $user = Auth::user()
                            ?>
                            <?php echo e(Form::label('Language', __('messages.user.language').':',['class' => 'form-label'])); ?>

                            <?php echo e(Form::select('language', \App\Models\User::LANGUAGES, isset($user) ? $user->language : null, ['class' => 'form-control form-select', 'required', 'data-control' => 'select2','id' => 'selectLanguage','data-dropdown-parent'=>'#changeLanguageModal'])); ?>

                    </div>
                </div>
                <div class="modal-footer pt-0">
                <?php echo e(Form::button(__('messages.common.save'),['class' => 'btn btn-primary m-0','id' => 'languageChangeBtn'])); ?>

                <?php echo e(Form::button(__('messages.common.discard'),['class' => 'btn btn-secondary my-0 ms-5 me-0','data-bs-dismiss' => 'modal'])); ?>

            </div>
            <?php echo e(Form::close()); ?>

        </div>
    </div>
</div>
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/profile/changelanguage.blade.php ENDPATH**/ ?>