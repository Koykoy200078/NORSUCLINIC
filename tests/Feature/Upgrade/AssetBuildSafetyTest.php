<?php

namespace Tests\Feature\Upgrade;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * Re-audit 2026-11-14 (the owner's choice): build-assets.mjs used to empty public/js, css, assets ... before building into
 * them, so a build that stopped halfway left every page without its scripts and stylesheets until a build ran through. It
 * now builds into a staging folder next to the output and puts the result in place only at the end. Each test runs the real
 * build (node) into a throwaway folder; --simulate-failure stops it with an error once everything is built, before anything
 * is put in place.
 */
class AssetBuildSafetyTest extends TestCase
{
    private string $out;

    protected function setUp(): void
    {
        parent::setUp();
        $this->out = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'norsu-asset-build-' . Str::lower(Str::random(8));
        File::ensureDirectoryExists($this->out . '/js');
        File::put($this->out . '/js/pages.js', '/* the previous build */');
        File::put($this->out . '/js/left-by-an-old-build.js', '/* stale */');
        File::put($this->out . '/mix-manifest.json', '{"/js/pages.js": "/js/pages.js?id=previous"}');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->out);
        foreach (glob(dirname($this->out) . '/.' . basename($this->out) . '-staging*') ?: [] as $leftover) {
            File::deleteDirectory($leftover);
        }
        parent::tearDown();
    }

    private function build(string ...$options): Process
    {
        $process = new Process(['node', 'build-assets.mjs', '--out=' . $this->out, ...$options], base_path(), null, null, 300);
        $process->run();

        return $process;
    }

    private function stagingLeftovers(): array
    {
        return glob(dirname($this->out) . '/.' . basename($this->out) . '-staging*') ?: [];
    }

    public function test_a_build_that_fails_leaves_the_previous_output_untouched(): void
    {
        $build = $this->build('--simulate-failure');

        $this->assertFalse($build->isSuccessful(), 'the simulated failure must stop the build');
        $this->assertSame('/* the previous build */', File::get($this->out . '/js/pages.js'));
        $this->assertFileExists($this->out . '/js/left-by-an-old-build.js');
        $this->assertSame('{"/js/pages.js": "/js/pages.js?id=previous"}', File::get($this->out . '/mix-manifest.json'));
        $this->assertSame([], $this->stagingLeftovers(), 'no half-built staging folder is left behind');
    }

    public function test_a_build_that_succeeds_replaces_the_output_as_before(): void
    {
        $build = $this->build();

        $this->assertTrue($build->isSuccessful(), $build->getErrorOutput());
        $this->assertNotSame('/* the previous build */', File::get($this->out . '/js/pages.js'));
        $this->assertFileDoesNotExist($this->out . '/js/left-by-an-old-build.js', 'a replaced folder holds only the new build');
        $manifest = json_decode(File::get($this->out . '/mix-manifest.json'), true);
        $this->assertStringStartsWith('/js/pages.js?id=', $manifest['/js/pages.js']);
        $this->assertArrayNotHasKey('/js/left-by-an-old-build.js', $manifest);
        foreach (array_keys($manifest) as $file) {
            $this->assertFileExists($this->out . $file);
        }
        $this->assertSame([], $this->stagingLeftovers());
    }

    public function test_a_build_into_a_folder_that_does_not_exist_yet_creates_it(): void
    {
        File::deleteDirectory($this->out);

        $build = $this->build();

        $this->assertTrue($build->isSuccessful(), $build->getErrorOutput());
        $this->assertFileExists($this->out . '/js/pages.js');
        $this->assertFileExists($this->out . '/mix-manifest.json');
        $this->assertSame([], $this->stagingLeftovers());
    }
}
