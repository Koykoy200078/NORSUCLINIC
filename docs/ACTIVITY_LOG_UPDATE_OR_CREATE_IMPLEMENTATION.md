# Activity Logs Update Implementation

## Overview

Modified the activity logging system to **update existing logs** instead of creating new entries for the same patient/consultation/medical certificate records.

---

## 🔄 Changes Made

### 1. **Core Logging Logic** (`app/Traits/LogsActivity.php`)

#### Modified `logActivity()` Method

-   **Before**: Used `ActivityLog::create()` - always created new logs
-   **After**: Uses `ActivityLog::updateOrCreate()` - updates existing logs or creates new ones

**Unique Identifier Pattern**:

```php
[
    'action' => 'patient_record',        // Generic action name
    'subject_type' => 'Patient',          // Type of record
    'subject_id' => 123                   // ID of the specific record
]
```

#### Updated Action Names (Generic instead of specific)

-   ~~`created_patient`~~ → `patient_record`
-   ~~`created_consultation`~~ → `consultation_record`
-   ~~`created_medical_certificate`~~ → `medical_certificate_record`
-   ~~`updated_patient`~~ → `patient_record` (same as creation)

### 2. **Patient Logging**

#### `logPatientCreation()` - Now works for both create and update

```php
public static function logPatientCreation($patient, $user)
{
    return self::logActivity(
        'patient_record',  // Generic action
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
            'subject_id' => $patient->id,  // Unique identifier
        ]
    );
}
```

#### `logPatientUpdate()` - Updated to match creation

-   Uses same `patient_record` action
-   Includes all patient fields (not just name)
-   Will update the existing log created during patient creation

**Location**: Already called in `app/Repositories/PatientRepository.php` line 189

---

### 3. **Consultation Form Logging**

#### `logConsultationCreation()` - Now works for both create and update

```php
public static function logConsultationCreation($requestDocument)
{
    return self::logActivity(
        'consultation_record',  // Generic action
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
            'subject_type' => 'RequestDocuments',
            'subject_id' => $requestDocument->id,  // Unique identifier
            'date' => $requestDocument->requested_at,
        ]
    );
}
```

#### Added to Update Flow

**Location**: `app/Http/Controllers/RequestDocumentsController.php`

-   `updateConsultationForm()` method (line ~529) - Added log call at end
-   `storeConsultationForm()` - Already had log call (line 303)

---

### 4. **Medical Certificate Logging**

#### `logMedicalCertificateCreation()` - Now works for both create and update

```php
public static function logMedicalCertificateCreation($requestDocument)
{
    return self::logActivity(
        'medical_certificate_record',  // Generic action
        "Medical certificate: {$requestDocument->name}",
        [
            'patient_name' => $requestDocument->name,
            'patient_age' => $requestDocument->age,
            'patient_gender' => $requestDocument->gender,
            'college' => $requestDocument->college,
            'diagnosis' => $requestDocument->complaints_diagnosis,
            'subject_type' => 'RequestDocuments',
            'subject_id' => $requestDocument->id,  // Unique identifier
            'date' => $logDate,
        ]
    );
}
```

#### Added to Update Flow

**Location**: `app/Http/Controllers/RequestDocumentsController.php`

-   `updateMedicalCertificate()` method (line ~465) - Added log call at end
-   `storeMedicalCertificate()` - Already had log call (line 183)

---

## 🎯 How It Works

### Before (Creating Duplicates)

```
Patient ID: 1
- Created: "Created new patient: John Doe"
- Updated: "Updated patient information: John Doe"
- Updated: "Updated patient information: John Doe"
- Updated: "Updated patient information: John Doe"

Result: 4 separate logs for same patient
```

### After (Updating Existing Log)

```
Patient ID: 1
- Latest: "Patient record: John Doe" (updated_at: 2025-12-03 10:30:00)

Result: 1 log, always showing current information
```

---

## 🔍 Unique Identifier Logic

The system identifies existing logs using a **composite key**:

```php
// For Patient
[
    'action' => 'patient_record',
    'subject_type' => 'Patient',
    'subject_id' => 123  // patient.id
]

// For Consultation
[
    'action' => 'consultation_record',
    'subject_type' => 'RequestDocuments',
    'subject_id' => 456  // request_document.id
]

// For Medical Certificate
[
    'action' => 'medical_certificate_record',
    'subject_type' => 'RequestDocuments',
    'subject_id' => 789  // request_document.id
]
```

