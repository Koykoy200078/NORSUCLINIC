<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\DocumentIssuance;
use App\Services\Reports\AccomplishmentReportBuilder;
use App\Services\Reports\ReportFilters;
use App\Services\Reports\ReportQueries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * R3-M6: consultations, medical certificates and excuse slips are clinical records. Deleting one hides it everywhere
 * but keeps the row and its photos (deleted_at), and still leaves the audit snapshot.
 */
class ClinicalRecordRetentionTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function consultation(array $attributes = []): DocumentIssuance
    {
        $doctor = $this->makeDoctor(['email' => 'ret.doc' . uniqid() . '@test.local']);
        $patient = $this->makePatient(['email' => 'ret.pt' . uniqid() . '@test.local']);

        return DocumentIssuance::create(array_merge([
            'document_type' => 'consultation_form', 'document_creator_id' => $doctor->id, 'user_id' => $patient->user_id,
            'name' => 'Retention Patient', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete',
            'requested_at' => now()->toDateString(), 'complaints' => 'cough',
        ], $attributes));
    }

    public function test_deleting_a_consultation_hides_it_everywhere_but_keeps_the_row_and_its_photos(): void
    {
        Storage::fake('consultation_images');
        Storage::disk('consultation_images')->put('2026/abc123.png', 'image-bytes');

        $visit = $this->consultation(['consultation_images' => [['disk' => 'consultation_images', 'path' => '2026/abc123.png', 'name' => 'xray.png']]]);
        $other = $this->consultation(['name' => 'Someone Else']);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->delete(route('document-issuances.destroy', $visit))->assertRedirect();

        // gone from every list ...
        $this->assertNull(DocumentIssuance::find($visit->id));
        $this->assertSame(['Someone Else'], ReportQueries::visits(ReportFilters::fromArray([]))->pluck('name')->all());
        $this->assertSame(1, app(AccomplishmentReportBuilder::class)->build(ReportFilters::fromArray([]), $admin)['consultations']);
        $this->get(route('document-issuances.show', $visit->id))->assertNotFound();

        // ... but not from the database, and the photo is still there
        $this->assertSoftDeleted('document_issuances', ['id' => $visit->id]);
        $this->assertNotNull(DocumentIssuance::withTrashed()->find($visit->id)->deleted_at);
        Storage::disk('consultation_images')->assertExists('2026/abc123.png');

        // the audit snapshot is still written
        $this->assertSame(1, ActivityLog::where('action', 'document_deleted')->count());
    }

    public function test_a_deleted_record_can_be_recovered(): void
    {
        $visit = $this->consultation();

        $visit->delete();
        $this->assertSame(0, DocumentIssuance::count());

        DocumentIssuance::withTrashed()->find($visit->id)->restore();
        $this->assertSame(1, DocumentIssuance::count());
        $this->assertSame('Retention Patient', DocumentIssuance::first()->name);
    }

    public function test_only_a_real_force_delete_removes_the_photos(): void
    {
        Storage::fake('consultation_images');
        Storage::disk('consultation_images')->put('2026/gone.png', 'image-bytes');
        $visit = $this->consultation(['consultation_images' => [['disk' => 'consultation_images', 'path' => '2026/gone.png', 'name' => 'x.png']]]);

        $visit->forceDelete();

        $this->assertDatabaseMissing('document_issuances', ['id' => $visit->id]);
        Storage::disk('consultation_images')->assertMissing('2026/gone.png');
    }

    public function test_a_deleted_certificate_no_longer_counts_toward_the_report(): void
    {
        $admin = $this->makeAdmin();
        $visit = $this->consultation();
        $certificate = DocumentIssuance::create([
            'document_type' => 'medical_certificate', 'document_creator_id' => $admin->id, 'user_id' => $visit->user_id,
            'name' => 'Retention Patient', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
        ]);

        $count = function () use ($admin) {
            $report = app(AccomplishmentReportBuilder::class)->build(ReportFilters::fromArray([]), $admin);
            $row = collect($report['service_sections'])->pluck('rows')->flatten(1)->firstWhere('name', 'Medical certificate issuance');

            return $row['total'];
        };

        $this->assertSame(1, $count());
        $certificate->delete();
        $this->assertSame(0, $count());
    }
}
