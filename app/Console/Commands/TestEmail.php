<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\PatientRegistrationMail;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the patient registration email template';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Updated Email System...');

        try {
            // Test getAppLogo function
            $logo = getAppLogo();
            $this->info("✓ getAppLogo() working: {$logo}");

            // Get a sample user and patient (using relationship instead of role column)
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

            // Get slider data
            $slider = \App\Models\Slider::first();
            if ($slider) {
                $this->info("✓ Slider found:");
                $this->info("  - Title: {$slider->title}");
                $this->info("  - Description: {$slider->short_description}");
                $this->info("  - Is Default: " . ($slider->is_default ? 'Yes' : 'No'));
            } else {
                $this->warn("No slider found in database");
            }

            $this->info("Using patient: {$patient->first_name} {$patient->last_name}");

            // Create the mail instance
            $mail = new PatientRegistrationMail($user, $patient);

            $this->info('✓ Mail class instantiated successfully!');
            $this->info("Subject: {$mail->subject}");

            // Test settings
            $clinicName = getSettingValue('clinic_name', 'Default Clinic');
            $this->info("✓ Clinic name: {$clinicName}");

            $this->info('✓ Email system with slider integration is working!');
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            $this->error("File: {$e->getFile()}:{$e->getLine()}");
            return 1;
        }

        return 0;
    }
}
