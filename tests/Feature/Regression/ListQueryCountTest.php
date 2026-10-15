<?php

namespace Tests\Feature\Regression;

use App\Livewire\DoctorTable;
use App\Livewire\DocumentIssuanceTable;
use App\Livewire\MedicineTable;
use App\Livewire\PatientTable;
use App\Livewire\LabRequestTable;
use App\Livewire\PrescriptionTable;
use App\Livewire\StaffTable;
use App\Models\DocumentIssuance;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\Prescription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 5, Pass A item 6 (N+1): the number of queries a list page makes must not grow with the number of rows it
 * shows. Each list is rendered with 3 rows and with 10 rows (one full page) and the two counts must be equal.
 */
class ListQueryCountTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->actingAs($this->makeAdmin());
    }

    private function queriesToShow(string $table): int
    {
        $component = Livewire::withoutLazyLoading()->test($table);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $component->call('$refresh');
        $component->instance()->getRows();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    /** @param callable(int): void $addRow */
    private function assertQueriesDoNotGrow(string $table, int $existing, callable $addRow): void
    {
        for ($i = 0; $i < 3 - $existing; $i++) {
            $addRow($i);
        }
        $few = $this->queriesToShow($table);

        for ($i = 3; $i < 10; $i++) {
            $addRow($i);
        }
        $many = $this->queriesToShow($table);

        $this->assertSame($few, $many, class_basename($table) . ": {$few} queries for 3 rows but {$many} for 10 rows (one query per row?)");
    }

    public function test_the_patient_list_does_not_query_once_per_row(): void
    {
        $this->assertQueriesDoNotGrow(PatientTable::class, 0, fn ($i) => $this->makePatient());
    }

    public function test_the_medicine_list_does_not_query_once_per_row(): void
    {
        $this->assertQueriesDoNotGrow(MedicineTable::class, 0, function ($i) {
            $medicine = $this->makeMedicine('Med ' . $i);
            $this->stockIn($medicine, 5, now()->addYear()->toDateString(), '500mg', 'B' . $i);
        });
    }

    public function test_the_consultation_list_does_not_query_once_per_row(): void
    {
        $nurse = $this->makeNurse();
        $patient = $this->makePatient();
        $this->assertQueriesDoNotGrow(DocumentIssuanceTable::class, 0, fn ($i) => DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $nurse->id, 'user_id' => $patient->user_id,
            'name' => 'Juan ' . $i, 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete',
        ]));
    }

    public function test_the_doctor_and_staff_lists_do_not_query_once_per_row(): void
    {
        $this->assertQueriesDoNotGrow(DoctorTable::class, 0, fn ($i) => $this->makeDoctor());
        $this->assertQueriesDoNotGrow(StaffTable::class, 1, fn ($i) => $this->makeNurse());
    }

    public function test_the_prescription_and_lab_request_lists_do_not_query_once_per_row(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();
        $this->assertQueriesDoNotGrow(PrescriptionTable::class, 0, fn ($i) => Prescription::create([
            'patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id, 'status' => 'pending', 'is_active' => 1,
        ]));

        $this->assertQueriesDoNotGrow(LabRequestTable::class, 0, function ($i) use ($patient) {
            $request = LabRequest::create([
                'request_number' => 'LR' . (100000 + $i), 'document_creator_id' => auth()->id(), 'patient_user_id' => $patient->user_id,
                'patient_name' => 'Juan ' . $i, 'requested_at' => now()->toDateString(), 'status' => LabRequest::STATUS_PENDING,
            ]);
            LabRequestItem::create(['lab_request_id' => $request->id, 'lab_test_id' => null, 'test_name' => 'Test ' . $i, 'test_category' => 'Other', 'result_status' => LabRequestItem::RESULT_PENDING]);
        });
    }

    public function test_the_earliest_expiry_is_the_same_whether_or_not_the_batches_were_loaded_with_the_list(): void
    {
        $medicine = $this->makeMedicine('Expmed');
        $this->stockIn($medicine, 5, now()->subDays(3)->toDateString(), '500mg', 'OLD');
        $this->stockIn($medicine, 5, now()->addDays(40)->toDateString(), '500mg', 'LATER');
        $this->stockIn($medicine, 5, now()->addDays(10)->toDateString(), '500mg', 'SOON');
        \App\Models\MedicineBatch::where('batch_number', 'SOON')->update(['quantity' => 0]); // used up: does not count
        $expected = now()->addDays(40)->toDateString();

        $alone = \App\Models\Medicine::find($medicine->id);
        $loaded = \App\Models\Medicine::with(['batches' => fn ($q) => $q->where('quantity', '>', 0)->orderBy('expiration_date')])->find($medicine->id);

        $this->assertSame($expected, substr((string) $alone->earliest_expiry_date, 0, 10));
        $this->assertSame($expected, substr((string) $loaded->earliest_expiry_date, 0, 10));
    }
}
