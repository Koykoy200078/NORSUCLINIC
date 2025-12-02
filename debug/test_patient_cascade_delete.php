<?php

use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\Appointment;
use App\Models\RequestDocuments;
use App\Models\Prescription;
use App\Models\Visit;
use App\Models\MedicineBill;
use App\Models\ActivityLog;
use App\Models\User;

// This script tests if patient cascade deletion is working properly
// Run this script from Laravel tinker or create a route to test it

function testPatientCascadeDelete($patientId)
{
    echo "=== PATIENT CASCADE DELETE TEST ===\n\n";

    // Find the patient
    $patient = Patient::find($patientId);
    if (!$patient) {
        echo "Patient with ID {$patientId} not found.\n";
        return;
    }

    echo "Testing deletion for Patient: {$patient->user->full_name} (ID: {$patient->id})\n";
    echo "User ID: {$patient->user_id}\n\n";

    // Count related records before deletion
    echo "=== BEFORE DELETION ===\n";
    echo "Patient Queue Entries: " . PatientQueue::where('patient_id', $patient->id)->count() . "\n";
    echo "Appointments: " . Appointment::where('patient_id', $patient->id)->count() . "\n";
    echo "Request Documents: " . RequestDocuments::where('user_id', $patient->user_id)->count() . "\n";
    echo "Prescriptions: " . Prescription::where('patient_id', $patient->id)->count() . "\n";
    echo "Visits: " . Visit::where('patient_id', $patient->id)->count() . "\n";
    echo "Medicine Bills: " . MedicineBill::where('patient_id', $patient->id)->count() . "\n";
    echo "Activity Logs (Patient): " . ActivityLog::where('subject_type', 'App\Models\Patient')->where('subject_id', $patient->id)->count() . "\n";
    echo "Activity Logs (User): " . ActivityLog::where('user_id', $patient->user_id)->count() . "\n";
    echo "User Record: " . (User::find($patient->user_id) ? 'EXISTS' : 'NOT FOUND') . "\n";

    echo "\n=== PERFORMING DELETION ===\n";

    // Perform the deletion
    try {
        $patient->delete();
        echo "Patient deleted successfully!\n\n";
    } catch (Exception $e) {
        echo "Error during deletion: " . $e->getMessage() . "\n";
        return;
    }

    // Count related records after deletion
    echo "=== AFTER DELETION ===\n";
    echo "Patient Queue Entries: " . PatientQueue::where('patient_id', $patientId)->count() . "\n";
    echo "Appointments: " . Appointment::where('patient_id', $patientId)->count() . "\n";
    echo "Request Documents: " . RequestDocuments::where('user_id', $patient->user_id)->count() . "\n";
    echo "Prescriptions: " . Prescription::where('patient_id', $patientId)->count() . "\n";
    echo "Visits: " . Visit::where('patient_id', $patientId)->count() . "\n";
    echo "Medicine Bills: " . MedicineBill::where('patient_id', $patientId)->count() . "\n";
    echo "Activity Logs (Patient): " . ActivityLog::where('subject_type', 'App\Models\Patient')->where('subject_id', $patientId)->count() . "\n";
    echo "Activity Logs (User): " . ActivityLog::where('user_id', $patient->user_id)->count() . "\n";
    echo "User Record: " . (User::find($patient->user_id) ? 'EXISTS' : 'NOT FOUND') . "\n";
    echo "Patient Record: " . (Patient::find($patientId) ? 'EXISTS' : 'NOT FOUND') . "\n";

    echo "\n=== TEST COMPLETE ===\n";
    echo "All counts should be 0 and records should be 'NOT FOUND' for proper cascade deletion.\n";
}

// Uncomment and set a patient ID to test
// testPatientCascadeDelete(1);
