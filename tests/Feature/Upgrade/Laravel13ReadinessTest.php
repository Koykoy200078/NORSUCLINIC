<?php

namespace Tests\Feature\Upgrade;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Repositories\MedicineBillRepository;
use App\Repositories\PrescriptionRepository;
use App\Repositories\SettingRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Upgrade plan U3 (Laravel 13): what Laravel 13 and Symfony 8 change under the application and has to keep working.
 *
 * - Symfony 7.4 deprecates `Request::get()` and Symfony 8 (which Laravel 13 allows) removes it: the application reads its
 *   query-string and form fields with `input()` instead;
 * - Laravel 13's cache refuses to turn a stored object back into an object unless the class is listed (`serializable_classes`):
 *   what the application caches must be plain arrays, numbers and strings;
 * - the cookie and cache names of the application's own config must not move with the framework's new defaults (everybody would be
 *   signed out and every cached list would be lost);
 * - the forgery check keeps refusing a POST without a token (Laravel 13 renames the middleware and adds an origin check).
 */
class Laravel13ReadinessTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    public function test_no_application_code_reads_a_request_field_with_get(): void
    {
        $offenders = [];

        foreach (File::allFiles(app_path()) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            foreach (preg_split('/\R/', $file->getContents()) as $number => $line) {
                if (preg_match('/\$request->get\(|\$this->request->get\(|\brequest\(\)->get\(|\bRequest::get\(/', $line) === 1) {
                    $offenders[] = $file->getRelativePathname().':'.($number + 1);
                }
            }
        }

        $this->assertSame([], $offenders, 'Request::get() is removed in Symfony 8; use input() / query(): '.implode(', ', $offenders));
    }

    public function test_every_csv_writer_names_its_escape_character(): void
    {
        // PHP 8.4 warns when fputcsv() is called without the escape argument and PHP 9 changes its default. The current default is
        // given explicitly, so the files stay byte for byte the same (ReportCsvParityTest pins them).
        $offenders = [];

        foreach (File::allFiles(app_path()) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $source = $file->getContents();
            $offset = 0;
            while (($at = strpos($source, 'fputcsv(', $offset)) !== false) {
                // the whole call, up to its closing parenthesis (a closure inside it may hold semicolons)
                $depth = 0;
                $end = $at + strlen('fputcsv');
                do {
                    $depth += $source[$end] === '(' ? 1 : ($source[$end] === ')' ? -1 : 0);
                    $end++;
                } while ($depth > 0 && $end < strlen($source));
                $statement = substr($source, $at, $end - $at);
                if (! str_contains($statement, "'\\\\'")) {
                    $offenders[] = $file->getRelativePathname().' at byte '.$at;
                }
                $offset = $at + 1;
            }
        }

        $this->assertSame([], $offenders, 'fputcsv() without the escape argument: '.implode(', ', $offenders));
    }

    public function test_the_settings_page_reads_its_section_from_the_query_string(): void
    {
        $this->seedAccessControl();
        $this->actingAs($this->makeAdmin());

        $this->get(route('setting.index'))->assertOk();
        $this->get(route('setting.index', ['section' => 'contact-information']))->assertOk();
        $this->get(route('setting.index', ['section' => 'nonsense']))->assertNotFound();
    }

    public function test_the_settings_state_and_city_lists_read_their_ids_from_the_query_string(): void
    {
        $this->seedAccessControl();
        $this->actingAs($this->makeAdmin());
        $country = Country::create(['name' => 'Zzland', 'short_code' => 'ZZ']);
        $state = State::create(['name' => 'Zzstate', 'country_id' => $country->id]);
        $city = City::create(['name' => 'Zzcity', 'state_id' => $state->id]);

        $states = $this->getJson(route('states-list', ['settingCountryId' => $country->id]))->assertOk()->json('data.states');
        $cities = $this->getJson(route('cities-list', ['stateId' => $state->id]))->assertOk()->json('data.cities');

        $this->assertSame('Zzstate', $states[$state->id] ?? null);
        $this->assertSame('Zzcity', $cities[$city->id] ?? null);
        $this->assertSame([], $this->getJson(route('states-list', ['settingCountryId' => $country->id + 999]))->assertOk()->json('data.states'));
    }

    public function test_the_document_form_opens_for_the_patient_named_in_the_query_string(): void
    {
        $this->seedAccessControl();
        $this->actingAs($this->makeAdmin());
        $patient = $this->makePatient(['first_name' => 'Zzpatientfirst']);

        $html = $this->get(route('document-issuances.create', ['document_type' => 'consultation_form', 'user_id' => $patient->user_id]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Zzpatientfirst', $html);
    }

    public function test_the_lab_request_user_search_reads_its_query(): void
    {
        $this->seedAccessControl();
        $this->actingAs($this->makeAdmin());
        $this->makePatient(['first_name' => 'Zzsearchable']);

        $this->assertSame([], $this->getJson(route('lab-requests.search-users', ['query' => 'z']))->assertOk()->json(), 'one character is not a search');
        $names = collect($this->getJson(route('lab-requests.search-users', ['query' => 'Zzsearch']))->assertOk()->json())->pluck('name')->implode(' ');
        $this->assertStringContainsString('Zzsearchable', $names);
    }

    public function test_what_the_application_caches_for_its_lists_holds_no_object(): void
    {
        $this->seedAccessControl();
        $this->makePatient(['first_name' => 'Zzcachedpatient']);
        $this->makeDoctor(['first_name' => 'Zzcacheddoctor']);
        Category::create(['name' => 'Zzcategory', 'is_active' => 1]);
        $bill = app(MedicineBillRepository::class);
        $prescription = app(PrescriptionRepository::class);

        // The lists the forms draw. Each call fills the cache; a second call is served from it and must be the same list.
        $lists = [
            'active_patients_medicine_bill' => fn () => $bill->getPatients(),
            'active_doctors_medicine_bill' => fn () => $bill->getDoctors(),
            'active_medicine_categories' => fn () => $bill->getMedicinesCategoriesData(),
            'active_patients_prescription' => fn () => $prescription->getPatients(),
            'active_doctors_prescription' => fn () => $prescription->getDoctors(),
        ];
        foreach ($lists as $key => $read) {
            Cache::forget($key);
            $cold = $read();
            $this->assertNotEmpty($cold, "{$key}: the fixture has a row for this list");
            $this->assertSame($cold->all(), $read()->all(), "{$key}: the cached list equals the freshly read one");
            $this->assertNoObject(Cache::get($key), $key);
        }

        // Saving the settings used to cache the whole Eloquent collection under 'settings'. Nothing reads that key.
        app(SettingRepository::class)->update(['sectionName' => 'general', 'clinic_name' => 'A clinic'], 1);
        $this->assertNoObject(Cache::get('settings'), 'settings');
    }

    public function test_the_cookie_and_cache_names_do_not_follow_the_framework_defaults(): void
    {
        // Laravel 13 changes the fallback names (hyphens instead of underscores); this application's own config keeps its names, so nobody is
        // signed out by the upgrade and the cached lists are not abandoned.
        $this->assertMatchesRegularExpression('/_session$/', (string) config('session.cookie'));
        $this->assertMatchesRegularExpression('/_cache_$/', (string) config('cache.prefix'));
    }

    public function test_the_forgery_check_still_refuses_a_post_without_a_token_and_accepts_one_with_it(): void
    {
        // Laravel skips the check while the environment is "testing"; switch it on for this request.
        $this->app['env'] = 'local';

        $this->post('/login', ['email' => 'nobody@example.test', 'password' => 'wrong'])->assertStatus(419);

        $this->withSession(['_token' => 'known-token'])
            ->post('/login', ['_token' => 'known-token', 'email' => 'nobody@example.test', 'password' => 'wrong'])
            ->assertRedirect()->assertSessionHasErrors();

        $this->withSession(['_token' => 'known-token'])
            ->post('/login', ['email' => 'nobody@example.test', 'password' => 'wrong'], ['X-CSRF-TOKEN' => 'known-token'])
            ->assertRedirect()->assertSessionHasErrors();
    }

    /** @param mixed $value */
    private function assertNoObject($value, string $label): void
    {
        if (is_array($value)) {
            foreach ($value as $inner) {
                $this->assertNoObject($inner, $label);
            }

            return;
        }

        $this->assertFalse(is_object($value), "{$label}: the cache holds an object (".(is_object($value) ? $value::class : '').'), Laravel 13 will not read it back');
    }
}
