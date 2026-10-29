<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\Campus;
use App\Models\College;
use App\Models\Course;
use App\Models\DispenseRecord;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineTransaction;
use App\Models\PatientType;
use App\Models\Role;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Meds Only (owner request 2026-10-29): a patient who only comes for medicine is recorded with the date, the name, the
 * course, year and college, and one or many drugs. It is saved as a dispense record, so the stock leaves through the
 * engine (FEFO, ledger) and every dispensing screen and report already knows it.
 */
class MedsOnlyModuleTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $nurse;

    private Medicine $paracetamol;

    private Medicine $cetirizine;

    private College $college;

    private Course $course;

    private YearLevel $year;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse']);
        $this->paracetamol = $this->makeMedicine('Paracetamol', ['dosage' => '500mg']);
        $this->cetirizine = $this->makeMedicine('Cetirizine', ['dosage' => '10mg']);
        $this->stockIn($this->paracetamol, 20, now()->addYear()->toDateString(), '500mg', 'PARA-1');
        $this->stockIn($this->cetirizine, 10, now()->addYear()->toDateString(), '10mg', 'CET-1');
        $this->college = College::create(['college_name' => 'College of Arts']);
        $this->course = Course::create(['course_name' => 'BS Biology']);
        $this->year = YearLevel::create(['year_level_name' => '2nd Year']);
    }

    /** A valid typed-name submission; $over replaces whole keys of it (a list such as `medicines` is replaced, not merged). */
    private function payload(array $over = []): array
    {
        return array_replace([
            'bill_date' => now()->toDateString(),
            'patient_name' => 'Juan Typed',
            'college_id' => $this->college->id,
            'course_id' => $this->course->id,
            'year_level_id' => $this->year->id,
            'patient_type_id' => PatientType::query()->value('id'),
            'note' => 'Headache and allergy',
            'medicines' => [
                ['medicine_id' => $this->paracetamol->id, 'dosage' => '500mg', 'quantity' => 4],
                ['medicine_id' => $this->cetirizine->id, 'dosage' => '10mg', 'quantity' => 2],
            ],
        ], $over);
    }

    private function stock(Medicine $medicine): int
    {
        return (int) MedicineBatch::where('medicine_id', $medicine->id)->sum('quantity');
    }

    private function record(): DispenseRecord
    {
        return DispenseRecord::medsOnly()->latest('id')->firstOrFail();
    }

    // ------------------------------------------------------------------ recording

    public function test_a_typed_name_with_two_drugs_is_recorded_and_the_stock_leaves_through_the_engine(): void
    {
        $this->actingAs($this->nurse)->post(route('staff.meds-only.store'), $this->payload())
            ->assertRedirect(route('staff.meds-only.index'));

        $record = $this->record();
        $this->assertTrue($record->meds_only);
        $this->assertNull($record->patient_id);
        $this->assertSame('Juan Typed', $record->patient_name);
        $this->assertSame('College of Arts', $record->college);
        $this->assertSame('BS Biology', $record->course);
        $this->assertSame('2nd Year', $record->year_level);
        $this->assertSame($this->college->id, (int) $record->college_id);
        $this->assertSame($this->nurse->id, (int) $record->recorded_by);
        $this->assertSame(now()->toDateString(), \Carbon\Carbon::parse($record->bill_date)->toDateString());
        $this->assertSame('Headache and allergy', $record->note);
        $this->assertStringStartsWith('HIS', $record->history_number);

        $this->assertSame([['Cetirizine', 2], ['Paracetamol', 4]], $record->dispenseItems()->with('medicine')->get()
            ->map(fn ($item) => [$item->medicine->name, (int) $item->quantity])->sortBy(0)->values()->all());
        $this->assertSame(16, $this->stock($this->paracetamol));
        $this->assertSame(8, $this->stock($this->cetirizine));
        $this->assertSame(2, MedicineTransaction::where('reference_type', DispenseRecord::class)->where('reference_id', $record->id)
            ->where('transaction_type', MedicineTransaction::TYPE_DISPENSE)->count(), 'one ledger line per drug');
        $this->assertSame('Meds Only', DB::table('dispense_history_view')->where('id', 'B'.$record->id)->value('source'));
        $this->assertSame(1, ActivityLog::where('action', 'meds_only_created')->where('subject_id', $record->id)->count());
    }

    public function test_a_registered_patient_gives_the_name_and_the_college_course_and_year_of_the_profile(): void
    {
        $patient = $this->makePatient(['first_name' => 'Rita', 'last_name' => 'Registered',
            'college_id' => $this->college->id, 'course_id' => $this->course->id, 'year_level_id' => $this->year->id]);

        $this->actingAs($this->nurse)->post(route('staff.meds-only.store'), [
            'bill_date' => now()->toDateString(), 'patient_id' => $patient->id,
            'medicines' => [['medicine_id' => $this->paracetamol->id, 'dosage' => '500mg', 'quantity' => 1]],
        ])->assertRedirect();

        $record = $this->record();
        $this->assertSame($patient->id, (int) $record->patient_id);
        $this->assertSame('Rita Registered', $record->patient_name, 'the name of the register, as of the day');
        $this->assertSame(['College of Arts', 'BS Biology', '2nd Year'], [$record->college, $record->course, $record->year_level]);
        $this->assertSame($patient->patient_type_id, $record->patient_type_id);
    }

    public function test_values_chosen_on_the_form_win_over_the_profile_of_a_registered_patient(): void
    {
        $other = College::create(['college_name' => 'College of Science']);
        $patient = $this->makePatient(['college_id' => $this->college->id]);

        $this->actingAs($this->nurse)->post(route('staff.meds-only.store'), [
            'bill_date' => now()->toDateString(), 'patient_id' => $patient->id, 'college_id' => $other->id,
            'medicines' => [['medicine_id' => $this->paracetamol->id, 'quantity' => 1]],
        ])->assertRedirect();

        $this->assertSame('College of Science', $this->record()->college);
    }

    public function test_a_drug_without_a_strength_takes_the_nearest_expiry_whatever_its_strength(): void
    {
        $this->actingAs($this->nurse)->post(route('staff.meds-only.store'), $this->payload([
            'medicines' => [['medicine_id' => $this->paracetamol->id, 'dosage' => '', 'quantity' => 3]],
        ]))->assertRedirect();

        $this->assertSame(17, $this->stock($this->paracetamol));
        $this->assertSame(1, $this->record()->dispenseItems()->count());
    }

    public function test_the_strength_that_was_chosen_is_the_one_that_leaves_even_when_another_strength_expires_first(): void
    {
        $this->stockIn($this->paracetamol, 10, now()->addMonths(2)->toDateString(), '250mg', 'PARA-250');

        $this->actingAs($this->nurse)->post(route('staff.meds-only.store'), $this->payload([
            'medicines' => [['medicine_id' => $this->paracetamol->id, 'dosage' => '500mg', 'quantity' => 3]],
        ]))->assertRedirect();

        $this->assertSame(17, (int) MedicineBatch::where('batch_number', 'PARA-1')->sum('quantity'));
        $this->assertSame(10, (int) MedicineBatch::where('batch_number', 'PARA-250')->sum('quantity'), 'the 250mg batch expires first but was not asked for');
        $this->assertSame(['500mg'], $this->record()->dispenseItems()->pluck('dosage')->all());
    }

    // ------------------------------------------------------------------ what is refused (and leaves nothing behind)

    public function test_nothing_is_saved_when_the_form_is_not_right(): void
    {
        $cases = [
            'no drug' => ['medicines' => []],
            'no person' => ['patient_name' => ''],
            'zero units' => ['medicines' => [['medicine_id' => $this->paracetamol->id, 'dosage' => '500mg', 'quantity' => 0]]],
            'a date far in the future' => ['bill_date' => now()->addYear()->toDateString()],
            'a date nobody wrote' => ['bill_date' => '2026-02-31'],
            'a college that does not exist' => ['college_id' => 99999],
            'an unknown medicine' => ['medicines' => [['medicine_id' => 99999, 'dosage' => '', 'quantity' => 1]]],
            'the same drug twice' => ['medicines' => [
                ['medicine_id' => $this->paracetamol->id, 'dosage' => '500mg', 'quantity' => 1],
                ['medicine_id' => $this->paracetamol->id, 'dosage' => '500MG', 'quantity' => 2],
            ]],
        ];

        foreach ($cases as $label => $over) {
            $payload = $this->payload();
            foreach ($over as $key => $value) {
                $payload[$key] = $value;
            }
            $this->actingAs($this->nurse)->post(route('staff.meds-only.store'), $payload)->assertSessionHasErrors();
            $this->assertSame(0, DispenseRecord::medsOnly()->count(), $label);
            $this->assertSame(20, $this->stock($this->paracetamol), "$label: no stock moved");
        }
    }

    public function test_a_later_drug_without_enough_stock_rolls_back_the_earlier_drugs_too(): void
    {
        $this->actingAs($this->nurse)->post(route('staff.meds-only.store'), $this->payload([
            'medicines' => [
                ['medicine_id' => $this->paracetamol->id, 'dosage' => '500mg', 'quantity' => 5],
                ['medicine_id' => $this->cetirizine->id, 'dosage' => '10mg', 'quantity' => 99],
            ],
        ]))->assertSessionHasErrors();

        $this->assertSame(0, DispenseRecord::medsOnly()->count());
        $this->assertSame(20, $this->stock($this->paracetamol), 'the first drug came back');
        $this->assertSame(10, $this->stock($this->cetirizine));
        $this->assertSame(0, MedicineTransaction::where('transaction_type', MedicineTransaction::TYPE_DISPENSE)->count());
    }

    public function test_an_archived_patient_cannot_be_chosen(): void
    {
        $patient = $this->makePatient();
        $patient->delete();

        $this->actingAs($this->nurse)->post(route('staff.meds-only.store'), $this->payload(['patient_id' => $patient->id]))->assertSessionHasErrors();

        $this->assertSame(0, DispenseRecord::medsOnly()->count());
    }

    // ------------------------------------------------------------------ editing and deleting give the stock back

    private function recordOne(array $over = []): DispenseRecord
    {
        $this->actingAs($this->nurse)->post(route('staff.meds-only.store'), $this->payload($over))->assertRedirect();

        return $this->record();
    }

    public function test_editing_changes_the_person_and_swaps_the_drugs_with_the_stock_following(): void
    {
        $record = $this->recordOne();
        $this->assertSame(16, $this->stock($this->paracetamol));

        $this->actingAs($this->nurse)->put(route('staff.meds-only.update', $record), $this->payload([
            'patient_name' => 'Juan Corrected',
            'medicines' => [['medicine_id' => $this->paracetamol->id, 'dosage' => '500mg', 'quantity' => 1]],
        ]))->assertRedirect(route('staff.meds-only.index'));

        $record->refresh();
        $this->assertSame('Juan Corrected', $record->patient_name);
        $this->assertSame(1, DispenseRecord::medsOnly()->count(), 'the same record, not a new one');
        $this->assertSame([[$this->paracetamol->id, 1]], $record->dispenseItems()->get()->map(fn ($item) => [(int) $item->medicine_id, (int) $item->quantity])->all());
        $this->assertSame(19, $this->stock($this->paracetamol), '4 came back, 1 went out');
        $this->assertSame(10, $this->stock($this->cetirizine), 'the removed drug came back');
        $this->assertSame(1, ActivityLog::where('action', 'meds_only_updated')->where('subject_id', $record->id)->count());
    }

    public function test_an_edit_that_fails_changes_nothing(): void
    {
        $record = $this->recordOne();

        $this->actingAs($this->nurse)->put(route('staff.meds-only.update', $record), $this->payload([
            'patient_name' => 'Should Not Stick',
            'medicines' => [['medicine_id' => $this->cetirizine->id, 'dosage' => '10mg', 'quantity' => 500]],
        ]))->assertSessionHasErrors();

        $record->refresh();
        $this->assertSame('Juan Typed', $record->patient_name);
        $this->assertSame(16, $this->stock($this->paracetamol));
        $this->assertSame(8, $this->stock($this->cetirizine));
        $this->assertSame(2, $record->dispenseItems()->count());
    }

    public function test_deleting_gives_every_unit_back_and_removes_the_record(): void
    {
        $record = $this->recordOne();

        $this->actingAs($this->nurse)->delete(route('staff.meds-only.destroy', $record))->assertRedirect(route('staff.meds-only.index'));

        $this->assertNull(DispenseRecord::find($record->id));
        $this->assertSame(0, DB::table('sale_medicines')->where('medicine_bill_id', $record->id)->count());
        $this->assertSame(20, $this->stock($this->paracetamol));
        $this->assertSame(10, $this->stock($this->cetirizine));
        $this->assertSame(1, ActivityLog::where('action', 'meds_only_deleted')->where('subject_id', $record->id)->count());
    }

    public function test_only_meds_only_records_can_be_opened_changed_or_deleted_here(): void
    {
        $manual = DispenseRecord::create(['history_number' => 'HIS1', 'patient_id' => $this->makePatient()->id, 'model_type' => DispenseRecord::class, 'bill_date' => now()]);

        $this->actingAs($this->nurse);
        $this->get(route('staff.meds-only.show', $manual))->assertNotFound();
        $this->get(route('staff.meds-only.edit', $manual))->assertNotFound();
        $this->put(route('staff.meds-only.update', $manual), $this->payload())->assertNotFound();
        $this->delete(route('staff.meds-only.destroy', $manual))->assertNotFound();
        $this->assertNotNull(DispenseRecord::find($manual->id));
    }

    /** The older Dispensing pages (`dispense-records/{id}`) know nothing of Meds Only: they would turn the record into one with a patient, delete it unlogged, or crash printing a record without a patient. */
    public function test_the_older_dispense_record_pages_never_open_change_delete_or_print_a_meds_only_record(): void
    {
        $record = $this->recordOne();
        $category = \App\Models\Category::create(['name' => 'Zzlegacy', 'is_active' => 1]);
        $before = [$this->stock($this->paracetamol), $this->stock($this->cetirizine)];

        $this->actingAs($this->makeAdmin());
        $this->get(route('dispense-records.show', $record))->assertRedirect(route('meds-only.show', $record));
        $this->get(route('dispense-records.edit', $record))->assertRedirect(route('meds-only.show', $record));
        $this->putJson(route('dispense-records.update', $record), [
            'patient_id' => $this->makePatient()->id, 'bill_date' => now()->toDateString(), 'note' => 'Hijack',
            'category_id' => [$category->id], 'medicine' => [$this->paracetamol->id], 'dosage' => ['500mg'], 'quantity' => [1],
        ])->assertStatus(422)->assertJsonPath('success', false);
        $this->deleteJson(route('dispense-records.destroy', $record))->assertStatus(422)->assertJsonPath('success', false);
        $this->get(route('dispense-records.pdf', $record->id))->assertNotFound();

        $this->actingAs($this->nurse);
        $this->get(route('staff.dispense-records.show', $record))->assertRedirect(route('staff.meds-only.show', $record));
        $this->actingAs($this->makeDoctor());
        $this->get(route('doctors.dispense-records.show', $record))->assertRedirect(route('doctors.meds-only.show', $record));

        $fresh = DispenseRecord::findOrFail($record->id);
        $this->assertTrue($fresh->meds_only);
        $this->assertNull($fresh->patient_id);
        $this->assertSame('Juan Typed', $fresh->patient_name);
        $this->assertSame('Headache and allergy', $fresh->note);
        $this->assertSame(2, $fresh->dispenseItems()->count());
        $this->assertSame($before, [$this->stock($this->paracetamol), $this->stock($this->cetirizine)], 'nothing moved in the stock');
    }

    // ------------------------------------------------------------------ who may, and the pages

    public function test_who_may_use_the_module(): void
    {
        $record = $this->recordOne();
        $admin = $this->makeAdmin();
        $doctor = $this->makeDoctor();

        $this->actingAs($admin)->get(route('meds-only.index'))->assertOk();
        $this->post(route('meds-only.store'), $this->payload(['patient_name' => 'By Admin']))->assertRedirect(route('meds-only.index'));
        $this->assertSame('By Admin', $this->record()->patient_name);

        // a doctor reads the list and a record, and cannot write
        $this->actingAs($doctor)->get(route('doctors.meds-only.index'))->assertOk()->assertSee('Juan Typed');
        $this->get(route('doctors.meds-only.show', $record))->assertOk();
        $this->post('/doctors/meds-only', $this->payload())->assertStatus(405);
        $this->put('/doctors/meds-only/'.$record->id, $this->payload())->assertStatus(405);
        $this->delete('/doctors/meds-only/'.$record->id)->assertStatus(405);
        $this->assertSame(2, DispenseRecord::medsOnly()->count());

        // a staff account whose role lost the medicines permission is refused everywhere
        Role::findByName('staff')->revokePermissionTo('manage_medicines');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->nurse->fresh());
        $this->get(route('staff.meds-only.index'))->assertForbidden();
        $this->post(route('staff.meds-only.store'), $this->payload())->assertForbidden();
        $this->get(route('staff.meds-only.patients', ['term' => 'x']))->assertForbidden();

        // and a stranger is sent to sign in
        auth()->logout();
        $this->get(route('meds-only.index'))->assertRedirect();
    }

    public function test_the_pages_open_and_show_what_was_recorded(): void
    {
        $record = $this->recordOne();
        $this->actingAs($this->nurse);

        $this->get(route('staff.meds-only.index'))->assertOk()->assertSee('Meds Only')->assertSee('Add Meds Only record');
        $this->get(route('staff.meds-only.create'))->assertOk()->assertSee('College of Arts')->assertSee('BS Biology')->assertSee('2nd Year')->assertSee('id="meds_only_config"', false)->assertSee('medicines-error', false);
        $this->get(route('staff.meds-only.show', $record))->assertOk()->assertSee('Juan Typed')->assertSee('College of Arts')
            ->assertSee('Paracetamol')->assertSee('Cetirizine')->assertSee('Headache and allergy');
        $this->get(route('staff.meds-only.edit', $record))->assertOk()->assertSee('value="Juan Typed"', false)->assertSee('"quantity":4', false)->assertSee('"dosage":"500mg"', false);
    }

    // ------------------------------------------------------------------ finding a registered patient

    public function test_the_patient_search_finds_registered_patients_by_words_and_never_the_archived_ones(): void
    {
        $this->makePatient(['first_name' => 'Maria', 'last_name' => 'Santos', 'university_id_number' => 'U-111', 'college_id' => $this->college->id]);
        $this->makePatient(['first_name' => 'Maria', 'last_name' => 'Reyes']);
        $gone = $this->makePatient(['first_name' => 'Maria', 'last_name' => 'Archived']);
        $gone->delete();

        $this->actingAs($this->nurse);
        $found = $this->getJson(route('staff.meds-only.patients', ['term' => 'maria']))->assertOk()->json('results');
        $this->assertCount(2, $found);
        $this->assertEqualsCanonicalizing(['Maria Reyes', 'Maria Santos'], array_map(fn ($row) => explode(' (', $row['text'])[0], $found));

        $santos = collect($this->getJson(route('staff.meds-only.patients', ['term' => 'maria santos']))->json('results'));
        $this->assertCount(1, $santos);
        $this->assertSame($this->college->id, $santos[0]['college_id']);
        $this->assertSame('U-111', $santos[0]['university_id']);

        $this->assertSame([], $this->getJson(route('staff.meds-only.patients', ['term' => '']))->json('results'), 'nothing for an empty search');
        $this->assertSame([], $this->getJson(route('staff.meds-only.patients', ['term' => 'zzzz']))->json('results'));
    }

    public function test_the_patient_search_returns_at_most_fifteen_people(): void
    {
        foreach (range(1, 17) as $n) {
            $this->makePatient(['first_name' => 'Crowd', 'last_name' => 'Member' . $n, 'email' => "crowd{$n}@example.test"]);
        }

        $this->actingAs($this->nurse);
        $this->assertCount(15, $this->getJson(route('staff.meds-only.patients', ['term' => 'crowd']))->assertOk()->json('results'));
    }
}
