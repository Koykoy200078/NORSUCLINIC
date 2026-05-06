<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Setting;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Allow all user types including patients to log in
        $request->session()->regenerate();

        $user = Auth::user();
        $hasDefaultPassword = $user && Hash::check('123456', $user->password);
        $isClinicAdmin = $user && (int) $user->type === User::ADMIN;

        // Force clinic admin to land on dashboard if still using the seeded default password.
        if ($hasDefaultPassword && $isClinicAdmin) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(getDashboardURL());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        Session::put('languageName', 'en');

        return redirect()->route('medical');
    }
}
