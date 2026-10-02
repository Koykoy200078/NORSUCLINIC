<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * An account that still has the well-known default password ("123456", which is what every administrator reset and
 * every newly created account gets) can reach nothing but its own dashboard - where the "Change Password" box opens by
 * itself - until the password is changed. It used to be enforced for the clinic administrator only; doctors and
 * staff/nurse could dismiss the box and carry on with the default password. R3-M11.
 *
 * Registered as `forceAdminPasswordChange` (admin group, kept for the old name) and `forcePasswordChange`
 * (doctor and staff groups).
 */
class ForceAdminDefaultPasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // The administrator is acting AS this person (impersonation): their password is not the administrator's problem.
        if ($request->session()->has('impersonated_by')) {
            return $next($request);
        }

        $dashboardRoute = $this->dashboardRouteFor($user);

        if ($dashboardRoute === null) {
            return $next($request);
        }

        // bcrypt is deliberately slow (~200 ms at 12 rounds) and this used to run on EVERY request, including each
        // Livewire/AJAX call. Remember the answer in the session, keyed by the current password hash, so it is
        // recomputed only when the password changes. M-11.
        $cached = $request->session()->get('default_password_check');

        if (is_array($cached) && ($cached['hash'] ?? null) === $user->password) {
            $usesDefaultPassword = (bool) $cached['is_default'];
        } else {
            $usesDefaultPassword = Hash::check('123456', $user->password);
            $request->session()->put('default_password_check', [
                'hash' => $user->password,
                'is_default' => $usesDefaultPassword,
            ]);
        }

        // Background calls (the dashboard's own AJAX / Livewire / JSON requests) are not page visits: let them through.
        $isBackgroundCall = $request->ajax() || $request->expectsJson() || $request->hasHeader('X-Livewire');

        if ($usesDefaultPassword && ! $request->routeIs($dashboardRoute) && ! $isBackgroundCall) {
            return redirect()->route($dashboardRoute);
        }

        return $next($request);
    }

    /** The dashboard this account lives on, or null for an account type this rule does not cover. */
    private function dashboardRouteFor(User $user): ?string
    {
        if ((int) $user->type === User::ADMIN || $user->hasRole('clinic_admin')) {
            return 'admin.dashboard';
        }

        if ($user->hasRole('doctor')) {
            return 'doctors.dashboard';
        }

        if ($user->hasRole('staff') || $user->hasRole('nurse')) {
            return 'staff.dashboard';
        }

        return null;
    }
}
