<?php

namespace Tests\Feature\Regression;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * The owner's decision (2026-11-14): the email-verified switches were removed from the lists because no list showed them,
 * and then the endpoint they called (POST {panel}/email-verified, which set or cleared a doctor's or patient's
 * email_verified_at) too. Nothing calls it, and nothing should be able to change an account's verification by hand.
 */
class EmailVerifiedEndpointRemovedTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    public function test_no_panel_has_the_endpoint_and_a_post_changes_nothing(): void
    {
        foreach (['emailVerified', 'staff.emailVerified', 'doctors.emailVerified'] as $name) {
            $this->assertFalse(Route::has($name), $name);
        }

        $doctor = $this->makeDoctor(['email_verified_at' => now()]);
        $this->actingAs($this->makeAdmin())->postJson('/admin/email-verified', ['id' => $doctor->id, 'value' => 0])->assertNotFound();
        $this->assertNotNull($doctor->fresh()->email_verified_at);

        // the resend link of the same group stays
        $this->assertTrue(Route::has('resend.email.verification'));
    }
}
