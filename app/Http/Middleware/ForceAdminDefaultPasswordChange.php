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

        $usesDefaultPassword = Hash::check('123456', $user->password);

        if ($usesDefaultPassword && ! $request->routeIs('admin.dashboard') && ! $request->ajax()) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
