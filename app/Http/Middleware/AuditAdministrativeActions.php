<?php

namespace App\Http\Middleware;

use App\Support\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

/**
 * Leaves a line in the activity trail for every administrative CHANGE a signed-in person makes: user accounts, roles
 * and permissions, settings, backups, password resets and changes, master data (medicines, categories, locations,
 * specialisations), manual dispense records. (Patient, consultation, certificate, lab request, prescription and stock-in
 * changes are logged with their full details where they happen.) R3-M5.
 *
 * Only the NAMES of the submitted fields are kept - never their values - so a password or a medical note can not
 * end up in the trail. Failed attempts (validation errors, refused requests) are not logged as changes.
 * The line is written after the response has been sent, so it never slows a request down.
 */
class AuditAdministrativeActions
{
    /** path (without the admin/ doctors/ staff/ prefix) => [action code, what it is] */
    private const AREAS = [
        // restoring a deleted record writes its own, fuller line
        '#^settings/deleted-records/#' => ['__skip', ''],
        '#^settings(/|$)#' => ['settings_changed', 'settings'],
        '#^roles(/|$)#' => ['roles_changed', 'role or permissions'],
        '#(^|/)reset-password$#' => ['password_reset', 'password reset'],
        '#^(doctors|staffs)(/|$)|^doctor-status$|^add-qualification$|^email-verified$|^email/verification-notification#' => ['account_changed', 'user account'],
        '#^backups(/|$)#' => ['backup_changed', 'backup'],
        '#^delete-old-patients$#' => ['patients_purged', 'old patient records'],
        '#^dispense-records(/|$)#' => ['dispense_record_changed', 'manual dispense record'],
        '#^(medicines|categories|generics|specializations|countries|states|cities|barangays|banner|cms)(/|$)#' => ['master_data_changed', 'master data'],
    ];

    /** the person's own account */
    private const OWN_ACCOUNT = [
        '#^change-user-password$#' => ['password_changed', 'own password'],
        '#^profile/update$#' => ['profile_changed', 'own profile'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $path = $this->pathWithoutRolePrefix($request->path());
        $match = $this->match($path);

        if ($match === null || ! $request->user()) {
            return;
        }

        // Refused (4xx/5xx) or a form sent back with validation errors: nothing changed.
        if ($response->getStatusCode() >= 400) {
            return;
        }
        if ($response->isRedirection() && $request->hasSession() && $request->session()->get('errors')?->any()) {
            return;
        }

        [$action, $what] = $match;

        $verb = match ($request->method()) {
            'DELETE' => 'Deleted',
            'PUT', 'PATCH' => 'Changed',
            default => $action === 'password_reset' ? 'Reset' : 'Created / changed',
        };

        $subjects = collect($request->route()?->parameters() ?? [])
            ->map(fn ($value) => is_object($value) && method_exists($value, 'getKey') ? $value->getKey() : $value)
            ->filter(fn ($value) => is_scalar($value))
            ->all();

        try {
            AuditLog::record($action, trim("{$verb} {$what}" . ($subjects ? ' #' . implode('/', $subjects) : '')), [
                'subject_type' => $request->route()?->getName(),
                'subject_id' => is_numeric(Arr::first($subjects)) ? (int) Arr::first($subjects) : null,
                'properties' => [
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'route' => $request->route()?->getName(),
                    'status' => $response->getStatusCode(),
                    // names only, never values
                    'fields' => array_values(array_diff(array_keys($request->except(['_token', '_method'])), [])),
                ],
            ]);
        } catch (\Throwable $e) {
            // The trail must never break the request that has already been answered.
            report($e);
        }
    }

    private function pathWithoutRolePrefix(string $path): string
    {
        return preg_replace('#^(admin|doctors|staff)/#', '', $path) ?? $path;
    }

    /** @return array{0: string, 1: string}|null */
    private function match(string $path): ?array
    {
        foreach (self::AREAS + self::OWN_ACCOUNT as $pattern => $area) {
            if (preg_match($pattern, $path)) {
                return $area[0] === '__skip' ? null : $area;
            }
        }

        return null;
    }
}
