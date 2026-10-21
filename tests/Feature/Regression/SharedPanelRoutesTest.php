<?php

namespace Tests\Feature\Regression;

use App\Support\PanelRoutes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SharedPanelRoutesTest extends TestCase
{
    // Captured before Phase 6.1 from af66ff0; Phases 8.2-8.4 add their approved merge and stock-control routes.
    public function test_panel_route_contract_is_unchanged_including_registration_order(): void
    {
        $expected = json_decode(file_get_contents(__DIR__.'/panel-routes.json'), true, 512, JSON_THROW_ON_ERROR);
        $actual = [];
        foreach (Route::getRoutes() as $route) {
            if (! preg_match('~^(admin|staff|doctors)(/|$)~', $route->uri())) {
                continue;
            }

            $actual[] = [
                'methods' => $route->methods(), 'uri' => $route->uri(), 'name' => $route->getName(),
                'action' => $route->getActionName(), 'middleware' => $route->gatherMiddleware(),
                'wheres' => $route->wheres, 'defaults' => $route->defaults,
                'domain' => $route->getDomain(), 'fallback' => $route->isFallback,
            ];
        }

        $this->assertCount(404, $expected);
        $this->assertSame($expected, $actual);
    }

    public function test_patient_definition_can_be_reused_in_a_group_without_expanding_doctor_access(): void
    {
        Route::prefix('probe')->name('probe.')->middleware('auth')->group(function () {
            PanelRoutes::patients('doctor');
        });
        Route::getRoutes()->refreshNameLookups();

        $update = Route::getRoutes()->getByName('probe.patients.update');
        $this->assertNotNull($update);
        $this->assertSame('probe/patients/{patient}', $update->uri());
        $this->assertSame(['PUT', 'PATCH'], $update->methods());
        $this->assertSame(['auth', 'permission:manage_patients'], $update->gatherMiddleware());
        $this->assertSame('probe.patients.index', Route::getRoutes()->match(Request::create('/probe/patients'))->getName());
        foreach (['create', 'store', 'destroy', 'restore', 'reset.password'] as $action) {
            $this->assertFalse(Route::has('probe.patients.'.$action));
        }
    }

    public function test_stock_out_redirects_keep_their_own_panel_and_tab(): void
    {
        $this->withoutMiddleware();
        foreach (['admin' => '', 'staff' => 'staff.', 'doctors' => 'doctors.'] as $panel => $prefix) {
            $this->get('/'.$panel.'/used-medicine')
                ->assertRedirect(route($prefix.'medicine-dispensing.index', ['tab' => 'stock-out']));
        }
    }

    public function test_lookup_routes_still_match_before_resource_identifiers(): void
    {
        foreach (['admin' => '', 'staff' => 'staff.', 'doctors' => 'doctors.'] as $panel => $prefix) {
            foreach (['document-issuances/search-users', 'document-issuances/get-last-consultation', 'lab-requests/search-users', 'patient-queue/refresh'] as $path) {
                $route = Route::getRoutes()->match(Request::create('/'.$panel.'/'.$path));
                $this->assertSame($prefix.str_replace('/', '.', $path), $route->getName());
            }
        }
    }
}
