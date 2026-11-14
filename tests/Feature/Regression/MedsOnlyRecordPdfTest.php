<?php

namespace Tests\Feature\Regression;

use App\Models\DispenseRecord;
use App\Models\Medicine;
use App\Models\Role;
use App\Models\User;
use App\Support\Letterhead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * The owner's decision of 2026-11-14 (Meds Only / Phase 11 default 4): each Meds Only record has its own printable PDF (only the
 * list had PDF / Excel / CSV): the letterhead, who got the medicine, the drugs and who gave them. Every panel that may open the
 * record gets it; a manual dispense record or a prescription's record is not a Meds Only record and has none.
 */
class MedsOnlyRecordPdfTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $nurse;

    private Medicine $paracetamol;

    private DispenseRecord $record;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse']);
        $this->paracetamol = $this->makeMedicine('Paracetamol', ['dosage' => '500mg']);
        $this->stockIn($this->paracetamol, 20, now()->addYear()->toDateString(), '500mg', 'PARA-1');

        $this->actingAs($this->nurse)->post(route('staff.meds-only.store'), [
            'bill_date' => now()->toDateString(), 'patient_name' => "Juan D'Typed", 'note' => 'Headache',
            'medicines' => [['medicine_id' => $this->paracetamol->id, 'dosage' => '500mg', 'quantity' => 4]],
        ])->assertRedirect();
        $this->record = DispenseRecord::medsOnly()->latest('id')->firstOrFail();
    }

    public function test_every_panel_that_opens_the_record_gets_its_pdf(): void
    {
        foreach ([[$this->nurse, 'staff.meds-only.pdf'], [$this->makeAdmin(), 'meds-only.pdf'], [$this->makeDoctor(), 'doctors.meds-only.pdf']] as [$user, $route]) {
            $response = $this->actingAs($user)->get(route($route, $this->record))->assertOk();
            $this->assertSame('application/pdf', $response->headers->get('Content-Type'), $route);
            $this->assertStringStartsWith('%PDF', $response->getContent(), $route);
        }
    }

    public function test_the_pdf_shows_the_letterhead_the_person_the_drugs_and_who_gave_them(): void
    {
        $html = view('meds_only.pdf', ['record' => $this->record->load(['dispenseItems.medicine', 'recorder', 'patientType']), 'generatedAt' => now()])->render();

        foreach ([Letterhead::UNIVERSITY, Letterhead::ADDRESS, 'MEDS ONLY RECORD', 'Juan D&#039;Typed', 'Paracetamol', '500mg', 'Nora Nurse', $this->record->history_number, 'Headache'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertMatchesRegularExpression('~<td[^>]*>\s*4\s*</td>~', $html, 'the quantity');
    }

    public function test_the_record_page_and_the_list_offer_it(): void
    {
        $this->get(route('staff.meds-only.show', $this->record))->assertOk()->assertSee(route('staff.meds-only.pdf', $this->record));
        $this->assertStringContainsString(route('staff.meds-only.pdf', $this->record), view('meds_only.columns.action', ['row' => $this->record])->render());
    }

    public function test_another_dispense_record_has_none_and_without_the_permission_nobody_gets_it(): void
    {
        $manual = DispenseRecord::create(['history_number' => 'HIS-MANUAL', 'patient_id' => $this->makePatient()->id, 'model_type' => DispenseRecord::class, 'bill_date' => now()]);
        $this->get(route('staff.meds-only.pdf', $manual))->assertNotFound();

        Role::findByName('staff')->revokePermissionTo('manage_medicines');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->nurse->fresh())->get(route('staff.meds-only.pdf', $this->record))->assertForbidden();
    }
}
