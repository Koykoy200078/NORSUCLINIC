<?php

namespace Tests\Feature\Regression;

use App\Livewire\PrescriptionTable;
use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Phase 5, row 5.5 Prescriptions, Pass A: what the earlier prescription tests (PrescriptionControlTest,
 * PrescriptionStrengthsTest, PrescriptionInventoryTest) did not reach.
 */
class PrescriptionPassATest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $doctor;

    private Patient $patient;

    private Medicine $medicine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();

        $this->doctor = $this->makeDoctor();
        $this->patient = $this->makePatient();
        $this->medicine = $this->makeMedicine('Amoxicillin');
        $this->stockIn($this->medicine, 100, now()->addMonths(6)->toDateString());
    }

    private function prescription(string $status = Prescription::DISPENSE_STATUS_PENDING, array $attributes = []): Prescription
    {
        $prescription = Prescription::create(array_merge([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->doctor->id,
            'status' => $status,
            'is_active' => $status !== Prescription::DISPENSE_STATUS_CANCELLED,
            'consultation_date' => now()->toDateString(),
        ], $attributes));

        PrescriptionMedicine::create([
            'prescription_id' => $prescription->id, 'medicine' => $this->medicine->id, 'dosage' => '500mg',
            'route_of_administration' => 'oral', 'frequency' => 1, 'duration_value' => 5, 'duration_unit' => 'day', 'total_quantity' => 5,
        ]);

        return $prescription;
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = [], array $medicine = []): array
    {
        return array_merge([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->doctor->id,
            'consultation_date' => now()->toDateString(),
            'medicines' => [array_merge([
                'medicine_id' => $this->medicine->id, 'dosage' => '500mg', 'route_of_administration' => 'oral',
                'frequency' => 2, 'duration_value' => 3, 'duration_unit' => 'day', 'total_quantity' => 6, 'instructions' => 'after meals',
            ], $medicine)],
        ], $overrides);
    }

    private function grant(string $role, array $permissions): void
    {
        Role::findByName($role)->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    // ------------------------------------------------------------------ validation matches the columns

    public function test_the_form_refuses_a_visit_date_and_numbers_the_database_would_not_take(): void
    {
        $this->actingAs($this->doctor);

        foreach (['1066-10-14', now()->addYears(3)->toDateString(), 'tomorrow-ish', '2026-02-30', '05/10/2026'] as $date) {
            $this->post(route('doctors.prescriptions.store'), $this->payload(['consultation_date' => $date]))->assertSessionHasErrors();
            $this->assertStringContainsString('consultation date', implode(' ', session('errors')->getBag('default')->all()), $date);
            session()->flush();
        }
        $this->post(route('doctors.prescriptions.store'), $this->payload([], ['total_quantity' => 'abc']))->assertSessionHasErrors();
        $this->assertSame(0, Prescription::count(), 'nothing was saved by a refused form');

        // an ordinary date is still accepted, today and a past visit
        $this->post(route('doctors.prescriptions.store'), $this->payload())->assertSessionHasNoErrors();
        $this->post(route('doctors.prescriptions.store'), $this->payload(['consultation_date' => now()->subMonths(2)->toDateString(), 'medicines' => [[
            'medicine_id' => $this->medicine->id, 'dosage' => '250mg', 'route_of_administration' => 'oral', 'frequency' => 1, 'duration_value' => 1, 'duration_unit' => 'day', 'total_quantity' => 1,
        ]]]))->assertSessionHasNoErrors();
        $this->assertSame(2, Prescription::count());
    }

    public function test_an_edit_checks_the_same_values_as_a_new_prescription(): void
    {
        $prescription = $this->prescription();

        $this->actingAs($this->doctor)
            ->put(route('doctors.prescriptions.update', $prescription), $this->payload(['consultation_date' => '1066-10-14']))
            ->assertSessionHasErrors();
        $this->assertSame(now()->toDateString(), substr((string) $prescription->fresh()->consultation_date, 0, 10));
    }

    // ------------------------------------------------------------------ authorisation

    public function test_creating_a_catalog_medicine_from_the_prescription_pages_needs_the_medicines_permission(): void
    {
        $payload = [
            'brand_name' => 'Zzbrand', 'generic_name' => 'Zzgeneric', 'category' => 'Zzcategory', 'dosage' => '500mg', 'uom' => 'tablet',
            'reorder_level' => 5, 'initial_stock_quantity' => 10, 'batch_number' => 'B-1', 'expiration_date' => now()->addYear()->toDateString(),
        ];
        $nurse = $this->makeNurse();

        $this->grant('staff', ['manage_patients', 'manage_request_documents']);
        $this->actingAs($nurse)->postJson(route('staff.prescription.medicine.store'), $payload)->assertForbidden();
        $this->assertSame(0, Medicine::where('brand_name', 'Zzbrand')->count(), 'no medicine and no stock were created');

        $this->grant('staff', ['manage_patients', 'manage_request_documents', 'manage_medicines']);
        $this->actingAs($nurse)->postJson(route('staff.prescription.medicine.store'), $payload)->assertStatus(200);
        $this->assertSame(1, Medicine::where('brand_name', 'Zzbrand')->count());

        // the form sends an unfilled reorder level as a blank: that is 0, not a database error
        $this->actingAs($nurse)->postJson(route('staff.prescription.medicine.store'), array_merge($payload, ['brand_name' => 'Zzbrand2', 'reorder_level' => '']))->assertStatus(200);
        $this->assertSame(0, (int) Medicine::where('brand_name', 'Zzbrand2')->value('reorder_level'));
    }

    public function test_the_list_offers_only_the_buttons_the_role_may_use_on_that_row(): void
    {
        $pending = $this->prescription();
        $dispensed = $this->prescription(Prescription::DISPENSE_STATUS_DISPENSED);
        $button = fn (string $html, Prescription $row, string $needle) => str_contains($this->rowHtml($html, $row), $needle);

        // a doctor: everything on an open prescription, nothing that would be refused on a closed one
        $html = Livewire::actingAs($this->doctor)->test(PrescriptionTable::class)->html();
        $this->assertTrue($button($html, $pending, 'delete-prescription-btn'));
        $this->assertTrue($button($html, $pending, '/edit'));
        $this->assertFalse($button($html, $dispensed, 'delete-prescription-btn'), 'a dispensed prescription cannot be deleted');
        $this->assertFalse($button($html, $dispensed, '/edit'), 'a dispensed prescription cannot be edited');
        $this->assertTrue($button($html, $dispensed, 'prescription-pdf'), 'but it can still be printed');

        // staff: edit (with a verbal order) and cancel, never delete
        $html = Livewire::actingAs($this->makeNurse())->test(PrescriptionTable::class)->html();
        $this->assertTrue($button($html, $pending, '/edit'));
        $this->assertFalse($button($html, $pending, 'delete-prescription-btn'), 'staff cannot delete a prescription: the button must not be drawn');

        // the administrator keeps the delete button on an open prescription
        $html = Livewire::actingAs($this->makeAdmin())->test(PrescriptionTable::class)->html();
        $this->assertTrue($button($html, $pending, 'delete-prescription-btn'));
    }

    /** The part of the table html that belongs to one prescription row (its action buttons carry its id). */
    private function rowHtml(string $html, Prescription $row): string
    {
        $parts = preg_split('/<tr\b/i', $html);
        foreach ($parts as $part) {
            if (str_contains($part, 'data-id="' . $row->id . '"') || str_contains($part, '/' . $row->id . '/edit') || str_contains($part, 'prescription-pdf/' . $row->id)) {
                return $part;
            }
        }

        return '';
    }

    // ------------------------------------------------------------------ pages with missing or awkward data

    public function test_a_missing_prescription_is_a_404_not_a_server_error_on_every_page(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->get(route('prescriptions.pdf', 987654))->assertNotFound();
        $this->get(route('prescription.medicine.show', 987654))->assertNotFound();
    }

    public function test_markup_in_a_prescription_is_shown_as_text_on_every_page(): void
    {
        $prescription = $this->prescription(Prescription::DISPENSE_STATUS_PENDING, [
            'problem_description' => '<script>alert(1)</script>Zzproblem', 'advice' => '<img src=x onerror=alert(2)>Zzadvice',
        ]);
        $prescription->getMedicine()->update(['instructions' => '<b onmouseover=alert(3)>Zzinstr</b>']);

        $this->actingAs($this->doctor);
        foreach ([route('doctors.prescription.medicine.show', $prescription->id), route('doctors.prescriptions.edit', $prescription->id)] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringNotContainsString('<script>alert(1)</script>', $html, $url);
            $this->assertStringNotContainsString('<img src=x onerror', $html, $url);
            $this->assertStringNotContainsString('<b onmouseover', $html, $url);
        }
        $this->assertStringNotContainsString('<script>alert(1)</script>', Livewire::test(PrescriptionTable::class)->html());
    }

    // ------------------------------------------------------------------ the patient's record

    public function test_a_prescription_cannot_be_started_for_an_archived_patient(): void
    {
        $this->patient->delete(); // archives (archived_at)

        $this->actingAs($this->doctor);
        $this->get(route('doctors.prescriptions.create', $this->patient->id))->assertNotFound();
        $this->post(route('doctors.prescriptions.store'), $this->payload())->assertNotFound();
        $this->assertSame(0, Prescription::count());
    }

    public function test_the_show_edit_and_print_pages_do_not_query_once_per_medicine(): void
    {
        $prescription = $this->prescription();
        $add = function (int $i) use ($prescription) {
            $medicine = $this->makeMedicine('Rxmed ' . $i);
            PrescriptionMedicine::create([
                'prescription_id' => $prescription->id, 'medicine' => $medicine->id, 'dosage' => '500mg', 'route_of_administration' => 'oral',
                'frequency' => 1, 'duration_value' => 5, 'duration_unit' => 'day', 'total_quantity' => 5,
            ]);
        };
        $queries = function (string $route) use ($prescription) {
            $this->actingAs($this->doctor)->get(route($route, $prescription->id))->assertOk(); // warms the permission and settings caches
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($this->doctor)->get(route($route, $prescription->id))->assertOk();
            $count = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $count;
        };
        $routes = ['doctors.prescription.medicine.show', 'doctors.prescriptions.edit', 'doctors.prescriptions.pdf'];

        foreach (range(1, 2) as $i) {
            $add($i);
        }
        $few = array_combine($routes, array_map($queries, $routes));
        foreach (range(3, 10) as $i) {
            $add($i);
        }
        foreach ($routes as $route) {
            $this->assertSame($few[$route], $queries($route), "{$route}: queries must not grow with the number of medicines");
        }
    }

    public function test_a_prescription_that_fails_half_way_is_saved_not_at_all_and_an_edit_that_fails_changes_nothing(): void
    {
        $second = $this->makeMedicine('Secondmed');
        $rows = [
            ['medicine_id' => $this->medicine->id, 'dosage' => '500mg', 'route_of_administration' => 'oral', 'frequency' => 1, 'duration_value' => 1, 'duration_unit' => 'day', 'total_quantity' => 1],
            ['medicine_id' => $second->id, 'dosage' => '500mg', 'route_of_administration' => 'oral', 'frequency' => 1, 'duration_value' => 1, 'duration_unit' => 'day', 'total_quantity' => 1],
        ];
        $existing = $this->prescription();
        $before = [Prescription::count(), PrescriptionMedicine::count(), DispenseRecord::count(), DispenseRecordItem::count()];

        // the second line of the dispense record cannot be written
        $lines = 0;
        Event::listen('eloquent.creating: ' . DispenseRecordItem::class, function () use (&$lines) {
            if (++$lines === 2) {
                throw new \RuntimeException('forced failure on the second line');
            }
        });

        $this->actingAs($this->doctor)->post(route('doctors.prescriptions.store'), $this->payload(['medicines' => $rows]))->assertRedirect();
        $this->assertSame($before, [Prescription::count(), PrescriptionMedicine::count(), DispenseRecord::count(), DispenseRecordItem::count()], 'a new prescription is saved whole or not at all');

        $lines = 1; // the next line written is the second one again
        $this->put(route('doctors.prescriptions.update', $existing), $this->payload(['medicines' => $rows]))->assertRedirect();
        $this->assertSame($before, [Prescription::count(), PrescriptionMedicine::count(), DispenseRecord::count(), DispenseRecordItem::count()], 'an edit that fails changes nothing');
        $this->assertSame(5, (int) $existing->fresh()->getMedicine()->firstOrFail()->total_quantity, 'the old medicine line is still there');
    }
}
