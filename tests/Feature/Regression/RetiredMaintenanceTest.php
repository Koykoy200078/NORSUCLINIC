<?php

namespace Tests\Feature\Regression;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

class RetiredMaintenanceTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function assertRetired(string $method, string $uri): void
    {
        // Stop before requesting a legacy handler: it may write accounts or generated files.
        $this->assertFalse(collect(Route::getRoutes())->contains(fn ($route) => $route->uri() === $uri));
        foreach ([$this->makeAdmin(), $this->makeNurse(), $this->makeDoctor()] as $actor) {
            $this->actingAs($actor)->json($method, '/'.$uri)->assertNotFound();
        }
    }

    public function test_translation_generation_is_not_a_browser_endpoint(): void
    {
        $this->assertRetired('GET', 'lang-js');
    }

    public function test_the_unused_bulk_patient_archive_is_not_a_browser_endpoint(): void
    {
        $this->assertRetired('POST', 'delete-old-patients');
    }

    public function test_retired_diagnostics_and_permission_repair_are_not_operator_commands(): void
    {
        $commands = Artisan::all();
        $this->assertArrayNotHasKey('fix:dashboard-permissions', $commands);
        $this->assertArrayNotHasKey('performance:monitor', $commands);
        $this->assertArrayHasKey('cache:warmup', $commands);
        $this->assertArrayHasKey('lang:js', $commands);
    }
}
