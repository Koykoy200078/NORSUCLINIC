# Patient Cascade Deletion System

## Overview

The patient deletion system has been enhanced to automatically clean up ALL related data when a patient is deleted. This prevents orphaned records and ensures database integrity.

## What Gets Deleted When a Patient is Deleted

### 1. Direct Patient Relationships

-   **Appointments** (`appointments` table)

    -   All appointments for the patient
    -   Foreign key: `patient_id`

-   **Patient Queue Entries** (`patient_queues` table)

    -   All queue entries (waiting, in-progress, completed, cancelled)
    -   Foreign key: `patient_id`

-   **Prescriptions** (`prescriptions` table)

    -   All prescriptions issued to the patient
    -   Foreign key: `patient_id`

-   **Visits** (`visits` table)

    -   All visit records for the patient
    -   Foreign key: `patient_id`

-   **Medicine Bills** (`medicine_bills` table)
    -   All billing records for medicines given to the patient
    -   Foreign key: `patient_id`

### 2. User Account Related Data

-   **Request Documents** (`request_documents` table)

    -   All consultation forms, medical certificates, etc.
    -   Foreign key: `user_id`
    -   **Important**: Images attached to documents are also deleted from storage

-   **Activity Logs** (`activity_logs` table)

    -   All logs where the patient is the subject
    -   All logs where the patient's user account is the subject
    -   All logs performed by the patient's user account
    -   Filters: `subject_type`, `subject_id`, `user_id`

-   **User Account** (`users` table)

    -   The entire user account associated with the patient
    -   Foreign key: `user_id` in `patients` table

-   **User Address** (`addresses` table)
    -   Address information for the patient's user account
    -   Polymorphic relationship: `owner_type` = 'App\Models\User', `owner_id` = user_id

### 3. Patient-Specific Data

-   **Patient Address** (`addresses` table)

    -   Address information specific to the patient record
    -   Polymorphic relationship: `owner_type` = 'App\Models\Patient', `owner_id` = patient_id

-   **Media Files**
    -   Profile pictures and other uploaded files
    -   Stored using Spatie MediaLibrary
    -   Collection: 'profile'

### 4. Automatic Cascade Deletions (Database Level)

These are handled automatically by foreign key constraints:

-   **Notifications** (`notifications` table)
    -   User notifications are automatically deleted via `cascadeOnDelete()`
    -   Foreign key: `user_id`

## Implementation Details

### Location

The cascade deletion logic is implemented in the `Patient` model's `boot()` method:

```php
// File: app/Models/Patient.php
protected static function boot()
{
    parent::boot();

    static::deleting(function ($patient) {
        // Deletion logic here
    });
}
```

### Order of Deletion

1. **Appointments** - Direct patient relationships first
2. **Patient Queue Entries** - Queue system cleanup
3. **Prescriptions** - Medical prescriptions
4. **Visits** - Visit records
5. **Medicine Bills** - Billing information
6. **Request Documents** - Documents with file cleanup (uses `->each()->delete()` to trigger model events)
7. **Activity Logs** - Audit trail cleanup (patient-related, user-related, and user-performed)
8. **User Account** - User and user address deletion
9. **Patient Address** - Patient-specific address
10. **Media Files** - Profile pictures and attachments

### Safety Measures

#### Soft Delete Compatibility

-   The system works with both hard and soft deletes
-   If models use soft deletes, the `deleting` event still fires appropriately

#### File Cleanup

-   Request documents use `->each()->delete()` to ensure model events fire
-   This triggers proper file deletion from storage for attached images
-   Media files are cleared using `clearMediaCollection()`

#### Transaction Safety

-   Laravel's Eloquent events run within the same database transaction
-   If any part fails, the entire deletion is rolled back

## Usage Examples

### Standard Patient Deletion

```php
// This will trigger cascade deletion of all related data
$patient = Patient::find(1);
$patient->delete();
```

### Through Patient Controller

The existing patient deletion in the action buttons will automatically use this system:

```php
// In PatientController@destroy
$patient->delete(); // Cascades automatically
```

### Bulk Deletion

```php
// For bulk operations, iterate to ensure events fire
Patient::whereIn('id', [1, 2, 3])->get()->each(function ($patient) {
    $patient->delete(); // Cascades for each patient
});
```

## Testing

### Manual Testing

Use the test script: `debug/test_patient_cascade_delete.php`

```php
// Set patient ID and run
testPatientCascadeDelete(1);
```

### What to Verify

1. All related records are deleted (count = 0)
2. User account is removed
3. No orphaned records remain
4. Files are cleaned from storage
5. Activity logs are cleared

## Database Relationships Summary

```
Patient (patients)
├── Appointments (appointments.patient_id)
├── PatientQueue (patient_queues.patient_id)
├── Prescriptions (prescriptions.patient_id)
├── Visits (visits.patient_id)
├── MedicineBills (medicine_bills.patient_id)
├── RequestDocuments (request_documents.user_id = patients.user_id)
├── ActivityLogs (activity_logs.subject_id when subject_type = 'App\Models\Patient')
├── User (users.id = patients.user_id)
│   ├── UserAddress (addresses.owner_id when owner_type = 'App\Models\User')
│   ├── ActivityLogs (activity_logs.user_id)
│   ├── ActivityLogs (activity_logs.subject_id when subject_type = 'App\Models\User')
│   └── Notifications (notifications.user_id) [Auto-cascade]
├── PatientAddress (addresses.owner_id when owner_type = 'App\Models\Patient')
└── MediaFiles (media.model_id when model_type = 'App\Models\Patient')
```

## Troubleshooting

### Common Issues

1. **Foreign Key Constraints**

    - Ensure foreign keys have proper cascade settings
    - Check migration files for `->cascadeOnDelete()`

2. **File Not Deleted**

    - Verify storage permissions
    - Check if `InteractsWithMedia` trait is used
    - Ensure `clearMediaCollection()` is called

3. **Activity Log Cleanup**

    - Verify ActivityLog model exists
    - Check subject_type formatting (full namespace)

4. **Performance on Large Datasets**
    - Consider chunking for bulk operations
    - Monitor query performance
    - Add appropriate database indexes

### Monitoring Deletion

Enable query logging to monitor cascade operations:

```php
DB::enableQueryLog();
$patient->delete();
dd(DB::getQueryLog());
```

## Security Considerations

-   **Irreversible Action**: Cascade deletion cannot be undone
-   **Authorization**: Ensure proper role checks before deletion
-   **Audit Trail**: Activity logs are deleted, consider external logging
-   **Backup**: Always have database backups before bulk operations

## Future Enhancements

1. **Soft Delete Integration**: Consider implementing soft deletes for patients
2. **Archive System**: Move deleted patient data to archive tables
3. **Batch Processing**: Optimize for large-scale deletions
4. **Notification System**: Notify administrators of patient deletions
5. **Data Export**: Allow data export before deletion
