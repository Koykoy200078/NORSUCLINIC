<?php

namespace Tests\Feature\Regression;

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * R3-M4: the uploaded logo is stored as an absolute URL built from APP_URL ("http://localhost/..."), which is a dead
 * image on every other PC of the LAN. It is now shown through the address the visitor used.
 */
class LogoUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_logo_and_favicon_follow_the_address_the_visitor_used(): void
    {
        config(['app.url' => 'http://localhost']);
        Setting::updateOrCreate(['key' => 'logo'], ['value' => 'http://localhost/uploads/1/logo.png']);
        Setting::updateOrCreate(['key' => 'favicon'], ['value' => 'http://localhost/uploads/2/favicon.png']);
        SettingsService::clearCache();

        $html = $this->get('http://192.168.1.50/login')->assertOk()->getContent();

        $this->assertStringContainsString('http://192.168.1.50/uploads/1/logo.png', $html);
        $this->assertStringContainsString('http://192.168.1.50/uploads/2/favicon.png', $html);
        $this->assertStringNotContainsString('http://localhost/uploads', $html);
    }

    public function test_a_relative_logo_and_the_default_logo_are_left_alone(): void
    {
        Setting::updateOrCreate(['key' => 'logo'], ['value' => '']);
        SettingsService::clearCache();

        $this->assertSame('assets/image/norsu_logo.png', getAppLogo());

        Setting::where('key', 'logo')->update(['value' => 'uploads/9/own.png']);
        SettingsService::clearCache();
        $this->assertSame('uploads/9/own.png', getAppLogo());
    }
}
