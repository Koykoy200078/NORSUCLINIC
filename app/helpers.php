<?php

use App\Models\Barangay;
use App\Models\City;
use App\Models\MedicineBatch;
use App\Models\Notification;
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
use Illuminate\Support\Facades\Request;

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
                // Stored as an absolute URL built from APP_URL when it was uploaded: show it through the address the
                // visitor used, or it points at "localhost" on every other PC of the LAN. R3-M4.
                return normalizeLocalUrl((string) $logoValue);
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
        return normalizeLocalUrl((string) (\App\Services\SettingsService::get('favicon') ?? ''));
    }
}

if (! function_exists('normalizeLocalUrl')) {
    /**
     * Normalize stored absolute URLs to the current request host.
     *
     * @param  string|null  $url
     * @return string|null
     */
    function normalizeLocalUrl(?string $url): ?string
    {
        if (! $url) {
            return $url;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! $host) {
            return $url;
        }

        $normalizedHost = strtolower((string) $host);
        $currentHost = strtolower((string) request()->getHost());
        if ($normalizedHost === $currentHost) {
            return $url;
        }

        $configuredAppHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $configuredLocalHosts = config('app.local_media_hosts', []);
        if (! is_array($configuredLocalHosts)) {
            $configuredLocalHosts = [];
        }

        $localHosts = array_values(array_filter(array_map(
            static fn($localHost): string => strtolower(trim((string) $localHost)),
            array_merge($configuredLocalHosts, [(string) $configuredAppHost])
        )));

        if (! in_array($normalizedHost, $localHosts, true)) {
            return $url;
        }

        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $query = parse_url($url, PHP_URL_QUERY);
        $fragment = parse_url($url, PHP_URL_FRAGMENT);

        $baseUrl = request()->getSchemeAndHttpHost();
        if (! $baseUrl) {
            $baseUrl = rtrim((string) config('app.url'), '/');
        }

        $normalized = rtrim($baseUrl, '/') . $path;
        if ($query) {
            $normalized .= '?' . $query;
        }
        if ($fragment) {
            $normalized .= '#' . $fragment;
        }

        return $normalized;
    }
}

