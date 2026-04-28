<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Meta Information -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ getAppName() }}</title>
    <link rel="icon" href="{{ asset(getAppFavicon()) }}" type="image/png">

    <!-- General CSS Files -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/third-party.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ mix('assets/css/pages.css') }}">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet" />

    <!-- Conditional Styles -->
    @if (!Auth::user()->dark_mode)
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/plugins.css') }}">
    @else
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style-dark.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/plugins.dark.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ mix('assets/css/custom-pages-dark.css') }}">
    @endif

    <!-- Fonts -->
    <link rel="stylesheet" href="{{ asset('css/poppins.css') }}">

    <!-- Livewire Styles -->
    @livewireStyles
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/laravel-livewire-tables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/laravel-livewire-tables-thirdparty.min.css') }}">

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
        }
    </style>

    <!-- Laravel Routes -->
    @routes
</head>

<body>
    <div class="d-flex flex-column flex-root">
        <div class="d-flex flex-row flex-column-fluid">
            <!-- Sidebar -->
            @include('layouts.sidebar')

            <!-- Main Wrapper -->
            <div class="wrapper d-flex flex-column flex-row-fluid">
                <!-- Header -->
                <div class="container-fluid d-flex align-items-stretch justify-content-between px-0">
                    @include('layouts.header')
                </div>

                <!-- Content -->
                <div class="content d-flex flex-column flex-column-fluid pt-7">
                    @yield('header_toolbar')
                    <div>
                        @yield('content')
                    </div>
                </div>

                <!-- Footer -->
                <div class="container-fluid">
                    @include('layouts.footer')
                </div>
            </div>
        </div>

        <!-- Hidden Inputs -->
        {{ Form::hidden('currentLanguage', getLoginUser()->language ?? checkLanguageSession(), ['class' => 'currentLanguage']) }}
    </div>

    <!-- Modals -->
    @include('profile.changePassword')
    @include('profile.email_notification')
    @include('profile.changelanguage')
    <x-modals.delete-confirmation />

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- General Scripts -->
    <script src="{{ mix('js/third-party.js') . '&v=' . filemtime(public_path('js/third-party.js')) }}"></script>
    <script src="{{ mix('js/pages.js') }}"></script>
    <script src="{{ asset('vendor/laravel-livewire-tables.min.js') }}"></script>
    <script src="{{ asset('vendor/laravel-livewire-tables-thirdparty.min.js') }}"></script>

    <!-- JavaScript Variables -->
    @php
    $bloodGroupArr = json_encode(App\Models\Doctor::BLOOD_TYPE_ARRAY);
    $bloodGroupArr = html_entity_decode($bloodGroupArr);
    @endphp
    <script data-turbo-eval="false">
        let usersRole = "{{ !empty(getLogInUser()->roles->first()) ? getLogInUser()->roles->first()->name : '' }}";
        window.currentPanel = '{{ isRole("clinic_admin") ? "admin" : ((isRole("staff") || isRole("nurse")) ? "staff" : (isRole("doctor") ? "doctors" : "patients")) }}';
        let currencyIcon = '{{ getCurrencyIcon() }}';
        let isSetFirstFocus = true;
        let womanAvatar = "{{ url(asset('web/media/avatars/female.png')) }}";
        let manAvatar = "{{ url(asset('web/media/avatars/male.png')) }}";
        let changePasswordUrl = "{{ route('user.changePassword') }}";
        let updateLanguageURL = "{{ route('front.change.language') }}";
        let dashboardChartBGColor = "{{ Auth::user()->dark_mode ? '#13151f' : '#FFFFFF' }}";
        let dashboardChartFontColor = "{{ Auth::user()->dark_mode ? '#FFFFFF' : '#000000' }}";
        let userRole = "{{ getLogInUser()->hasRole('patient') }}";
        let checkLanguageSession = '{{ checkLanguageSession() }}';
        let noData = "{{ __('messages.common.no_data_available') }}";
        let defaultCountryCodeValue = "{{ getSettingValue('default_country_code') }}";
        let currentLoginUserId = "{{ getLogInUserId() }}";
        let bloodGroupArray = @json($bloodGroupArr);
        Lang.setLocale(checkLanguageSession);
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof IOInitSideBarCollapse === 'function') {
                IOInitSideBarCollapse();
            }
        });
    </script>

    @yield('page_js')
</body>

</html>