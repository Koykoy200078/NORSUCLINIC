<?php

namespace Tests\Feature\Regression;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Re-audit 2026-11-13 (DRY pass): the admin, doctor and staff dashboards each carried their own copy of the script that
 * opens Change Password by itself while the account still has the default password (the admin's read the flag from a
 * hidden input). One shared prompt now: each dashboard switches it on exactly once, and only for that account.
 */
class DefaultPasswordPromptTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    /** @return array<string, array{string, string}> */
    public static function dashboards(): array
    {
        return ['admin' => ['makeAdmin', 'admin.dashboard'], 'doctor' => ['makeDoctor', 'doctors.dashboard'], 'staff' => ['makeNurse', 'staff.dashboard']];
    }

    #[DataProvider('dashboards')]
    public function test_the_dashboard_opens_change_password_once_for_a_default_password(string $maker, string $route): void
    {
        $html = $this->actingAs($this->{$maker}(['password' => Hash::make('123456')]))->get(route($route))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'var hasDefaultPassword = true;'));
        $this->assertSame(1, substr_count($html, "document.getElementById('changePassword')"));
    }

    #[DataProvider('dashboards')]
    public function test_the_dashboard_leaves_change_password_closed_otherwise(string $maker, string $route): void
    {
        $html = $this->actingAs($this->{$maker}(['password' => Hash::make('a-chosen-password')]))->get(route($route))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'var hasDefaultPassword = false;'));
    }
}
