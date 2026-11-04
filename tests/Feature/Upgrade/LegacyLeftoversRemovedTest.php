<?php

namespace Tests\Feature\Upgrade;

use App\Livewire\SliderTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Upgrade plan U0.8: leftovers that nothing used any more and that were removed one by one, each proven unused first
 * (grep, the routes, a byte-identical production build). They must stay gone: some of them would fail, or lie, on the next
 * Laravel step. What was deliberately KEPT is pinned too, so nobody removes it for looking unused (Tailwind: the consultation
 * pages are laid out with its utility classes).
 */
class LegacyLeftoversRemovedTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    /** the source files a leftover could hide in (no vendor, no built output, no theme bundle) */
    private function files(string $directory, string $extension): array
    {
        $files = [];
        foreach (File::allFiles(base_path($directory)) as $file) {
            $path = str_replace('\\', '/', $file->getPathname());
            if (preg_match('/\.' . $extension . '$/', $path)) {
                $files[] = $path;
            }
        }

        return $files;
    }

    public function test_the_unused_token_api_route_is_gone(): void
    {
        // `auth:api` is the token guard, which reads a users.api_token column that no migration ever created: it could
        // only answer 401 (or fail on the missing column), and nothing in the application called it
        $this->getJson('/api/user')->assertNotFound();
        $this->assertNull(config('auth.guards.api'), 'the token guard has no user left');
    }

    public function test_package_providers_and_aliases_are_discovered_not_listed(): void
    {
        $config = require config_path('app.php');
        $listed = array_map('strval', $config['providers']);

        // all five register themselves through Composer package discovery (bootstrap/cache/packages.php)
        foreach ([
            \Spatie\Permission\PermissionServiceProvider::class,
            \Barryvdh\DomPDF\ServiceProvider::class,
            \Maatwebsite\Excel\ExcelServiceProvider::class,
            \Lab404\Impersonate\ImpersonateServiceProvider::class,
            \Mariuzzo\LaravelJsLocalization\LaravelJsLocalizationServiceProvider::class,
        ] as $provider) {
            $this->assertNotContains($provider, $listed, "$provider is auto-discovered: listing it again does nothing");
            $this->assertNotNull(app()->getProvider($provider), "$provider is no longer registered");
        }

        $this->assertArrayNotHasKey('Excel', $config['aliases']);
        $this->assertArrayNotHasKey('PDF', $config['aliases']);
        $this->assertTrue(class_exists('Excel'), 'the Excel alias comes from the package');
        $this->assertTrue(class_exists('PDF'), 'the PDF alias comes from the package');
    }

    public function test_the_broken_node_bootstrap_script_and_its_dead_packages_are_gone(): void
    {
        // resources/js/bootstrap.js set window._ (lodash, used nowhere) and then threw on `window.axios.defaults` because axios
        // was never installed, so resources/js/app.js stopped at its first line on the two pages that load it
        $this->assertFileDoesNotExist(resource_path('js/bootstrap.js'));
        $this->assertStringNotContainsString('bootstrap', file_get_contents(resource_path('js/app.js')));
        $this->assertFileDoesNotExist(base_path('tailwind.config.js'), 'a Tailwind 3 config that Tailwind 4 never reads');

        $package = json_decode(file_get_contents(base_path('package.json')), true);
        $declared = array_merge($package['dependencies'] ?? [], $package['devDependencies'] ?? []);
        foreach (['lodash', 'autoprefixer', 'postcss-import'] as $name) {
            $this->assertArrayNotHasKey($name, $declared, "$name is not used by the build");
        }
    }

    public function test_tailwind_stays_because_the_consultation_pages_use_its_classes(): void
    {
        // measured 2026-11-02 with css/app.css switched off: the consultation form drops from 8,507 px to 5,133 px tall and the
        // text colour changes on 280 of its elements; see the plan's progress table before removing any of this
        $package = json_decode(file_get_contents(base_path('package.json')), true);
        $this->assertArrayHasKey('tailwindcss', $package['dependencies']);
        $this->assertArrayHasKey('@tailwindcss/postcss', $package['dependencies']);
        $this->assertStringContainsString('@tailwindcss/postcss', file_get_contents(base_path('webpack.mix.js')));
        $this->assertStringContainsString('css/app.css', file_get_contents(resource_path('views/layouts/app.blade.php')));
    }

    public function test_the_list_classes_no_page_mounts_are_gone_and_the_banner_list_stays(): void
    {
        // ProvinceTable was renamed to StateTable and MedicineAvailabilityTable to StockInTable; nothing mounts the old ones
        foreach (['ProvinceTable', 'MedicineAvailabilityTable'] as $list) {
            $this->assertFileDoesNotExist(app_path("Livewire/{$list}.php"));
            $this->assertFileDoesNotExist(base_path("tests/Fixtures/tables/{$list}.json"), "{$list} left its golden master behind");
        }

        // SliderTable was listed as unreachable by mistake (U0.4c): GET /admin/banner mounts it and the menu links there
        $this->assertFileExists(app_path('Livewire/SliderTable.php'));
        $this->seedAccessControl();
        $this->actingAs($this->makeAdmin())->get('/admin/banner')->assertOk()->assertSeeLivewire(SliderTable::class);
    }

    public function test_the_form_views_no_page_draws_are_gone(): void
    {
        // no route, controller or include outside this group names them (found by the form comparison of U0.3)
        foreach ([
            'categories.create', 'categories.edit', 'categories.fields', 'fronts.sliders.create', 'fronts.sliders.fields',
            'generics.create_modal', 'medicine-history.index', 'prescriptions.add_new_medicine', 'prescriptions.edit-other-fields',
            'prescriptions.edit-physical-info-fields', 'prescriptions.edit_fields', 'prescriptions.fields', 'prescriptions.filter-button',
            'prescriptions.medical-table', 'prescriptions.other-fields', 'prescriptions.physical-info-fields',
        ] as $view) {
            $this->assertFalse(view()->exists($view), "the view {$view} no page draws is still there");
        }
    }

    public function test_the_doctor_script_does_not_read_the_social_fields_that_were_removed(): void
    {
        // the doctor form has no Twitter / LinkedIn / Instagram field any more: reading them threw a TypeError on every save
        $script = file_get_contents(resource_path('assets/js/doctors/create-edit.js'));

        foreach (['twitterUrl', 'linkedinUrl', 'instagramUrl', 'valid_twitter', 'valid_linkedin', 'valid_instagram'] as $name) {
            $this->assertStringNotContainsString($name, $script);
        }
    }

    public function test_no_turbo_leftovers_remain(): void
    {
        // Turbo was never part of the bundle (U0.6): its events never fire and its data attributes are read by nobody
        $root = str_replace('\\', '/', base_path()) . '/';
        $found = [];
        foreach (array_merge($this->files('resources/views', 'php'), $this->files('resources/assets/js', 'js')) as $path) {
            if (preg_match('/turbo/i', file_get_contents($path), $match)) {
                $found[] = str_replace($root, '', $path);
            }
        }

        $this->assertSame([], $found, 'Turbo events or attributes are still written in these files');
    }
}
