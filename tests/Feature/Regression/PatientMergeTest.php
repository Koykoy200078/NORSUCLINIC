<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\DocumentIssuance;
use App\Models\LabRequest;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\Prescription;
use App\Models\DispenseRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

class PatientMergeTest extends TestCase
{
    use RefreshDatabase, BuildsClinicData;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->admin = $this->makeAdmin();
        $this->actingAs($this->admin);
    }

    private function preview(Patient $source, Patient $target): array
    {
        return $this->get(route('patient-merge.index', ['source_id' => $source->id, 'target_id' => $target->id]))
            ->assertOk()->viewData('preview');
    }

    private function merge(Patient $source, Patient $target, array $preview, array $extra = []): \Illuminate\Testing\TestResponse
    {
        return $this->postJson(route('patient-merge.store'), array_merge(['source_id' => $source->id, 'target_id' => $target->id,
            'preview_token' => $preview['token'], 'confirmed' => true], $extra));
    }

    public function test_tool_and_confirmation_are_admin_only(): void
    {
        $this->get('/admin/patient-merge')->assertOk()->assertSee('Merge duplicate patients');
        foreach ([$this->makeNurse(), $this->makeDoctor(), $this->makePatient()->user] as $actor) {
            $this->actingAs($actor)->get('/admin/patient-merge')->assertForbidden();
            $this->postJson('/admin/patient-merge', [])->assertForbidden();
        }
    }

    public function test_candidate_reasons_are_explicit_and_do_not_merge_or_use_blank_keys(): void
    {
        $a = $this->makePatient(['first_name' => 'ANA', 'last_name' => 'Cruz', 'contact' => '9171234567', 'country_code' => '63']);
        $b = $this->makePatient(['first_name' => ' ana ', 'last_name' => 'cruz', 'contact' => '0917-123-4567']);
        $c = $this->makePatient(['first_name' => 'Other', 'last_name' => 'Person', 'gender' => User::FEMALE]);
        $response = $this->get('/admin/patient-merge')->assertOk()->assertSee('Name, birth date and sex')->assertSee('Phone');
        $pairs = $response->viewData('candidates');
        $this->assertCount(1, $pairs);
        $this->assertSame([$a->id, $b->id], [$pairs[0]['source']->id, $pairs[0]['target']->id]);
        $this->assertNotNull($c->fresh());
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_university_id_matches_a_legacy_patient_identifier_without_matching_demographics(): void
    {
        $a = $this->makePatient(['university_id_number' => 'UNI-2026']);
        $b = $this->makePatient(['first_name' => 'Different', 'dob' => '1990-03-10'], ['patient_unique_id' => 'UNI-2026']);
        $pairs = $this->get('/admin/patient-merge')->assertOk()->viewData('candidates');
        $this->assertCount(1, $pairs);
        $this->assertSame(['University ID'], $pairs[0]['reasons']);
        $this->assertSame([$a->id, $b->id], [$pairs[0]['source']->id, $pairs[0]['target']->id]);
    }

    public function test_preview_is_read_only_and_merge_preserves_history_snapshots_authors_and_stock(): void
    {
        $source = $this->makePatient([], ['allergies' => 'Source allergy']);
        $target = $this->makePatient(['first_name' => 'Kept'], ['allergies' => 'Kept allergy']);
        $doctor = $this->makeDoctor();
        $document = DocumentIssuance::create(['document_type' => 'consultation_form', 'document_creator_id' => $doctor->id,
            'user_id' => $source->user_id, 'name' => 'Historical name', 'age' => 23, 'gender' => 'Male', 'address' => 'Historical address',
            'requested_at' => today(), 'consultation_images' => [['path' => 'synthetic/photo.png', 'disk' => 'consultation_images']]]);
        $deleted = $document->replicate();
        $deleted->save();
        $deleted->delete();
        $lab = LabRequest::create(['request_number' => 'MERGE-LAB', 'document_creator_id' => $doctor->id, 'patient_user_id' => $source->user_id,
            'patient_name' => 'Old lab name', 'requested_at' => today()]);
        $rx = Prescription::create(['patient_id' => $source->id, 'doctor_id' => $doctor->doctor->id, 'consultation_date' => today(),
            'problem_description' => 'Saved diagnosis', 'status' => Prescription::DISPENSE_STATUS_PENDING]);
        $dispense = DispenseRecord::create(['patient_id' => $source->id, 'history_number' => 'MERGE-DISPENSE', 'bill_date' => today(), 'note' => 'Saved note', 'model_type' => DispenseRecord::class, 'model_id' => 0]);
        $queue = PatientQueue::create(['patient_id' => $source->id, 'added_by' => $doctor->id, 'status' => 'waiting', 'notes' => 'Saved queue note']);
        $sourceAddress = $source->address()->create(['address1' => 'Source address']);
        $userAddress = $source->user->address()->create(['address1' => 'Source user address']);
        $keptAddress = $target->address()->create(['address1' => 'Kept address']);
        $medicine = $this->makeMedicine();
        $batch = $this->stockIn($medicine, 5, '2027-12-31');
        $ledger = DB::table('medicine_transactions')->get()->toArray();
        $before = $source->fresh()->getAttributes();
        $preview = $this->preview($source, $target);
        $this->assertNotEmpty($preview['token']);
        $this->assertSame($before, $source->fresh()->getAttributes());
        $this->assertDatabaseCount('activity_logs', 0);
        $this->merge($source, $target, $preview)->assertOk();
        $this->assertNull(Patient::find($source->id));
        $this->assertNull(User::find($source->user_id));
        $archive = Patient::withTrashed()->findOrFail($source->id);
        $this->assertSame($target->id, $archive->merged_into_id);
        $this->assertSame('Source allergy', $archive->allergies);
        $this->assertSame('Kept allergy', $target->fresh()->allergies);
        $this->assertSame($target->user_id, $document->fresh()->user_id);
        $this->assertSame($target->user_id, DocumentIssuance::withTrashed()->find($deleted->id)->user_id);
        $this->assertNotNull(DocumentIssuance::withTrashed()->find($deleted->id)->deleted_at);
        $this->assertSame('Historical name', $document->fresh()->name);
        $this->assertSame($doctor->id, $document->fresh()->document_creator_id);
        $this->assertSame('synthetic/photo.png', $document->fresh()->consultation_images[0]['path']);
        $this->assertSame($target->user_id, $lab->fresh()->patient_user_id);
        $this->assertSame('Old lab name', $lab->fresh()->patient_name);
        foreach ([$rx, $dispense, $queue] as $row) {
            $this->assertSame($target->id, $row->fresh()->patient_id);
        }
        $this->assertSame('waiting', $queue->fresh()->status);
        $this->assertSame($document->id, $queue->fresh()->latest_consultation_id);
        $this->assertSame($keptAddress->id, $target->fresh()->address->id);
        foreach ([$sourceAddress, $userAddress] as $address) {
            $this->assertSame($target->id, $address->fresh()->owner_id);
            $this->assertSame(Patient::class, $address->fresh()->owner_type);
            $this->assertSame($source->id, $address->fresh()->merged_from_patient_id);
        }
        $this->assertEquals($ledger, DB::table('medicine_transactions')->get()->toArray());
        $this->assertSame(5, $batch->fresh()->quantity);
        $audit = ActivityLog::where('action', 'patient_merged')->sole();
        $this->assertSame($source->id, $audit->properties['source']['id']);
        $this->assertSame([$document->id, $deleted->id], $audit->properties['moved_ids']['document_issuances']);
        $this->assertStringNotContainsString('Source allergy', json_encode($audit->properties));
        $this->get(route('patient-merge.index', ['archive_id' => $source->id]))->assertOk()->assertSee('Source allergy')->assertSee('Merged into #'.$target->id);
        $this->postJson('/admin/patients/'.$source->id.'/restore')->assertUnprocessable();
        $this->artisan('user:restore', ['--id' => $source->user_id])->assertExitCode(1);
        $this->merge($source, $target, $preview)->assertUnprocessable();
    }

    public function test_two_open_queue_entries_block_merge_without_changing_the_queue(): void
    {
        $source = $this->makePatient();
        $target = $this->makePatient();
        foreach ([$source, $target] as $patient) {
            PatientQueue::create(['patient_id' => $patient->id, 'added_by' => $this->admin->id, 'status' => 'waiting']);
        }
        $preview = $this->preview($source, $target);
        $this->assertNull($preview['token']);
        $this->assertStringContainsString('open queue', implode(' ', $preview['blockers']));
        $this->assertSame(2, PatientQueue::where('status', 'waiting')->count());
    }

    public function test_stale_expired_tampered_actor_mismatch_and_unconfirmed_previews_are_rejected(): void
    {
        $source = $this->makePatient();
        $target = $this->makePatient();
        $preview = $this->preview($source, $target);
        $source->update(['allergies' => 'Changed']);
        $this->merge($source, $target, $preview)->assertUnprocessable();
        $preview = $this->preview($source, $target);
        $this->merge($source, $target, $preview, ['confirmed' => false])->assertUnprocessable();
        $this->merge($source, $target, $preview, ['preview_token' => 'tampered'])->assertUnprocessable();
        $this->actingAs($this->makeAdmin());
        $this->merge($source, $target, $preview)->assertUnprocessable();
        $this->actingAs($this->admin);
        $this->travel(16)->minutes();
        $this->merge($source, $target, $preview)->assertUnprocessable();
        $this->assertNotNull($source->fresh());
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_audit_failure_rolls_back_transfers_and_archival(): void
    {
        $source = $this->makePatient();
        $target = $this->makePatient();
        $queue = PatientQueue::create(['patient_id' => $source->id, 'added_by' => $this->admin->id, 'status' => 'completed']);
        $preview = $this->preview($source, $target);
        ActivityLog::creating(function ($log): void {
            if ($log->action === 'patient_merged') {
                throw new \RuntimeException('Synthetic audit failure');
            }
        });
        try {
            $this->merge($source, $target, $preview)->assertUnprocessable();
        } finally {
            ActivityLog::flushEventListeners();
        }
        $this->assertNotNull(Patient::find($source->id));
        $this->assertNotNull(User::find($source->user_id));
        $this->assertSame($source->id, $queue->fresh()->patient_id);
    }

    public function test_profile_files_keep_their_ids_paths_and_bytes_and_survive_replacing_the_kept_profile(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $source = $this->makePatient();
        $target = $this->makePatient();
        $media = $source->addMedia(\Illuminate\Http\UploadedFile::fake()->image('source.png'))->toMediaCollection(Patient::PROFILE, 'public');
        $path = $media->getPathRelativeToRoot();
        $bytes = \Illuminate\Support\Facades\Storage::disk('public')->get($path);
        $this->merge($source, $target, $this->preview($source, $target))->assertOk();
        $media->refresh();
        $this->assertSame($target->id, $media->model_id);
        $this->assertSame('merged_profiles', $media->collection_name);
        $this->assertSame($path, $media->getPathRelativeToRoot());
        $target->clearMediaCollection(Patient::PROFILE);
        $this->assertSame($bytes, \Illuminate\Support\Facades\Storage::disk('public')->get($path));
        $this->get(route('patient-merge.index', ['archive_id' => $source->id]))->assertOk()->assertSee('source.png');
    }

    public function test_changed_linked_history_invalidates_preview_and_old_html_preview_renders_after_merge(): void
    {
        $source = $this->makePatient();
        $target = $this->makePatient();
        $preview = $this->preview($source, $target);
        PatientQueue::create(['patient_id' => $source->id, 'added_by' => $this->admin->id, 'status' => 'completed']);
        $this->merge($source, $target, $preview)->assertUnprocessable();
        $this->merge($source, $target, $this->preview($source, $target))->assertOk();
        $this->get(route('patient-merge.index', ['source_id' => $source->id, 'target_id' => $target->id]))
            ->assertOk()->assertSee('already merged')->assertDontSee('name="preview_token"', false);
        $this->get('/admin/patients/'.$source->id.'/edit')->assertNotFound();
        $this->get('/admin/patients/'.$target->id)->assertOk()->assertSee('Merged patient details for review');
    }

    #[DataProvider('clinicalTables')]
    public function test_stale_forms_cannot_attach_new_history_to_a_merged_source(string $model, string $column, bool $userId): void
    {
        $source = $this->makePatient();
        $target = $this->makePatient();
        $this->merge($source, $target, $this->preview($source, $target))->assertOk();
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        (new $model([$column => $userId ? $source->user_id : $source->id]))->save();
    }

    public static function clinicalTables(): array
    {
        return [[DocumentIssuance::class, 'user_id', true], [LabRequest::class, 'patient_user_id', true],
            [Prescription::class, 'patient_id', false], [DispenseRecord::class, 'patient_id', false], [PatientQueue::class, 'patient_id', false]];
    }

    public function test_archived_accounts_and_nonpatient_roles_are_rejected_even_when_they_have_a_patient_profile(): void
    {
        $source = $this->makePatient();
        $target = $this->makePatient();
        $source->user->assignRole('staff');
        $this->getJson(route('patient-merge.index', ['source_id' => $source->id, 'target_id' => $target->id]))->assertUnprocessable();
        $source->user->removeRole('staff');
        $source->delete();
        $this->getJson(route('patient-merge.index', ['source_id' => $source->id, 'target_id' => $target->id]))->assertUnprocessable();
        $this->getJson(route('patient-merge.index', ['source_id' => $target->id, 'target_id' => $target->id]))->assertUnprocessable();
    }

    public function test_chained_merges_keep_original_archive_details_and_address_provenance(): void
    {
        $a = $this->makePatient([], ['allergies' => 'First archive allergy']);
        $b = $this->makePatient();
        $c = $this->makePatient();
        $address = $a->address()->create(['address1' => 'First address']);
        $this->merge($a, $b, $this->preview($a, $b))->assertOk();
        $this->merge($b, $c, $this->preview($b, $c))->assertOk();
        $this->assertSame($c->id, Patient::withTrashed()->find($a->id)->merged_into_id);
        $this->assertSame($c->id, $address->fresh()->owner_id);
        $this->assertSame($a->id, $address->fresh()->merged_from_patient_id);
        $this->get(route('patient-merge.index', ['archive_id' => $a->id]))->assertOk()->assertSee('First archive allergy')->assertSee('First address');
    }

    public function test_cancelled_audit_insert_cannot_leave_an_unaudited_merge(): void
    {
        $source = $this->makePatient();
        $target = $this->makePatient();
        $preview = $this->preview($source, $target);
        ActivityLog::creating(fn () => false);
        try {
            $this->merge($source, $target, $preview)->assertUnprocessable();
        } finally {
            ActivityLog::flushEventListeners();
        }
        $this->assertNotNull(Patient::find($source->id));
        $this->assertDatabaseCount('activity_logs', 0);
    }
}
