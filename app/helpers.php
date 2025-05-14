<?php

use App\Models\City;
use App\Models\Currency;
use App\Models\DoctorSession;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\PaymentGateway;
use App\Models\Setting;
use App\Models\State;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\HigherOrderBuilderProxy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Stripe\Stripe;

if (! function_exists('getLogInUser')) {
    /**
     * @return Authenticatable|null
     */
    function getLogInUser()
    {
        return Auth::user();
    }
}


if (! function_exists('getAppName')) {
    /**
     * @return mixed
     */
    function getAppName()
    {
        static $setting;
        if (empty($setting)) {
            $setting = Setting::all()->keyBy('key');
        }

        return $setting['clinic_name']->value;
    }
}

if (! function_exists('getAppLogo')) {
    /**
     * @return mixed
     */
    function getAppLogo()
    {
        static $setting;
        if (empty($setting)) {

            $setting = Setting::all()->keyBy('key');
        }

        return $setting['logo']->value;
    }
}

if (! function_exists('getAppFavicon')) {
    /**
     * @return mixed
     */
    function getAppFavicon()
    {
        static $setting;
        if (empty($setting)) {
            $setting = Setting::all()->keyBy('key');
        }

        return $setting['favicon']->value;
    }
}

if (! function_exists('getLogInUserId')) {
    /**
     * @return int
     */
    function getLogInUserId()
    {
        return Auth::user()->id;
    }
}

if (! function_exists('getStates')) {
    /**
     * @return mixed
     */
    function getStates($countryId)
    {
        return State::where('country_id', $countryId)->toBase()->pluck('name', 'id')->toArray();
    }
}

if (! function_exists('getCities')) {
    /**
     * @return mixed
     */
    function getCities($stateId)
    {
        return City::where('state_id', $stateId)->pluck('name', 'id')->toArray();
    }
}

if (!function_exists('getDashboardURL')) {
    /**
     * Get the dashboard URL based on the user's role or permissions.
     *
     * @return string
     */
    function getDashboardURL()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Return the default home URL if no user is authenticated
        if (!$user) {
            return RouteServiceProvider::HOME;
        }

        // Role-based dashboard URLs
        $roleDashboardMap = [
            'clinic_admin' => 'admin/dashboard',
            'doctor' => 'doctors/dashboard',
        ];

        // Check if the user has a specific role and return the corresponding dashboard URL
        foreach ($roleDashboardMap as $role => $url) {
            if ($user->hasRole($role)) {
                return $url;
            }
        }

        // Permission-based dashboard URLs
        $permissionDashboardMap = [
            'manage_admin_dashboard' => 'admin/dashboard',
            'manage_doctors' => 'admin/doctors',
            'manage_patients' => 'admin/patients',
            'manage_staff' => 'admin/staff',
            'manage_appointments' => 'admin/appointments',
            'manage_patient_visits' => 'admin/visits',
            'manage_settings' => 'admin/settings',
            'manage_specialties' => 'admin/specializations',
            'manage_services' => 'admin/services',
            'manage_front_cms' => 'admin/cms',
            'manage_transactions' => 'admin/transactions',
        ];

        // Get all user permissions
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        // Check if the user has a specific permission and return the corresponding dashboard URL
        foreach ($permissionDashboardMap as $permission => $url) {
            if (in_array($permission, $permissions, true)) {
                return $url;
            }
        }

        // Special handling for 'manage_request_documents' permission
        if (in_array('manage_request_documents', $permissions, true)) {
            if ($user->hasRole('clinic_admin')) {
                return 'admin/request-documents';
            } elseif ($user->hasRole('staff')) {
                return 'staff/request-documents';
            } elseif ($user->hasRole('doctor')) {
                return 'doctor/request-documents';
            }
        }

        // Return the default home URL if no role or permission matches
        return RouteServiceProvider::HOME;
    }
}

if (! function_exists('getDoctorSessionURL')) {

    /**
     * @return string
     */
    function getDoctorSessionURL()
    {
        if (Auth::user()->hasRole('clinic_admin')) {
            return 'admin/doctor-sessions';
        } elseif (Auth::user()->hasRole('staff')) {
            return 'staff/doctor-sessions';
        } elseif (Auth::user()->hasRole('doctor')) {
            return 'doctors/doctor-sessions';
        } elseif (Auth::user()->hasRole('patient')) {
            return 'patients/doctor-sessions';
        }

        return RouteServiceProvider::HOME;
    }
}

