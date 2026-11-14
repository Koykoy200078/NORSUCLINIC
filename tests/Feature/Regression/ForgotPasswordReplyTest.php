<?php

namespace Tests\Feature\Regression;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * STATUS R3-L15, the owner's decision of 2026-11-14: the Forgot password page gives one reply for any e-mail and sends the
 * user to the clinic administrator. It used to answer "We cannot find a user with that email address" for an unknown
 * address and "Email send successfully." for a known one, which told anyone which e-mails have an account; and on the
 * clinic PC the mail only goes to the log, so the link never arrives. The link is still sent (where mail is set up it works).
 */
class ForgotPasswordReplyTest extends TestCase
{
    use RefreshDatabase;

    private const REPLY = 'If an account uses this e-mail, a password reset link has been sent to it. If no e-mail arrives, ask the clinic administrator to reset your password.';

    private function askForALink(string $email): string
    {
        return $this->from('/forgot-password')->followingRedirects()
            ->post('/forgot-password', ['email' => $email])
            ->assertOk()
            ->getContent();
    }

    public function test_an_unknown_and_a_known_email_get_the_same_reply(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $known = $this->askForALink($user->email);
        $unknown = $this->askForALink('nobody-here@example.test');

        foreach ([$known, $unknown] as $page) {
            $this->assertStringContainsString(self::REPLY, $page);
            $this->assertStringNotContainsString('We cannot find a user', $page);
            $this->assertStringNotContainsString('Email send successfully', $page);
        }
        Notification::assertSentTo($user, ResetPassword::class);
        Notification::assertSentTimes(ResetPassword::class, 1);
    }

    public function test_a_second_request_within_the_wait_gets_the_same_reply(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->askForALink($user->email);
        $again = $this->askForALink($user->email);

        // the broker answers "throttled" here, which only an existing account can get
        $this->assertStringContainsString(self::REPLY, $again);
        $this->assertStringNotContainsString('Please wait before retrying', $again);
    }

    public function test_an_address_that_is_not_an_email_is_still_refused(): void
    {
        Notification::fake();

        // the application keeps validation messages as a plain list (not keyed by field), so the message is checked
        $this->from('/forgot-password')->post('/forgot-password', ['email' => 'not-an-email'])
            ->assertRedirect('/forgot-password')
            ->assertSessionHasErrors();
        $this->assertStringContainsString('valid email address', implode(' ', session('errors')->all()));
        Notification::assertNothingSent();
    }
}
