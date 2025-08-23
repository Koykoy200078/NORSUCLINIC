<?php

require_once 'bootstrap/app.php';

use App\Mail\PatientRegistrationMail;
use App\Models\User;
use App\Models\Patient;

try {
    // Get a sample user and patient
    $user = User::where('role', 'patient')->first();
    $patient = Patient::first();

    if (!$user || !$patient) {
        echo "Creating test data...\n";
        // Create test data if needed
        $user = User::factory()->create(['role' => 'patient']);
        $patient = Patient::factory()->create(['user_id' => $user->id]);
    }

    echo "Testing PatientRegistrationMail...\n";

    // Create the mail instance
    $mail = new PatientRegistrationMail($user, $patient);

    echo "Mail class instantiated successfully!\n";
    echo "Subject: " . $mail->subject . "\n";

    // Test the view rendering
    $view = $mail->render();

    echo "Email template rendered successfully!\n";
    echo "Template contains clinic name: " . (strpos($view, getSettingValue('clinic_name', 'Norsu Clinic')) !== false ? 'YES' : 'NO') . "\n";
    echo "Template contains contact info: " . (strpos($view, getSettingValue('contact_no', '')) !== false ? 'YES' : 'NO') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
