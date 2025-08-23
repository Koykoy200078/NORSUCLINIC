<?php

namespace App\Console\Commands;

use App\Mail\PatientRegistrationMail;
use App\Models\Patient;
use Illuminate\Console\Command;

class TestEmailRender extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-render';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email template rendering';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Email Template Rendering...');

        try {
            // Get a sample patient
            $patient = Patient::first();
            if (!$patient) {
                $this->error('No patient found in database.');
                return 1;
            }

            $user = $patient->user;
            if (!$user) {
                $this->error('Patient has no associated user.');
                return 1;
            }

            $this->info("Using patient: {$patient->first_name} {$patient->last_name}");

            // Test components
            $this->info('Testing components:');

            // Test getAppLogo
            $logo = getAppLogo();
            $this->info("✓ Logo: {$logo}");

            // Test settings
            $clinicName = getSettingValue('clinic_name');
            $this->info("✓ Clinic name: {$clinicName}");

            // Test slider
            $slider = \App\Models\Slider::first();
            if ($slider) {
                $this->info("✓ Slider title: {$slider->title}");
                $this->info("✓ Slider description: {$slider->short_description}");
            }

            // Create mail instance
            $mail = new PatientRegistrationMail($user, $patient);
            $this->info('✓ Mail instance created successfully');

            // Test if we can render the template
            $view = view('emails.patient-registration')
                ->with([
                    'patientName' => $user->first_name . ' ' . $user->last_name,
                    'patientId' => $patient->patient_unique_id,
                    'email' => $user->email,
                    'registrationDate' => now()->setTimezone('Asia/Manila')->format('F j, Y g:i A'),
                    'slider' => $slider,
                ]);

            $rendered = $view->render();
            $this->info('✓ Email template rendered successfully!');
            $this->info('Template size: ' . strlen($rendered) . ' characters');

            // Check if logo is in the rendered template
            if (strpos($rendered, $logo) !== false) {
                $this->info('✓ Logo path found in rendered template');
            } else {
                $this->warn('⚠ Logo path not found in rendered template');
            }
        } catch (\Exception $e) {
            $this->error("✗ Error: {$e->getMessage()}");
            $this->error("File: {$e->getFile()}:{$e->getLine()}");
            return 1;
        }

        return 0;
    }
}
