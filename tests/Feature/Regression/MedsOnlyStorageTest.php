<?php

namespace Tests\Feature\Regression;

use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\MedicineTransaction;
use App\Services\MedicineInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Meds Only storage (2026-10-29): a dispense record can belong to a person who is not registered, carries the college /
 * course / year of the day and the author, and the Dispense History and Stock Out views name it. Every record that existed
 * before reads exactly as it did.
 */
class MedsOnlyStorageTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    /** A dispense record with one line that really left the stock, as the application writes it. */
    private function record(array $attributes, int $quantity = 3, ?int $userId = null): DispenseRecord
    {
        $medicine = $this->makeMedicine('Paracetamol '.uniqid());
        $this->stockIn($medicine, 50, now()->addYear()->toDateString(), '500mg', 'LOT-'.uniqid());
        $record = DispenseRecord::create($attributes + ['history_number' => 'HIS'.random_int(100000, 999999), 'model_type' => DispenseRecord::class, 'bill_date' => now()]);
        app(MedicineInventoryService::class)->deductStockFefo($medicine->id, $quantity, $userId, $record, 'test', MedicineTransaction::TYPE_DISPENSE, '500mg');
        DispenseRecordItem::create(['dispense_id' => $record->id, 'medicine_id' => $medicine->id, 'dosage' => '500mg', 'expires_at' => null, 'quantity' => $quantity]);

        return $record;
    }

    private function history(DispenseRecord $record): object
    {
        return DB::table('dispense_history_view')->where('id', 'B'.$record->id)->first();
    }

    private function stockOut(DispenseRecord $record): object
    {
        // each record here has its own medicine, so the medicine finds the line of this record
        return DB::table('used_medicines_view')->where('medicine_id', $record->dispenseItems()->value('medicine_id'))->where('quantity', '>', 0)->first();
    }

    public function test_the_new_columns_exist_and_a_record_may_have_no_patient(): void
    {
        foreach (['meds_only', 'patient_name', 'campus_id', 'college_id', 'course_id', 'year_level_id', 'patient_type_id', 'campus', 'college', 'course', 'year_level', 'recorded_by'] as $column) {
            $this->assertTrue(Schema::hasColumn('medicine_bills', $column), $column);
        }
        $this->assertSame('YES', collect(DB::select('SHOW COLUMNS FROM medicine_bills'))->firstWhere('Field', 'patient_id')->Null);

        $legacy = $this->record(['patient_id' => $this->makePatient()->id]);
        $this->assertFalse($legacy->fresh()->meds_only, 'every record that exists today is not a Meds Only record');
        $this->assertSame(0, DispenseRecord::medsOnly()->count());
    }

    public function test_an_older_dispense_record_and_its_stock_out_line_read_exactly_as_before(): void
    {
        $patient = $this->makePatient(['first_name' => 'Legacy', 'last_name' => 'Person']);
        $record = $this->record(['patient_id' => $patient->id]);

        $history = $this->history($record);
        $this->assertSame('Dispense Record', $history->source);
        $this->assertSame('Legacy Person', $history->patient_name);
        $this->assertSame($patient->id, (int) $history->patient_id);
        $this->assertNull($history->given_by, 'a manual dispense record has no author');
        $this->assertSame(3, (int) $history->quantity);

        $out = $this->stockOut($record);
        $this->assertSame('Dispense Record', $out->source);
        $this->assertSame('Legacy Person', $out->patient_name);
    }

    public function test_a_meds_only_record_of_a_typed_name_is_named_in_dispense_history_and_stock_out(): void
    {
        $nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse']);
        $record = $this->record([
            'meds_only' => true, 'patient_id' => null, 'patient_name' => 'Juan Typed', 'recorded_by' => $nurse->id,
            'college' => 'College of Law (COL)', 'course' => 'BS Law', 'year_level' => '3rd Year',
        ], 4, $nurse->id);

        $history = $this->history($record);
        $this->assertSame('Meds Only', $history->source);
        $this->assertSame('Juan Typed', $history->patient_name);
        $this->assertNull($history->patient_id, 'a typed name has no patient row');
        $this->assertSame('Nora Nurse', $history->given_by);
        $this->assertSame($nurse->email, $history->given_by_email);
        $this->assertSame(4, (int) $history->quantity);

        $out = $this->stockOut($record);
        $this->assertSame('Meds Only', $out->source);
        $this->assertSame('Juan Typed', $out->patient_name);
        $this->assertSame('Nora Nurse', $out->nurse_incharged);
    }

    public function test_a_registered_patient_is_named_as_registered_and_a_return_is_marked_as_one(): void
    {
        $patient = $this->makePatient(['first_name' => 'Rita', 'last_name' => 'Registered']);
        $nurse = $this->makeNurse();
        $record = $this->record(['meds_only' => true, 'patient_id' => $patient->id, 'patient_name' => 'Old Spelling', 'recorded_by' => $nurse->id], 5, $nurse->id);

        $this->assertSame('Rita Registered', $this->history($record)->patient_name, 'the register wins over the typed copy');

        $item = $record->dispenseItems()->first();
        app(MedicineInventoryService::class)->restoreStock((int) $item->medicine_id, 5, $nurse->id, $record, 'returned', '500mg', null);

        $returned = DB::table('used_medicines_view')->where('source', 'Meds Only (returned)')->first();
        $this->assertNotNull($returned);
        $this->assertSame(-5, (int) $returned->quantity);
        $this->assertSame('Rita Registered', $returned->patient_name);
    }

    public function test_the_author_of_a_meds_only_record_without_a_doctor_is_shown_and_a_doctors_record_keeps_its_doctor(): void
    {
        $doctor = $this->makeDoctor(['first_name' => 'Dora', 'last_name' => 'Doctor']);
        $record = $this->record(['patient_id' => $this->makePatient()->id, 'doctor_id' => $doctor->doctor->id]);

        $this->assertSame('Dora Doctor', $this->history($record)->given_by);
        $this->assertSame($doctor->doctor->id, (int) $this->history($record)->doctor_id);
    }
}
