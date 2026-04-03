<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <!-- Meta Information -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title'); ?> | <?php echo e(getAppName()); ?></title>

    <!-- Favicon -->
    <link rel="icon" href="<?php echo e(asset(getAppFavicon())); ?>" type="image/png">

    <!-- Fonts -->
    <link rel="stylesheet" href="<?php echo e(asset('css/poppins.css')); ?>">

    <!-- General CSS Files -->
    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('assets/css/style.css')); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('assets/css/third-party.css')); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo e(mix('assets/css/pages.css')); ?>">

    <!-- CSS Libraries -->
    <?php echo $__env->yieldPushContent('css'); ?>
</head>

<body
    class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed aside-enabled aside-fixed">
    <div class="d-flex flex-column flex-root">
        <div
            class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed authImage">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container-fluid padding-0">
            <div class="row align-items-center justify-content-center">
                <div class="col-xl-6">
                    <div class="copyright text-center text-muted">
                        <?php echo e(__('messages.all_rights_reserved')); ?> &copy; <?php echo e(date('Y')); ?>

                        <a href="/" class="font-weight-bold ml-1" target="_blank"><?php echo e(getAppName()); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="<?php echo e(asset('backend/js/vendor.js')); ?>"></script>
    <script src="<?php echo e(mix('assets/js/auto_fill/auto_fill.js')); ?>"></script>
    
    <script src="<?php echo e(mix('js/custom-auth.js')); ?>"></script>

    <?php echo $__env->yieldPushContent('scripts'); ?>

    <script>
        $(document).ready(function() {
            $('.alert').delay(5000).slideUp(300);
        });
    </script>
</body>

</html><?php /**PATH C:\Projects\NORSUCLINIC\resources\views/layouts/auth.blade.php ENDPATH**/ ?>