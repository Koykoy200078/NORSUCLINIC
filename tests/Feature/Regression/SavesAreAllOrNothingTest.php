<?php

namespace Tests\Feature\Regression;

use App\Models\Permission;
use App\Models\Setting;
use App\Repositories\RoleRepository;
use App\Repositories\SettingRepository;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 5, Pass A item 2: a save that makes several writes is all-or-nothing. A transaction inside the test's own
 * transaction raises the level by one, so "the level when the write ran is above the level before" proves the write
 * ran inside the repository's own transaction.
 */
class SavesAreAllOrNothingTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    /** @return array<int, int> the transaction level at which each statement matching $pattern ran */
    private function levelsOf(string $pattern, callable $action): array
    {
        $levels = [];
        DB::listen(function (QueryExecuted $query) use ($pattern, &$levels) {
            if (preg_match($pattern, $query->sql) === 1) {
                $levels[] = DB::transactionLevel();
            }
        });
        $action();

        return $levels;
    }

    public function test_a_new_role_and_its_permissions_are_saved_in_one_transaction(): void
    {
        $this->seedAccessControl();
        $base = DB::transactionLevel();

        $levels = $this->levelsOf('/^insert into `(roles|role_has_permissions)`/', function () {
            app(RoleRepository::class)->store([
                'display_name' => 'Lab Assistant',
                'permission_id' => Permission::whereIn('name', ['manage_patients'])->pluck('id')->all(),
            ]);
        });

        $this->assertNotEmpty($levels);
        foreach ($levels as $level) {
            $this->assertGreaterThan($base, $level, 'the role and its permissions must share a transaction of their own');
        }
    }

    public function test_saving_the_front_cms_shows_the_new_text_at_once_and_saves_it_in_one_transaction(): void
    {
        $this->seedAccessControl();
        foreach (['terms_conditions', 'privacy_policy', 'about_title', 'about_short_description', 'about_experience'] as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => 'old']);
        }
        $this->assertSame('old', getSettingValue('about_title'), 'the value is cached now');
        $base = DB::transactionLevel();

        $levels = [];
        DB::listen(function (QueryExecuted $query) use (&$levels) {
            if (str_starts_with($query->sql, 'update `settings`')) {
                $levels[] = DB::transactionLevel();
            }
        });
        $this->actingAs($this->makeAdmin())->post(route('cms.update'), [
            'about_title' => 'New about title', 'about_short_description' => 'Short', 'about_experience' => 12,
            'terms_conditions' => json_encode('<p>Terms</p>'), 'privacy_policy' => json_encode('<p>Privacy</p>'),
        ])->assertRedirect();

        $this->assertSame('New about title', getSettingValue('about_title'), 'the public About page must not keep showing the old text for an hour');
        $this->assertNotEmpty($levels);
        foreach ($levels as $level) {
            $this->assertGreaterThan($base, $level);
        }
    }

    public function test_the_settings_of_one_form_are_saved_in_one_transaction(): void
    {
        $this->seedAccessControl();
        Setting::updateOrCreate(['key' => 'clinic_name'], ['value' => 'Old']);
        Setting::updateOrCreate(['key' => 'email'], ['value' => 'old@test.local']);
        $base = DB::transactionLevel();

        $levels = $this->levelsOf('/^update `settings`/', function () {
            app(SettingRepository::class)->update(['sectionName' => 'general', 'clinic_name' => 'New name', 'email' => 'new@test.local'], 1);
        });

        $this->assertGreaterThanOrEqual(2, count($levels));
        foreach ($levels as $level) {
            $this->assertGreaterThan($base, $level, 'every setting of the form is written inside one transaction');
        }
        $this->assertSame('New name', Setting::where('key', 'clinic_name')->value('value'));
    }

    public function test_the_language_and_the_other_settings_of_the_form_are_saved_in_one_transaction(): void
    {
        $this->seedAccessControl();
        Setting::updateOrCreate(['key' => 'language'], ['value' => 'en']);
        Setting::updateOrCreate(['key' => 'clinic_name'], ['value' => 'Old']);
        $base = DB::transactionLevel();

        $levels = [];
        DB::listen(function (QueryExecuted $query) use (&$levels) {
            if (preg_match('/^update `(settings|users)`/', $query->sql) === 1) {
                $levels[] = DB::transactionLevel();
            }
        });
        $this->actingAs($this->makeAdmin())->post(route('setting.update'), [
            'sectionName' => 'general', 'language' => 'en', 'clinic_name' => 'Medical University Clinic', 'email' => 'c@test.local',
            'contact_no' => '9171234567', 'specialties' => [\App\Models\Specialization::firstOrCreate(['name' => 'General Medicine'])->id],
        ]);

        $this->assertGreaterThanOrEqual(3, count($levels), 'the language, the users and the form settings were written');
        foreach ($levels as $level) {
            $this->assertGreaterThan($base, $level);
        }
    }
}
