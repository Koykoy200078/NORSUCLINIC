<?php

namespace Tests\Feature\Regression;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * The owner's decision (2026-11-14): keep Quill 2.0.3, the Front CMS editor of Terms / Privacy, although npm audit reports
 * GHSA-v3m3-f69x-jf25 (low) for it: the XSS is in Quill's HTML export, which has no fixed release (npm's "fix" is a
 * downgrade). The app never calls that export: it reads the editor's HTML (quill.root.innerHTML) and the server cleans it
 * with HTMLPurifier before saving (CMSController::update, E-SEC-4). These two tests hold that reasoning true.
 */
class QuillAdvisoryTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    public function test_no_script_uses_quills_html_export(): void
    {
        $users = [];
        foreach (['resources/assets/js', 'resources/js', 'resources/views'] as $folder) {
            foreach (File::allFiles(base_path($folder)) as $file) {
                if (str_contains($file->getContents(), 'getSemanticHTML')) {
                    $users[] = $file->getRelativePathname();
                }
            }
        }

        $this->assertSame([], $users, 'Quill\'s HTML export (getSemanticHTML) is what GHSA-v3m3-f69x-jf25 is about: read the advisory again');
    }

    public function test_script_in_the_cms_text_is_removed_before_it_is_saved_and_shown(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $hostile = '<p>Our terms</p><script>alert(1)</script><img src="x" onerror="alert(2)"><a href="javascript:alert(3)">link</a>';

        $this->actingAs($this->makeAdmin())->post(route('cms.update'), [
            'about_title' => 'About', 'about_short_description' => 'Short', 'about_experience' => 12,
            'terms_conditions' => json_encode($hostile), 'privacy_policy' => json_encode('<p>Privacy</p>'),
        ])->assertRedirect();

        $stored = Setting::where('key', 'terms_conditions')->value('value');
        $this->assertStringContainsString('<p>Our terms</p>', $stored);
        foreach (['<script', 'onerror', 'javascript:'] as $dangerous) {
            $this->assertStringNotContainsString($dangerous, $stored);
        }

        $page = $this->get(route('terms.conditions'))->assertOk()->getContent();
        $this->assertStringContainsString('<p>Our terms</p>', $page);
        $this->assertStringNotContainsString('alert(1)', $page);
        $this->assertStringNotContainsString('onerror="alert(2)"', $page);
    }
}
