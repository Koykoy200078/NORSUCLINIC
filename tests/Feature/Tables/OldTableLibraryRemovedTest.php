<?php

namespace Tests\Feature\Tables;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Upgrade plan U0.4d: the rappasoft table package is gone and must stay gone. The list screens are the project's own
 * (App\Livewire\Tables\TableComponent); a new import of the old package, or a page that asks for its files, would fail the
 * next Laravel step, which the package does not support.
 */
class OldTableLibraryRemovedTest extends TestCase
{
    /** @return array<int, string> the files whose code or markup could still name the old package (no vendor, no built output) */
    private function sourceFiles(): array
    {
        $files = [];
        foreach (['app', 'config', 'database', 'resources', 'routes', 'tests'] as $directory) {
            foreach (File::allFiles(base_path($directory)) as $file) {
                $path = str_replace('\\', '/', $file->getPathname());
                if (preg_match('/\.(php|js|json|scss|css)$/', $path) && ! str_contains($path, '/tests/Fixtures/') && ! str_contains($path, 'OldTableLibraryRemovedTest')) {
                    $files[] = $path;
                }
            }
        }
        foreach (['build-assets.mjs', 'package.json', 'composer.json'] as $file) {
            $files[] = str_replace('\\', '/', base_path($file));
        }

        return $files;
    }

    public function test_nothing_imports_or_loads_the_old_table_package(): void
    {
        // (a comment may still say which package the in-house base replaced: that is history, so the package's name as written
        // in composer.json is checked by the next test, and here only what code, views and the build can actually use)
        $root = str_replace('\\', '/', base_path()) . '/';
        $found = [];
        foreach ($this->sourceFiles() as $path) {
            $text = file_get_contents($path);
            foreach (['Rappasoft\\', 'vendor/rappasoft', 'livewire-tables::', 'vendor/laravel-livewire-tables', 'LivewireTableComponent', 'DataTableComponent'] as $needle) {
                if (str_contains($text, $needle)) {
                    $found[] = str_replace($root, '', $path) . ' mentions ' . $needle;
                }
            }
        }

        $this->assertSame([], $found, 'the old table package is still referred to');
    }

    public function test_the_package_is_not_required_and_its_files_are_gone(): void
    {
        $this->assertStringNotContainsString('rappasoft', file_get_contents(base_path('composer.json')));
        $this->assertStringNotContainsString('rappasoft/laravel-livewire-tables', file_get_contents(base_path('composer.lock')));
        $this->assertDirectoryDoesNotExist(base_path('resources/views/vendor/livewire-tables'));
        $this->assertFileDoesNotExist(config_path('livewire-tables.php'));
        $this->assertFileDoesNotExist(app_path('Livewire/LivewireTableComponent.php'));
        $this->assertDirectoryDoesNotExist(app_path('Livewire/Concerns'), 'the two concerns became part of the in-house base');
    }

    public function test_no_page_asks_for_the_package_assets(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertStringNotContainsString('laravel-livewire-tables', $layout);
    }
}
