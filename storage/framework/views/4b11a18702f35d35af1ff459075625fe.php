<footer class="border-top w-100 pt-4 mt-7">
    <div class="d-flex justify-content-md-between">
        <p class="fs-6 text-gray-600"><?php echo e(__('messages.all_rights_reserved_©')); ?> <?php echo e(\Carbon\Carbon::now()->year); ?>

            <a href="#" class="text-decoration-none"><?php echo e(getAppName()); ?></a>
        </p>
        <?php if(config('app.footer_version_show')): ?>
        <span class="text-muted align-content-end"><?php echo e(version()); ?></span>
        <?php endif; ?>
    </div>
</footer>
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/layouts/footer.blade.php ENDPATH**/ ?>