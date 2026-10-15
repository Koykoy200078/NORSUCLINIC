<?php

namespace Tests\Feature\Regression;

use App\Mail\PatientRegistrationMail;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Plan Phase 4.1: the clinic is "Medical University Clinic". The old display name ("NORSU Clinic", in any letter case)
 * must not survive in anything a user can read: pages, PDFs, prints, the e-mail, the language file, the seeders, the
 * environment template. What stays on purpose is technical or the university's own name: the `norsu_clinic` database,
 * `norsu_logo.png` / `norsu_clinic_logo.png`, the NORSU e-mail address, "Negros Oriental State University", "NORSU
 * community" / "NORSU ID" (the university), none of which is the two words "NORSU Clinic".
 */
class ClinicNameInSourceTest extends TestCase
{
    use RefreshDatabase;

    private const OLD_NAME = '/norsu[ \t]+clinic/i';

    /** @return array<int, string> "file:line: text" for every line that still says the old name */
    private function offences(): array
    {
        $root = dirname(__DIR__, 3);
        $found = [];

        $files = [$root . '/.env.example'];
        foreach (['app', 'resources/views', 'resources/assets/js', 'lang', 'database/seeders', 'database/migrations', 'routes', 'config'] as $directory) {
            if (! is_dir($root . '/' . $directory)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root . '/' . $directory, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file->isFile() && preg_match('/\.(php|js|json|md|txt)$/', $file->getFilename())) {
                    $files[] = $file->getPathname();
                }
            }
        }

        foreach ($files as $path) {
            // The two migrations that REPLACE the old name necessarily name it: they are the one allowed place.
            if (str_contains(str_replace('\\', '/', $path), 'rename_clinic_to_medical_university_clinic')) {
                continue;
            }
            foreach (file($path) as $number => $line) {
                if (preg_match(self::OLD_NAME, $line)) {
                    $found[] = str_replace('\\', '/', substr($path, strlen($root) + 1)) . ':' . ($number + 1) . ': ' . trim($line);
                }
            }
        }

        return $found;
    }

    public function test_no_page_print_email_seeder_or_template_still_says_norsu_clinic(): void
    {
        $this->assertSame([], $this->offences(), "the old clinic name is still in the source:\n");
    }

    public function test_the_environment_template_names_the_application_medical_university_clinic(): void
    {
        $template = file_get_contents(dirname(__DIR__, 3) . '/.env.example');

        $this->assertMatchesRegularExpression('/^APP_NAME="Medical University Clinic"\s*$/m', $template);
        $this->assertStringContainsString('DB_DATABASE=norsu_clinic', $template, 'the database keeps its technical name');
    }

    public function test_the_welcome_text_and_the_registration_mail_use_the_new_name(): void
    {
        $this->assertSame('Welcome to Medical University Clinic', __('messages.web.welcome_to_norsu_clinic'));

        // A clinic that never saved a name (no clinic_name setting) gets the new default, never the old one.
        $mail = new PatientRegistrationMail(
            new User(['first_name' => 'Test', 'last_name' => 'Patient', 'email' => 'p@test.local']),
            new Patient(['patient_unique_id' => 'P-1'])
        );
        $html = $mail->render();

        $this->assertSame('Welcome to Medical University Clinic - Registration Successful!', $mail->subject);
        $this->assertStringContainsString('Medical University Clinic', $html);
        $this->assertDoesNotMatchRegularExpression(self::OLD_NAME, $html);
        $this->assertStringContainsString('Negros Oriental State University', $html, 'the university name stays');
    }
}
