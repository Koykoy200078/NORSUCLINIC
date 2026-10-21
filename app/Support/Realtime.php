<?php

namespace App\Support;

use App\Events\ModuleChanged;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

/**
 * Real-time updates (plan Phase 7): when a module's data is written, the screens that show it are told to ask again.
 *
 * HOW. Every INSERT / UPDATE / DELETE that reaches the database is looked at (one listener on the connection, so a save
 * through Eloquent, the query builder or a raw statement all count, and no controller can forget it). The table decides
 * the module(s) (TABLES). The notice (App\Events\ModuleChanged: the module name and the time, nothing else) goes out
 * AFTER the surrounding transaction commits, so a screen never reads a half-saved record, and never for a rolled-back
 * one.
 *
 * A STOPPED REVERB NEVER BREAKS A SAVE. Publishing is wrapped in a catch; the first failure marks real-time as "down"
 * for DOWN_FOR seconds (the whole clinic server shares that mark through the cache), during which nothing is even
 * tried, so a saved record is never delayed by an unreachable server more than once. The browsers show "Live updates
 * off" and refresh every 60 seconds while they are disconnected.
 */
final class Realtime
{
    /** How long a failed publish stops further attempts (seconds). */
    public const DOWN_FOR = 30;

    private const DOWN_KEY = 'realtime.down';

    /** The modules that have a channel: the browser listens to the ones the signed-in user may use. */
    public const MODULES = [
        'patients', 'queue', 'consultations', 'certificates', 'prescriptions', 'lab_requests',
        'inventory', 'dispensing', 'doctors', 'specializations', 'settings', 'notifications',
    ];

    /** table => the modules whose screens show it. Tables that are not listed (sessions, cache, jobs ...) change nothing. */
    public const TABLES = [
        'patient_queues' => ['queue'],
        'patients' => ['patients'], 'addresses' => ['patients'], 'patient_types' => ['patients', 'settings'],
        // an account is a patient, a doctor or a staff member
        'users' => ['patients', 'doctors', 'settings'],
        'doctors' => ['doctors'], 'doctor_specialization' => ['doctors', 'specializations'], 'qualifications' => ['doctors'],
        'specializations' => ['specializations', 'doctors'],
        'document_issuances' => ['consultations', 'certificates'],
        'consultation_illnesses' => ['consultations'], 'consultation_medicines' => ['consultations'], 'consultation_services' => ['consultations'],
        'prescriptions' => ['prescriptions', 'dispensing'], 'prescriptions_medicines' => ['prescriptions', 'dispensing'],
        'lab_requests' => ['lab_requests'], 'lab_request_items' => ['lab_requests'],
        'medicines' => ['inventory'], 'medicine_batches' => ['inventory'], 'medicine_availabilities' => ['inventory'],
        'purchased_medicines' => ['inventory'], 'categories' => ['inventory'], 'generics' => ['inventory'],
        'medicine_transactions' => ['inventory', 'dispensing'],
        'medicine_bills' => ['dispensing'], 'sale_medicines' => ['dispensing'], 'used_medicines' => ['dispensing'],
        'activity_logs' => ['notifications'],
        'notifications' => ['notifications'],
        'settings' => ['settings'], 'roles' => ['settings'], 'permissions' => ['settings'], 'role_has_permissions' => ['settings'],
        'model_has_roles' => ['settings'], 'model_has_permissions' => ['settings'], 'staff_profiles' => ['settings'],
        'countries' => ['settings'], 'states' => ['settings'], 'cities' => ['settings'], 'barangays' => ['settings'],
        'campuses' => ['settings'], 'colleges' => ['settings'], 'courses' => ['settings'], 'year_levels' => ['settings'],
        'departments' => ['settings'], 'offices' => ['settings'], 'illness_systems' => ['settings'], 'illnesses' => ['settings'],
        'service_types' => ['settings'], 'vaccinations' => ['settings'], 'diagnoses' => ['settings'], 'lab_tests' => ['settings'],
        'sliders' => ['settings'], 'insurance_providers' => ['settings'], 'clinic_stations' => ['settings'], 'staff_designations' => ['settings'],
    ];

    /**
     * The first path segment after the panel prefix (admin / staff / doctors) => the modules whose changes the page shows.
     * The browser refreshes that page's tables and regions only for these; a page that is not listed refreshes for none.
     * The dashboards and the reports (activity-logs) show figures from many modules.
     */
    public const PAGES = [
        'notifications' => ['notifications'],
        'stock-control' => ['inventory'],
        'patient-queue' => ['queue'],
        'patients' => ['patients'], 'patients-detail' => ['patients', 'consultations', 'prescriptions', 'lab_requests', 'queue'],
        'document-issuances' => ['consultations', 'certificates', 'patients'],
        'prescriptions' => ['prescriptions', 'dispensing', 'inventory'],
        'lab-requests' => ['lab_requests'],
        'medicines' => ['inventory'], 'medicine-inventory-tracking' => ['inventory'], 'categories' => ['inventory'], 'generics' => ['inventory'],
        'stock-in' => ['inventory'], 'medicine-history' => ['inventory', 'dispensing'], 'used-medicine' => ['inventory', 'dispensing'],
        'medicine-dispensing-management' => ['dispensing', 'prescriptions', 'inventory'], 'dispense-records' => ['dispensing', 'inventory'],
        'doctors' => ['doctors'], 'specializations' => ['specializations', 'doctors'],
        'dashboard' => ['queue', 'patients', 'consultations', 'certificates', 'prescriptions', 'lab_requests', 'inventory', 'dispensing', 'doctors'],
        'dashboard-patients' => ['patients', 'queue', 'consultations'],
        'activity-logs' => ['notifications', 'consultations', 'certificates', 'prescriptions', 'lab_requests', 'inventory', 'dispensing', 'patients'],
        'logs' => ['notifications'],
        'settings' => ['settings'], 'roles' => ['settings'], 'staffs' => ['settings'], 'cms' => ['settings'], 'banner' => ['settings'],
        'countries' => ['settings'], 'states' => ['settings'], 'cities' => ['settings'], 'barangays' => ['settings'], 'backups' => ['settings'],
    ];

