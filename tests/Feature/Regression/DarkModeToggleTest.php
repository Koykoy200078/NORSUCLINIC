<?php

namespace Tests\Feature\Regression;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Re-audit 2026-11-13: switching the theme changes a saved setting, so it must not happen on a GET. A GET can be fired by
 * any page the user opens (an <img> or a prefetched link pointing at /update-dark-mode) without the CSRF check that a POST
 * goes through.
 */
class DarkModeToggleTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    public function test_a_get_request_no_longer_switches_the_theme(): void
    {
        $user = $this->makeNurse(['dark_mode' => false]);

        $this->actingAs($user)->get('/update-dark-mode')->assertStatus(405);

        $this->assertFalse((bool) $user->fresh()->dark_mode);
    }

    public function test_the_theme_button_switches_it_with_a_post_and_back(): void
    {
        $user = $this->makeNurse(['dark_mode' => false]);

        $this->actingAs($user)->postJson(route('update-dark-mode'))->assertOk()->assertJson(['success' => true]);
        $this->assertTrue((bool) $user->fresh()->dark_mode);

        $this->actingAs($user->fresh())->postJson(route('update-dark-mode'))->assertOk();
        $this->assertFalse((bool) $user->fresh()->dark_mode);
    }

    public function test_the_button_sends_a_post(): void
    {
        $script = (string) file_get_contents(resource_path('assets/js/custom/custom.js'));
        $call = substr($script, (int) strpos($script, 'route("update-dark-mode")'), 80);

        $this->assertMatchesRegularExpression('/type:\s*"post"/i', $call);
    }
}
