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

        let manually = "{{ \App\Models\Appointment::MANUALLY }}";
        let paystack = "{{ \App\Models\Appointment::PAYSTACK }}";
        let paypal = "{{ \App\Models\Appointment::PAYPAL }}";
        let stripeMethod = "{{ \App\Models\Appointment::STRIPE }}";
        let razorpayMethod = "{{ \App\Models\Appointment::RAZORPAY }}";
        let authorizeMethod = "{{ \App\Models\Appointment::AUTHORIZE }}";
        let paytmMethod = "{{ \App\Models\Appointment::PAYTM }}";

        let checkLanguageSession = '{{ checkLanguageSession() }}';
        Lang.setLocale(checkLanguageSession);

        let options = {
            key: "{{ config('payments.razorpay.key') }}",
            amount: 0, // 100 refers to 1
            currency: 'PHP',
            name: "{{ getAppName() }}",
            order_id: '',
            description: '',
            image: '{{ asset(getAppLogo()) }}', // logo here
            callback_url: "{{ route('razorpay.success') }}",
            prefill: {
                email: '', // recipient email here
                name: '', // recipient name here
                contact: '', // recipient phone here
                appointmentID: '', // appointmentID here
            },
            readonly: {
                name: 'true',
                email: 'true',
                contact: 'true',
            },
            theme: {
                color: '#4FB281',
            },
            modal: {
                ondismiss: function() {
                    $('.book-appointment-message').css('display', 'block');
                    let response =
                        '<div class="gen alert alert-danger">Appointment created successfully and payment not completed.</div>';
                    $('.book-appointment-message').html(response).delay(5000).hide('slow');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                },
            },
        };
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