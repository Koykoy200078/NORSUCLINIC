<?php

namespace Tests\Feature\Regression;

use App\Models\College;
use App\Models\DocumentIssuance;
use App\Models\Patient;
use App\Models\Slider;
use App\Models\User;
use App\Services\SettingsService;
use Database\Seeders\CollegeSeeder;
use Database\Seeders\DefaultSliderSeeder;
use Database\Seeders\SettingTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 4.1 and 4.2: the clinic is called "Medical University Clinic" (clinic name only) and the College of Law
 * is "(COL)", not "(CL)". Both are data migrations: they change only what still holds the old default, never a text the
 * clinic wrote itself, and running them twice changes nothing.
 */
class ClinicNameAndCollegeMigrationTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private const OLD_LAW = 'College of Law (CL)';

    private const NEW_LAW = 'College of Law (COL)';

    private User $author;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->author = $this->makeNurse();
        $this->patient = $this->makePatient();
    }

    private function renameCollege(): object
    {
        return require database_path('migrations/2026_10_12_090000_rename_college_of_law_cl_to_col.php');
    }

    private function renameClinic(): object
    {
        return require database_path('migrations/2026_10_12_091000_rename_clinic_to_medical_university_clinic.php');
    }

    private function setting(string $key): ?string
    {
        return DB::table('settings')->where('key', $key)->value('value');
    }

    private function consultationOf(string $college): DocumentIssuance
    {
        return DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $this->author->id, 'user_id' => $this->patient->user_id,
            'name' => 'Juan Dela Cruz', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'college' => $college,
        ]);
    }

    public function test_the_college_of_law_and_every_saved_copy_of_its_name_say_col(): void
    {
        $law = DB::table('colleges')->insertGetId(['college_name' => self::OLD_LAW, 'created_at' => now(), 'updated_at' => now()]);
        $other = DB::table('colleges')->insertGetId(['college_name' => 'College of Arts and Sciences (CAS)', 'created_at' => now(), 'updated_at' => now()]);
        $lawVisit = $this->consultationOf(self::OLD_LAW);
        $otherVisit = $this->consultationOf('College of Arts and Sciences (CAS)');
        DB::table('lab_requests')->insert([
            'request_number' => '000001', 'document_creator_id' => $lawVisit->document_creator_id, 'patient_user_id' => $lawVisit->user_id,
            'patient_name' => 'Juan Dela Cruz', 'requested_at' => '2026-03-10', 'college' => self::OLD_LAW, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('activity_logs')->insert(['action' => 'created_consultation', 'description' => 'x', 'college' => self::OLD_LAW, 'created_at' => now(), 'updated_at' => now()]);

        $this->renameCollege()->up();

        $this->assertSame(self::NEW_LAW, DB::table('colleges')->where('id', $law)->value('college_name'));
        $this->assertSame('College of Arts and Sciences (CAS)', DB::table('colleges')->where('id', $other)->value('college_name'), 'another college is not touched');
        $this->assertSame(self::NEW_LAW, $lawVisit->fresh()->college);
        $this->assertSame('College of Arts and Sciences (CAS)', $otherVisit->fresh()->college);
        $this->assertSame(self::NEW_LAW, DB::table('lab_requests')->value('college'));
        $this->assertSame(self::NEW_LAW, DB::table('activity_logs')->value('college'));
        $this->assertSame(0, DB::table('colleges')->where('college_name', 'like', '%(CL)%')->count());
    }

    public function test_the_college_migration_is_safe_to_run_again_and_on_a_database_without_the_college(): void
    {
        $this->renameCollege()->up();
        $this->assertSame(0, DB::table('colleges')->where('college_name', self::NEW_LAW)->count(), 'nothing to rename, nothing created');

        DB::table('colleges')->insert(['college_name' => self::OLD_LAW, 'created_at' => now(), 'updated_at' => now()]);
        $this->renameCollege()->up();
        $this->renameCollege()->up();
        $this->assertSame(1, DB::table('colleges')->where('college_name', self::NEW_LAW)->count());
        $this->assertSame(1, DB::table('colleges')->where('college_name', 'like', 'College of Law%')->count());
    }

    public function test_the_college_seeder_now_creates_col_and_the_report_heading_reads_col(): void
    {
        $this->seed(CollegeSeeder::class);

        $law = College::where('college_name', 'like', 'College of Law%')->sole();
        $this->assertSame(self::NEW_LAW, $law->college_name);
        $this->assertSame('COL', $law->abbreviation);
    }

    public function test_the_default_clinic_name_becomes_medical_university_clinic_and_a_name_the_clinic_chose_is_kept(): void
    {
        foreach (['Norsu Clinic', 'NORSU Clinic', '  norsu clinic ', 'NORSU CLINIC'] as $old) {
            DB::table('settings')->where('key', 'clinic_name')->delete();
            DB::table('settings')->insert(['key' => 'clinic_name', 'value' => $old]);

            $this->renameClinic()->up();

            $this->assertSame('Medical University Clinic', $this->setting('clinic_name'), "'{$old}'");
        }

        DB::table('settings')->where('key', 'clinic_name')->update(['value' => 'My Edited Clinic']);
        $this->renameClinic()->up();
        $this->assertSame('My Edited Clinic', $this->setting('clinic_name'), 'a name somebody typed is never replaced');

        // The university's own e-mail address, logo and favicon keep their technical names.
        DB::table('settings')->insert([
            ['key' => 'email', 'value' => 'norsumedicalclinic@gmail.com'],
            ['key' => 'logo', 'value' => 'assets/image/norsu_logo.png'],
        ]);
        $this->renameClinic()->up();
        $this->assertSame('norsumedicalclinic@gmail.com', $this->setting('email'));
        $this->assertSame('assets/image/norsu_logo.png', $this->setting('logo'));
    }

    public function test_the_pages_show_the_new_name_at_once_not_after_the_settings_cache_runs_out(): void
    {
        DB::table('settings')->insert(['key' => 'clinic_name', 'value' => 'Norsu Clinic']);
        SettingsService::clearCache();

        // Every page asked for the name an hour ago and the answer is cached.
        $this->assertSame('Norsu Clinic', getAppName());

        $this->renameClinic()->up();

        $this->assertSame('Medical University Clinic', getAppName());
        $this->assertSame('Medical University Clinic', getSettingValue('clinic_name'));
    }

    public function test_the_default_landing_slider_text_follows_the_new_name_but_an_edited_text_does_not(): void
    {
        $default = Slider::create(['title' => 'Your Campus Health Care', 'short_description' => 'Prioritizing your wellness at NORSU Clinic.', 'is_default' => true]);
        $edited = Slider::create(['title' => 'Our own slide', 'short_description' => 'We care about NORSU Clinic patients', 'is_default' => false]);

        $this->renameClinic()->up();
        $this->renameClinic()->up();

        $this->assertSame('Prioritizing your wellness at Medical University Clinic', $default->fresh()->short_description);
        $this->assertSame('We care about NORSU Clinic patients', $edited->fresh()->short_description);
    }

    public function test_the_seeders_use_the_new_name_and_the_slider_text_still_fits_its_form(): void
    {
        $this->seed([SettingTableSeeder::class, DefaultSliderSeeder::class]);

        $this->assertSame('Medical University Clinic', $this->setting('clinic_name'));
        $text = Slider::where('is_default', true)->value('short_description');
        $this->assertSame('Prioritizing your wellness at Medical University Clinic', $text);
        $this->assertLessThanOrEqual(55, mb_strlen($text), 'the slider form refuses a longer text');
    }

    public function test_the_migrations_leave_a_database_with_nothing_to_rename_unchanged(): void
    {
        $before = [DB::table('settings')->count(), DB::table('sliders')->count(), DB::table('colleges')->count()];

        $this->renameCollege()->up();
        $this->renameClinic()->up();

        $this->assertSame($before, [DB::table('settings')->count(), DB::table('sliders')->count(), DB::table('colleges')->count()]);
    }
}
