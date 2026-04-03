<!DOCTYPE html>
<html dir="ltr" lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <!-- Meta Information -->
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="author" content="<?php echo e(getAppName()); ?>" />
    <meta name="robots" content="index, follow">
    <link rel="icon" href="<?php echo e(asset(getAppFavicon())); ?>" type="image/png">

    <!-- Document Title -->
    <title><?php echo $__env->yieldContent('front-title'); ?> | <?php echo e(getAppName()); ?></title>

    <!-- Stylesheets -->
    <!-- Google Fonts -->
    <link rel="stylesheet" href="<?php echo e(asset('css/montserrat.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/poppins.css')); ?>">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo e(asset('css/fontawesome.all.min.css')); ?>">

    <!-- Third-Party and Custom Styles -->
    <link href="<?php echo e(mix('css/front-third-party.css')); ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo e(mix('css/front-pages.css')); ?>" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/bootstrap-datepicker/bootstrap-datepicker.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/intlTelInput.css')); ?>">

    <!-- Scripts -->
    <script src="<?php echo e(asset('messages.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/front/vendor/bootstrap.bundle.min.js')); ?>"></script>
    <script src="<?php echo e(asset('js/front-third-party.js')); ?>"></script>
    <script src="<?php echo e(asset('js/front-pages.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/bootstrap-datepicker/bootstrap-datepicker.js')); ?>"></script>

    <!-- JavaScript Variables -->
    <script data-turbo-eval="false">
        let currencyIcon = '<?php echo e(getCurrencyIcon()); ?>';
        let isSetFirstFocus = false;
        let csrfToken = "<?php echo e(csrf_token()); ?>";
        let defaultCountryCodeValue = "<?php echo e(getSettingValue('default_country_code')); ?>";
    </script>

    <!-- Language Configuration -->
    <script data-turbo-eval="false">
        let checkLanguageSession = '<?php echo e(checkLanguageSession()); ?>';
        Lang.setLocale(checkLanguageSession);
    </script>

    <!-- Laravel Routes -->
    <?php echo app('Tightenco\Ziggy\BladeRouteGenerator')->generate(); ?>
</head>

<body>
    <!-- Header -->
    <?php echo $__env->make('fronts.layouts.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Main Content -->
    <?php echo $__env->yieldContent('front-content'); ?>

    <!-- Footer -->
    <?php echo $__env->make('fronts.layouts.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>

</html><?php /**PATH C:\Projects\NORSUCLINIC\resources\views/fronts/layouts/app.blade.php ENDPATH**/ ?>