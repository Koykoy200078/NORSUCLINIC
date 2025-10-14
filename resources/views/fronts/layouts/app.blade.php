<!DOCTYPE html>
<html dir="ltr" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Meta Information -->
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="author" content="{{ getAppName() }}" />
    <meta name="robots" content="index, follow">
    <link rel="icon" href="{{ asset(getAppFavicon()) }}" type="image/png">

    <!-- Document Title -->
    <title>@yield('front-title') | {{ getAppName() }}</title>

    <!-- Stylesheets -->
    <!-- Google Fonts -->
    <link rel="stylesheet" href="{{ asset('css/montserrat.css') }}">
    <link rel="stylesheet" href="{{ asset('css/poppins.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('css/fontawesome.all.min.css') }}">

    <!-- Third-Party and Custom Styles -->
    <link href="{{ mix('css/front-third-party.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ mix('css/front-pages.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datepicker/bootstrap-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/intlTelInput.css') }}">

    <!-- Scripts -->
    <script src="{{ asset('messages.js') }}"></script>
    <script src="{{ asset('assets/front/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/front-third-party.js') }}"></script>
    <script src="{{ asset('js/front-pages.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>

    <!-- JavaScript Variables -->
    <script data-turbo-eval="false">
        let currencyIcon = '{{ getCurrencyIcon() }}';
        let isSetFirstFocus = false;
        let csrfToken = "{{ csrf_token() }}";
        let defaultCountryCodeValue = "{{ getSettingValue('default_country_code') }}";
    </script>

    <!-- Payment Configuration -->
    <script data-turbo-eval="false">
        let appointmentStripePaymentUrl = "{{ url('appointment-stripe-charge') }}";
        let stripe = '';
        @if(config('services.stripe.key'))
        stripe = Stripe("{{ config('services.stripe.key') }}");
        @endif

        let manually = "{{ \App\Models\PatientQueue::MANUALLY }}";
        let paypal = "{{ \App\Models\PatientQueue::PAYPAL }}";
        let stripeMethod = "{{ \App\Models\PatientQueue::STRIPE }}";
        let authorizeMethod = "{{ \App\Models\PatientQueue::AUTHORIZE }}";
        let paytmMethod = "{{ \App\Models\PatientQueue::PAYTM }}";

        let checkLanguageSession = '{{ checkLanguageSession() }}';
        Lang.setLocale(checkLanguageSession);
    </script>

    <!-- Laravel Routes -->
    @routes
</head>

<body>
    <!-- Header -->
    @include('fronts.layouts.header')

    <!-- Main Content -->
    @yield('front-content')

    <!-- Footer -->
    @include('fronts.layouts.footer')
</body>

</html>

