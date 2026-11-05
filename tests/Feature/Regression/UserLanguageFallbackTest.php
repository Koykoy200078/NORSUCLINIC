<?php

namespace Tests\Feature\Regression;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * `users.language` may be NULL (the column is nullable; imported or older accounts). The sign-in check copied it into the
 * application's locale as it was, so the whole page was drawn with no locale: `<html lang="">`, an empty language for
 * the browser and screen readers, and a "null passed to str_replace()" deprecation on every page of that account. The
 * texts themselves fell back to English. It is the same on the clinic's current code; found in the verification pass after
 * the Laravel 12 upgrade.
 */
class UserLanguageFallbackTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    public function test_an_account_without_a_language_gets_the_default_language_on_every_page(): void
    {
        $this->seedAccessControl();
        $admin = $this->makeAdmin();
        User::whereKey($admin->id)->update(['language' => null]);

        $html = $this->actingAs($admin->fresh())->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('<html lang="en">', $html);
        $this->assertSame('en', app()->getLocale());
    }

    public function test_an_account_with_a_language_keeps_it(): void
    {
        $this->seedAccessControl();
        $admin = $this->makeAdmin();
        User::whereKey($admin->id)->update(['language' => 'en']);

        $this->actingAs($admin->fresh())->get(route('admin.dashboard'))->assertOk()->assertSee('<html lang="en">', false);
    }
}
