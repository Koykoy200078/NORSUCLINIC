<?php

namespace Tests\Feature\Regression;

use App\Models\DocumentIssuance;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

class RowActionsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    public function test_menu_has_a_named_button_and_keeps_action_hooks_and_text(): void
    {
        $this->actingAs($this->makeNurse());
        $html = Blade::render('<x-row-actions module="patients"><a href="/view" class="dropdown-item sample-view" data-id="42">View</a></x-row-actions>');

        $this->assertStringContainsString('data-row-actions-toggle', $html);
        $this->assertStringContainsString('aria-label="Row actions"', $html);
        $this->assertStringContainsString('aria-expanded="false"', $html);
        $this->assertStringContainsString('role="menu"', $html);
        $this->assertStringContainsString('sample-view', $html);
        $this->assertStringContainsString('data-id="42"', $html);
        $this->assertStringContainsString('>View</a>', $html);
    }

    public function test_a_revoked_module_draws_no_menu_or_actions(): void
    {
        $nurse = $this->makeNurse();
        Role::findByName('staff')->revokePermissionTo('manage_patients');
        $this->actingAs($nurse);
        $html = Blade::render('<x-row-actions module="patients"><a href="/private">View</a></x-row-actions>');

        $this->assertStringNotContainsString('data-row-actions-toggle', $html);
        $this->assertStringNotContainsString('/private', $html);
    }

    public function test_a_slot_with_only_comments_does_not_draw_an_empty_menu(): void
    {
        $this->actingAs($this->makeNurse());
        $html = Blade::render('<x-row-actions module="patients"><!-- no actions --></x-row-actions>');
        $this->assertStringNotContainsString('data-row-actions-toggle', $html);
    }

    public function test_doctor_patient_actions_keep_edit_and_hide_archive_and_restore(): void
    {
        $this->actingAs($this->makeDoctor());
        $patient = $this->makePatient();
        $html = view('patients.components.action', ['row' => $patient])->render();

        $this->assertStringContainsString('data-row-actions-toggle', $html);
        $this->assertStringContainsString(getRouteByRole('patients.edit', [$patient]), $html);
        $this->assertStringNotContainsString('patient-delete-btn', $html);
        $this->assertStringNotContainsString('patient-restore-btn', $html);
    }

    public function test_prescriptions_only_patient_menu_keeps_only_the_prescription_entry(): void
    {
        $this->actingAs($this->makeNurse());
        Role::findByName('staff')->revokePermissionTo('manage_patients');
        $patient = $this->makePatient();
        $html = view('patients.components.action', ['row' => $patient])->render();

        $this->assertStringContainsString(getRouteByRole('prescriptions.create', [$patient]), $html);
        $this->assertStringNotContainsString(getRouteByRole('patients.edit', [$patient]), $html);
        $this->assertStringNotContainsString('patient-delete-btn', $html);
        $this->assertStringNotContainsString('patient-restore-btn', $html);
    }

    public function test_archived_patient_actions_offer_restore_without_edit_or_archive(): void
    {
        $this->actingAs($this->makeNurse());
        $patient = $this->makePatient();
        $patient->delete();
        $html = view('patients.components.action', ['row' => $patient])->render();

        $this->assertStringContainsString('data-row-actions-toggle', $html);
        $this->assertStringContainsString('patient-restore-btn', $html);
        $this->assertStringNotContainsString('patient-delete-btn', $html);
        $this->assertStringNotContainsString(getRouteByRole('patients.edit', [$patient]), $html);
    }

    public function test_dispensed_prescription_menu_draws_only_view_and_pdf(): void
    {
        $doctor = $this->makeDoctor();
        $this->actingAs($doctor);
        $row = Prescription::create(['patient_id' => $this->makePatient()->id, 'doctor_id' => $doctor->doctor->id,
            'status' => Prescription::DISPENSE_STATUS_DISPENSED, 'is_active' => 1]);
        $html = view('prescriptions.action', compact('row'))->render();

        $this->assertStringContainsString('data-row-actions-toggle', $html);
        $this->assertStringContainsString(getRouteByRole('prescriptions.pdf', [$row->id]), $html);
        $this->assertStringNotContainsString('delete-prescription-btn', $html);
        $this->assertStringNotContainsString(getRouteByRole('prescriptions.edit', [$row]), $html);
        $this->assertStringNotContainsString(getRouteByRole('prescription.status', [$row]), $html);
    }

    public function test_doctor_menu_keeps_hooks_and_places_delete_after_other_actions(): void
    {
        $this->actingAs($this->makeAdmin());
        $doctor = $this->makeDoctor();
        $html = view('doctors.components.action', ['row' => $doctor->doctor])->render();

        $this->assertStringContainsString('add-qualification', $html);
        $this->assertStringContainsString('doctor-edit-btn', $html);
        $this->assertStringContainsString('data-reset-url=', $html);
        $this->assertStringContainsString('data-delete-url=', $html);
        $this->assertLessThan(strpos($html, 'doctor-delete-btn'), strpos($html, 'doctor-reset-password-btn'));
    }

    public function test_consultation_history_menu_places_delete_after_pdf(): void
    {
        $admin = $this->makeAdmin();
        $patient = $this->makePatient();
        $visit = DocumentIssuance::create(['document_type' => 'consultation_form', 'document_creator_id' => $admin->id,
            'user_id' => $patient->user_id, 'name' => 'Menu Patient', 'age' => 23, 'gender' => 'Male',
            'address' => 'Test Address', 'requested_at' => now()->toDateString()]);
        $html = $this->actingAs($admin)->get(route('patients.showMyHistory', $patient))->assertOk()->getContent();
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $menu = $xpath->query('//form[@action="'.route('document-issuances.destroy', $visit).'"]/ancestor::div[contains(@class,"dropdown-menu")]')->item(0);

        $this->assertNotNull($menu);
        $items = $xpath->query('.//*[@role="menuitem"]', $menu);
        $this->assertCount(3, $items);
        $this->assertSame('PDF', $items->item(1)->getAttribute('title'));
        $this->assertSame('Delete', $items->item(2)->getAttribute('title'));
    }

    public function test_medicine_history_hides_delete_but_unused_medicines_keep_it(): void
    {
        $this->actingAs($this->makeAdmin());
        $unused = $this->makeMedicine('Unused Menu Medicine');
        $stocked = $this->makeMedicine('Stocked Menu Medicine');
        $this->stockIn($stocked, 2, now()->addYear()->toDateString());

        $this->assertStringContainsString('deleteMedicineBtn', view('medicines.action', ['row' => $unused])->render());
        $html = view('medicines.action', ['row' => $stocked])->render();
        $this->assertStringNotContainsString('deleteMedicineBtn', $html);
        $this->assertStringContainsString(getRouteByRole('medicines.edit', [$stocked]), $html);
    }

    public function test_loaded_medicine_history_flags_need_no_per_row_queries(): void
    {
        $this->makeMedicine('Unused Flags Medicine');
        $stocked = $this->makeMedicine('Stocked Flags Medicine');
        $this->stockIn($stocked, 2, now()->addYear()->toDateString());
        $rows = (new \App\Livewire\MedicineTable())->builder()->get();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $flags = $rows->mapWithKeys(fn (Medicine $medicine) => [$medicine->name => $medicine->hasHistory()]);

        $this->assertFalse($flags['Unused Flags Medicine']);
        $this->assertTrue($flags['Stocked Flags Medicine']);
        $this->assertCount(0, DB::getQueryLog());
        DB::disableQueryLog();
    }
}
