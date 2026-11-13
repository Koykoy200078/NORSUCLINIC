<?php

namespace Tests\Feature\Upgrade;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Upgrade plan U4 (Laravel Mix -> Vite): the contract between the views and the asset build. Every built or copied file a
 * view names through asset() or mix() must be one the build declares (a key of public/mix-manifest.json, which Mix writes
 * today and the new build writes in the same format, so mix() keeps working) and must exist on disk: a missing one is a
 * 404 in the browser. Nothing may point the pages at a development server (public/hot): the clinic PC never runs Node.
 */
class AssetBuildContractTest extends TestCase
{
    /** where the build writes; anything a view names below these comes from the build, not from git or uploads */
    private const BUILD_AREAS = ['assets/', 'backend/', 'css/', 'fonts/', 'js/', 'web/', 'webfonts/', 'messages.js'];

    /** @return array<string, list<string>> built path => the views that name it */
    private function builtPathsNamedByViews(): array
    {
        $named = [];
        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            // Blade and HTML comments are not drawn, so a path inside one is never requested
            $source = preg_replace(['/\{\{--.*?--\}\}/s', '/<!--.*?-->/s'], '', $file->getContents());
            preg_match_all('/\b(?:asset|mix)\(\s*([\'"])([^\'"]+)\1\s*\)/', $source, $matches);
            foreach ($matches[2] as $path) {
                $path = ltrim($path, '/');
                foreach (self::BUILD_AREAS as $area) {
                    if (str_starts_with($path, $area)) {
                        $named[$path][] = str_replace('\\', '/', $file->getRelativePathname());
                        break;
                    }
                }
            }
        }
        ksort($named);

        return $named;
    }

    public function test_every_built_file_a_view_names_is_declared_by_the_build_and_exists(): void
    {
        $manifestPath = public_path('mix-manifest.json');
        $this->assertFileExists($manifestPath, 'build the assets first (npm run dev)');
        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        $this->assertIsArray($manifest);

        $named = $this->builtPathsNamedByViews();
        $this->assertGreaterThan(30, count($named), 'the views name the layouts\' scripts and styles');

        $problems = [];
        foreach ($named as $path => $views) {
            $where = implode(', ', array_unique($views));
            if (! is_file(public_path($path))) {
                $problems[] = "{$path} does not exist (named in {$where})";
            } elseif (! array_key_exists('/' . $path, $manifest) && $path !== 'messages.js') {
                $problems[] = "{$path} is not declared by the build (named in {$where})";
            }
        }

        $this->assertSame([], $problems, "Views name built files the build does not provide:\n" . implode("\n", $problems));
    }

    public function test_the_layouts_scripts_and_styles_are_among_them(): void
    {
        // the contract is only worth something if it sees the files every layout loads
        $named = array_keys($this->builtPathsNamedByViews());
        foreach (['js/third-party.js', 'js/pages.js', 'js/realtime.js', 'js/front-third-party.js', 'js/front-pages.js', 'js/custom-auth.js', 'js/queue-board.js', 'assets/css/style.css', 'css/plugins.css', 'assets/css/pages.css', 'css/app.css', 'css/front-pages.css'] as $file) {
            $this->assertContains($file, $named);
        }
    }

    public function test_no_page_is_pointed_at_a_development_server(): void
    {
        // mix() serves every asset from the URL in public/hot when that file exists (a hot-reload server on the laptop)
        $this->assertFileDoesNotExist(public_path('hot'));
    }
}
