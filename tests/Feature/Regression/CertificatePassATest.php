<?php

namespace Tests\Feature\Regression;

use App\Models\DocumentIssuance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Phase 5, row 5.9 Certificates, Pass A: the medical certificate and the excuse slip use boxes with their own names
 * (vital_signs_*_2, subjects[]) that the consultation rules never saw, and one patient field that points at nothing.
 */
class CertificatePassATest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    /** @return array<string, mixed> */
    private function payload(string $type, int $userId, array $extra = []): array
    {
        return array_merge([
            'document_type' => $type, 'user_id' => $userId, 'name' => 'Juan Dela Cruz', 'gender' => 'Male', 'address' => 'Dumaguete',
            'examined_on' => '2026-10-17', 'complaints_diagnosis' => 'Cough', 'medical_cert_remarks' => 'Rest for three days',
            'vital_signs_bp_2' => '120', 'vital_signs_bp_22' => '80', 'vital_signs_pr_2' => '80', 'vital_signs_rr_2' => '18',
            'vital_signs_temp_2' => '36.5', 'vital_signs_height_2' => '170', 'vital_signs_weight_2' => '65',
        ], $extra);
    }

    // ------------------------------------------------------------------ validation: the boxes of the certificate forms

    public function test_the_certificate_boxes_with_their_own_names_are_checked_against_their_columns(): void
    {
        $admin = $this->makeAdmin();
        $patient = $this->makePatient();
        $this->actingAs($admin);

        foreach (['medical_certificate', 'excuse_slip'] as $type) {
            foreach ([
                'pulse too long' => ['vital_signs_pr_2' => str_repeat('9', 192)],
                'blood pressure too long' => ['vital_signs_bp_2' => str_repeat('9', 100), 'vital_signs_bp_22' => str_repeat('9', 100)],
                'temperature too long' => ['vital_signs_temp_2' => str_repeat('9', 192)],
                'subjects is not a list' => ['subjects' => 'Mathematics'],
                'a subject too long' => ['subjects' => [['subject' => str_repeat('a', 192), 'teacher' => 'T']]],
                'a teacher too long' => ['subjects' => [['subject' => 'Math', 'teacher' => str_repeat('a', 192)]]],
                'a patient that does not exist' => ['user_id' => 987654],
            ] as $label => $override) {
                $this->post(route('document-issuances.store'), $this->payload($type, $patient->user_id, $override))->assertSessionHasErrors();
                $this->assertSame(0, DocumentIssuance::where('document_type', $type)->count(), "{$type}: {$label} must save nothing");
                session()->flush();
            }
        }
    }

    public function test_the_same_checks_apply_when_a_certificate_is_edited(): void
    {
        $admin = $this->makeAdmin();
        $patient = $this->makePatient();
        $this->actingAs($admin);
        $this->post(route('document-issuances.store'), $this->payload('excuse_slip', $patient->user_id, ['subjects' => [['subject' => 'Math', 'teacher' => 'T']]]))->assertSessionHasNoErrors();
        $slip = DocumentIssuance::firstOrFail();

        foreach ([['vital_signs_pr_2' => str_repeat('9', 192)], ['subjects' => 'Mathematics'], ['subjects' => [['subject' => str_repeat('a', 192)]]]] as $override) {
            $this->put(route('document-issuances.update', $slip), $this->payload('excuse_slip', $patient->user_id, $override))->assertSessionHasErrors();
            session()->flush();
        }
        $this->assertSame('80', $slip->fresh()->vital_signs_pr);
        $this->assertSame('Math', $slip->fresh()->subjects[0]['subject']);
    }

    public function test_a_blood_pressure_left_blank_is_saved_as_nothing_not_as_a_slash(): void
    {
        $admin = $this->makeAdmin();
        $patient = $this->makePatient();
        $this->actingAs($admin);

        $this->post(route('document-issuances.store'), $this->payload('medical_certificate', $patient->user_id, ['vital_signs_bp_2' => '', 'vital_signs_bp_22' => '']))->assertSessionHasNoErrors();
        $this->assertNull(DocumentIssuance::firstOrFail()->vital_signs_bp, 'a blank pressure is not "/"');

        $this->post(route('document-issuances.store'), $this->payload('medical_certificate', $patient->user_id))->assertSessionHasNoErrors();
        $this->assertSame('120/80', DocumentIssuance::orderByDesc('id')->firstOrFail()->vital_signs_bp);
    }

    // ------------------------------------------------------------------ the finished documents

    public function test_a_certificate_with_its_subjects_saves_shows_and_prints(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();
        $this->actingAs($doctor);

        $this->post(route('doctors.document-issuances.store'), $this->payload('excuse_slip', $patient->user_id, [
            'subjects' => [['subject' => 'Mathematics <b>101</b>', 'teacher' => 'Ms. Cruz'], ['subject' => '', 'teacher' => '']],
        ]))->assertSessionHasNoErrors();

        $slip = DocumentIssuance::firstOrFail();
        $this->assertSame('Mathematics <b>101</b>', $slip->subjects[0]['subject']);

        $html = $this->get(route('doctors.document-issuances.show', $slip))->assertOk()->getContent();
        $this->assertStringNotContainsString('<b>101</b>', $html, 'markup in a subject is shown as text');
        $this->get(route('doctors.document-issuances.export-pdf', $slip))->assertOk();

        // the page that opens for printing shows the same text, never markup
        $print = $this->get(route('doctors.document-issuances.export-pdf', ['document_issuance' => $slip, 'action' => 'print']))->assertOk()->getContent();
        $this->assertStringContainsString('Mathematics', $print);
        $this->assertStringNotContainsString('<b>101</b>', $print);

        $this->post(route('doctors.document-issuances.store'), $this->payload('medical_certificate', $patient->user_id, ['name' => 'Nasty <script>alert(1)</script> Name', 'complaints_diagnosis' => '<img src=x onerror=alert(2)>']))->assertSessionHasNoErrors();
        $certificate = DocumentIssuance::where('document_type', 'medical_certificate')->firstOrFail();
        $print = $this->get(route('doctors.document-issuances.export-pdf', ['document_issuance' => $certificate, 'action' => 'print']))->assertOk()->getContent();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $print);
        $this->assertStringNotContainsString('<img src=x onerror', $print);
    }
}
