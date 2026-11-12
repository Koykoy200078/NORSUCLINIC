<?php

namespace Tests\Feature\Regression;

use App\Models\DocumentIssuance;
use App\Models\LabRequest;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\SettingTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * What DomPDF may load while it draws a PDF (the owner's decision of 2026-11-12: no remote loading).
 *
 * - The medical certificate and the excuse slip carry the university and clinic logos as `data:` URIs (built from the files
 *   in public/assets/image). DomPDF only loads protocols named in `allowed_protocols`, and the list had no `data://`, so both
 *   PDFs came out without their logos (only the text of the PDFs was compared before, so nothing noticed).
 * - The lab request draws its logo from a file under the project (`public_path()`): `file://` stays allowed.
 * - Nothing is ever fetched over the network (`enable_remote` off): a PDF must not make the server request a URL, and every
 *   picture a PDF needs is on the clinic PC.
 */
class PdfImageLoadingTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->admin = $this->makeAdmin();
        $this->seed(SettingTableSeeder::class);

        foreach (['assets/image/norsu_logo.png', 'assets/image/norsu_clinic_logo.png'] as $logo) {
            $this->assertFileExists(public_path($logo), "{$logo} is built by `npm run dev` / `npm run prod` (copied from resources/assets/images)");
        }
    }

    /**
     * The number of pictures embedded in a PDF. A PNG with transparency (both logos) is stored as an image plus a second image,
     * its soft mask (`/SMask n 0 R`), so the masks are not counted.
     */
    private function embeddedImages(string $pdf): int
    {
        return preg_match_all('#/Subtype\s*/Image\b#', $pdf) - preg_match_all('#/SMask\s+\d+\s+0\s+R#', $pdf);
    }

    public function test_the_certificate_and_excuse_slip_pdfs_carry_both_logos(): void
    {
        $this->actingAs($this->admin);

        foreach (['medical_certificate', 'excuse_slip'] as $type) {
            $patient = $this->makePatient();
            $document = DocumentIssuance::create([
                'document_type' => $type, 'document_creator_id' => $this->admin->id, 'user_id' => $patient->user_id,
                'name' => 'Ana Reyes', 'age' => 20, 'gender' => 'Female', 'address' => 'Dumaguete City',
                'requested_at' => now()->toDateString(),
            ]);

            $response = $this->get(route('document-issuances.export-pdf', $document));

            $response->assertOk();
            $this->assertStringStartsWith('%PDF', $response->getContent(), "{$type}: not a PDF");
            $this->assertSame(2, $this->embeddedImages($response->getContent()), "{$type}: the university logo and the clinic logo must both be in the PDF");
        }
    }

    public function test_the_lab_request_pdf_still_draws_its_logo_from_a_file(): void
    {
        $patient = $this->makePatient();
        $labRequest = LabRequest::create([
            'request_number' => 'LR' . random_int(100000, 999999), 'document_creator_id' => $this->admin->id,
            'patient_user_id' => $patient->user_id, 'patient_name' => 'Ana Reyes', 'requested_at' => now()->toDateString(),
            'status' => LabRequest::STATUS_PENDING,
        ]);

        $this->actingAs($this->admin);
        $response = $this->get(route('lab-requests.pdf', $labRequest->id));

        $response->assertOk();
        $this->assertGreaterThanOrEqual(1, $this->embeddedImages($response->getContent()), 'the lab request letterhead logo is loaded from public/');
    }

    public function test_dompdf_never_fetches_anything_over_the_network(): void
    {
        $this->assertFalse(config('dompdf.options.enable_remote'), 'enable_remote must be off: no PDF may make the server fetch a URL');

        $protocols = array_keys(config('dompdf.options.allowed_protocols'));
        sort($protocols);
        $this->assertSame(['data://', 'file://'], $protocols, 'only pictures embedded in the page or files on the clinic PC');

        // A page that names a remote picture is refused before any connection is made (port 9 is the discard port: a fetch
        // attempt would only show up as "Image not found").
        $GLOBALS['_dompdf_warnings'] = [];
        $pdf = Pdf::loadHTML('<html><body><img src="http://127.0.0.1:9/logo.png" width="40"></body></html>')->output();
        $warnings = implode("\n", $GLOBALS['_dompdf_warnings'] ?? []);

        $this->assertSame(0, $this->embeddedImages($pdf));
        $this->assertStringNotContainsString('Image not found', $warnings, 'DomPDF tried to download the picture');
        $this->assertMatchesRegularExpression('/Permission denied|remote file download is disabled/i', $warnings);
    }
}
