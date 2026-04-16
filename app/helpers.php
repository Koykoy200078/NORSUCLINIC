<?php

use App\Models\Barangay;
use App\Models\City;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\PurchasedMedicine;
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
use Illuminate\Support\Facades\Session;

if (! function_exists('getLogInUser')) {
    /**
     * @return Authenticatable|null
     */
    function getLogInUser()
    {
        // Always return current authenticated user
        // DO NOT use static cache as it causes wrong user data across different logins
        return Auth::user();
    }
}


if (! function_exists('getAppName')) {
    /**
     * @return mixed
     */
    function getAppName()
    {
        return \App\Services\SettingsService::get('clinic_name');
    }
}

if (! function_exists('getAppLogo')) {
    /**
     * @return mixed
     */
    function getAppLogo()
    {
        try {
            // Try to get logo setting using the same method as getSettingValue
            $logoValue = getSettingValue('logo');

            if (!empty($logoValue)) {
                return $logoValue;
            }

            // Return default logo path if no logo setting found
            return 'assets/image/norsu_logo.png';
        } catch (Exception $e) {
            // If any error occurs, return default logo
            return 'assets/image/norsu_logo.png';
        }
    }
}

if (! function_exists('getAppFavicon')) {
    /**
     * @return mixed
     */
    function getAppFavicon()
    {
        return \App\Services\SettingsService::get('favicon') ?? '';
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

if (! function_exists('getBarangays')) {
    /**
     * Get barangays for a specific city
     * @param int|array $cityId
     * @return array
     */
    function getBarangays($cityId)
    {
        return Barangay::where('city_id', $cityId)->pluck('name', 'id')->toArray();
    }
}

if (!function_exists('getDashboardURL')) {
    /**
     * Get the dashboard URL based on the user's role or permissions.
     * Cached for performance on repeated calls.
     *
     * @return string
     */
    function getDashboardURL()
    {
        // Get the authenticated user
        // DO NOT use static cache as it causes wrong dashboard URLs across different users
        $user = getLogInUser();

        // Return the default home URL if no user is authenticated
        if (!$user) {
            return RouteServiceProvider::HOME;
        }

        // Role-based dashboard URLs - check most common roles first
        if ($user->hasRole('clinic_admin')) {
            return 'admin/dashboard';
        } elseif ($user->hasRole('staff')) {
            return 'staff/dashboard';
        } elseif ($user->hasRole('doctor')) {
            return 'doctors/dashboard';
        } elseif ($user->hasRole('patient')) {
            return 'patients/dashboard';
        } else {
            // Fallback to permission-based check for admin users only
            if ($user->hasRole('clinic_admin')) {
                $permissions = Cache::remember("user_permissions_{$user->id}", 300, function () use ($user) {
                    return $user->getAllPermissions()->pluck('name')->toArray();
                });

                $permissionDashboardMap = [
                    'manage_admin_dashboard' => 'admin/dashboard',
                    'manage_doctors' => 'admin/doctors',
                    'manage_patients' => 'admin/patients',
                    'manage_staff' => 'admin/staff',
                ];

                foreach ($permissionDashboardMap as $permission => $url) {
                    if (in_array($permission, $permissions, true)) {
                        return $url;
                    }
                }
            }

            // Default fallback
            return RouteServiceProvider::HOME;
        }
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
            'bg-status-accepted',
            'bg-status-finished',
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
        return \App\Services\SettingsService::get($key);
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
        return Notification::whereReadAt(null)
            ->where('user_id', getLogInUserId())
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }
}

if (! function_exists('getCurrencyCode')) {
    /**
     * Returns hardcoded Philippine Peso currency code
     * @return string
     */
    function getCurrencyCode(): string
    {
        return 'PHP';
    }
}

if (! function_exists('getCurrencyFormat')) {
    /**
     * Format amount with Philippine Peso currency
     * @param string $currency
     * @param float $amount
     * @return string
     */
    function getCurrencyFormat($currency, $amount): string
    {
        return '₱' . number_format($amount, 2);
    }
}

if (! function_exists('getCurrentCurrency')) {
    /**
     * Returns hardcoded Philippine Peso symbol
     * @return string
     */
    function getCurrentCurrency(): string
    {
        return '₱';
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

if (! function_exists('filterLangChange')) {

    function filterLangChange($filterArray): array
    {
        foreach ($filterArray as $key => $value) {
            $array[$key] = __('messages.filter.' . strtolower($value));
        }

        return $array;
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

if (! function_exists('getCurrentLoginUserLanguageName')) {

    /**
     * @return mixed
     */
    function getCurrentLoginUserLanguageName()
    {
        return Auth::user()->language;
    }
}

if (! function_exists('generateUniqueAvailabilityNumber')) {

    function generateUniqueAvailabilityNumber()
    {
        do {
            $code = random_int(100000, 999999);
        } while (\App\Models\StockIn::where('availability_no', '=', $code)->first());

        return $code;
    }
}

if (! function_exists('getPatientUniqueId')) {

    function getPatientUniqueId()
    {
        return mb_strtoupper(Patient::generatePatientUniqueId());
    }
}

if (! function_exists('generateUniqueHistoryNumber')) {

    function generateUniqueHistoryNumber()
    {
        do {
            $code = random_int(1000, 9999);
        } while (\App\Models\DispenseRecord::where('history_number', '=', $code)->first());

        return $code;
    }
}

if (! function_exists('generateUniqueBillNumber')) {
    /**
     * @deprecated Use generateUniqueHistoryNumber() instead
     * @return int
     */
    function generateUniqueBillNumber()
    {
        return generateUniqueHistoryNumber();
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

        return ($implode_to_Rupees ? $implode_to_Rupees . 'PHP' : '') . $get_paise;
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
        return Auth::user()->hasRole(['doctor']);
    }
}

if (! function_exists('isRole')) {

    function isRole(string $role)
    {
        $user = getLogInUser();
        if ($user && $user->hasRole($role)) {
            return true;
        }

        return false;
    }
}

if (! function_exists('getRouteByRole')) {
    /**
     * Get route based on user role
     * @param string $baseName
     * @param array $parameters
     * @return string
     */
    function getRouteByRole(string $baseName, array $parameters = []): string
    {
        $user = getLogInUser();

        if ($user->hasRole('clinic_admin')) {
            $routeName = 'admin.' . $baseName;
        } elseif ($user->hasRole('staff')) {
            $routeName = 'staff.' . $baseName;
        } elseif ($user->hasRole('doctor')) {
            $routeName = 'doctors.' . $baseName;
        } elseif ($user->hasRole('patient')) {
            $routeName = 'patients.' . $baseName;
        } else {
            $routeName = $baseName;
        }

        return route($routeName, $parameters);
    }
}

if (! function_exists('getPrescriptionRoute')) {
    /**
     * Get prescription route based on user role
     * @param string $action
     * @param array $parameters
     * @return string
     */
    function getPrescriptionRoute(string $action, array $parameters = []): string
    {
        $user = getLogInUser();

        if ($user->hasRole('clinic_admin')) {
            return route('admin.prescriptions.' . $action, $parameters);
        } elseif ($user->hasRole('doctor')) {
            return route('doctors.prescriptions.' . $action, $parameters);
        } elseif ($user->hasRole('staff')) {
            return route('staff.prescriptions.' . $action, $parameters);
        } else {
            return route('prescriptions.' . $action, $parameters);
        }
    }
}

if (!function_exists('getExpiringMedicinesCount')) {
    /**
     * Get count of medicines expiring within a month
     *
     * @return int
     */
    function getExpiringMedicinesCount()
    {
        // Cache the result for 1 hour to avoid repeated database queries
        return Cache::remember('expiring_medicines_count', 3600, function () {
            $oneMonthFromNow = Carbon::now()->addMonth();
            $today = Carbon::now();

            // Count unique medicines that have purchased batches expiring within a month
            // and still have available quantity
            return PurchasedMedicine::whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [$today, $oneMonthFromNow])
                ->whereHas('medicines', function ($query) {
                    $query->where('available_quantity', '>', 0);
                })
                ->distinct('medicine_id')
                ->count('medicine_id');
        });
    }
}

if (! function_exists('getCurrencyIcon')) {
    /**
     * Get the currency icon (hardcoded to Philippine Peso)
     *
     * @return string
     */
    function getCurrencyIcon(): string
    {
        return '₱';
    }
}
