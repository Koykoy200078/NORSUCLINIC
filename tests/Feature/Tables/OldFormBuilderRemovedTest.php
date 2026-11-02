<?php

namespace Tests\Feature\Tables;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Upgrade plan U0.3: the abandoned laravelcollective/html package is gone and must stay gone. Every form of the 83 views is drawn
 * with spatie/laravel-html (`html()->`); a new `Form::` call, or the package coming back, would fail at the next Laravel step,
 * which Collective does not support.
 */
class OldFormBuilderRemovedTest extends TestCase
{
    /** @return array<int, string> */
    private function sourceFiles(): array
    {
        $files = [];
        foreach (['app', 'config', 'database', 'resources', 'routes', 'tests'] as $directory) {
            foreach (File::allFiles(base_path($directory)) as $file) {
                $path = str_replace('\\', '/', $file->getPathname());
                if (preg_match('/\.php$/', $path) && ! str_contains($path, '/tests/Fixtures/') && ! str_contains($path, 'OldFormBuilderRemovedTest') && ! str_contains($path, 'FormBuilderRulesTest')) {
                    $files[] = $path;
                }
            }
        }

        return $files;
    }

    public function test_no_view_or_class_calls_the_old_form_builder(): void
    {
        $root = str_replace('\\', '/', base_path()) . '/';
        $found = [];
        foreach ($this->sourceFiles() as $path) {
            $text = file_get_contents($path);
            if (preg_match('/(?<![A-Za-z_\\\\])Form::[a-z]+\(|Collective\\\\Html|FormFacade|HtmlFacade/', $text, $match)) {
                $found[] = str_replace($root, '', $path) . ' uses ' . $match[0];
            }
        }

        $this->assertSame([], $found, 'the old form builder is still called');
    }

    public function test_the_package_is_not_required_and_the_form_helpers_are_spatie_s(): void
    {
        $this->assertStringNotContainsString('laravelcollective', file_get_contents(base_path('composer.json')));
        $this->assertStringNotContainsString('laravelcollective', file_get_contents(base_path('composer.lock')));
        $this->assertFalse(class_exists('Collective\Html\FormBuilder'), 'the package is still installed');
        $this->assertInstanceOf(\Spatie\Html\Html::class, html());
        $this->assertFalse(function_exists('link_to'), 'Collective\'s helper functions are gone with it');
    }
}
