<?php

namespace Tests\Feature\Regression;

use App\Models\Country;
use App\Models\Patient;
use App\Models\PatientType;
use App\Models\Province;
use App\Models\User;
use App\Repositories\PrescriptionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * STATUS.md section 5 clean-up: broken / dead code that was still reachable from a screen, and routes
 * that were registered twice.
 */
class PendingCleanupTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function patientPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Quick', 'last_name' => 'Added', 'email' => 'quick.added@test.local',
            'gender' => User::FEMALE, 'dob' => '2004-02-02',
            'patient_type_id' => PatientType::where('code', 'student')->value('id'),
            'university_id_number' => 'S-2026-777',
            'nationality_citizenship' => 'Filipino', 'immunization_record' => 'Complete',
        ], $overrides);
    }

    /** The "add patient" pop-up on the dispensing form saved the patient and then crashed on an undefined helper. */
    public function test_adding_a_patient_from_the_dispensing_form_works_and_the_list_is_fresh(): void
    {
        $admin = $this->makeAdmin();
        $repository = app(PrescriptionRepository::class);
        $repository->getPatients();   // primes the 10-minute dropdown cache

        $response = $this->actingAs($admin)->postJson(route('dispense-records.store-patient'), $this->patientPayload());

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertNotNull(User::where('email', 'quick.added@test.local')->first());
        $this->assertContains('Quick Added', array_values((array) $response->json('data')));
    }

    public function test_a_pharmacy_staff_member_can_add_a_patient_from_the_dispensing_form(): void
    {
        $pharmacist = $this->makeStaff('pharmacist', 'pharmacy');

        $this->actingAs($pharmacist)
            ->postJson(route('staff.dispense-records.store-patient'), $this->patientPayload(['email' => 'second@test.local', 'university_id_number' => 'S-2026-778']))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(1, Patient::whereHas('user', fn ($q) => $q->where('email', 'second@test.local'))->count());
    }

    /** Nothing in the application calls /api/medicines (the consultation form uses medicines-by-category). */
    public function test_the_unused_medicines_api_is_gone(): void
    {
        $this->actingAs($this->makeAdmin())->getJson('/api/medicines')->assertNotFound();
    }

    /** The country edit form posts to countries/{id} without a PUT override, so that route must keep working. */
    public function test_the_country_edit_form_still_saves(): void
    {
        $admin = $this->makeAdmin();
        $country = Country::create(['name' => 'Testland', 'short_code' => 'TL']);

        $this->actingAs($admin)
            ->post(url('/admin/countries/' . $country->id), ['name' => 'Testland Renamed', 'short_code' => 'TR'])
            ->assertOk();

        $this->assertSame('Testland Renamed', $country->fresh()->name);
    }

    /** The state edit form sends PUT, so the extra POST route was never used. */
    public function test_states_are_edited_with_put_only(): void
    {
        $admin = $this->makeAdmin();
        $country = Country::create(['name' => 'Testland', 'short_code' => 'TL']);
        $state = Province::create(['name' => 'Province A', 'country_id' => $country->id]);

        $this->actingAs($admin)
            ->putJson(url('/admin/states/' . $state->id), ['name' => 'Province B', 'country_id' => $country->id])
            ->assertOk();
        $this->assertSame('Province B', $state->fresh()->name);

        $this->actingAs($admin)
            ->postJson(url('/admin/states/' . $state->id), ['name' => 'Province C', 'country_id' => $country->id])
            ->assertStatus(405);
    }

    public function test_there_is_exactly_one_cache_warmup_command_and_it_runs(): void
    {
        $this->assertSame(1, collect(Artisan::all())->keys()->filter(fn ($name) => $name === 'cache:warmup')->count());
        $this->assertSame(1, count(glob(app_path('Console/Commands/*Warm*.php'))));
        $this->artisan('cache:warmup')->assertSuccessful();
    }

    public function test_no_command_file_is_empty(): void
    {
        foreach (glob(app_path('Console/Commands/*.php')) as $file) {
            $this->assertGreaterThan(0, filesize($file), basename($file) . ' is an empty file');
        }
    }

    public function test_the_404_page_uses_an_image_that_exists(): void
    {
        $html = $this->get('/definitely-not-a-page')->assertNotFound()->getContent();

        preg_match('/class="inner-content">\s*<img src="([^"]+)"/', $html, $matches);
        $this->assertNotEmpty($matches, 'the 404 page shows an image');
        $path = parse_url($matches[1], PHP_URL_PATH);
        $this->assertFileExists(public_path(ltrim($path, '/')));
    }
}
