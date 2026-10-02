<?php

namespace Tests\Feature\Regression;

use App\Models\DocumentIssuance;
use App\Models\Illness;
use App\Models\IllnessSystem;
use App\Models\PatientType;
use App\Models\ServiceType;
use App\Services\Reports\AccomplishmentReportBuilder;
use App\Services\Reports\ReportFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Settings > Report lists: the administrator keeps the illness and service lists that the consultation form offers
 * and the Accomplishment Report counts. A line is switched off, never deleted, so old consultations keep their picks.
 */
class ReportListsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function systemId(string $name): int
    {
        return (int) IllnessSystem::where('name', $name)->value('id');
    }

    public function test_only_the_administrator_can_open_and_change_the_lists(): void
    {
        $illness = Illness::where('name', 'Headache')->firstOrFail();

        $this->actingAs($this->makeAdmin())->get(route('report-lists.index'))->assertOk()
            ->assertSee('Illnesses (by body system)')->assertSee('Headache')->assertSee('Tetanus injection');

        foreach ([$this->makeDoctor(), $this->makeStaff('nurse', 'triage_area')] as $user) {
            $this->actingAs($user)->get(route('report-lists.index'))->assertForbidden();
            $this->put(route('report-lists.illnesses.update', $illness), ['name' => 'Hacked'])->assertForbidden();
            $this->post(route('report-lists.services.store'), ['category' => ServiceType::CLINICAL, 'name' => 'Hacked'])->assertForbidden();
        }

        $this->assertSame('Headache', $illness->fresh()->name);
    }

    public function test_a_new_illness_is_offered_on_the_form_before_the_others_line_and_duplicates_are_refused(): void
    {
        $admin = $this->makeAdmin();
        $system = $this->systemId('Nervous System');

        $this->actingAs($admin)->post(route('report-lists.illnesses.store'), [
            'illness_system_id' => $system, 'name' => 'Tension-type headache', 'group_label' => '',
        ])->assertSessionDoesntHaveErrors();

        $new = Illness::where('name', 'Tension-type headache')->firstOrFail();
        $others = Illness::where('illness_system_id', $system)->where('is_other', true)->firstOrFail();
        $this->assertTrue($new->is_active);
        $this->assertFalse($new->is_other);
        $this->assertLessThan($others->sort_order, $new->sort_order, 'the Others line stays last');

        $this->get(route('document-issuances.create', ['document_type' => 'consultation_form']))->assertOk()->assertSee('Tension-type headache');

        // The same name in the same body system is refused; another system may reuse it.
        $this->post(route('report-lists.illnesses.store'), ['illness_system_id' => $system, 'name' => 'Tension-type headache'])->assertSessionHasErrors();
        $this->assertSame(1, Illness::where('illness_system_id', $system)->where('name', 'Tension-type headache')->count());
        $this->post(route('report-lists.illnesses.store'), ['illness_system_id' => $this->systemId('EENT'), 'name' => 'Tension-type headache'])->assertSessionDoesntHaveErrors();

        // Unknown body system / empty name are refused.
        $this->post(route('report-lists.illnesses.store'), ['illness_system_id' => 99999, 'name' => 'X'])->assertSessionHasErrors();
        $this->post(route('report-lists.illnesses.store'), ['illness_system_id' => $system, 'name' => ''])->assertSessionHasErrors();
    }

    public function test_an_illness_can_be_renamed_regrouped_and_switched_off_but_the_others_line_keeps_its_name(): void
    {
        $this->actingAs($this->makeAdmin());
        $headache = Illness::where('name', 'Headache')->firstOrFail();

        $this->put(route('report-lists.illnesses.update', $headache), ['name' => 'Headache (any)', 'group_label' => 'Pain', 'is_active' => '1'])
            ->assertSessionDoesntHaveErrors();
        $headache->refresh();
        $this->assertSame(['Headache (any)', 'Pain', true], [$headache->name, $headache->group_label, $headache->is_active]);

        // A checkbox that is not sent means "switch it off".
        $this->put(route('report-lists.illnesses.update', $headache), ['name' => 'Headache (any)'])->assertSessionDoesntHaveErrors();
        $this->assertFalse($headache->fresh()->is_active);

        $others = Illness::where('is_other', true)->firstOrFail();
        $this->put(route('report-lists.illnesses.update', $others), ['name' => 'Something else', 'is_active' => '1']);
        $this->assertSame('Others', $others->fresh()->name);

        // Renaming onto an existing line of the same body system is refused.
        $migraine = Illness::where('name', 'Migraine')->firstOrFail();
        $this->put(route('report-lists.illnesses.update', $migraine), ['name' => 'Insomnia', 'is_active' => '1'])->assertSessionHasErrors();
        $this->assertSame('Migraine', $migraine->fresh()->name);
    }

    public function test_a_switched_off_illness_leaves_the_form_but_old_consultations_keep_it_and_the_report_still_counts_it(): void
    {
        $admin = $this->makeAdmin();
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $patient = $this->makePatient();
        $headache = Illness::where('name', 'Headache')->firstOrFail();

        $visit = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $nurse->id, 'user_id' => $patient->user_id,
            'name' => 'Juan Dela Cruz', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => '2026-03-10',
            'consult_mode' => 'physical', 'informant' => 'Student', 'patient_type_id' => PatientType::where('code', 'student')->value('id'),
        ]);
        $visit->illnesses()->sync([$headache->id]);

        $this->actingAs($admin)->put(route('report-lists.illnesses.update', $headache), ['name' => 'Headache']);
        $this->assertFalse($headache->fresh()->is_active);

        // New visits no longer see it ...
        $this->get(route('document-issuances.create', ['document_type' => 'consultation_form']))->assertOk()
            ->assertDontSee('id="illness_' . $headache->id . '"', false);

        // ... the old visit still shows it ticked, so saving it does not drop the illness ...
        $edit = $this->get(route('document-issuances.edit', $visit))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/name="illness_ids\[\]"\s+value="' . $headache->id . '"[^>]*checked/', $edit);

        // ... and the report keeps its history (and the filter can still find it).
        $report = app(AccomplishmentReportBuilder::class)->build(ReportFilters::fromArray([]), $admin);
        $row = collect($report['illness_sections'])->pluck('groups')->flatten(1)->pluck('rows')->flatten(1)->firstWhere('name', 'Headache');
        $this->assertSame(1, $row['total']);
    }

    public function test_services_can_be_added_renamed_and_switched_off_and_automatic_ones_keep_their_names(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->post(route('report-lists.services.store'), ['category' => ServiceType::PROMOTION, 'name' => 'Dental referral'])->assertSessionDoesntHaveErrors();
        $dental = ServiceType::where('name', 'Dental referral')->firstOrFail();
        $this->assertSame([ServiceType::PROMOTION, null, true], [$dental->category, $dental->auto_rule, $dental->is_active]);
        $this->get(route('document-issuances.create', ['document_type' => 'consultation_form']))->assertOk()->assertSee('Dental referral');

        $this->post(route('report-lists.services.store'), ['category' => ServiceType::PROMOTION, 'name' => 'Dental referral'])->assertSessionHasErrors();
        $this->post(route('report-lists.services.store'), ['category' => 'bogus', 'name' => 'Whatever'])->assertSessionHasErrors();

        $this->put(route('report-lists.services.update', $dental), ['name' => 'Dental referral (CAF)', 'is_active' => '1']);
        $this->assertSame('Dental referral (CAF)', $dental->fresh()->name);

        $this->put(route('report-lists.services.update', $dental), ['name' => 'Dental referral (CAF)']);
        $this->assertFalse($dental->fresh()->is_active);

        $automatic = ServiceType::where('name', 'Medicine assistance')->firstOrFail();
        $this->put(route('report-lists.services.update', $automatic), ['name' => 'Renamed', 'is_active' => '1']);
        $this->assertSame('Medicine assistance', $automatic->fresh()->name);
        $this->assertSame('medicine_given', $automatic->fresh()->auto_rule);
    }
}