if (! function_exists('getDoctorSessionTime')) {

    function getDoctorSessionTime($doctor_id)
    {
        $doctorSession = DoctorSession::whereDoctorId($doctor_id)->get();
    }
}

if (! function_exists('getSlotByGap')) {

    function getSlotByGap($startTime, $endTime)
    {
        $period = new CarbonPeriod(
            $startTime,
            '15 minutes',
            $endTime
        ); // for create use 24 hours format later change format
        $slots = [];
        foreach ($period as $item) {
            $slots[$item->format('h:i A')] = $item->format('h:i A');
        }

        return $slots;
    }
}

if (! function_exists('getSchedulesTimingSlot')) {

    function getSchedulesTimingSlot()
    {
        $period = new CarbonPeriod('00:00', '15 minutes', '24:00'); // for create use 24 hours format later change format
        $slots = [];
        foreach ($period as $item) {
            $slots[$item->format('h:i A')] = $item->format('h:i A');
        }

        return $slots;
    }
}

if (! function_exists('getBadgeColor')) {

    /**
     * @return string
     */
    function getBadgeColor($index)
    {
        $colors = [
            'primary',
            'danger',
            'success',
            'info',
            'warning',
            'dark',
        ];

        $index = $index % 6;
        if (Auth::user()->dark_mode) {
            array_splice($colors, 5, 1);
            array_push($colors, 'bg-white');
        }

        return $colors[$index];
    }
}

if (! function_exists('getBadgeStatusColor')) {

    /**
     * @return string
     */
    function getBadgeStatusColor($status)
    {
        $colors = [
            'danger',
            'primary',
            'success',
            'warning',
            'danger',
        ];

        return $colors[$status];
    }
}
if (! function_exists('getLoginDoctorSessionUrl')) {

    function getLoginDoctorSessionUrl(): string
    {
        return DoctorSession::toBase()->whereDoctorId(getLogInUser()->doctor->id)->exists() ? route('doctors.doctor.schedule.edit') : route('doctors.doctor-sessions.create');
    }
}

if (! function_exists('doctorSessionActiveUrl')) {

    function doctorSessionActiveUrl(): string
    {
        return DoctorSession::toBase()->whereDoctorId(getLogInUser()->doctor->id)->exists() ? 'doctors/doctor-schedule-edit*' : 'doctors/doctor-sessions/create*';
    }
}

if (! function_exists('getStatusBadgeColor')) {

    /**
     * @return string
     */
    function getStatusBadgeColor($index)
    {
        $colors = [
            'danger',
            'primary',
            'success',
            'warning',
        ];

        $index = $index % 4;

        return $colors[$index];
    }
}

if (! function_exists('getStatusColor')) {

    /**
     * @return string
     */
    function getStatusColor($index)
    {
        $colors = [
            '#F46387',
            '#399EF7',
            '#50CD89',
            '#FAC702',
        ];

        $index = $index % 4;

        return $colors[$index];
    }
}

if (! function_exists('getStatusClassName')) {

    /**
     * @return string
     */
    function getStatusClassName($status)
    {
        $classNames = [
            'bg-status-canceled',
            'bg-status-booked',
            'bg-status-checkIn',
            'bg-status-checkOut',
        ];

        $index = $status % 4;

        return $classNames[$index];
    }
}

if (! function_exists('getSettingValue')) {

    /**
     * @return mixed
     */
    function getSettingValue($key)
    {
        static $setting;

        if (empty($setting)) {
            $setting = Setting::all()->keyBy('key');
        }

        // Check and update the URL for specific keys
        if (in_array($key, ['about_image_1', 'about_image_2', 'about_image_3']) && isset($setting[$key])) {
            $value = $setting[$key]->value;
            if (str_starts_with($value, 'http://localhost')) {
                $value = request()->getSchemeAndHttpHost() . parse_url($value, PHP_URL_PATH);
            }
            return $value;
        }

        return $setting[$key]->value;
    }
}

