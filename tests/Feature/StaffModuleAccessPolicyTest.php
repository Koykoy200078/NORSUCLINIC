<?php

namespace Tests\Feature;

use App\Models\ClinicStation;
use App\Models\StaffDesignation;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffModuleAccessPolicyTest extends TestCase
{
    public function test_designation_station_policy_map_is_enforced_for_operational_modules(): void
    {
        $pharmacistAtTriage = $this->makeStaffUser('pharmacist', 'triage_area');

        $this->assertFalse(canStaffAccessModule('inventory', $pharmacistAtTriage));
        $this->assertFalse(canStaffAccessModule('dispensing', $pharmacistAtTriage));
        $this->assertFalse(canStaffAccessModule('prescriptions', $pharmacistAtTriage));
        $this->assertTrue(canStaffAccessModule('reports', $pharmacistAtTriage));
        $this->assertTrue(canStaffAccessModule('notifications', $pharmacistAtTriage));

        $this->assertFalse(canStaffDesignationWorkAtStation('pharmacist', 'triage_area'));
        $this->assertTrue(canStaffDesignationWorkAtStation('pharmacist', 'pharmacy'));
    }

    public function test_clinic_head_can_access_station_scoped_modules_anywhere(): void
    {
        $clinicHeadAtFrontDesk = $this->makeStaffUser('clinic_head', 'front_desk');

        $this->assertTrue(canStaffAccessModule('inventory', $clinicHeadAtFrontDesk));
        $this->assertTrue(canStaffAccessModule('dispensing', $clinicHeadAtFrontDesk));
        $this->assertTrue(canStaffAccessModule('prescriptions', $clinicHeadAtFrontDesk));
    }

    public function test_key_staff_submodule_routes_have_expected_module_guards(): void
    {
        $expectedGuards = [
            'staff.document-issuances.search-users' => 'staff.module:consultations,certificates',
            'staff.prescriptions.index' => 'staff.module:prescriptions',
            'staff.medicines.index' => 'staff.module:inventory',
            'staff.dispense-records.index' => 'staff.module:dispensing',
            'staff.patient-queue.index' => 'staff.module:queue',
        ];

        foreach ($expectedGuards as $routeName => $expectedMiddleware) {
            $this->assertTrue(Route::has($routeName), "Route [{$routeName}] should exist.");

            $route = Route::getRoutes()->getByName($routeName);
            $this->assertNotNull($route, "Route [{$routeName}] should be retrievable.");

            $middlewares = $route->gatherMiddleware();
            $this->assertContains(
                $expectedMiddleware,
                $middlewares,
                "Route [{$routeName}] should contain middleware [{$expectedMiddleware}]."
            );
        }
    }

    public function test_all_staff_routes_are_guarded_by_staff_module_middleware(): void
    {
        foreach (Route::getRoutes()->getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'staff/')) {
                continue;
            }

            $middlewares = $route->gatherMiddleware();
            $isStaffOrNurseRoute = collect($middlewares)->contains(function (string $middleware): bool {
                return str_contains($middleware, 'role:staff|nurse') || str_contains($middleware, 'RoleMiddleware:staff|nurse');
            });

            if (! $isStaffOrNurseRoute) {
                continue;
            }

            $hasModuleGuard = collect($middlewares)->contains(function (string $middleware): bool {
                return str_contains($middleware, 'staff.module:') || str_contains($middleware, 'EnsureStaffModuleAccess');
            });

            $this->assertTrue(
                $hasModuleGuard,
                'Missing staff.module middleware on route [' . implode('|', $route->methods()) . ' ' . $route->uri() . ']'
            );
        }
    }

    public function test_admin_only_staff_namespace_routes_are_removed(): void
    {
        $this->assertFalse(Route::has('staff.setting.index'));
        $this->assertFalse(Route::has('staff.states-list'));
        $this->assertFalse(Route::has('staff.cities-list'));
        $this->assertFalse(Route::has('staff.roles.index'));
        $this->assertFalse(Route::has('staff.countries.index'));
        $this->assertFalse(Route::has('staff.cms.index'));
        $this->assertFalse(Route::has('staff.banner.index'));
    }

    private function makeStaffUser(string $designationCode, string $stationCode): User
    {
        $user = new User();
        $user->id = random_int(1000, 999999);

        $role = new Role();
        $role->name = 'staff';
        $role->guard_name = 'web';
        $user->setRelation('roles', collect([$role]));

        $designation = new StaffDesignation();
        $designation->code = $designationCode;
        $designation->name = ucfirst(str_replace('_', ' ', $designationCode));

        $station = new ClinicStation();
        $station->code = $stationCode;
        $station->name = ucfirst(str_replace('_', ' ', $stationCode));

        $profile = new StaffProfile();
        $profile->role_designation_id = 1;
        $profile->assigned_station_id = 1;
        $profile->setRelation('roleDesignation', $designation);
        $profile->setRelation('assignedStation', $station);

        $user->setRelation('staffProfile', $profile);

        return $user;
    }
}
