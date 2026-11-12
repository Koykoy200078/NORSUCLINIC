<?php

namespace Tests\Feature\Regression;

use App\Models\DocumentIssuance;
use App\Models\User;
use Database\Seeders\SettingTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * A consultation, a medical certificate or an excuse slip whose optional fields were left empty (NULL in the database) must open
 * on every page without handing NULL to a string function (trim, explode, strtolower ...). PHP 8.1 – 8.5 only report that as a
 * deprecation, which Laravel ignores; PHP 9 makes it a TypeError, and the page would fail. Upgrade plan U3, item 3 (decided by
 * the owner on 2026-11-12: cast to string).
 *
 * Laravel ignores deprecations while the tests run, so this test collects them itself.
 */
class EmptyDocumentFieldsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $admin;

    /** @var list<string> */
    private array $deprecations = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->admin = $this->makeAdmin();
        $this->seed(SettingTableSeeder::class);
    }

    protected function tearDown(): void
    {
        restore_error_handler();
        parent::tearDown();
    }

    /**
     * Records every deprecation the application's own code (app/, the views) raises from here on; anything else goes to the
     * handler that was there before. Deprecations inside vendor/ are a package's to fix (on PHP 8.4, Ziggy 1.8's implicitly
     * nullable constructor parameter is one) and are not what this test is about.
     */
    private function collectDeprecations(): void
    {
        $previous = set_error_handler(function (int $level, string $message, string $file = '', int $line = 0) use (&$previous) {
            if ($level === E_DEPRECATED || $level === E_USER_DEPRECATED) {
                if (! str_starts_with(str_replace('\\', '/', $file), str_replace('\\', '/', base_path('vendor')) . '/')) {
                    $this->deprecations[] = $this->sourceOf($file) . ':' . $line . ' ' . $message;
                }

                return true;
            }

            return $previous ? $previous($level, $message, $file, $line) : false;
        });
    }

    /** A compiled Blade file names its source in a trailing PATH comment; show that instead of the cache file. */
    private function sourceOf(string $file): string
    {
        if (str_contains($file, 'framework') && is_file($file) && preg_match('#/\*\*PATH (.+?) ENDPATH\*\*/#', (string) file_get_contents($file), $match) === 1) {
            return 'view ' . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $match[1]) . ' (compiled line)';
        }

        return str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);
    }

    /** @return array<string, array{string}> */
    public static function documentTypes(): array
    {
        return [
            'consultation' => ['consultation_form'],
            'medical certificate' => ['medical_certificate'],
            'excuse slip' => ['excuse_slip'],
        ];
    }

    #[DataProvider('documentTypes')]
    public function test_a_document_with_empty_optional_fields_opens_everywhere_without_a_null_string_notice(string $type): void
    {
        $patient = $this->makePatient();
        // Only what a save needs; every optional text field (vital signs, complaints, diagnosis, remarks, consult mode ...) is NULL.
        $document = DocumentIssuance::create([
            'document_type' => $type, 'document_creator_id' => $this->admin->id, 'user_id' => $patient->user_id,
            'name' => 'Bare Minimum', 'age' => 0, 'gender' => '', 'address' => '', 'requested_at' => now()->toDateString(),
        ]);

        $this->actingAs($this->admin);
        $this->collectDeprecations();

        $pages = [
            'view' => route('document-issuances.show', $document),
            'edit' => route('document-issuances.edit', $document),
            'pdf' => route('document-issuances.export-pdf', $document),
            'print' => route('document-issuances.export-pdf', [$document, 'action' => 'print']),
        ];

        foreach ($pages as $page => $url) {
            $response = $this->get($url);
            $this->assertSame(200, $response->getStatusCode(), "{$type} {$page}: did not open");
        }

        $this->assertSame([], array_values(array_unique($this->deprecations)), "{$type}: NULL handed to a string function");
    }
}