if (! function_exists('setEmailLowerCase')) {

    /**
     * @return string
     */
    function setEmailLowerCase($email)
    {
        return strtolower($email);
    }
}

if (! function_exists('getUserLanguages')) {

    /**
     * @return string[]
     */
    function getUserLanguages()
    {
        $language = User::LANGUAGES;
        asort($language);

        return $language;
    }
}

if (! function_exists('getCurrencyIcon')) {

    /**
     * @return mixed
     */
    function getCurrencyIcon()
    {
        static $setting;

        if (empty($setting)) {

            $setting = Setting::all()->keyBy('key');
        }

        static $currencies;

        if (empty($currencies)) {
            $currencies = Currency::all()->keyBy('id');
        }

        $currencyId = $setting['currency']->value;
        $currency = $currencies[$currencyId];
        $currencyIcon = $currency->currency_icon ?? '₱';

        return $currencyIcon;
    }
}

if (! function_exists('setStripeApiKey')) {

    function setStripeApiKey()
    {
        Stripe::setApiKey(config('services.stripe.secret_key'));
    }
}

if (! function_exists('getCurrencyCode')) {

    /**
     * @return HigherOrderBuilderProxy|mixed|string
     */
    function getCurrencyCode()
    {
        static $setting;
        if (empty($setting)) {
            $setting = Setting::all()->keyBy('key');
        }

        $currencyId = $setting['currency'];

        $currencies = Cache::get('currency', null);

        if (empty($currencies)) {
            $currency = Currency::find($currencyId)->first();
            Cache::put('currency', $currency);

            return $currency->currency_code;
        }

        return $currencies->currency_code;
    }
}

if (! function_exists('version')) {

    function version()
    {
        if (config('app.is_version') == 'true') {
            $composerFile = file_get_contents('../composer.json');
            $composerData = json_decode($composerFile, true);
            $currentVersion = $composerData['version'];

            return 'v' . $currentVersion;
        }
    }
}

if (! function_exists('getNotification')) {
    function getNotification()
    {
        return Notification::whereReadAt(null)->where(
            'user_id',
            getLogInUserId()
        )->orderByDesc('created_at')->get();
    }
}

if (! function_exists('getNotificationIcon')) {

    function getNotificationIcon($notificationFor)
    {
        switch ($notificationFor) {
            case $notificationFor == Notification::CHECKOUT:
                return 'fas fa-check-square';
            case $notificationFor == Notification::PAYMENT_DONE:
                return 'fas fa-money-bill-wave';
            case $notificationFor == Notification::BOOKED:
                return 'fas fa-calendar-alt';
            case $notificationFor == Notification::CANCELED:
                return 'fas fa-calendar-times';
            case $notificationFor == Notification::REVIEW:
                return 'fas fa-star';
            case $notificationFor == Notification::LIVE_CONSULTATION:
                return 'fas fa-video';
        }
    }
}

if (! function_exists('checkLanguageSession')) {

    /**
     * @return mixed|null
     */
    function checkLanguageSession()
    {
        if (Session::has('languageName')) {

            return Session::get('languageName');
        } else {
            return getSettingValue('language');
        }

        return 'en';
    }
}

if (! function_exists('getCurrentLanguageName')) {

    /**
     * @return mixed|null
     */
    function getCurrentLanguageName()
    {
        return User::LANGUAGES[checkLanguageSession()];
    }
}

if (! function_exists('getMonth')) {

    function getMonth()
    {
        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec',
        ];

        return $months;
    }
}

if (! function_exists('getAllPaymentStatus')) {

    /**
     * @return string[]
     */
    function getAllPaymentStatus()
    {
        $paymentGateway = \App\Models\Appointment::PAYMENT_METHOD;

        $selectedPaymentGateway = PaymentGateway::pluck('payment_gateway', 'payment_gateway_id')->toArray();

        $paymentMethodToReturn = array_intersect($paymentGateway, $selectedPaymentGateway);

        return $paymentMethodToReturn;
    }
}

if (! function_exists('getPaymentGateway')) {

    /**
     * @return string[]
     */
    function getPaymentGateway()
    {

        $paymentGateway = \App\Models\Appointment::PAYMENT_GATEWAY;
        $selectedPaymentGateway = PaymentGateway::pluck('payment_gateway')->toArray();

        $paymentGatewayToReturn = array_intersect($paymentGateway, $selectedPaymentGateway);

        return $paymentGatewayToReturn;
    }
}

