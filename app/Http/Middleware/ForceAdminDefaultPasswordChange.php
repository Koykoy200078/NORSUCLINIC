<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

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
        $isClinicAdmin = (int) $user->type === User::ADMIN;

        if (! $isClinicAdmin) {
            return $next($request);
        }

        // bcrypt is deliberately slow (~200 ms at 12 rounds) and this used to run on EVERY admin
        // request, including each Livewire/AJAX call. Remember the answer in the session, keyed by
        // the current password hash, so it is recomputed only when the password changes. M-11.
        $cached = $request->session()->get('admin_default_password_check');

        if (is_array($cached) && ($cached['hash'] ?? null) === $user->password) {
            $usesDefaultPassword = (bool) $cached['is_default'];
        } else {
            $usesDefaultPassword = Hash::check('123456', $user->password);
            $request->session()->put('admin_default_password_check', [
                'hash' => $user->password,
                'is_default' => $usesDefaultPassword,
            ]);
        }

        if ($usesDefaultPassword && ! $request->routeIs('admin.dashboard') && ! $request->ajax()) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
