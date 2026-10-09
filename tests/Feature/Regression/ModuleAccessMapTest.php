<?php

namespace Tests\Feature\Regression;

use App\Models\Permission;
use App\Support\ModuleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 3.4 / 3.5 / 3.6: the permissions in Manage User roles must be exactly the ones the routes check.
 *
 * "Manage User roles does not apply" came from permissions that no staff or doctor route used (8 for staff, 9 for
 * doctors), routes that checked nothing, and a hidden designation / station layer on top. These static tests read the
 * route table (no request is made) so a later route cannot quietly bring any of that back.
 */
class ModuleAccessMapTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    /** Open to every signed-in staff / doctor on purpose: the dashboards and Reports / Notifications & Alerts. */
    private const OPEN_ROUTES = [
        'staff.dashboard', 'doctors.dashboard',
        'staff.activity-logs.index', 'staff.activity-logs.export', 'staff.activity-logs.accomplishment', 'staff.activity-logs.show',
        'doctors.activity-logs.index', 'doctors.activity-logs.export', 'doctors.activity-logs.accomplishment', 'doctors.activity-logs.show',
    ];

    /** @return array<string, array<int, string>> route name => permission middleware arguments ("a|b" kept as one entry) */
    private function panelRoutes(string $panel): array
    {
        $routes = [];
        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            if ($uri !== $panel && ! str_starts_with($uri, $panel . '/')) {
                continue;
            }

            $permissions = [];
            foreach ($route->gatherMiddleware() as $middleware) {
                if (is_string($middleware) && str_starts_with($middleware, 'permission:')) {
                    $permissions[] = substr($middleware, strlen('permission:'));
                }
            }
            $routes[$route->getName() ?? $route->methods()[0] . ' ' . $uri] = $permissions;
        }

        return $routes;
    }

    public function test_every_staff_and_doctor_route_is_guarded_by_a_permission_or_is_deliberately_open(): void
    {
        $unguarded = [];
        foreach (['staff', 'doctors'] as $panel) {
            $routes = $this->panelRoutes($panel);
            $this->assertNotEmpty($routes, "no {$panel} routes found, the check would prove nothing");

            foreach ($routes as $name => $permissions) {
                if ($permissions === [] && ! in_array($name, self::OPEN_ROUTES, true)) {
                    $unguarded[] = $name;
                }
            }
        }

        $this->assertSame([], $unguarded, "Routes that no role permission controls (add permission:<name> or list them as open on purpose):\n" . implode("\n", $unguarded));
    }

    public function test_the_roles_screen_offers_exactly_the_permissions_the_panels_check(): void
    {
        foreach (['staff', 'doctors'] as $panel) {
            $used = [];
            foreach ($this->panelRoutes($panel) as $permissions) {
                foreach ($permissions as $argument) {
                    array_push($used, ...explode('|', $argument));
                }
            }
            $used = array_values(array_unique($used));
            sort($used);

            $offered = ModuleAccess::CLINICAL_PERMISSIONS;
            sort($offered);

            // Nothing offered is dead (a checkbox that changes nothing) and nothing checked is missing from the screen.
            $this->assertSame($offered, $used, "permissions checked by the {$panel} routes vs. offered by the Roles screen");
        }
    }

    public function test_every_module_permission_exists_so_the_helper_never_throws(): void
    {
        $this->seedAccessControl();

        $wanted = array_values(array_unique(array_values(ModuleAccess::PERMISSIONS)));
        sort($wanted);
        $existing = Permission::whereIn('name', $wanted)->pluck('name')->all();
        sort($existing);

        $this->assertSame($wanted, $existing, 'canUseModule() asks for these by name; a missing one would throw PermissionDoesNotExist');
    }

    public function test_the_module_map_agrees_with_the_permissions_of_the_matching_routes(): void
    {
        // The pages each module opens, per panel: whoever may use the module may open them, nobody else.
        $examples = [
            'patients' => 'patients.index', 'queue' => 'patient-queue.index',
            'consultations' => 'document-issuances.index', 'prescriptions' => 'prescriptions.index', 'lab_requests' => 'lab-requests.index',
            'inventory' => 'medicines.index', 'dispensing' => 'medicine-dispensing.index',
            'specializations' => 'specializations.index',
        ];

        foreach (['staff' => 'staff.', 'doctors' => 'doctors.'] as $panel => $prefix) {
            $routes = $this->panelRoutes($panel);
            foreach ($examples as $module => $routeName) {
                $this->assertArrayHasKey($prefix . $routeName, $routes, "{$panel}: {$routeName} does not exist");
                $this->assertContains(
                    ModuleAccess::PERMISSIONS[$module],
                    $routes[$prefix . $routeName],
                    "{$panel}: module '{$module}' maps to " . ModuleAccess::PERMISSIONS[$module] . ' but ' . $routeName . ' is guarded by ' . json_encode($routes[$prefix . $routeName])
                );
            }
        }
    }
}