if (! function_exists('getLogInUserId')) {
    /**
     * @return int
     */
    function getLogInUserId()
    {
        // Null-safe: this is also called from console commands / seeders where nobody is signed in.
        return Auth::id();
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

if (!function_exists('getDashboardRouteName')) {
    /**
     * Name of the dashboard route that belongs to the user's role, or null when the user has none (no role, or the
     * retired patient role whose portal no longer exists).
     *
     * Callers that draw a "Dashboard" link must hide it on null instead of guessing a route: every guess lands on a
     * page another role owns and answers 403 (the landing page used to send everybody to the admin patient list).
     */
    function getDashboardRouteName(?Authenticatable $user = null): ?string
    {
        $user = $user ?: getLogInUser();
        if (! $user) {
            return null;
        }

        // Role-based dashboard routes - check most common roles first
        $routeName = null;
        if ($user->hasRole('clinic_admin')) {
            $routeName = 'admin.dashboard';
        } elseif ($user->hasRole('staff') || $user->hasRole('nurse')) {
            $routeName = 'staff.dashboard';
        } elseif ($user->hasRole('doctor')) {
            $routeName = 'doctors.dashboard';
        } elseif ($user->hasRole('patient')) {
            $routeName = 'patients.dashboard';
        }

        return ($routeName && \Illuminate\Support\Facades\Route::has($routeName)) ? $routeName : null;
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
            return url(RouteServiceProvider::HOME);
        }

        $routeName = getDashboardRouteName($user);
        if ($routeName) {
            return route($routeName);
        }

        // Fallback to permission-based check for admin users only
        if ($user->hasRole('clinic_admin')) {
            $permissions = Cache::remember("user_permissions_{$user->id}", 300, function () use ($user) {
                return $user->getAllPermissions()->pluck('name')->toArray();
            });

            $permissionDashboardMap = [
                'manage_admin_dashboard' => 'admin.dashboard',
                'manage_doctors' => 'doctors.index',
                'manage_patients' => 'patients.index',
                'manage_staff' => 'staffs.index',
            ];

            foreach ($permissionDashboardMap as $permission => $permissionRouteName) {
                if (in_array($permission, $permissions, true) && \Illuminate\Support\Facades\Route::has($permissionRouteName)) {
                    return route($permissionRouteName);
                }
            }
        }

        // A signed-in user without a dashboard (no role, retired patient role). HOME is the ADMIN dashboard, which
        // answers 403 for them - and /login and the auth redirects all use this helper, so they would be stuck there.
        // The public landing page is the one page every account can open.
        return route('medical');
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
        if (Auth::user()?->dark_mode) {
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
            $composerFile = file_get_contents(base_path('composer.json'));
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

if (! function_exists('getNotificationIcon')) {

    function getNotificationIcon($notificationFor)
    {
        switch ($notificationFor) {
            case $notificationFor == Notification::CHECKOUT:
                return 'fas fa-check-square';
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

if (! function_exists('formatPhilippinePhone')) {

    /**
     * "+63 917 123 4567" for any readable Philippine number; anything else is returned as it was stored.
     */
    function formatPhilippinePhone(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return $value;
        }

        return \App\Support\PhilippinePhone::format($value) ?? $value;
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

if (! function_exists('generateUniqueHistoryNumber')) {

    /**
     * Numeric part of a dispense history number; callers store it as 'HIS' . $code.
     *
     * The uniqueness check has to look for the value as it is STORED ('HIS123456'). It used to
     * compare the bare number, which never matched, so collisions were never detected (and the
     * 4-digit space made them likely after a few hundred records). A unique index now backs it up.
     */
    function generateUniqueHistoryNumber()
    {
        do {
            $code = random_int(100000, 999999);
        } while (\App\Models\DispenseRecord::where('history_number', 'HIS' . $code)->exists());

        return $code;
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
        if (! $user) {
            return false;
        }

        // Staff = Nurse (plan Phase 3.1): the 2026_10_09 migration moves every account of the retired `nurse` role to
        // `staff`. The old role is still matched here for a database that has not run that migration yet, so such an
        // account is sent to the staff screens instead of the admin ones. (Dead code to remove in Phase 6.)
        if ($role === 'staff') {
            return $user->hasRole('staff') || $user->hasRole('nurse');
        }

        return $user->hasRole($role);
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
        if (!$user) {
            return route($baseName, $parameters);
        }

        $routeName = $baseName;

        if ($user->hasRole('staff')) {
            $routeName = 'staff.' . $baseName;
        } elseif ($user->hasRole('nurse')) {
            $routeName = 'staff.' . $baseName;
        } elseif ($user->hasRole('doctor')) {
            $routeName = 'doctors.' . $baseName;
        } elseif ($user->hasRole('patient')) {
            $routeName = 'patients.' . $baseName;
        }

        // Check if route exists, if not fallback to baseName
        if (!\Illuminate\Support\Facades\Route::has($routeName)) {
            return route($baseName, $parameters);
        }

        return route($routeName, $parameters);
    }
}

if (! function_exists('getRouteNameByRole')) {
    /**
     * Get the route name for the current role without fallback.
     * @param string $baseName
     * @return string
     */
    function getRouteNameByRole(string $baseName): string
    {
        $user = getLogInUser();
        if (! $user) {
            return $baseName;
        }

        if ($user->hasRole('staff') || $user->hasRole('nurse')) {
            return 'staff.' . $baseName;
        }

        if ($user->hasRole('doctor')) {
            return 'doctors.' . $baseName;
        }

        if ($user->hasRole('patient')) {
            return 'patients.' . $baseName;
        }

        return $baseName;
    }
}

if (! function_exists('normalizeStaffModuleKey')) {
    /**
     * Normalize module keys to a stable internal format.
     */
    function normalizeStaffModuleKey(string $module): string
    {
        $module = strtolower(trim(str_replace('-', '_', $module)));

        $aliases = [
            'patient_queue' => 'queue',
            'consultation' => 'consultations',
            'certificate' => 'certificates',
            'lab_requests' => 'lab_requests',
            'document_issuance' => 'document_issuances',
            'medicine_inventory' => 'inventory',
            'medicine_inventory_tracking' => 'inventory',
            'medicine_dispensing' => 'dispensing',
            'dispense_records' => 'dispensing',
        ];

        return $aliases[$module] ?? $module;
    }
}

if (! function_exists('getDoctorsMenuUrl')) {
    /**
     * Where the "Doctors" menu entry leads for the signed-in user. Admin and staff have the doctors list; a doctor has no
     * list, only profile pages, so the entry opens the doctor's own profile (null when that record is missing, which hides
     * the entry instead of crashing every page).
     */
    function getDoctorsMenuUrl(): ?string
    {
        $user = getLogInUser();
        if (! $user) {
            return null;
        }

        if ($user->hasRole('doctor')) {
            $doctorId = $user->doctor?->id;

            return $doctorId ? route('doctors.doctors.detail', $doctorId) : null;
        }

        return getRouteByRole('doctors.index');
    }
}

if (! function_exists('canUsePermission')) {
    /**
     * Whether the user holds a permission by name. A permission that is not in the table (an old or hand-edited
     * database) reads as "not allowed": spatie would throw PermissionDoesNotExist, and since the menu asks on every
     * page that would be a 500 for the whole panel.
     */
    function canUsePermission(Authenticatable $user, string $permission): bool
    {
        try {
            return $user->hasPermissionTo($permission);
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist) {
            return false;
        }
    }
}

if (! function_exists('canUseModule')) {
    /** Module access follows the role grants; legacy staff profiles never restrict it. */
    function canUseModule(string|array $module, ?Authenticatable $user = null): bool
    {
        $user = $user ?: getLogInUser();
        if (! $user || ! $user->hasAnyRole(['clinic_admin', 'staff', 'doctor'])) {
            return false;
        }
        foreach ((array) $module as $key) {
            $key = normalizeStaffModuleKey($key);
            if (in_array($key, ['dashboard', 'reports', 'notifications', 'activity_logs'], true)) {
                return true;
            }
            $permission = \App\Support\ModuleAccess::PERMISSIONS[$key] ?? null;
            if ($permission && canUsePermission($user, $permission)) {
                return true;
            }
        }
        return false;
    }
}

if (! function_exists('isModuleActive')) {
    /**
     * Check if the current request belongs to a specific module
     * @param string $moduleName
     * @return bool
     */
    function isModuleActive(string $moduleName): bool
    {
        $module = request()->query('module');
        $type = request()->query('document_type');
        $tab = request()->query('tab');

        switch ($moduleName) {
            case 'dashboard':
                return Request::is('*/dashboard*');
            case 'patients':
                return Request::is('*/patients*') && $module !== 'prescription';
            case 'patient-queue':
                return Request::is('*/patient-queue*');
            case 'consultations':
                return Request::is('*/document-issuances*') && ($module === 'consultation' || (!$module && $type === 'consultation_form') || (!$module && !$type && !request()->query('status')));
            case 'prescriptions':
                return Request::is('*/prescriptions*', '*/prescription-medicine*', '*/prescription-pdf*') || (Request::is('*/patients*') && $module === 'prescription');
            case 'inventory':
                return Request::is('*/categories*', '*/generics*', '*/medicines*', '*/stock-in*', '*/medicine-inventory-tracking*');
            case 'dispensing':
                return Request::is('*/used-medicine*', '*/medicine-history*', '*/dispense-records*', '*/medicine-dispensing-management*');
            case 'lab-requests':
                return Request::is('*/lab-requests*');
            case 'certificates':
                return Request::is('*/document-issuances*') && ($module === 'certificate' || (!$module && $type === 'medical_certificate'));
            case 'reports':
                return Request::is('*/activity-logs*') && !$tab && !request()->query('status');
            case 'notifications':
                return Request::is('*/activity-logs*') && ($tab === 'inventory' || $tab === 'logs' || request()->query('status') === 'low_stock');
            case 'settings':
                return Request::is('*/settings*', '*/roles*', '*/backups*', '*/staffs*', '*/doctors*', '*/specializations*', '*/cms*', '*/countries*', '*/states*', '*/cities*', '*/barangays*');
            default:
                return Request::is("*/$moduleName*");
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
            $today = Carbon::now();
            $oneMonthFromNow = $today->copy()->addMonth();

            // Count unique medicines that still have stock in batch ledger and expire within a month.
            return MedicineBatch::where('quantity', '>', 0)
                ->whereNotNull('expiration_date')
                ->whereDate('expiration_date', '>=', $today->toDateString())
                ->whereDate('expiration_date', '<=', $oneMonthFromNow->toDateString())
                ->distinct('medicine_id')
                ->count('medicine_id');
        });
    }
}


if (! function_exists('generateUniqueLabRequestNumber')) {
    /**
     * Generate a unique 6-digit lab request number.
     * Follows the same pattern as generateUniqueAvailabilityNumber().
     *
     * @return int
     */
    function generateUniqueLabRequestNumber(): int
    {
        do {
            $code = random_int(100000, 999999);
        } while (\App\Models\LabRequest::withTrashed()->where('request_number', $code)->exists());

        return $code;
    }
}

if (! function_exists('retryOnDuplicateKey')) {
    /**
     * Run $attempt; if the database refuses it because a unique number was taken a moment earlier by someone else
     * (duplicate key), run it again so it can draw a new number. Any other error is thrown at once.
     * The numbers are drawn at random and checked before saving, which leaves a tiny race between two simultaneous
     * saves; the unique index catches it and this retries. pass-1 L-05.
     *
     * @param  callable(int): mixed  $attempt  receives the attempt number (1, 2, ...)
     */
    function retryOnDuplicateKey(callable $attempt, int $times = 5)
    {
        for ($try = 1; ; $try++) {
            try {
                return $attempt($try);
            } catch (\Illuminate\Database\QueryException $e) {
                $duplicate = (int) ($e->errorInfo[1] ?? 0) === 1062;

                if (! $duplicate || $try >= $times) {
                    throw $e;
                }
            }
        }
    }
}

if (! function_exists('activityLogModuleForTab')) {
    /**
     * The staff module that unlocks a tab of the activity-log / reports screen: the raw activity log and
     * the low-stock inventory view are "notifications"; the clinical and dispensing reports are "reports".
     */
    function activityLogModuleForTab(?string $tab): string
    {
        return in_array((string) $tab, ['logs', 'inventory'], true) ? 'notifications' : 'reports';
    }
}

if (! function_exists('canViewActivityLogTab')) {
    /**
     * Reports and notifications are available to every signed-in clinic role.
     * The raw activity log carries patient names, contact numbers, complaints and diagnoses.
     */
    function canViewActivityLogTab(?string $tab, ?Authenticatable $user = null): bool
    {
        return canUseModule(activityLogModuleForTab($tab), $user);
    }
}