    /** @var array<string, bool> modules that already have a notice waiting for the end of the current transaction */
    private static array $scheduled = [];

    /** Start looking at the writes. Called once when the application boots. */
    public static function listen(): void
    {
        // once per application instance (a static flag would survive into the next application a test boots)
        if (app()->bound('realtime.listening')) {
            return;
        }
        app()->instance('realtime.listening', true);
        self::$scheduled = [];

        DB::listen(function (QueryExecuted $query) {
            $table = self::writtenTable($query->sql);
            if ($table === null || ! isset(self::TABLES[$table])) {
                return;
            }

            foreach (self::TABLES[$table] as $module) {
                self::changed($module);
            }
        });

        // A rolled-back transaction throws its waiting notices away (the commit callbacks never run), so nothing may
        // stay marked as "already scheduled".
        Event::listen(TransactionRolledBack::class, function () {
            self::$scheduled = [];
        });
    }

    /** The table an INSERT / UPDATE / DELETE / REPLACE writes to, or null for anything else. */
    public static function writtenTable(string $sql): ?string
    {
        // reads (the vast majority) are decided by their first letter
        $first = $sql[0] ?? '';
        if (! in_array($first, ['i', 'I', 'u', 'U', 'd', 'D', 'r', 'R', ' ', "\n", "\t"], true)) {
            return null;
        }

        if (preg_match('/^\s*(?:insert\s+(?:ignore\s+)?into|update(?:\s+ignore)?|delete\s+from|replace\s+into)\s+`?([A-Za-z0-9_]+)`?/i', $sql, $match) !== 1) {
            return null;
        }

        return strtolower($match[1]);
    }

    /** The module changed: tell its listeners once this transaction is committed. */
    public static function changed(string $module): void
    {
        if (isset(self::$scheduled[$module])) {
            return;
        }
        self::$scheduled[$module] = true;

        DB::afterCommit(function () use ($module) {
            unset(self::$scheduled[$module]);
            if (in_array($module, ['inventory', 'queue', 'consultations'], true)) {
                $refresh = function () use ($module) {
                    try {
                        app(\App\Services\ClinicNotificationService::class)->refresh($module);
                    } catch (\Throwable $e) {
                        Log::warning('Clinic alert refresh deferred to scheduler.', ['error' => class_basename($e)]);
                    }
                };
                // QueryExecuted precedes insertGetId. Never issue alert INSERTs from that listener: even an
                // autocommit save must finish reading its generated ID first. The commit callback registers
                // this work for HTTP termination; rolled-back transactions never register it.
                $key = 'clinic.alerts.pending.'.$module;
                if (! app()->bound($key)) {
                    app()->instance($key, true);
                    app()->terminating(function () use ($key, $refresh) {
                        if (! app()->bound($key)) {
                            return;
                        }
                        app()->forgetInstance($key);
                        $refresh();
                    });
                }
            }
            self::publish($module);
        });
    }

    /** Send the notice. Never throws: a Reverb that is stopped must not turn a saved record into an error. */
    public static function publish(string $module): void
    {
        if (self::isDown()) {
            return;
        }

        try {
            event(new ModuleChanged($module));
            if ($module === 'queue') {
                app(\App\Services\QueueBoardService::class)->publish();
            }
        } catch (\Throwable $e) {
            Cache::put(self::DOWN_KEY, now()->getTimestamp(), self::DOWN_FOR);
            Log::warning('Real-time updates are unavailable (Reverb does not answer); saves are not affected.', [
                'module' => $module,
                'error' => class_basename($e),
            ]);
        }
    }

    public static function isDown(): bool
    {
        return Cache::has(self::DOWN_KEY);
    }

    /** The modules shown by the page at this path (the browser refreshes the page when one of them changes). */
    public static function pageModules(?string $path = null, $user = null): array
    {
        $segments = array_values(array_filter(explode('/', trim($path ?? request()->path(), '/'))));
        if (($segments[0] ?? null) !== null && in_array($segments[0], ['admin', 'staff', 'doctors'], true)) {
            array_shift($segments);
        }

        $modules = self::PAGES[$segments[0] ?? ''] ?? [];

        return array_values(array_intersect($modules, self::modulesFor($user ?? auth()->user())));
    }

    /** The modules the user may listen to (the channel rule of routes/channels.php, asked for each module). */
    public static function modulesFor($user): array
    {
        return array_values(array_filter(self::MODULES, fn (string $module) => canUseModule($module, $user)));
    }
}
