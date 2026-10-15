<?php

namespace Tests\Feature\Regression;

use App\Models\DocumentIssuance;
use App\Models\Illness;
use App\Models\ServiceType;
use App\Models\User;
use App\Support\OtherName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan item 5.23 (owner request 2026-10-15): an "Others" line on the consultation form records what was actually
 * found / done. A ticked "Others" must carry a name (illnesses and services alike), the name is saved tidied up, shown
 * again on edit, and dropped when the line is un-ticked.
 */
class OtherNamesSaveTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function otherIllness(string $system = 'Skeletal System'): Illness
    {
        return Illness::where('is_other', true)->whereHas('system', fn ($q) => $q->where('name', $system))->firstOrFail();
    }

    private function otherService(string $category = 'clinical_procedure'): ServiceType
    {
        return ServiceType::where('is_other', true)->where('category', $category)->firstOrFail();
    }

    /** @return array<string, mixed> */
    private function payload(User $patientUser, User $nurse, array $extra = []): array
    {
        return array_merge([
            'document_type' => 'consultation_form', 'user_id' => $patientUser->id, 'name' => $patientUser->full_name,
            'date_of_birth' => '2003-05-01', 'gender' => 'Male', 'requested_at' => now()->toDateString(),
            'consult_mode' => 'physical', 'complaints' => 'Cough', 'nursing_incharged' => $nurse->id,
            'classification_submitted' => '1',
        ], $extra);
    }

    public function test_names_are_compared_without_case_extra_spaces_or_end_punctuation(): void
    {
        $this->assertSame('Dengue fever', OtherName::clean("  Dengue \t  fever  "));
        $this->assertNull(OtherName::clean('   '));
        $this->assertNull(OtherName::clean(null));

        foreach (['dengue', 'Dengue ', 'DENGUE.', ' dengue!! ', "\u{00A0}Dengue\u{00A0}"] as $typed) {
            $this->assertSame('dengue', OtherName::key($typed), $typed);
        }
        $this->assertSame(OtherName::key('aa'), OtherName::key('aA'));
        $this->assertSame(OtherName::key('dengue fever'), OtherName::key('Dengue   Fever.'));
        $this->assertNotSame(OtherName::key('dengue'), OtherName::key('dengue fever'));
        $this->assertSame(OtherName::key('é'), OtherName::key('É'), 'accented letters too');
        $this->assertSame('...', OtherName::key('...'), 'a name that is only punctuation is still a line of its own');
    }

    public function test_a_service_others_line_records_what_was_done_and_it_comes_back_on_the_edit_page(): void
    {
        $doctor = $this->makeDoctor();
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $patient = $this->makePatient();
        $others = $this->otherService();

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->payload($patient->user, $nurse, [
            'service_ids' => [$others->id, ServiceType::where('name', 'Tetanus injection')->value('id')],
            'service_other' => [$others->id => "  Ear   syringing \t", ServiceType::where('name', 'Tetanus injection')->value('id') => 'not an Others line'],
        ]))->assertRedirect()->assertSessionMissing('error')->assertSessionHasNoErrors();

        $document = DocumentIssuance::firstOrFail();
        $this->assertSame('Ear syringing', $document->services->firstWhere('id', $others->id)->pivot->other_text, 'trimmed, one space between words');
        $this->assertNull($document->services->firstWhere('name', 'Tetanus injection')->pivot->other_text);

        $edit = $this->get(route('doctors.document-issuances.edit', $document))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/name="service_other\[' . $others->id . '\]"[^>]*value="Ear syringing"/', $edit);

        $this->get(route('doctors.document-issuances.show', $document))->assertOk()->assertSee('Ear syringing');
        $this->assertContains('Others: Ear syringing', $document->fresh()->serviceLabels());
    }

    public function test_a_ticked_others_line_without_a_name_is_refused_and_nothing_is_saved(): void
    {
        $doctor = $this->makeDoctor();
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $patient = $this->makePatient();

        $cases = [
            'service, no box' => ['service_ids' => [$this->otherService()->id]],
            'service, empty' => ['service_ids' => [$this->otherService()->id], 'service_other' => [$this->otherService()->id => '']],
            'service, spaces only' => ['service_ids' => [$this->otherService('health_promotion')->id], 'service_other' => [$this->otherService('health_promotion')->id => "  \t "]],
            'illness, empty' => ['illness_ids' => [$this->otherIllness()->id], 'illness_other' => [$this->otherIllness()->id => '']],
            'illness, no box' => ['illness_ids' => [$this->otherIllness('EENT')->id]],
            'one named and one not' => [
                'illness_ids' => [$this->otherIllness()->id, $this->otherIllness('EENT')->id],
                'illness_other' => [$this->otherIllness()->id => 'Sprain'],
            ],
        ];

        foreach ($cases as $label => $extra) {
            $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->payload($patient->user, $nurse, $extra))
                ->assertSessionHasErrors();
            $this->assertSame(0, DocumentIssuance::count(), "{$label}: nothing may be saved");
        }

        // The message says which line needs the name.
        $response = $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->payload($patient->user, $nurse, [
            'service_ids' => [$this->otherService()->id],
        ]));
        $this->assertStringContainsString('Others', collect(session('errors')->all())->implode(' '));
    }

    public function test_a_name_longer_than_the_column_is_refused_for_services_too(): void
    {
        $doctor = $this->makeDoctor();
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $patient = $this->makePatient();
        $others = $this->otherService();

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->payload($patient->user, $nurse, [
            'service_ids' => [$others->id], 'service_other' => [$others->id => str_repeat('x', 151)],
        ]))->assertSessionHasErrors();
        $this->assertSame(0, DocumentIssuance::count());

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->payload($patient->user, $nurse, [
            'service_ids' => [$others->id], 'service_other' => [$others->id => str_repeat('x', 150)],
        ]))->assertSessionHasNoErrors();
        $this->assertSame(1, DocumentIssuance::count());
    }

    public function test_the_name_of_an_unticked_others_line_is_dropped_and_editing_replaces_it(): void
    {
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $patient = $this->makePatient();
        $illness = $this->otherIllness();
        $service = $this->otherService();
        $second = $this->otherService('health_promotion');

        $this->actingAs($nurse)->post(route('staff.document-issuances.store'), $this->payload($patient->user, $nurse, [
            'illness_ids' => [$illness->id], 'illness_other' => [$illness->id => 'Lump on right leg'],
            'service_ids' => [$service->id], 'service_other' => [$service->id => 'Ear syringing', $second->id => 'typed but not ticked'],
        ]))->assertSessionHasNoErrors();
        $document = DocumentIssuance::firstOrFail();

        $this->assertSame(1, $document->services()->count(), 'text of a line that is not ticked is not kept');
        $this->assertNull(\DB::table('consultation_services')->where('service_type_id', $second->id)->first());

        // Edit: another name replaces the first, and the illness name is tidied the same way.
        $this->put(route('staff.document-issuances.update', $document), [
            'name' => $patient->user->full_name, 'complaints' => 'Cough', 'requested_at' => now()->toDateString(),
            'nursing_incharged' => $nurse->id, 'nursing_intervention' => 'ok', 'classification_submitted' => '1',
            'illness_ids' => [$illness->id], 'illness_other' => [$illness->id => '  Sprain   of the  ankle '],
            'service_ids' => [$service->id], 'service_other' => [$service->id => 'Eye wash'],
        ])->assertSessionHasNoErrors();
        $document->refresh();
        $this->assertSame('Sprain of the ankle', $document->illnesses->first()->pivot->other_text);
        $this->assertSame('Eye wash', $document->services->first()->pivot->other_text);

        // Un-ticking the service clears its name; a refused edit changes nothing.
        $this->put(route('staff.document-issuances.update', $document), [
            'name' => $patient->user->full_name, 'complaints' => 'Cough', 'requested_at' => now()->toDateString(),
            'nursing_incharged' => $nurse->id, 'nursing_intervention' => 'ok', 'classification_submitted' => '1',
            'illness_ids' => [$illness->id], 'illness_other' => [$illness->id => 'Sprain of the ankle'],
        ])->assertSessionHasNoErrors();
        $this->assertSame(0, $document->services()->count());

        $this->put(route('staff.document-issuances.update', $document), [
            'name' => 'Changed Name', 'complaints' => 'Changed', 'requested_at' => now()->toDateString(),
            'nursing_incharged' => $nurse->id, 'nursing_intervention' => 'ok', 'classification_submitted' => '1',
            'illness_ids' => [$illness->id], 'illness_other' => [$illness->id => ''],
        ])->assertSessionHasErrors();
        $document->refresh();
        $this->assertSame('Sprain of the ankle', $document->illnesses->first()->pivot->other_text);
        $this->assertNotSame('Changed', $document->complaints, 'a refused edit saves nothing');
    }

    public function test_a_form_without_the_picks_does_not_trigger_the_rule(): void
    {
        $doctor = $this->makeDoctor();
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $patient = $this->makePatient();

        $payload = $this->payload($patient->user, $nurse);
        unset($payload['classification_submitted']);

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $payload)->assertSessionHasNoErrors();
        $this->assertSame(1, DocumentIssuance::count());
    }

    public function test_the_form_offers_a_name_box_for_every_others_line_and_asks_for_it_when_ticked(): void
    {
        $doctor = $this->makeDoctor();
        $html = $this->actingAs($doctor)->get(route('doctors.document-issuances.create', ['document_type' => 'consultation_form']))->assertOk()->getContent();

        foreach (ServiceType::where('is_other', true)->get() as $service) {
            $this->assertStringContainsString('name="service_other[' . $service->id . ']"', $html, "{$service->category}: Others has a box");
        }
        foreach (Illness::where('is_other', true)->get() as $illness) {
            $this->assertStringContainsString('name="illness_other[' . $illness->id . ']"', $html);
        }
        $this->assertStringContainsString('data-other-for', $html, 'the page script links each box to its checkbox');
        $this->assertStringContainsString("addEventListener('invalid'", $html, 'a hidden empty box is revealed instead of blocking the save silently');
    }
}
