<?php

namespace Tests\Feature\Regression;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * STATUS R3-L17, the owner's decision of 2026-11-14: on the shared clinic PCs a sign-in ends when the browser is closed
 * (the 120 idle minutes stay). The session cookie used to be kept for 120 minutes after the browser closed, so the next
 * person at the PC opened the clinic system as the last user. A "remember me" cookie would keep the sign-in past the
 * browser too; no sign-in page offers it, and a hand-made request can no longer get one.
 */
class SessionEndsOnBrowserCloseTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_session_cookie_is_dropped_when_the_browser_closes(): void
    {
        $cookie = $this->get('/login')->assertOk()->getCookie(config('session.cookie'), false);

        $this->assertNotNull($cookie);
        $this->assertSame(0, $cookie->getExpiresTime(), 'a session cookie with an expiry outlives the browser');
    }

    public function test_signing_in_never_sets_a_remember_me_cookie(): void
    {
        $user = User::factory()->create(['remember_token' => null]); // a "remember me" sign-in would set one

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password', 'remember' => 'on']);

        $this->assertAuthenticatedAs($user);
        $this->assertNull($response->getCookie(Auth::guard('web')->getRecallerName(), false));
        $this->assertNull($user->fresh()->remember_token);
    }
}
