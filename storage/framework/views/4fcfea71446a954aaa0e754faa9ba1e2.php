<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <!-- Meta Information -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title'); ?> | <?php echo e(getAppName()); ?></title>
    <link rel="icon" href="<?php echo e(asset(getAppFavicon())); ?>" type="image/png">

    <!-- General CSS Files -->
    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('assets/css/third-party.css')); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo e(mix('assets/css/pages.css')); ?>">
    <link href="<?php echo e(asset('css/app.css')); ?>" rel="stylesheet" />

    <!-- Conditional Styles -->
    <?php if(!Auth::user()->dark_mode): ?>
    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('assets/css/style.css')); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('css/plugins.css')); ?>">
    <?php else: ?>
    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('assets/css/style-dark.css')); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('css/plugins.dark.css')); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo e(mix('assets/css/custom-pages-dark.css')); ?>">
    <?php endif; ?>

    <!-- Fonts -->
    <link rel="stylesheet" href="<?php echo e(asset('css/poppins.css')); ?>">

    <!-- Livewire Styles -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('vendor/laravel-livewire-tables.min.css')); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('vendor/laravel-livewire-tables-thirdparty.min.css')); ?>">

    <!-- Inline Styles -->
    <style>
        .listing-skeleton {
            .card {
                height: 750px;
            }

            .pulsate {
                background: linear-gradient(-45deg, #dddddd, #f0f0f0, #dddddd, #f0f0f0);
                background-size: auto;
                animation: Gradient 2.25s ease infinite;
                border-radius: 10px;
            }

            .card-content {
                clear: both;
                box-sizing: border-box;
                padding: 16px;
                background: #fff;
            }

            .search-box,
            .date-box,
            .listing,
            .filter-box,
            .export-box,
            .add-button-box,
            .add-button,
            .add-button-box-lg,
            .table,
            .column-box {
                margin-top: 8px;
                margin-left: 5px;
            }

            .filter-box {
                margin-left: auto;
            }

            @keyframes Gradient {
                0% {
                    background-position: 0% 50%;
                }

                50% {
                    background-position: 100% 50%;
                }

                100% {
                    background-position: 0% 50%;
                }
            }
        }
    </style>

    <!-- Laravel Routes -->
    <?php echo app('Tightenco\Ziggy\BladeRouteGenerator')->generate(); ?>
</head>

<body>
    <div class="d-flex flex-column flex-root">
        <div class="d-flex flex-row flex-column-fluid">
            <!-- Sidebar -->
            <?php echo $__env->make('layouts.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <!-- Main Wrapper -->
            <div class="wrapper d-flex flex-column flex-row-fluid">
                <!-- Header -->
                <div class="container-fluid d-flex align-items-stretch justify-content-between px-0">
                    <?php echo $__env->make('layouts.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>

                <!-- Content -->
                <div class="content d-flex flex-column flex-column-fluid pt-7">
                    <?php echo $__env->yieldContent('header_toolbar'); ?>
                    <div>
                        <?php echo $__env->yieldContent('content'); ?>
                    </div>
                </div>

                <!-- Footer -->
                <div class="container-fluid">
                    <?php echo $__env->make('layouts.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </div>
        </div>

        <!-- Hidden Inputs -->
        <?php echo e(Form::hidden('currentLanguage', getLoginUser()->language ?? checkLanguageSession(), ['class' => 'currentLanguage'])); ?>

    </div>

    <!-- Modals -->
    <?php echo $__env->make('profile.changePassword', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('profile.email_notification', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('profile.changelanguage', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php if (isset($component)) { $__componentOriginal070c145072275a85e3cfc1763384945f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal070c145072275a85e3cfc1763384945f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modals.delete-confirmation','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modals.delete-confirmation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal070c145072275a85e3cfc1763384945f)): ?>
<?php $attributes = $__attributesOriginal070c145072275a85e3cfc1763384945f; ?>
<?php unset($__attributesOriginal070c145072275a85e3cfc1763384945f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal070c145072275a85e3cfc1763384945f)): ?>
<?php $component = $__componentOriginal070c145072275a85e3cfc1763384945f; ?>
<?php unset($__componentOriginal070c145072275a85e3cfc1763384945f); ?>
<?php endif; ?>

    <!-- Livewire Scripts -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>


    <!-- General Scripts -->
    <script src="<?php echo e(mix('js/third-party.js')); ?>"></script>
    <script src="<?php echo e(mix('js/pages.js')); ?>"></script>
    <script src="<?php echo e(asset('vendor/laravel-livewire-tables.min.js')); ?>"></script>
    <script src="<?php echo e(asset('vendor/laravel-livewire-tables-thirdparty.min.js')); ?>"></script>

    <!-- JavaScript Variables -->
    <?php
    $bloodGroupArr = json_encode(App\Models\Doctor::BLOOD_TYPE_ARRAY);
    $bloodGroupArr = html_entity_decode($bloodGroupArr);
    ?>
    <script data-turbo-eval="false">
        let usersRole = "<?php echo e(!empty(getLogInUser()->roles->first()) ? getLogInUser()->roles->first()->name : ''); ?>";
        let currencyIcon = '<?php echo e(getCurrencyIcon()); ?>';
        let isSetFirstFocus = true;
        let womanAvatar = "<?php echo e(url(asset('web/media/avatars/female.png'))); ?>";
        let manAvatar = "<?php echo e(url(asset('web/media/avatars/male.png'))); ?>";
        let changePasswordUrl = "<?php echo e(route('user.changePassword')); ?>";
        let updateLanguageURL = "<?php echo e(route('front.change.language')); ?>";
        let dashboardChartBGColor = "<?php echo e(Auth::user()->dark_mode ? '#13151f' : '#FFFFFF'); ?>";
        let dashboardChartFontColor = "<?php echo e(Auth::user()->dark_mode ? '#FFFFFF' : '#000000'); ?>";
        let userRole = "<?php echo e(getLogInUser()->hasRole('patient')); ?>";
        let checkLanguageSession = '<?php echo e(checkLanguageSession()); ?>';
        let noData = "<?php echo e(__('messages.common.no_data_available')); ?>";
        let defaultCountryCodeValue = "<?php echo e(getSettingValue('default_country_code')); ?>";
        let currentLoginUserId = "<?php echo e(getLogInUserId()); ?>";
        let bloodGroupArray = <?php echo json_encode($bloodGroupArr, 15, 512) ?>;
        Lang.setLocale(checkLanguageSession);
    </script>
</body>

</html><?php /**PATH C:\Projects\NORSUCLINIC\resources\views/layouts/app.blade.php ENDPATH**/ ?>