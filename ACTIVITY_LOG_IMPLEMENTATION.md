# Activity Logging System for NORSU Clinic

## Overview

This activity logging system tracks all actions performed by clinic administrators, staff, and doctors in the NORSU Clinic management system. It captures detailed information about patient management, consultation forms, medical certificates, and medicine operations.

## Features Implemented

### 1. **Database Migration**

-   **File**: `database/migrations/2025_10_16_000001_create_activity_logs_table.php`
-   **Table**: `activity_logs`
-   **Fields**:
    -   User information (user_id, user_type, user_name)
    -   Action details (action, subject_type, subject_id, description)
    -   Patient/Document fields (date, patient_name, age, gender, college, etc.)
    -   Medical information (complaints, diagnosis, informant, consult_mode, course_section)
    -   Additional metadata (properties, ip_address, user_agent)

### 2. **ActivityLog Model**

-   **File**: `app/Models/ActivityLog.php`
-   **Features**:
    -   Relationships with User model
    -   Scopes for filtering (byUserType, byAction, dateRange, bySubjectType)
    -   Formatted attributes for display
    -   JSON casting for properties field

### 3. **LogsActivity Trait**

-   **File**: `app/Traits/LogsActivity.php`
-   **Methods**:
    -   `logActivity()` - Base logging method
    -   `logPatientCreation()` - Log patient creation
    -   `logPatientUpdate()` - Log patient updates
    -   `logPatientDeletion()` - Log patient deletions
    -   `logConsultationCreation()` - Log consultation form creation
    -   `logMedicalCertificateCreation()` - Log medical certificate creation
    -   `logMedicineProcurement()` - Log medicine procurement
    -   `logMedicineUsage()` - Log medicine usage
-   **Features**:
    -   Auto-calculates age from date of birth if not provided
    -   Combines course and year_level into course_section
    -   Captures IP address and user agent
    -   Supports additional properties as JSON

### 4. **ActivityLogController**

-   **File**: `app/Http/Controllers/ActivityLogController.php`
-   **Routes**:
    -   `GET /admin/activity-logs` - List all activity logs with filters
    -   `GET /admin/activity-logs/{id}` - View single activity log details
    -   `GET /admin/activity-logs/export` - Export logs to CSV
-   **Features**:
    -   Pagination (20 records per page)
    -   Filtering by user type, action, date range, and search
    -   CSV export with all relevant fields

### 5. **Views**

-   **Files**:
    -   `resources/views/activity_logs/index.blade.php` - Activity logs listing
    -   `resources/views/activity_logs/show.blade.php` - Activity log details

### 6. **Integration Points**

#### Patient Management

-   **File**: `app/Repositories/PatientRepository.php`
-   **Actions Logged**:
    -   Patient creation (with full details from User model)
    -   Patient updates

#### Consultation Forms & Medical Certificates

-   **File**: `app/Http/Controllers/RequestDocumentsController.php`
-   **Actions Logged**:
    -   Consultation form creation (includes all medical details)
    -   Medical certificate creation

#### Medicine Management

-   **Files**:
    -   `app/Repositories/PurchaseMedicineRepository.php` - Procurement logging
    -   `app/Http/Controllers/RequestDocumentsController.php` - Usage logging
-   **Actions Logged**:
    -   Medicine procurement (with batch number and expiry date)
    -   Medicine usage (linked to patient consultations)

## Installation Steps

### 1. Run the Migration

```bash
php artisan migrate
```

### 2. Clear Cache (if needed)

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 3. Access Activity Logs

Navigate to: `/admin/activity-logs` (for clinic admins)

## Usage Examples

### Viewing Activity Logs

1. Login as clinic_admin
2. Navigate to Activity Logs menu
3. Use filters to find specific activities:
    - Search by patient name, user name, or description
    - Filter by user type (Admin, Doctor, Staff)
    - Filter by action type
    - Filter by date range

### Exporting Activity Logs

1. Apply desired filters
2. Click "Export CSV" button
3. CSV file will download with all filtered records

## Logged Actions

### Patient Actions

-   `created_patient` - When a new patient is added
-   `updated_patient` - When patient information is modified
-   `deleted_patient` - When a patient is removed

### Document Actions

-   `created_consultation` - When a consultation form is created
-   `created_medical_certificate` - When a medical certificate is created

### Medicine Actions

-   `procured_medicine` - When medicine is purchased/added to inventory
-   `used_medicine` - When medicine is dispensed during consultation

## Field Mappings

### Age Calculation

-   If `age` is not provided but `date_of_birth` exists, age is automatically calculated
-   This ensures accurate age recording even when not explicitly entered

### Course/Section

-   Automatically combines `course` and `year_level` fields into `course_section`
-   Format: "Course Name - Year Level Name"

### Diagnosis Field

-   For consultations: comes from `assessment` field
-   For medical certificates: comes from `complaints_diagnosis` field

### Contact Number

-   Mapped from `patient_contact` or `contact` field depending on context

## Security Features

1. **IP Address Tracking** - Records the IP address of the user performing the action
2. **User Agent Tracking** - Records browser and device information
3. **Timestamp** - All logs have `created_at` timestamp
4. **User Attribution** - Every action is linked to the authenticated user

## Future Enhancements (Optional)

### Recommended Additions:

1. **Activity Log Dashboard Widget** - Show recent activities on admin dashboard
2. **User Activity Summary** - Show total actions per user
3. **Automated Alerts** - Email notifications for specific actions
4. **Log Retention Policy** - Automatic archival of old logs
5. **Advanced Search** - Full-text search capabilities
6. **Activity Charts** - Visual representation of activities over time

## Troubleshooting

### Logs Not Appearing?

1. Check if migration ran successfully: `php artisan migrate:status`
2. Verify user is authenticated when performing actions
3. Check if `LogsActivity` trait is properly imported

### Age Shows as NULL?

-   Ensure `dob` (date of birth) is set in the User model
-   If manually logging, provide either `patient_age` or `date_of_birth`

### Permission Issues?

-   Ensure the activity-logs routes are inside the admin middleware group
-   Currently accessible to all admin users (consider adding specific permission if needed)

## Database Indexes

The following indexes are created for optimal query performance:

-   `user_id`
-   `user_type`
-   `subject_type`
-   `subject_id`
-   `action`
-   `date`
-   `created_at`

## API Examples

### Programmatically Log an Activity

```php
use App\Traits\LogsActivity;

class YourController extends Controller
{
    use LogsActivity;

    public function yourMethod()
    {
        // Simple logging
        self::logActivity(
            'custom_action',
            'Description of what happened',
            [
                'patient_name' => 'John Doe',
                'patient_age' => 25,
                // ... other fields
            ]
        );
    }
}
```

### Query Activity Logs

```php
use App\Models\ActivityLog;

// Get all consultation creations
$consultations = ActivityLog::byAction('created_consultation')->get();

// Get all activities by doctors this month
$doctorActivities = ActivityLog::byUserType('doctor')
    ->whereMonth('created_at', now()->month)
    ->get();

// Get activities for a specific patient
$patientLogs = ActivityLog::where('patient_name', 'John Doe')->get();
```

## Notes

-   All logging is done automatically when using the integrated controllers and repositories
-   Logs are stored permanently (consider implementing archival if needed)
-   The system captures both successful actions (logs are created after DB commit)
-   Contact number and diagnosis fields are automatically extracted from consultation forms
-   Age is auto-calculated if date of birth is available

## Support

For issues or questions, refer to:

-   Laravel 10 Documentation: https://laravel.com/docs/10.x
-   Project maintainer
