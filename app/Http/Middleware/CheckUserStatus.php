<?php

namespace App\Http\Middleware;

use Closure;
use Laracasts\Flash\Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CheckUserStatus
 */
class CheckUserStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Enforce status/verification BEFORE running the controller, otherwise a disabled
        // or unverified user's write (stock deduction, record changes) executes before they
        // are logged out. Also null-safe: only dereference the user when authenticated. AUTH-3.
        if (Auth::check()) {
            $user = getLogInUser();

            if (getSettingValue('email_verified') && ! $user->email_verified_at) {
                Auth::logout();
                Flash::error('Please verify your email.');

                return redirect()->route('login');
            }

            if (! $user->status) {
                Auth::logout();
                Flash::error('Your Account is currently disabled, please contact to administrator.');

                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}
