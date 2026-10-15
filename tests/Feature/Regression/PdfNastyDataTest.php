<?php

namespace Tests\Feature\Regression;

use App\Models\DispenseRecord;
use App\Models\DocumentIssuance;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\Prescription;
use App\Models\User;
use Database\Seeders\SettingTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 5, Pass A item 5: every PDF is made with text that breaks naive layouts (quotes, angle brackets, ampersands,
 * accents, emoji, a very long complaint with line breaks) and with the optional parts missing.
 */
class PdfNastyDataTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private const NASTY = 'O\'Brien & "Sons" <b>bold</b> <script>alert(1)</script> Ñandú Peñafrancia 😀 漢字';

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->admin = $this->makeAdmin();
        $this->seed(SettingTableSeeder::class);
    }

    private function assertPdf($response, string $what): void
    {
        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent(), "{$what}: not a PDF");
    }

    /** The dispense record "PDF" is an HTML print page: it must come out and must not let the awkward text act as markup. */
    private function assertPrintPage($response, string $what): void
    {
        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html, "{$what}: text typed by a person must be escaped");
        $this->assertStringNotContainsString('Undefined', $html, "{$what}: a PHP notice leaked into the page");
    }

    private function document(string $type, array $extra = []): DocumentIssuance
    {
        $patient = $this->makePatient();

        return DocumentIssuance::create(array_merge([
            'document_type' => $type, 'document_creator_id' => $this->admin->id, 'user_id' => $patient->user_id,
            'name' => self::NASTY, 'age' => 20, 'gender' => 'Male', 'address' => self::NASTY,
        ], $extra));
    }

    public function test_a_consultation_pdf_survives_awkward_text_and_missing_parts(): void
    {
        $long = str_repeat("Cough and colds for three days.\nNo fever.\n", 200);
        $nasty = $this->document('consultation_form', [
            'complaints' => $long, 'assessment' => self::NASTY, 'plan' => $long, 'pertinent_exam' => self::NASTY,
            'religion' => self::NASTY, 'patient_contact' => '0917 123 4567', 'requested_at' => now()->toDateString(),
        ]);
        $bare = $this->document('consultation_form', ['name' => 'Bare Minimum', 'address' => '', 'age' => 0, 'gender' => '']);

        $this->actingAs($this->admin);
        $this->assertPdf($this->get(route('document-issuances.export-pdf', $nasty)), 'consultation with awkward text');
        $this->assertPdf($this->get(route('document-issuances.export-pdf', $bare)), 'consultation with nothing optional filled in');
    }

    public function test_the_certificate_and_excuse_slip_pdfs_survive_awkward_text_and_missing_parts(): void
    {
        $this->actingAs($this->admin);

        foreach (['medical_certificate', 'excuse_slip'] as $type) {
            $awkward = $this->document($type, ['complaints' => self::NASTY, 'medical_cert_remarks' => str_repeat(self::NASTY . "\n", 50), 'requested_at' => now()->toDateString()]);
            $bare = $this->document($type, ['name' => 'Bare Minimum', 'address' => '', 'age' => 0, 'gender' => '']);

            $this->assertPdf($this->get(route('document-issuances.export-pdf', $awkward)), "{$type} with awkward text");
            $this->assertPdf($this->get(route('document-issuances.export-pdf', $bare)), "{$type} with nothing optional filled in");
        }
    }

    public function test_the_lab_request_pdf_survives_awkward_text_and_missing_parts(): void
    {
        $patient = $this->makePatient();
        $make = fn (array $extra) => tap(LabRequest::create(array_merge([
            'request_number' => 'LR' . random_int(100000, 999999), 'document_creator_id' => $this->admin->id, 'patient_user_id' => $patient->user_id,
            'patient_name' => self::NASTY, 'requested_at' => now()->toDateString(), 'status' => LabRequest::STATUS_PENDING,
        ], $extra)), function (LabRequest $request) {
            LabRequestItem::create(['lab_request_id' => $request->id, 'lab_test_id' => null, 'test_name' => self::NASTY, 'test_category' => 'Other', 'result_status' => LabRequestItem::RESULT_PENDING]);
        });

        $awkward = $make(['remarks' => str_repeat(self::NASTY . "\n", 100), 'clinical_indication' => self::NASTY, 'address' => self::NASTY]);
        $bare = $make(['patient_name' => 'Bare Minimum']);

        $this->actingAs($this->admin);
        $this->assertPdf($this->get(route('lab-requests.pdf', $awkward->id)), 'lab request with awkward text');
        $this->assertPdf($this->get(route('lab-requests.pdf', $bare->id)), 'lab request with nothing optional filled in');
    }

    public function test_the_prescription_and_dispense_record_pdfs_survive_a_record_without_lines(): void
    {
        $doctor = $this->makeDoctor(['first_name' => 'Zoë', 'last_name' => "O'Hara & <Sons>"]);
        $patient = $this->makePatient(['first_name' => self::NASTY]);

        $prescription = Prescription::create(['patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id, 'status' => Prescription::DISPENSE_STATUS_PENDING ?? 'pending', 'is_active' => 1]);
        $record = DispenseRecord::create([
            'history_number' => 'HIS' . generateUniqueHistoryNumber(), 'patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id,
            'model_type' => DispenseRecord::class, 'model_id' => (string) $patient->id, 'bill_date' => now(), 'note' => self::NASTY,
        ]);

        $this->actingAs($this->admin);
        $this->assertPdf($this->get(route('prescriptions.pdf', $prescription->id)), 'prescription without medicines');
        $this->assertPrintPage($this->get(route('dispense-records.pdf', $record->id)), 'dispense record without lines');
    }

    public function test_the_dispense_record_pdf_does_not_depend_on_every_setting_being_present(): void
    {
        \App\Models\Setting::query()->delete();
        \App\Services\SettingsService::clearCache();
        $patient = $this->makePatient();
        $record = DispenseRecord::create([
            'history_number' => 'HIS' . generateUniqueHistoryNumber(), 'patient_id' => $patient->id,
            'model_type' => DispenseRecord::class, 'model_id' => (string) $patient->id, 'bill_date' => now(),
        ]);

        $this->actingAs($this->admin);
        $this->assertPrintPage($this->get(route('dispense-records.pdf', $record->id)), 'dispense record on a clinic with no saved settings');
    }
}
