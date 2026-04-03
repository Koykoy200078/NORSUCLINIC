<?php if($errors->any()): ?>
    <div class="alert alert-danger">
        <div>
            <div class="d-flex">
                <span><i class="fa-solid fa-face-frown me-5"></i></span>
                <span class=""><?php echo e($errors->first()); ?></span>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/layouts/errors.blade.php ENDPATH**/ ?>