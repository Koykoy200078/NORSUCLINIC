<?php

namespace App\Traits;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Log an activity with patient/document details (append-only: every call adds a row)
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
        bool $append = true // kept for backward compatibility; logging is always append-only now
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

        // The trail is APPEND-ONLY. It used to updateOrCreate() on action + subject, so every edit of a
        // consultation, certificate, patient or lab request REPLACED the previous log row and who
        // changed what, and when, was lost. Each call now writes a new row; a record log that follows an
        // earlier one for the same record is labelled as an update so the history reads correctly. M-05.
        $subjectType = $details['subject_type'] ?? null;
        $subjectId = $details['subject_id'] ?? null;

        if (str_ends_with($action, '_record') && $subjectType !== null && $subjectId !== null) {
            $alreadyLogged = ActivityLog::where('action', $action)
                ->where('subject_type', $subjectType)
                ->where('subject_id', $subjectId)
                ->exists();

            $description = ($alreadyLogged ? 'Updated - ' : 'Created - ') . $description;
        }

        $properties = $details['properties'] ?? null;
        $impersonator = session()->get('impersonated_by');
        if ($impersonator) {
            $properties = array_merge((array) $properties, ['impersonated_by' => $impersonator]);
        }

        return ActivityLog::create([
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
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
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log patient creation or update
     */
    public static function logPatientCreation($patient, $user)
    {
        return self::logActivity(
            'patient_record', // Changed from 'created_patient' to generic action
            "Patient record: {$user->full_name}",
            self::patientRecordDetails($patient, $user)
        );
    }

    /**
     * Snapshot of the patient identity fields written to the audit row. The college / course / year
     * level / address columns are college_name, course_name, year_level_name and the patient's own
     * address (it is owned by the patient, not the user); the old code read non-existent properties so
     * those fields were always blank.
     */
    private static function patientRecordDetails($patient, $user): array
    {
        $address = $patient->address;

        return [
            'patient_name' => $user->full_name,
            'date_of_birth' => $user->dob,
            'patient_gender' => $user->gender == User::MALE ? 'Male' : 'Female',
            'college' => $user->college?->college_name,
            'address' => ($address?->full_address ?: $address?->address1) ?: null,
            'contact_number' => $user->contact,
            'course' => $user->course?->course_name,
            'year_level' => $user->yearLevel?->year_level_name,
            'subject_type' => 'Patient',
            'subject_id' => $patient->id,
        ];
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
     * Audit row for a prescription event: 'saved' (created or edited), 'cancelled', 'reactivated',
     * 'dispensed' or 'deleted'. A prescription entered by someone other than its doctor (staff on a verbal /
     * phone order, or the admin) records who entered it. R3-H4 / R3-M5.
     */
    public static function logPrescriptionEvent($prescription, string $event)
    {
        $actor = Auth::user();
        $patientName = $prescription->patient?->user?->full_name ?? ('Patient #' . $prescription->patient_id);
        $doctorUser = $prescription->doctor?->user;
        $doctorName = $doctorUser?->full_name;

        $verbalOrder = $event === 'saved'
            && $actor
            && $doctorUser
            && (int) $actor->id !== (int) $doctorUser->id
            && $actor->hasAnyRole(['staff', 'nurse']);

        $properties = [
            'prescription_id' => $prescription->id,
            'doctor_id' => $prescription->doctor_id,
            'doctor_name' => $doctorName,
            'entered_by' => $actor?->id,
            'verbal_order' => $verbalOrder,
        ];

        if ($event === 'deleted') {
            $properties['medicines'] = $prescription->getMedicine->map(fn ($row) => [
                'medicine' => $row->medicines?->name,
                'dosage' => $row->dosage,
                'quantity' => (int) $row->total_quantity,
            ])->all();
        }

        $label = [
            'saved' => 'Prescription',
            'cancelled' => 'Cancelled prescription',
            'reactivated' => 'Reactivated prescription',
            'dispensed' => 'Dispensed prescription',
            'deleted' => 'Deleted prescription',
        ][$event] ?? 'Prescription';

        $description = "{$label} #{$prescription->id} for {$patientName}" . ($doctorName ? " (doctor {$doctorName})" : '');
        if ($verbalOrder) {
            $description .= " - entered by {$actor->full_name} on the verbal / phone order of the doctor";
        }

        return self::logActivity(
            $event === 'saved' ? 'prescription_record' : 'prescription_' . $event,
            $description,
            [
                'patient_name' => $patientName,
                'subject_type' => 'Prescription',
                'subject_id' => $prescription->id,
                'date' => now()->toDateString(),
                'properties' => $properties,
            ]
        );
    }

    /**
     * Log that a consultation / certificate / excuse slip was deleted. The row keeps a snapshot of the
     * record (who it was for, what it said, which medicines it had used) because the record itself is gone.
     */
    public static function logDocumentDeletion($requestDocument)
    {
        $label = match ($requestDocument->document_type) {
            'consultation_form' => 'consultation form',
            'medical_certificate' => 'medical certificate',
            'excuse_slip' => 'excuse slip',
            default => 'document',
        };

        $medicines = $requestDocument->consultationMedicines()->with('medicine:id,name')->get()
            ->map(fn ($row) => [
                'medicine' => $row->medicine?->name,
                'dosage' => $row->dosage,
                'quantity' => (int) $row->quantity,
                'used_for' => $row->used_for,
            ])->all();

        return self::logActivity(
            'document_deleted',
            "Deleted {$label}: {$requestDocument->name}",
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
                'properties' => [
                    'document_type' => $requestDocument->document_type,
                    'created_by' => $requestDocument->document_creator_id,
                    'created_at' => optional($requestDocument->created_at)->toDateTimeString(),
                    'plan' => $requestDocument->plan,
                    'medicines' => $medicines,
                ],
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
            self::patientRecordDetails($patient, $user)
        );
    }

    /**
     * Log patient deletion
     */
    public static function logPatientDeletion($patient, $userName)
    {
        return self::logActivity(
            'deleted_patient',
            "Archived patient: {$userName}",
            [
                'patient_name' => $userName,
                'subject_type' => 'Patient',
                'subject_id' => $patient->id,
            ]
        );
    }

    /**
     * Log that an archived patient was restored
     */
    public static function logPatientRestoration($patient, $userName)
    {
        return self::logActivity(
            'restored_patient',
            "Restored patient: {$userName}",
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
