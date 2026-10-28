<?php

namespace Tests\Feature\Regression;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Two ways a screen looked unfinished on the Inventory page (2026-10-28 owner report), both caused by markup written for a
 * Metronic theme that this application's stylesheets only partly contain:
 *
 *  - `btn-light-primary` (and its siblings) is defined by NO stylesheet of the application, so a button that uses it is drawn as
 *    plain text, without a border, padding or background;
 *  - the theme draws the underline of a tab as an absolutely positioned `::after` (width 100%, bottom -6px) of `.nav-link`, which
 *    needs a positioned ancestor: without `position-relative` on the `li.nav-item` the line is painted at the bottom of the whole
 *    page (a blue line across the last table rows) instead of under the tab.
 *
 * Static checks, so they need no built assets and run in the normal suite.
 */
class ViewThemeClassesTest extends TestCase
{
    /** @return array<string, string> view path (relative) => contents, without the Livewire tables vendor views (Bootstrap 4 / 5 variants of the package) */
    private function views(): array
    {
        $views = [];
        foreach (File::allFiles(resource_path('views')) as $file) {
            $path = str_replace('\\', '/', $file->getRelativePathname());
            if (str_starts_with($path, 'vendor/livewire-tables/')) {
                continue;
            }
            $views[$path] = $file->getContents();
        }

        return $views;
    }

    public function test_no_view_uses_a_button_or_badge_style_that_no_stylesheet_defines(): void
    {
        $found = [];
        foreach ($this->views() as $path => $contents) {
            if (preg_match_all('/\b(?:btn|badge)-light-(?:primary|secondary|success|danger|warning|info|dark)\b/', $contents, $matches)) {
                $found[$path] = array_values(array_unique($matches[0]));
            }
        }

        $this->assertSame([], $found, 'These classes are not in the application stylesheets (the element would look like plain text). '
            .'Use a Bootstrap style that exists, e.g. btn-outline-primary or bg-primary: '.json_encode($found));
    }

    public function test_a_back_button_is_visible_on_the_page_background(): void
    {
        // btn-light is #eff3f6 on a page that is almost the same colour: "Back to inventory" / "Back to queue" looked like plain text
        // next to the buttons around them. The other Back buttons of the application are btn-secondary.
        $pale = [];
        foreach ($this->views() as $path => $contents) {
            if (preg_match('/<a\b[^>]*\bclass="btn btn-light"[^>]*>\s*Back\b/', $contents)) {
                $pale[] = $path;
            }
        }

        $this->assertSame([], $pale, 'Use btn btn-secondary for a Back button: '.json_encode($pale));
    }

    public function test_every_tab_of_a_tab_list_can_hold_its_underline(): void
    {
        $lists = 0;
        $unpositioned = [];
        foreach ($this->views() as $path => $contents) {
            if (! preg_match_all('/<ul\b[^>]*\bclass="[^"]*\bnav-tabs\b[^"]*"[^>]*>(.*?)<\/ul>/s', $contents, $blocks)) {
                continue;
            }
            foreach ($blocks[1] as $block) {
                $lists++;
                preg_match_all('/<li\b[^>]*\bclass="([^"]*\bnav-item\b[^"]*)"/', $block, $items);
                $this->assertNotEmpty($items[1], "$path: a nav-tabs list without tab items");
                foreach ($items[1] as $classes) {
                    if (! preg_match('/\bposition-relative\b/', $classes)) {
                        $unpositioned[$path][] = trim($classes);
                    }
                }
            }
        }

        $this->assertGreaterThanOrEqual(6, $lists, 'the scan found too few tab lists: the pattern no longer matches the markup');
        $this->assertSame([], $unpositioned, 'li.nav-item needs position-relative, or the tab underline is painted at the bottom of the page: '
            .json_encode($unpositioned));
    }
}
