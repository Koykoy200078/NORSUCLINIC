<?php

namespace Tests\Feature\Regression;

use App\Models\Setting;
use App\Models\Specialization;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

class SettingsPassATest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function general(array $overrides = []): array
    {
        $specialty = Specialization::firstOrCreate(['name' => 'General Medicine']);

        return array_merge(['sectionName' => 'general', 'clinic_name' => 'Medical University Clinic',
            'email' => 'clinic@test.local', 'contact_no' => '9171234567', 'specialties' => [$specialty->id]], $overrides);
    }

    public function test_settings_save_only_the_fields_of_the_selected_form_and_refresh_the_cache(): void
    {
        Setting::updateOrCreate(['key' => 'clinic_name'], ['value' => 'Old name']);
        Setting::updateOrCreate(['key' => 'privacy_policy'], ['value' => 'Keep this policy']);
        Setting::updateOrCreate(['key' => 'default_role_permissions_applied'], ['value' => '["keep"]']);
        SettingsService::clearCache();
        $this->assertSame('Old name', SettingsService::get()['clinic_name']);

        $this->actingAs($this->makeAdmin())->post(route('setting.update'), $this->general([
            'privacy_policy' => 'Overwritten', 'default_role_permissions_applied' => '[]',
        ]))->assertSessionHasNoErrors();

        $this->assertSame('Medical University Clinic', SettingsService::get()['clinic_name']);
        $this->assertSame('Keep this policy', Setting::where('key', 'privacy_policy')->value('value'));
        $this->assertSame('["keep"]', Setting::where('key', 'default_role_permissions_applied')->value('value'));
    }

    public function test_settings_validate_the_specialty_list_and_both_contact_section_spellings(): void
    {
        $this->actingAs($this->makeAdmin());
        foreach (['text', [999999], [['bad']]] as $specialties) {
            $response = $this->postJson(route('setting.update'), $this->general(['specialties' => $specialties]));
            $response->assertUnprocessable();
            $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
        }
        foreach (['contact-information', 'contact_information'] as $section) {
            $this->postJson(route('setting.update'), ['sectionName' => $section])->assertUnprocessable();
        }
    }

    public function test_contact_settings_load_only_cities_in_the_selected_province(): void
    {
        $country = \App\Models\Country::firstOrCreate(['short_code' => 'PH'], ['name' => 'Philippines', 'phone_code' => '63']);
        $province = \App\Models\Province::create(['name' => 'Selected Province', 'country_id' => $country->id]);
        $other = \App\Models\Province::create(['name' => 'Other Province', 'country_id' => $country->id]);
        $city = \App\Models\City::create(['name' => 'Selected City', 'type' => 'city', 'state_id' => $province->id]);
        \App\Models\City::create(['name' => 'Unselected City', 'type' => 'city', 'state_id' => $other->id]);
        foreach (['country_id' => $country->id, 'state_id' => $province->id, 'city_id' => $city->id] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        SettingsService::clearCache();
        $this->actingAs($this->makeAdmin())->get(route('setting.index', ['section' => 'contact-information']))
            ->assertOk()->assertViewHas('cities', [$city->id => 'Selected City']);
    }

    public function test_the_real_general_form_saves_with_blank_optional_text_and_no_new_images(): void
    {
        foreach (['clinic_name' => 'Old clinic', 'university_physician_name' => 'Old signer', 'university_physician_title' => 'Old title',
            'logo' => '/assets/old-logo.png', 'favicon' => '/assets/old-icon.png'] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        $this->actingAs($this->makeAdmin())->post(route('setting.update'), $this->general([
            'landline_no' => '', 'university_physician_name' => '', 'university_physician_title' => '', 'logo' => null, 'favicon' => null,
        ]))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('', Setting::where('key', 'university_physician_name')->value('value'));
        $this->assertSame('', Setting::where('key', 'university_physician_title')->value('value'));
        $this->assertSame('/assets/old-logo.png', Setting::where('key', 'logo')->value('value'));
        $this->assertSame('/assets/old-icon.png', Setting::where('key', 'favicon')->value('value'));
    }
}
