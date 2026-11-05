<?php

namespace Tests\Feature\Upgrade;

use App\Http\Kernel;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Upgrade plan U1 (Laravel 11): what Laravel 11 touches and the application has to keep.
 *
 * - the sign-in throttle (five failures, then locked) and the `throttle:6,1` of the e-mail verification routes. Laravel 11
 *   moved the rate limiter to seconds; the string form `throttle:6,1` and `RateLimiter::hit()` must still mean "per minute"
 *   and "60 seconds";
 * - the classic `app/Http/Kernel.php` is kept (the plan keeps the Laravel 10 structure), so the deferred-callback middleware
 *   that Laravel 11's own skeleton registers has to be listed there by hand (finding C8).
 */
class Laravel11ReadinessTest extends TestCase
{
    use RefreshDatabase, BuildsClinicData;

    public function test_a_sixth_sign_in_is_refused_by_the_throttle_even_with_the_right_password(): void
    {
        $this->seedAccessControl();
        $this->makeAdmin(['email' => 'throttle.me@test.local', 'password' => Hash::make('Right#Pass1')]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            // App\Exceptions\Handler turns a validation error into one plain message (no field key) for the sign-in screen
            $this->post(route('login'), ['email' => 'throttle.me@test.local', 'password' => 'wrong'])->assertSessionHasErrors();
            $this->assertSame(trans('auth.failed'), session('errors')->first(), "attempt {$attempt} is a plain wrong password");
        }

        Event::fake([Lockout::class]);
        $this->post(route('login'), ['email' => 'throttle.me@test.local', 'password' => 'Right#Pass1'])->assertSessionHasErrors();

        $this->assertGuest();
        Event::assertDispatched(Lockout::class);
        $this->assertStringContainsString('Too many login attempts', session('errors')->first());
    }

    public function test_the_verification_mail_can_be_requested_six_times_a_minute_and_the_seventh_is_refused(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        for ($request = 1; $request <= 6; $request++) {
            $this->actingAs($user)->post(route('verification.send'))->assertRedirect();
        }

        $this->actingAs($user)->post(route('verification.send'))->assertStatus(429);
    }

    public function test_the_kernel_runs_deferred_callbacks_first(): void
    {
        $this->assertTrue(
            class_exists(InvokeDeferredCallbacks::class),
            'Laravel 11 provides the deferred-callback middleware'
        );

        $global = (function () {
            return $this->middleware;
        })->call($this->app->make(Kernel::class));

        $this->assertSame(InvokeDeferredCallbacks::class, $global[0]);
    }
}
