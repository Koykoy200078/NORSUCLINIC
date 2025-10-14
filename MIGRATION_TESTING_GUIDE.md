# Migration Test & Verification Script

## Quick Verification Commands

### 1. Test Database Connection

```bash
php artisan tinker
```

Then run:

```php
// Test PatientQueue model
\App\Models\PatientQueue::count();

// Verify table name
(new \App\Models\PatientQueue)->getTable();
// Expected: "patient_queues"

// Test relationships
\App\Models\Patient::first()->appointments ?? 'No patients yet';
\App\Models\Doctor::first()->appointments ?? 'No doctors yet';
```

---

### 2. Check Routes

```bash
# List all patient-queues routes
php artisan route:list --name=patient-queues

# Expected: 17+ routes with names like:
# - admin.patient-queues.index
# - admin.patient-queues.create
# - admin.patient-queues.store
# - staff.patient-queues.*
# - doctors.patient-queues.*
```

---

### 3. Verify No More "Appointment" References

```bash
# Search in PHP files (should return 0 results from app/ folder)
Get-ChildItem app -Filter *.php -Recurse | Select-String -Pattern "\\bappointments\\.(status|date|payment_type|\\*)" | Where-Object { $_.Path -notlike "*backup*" }

# Search for Appointment model imports in app files
Get-ChildItem app -Filter *.php -Recurse | Select-String -Pattern "use App\\Models\\Appointment;" | Where-Object { $_.Path -notlike "*backup*" }
```

---

### 4. Check Database Schema

```bash
php artisan tinker
```

Then run:

```php
// Check if patient_queues table exists
\Schema::hasTable('patient_queues');
// Expected: true

// Check if appointments table still exists (should be dropped)
\Schema::hasTable('appointments');
// Expected: false (if you dropped the old table)

// List patient_queues columns
\Schema::getColumnListing('patient_queues');
// Expected: includes queue-specific columns like room_number, priority, queue_number, etc.
```

---

### 5. Test Livewire Components

```bash
# Clear all caches first
php artisan optimize:clear

# Test specific component
php artisan tinker
```

Then run:

```php
// Test AppointmentTable query builder
$table = new \App\Livewire\AppointmentTable();
$table->statusFilter = 1;
$query = $table->builder();
dump($query->toSql());
// Should contain "patient_queues.status" NOT "appointments.status"
```

---

### 6. Browser Testing Checklist

#### Admin Routes

-   [ ] Visit: `http://127.0.0.1:8000/admin/patient-queues`
    -   Page should load without errors
    -   Livewire table should display
    -   Filter buttons should work
-   [ ] Visit: `http://127.0.0.1:8000/admin/patient-queues/create`
    -   Form should load
    -   Can create new queue entry

#### Staff Routes

-   [ ] Visit: `http://127.0.0.1:8000/staff/patient-queues`
    -   Page should load without errors
    -   Can view queue list

#### Doctor Routes

-   [ ] Visit: `http://127.0.0.1:8000/doctors/patient-queues`
    -   Page should load without errors
    -   Shows only doctor's assigned queues

#### Patient Routes

-   [ ] Visit: `http://127.0.0.1:8000/patient/appointments`
    -   Page should load without errors
    -   Shows patient's queue entries

---

### 7. Check Error Logs

```bash
# View recent errors (should be empty or no appointment-related errors)
Get-Content storage/logs/laravel.log -Tail 100 | Select-String -Pattern "ERROR|appointments\."

# Clear old logs for fresh testing
# Remove-Item storage/logs/laravel.log  # Use with caution!
```

---

### 8. Test Filter Functionality

**Status Filter Test:**

1. Go to patient queues page
2. Click "Booked" status filter
3. Network tab should show SQL with `patient_queues.status = 1` (not `appointments.status`)

**Date Filter Test:**

1. Select date range filter
2. Network tab should show SQL with `patient_queues.date BETWEEN ...` (not `appointments.date`)

**Payment Type Filter Test:**

1. Select payment type filter
2. Network tab should show SQL with `patient_queues.payment_type = ...` (not `appointments.payment_type`)

---

### 9. Migration Completeness Verification

```bash
# Count total "appointment" references in code (excluding backups, logs, docs)
Get-ChildItem -Path app,resources/views -Include *.php,*.blade.php -Recurse |
    Select-String -Pattern "\\bAppointment\\b" |
    Where-Object {
        $_.Path -notlike "*backup*" -and
        $_.Path -notlike "*vendor*"
    } |
    Measure-Object

# Should only show references to:
# - Livewire component class names (AppointmentTable, DoctorAppointmentTable, etc.)
# - View paths (appointments.components.*)
# - Migration file names
# - Comments/documentation
```

---

### 10. Performance Check

```bash
php artisan tinker
```

Then run:

```php
// Test query performance
\DB::enableQueryLog();

\App\Models\PatientQueue::with(['doctor', 'patient'])->take(10)->get();

dump(\DB::getQueryLog());
// Check queries use "patient_queues" table
```

---

## Expected Results ✅

| Test                      | Expected Result                           | Status           |
| ------------------------- | ----------------------------------------- | ---------------- |
| Database Connection       | ✅ Connects to `patient_queues` table     | ✅               |
| Routes Registered         | ✅ 17+ routes working                     | ✅               |
| No Appointment References | ✅ Zero in app/ folder                    | ✅               |
| Table Schema              | ✅ patient_queues exists with new columns | ✅               |
| Livewire Components       | ✅ All use patient_queues table           | ✅               |
| Admin Page Loads          | ✅ No errors                              | 🧪 Test Required |
| Staff Page Loads          | ✅ No errors                              | 🧪 Test Required |
| Doctor Page Loads         | ✅ No errors                              | 🧪 Test Required |
| Patient Page Loads        | ✅ No errors                              | 🧪 Test Required |
| Filter Functionality      | ✅ All filters work                       | 🧪 Test Required |
| Error Logs                | ✅ No SQL errors                          | ✅               |

---

## Common Issues & Solutions

### Issue: "Table appointments doesn't exist"

**Solution**:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```

### Issue: "Class App\Models\Appointment not found"

**Solution**: Check if any file still imports the old model:

```bash
Get-ChildItem app -Filter *.php -Recurse | Select-String "use App\\Models\\Appointment;"
```

### Issue: Livewire table shows no data

**Solution**: Check if table name is correct in Livewire component:

```php
protected $model = PatientQueue::class; // Correct
protected string $tableName = 'appointments'; // This is OK - it's just a component identifier
```

---

## Final Verification Command

Run this all-in-one verification:

```bash
# Clear caches
php artisan optimize:clear

# Check routes
php artisan route:list --name=patient-queues | Select-Object -First 5

# Search for old references
Get-ChildItem app -Filter *.php -Recurse | Select-String -Pattern "\\bappointments\\.(status|date)" | Measure-Object

# Expected: 0 results
```

---

## 🎉 Migration Complete!

If all tests pass, your migration from `appointments` to `patient_queues` is **100% complete** and ready for production!

**Last Updated**: October 14, 2025
