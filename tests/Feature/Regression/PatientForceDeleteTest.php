<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * STATUS section 6, "A dormant force delete of a patient" (the owner, 2026-11-14: remove the cascade): Patient::boot() used to
 * delete the person's queue entries, prescriptions, dispense records, documents, activity-log rows, user account and
 * addresses when a patient was deleted for good. Nothing in the app does that (patients are archived), so the cascade is
 * gone and a direct force delete is refused: a clinical record is never removed for good by accident.
 */
class PatientForceDeleteTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    public function test_a_patient_cannot_be_deleted_for_good_and_nothing_of_theirs_is_removed(): void
    {
        $admin = $this->makeAdmin();
        $patient = $this->makePatient();
        $userId = $patient->user_id;
        PatientQueue::create(['patient_id' => $patient->id, 'added_by' => $admin->id, 'status' => PatientQueue::STATUS_COMPLETED]);
        ActivityLog::create(['user_id' => $userId, 'action' => 'login', 'subject_type' => User::class, 'subject_id' => $userId, 'description' => 'Signed in']);
        ActivityLog::create(['user_id' => $admin->id, 'action' => 'patient_updated', 'subject_type' => Patient::class, 'subject_id' => $patient->id, 'description' => 'Edited']);

        try {
            $patient->forceDelete();
            $this->fail('a force delete of a patient must be refused');
        } catch (LogicException $refused) {
            $this->assertStringContainsString('archive', strtolower($refused->getMessage()));
        }

        $this->assertNotNull(Patient::withTrashed()->find($patient->id));
        $this->assertNotNull(User::withTrashed()->find($userId));
        $this->assertSame(1, PatientQueue::where('patient_id', $patient->id)->count());
        $this->assertSame(2, ActivityLog::whereIn('subject_id', [$userId, $patient->id])->count());
    }

    public function test_archiving_a_patient_works_as_before(): void
    {
        $patient = $this->makePatient();

        $patient->delete();

        $this->assertNotNull($patient->fresh()->archived_at);
        $this->assertNotNull(User::withTrashed()->find($patient->user_id)->archived_at);
    }
}
