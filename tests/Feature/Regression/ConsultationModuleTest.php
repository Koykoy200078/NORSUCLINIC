<?php

namespace Tests\Feature\Regression;

use App\Models\DocumentIssuance;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Regression tests for the consultation / certificate module fixes of the 2026-09 re-audit
 * (H-02, C-03, H-03, C-01, C-06, P2-H4, M-14).
 */
class ConsultationModuleTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function consultationPayload(array $overrides = []): array
    {
        return array_merge([
            'document_type' => 'consultation_form',
            'requested_at' => now()->toDateString(),
            'consult_mode' => 'physical',
            'complaints' => 'Cough',
        ], $overrides);
    }

    private function makeConsultation(User $creator, Patient $patient, string $type = 'consultation_form'): DocumentIssuance
    {
        return DocumentIssuance::create([
            'document_type' => $type,
            'document_creator_id' => $creator->id,
            'user_id' => $patient->user_id,
            'name' => 'Juan Dela Cruz',
            'age' => 23,
            'gender' => 'Male',
            'address' => 'Dumaguete',
            'requested_at' => now()->toDateString(),
        ]);
    }

    /** H-02 */
    public function test_certificates_only_staff_cannot_reach_a_consultation_by_overriding_the_module(): void
    {
        $frontDesk = $this->makeStaff('clinic_staff', 'front_desk');
        $consultation = $this->makeConsultation($frontDesk, $this->makePatient());
        $certificate = $this->makeConsultation($frontDesk, $this->makePatient(), 'medical_certificate');

        $this->actingAs($frontDesk);

        $this->get(route('staff.document-issuances.show', $consultation) . '?module=certificates')->assertForbidden();
        $this->get(route('staff.document-issuances.edit', $consultation) . '?module=certificate')->assertForbidden();
        $this->get(route('staff.document-issuances.export-pdf', $consultation) . '?module=certificates')->assertForbidden();

        $this->put(route('staff.document-issuances.update', $consultation), [
            'document_type' => 'medical_certificate',
            'name' => 'Tampered Name',
        ])->assertForbidden();
        $this->assertSame('Juan Dela Cruz', $consultation->fresh()->name);

        // Their own module still works.
        $this->get(route('staff.document-issuances.show', $certificate))->assertOk();
    }

    /** C-03 */
    public function test_blank_history_fields_do_not_wipe_the_patients_allergies(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient([], [
            'allergies' => 'PENICILLIN (anaphylaxis)',
            'maintenance' => 'Metformin 500mg',
            'comorbidities' => 'Diabetes',
        ]);
        $nurse = $this->makeStaff('nurse', 'triage_area');

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->consultationPayload([
            'user_id' => $patient->user_id,
            'name' => 'Juan Dela Cruz',
            'date_of_birth' => '2003-05-01',
            'gender' => 'Male',
            'allergies' => '',
            'maintenance' => '',
            'comorbidities_custom' => '',
            'nursing_incharged' => $nurse->id,
        ]))->assertRedirect();

        $patient->refresh();
        $this->assertSame('PENICILLIN (anaphylaxis)', $patient->allergies);
        $this->assertSame('Metformin 500mg', $patient->maintenance);
        $this->assertSame('Diabetes', $patient->comorbidities);
    }

    /** H-03 */
    public function test_a_similar_but_different_name_is_not_merged_into_an_existing_patient(): void
    {
        $doctor = $this->makeDoctor();
        $existing = $this->makePatient([
            'first_name' => 'Juliana', 'last_name' => 'Cruzado', 'dob' => '2001-02-03', 'gender' => User::FEMALE,
        ]);
        $nurse = $this->makeStaff('nurse', 'triage_area');

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->consultationPayload([
            'name' => 'Ana Cruz',
            'date_of_birth' => '2001-02-03',
            'gender' => 'Female',
            'nursing_incharged' => $nurse->id,
        ]))->assertRedirect();

        $this->assertSame('Juliana', $existing->user->fresh()->first_name);
        $this->assertSame(0, DocumentIssuance::where('user_id', $existing->user_id)->count());
        $this->assertNotNull(User::where('first_name', 'Ana')->where('last_name', 'Cruz')->first());
    }

    /** H-03 */
    public function test_the_same_name_links_to_the_existing_patient_without_rewriting_identity(): void
    {
        $doctor = $this->makeDoctor();
        $existing = $this->makePatient([
            'first_name' => 'Juliana', 'last_name' => 'Cruzado', 'dob' => '2001-02-03', 'gender' => User::FEMALE,
        ]);
        $nurse = $this->makeStaff('nurse', 'triage_area');

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->consultationPayload([
            'name' => 'JULIANA M. CRUZADO',
            'date_of_birth' => '2001-02-03',
            'gender' => 'Female',
            'nursing_incharged' => $nurse->id,
        ]))->assertRedirect();

        $this->assertSame(1, User::where('last_name', 'like', 'cruzado')->count());
        $this->assertSame('Juliana', $existing->user->fresh()->first_name);
        $this->assertSame(1, DocumentIssuance::where('user_id', $existing->user_id)->count());
    }

    /** C-01 */
    public function test_consultation_photos_are_private_random_named_and_only_real_images_are_accepted(): void
    {
        Storage::fake('consultation_images');

        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $base = $this->consultationPayload([
            'user_id' => $patient->user_id,
            'name' => 'Juan Dela Cruz',
            'date_of_birth' => '2003-05-01',
            'gender' => 'Male',
            'nursing_incharged' => $nurse->id,
        ]);

        // A script disguised as a photo is rejected and nothing is saved.
        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $base + [
            'consultation_images' => [UploadedFile::fake()->create('probe.php', 1, 'application/x-php')],
        ])->assertSessionHasErrors();
        $this->assertStringContainsString(
            'JPEG, PNG, GIF or WEBP',
            (string) collect(session('errors')->getBag('default')->all())->first()
        );
        $this->assertSame(0, DocumentIssuance::count());
        $this->assertSame([], Storage::disk('consultation_images')->allFiles());

        // A real image is stored on the private disk under a random name.
        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $base + [
            'consultation_images' => [UploadedFile::fake()->image('xray.png', 20, 20)],
        ])->assertRedirect();

        $document = DocumentIssuance::firstOrFail();
        $images = $document->consultationImageList();
        $this->assertCount(1, $images);
        $this->assertSame('consultation_images', $images[0]['disk']);
        $this->assertSame('xray.png', $images[0]['name']);
        $this->assertStringNotContainsString('xray', $images[0]['path']);
        Storage::disk('consultation_images')->assertExists($images[0]['path']);

        // Served only through the authenticated route.
        $imageUrl = route('doctors.document-issuances.image', [$document, 0]);
        $this->actingAs($doctor)->get($imageUrl)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        auth()->logout();
        $this->get($imageUrl)->assertRedirect(route('login'));
    }

    /** C-06 */
    public function test_clinical_symbols_are_stored_exactly_as_typed_by_a_doctor(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $text = 'BP > 140/90, Temp < 37 and x<y and z>w & Tom & Jerry';

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->consultationPayload([
            'user_id' => $patient->user_id,
            'name' => 'Juan Dela Cruz',
            'date_of_birth' => '2003-05-01',
            'gender' => 'Male',
            'pertinent_exam' => $text,
            'nursing_incharged' => $nurse->id,
        ]))->assertRedirect();

        $this->assertSame($text, DocumentIssuance::firstOrFail()->pertinent_exam);
    }

    /** P2-H4 */
    public function test_long_allergy_and_nursing_text_is_stored_in_full(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $allergies = str_repeat('PENICILLIN (anaphylaxis), ', 12) . 'SULFA (anaphylaxis)';

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->consultationPayload([
            'user_id' => $patient->user_id,
            'name' => 'Juan Dela Cruz',
            'date_of_birth' => '2003-05-01',
            'gender' => 'Male',
            'allergies' => $allergies,
            'nursing_intervention' => str_repeat('STOP IF RASH APPEARS. ', 30),
            'nursing_incharged' => $nurse->id,
        ]))->assertRedirect();

        $document = DocumentIssuance::firstOrFail();
        $this->assertSame($allergies, $document->allergies);
        $this->assertGreaterThan(500, strlen($document->nursing_intervention));
    }

    /** M-14 */
    public function test_creator_is_always_the_signed_in_user_and_unknown_types_are_rejected(): void
    {
        $doctor = $this->makeDoctor();
        $other = $this->makeAdmin();
        $patient = $this->makePatient();
        $nurse = $this->makeStaff('nurse', 'triage_area');

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->consultationPayload([
            'user_id' => $patient->user_id,
            'name' => 'Juan Dela Cruz',
            'date_of_birth' => '2003-05-01',
            'gender' => 'Male',
            'document_creator_id' => $other->id, // spoofed author
            'nursing_incharged' => $nurse->id,
        ]))->assertRedirect();

        $this->assertSame($doctor->id, (int) DocumentIssuance::firstOrFail()->document_creator_id);

        $before = DocumentIssuance::count();
        $this->post(route('doctors.document-issuances.store'), ['document_type' => 'bogus'])->assertSessionHasErrors();
        $this->assertSame($before, DocumentIssuance::count());
    }

    /** M-05 */
    public function test_every_edit_of_a_consultation_adds_an_audit_row(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();
        $nurse = $this->makeStaff('nurse', 'triage_area');

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->consultationPayload([
            'user_id' => $patient->user_id,
            'name' => 'Juan Dela Cruz',
            'date_of_birth' => '2003-05-01',
            'gender' => 'Male',
            'complaints' => 'first',
            'nursing_incharged' => $nurse->id,
        ]))->assertRedirect();

        $document = DocumentIssuance::firstOrFail();

        foreach (['edited once', 'edited twice'] as $complaint) {
            $this->put(route('doctors.document-issuances.update', $document), [
                'name' => 'Juan Dela Cruz',
                'complaints' => $complaint,
                'nursing_incharged' => $nurse->id,
                'requested_at' => now()->toDateString(),
            ])->assertRedirect();
        }

        $rows = \App\Models\ActivityLog::where('action', 'consultation_record')
            ->where('subject_id', $document->id)->orderBy('id')->get();

        $this->assertCount(3, $rows);
        $this->assertSame(['first', 'edited once', 'edited twice'], $rows->pluck('complaints')->all());
        $this->assertStringStartsWith('Created - ', $rows[0]->description);
        $this->assertStringStartsWith('Updated - ', $rows[2]->description);
    }
}
