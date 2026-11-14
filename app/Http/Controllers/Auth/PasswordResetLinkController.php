<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laracasts\Flash\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request. The link is sent when the address has an account, but the reply
     * is the same for every address (the owner's decision, 2026-11-14, STATUS R3-L15): it used to say "We cannot find a
     * user with that email address" or "Please wait before retrying", which told anyone which e-mails have an account.
     * On the clinic PC the mail goes to the log, so the reply also points to the clinic administrator.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        Password::sendResetLink($request->only('email'));

        $reply = __('messages.flash.password_reset_requested');
        Flash::success($reply);

        return back()->with('status', $reply);
    }
}
