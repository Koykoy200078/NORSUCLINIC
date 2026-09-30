<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Public self-registration is disabled: patients are created by clinic staff (patient records only,
 * nobody logs in as a patient) and the account it created had no usable password anyway.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_is_not_available(): void
    {
        $this->get('/register')->assertStatus(404);
    }

    public function test_public_registration_does_not_create_accounts(): void
    {
        $before = User::count();

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $this->assertSame($before, User::count());
    }
}
