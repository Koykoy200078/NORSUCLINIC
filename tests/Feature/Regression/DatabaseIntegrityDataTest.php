<?php

namespace Tests\Feature\Regression;

use App\Models\DocumentIssuance;
use App\Models\User;
use App\Services\DatabaseIntegrityChecker;
use App\Services\MedicineInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 1.5: the data checks of `php artisan db:integrity` (stock, permissions, duplicate candidates, repair
 * commands) on valid data that each test then damages in exactly one way.
 */
class DatabaseIntegrityDataTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedAccessControl();
        // Seeding runs through $this->artisan(), which leaves a mock console output bound for the rest of the test;
        // the repair-command check captures real output from Artisan::call().
        $this->withoutMockingConsoleOutput();
    }

    public function test_a_clean_database_passes_every_check(): void
    {
        $medicine = $this->makeMedicine('Paracetamol');
        $this->stockIn($medicine, 20, now()->addYear()->toDateString());
        $this->consultationDispensing($medicine, 5);

        foreach ((new DatabaseIntegrityChecker())->run() as $check) {
            $this->assertSame('ok', $check['severity'], $check['title'].': '.implode('; ', [...$check['errors'], ...$check['warnings']]));
        }
    }

    public function test_a_medicine_total_that_differs_from_its_batches_is_an_error(): void
    {
        $medicine = $this->makeMedicine('Paracetamol');
        $this->stockIn($medicine, 20, now()->addYear()->toDateString());
        DB::table('medicines')->where('id', $medicine->id)->update(['quantity' => 25]);

        $check = $this->check('medicine totals');

        $this->assertSame('error', $check['severity']);
        $this->assertSame(["Paracetamol (#{$medicine->id}): recorded 25, batches add up to 20"], $check['errors']);
    }

    public function test_a_batch_that_differs_from_the_ledger_is_an_error(): void
    {
        $medicine = $this->makeMedicine('Paracetamol');
        $batch = $this->stockIn($medicine, 20, now()->addYear()->toDateString());
        // Keep the medicine total in step so that only the ledger disagrees.
        DB::table('medicine_batches')->where('id', $batch->id)->update(['quantity' => 23]);
        DB::table('medicines')->where('id', $medicine->id)->update(['quantity' => 23]);

        $check = $this->check('batch balances');

        $this->assertSame('error', $check['severity']);
        $this->assertStringContainsString("batch #{$batch->id}", $check['errors'][0]);
        $this->assertStringContainsString('quantity 23, the ledger says 20', $check['errors'][0]);
    }

    public function test_consultation_medicines_that_the_ledger_never_deducted_are_an_error(): void
    {
        $medicine = $this->makeMedicine('Paracetamol');
        $this->stockIn($medicine, 20, now()->addYear()->toDateString());
        $consultation = $this->consultation();
        DB::table('consultation_medicines')->insert([
            'request_document_id' => $consultation->id, 'medicine_id' => $medicine->id, 'quantity' => 4,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $check = $this->check('consultation medicines');

        $this->assertSame('error', $check['severity']);
        $this->assertSame(["consultation #{$consultation->id}, Paracetamol (#{$medicine->id}): the lines say 4, the ledger deducted 0"], $check['errors']);
    }

    public function test_a_deleted_consultation_must_have_returned_its_stock(): void
    {
        $medicine = $this->makeMedicine('Paracetamol');
        $this->stockIn($medicine, 20, now()->addYear()->toDateString());
        $consultation = $this->consultationDispensing($medicine, 5);

        // Soft-deleted without the stock being returned.
        DB::table('document_issuances')->where('id', $consultation->id)->update(['deleted_at' => now()]);

        $check = $this->check('consultation medicines');

        $this->assertSame('error', $check['severity']);
        $this->assertSame(["consultation #{$consultation->id}, Paracetamol (#{$medicine->id}): the lines say 0, the ledger deducted 5"], $check['errors']);
    }

    public function test_stock_on_the_shelf_without_any_ledger_row_is_a_warning(): void
    {
        $medicine = $this->makeMedicine('Paracetamol');
        DB::table('medicine_batches')->insert([
            'medicine_id' => $medicine->id, 'batch_number' => 'OLD-1', 'quantity' => 12, 'dosage' => '500mg',
            'date_received' => now()->toDateString(), 'expiration_date' => now()->addYear()->toDateString(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $check = $this->check('stock without ledger');

        $this->assertSame('warning', $check['severity']);
        $this->assertSame(["Paracetamol (#{$medicine->id}): 12 unit(s) in batches that have no ledger row"], $check['warnings']);
    }

    public function test_direct_permissions_on_a_non_admin_user_are_a_warning_but_not_on_the_admin(): void
    {
        $admin = $this->makeAdmin();
        $admin->givePermissionTo('manage_patients');
        $staff = $this->makeStaff();
        $staff->givePermissionTo('manage_patients');

        $check = $this->check('direct permissions');

        $this->assertSame('warning', $check['severity']);
        $this->assertSame(["user #{$staff->id} (staff): 1 permission(s) given directly instead of by the role"], $check['warnings']);
    }

    public function test_two_medicines_and_two_patients_that_look_the_same_are_listed_as_candidates(): void
    {
        $first = $this->makeMedicine('Kremil-S', ['dosage' => '178/233/30 mg']);
        $second = $this->makeMedicine('kremil-s ', ['dosage' => '178/233/30 mg']);
        $this->makeMedicine('Kremil-S', ['dosage' => '10 mg']);
        $one = $this->makePatient(['first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'dob' => '2003-05-01']);
        $two = $this->makePatient(['first_name' => 'juan ', 'last_name' => 'dela cruz', 'dob' => '2003-05-01']);
        $this->makePatient(['first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'dob' => '1999-01-01']);

        $check = $this->check('duplicate candidates');

        $this->assertSame('warning', $check['severity']);
        $this->assertSame([
            "medicines #{$first->id}, #{$second->id}: same name and strength",
            "patients #{$one->id}, #{$two->id}: same name and birth date",
        ], $check['warnings']);
    }

    public function test_text_and_phone_repairs_that_are_still_waiting_are_counted(): void
    {
        $this->makePatient(['first_name' => 'Tom &amp; Jerry', 'contact' => '09171234567']);

        $check = $this->check('pending data repairs');

        $this->assertSame('warning', $check['severity']);
        $joined = implode("\n", $check['warnings']);
        $this->assertStringContainsString('text:repair-entities would decode 1 value(s)', $joined);
        $this->assertStringContainsString('phone:normalize would update 1 record(s)', $joined);
    }

    /**
     * @return array{key: string, title: string, severity: string, errors: list<string>, warnings: list<string>}
     */
    private function check(string $titleContains): array
    {
        foreach ((new DatabaseIntegrityChecker())->run() as $check) {
            if (str_contains(strtolower($check['title']), $titleContains)) {
                return $check;
            }
        }

        $this->fail("No check with a title containing '{$titleContains}'.");
    }

    private function consultation(): DocumentIssuance
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();

        return DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $doctor->id, 'user_id' => $patient->user_id,
            'name' => 'Test Patient', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete',
            'requested_at' => now()->toDateString(), 'consult_mode' => 'physical', 'informant' => 'Student',
            'complaints' => 'cough', 'assessment' => 'URTI', 'plan' => 'rest',
        ]);
    }

    /**
     * A consultation whose medicine line was deducted from stock the way the application does it.
     */
    private function consultationDispensing($medicine, int $quantity): DocumentIssuance
    {
        $consultation = $this->consultation();
        DB::table('consultation_medicines')->insert([
            'request_document_id' => $consultation->id, 'medicine_id' => $medicine->id, 'quantity' => $quantity,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        app(MedicineInventoryService::class)->deductStockFefo($medicine->id, $quantity, null, $consultation);

        return $consultation;
    }
}
