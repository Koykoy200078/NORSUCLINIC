<?php

namespace Tests\Feature\Regression;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Re-audit 2026-11-13 (DRY pass): "reset the password to the default" was written three times. When the save failed,
 * the patient and doctor copies sent the raw error text to the browser; the staff copy left it to the normal error
 * handling (logged, generic message). The owner chose the generic error for the one shared version.
 */
class PasswordResetErrorTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        config(['app.debug' => false]);
    }

    /** @return array<string, array{string}> */
    public static function accounts(): array
    {
        return ['patient' => ['patients.reset.password'], 'doctor' => ['doctors.reset.password'], 'staff' => ['staffs.reset.password']];
    }

    #[DataProvider('accounts')]
    public function test_a_failed_save_shows_a_generic_error_without_the_database_text(string $route): void
    {
        $account = match ($route) {
            'patients.reset.password' => $this->makePatient()->user,
            'doctors.reset.password' => $this->makeDoctor(),
            default => $this->makeNurse(),
        };
        $before = $account->password;
        User::updating(function (): void {
            throw new RuntimeException('SQLSTATE[HY000] secret table detail');
        });

        $response = $this->actingAs($this->makeAdmin())->postJson(route($route, $account->id));

        $response->assertStatus(500);
        $this->assertStringNotContainsString('secret table detail', $response->getContent());
        $this->assertSame($before, $account->fresh()->password);
    }

    #[DataProvider('accounts')]
    public function test_a_reset_still_sets_the_default_password(string $route): void
    {
        $account = match ($route) {
            'patients.reset.password' => $this->makePatient()->user,
            'doctors.reset.password' => $this->makeDoctor(),
            default => $this->makeNurse(),
        };

        $this->actingAs($this->makeAdmin())->postJson(route($route, $account->id))
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Password has been reset to default (' . User::DEFAULT_PASSWORD . ') successfully.']);

        $this->assertTrue(Hash::check(User::DEFAULT_PASSWORD, $account->fresh()->password));
    }
}
