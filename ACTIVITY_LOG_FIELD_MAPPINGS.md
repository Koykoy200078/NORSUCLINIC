# Activity Log Field Mappings

## Overview

This document shows exactly which fields from your existing system are logged for each activity type.

---

## 1. Patient Creation (`created_patient`)

### Source: `patients` table + `users` table

| Activity Log Field | Source Field                                          | Notes                        |
| ------------------ | ----------------------------------------------------- | ---------------------------- |
| `date`             | Current date                                          | Auto-set to today            |
| `patient_name`     | `users.first_name` + `users.last_name`                | Combined full name           |
| `patient_age`      | **Auto-calculated** from `users.dob`                  | Age in years                 |
| `patient_gender`   | `users.gender`                                        | Converted to 'Male'/'Female' |
| `college`          | `colleges.college_name`                               | Via `users.college_id`       |
| `address`          | `addresses.address`                                   | Via patient->user->address   |
| `contact_number`   | `users.contact`                                       | Direct field                 |
| `course_section`   | `courses.course_name` + `year_levels.year_level_name` | Auto-combined                |
| `diagnosis`        | NULL                                                  | N/A for patient creation     |
| `complaints`       | NULL                                                  | N/A for patient creation     |
| `informant`        | NULL                                                  | N/A for patient creation     |
| `consult_mode`     | NULL                                                  | N/A for patient creation     |

### Example Log Entry

```json
{
    "action": "created_patient",
    "patient_name": "Juan Dela Cruz",
    "patient_age": 21,
    "patient_gender": "Male",
    "college": "College of Engineering",
    "course_section": "BSCS - 3rd Year",
    "address": "123 Main St, Dumaguete City",
    "contact_number": "09171234567",
    "date": "2025-10-16"
}
```

---

## 2. Consultation Form Creation (`created_consultation`)

### Source: `request_documents` table (document_type = 'consultation_form')

| Activity Log Field | Source Field                   | Auto-Calculated?                     |
| ------------------ | ------------------------------ | ------------------------------------ |
| `date`             | `requested_at` or current date | ✓                                    |
| `patient_name`     | `name`                         | -                                    |
| `patient_age`      | `age`                          | **✓ if NULL** (from `date_of_birth`) |
| `patient_gender`   | `gender`                       | -                                    |
| `college`          | `college`                      | -                                    |
| `address`          | `address`                      | -                                    |
| `contact_number`   | `patient_contact`              | -                                    |
| `course_section`   | `course` + `year_level`        | ✓ Auto-combined                      |
| `complaints`       | `complaints`                   | -                                    |
| `diagnosis`        | `assessment`                   | Mapped from assessment field         |
| `informant`        | `informant`                    | -                                    |
| `consult_mode`     | `consult_mode`                 | -                                    |

### Example Log Entry

```json
{
    "action": "created_consultation",
    "patient_name": "Maria Santos",
    "patient_age": 19,
    "patient_gender": "Female",
    "college": "College of Arts and Sciences",
    "course_section": "BSBio - 2nd Year",
    "address": "456 Rizal Ave, Dumaguete",
    "contact_number": "09181234567",
    "complaints": "Headache, fever, body pain",
    "diagnosis": "Upper Respiratory Tract Infection",
    "informant": "Self",
    "consult_mode": "Walk-in",
    "date": "2025-10-16"
}
```

---

## 3. Medical Certificate Creation (`created_medical_certificate`)

### Source: `request_documents` table (document_type = 'medical_certificate')

| Activity Log Field | Source Field                  | Notes                        |
| ------------------ | ----------------------------- | ---------------------------- |
| `date`             | `examined_on` or current date | Examination date             |
| `patient_name`     | `name`                        | -                            |
| `patient_age`      | `age`                         | **Auto-calculated if NULL**  |
| `patient_gender`   | `gender`                      | -                            |
| `college`          | `college`                     | -                            |
| `address`          | `address`                     | -                            |
| `contact_number`   | `patient_contact`             | -                            |
| `course_section`   | `course` + `year_level`       | Auto-combined                |
| `diagnosis`        | `complaints_diagnosis`        | Medical cert diagnosis field |
| `complaints`       | NULL                          | N/A for medical cert         |
| `informant`        | NULL                          | N/A for medical cert         |
| `consult_mode`     | NULL                          | N/A for medical cert         |

### Example Log Entry

```json
{
    "action": "created_medical_certificate",
    "patient_name": "Pedro Reyes",
    "patient_age": 22,
    "patient_gender": "Male",
    "college": "College of Business",
    "course_section": "BSBA - 4th Year",
    "diagnosis": "Acute Gastroenteritis",
    "date": "2025-10-16"
}
```

---

## 4. Medicine Procurement (`procured_medicine`)

### Source: `purchase_medicines` + `medicines` tables

