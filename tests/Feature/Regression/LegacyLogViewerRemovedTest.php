<?php

namespace Tests\Feature\Regression;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Found by the Phase 9 live check (2026-10-22): the legacy `/admin/logs` page (the rap2hpoutre log viewer, linked from
 * nowhere) loaded seven resources from public CDNs (Bootstrap 4, DataTables, jQuery, Font Awesome), so on the clinic's
 * offline LAN it rendered broken; the in-process link crawl never reached it. The clinic administrator reads the logs
 * through the self-contained Log Viewer at `/log-viewer`, so the legacy page is gone.
 */
class LegacyLogViewerRemovedTest extends TestCase
{
    use RefreshDatabase, BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    public function test_the_legacy_admin_logs_page_no_longer_exists(): void
    {
        $uris = array_map(fn ($route) => $route->uri(), Route::getRoutes()->getRoutes());
        $this->assertNotContains('admin/logs', $uris, 'no route serves the legacy log viewer');
        $this->assertFileDoesNotExist(app_path('Http/Controllers/AdminLogViewerController.php'), 'and its controller is gone');

        $this->actingAs($this->makeAdmin())->get('/admin/logs')->assertNotFound();
    }

    public function test_the_log_viewer_still_works_for_the_admin_and_loads_nothing_from_another_host(): void
    {
        $admin = $this->makeAdmin();

        $page = $this->actingAs($admin)->get('/log-viewer')->assertOk();
        $this->actingAs($admin)->getJson('/log-viewer/api/files')->assertOk();

        $this->assertDoesNotMatchRegularExpression('#(?:src|href)=["\']https?://#i', $page->getContent(), 'the page needs no other host');
    }
}
