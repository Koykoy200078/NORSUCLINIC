<?php

namespace Tests\Feature\Regression;

use App\Models\PatientQueue;
use App\Repositories\PatientMergeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Phase 8 keeps working on a database that has not had its migration yet, so a few places ask whether a column exists
 * (Schema::hasColumn is one information_schema query each time). Those answers do not change while a request runs, so
 * each one must be asked once per application instance, not once per queue entry or per patient row.
 */
class SchemaColumnChecksTest extends TestCase
{
    use RefreshDatabase, BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    /** @return array<string, int> table name => how many column-list queries ran for it while $work ran */
    private function columnListQueries(callable $work): array
    {
        $reads = [];
        DB::listen(function ($query) use (&$reads) {
            // Laravel 10.50 writes the table name into the statement itself: ... and table_name = 'patient_queues' ...
            if (str_contains($query->sql, 'information_schema.columns') && preg_match("/table_name = '([^']+)'/", $query->sql, $match)) {
                $reads[$match[1]] = ($reads[$match[1]] ?? 0) + 1;
            }
        });
        $work();

        return $reads;
    }

    public function test_creating_queue_entries_asks_for_the_ticket_column_once(): void
    {
        $nurse = $this->makeNurse();
        $patients = [$this->makePatient(), $this->makePatient(), $this->makePatient()];

        $reads = $this->columnListQueries(function () use ($patients, $nurse) {
            foreach ($patients as $patient) {
                PatientQueue::create(['patient_id' => $patient->id, 'added_by' => $nurse->id, 'status' => 'waiting']);
            }
        });

        $this->assertLessThanOrEqual(1, $reads['patient_queues'] ?? 0, 'one metadata query for three queue entries');
        $this->assertSame([1, 2, 3], PatientQueue::orderBy('id')->pluck('ticket_number')->map(fn ($n) => (int) $n)->all(), 'and the tickets are still allocated');
    }

    public function test_the_patient_address_and_the_merge_readiness_checks_ask_once(): void
    {
        $patient = $this->makePatient();

        $reads = $this->columnListQueries(function () use ($patient) {
            for ($i = 0; $i < 5; $i++) {
                $patient->address();
                PatientMergeRepository::ready();
            }
        });

        $this->assertLessThanOrEqual(1, $reads['addresses'] ?? 0, 'addresses.merged_from_patient_id is asked once, shared by both checks');
        $this->assertLessThanOrEqual(1, $reads['patients'] ?? 0, 'patients.merged_into_id is asked once');
        $this->assertTrue(PatientMergeRepository::ready(), 'and the answer is still right');
    }
}