| Activity Log Field | Source Field          | Stored In           |
| ------------------ | --------------------- | ------------------- |
| `date`             | Current date          | -                   |
| `patient_name`     | NULL                  | N/A                 |
| `patient_age`      | NULL                  | N/A                 |
| `patient_gender`   | NULL                  | N/A                 |
| `college`          | NULL                  | N/A                 |
| `address`          | NULL                  | N/A                 |
| `contact_number`   | NULL                  | N/A                 |
| `course_section`   | NULL                  | N/A                 |
| `diagnosis`        | NULL                  | N/A                 |
| `complaints`       | NULL                  | N/A                 |
| `informant`        | NULL                  | N/A                 |
| `consult_mode`     | NULL                  | N/A                 |
| `properties`       | Medicine details JSON | `properties` column |

### Properties JSON Structure

```json
{
    "medicine_name": "Paracetamol 500mg",
    "quantity": 100,
    "batch_no": "BATCH123",
    "expiry_date": "2026-12-31"
}
```

### Example Log Entry

```json
{
    "action": "procured_medicine",
    "description": "Procured 100 units of Paracetamol 500mg",
    "properties": {
        "medicine_name": "Paracetamol 500mg",
        "quantity": 100,
        "batch_no": "BATCH123",
        "expiry_date": "2026-12-31"
    },
    "date": "2025-10-16"
}
```

---

## 5. Medicine Usage (`used_medicine`)

### Source: `consultation_medicines` + `request_documents` tables

| Activity Log Field | Source Field                   | Notes                      |
| ------------------ | ------------------------------ | -------------------------- |
| `date`             | Current date                   | -                          |
| `patient_name`     | `request_documents.name`       | Patient receiving medicine |
| `patient_age`      | `request_documents.age`        | -                          |
| `patient_gender`   | `request_documents.gender`     | -                          |
| `college`          | `request_documents.college`    | -                          |
| `diagnosis`        | `request_documents.assessment` | Consultation diagnosis     |
| `properties`       | Medicine usage details JSON    | `properties` column        |

### Properties JSON Structure

```json
{
    "medicine_name": "Paracetamol 500mg",
    "quantity": 2
}
```

### Example Log Entry

```json
{
    "action": "used_medicine",
    "description": "Used 2 units of Paracetamol 500mg for patient: Maria Santos",
    "patient_name": "Maria Santos",
    "patient_age": 19,
    "patient_gender": "Female",
    "college": "College of Arts and Sciences",
    "diagnosis": "Upper Respiratory Tract Infection",
    "properties": {
        "medicine_name": "Paracetamol 500mg",
        "quantity": 2
    },
    "date": "2025-10-16"
}
```

---

## Special Field Behaviors

### Auto-Calculated Age

**When**: `patient_age` is NULL but `date_of_birth` exists
**How**:

```php
$age = Carbon::parse($date_of_birth)->age;
```

**Example**:

-   DOB: 2003-05-15
-   Current Date: 2025-10-16
-   Calculated Age: 22

### Course/Section Combination

**When**: Both `course` and `year_level` exist
**How**:

```php
$course_section = $course . ' - ' . $year_level;
```

**Examples**:

-   "BSCS - 3rd Year"
-   "BSN - 1st Year"
-   "BSBA - 4th Year"

### Contact Number Mapping

**Sources** (in order of preference):

1. `request_documents.patient_contact` (for consultations/certificates)
2. `users.contact` (for patient creation)

### Diagnosis Mapping

**Sources**:

-   Consultation Form: `request_documents.assessment`
-   Medical Certificate: `request_documents.complaints_diagnosis`

---

## NULL vs Empty String

The system properly handles:

-   **NULL**: Field doesn't apply to this action type
-   **Empty String**: Field applies but wasn't filled in
-   **Calculated**: Auto-generated from other fields

This allows you to distinguish between "not applicable" and "not provided" in your logs.

---

## Filtering in UI

### User Type Filter

-   `admin` → "Clinic Admin"
-   `doctor` → "Doctor"
-   `staff` → "Staff"

### Action Filter

Displays all unique actions in the database:

-   Created Patient
-   Updated Patient
-   Created Consultation
-   Created Medical Certificate
-   Procured Medicine
-   Used Medicine

### Date Filter

-   Date From: Inclusive start date
-   Date To: Inclusive end date
-   Searches the `date` field (not `created_at`)

### Search

Searches across:

-   `patient_name`
-   `description`
-   `user_name`

---

## CSV Export Columns

When you export activity logs, the CSV includes:

1. Date
2. Time
3. User (who performed action)
4. User Type
5. Action
6. Patient Name
7. Age
8. Gender
9. College
10. Course/Section
11. Address
12. Contact Number
13. Complaints
14. Diagnosis
15. Informant
16. Consult Mode
17. Description

---

## Summary

✅ **Patient fields**: All captured during patient creation and consultation
✅ **Age**: Auto-calculated from date of birth when NULL
✅ **Course/Section**: Auto-combined from separate fields
✅ **Diagnosis**: Mapped from different source fields depending on document type
✅ **Contact**: Properly extracted from relevant tables
✅ **Medicine**: Stored in JSON for flexible data structure

All fields from your requirements are now being logged! 🎉