---

## 📊 Database Behavior

### `updateOrCreate()` Method

```php
ActivityLog::updateOrCreate(
    ['action' => 'patient_record', 'subject_type' => 'Patient', 'subject_id' => 1],  // WHERE clause
    ['patient_name' => 'John Doe', 'address' => 'New Address', ...]  // SET clause
);
```

**SQL Equivalent**:

```sql
-- First tries to find
SELECT * FROM activity_logs
WHERE action = 'patient_record'
  AND subject_type = 'Patient'
  AND subject_id = 1;

-- If found: UPDATE
UPDATE activity_logs
SET patient_name = 'John Doe', address = 'New Address', updated_at = NOW()
WHERE id = 123;

-- If not found: INSERT
INSERT INTO activity_logs (action, subject_type, subject_id, patient_name, address, ...)
VALUES ('patient_record', 'Patient', 1, 'John Doe', 'New Address', ...);
```

---

## ✅ Benefits

1. **No Duplicate Logs**: Each patient/consultation/certificate has only ONE log entry
2. **Always Current**: Log shows latest information from most recent update
3. **Cleaner UI**: Activity logs page shows concise, non-redundant records
4. **Timestamp Tracking**: `created_at` shows first creation, `updated_at` shows last modification
5. **Same Code**: Controllers don't need separate "create log" vs "update log" calls

---

## 🚀 Usage Examples

### Patient Creation

```php
// In PatientRepository::store()
$patient = Patient::create($data);
self::logPatientCreation($patient, $user);
// Creates: activity_logs entry with action='patient_record'
```

### Patient Update

```php
// In PatientRepository::update()
$patient->update($data);
self::logPatientUpdate($patient, $user);
// Updates: existing activity_logs entry with same action/subject_type/subject_id
```

### Consultation Creation

```php
// In RequestDocumentsController::storeConsultationForm()
$requestDocument = RequestDocuments::create($data);
self::logConsultationCreation($requestDocument);
// Creates: activity_logs entry with action='consultation_record'
```

### Consultation Update

```php
// In RequestDocumentsController::updateConsultationForm()
$requestDocument->update($data);
self::logConsultationCreation($requestDocument->fresh());
// Updates: existing activity_logs entry with same action/subject_type/subject_id
```

---

## 📝 Notes

### Why `->fresh()`?

Used `$requestDocument->fresh()` in update methods to ensure we're logging the freshly updated data from the database:

```php
$requestDocument->update([...]);  // Updates database
self::logConsultationCreation($requestDocument->fresh());  // Logs fresh data
```

### Activity Log Index

Database indexes ensure fast lookups:

```php
$table->index('action');
$table->index('subject_type');
$table->index('subject_id');
```

### Description Field

Description text is **generic** now (no "Created" or "Updated"):

-   Before: "Created new patient: John Doe"
-   After: "Patient record: John Doe"

This makes more sense since the log represents the **current state**, not a specific action.

---

## 🧪 Testing Recommendations

1. **Create a new patient** → Check activity log shows "Patient record: [name]"
2. **Update the patient** → Check same log updates, no duplicate created
3. **Create consultation** → Check log shows "Consultation form: [name]"
4. **Edit consultation** → Check same log updates with new data
5. **Create medical cert** → Check log shows "Medical certificate: [name]"
6. **Edit medical cert** → Check same log updates

### Expected Result

Each record (patient, consultation, medical certificate) should have **exactly ONE** corresponding activity log entry that updates whenever the record is modified.

---

## 📌 Modified Files

1. ✅ `app/Traits/LogsActivity.php` - Core logging logic
2. ✅ `app/Repositories/PatientRepository.php` - Already had update logging
3. ✅ `app/Http/Controllers/RequestDocumentsController.php` - Added update logging for consultation and medical certificates

---

## 🎉 Summary

The activity logging system now behaves like a **"snapshot"** of the current record state rather than a **"history"** of all changes. This provides a cleaner, more maintainable activity log that always shows the most recent information without creating duplicate entries.
