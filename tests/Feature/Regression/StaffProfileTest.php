<?php

namespace Tests\Feature\Regression;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * R3-M2 / R3-L2 (2026-10-01 re-audit): the "My profile" form of a staff / nurse account answered
 * "updated successfully" but saved nothing, and rejected perfectly valid e-mail domains.
 */
class StaffProfileTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function profilePayload(User $user, array $overrides = []): array
    {
        return array_merge([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'time_zone' => 'Asia/Manila',
        ], $overrides);
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function staffAccounts(): array
    {
        return [
            'nurse at triage' => ['nurse', 'triage_area'],
            'front-desk clinic staff' => ['clinic_staff', 'front_desk'],
            'pharmacist' => ['pharmacist', 'pharmacy'],
        ];
    }

    /**
     * @dataProvider staffAccounts
     */
    public function test_a_staff_member_can_change_their_own_profile(string $designation, string $station): void
    {
        $staff = $this->makeStaff($designation, $station);

        $this->actingAs($staff)->get(route('profile.setting'))->assertOk();

        $this->put(route('update.profile.setting'), $this->profilePayload($staff, [
            'first_name' => 'Marites',
            'middle_name' => 'Cruz',
            'contact' => '9171234567',
            'address1' => 'Purok 5, Bantayan',
        ]))->assertRedirect(route('profile.setting'));

        $staff->refresh();
        $this->assertSame('Marites', $staff->first_name);
        $this->assertSame('Cruz', $staff->middle_name);
        $this->assertSame('Purok 5, Bantayan', $staff->address->address1);
    }

    public function test_the_profile_form_cannot_change_type_status_or_password(): void
    {
        $staff = $this->makeStaff('nurse', 'triage_area');
        $passwordBefore = $staff->password;

        $this->actingAs($staff)->put(route('update.profile.setting'), $this->profilePayload($staff, [
            'first_name' => 'Marites',
            'type' => User::ADMIN,
            'status' => 0,
            'password' => 'hacked-password',
            'employee_id' => 'EMP-HACK',
        ]))->assertRedirect();

        $staff->refresh();
        $this->assertSame('Marites', $staff->first_name);
        $this->assertSame(User::STAFF, (int) $staff->type);
        $this->assertTrue((bool) $staff->status);
        $this->assertSame($passwordBefore, $staff->password);
        $this->assertNotSame('EMP-HACK', $staff->employee_id);
    }

    /** R3-L2 */
    public function test_email_domains_with_a_long_top_level_domain_are_accepted(): void
    {
        $staff = $this->makeStaff('nurse', 'triage_area');

        $this->actingAs($staff)->put(route('update.profile.setting'), $this->profilePayload($staff, [
            'email' => 'nurse.joy@clinic.school',
        ]))->assertRedirect(route('profile.setting'))->assertSessionDoesntHaveErrors();

        $this->assertSame('nurse.joy@clinic.school', $staff->fresh()->email);
    }

    public function test_an_account_with_no_editable_profile_branch_is_told_so_instead_of_a_false_success(): void
    {
        $user = User::create([
            'first_name' => 'Role', 'last_name' => 'Less', 'email' => 'roleless@test.local', 'password' => 'password',
            'type' => User::STAFF, 'status' => 1, 'gender' => User::MALE, 'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->put(route('update.profile.setting'), $this->profilePayload($user, ['first_name' => 'Changed']))
            ->assertStatus(422);

        $this->assertSame('Role', $user->fresh()->first_name);
    }
}
