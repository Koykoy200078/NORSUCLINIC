<!DOCTYPE html>
<html dir="ltr" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="author" content="{{ getAppName() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="index, follow">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset(getAppFavicon()) }}" type="image/png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Stylesheets -->
    <link href="{{ mix('css/front-third-party.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ mix('css/front-pages.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap-datepicker/bootstrap-datepicker.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/intlTelInput.css') }}">

    <!-- Document Title -->
    <title>@yield('front-title') | {{ getAppName() }}</title>

    @routes
    <!-- Scripts -->
    <script src="{{ asset('messages.js') }}"></script>
    <script src="{{ asset('assets/front/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ mix('js/front-third-party.js') }}"></script>
    <script src="{{ mix('js/front-pages.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script src="https://www.google.com/recaptcha/api.js"></script>

    <!-- Dynamic Variables -->
    <script>
        let currencyIcon = '{{ getCurrencyIcon() }}';
        let isSetFirstFocus = false;
        let csrfToken = "{{ csrf_token() }}";
        let defaultCountryCodeValue = "{{ getSettingValue('default_country_code') }}";
        let manually = "{{ \App\Models\Appointment::MANUALLY }}";
        let authorizeMethod = "{{ \App\Models\Appointment::AUTHORIZE }}";
        let checkLanguageSession = '{{ checkLanguageSession() }}';
        Lang.setLocale(checkLanguageSession);
    </script>
</head>

<body>
    <!-- Header -->
    @include('fronts.layouts.header')

    <!-- Content -->
    @yield('front-content')

    <!-- Footer -->
    @include('fronts.layouts.footer')
</body>

</html>