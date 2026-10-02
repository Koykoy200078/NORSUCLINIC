<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * R3-M6 / R3-M5 for lab requests (2026-10-01 re-audit): a lab request that is finished (it holds results)
 * can never be deleted; an unfinished one only by its creator or the clinic admin; every delete is audited.
 */
class LabRequestDeleteTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->patient = $this->makePatient();
    }

    private function labRequest(User $creator, string $status): LabRequest
    {
        static $sequence = 0;
        $sequence++;

        $request = LabRequest::create([
            'request_number' => 'LAB-T-' . $sequence,
            'document_creator_id' => $creator->id,
            'patient_user_id' => $this->patient->user_id,
            'patient_name' => 'Juan Dela Cruz',
            'patient_age' => 23,
            'patient_gender' => 'Male',
            'requested_at' => now()->toDateString(),
            'status' => $status,
            'requesting_physician' => 'Dr. Doc Tester',
        ]);

        LabRequestItem::create([
            'lab_request_id' => $request->id,
            'test_name' => 'Complete Blood Count',
            'result_value' => $status === LabRequest::STATUS_COMPLETED ? '5.1' : null,
        ]);

        return $request;
    }

    /** @return array<string, array{0: string}> */
    public static function finishedStatuses(): array
    {
        return [
            'completed' => [LabRequest::STATUS_COMPLETED],
            'referred' => [LabRequest::STATUS_REFERRED],
            'rejected' => [LabRequest::STATUS_REJECTED],
        ];
    }

    /**
     * @dataProvider finishedStatuses
     */
    public function test_a_finished_lab_request_can_never_be_deleted_not_even_by_the_admin(string $status): void
    {
        $doctor = $this->makeDoctor();
        $admin = $this->makeAdmin();
        $request = $this->labRequest($doctor, $status);

        $this->actingAs($doctor)->delete(route('doctors.lab-requests.destroy', $request))->assertSessionHas('error');
        $this->actingAs($admin)->delete(route('lab-requests.destroy', $request))->assertSessionHas('error');

        $this->assertNotNull(LabRequest::find($request->id));
        $this->assertSame(1, LabRequestItem::where('lab_request_id', $request->id)->count());
    }

    /**
     * @dataProvider unfinishedButInProgress
     */
    public function test_a_request_in_progress_must_be_cancelled_before_it_can_be_deleted(string $status): void
    {
        $doctor = $this->makeDoctor();
        $request = $this->labRequest($doctor, $status);

        $this->actingAs($doctor)->delete(route('doctors.lab-requests.destroy', $request))->assertSessionHas('error');

        $this->assertNotNull(LabRequest::find($request->id));
    }

    /** @return array<string, array{0: string}> */
    public static function unfinishedButInProgress(): array
    {
        return [
            'collected' => [LabRequest::STATUS_COLLECTED],
            'processing' => [LabRequest::STATUS_PROCESSING],
        ];
    }

    public function test_only_the_creator_or_the_admin_can_delete_a_pending_or_cancelled_request(): void
    {
        $doctor = $this->makeDoctor();
        $otherDoctor = $this->makeDoctor();
        $admin = $this->makeAdmin();
        $pending = $this->labRequest($doctor, LabRequest::STATUS_PENDING);

        $this->actingAs($otherDoctor)->delete(route('doctors.lab-requests.destroy', $pending))->assertForbidden();
        $this->assertNotNull(LabRequest::find($pending->id));

        $this->actingAs($doctor)->delete(route('doctors.lab-requests.destroy', $pending))->assertSessionHas('success');
        $this->assertNull(LabRequest::find($pending->id));

        $cancelled = $this->labRequest($doctor, LabRequest::STATUS_CANCELLED);
        $this->actingAs($admin)->delete(route('lab-requests.destroy', $cancelled))->assertSessionHas('success');
        $this->assertNull(LabRequest::find($cancelled->id));
    }

    public function test_deleting_a_lab_request_leaves_an_audit_snapshot(): void
    {
        $doctor = $this->makeDoctor();
        $request = $this->labRequest($doctor, LabRequest::STATUS_PENDING);

        $this->actingAs($doctor)->delete(route('doctors.lab-requests.destroy', $request))->assertSessionHas('success');

        $log = ActivityLog::where('action', 'lab_request_deleted')->where('subject_id', $request->id)->firstOrFail();
        $this->assertSame($doctor->id, (int) $log->user_id);
        $this->assertSame('Juan Dela Cruz', $log->patient_name);
        $this->assertSame('pending', $log->properties['status']);
        $this->assertSame(['Complete Blood Count'], $log->properties['tests']);
        $this->assertSame($request->request_number, $log->properties['request_number']);
    }
}
