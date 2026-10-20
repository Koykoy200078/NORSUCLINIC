<?php

namespace Tests\Feature\Regression;

use App\Models\Country;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

class RemainingAuditPassATest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    public function test_location_edits_validate_every_saved_column(): void
    {
        $this->actingAs($this->makeAdmin());
        $country = Country::firstOrCreate(['short_code' => 'PH'], ['name' => 'Philippines', 'phone_code' => '63']);
        $province = Province::create(['name' => 'Test Province', 'country_id' => $country->id]);
        foreach ([['country_id' => 999999], ['country_id' => ['bad']]] as $invalid) {
            $response = $this->putJson(route('states.update', $province->id), array_merge(['name' => $province->name], $invalid));
            $response->assertUnprocessable();
            $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
        }
        $response = $this->putJson(route('countries.update', $country), ['name' => $country->name, 'short_code' => str_repeat('x', 192)]);
        $response->assertUnprocessable();
        $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
    }

    public function test_cms_refuses_structured_text_and_malformed_rich_text_without_writing_anything(): void
    {
        $this->actingAs($this->makeAdmin());
        $valid = ['about_title' => 'Audit title', 'about_experience' => 12, 'about_short_description' => 'About the clinic',
            'terms_conditions' => json_encode('<p>Terms</p>'), 'privacy_policy' => json_encode('<p>Privacy</p>')];
        foreach ([['about_title' => ['bad']], ['about_short_description' => ['bad']], ['terms_conditions' => 'broken JSON'],
            ['privacy_policy' => json_encode(['bad'])], ['about_experience' => -1]] as $invalid) {
            $response = $this->postJson(route('cms.update'), array_merge($valid, $invalid));
            $response->assertUnprocessable();
            $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
        }
    }

    public function test_invalid_profile_input_is_not_copied_into_application_logs(): void
    {
        $user = $this->makeNurse();
        Log::spy();
        $this->actingAs($user)->putJson(route('update.profile.setting'), [
            'first_name' => 'Sensitive-profile-marker', 'email' => 'not-an-email', 'time_zone' => 'Asia/Manila',
        ])->assertUnprocessable();
        Log::shouldNotHaveReceived('error', function (...$arguments): bool {
            return str_contains(json_encode($arguments), 'Sensitive-profile-marker');
        });
    }

    public function test_queue_preview_does_not_expose_clinical_details_without_the_documents_permission(): void
    {
        $patient = $this->makePatient();
        $nurse = $this->makeNurse();
        \App\Models\DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $nurse->id, 'user_id' => $patient->user_id,
            'name' => 'Test Patient', 'age' => 20, 'gender' => 'Male', 'address' => 'Test Address',
            'requested_at' => now()->toDateString(), 'complaints' => 'Private complaint', 'assessment' => 'Private assessment',
        ]);
        \App\Models\Role::findByName('staff')->syncPermissions(['manage_patients']);
        $this->actingAs($nurse->fresh())->getJson('/api/patient/'.$patient->id.'/latest-consultation')->assertOk()
            ->assertJsonPath('success', true)->assertJsonMissingPath('consultation.complaints')->assertJsonMissingPath('consultation.assessment');
    }

    public function test_add_queue_and_edit_lab_pages_include_their_working_scripts(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get(route('patient-queue.create'))->assertOk()->assertSee("$('#patientId').select2", false);
        $patient = $this->makePatient();
        $lab = \App\Models\LabRequest::create([
            'request_number' => 'AUDIT-522', 'document_creator_id' => $admin->id, 'patient_user_id' => $patient->user_id,
            'patient_name' => 'Test Patient', 'requested_at' => now()->toDateString(), 'status' => 'pending',
        ]);
        $this->get(route('lab-requests.edit', $lab))->assertOk()->assertSee("const badge = document.getElementById('selected-count-badge')", false);
    }

    public function test_every_named_route_is_unique(): void
    {
        $names = [];
        foreach (\Illuminate\Support\Facades\Route::getRoutes() as $route) {
            $name = $route->getName();
            if ($name === null) {
                continue;
            }
            $this->assertArrayNotHasKey($name, $names, 'Duplicate route name: '.$name);
            $names[$name] = $route->uri();
        }
    }

    public function test_a_province_can_be_created_under_a_real_country(): void
    {
        $country = Country::firstOrCreate(['short_code' => 'PH'], ['name' => 'Philippines']);
        $this->actingAs($this->makeAdmin())->postJson(route('states.store'), [
            'name' => 'New Audit Province', 'country_id' => $country->id,
        ])->assertOk();
        $this->assertDatabaseHas('states', ['name' => 'New Audit Province', 'country_id' => $country->id]);
    }
}
