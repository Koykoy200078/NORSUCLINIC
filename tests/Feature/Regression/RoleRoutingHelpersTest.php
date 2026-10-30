<?php

namespace Tests\Feature\Regression;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleRoutingHelpersTest extends TestCase
{
    #[DataProvider('panels')]
    public function test_names_urls_and_redirects_share_the_existing_role_route_contract(?string $role, string $prefix): void
    {
        if ($role !== null) {
            $user = new User();
            $user->setRelation('roles', new Collection([new Role(['name' => $role, 'guard_name' => 'web'])]));
            $this->actingAs($user);
        }

        $base = 'patients.show';
        $parameters = ['patient' => 42, 'tab' => 'history'];
        $name = $prefix.$base;
        $expected = route(\Illuminate\Support\Facades\Route::has($name) ? $name : $base, $parameters);

        $this->assertSame($name, getRouteNameByRole($base));
        $this->assertSame($expected, getRouteByRole($base, $parameters));
        $response = redirectByRole($base, $parameters)->with('success', 'Saved');
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($expected, $response->getTargetUrl());
        $this->assertSame('Saved', session('success'));
        // A module available only to the admin keeps the URL helper's existing fallback.
        $this->assertSame(route('staffs.index'), getRouteByRole('staffs.index'));
    }

    public static function panels(): array
    {
        return [
            'guest' => [null, ''], 'admin' => ['clinic_admin', ''], 'staff' => ['staff', 'staff.'],
            'doctor' => ['doctor', 'doctors.'], 'legacy nurse' => ['nurse', 'staff.'],
            'retired patient' => ['patient', 'patients.'],
        ];
    }

    public function test_doctor_profile_alias_keeps_the_existing_doctor_panel_url(): void
    {
        $user = new User();
        $user->setRelation('roles', new Collection([new Role(['name' => 'doctor', 'guard_name' => 'web'])]));
        $this->actingAs($user);

        $this->assertSame('doctors.doctors.detail', getRouteNameByRole('doctors.show'));
        $this->assertSame(route('doctors.doctors.detail', 42), getRouteByRole('doctors.show', [42]));
    }

    public function test_default_password_is_shared_with_validation(): void
    {
        $this->assertSame('123456', User::DEFAULT_PASSWORD);
        $rules = (new \App\Http\Requests\UpdateChangePasswordRequest())->rules();
        $validator = validator([
            'current_password' => 'Previous!Pass', 'new_password' => User::DEFAULT_PASSWORD,
            'confirm_password' => User::DEFAULT_PASSWORD,
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('new_password', $validator->errors()->toArray());
    }
}