if (! function_exists('getWeekDate')) {

    function getWeekDate(): string
    {
        $date = Carbon::now();
        $startOfWeek = $date->startOfWeek()->subDays(1);
        $startDate = $startOfWeek->format('Y-m-d');
        $endOfWeek = $startOfWeek->addDays(6);
        $endDate = $endOfWeek->format('Y-m-d');

        return $startDate . ' - ' . $endDate;
    }
}

if (! function_exists('getYearDate')) {

    function getYearDate(): string
    {
        $date = Carbon::now()->year;
        $startDate = Carbon::createFromDate($date, 1, 1)->startOfDay();
        $endDate = Carbon::createFromDate($date, 12, 31)->endOfDay();

        return $startDate . ' - ' . $endDate;
    }
}

if (! function_exists('getCurrencyFormat')) {

    function getCurrencyFormat($currencies, $amount): string
    {
        return moneyFormat($amount, $currencies);
    }
}

if (! function_exists('filterLangChange')) {

    function filterLangChange($filterArray): array
    {
        foreach ($filterArray as $key => $value) {
            $array[$key] = __('messages.filter.' . strtolower($value));
        }

        return $array;
    }
}

if (! function_exists('paymentMethodLangChange')) {

    function paymentMethodLangChange($paymentMethodNameArray): array
    {
        $array = [];
        foreach ($paymentMethodNameArray as $key => $value) {
            $array[$key] = __('messages.payment_method.' . strtolower($value));
        }

        return $array;
    }
}

if (! function_exists('zeroDecimalCurrencies')) {

    function zeroDecimalCurrencies(): array
    {
        return [
            'BIF',
            'CLP',
            'DJF',
            'GNF',
            'JPY',
            'KMF',
            'KRW',
            'MGA',
            'PYG',
            'RWF',
            'UGX',
            'VND',
            'VUV',
            'XAF',
            'XOF',
            'XPF',
        ];
    }
}

