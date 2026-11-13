<?php

namespace Tests\Feature\Regression;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Re-audit 2026-11-13 (found by the Playwright sweep): the about-us pictures are seeded as relative paths
 * ("assets/front/images/about/pic-2.jpg"). The public page lives at the site root, so they resolved there, but Front CMS
 * lives under /admin/, where the browser asked for /admin/assets/... and showed three broken pictures. The CMS page now
 * gives them as full asset URLs; uploaded pictures (full URLs) are unchanged.
 */
class CmsAboutImagesTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        // the real seed set: the CMS page needs the settings a real install has (terms, privacy policy, ...)
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_front_cms_shows_the_seeded_about_pictures_from_the_site_root(): void
    {
        foreach ([1 => 'pic-2.jpg', 2 => 'pic-1.jpg', 3 => 'pic-3.jpg'] as $n => $file) {
            Setting::updateOrCreate(['key' => "about_image_{$n}"], ['value' => "assets/front/images/about/{$file}"]);
        }

        $html = $this->actingAs($this->makeAdmin())->get(route('cms.index'))->assertOk()->getContent();

        foreach (['pic-2.jpg', 'pic-1.jpg', 'pic-3.jpg'] as $file) {
            $this->assertStringContainsString("url('" . asset("assets/front/images/about/{$file}") . "')", $html);
            $this->assertStringNotContainsString("url('assets/front/images/about/{$file}')", $html, 'a relative path resolves under /admin/');
        }
    }

    public function test_an_uploaded_picture_keeps_its_full_address(): void
    {
        $uploaded = url('uploads/settings/7/about.png');
        Setting::updateOrCreate(['key' => 'about_image_1'], ['value' => $uploaded]);

        $html = $this->actingAs($this->makeAdmin())->get(route('cms.index'))->assertOk()->getContent();

        $this->assertStringContainsString("url('{$uploaded}')", $html);
    }
}
