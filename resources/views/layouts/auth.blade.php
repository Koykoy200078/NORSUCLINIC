<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Meta Information -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ getAppName() }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset(getAppFavicon()) }}" type="image/png">

    <!-- Fonts -->
    <link rel="stylesheet" href="{{ asset('css/poppins.css') }}">

    <!-- General CSS Files -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/third-party.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ mix('assets/css/pages.css') }}">

    <!-- CSS Libraries -->
    @stack('css')
</head>

<body
    class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed aside-enabled aside-fixed">
    <div class="d-flex flex-column flex-root">
        <div
            class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed authImage">
            @yield('content')
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container-fluid padding-0">
            <div class="row align-items-center justify-content-center">
                <div class="col-xl-6">
                    <div class="copyright text-center text-muted">
                        {{ __('messages.all_rights_reserved') }} &copy; {{ date('Y') }}
                        <a href="/" class="font-weight-bold ml-1" target="_blank">{{ getAppName() }}</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('backend/js/vendor.js') }}"></script>
    <script src="{{ mix('assets/js/auto_fill/auto_fill.js') }}"></script>
    {{-- <script src="{{ asset('backend/js/3rd-party-custom.js') }}"></script> --}}
    <script src="{{ mix('js/custom-auth.js') }}"></script>

    @stack('scripts')

    <script>
        $(document).ready(function() {
            $('.alert').delay(5000).slideUp(300);
        });
    </script>
</body>

</html>