if (! function_exists('canDelete')) {

    /**
     * @param  array  $models
     * @param  string  $columnName
     * @param  int  $id
     * @return bool
     */
    function canDelete($models, $columnName, $id)
    {
        foreach ($models as $model) {
            $result = $model::where($columnName, $id)->exists();
            if ($result) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('preparePhoneNumber')) {

    /**
     * @param  array  $input
     * @param  string  $key
     * @return string|null
     */
    function preparePhoneNumber($input, $key)
    {
        return (! empty($input[$key])) ? '+' . $input['country_code'] . $input[$key] : null;
    }
}

if (! function_exists('getCurrentCurrency')) {

    /**
     * @return mixed
     */
    function getCurrentCurrency()
    {
        /** @var Setting $currentCurrency */
        static $currentCurrency;

        if (empty($currentCurrency)) {
            $currentCurrency = Setting::where('key', 'currency')->first();
        }
        return $currentCurrency->value;
    }
}

if (! function_exists('getCurrentLoginUserLanguageName')) {

    /**
     * @return mixed
     */
    function getCurrentLoginUserLanguageName()
    {
        return Auth::user()->language;
    }
}

if (! function_exists('generateUniquePurchaseNumber')) {

    function generateUniquePurchaseNumber()
    {
        do {
            $code = random_int(100000, 999999);
        } while (\App\Models\PurchaseMedicine::where('purchase_no', '=', $code)->first());

        return $code;
    }
}

if (! function_exists('getPatientUniqueId')) {

    function getPatientUniqueId()
    {
        return mb_strtoupper(Patient::generatePatientUniqueId());
    }
}

if (! function_exists('generateUniqueBillNumber')) {

    function generateUniqueBillNumber()
    {
        do {
            $code = random_int(1000, 9999);
        } while (\App\Models\MedicineBill::where('bill_number', '=', $code)->first());

        return $code;
    }
}

if (! function_exists('getAmountToWord')) {

    function getAmountToWord(float $amount): string
    {
        $amount_after_decimal = round($amount - ($num = floor($amount)), 2);
        $count_length = strlen($num);
        $x = 0;
        $string = [];
        $change_words = [
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Forty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety',
        ];
        $here_digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];
        while ($x < $count_length) {
            $get_divider = ($x == 2) ? 10 : 100;
            $amount = floor($num % $get_divider);
            $num = floor($num / $get_divider);
            $x += $get_divider == 10 ? 1 : 2;
            if ($amount) {
                $add_plural = (($counter = count($string)) && $amount > 9) ? 's' : null;
                $amt_hundred = ($counter == 1 && $string[0]) ? ' and ' : null;
                $string[] = ($amount < 21) ? $change_words[$amount] . ' ' . $here_digits[$counter] . $add_plural . '
       ' . $amt_hundred : $change_words[floor($amount / 10)] . ' ' . $change_words[$amount % 10] . '
       ' . $here_digits[$counter] . $add_plural . ' ' . $amt_hundred;
            } else {
                $string[] = null;
            }
        }
        $implode_to_Rupees = implode('', array_reverse($string));
        $get_paise = ($amount_after_decimal > 0) ? 'And ' . ($change_words[$amount_after_decimal / 10] . '
   ' . $change_words[$amount_after_decimal % 10]) . ' Paise' : '';

        return ($implode_to_Rupees ? $implode_to_Rupees . getCurrencyCode() : '') . $get_paise;
    }
}

if (! function_exists('canAccessRecord')) {

    /**
     * @return bool
     */
    function canAccessRecord($model, $id)
    {
        $recordExists = $model::where('id', $id)->exists();

        if ($recordExists) {
            return true;
        }

        return false;
    }
}

if (! function_exists('getLoggedinDoctor')) {

    /**
     * @return bool
     */
    function getLoggedinDoctor()
    {
        return Auth::user()->hasRole(['Doctor']);
    }
}

if (! function_exists('isRole')) {

    function isRole(string $role)
    {

        if (getLogInUser()->hasRole($role)) {

            return true;
        }

        return false;
    }
}

if (!function_exists('paypalCurrencySupports')) {
    function paypalCurrencySupports()
    {
        $paypal = ['AUD', 'BRL', 'CAD', 'CNY', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'USD', 'SGD', 'SEK', 'CHF', 'THB'];

        if (!in_array(strtoupper(getCurrencyCode()), $paypal)) {
            return  false;
        }
        return  true;
    }
}

if (!function_exists('authorizedCurrencySupports')) {
    function authorizedCurrencySupports()
    {
        $authorized = ['USD', 'CAD', 'GBP', 'EUR', 'CHF', 'DKK', 'NOK', 'PLN', 'SEK'];

        if (!in_array(strtoupper(getCurrencyCode()), $authorized)) {
            return  false;
        }
        return  true;
    }
}

if (!function_exists('paytmCurrencySupports')) {
    function paytmCurrencySupports()
    {
        $paytm = [
            'AFN',
            'ARS',
            'AZN',
            'AUD',
            'AED',
            'BBD',
            'BDT',
            'BGN',
            'BHD',
            'BMD',
            'BND',
            'BRL',
            'BWP',
            'BZD',
            'CLP',
            'COP',
            'CRC',
            'CZK',
            'CAD',
            'CHF',
            'CNY',
            'DKK',
            'DOP',
            'EGP',
            'EUR',
            'GTQ',
            'GBP',
            'HKD',
            'HNL',
            'HRK',
            'HUF',
            'ILS',
            'JPY',
            'JMD',
            'JOD',
            'KES',
            'KRW',
            'KWD',
            'KZT',
            'LBP',
            'LKR',
            'LTL',
            'LVL',
            'MOP',
            'MUR',
            'MXN',
            'MYR',
            'NGN',
            'NOK',
            'NZD',
            'OMR',
            'PEN',
            'PHP',
            'PKR',
            'PLN',
            'QAR',
            'RON',
            'RUB',
            'SCR',
            'SEK',
            'SAR',
            'SGD',
            'THB',
            'TRY',
            'TTD',
            'TWD',
            'TZS',
            'UAH',
            'USD',
            'XCD',
            'XOF',
            'ZAR'
        ];

        if (!in_array(strtoupper(getCurrencyCode()), $paytm)) {
            return  false;
        }
        return  true;
    }
}
