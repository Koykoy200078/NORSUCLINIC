<?php

namespace App\Traits;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Log an activity with patient/document details
     * Uses updateOrCreate to update existing logs instead of creating duplicates
     *
     * @param string $action The action performed (e.g., 'created_patient', 'created_consultation', 'used_medicine')
     * @param string $description Human-readable description
     * @param array $details Additional details to log
     * @return ActivityLog|null
     */
    public static function logActivity(
        string $action,
        string $description,
        array $details = [],
        bool $append = false
    ): ?ActivityLog {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        // Determine user type
        $userType = self::getUserType($user);

        // Auto-calculate age if date_of_birth is provided and age is not
        if (isset($details['date_of_birth']) && !isset($details['patient_age'])) {
            $details['patient_age'] = self::calculateAge($details['date_of_birth']);
        }

        // Combine course and year_level if both exist
        if (isset($details['course']) && isset($details['year_level'])) {
            $details['course_section'] = $details['course'] . ' - ' . $details['year_level'];
        }

        // Build unique identifier for finding existing logs
        // Use action + subject_type + subject_id as unique key
        $uniqueIdentifier = [
            'action' => $action,
            'subject_type' => $details['subject_type'] ?? null,
            'subject_id' => $details['subject_id'] ?? null,
        ];

        // Remove null values from unique identifier
        $uniqueIdentifier = array_filter($uniqueIdentifier, fn($value) => $value !== null);

        $logData = [
            'user_id' => $user->id,
            'user_type' => $userType,
            'user_name' => $user->full_name,
            'description' => $description,
            'date' => $details['date'] ?? now()->toDateString(),
            'patient_name' => $details['patient_name'] ?? null,
            'patient_age' => $details['patient_age'] ?? null,
            'patient_gender' => $details['patient_gender'] ?? null,
            'college' => $details['college'] ?? null,
            'address' => $details['address'] ?? null,
            'contact_number' => $details['contact_number'] ?? null,
            'complaints' => $details['complaints'] ?? null,
            'diagnosis' => $details['diagnosis'] ?? null,
            'informant' => $details['informant'] ?? null,
            'consult_mode' => $details['consult_mode'] ?? null,
            'course_section' => $details['course_section'] ?? null,
            'properties' => $details['properties'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        // Append-only events (procurement, usage) must create a NEW row each time; otherwise
        // they collapse onto one row keyed by action+subject and overwrite history. AUDIT-2.
        if ($append) {
            return ActivityLog::create(array_merge([
                'action' => $action,
                'subject_type' => $details['subject_type'] ?? null,
                'subject_id' => $details['subject_id'] ?? null,
            ], $logData));
        }

        // Use updateOrCreate to update existing log or create new one
        return ActivityLog::updateOrCreate($uniqueIdentifier, $logData);
    }

    /**
     * Log patient creation or update
     */
    public static function logPatientCreation($patient, $user)
    {
        return self::logActivity(
            'patient_record', // Changed from 'created_patient' to generic action
            "Patient record: {$user->full_name}",
            [
                'patient_name' => $user->full_name,
                'date_of_birth' => $user->dob,
                'patient_gender' => $user->gender == User::MALE ? 'Male' : 'Female',
                'college' => $user->college->name ?? null,
                'address' => $user->address->address ?? null,
                'contact_number' => $user->contact,
                'course' => $user->course->name ?? null,
                'year_level' => $user->yearLevel->name ?? null,
                'subject_type' => 'Patient',
                'subject_id' => $patient->id,
            ]
        );
    }

    /**
     * Log consultation form creation or update
     */
    public static function logConsultationCreation($requestDocument)
    {
        return self::logActivity(
            'consultation_record', // Changed from 'created_consultation' to generic action
            "Consultation form: {$requestDocument->name}",
            [
                'patient_name' => $requestDocument->name,
                'patient_age' => $requestDocument->age,
                'patient_gender' => $requestDocument->gender,
                'college' => $requestDocument->college,
                'address' => $requestDocument->address,
                'contact_number' => $requestDocument->patient_contact,
                'complaints' => $requestDocument->complaints,
                'diagnosis' => $requestDocument->assessment,
                'informant' => $requestDocument->informant,
                'consult_mode' => $requestDocument->consult_mode,
                'course' => $requestDocument->course,
                'year_level' => $requestDocument->year_level,
                'subject_type' => 'RequestDocuments',
                'subject_id' => $requestDocument->id,
                'date' => $requestDocument->requested_at ?? now()->toDateString(),
            ]
        );
    }

    /**
     * Log medical certificate creation or update
     */
    public static function logMedicalCertificateCreation($requestDocument)
    {
        // Parse examined_on to get a single date for logging
        $logDate = $requestDocument->examined_on ?? now()->toDateString();

        // If examined_on contains pipe-delimited dates (range or multiple), extract the first date
        if (is_string($logDate) && (strpos($logDate, '|') !== false || strpos($logDate, ',') !== false)) {
            // For range: "2025-10-13|2025-10-16|range" -> get first date
            if (strpos($logDate, '|') !== false) {
                $parts = explode('|', $logDate);
                $logDate = $parts[0];
            }
            // For multiple: "2025-10-12,2025-10-14,2025-10-16|multiple" -> get first date
            if (strpos($logDate, ',') !== false) {
                $parts = explode(',', $logDate);
                $logDate = $parts[0];
            }
        }

        return self::logActivity(
            'medical_certificate_record', // Changed from 'created_medical_certificate' to generic action
            "Medical certificate: {$requestDocument->name}",
            [
                'patient_name' => $requestDocument->name,
                'patient_age' => $requestDocument->age,
                'patient_gender' => $requestDocument->gender,
                'college' => $requestDocument->college,
                'address' => $requestDocument->address,
                'contact_number' => $requestDocument->patient_contact,
                'diagnosis' => $requestDocument->complaints_diagnosis,
                'course' => $requestDocument->course,
                'year_level' => $requestDocument->year_level,
                'subject_type' => 'RequestDocuments',
                'subject_id' => $requestDocument->id,
                'date' => $logDate,
            ]
        );
    }

    /**
     * Log medicine procurement
     */
    public static function logMedicineProcurement($medicine, $quantity, $additionalDetails = [])
    {
        return self::logActivity(
            'procured_medicine',
            "Stocked in {$quantity} units of {$medicine->name}",
            array_merge([
                'subject_type' => 'Medicine',
                'subject_id' => $medicine->id,
                'properties' => [
                    'medicine_name' => $medicine->name,
                    'quantity' => $quantity,
                    'batch_no' => $additionalDetails['batch_no'] ?? null,
                    'expiry_date' => $additionalDetails['expiry_date'] ?? null,
                ],
            ], $additionalDetails),
            true // append: keep every procurement as its own audit row. AUDIT-2.
        );
    }

    /**
     * Log medicine usage
     */
    public static function logMedicineUsage($medicine, $quantity, $patientName, $requestDocument = null)
    {
        $details = [
            'patient_name' => $patientName,
            'subject_type' => 'Medicine',
            'subject_id' => $medicine->id,
            'properties' => [
                'medicine_name' => $medicine->name,
                'quantity' => $quantity,
            ],
        ];

        if ($requestDocument) {
            $details['patient_age'] = $requestDocument->age;
            $details['patient_gender'] = $requestDocument->gender;
            $details['college'] = $requestDocument->college;
            $details['diagnosis'] = $requestDocument->assessment;
        }

        return self::logActivity(
            'used_medicine',
            "Used {$quantity} units of {$medicine->name} for patient: {$patientName}",
            $details,
            true // append: keep every dispensing event as its own audit row. AUDIT-2.
        );
    }

    /**
     * Log patient update (will update the same log as patient creation)
     */
    public static function logPatientUpdate($patient, $user)
    {
        return self::logActivity(
            'patient_record', // Use same action as creation - will update existing log
            "Patient record: {$user->full_name}",
            [
                'patient_name' => $user->full_name,
                'date_of_birth' => $user->dob,
                'patient_gender' => $user->gender == User::MALE ? 'Male' : 'Female',
                'college' => $user->college->name ?? null,
                'address' => $user->address->address ?? null,
                'contact_number' => $user->contact,
                'course' => $user->course->name ?? null,
                'year_level' => $user->yearLevel->name ?? null,
                'subject_type' => 'Patient',
                'subject_id' => $patient->id,
            ]
        );
    }

    /**
     * Log patient deletion
     */
    public static function logPatientDeletion($patient, $userName)
    {
        return self::logActivity(
            'deleted_patient',
            "Deleted patient: {$userName}",
            [
                'patient_name' => $userName,
                'subject_type' => 'Patient',
                'subject_id' => $patient->id,
            ]
        );
    }

    /**
     * Get user type from user model
     */
    private static function getUserType(User $user): string
    {
        $role = $user->roles->first();

        if (!$role) {
            return 'unknown';
        }

        return match ($role->name) {
            'clinic_admin' => 'admin',
            'doctor' => 'doctor',
            'staff' => 'staff',
            default => strtolower($role->name)
        };
    }

    /**
     * Calculate age from date of birth
     */
    private static function calculateAge($dateOfBirth): ?int
    {
        if (!$dateOfBirth) {
            return null;
        }

        try {
            $dob = \Carbon\Carbon::parse($dateOfBirth);
            return $dob->age;
        } catch (\Exception $e) {
            return null;
        }
    }
}
