# Activity Logs - Testing Guide

## Overview

This guide will help you test the Activity Logs system for all three user roles: clinic_admin, staff, and doctor.

## Date

January 16, 2025

## Prerequisites

Before testing:

1. Ensure the database migration has been run
2. Ensure there are test users for each role (clinic_admin, staff, doctor)
3. Ensure there is at least one activity log record in the database

## Route Testing

### 1. Verify Routes Exist

Run the following command to verify all routes are registered:

```bash
php artisan route:list --name=activity-logs
```

**Expected Output:**

```
+--------+----------+-----------------------------------+----------------------------------+
| Method | URI      | Name                              | Action                           |
+--------+----------+-----------------------------------+----------------------------------+
| GET    | /admin/activity-logs                | activity-logs.index             | ActivityLogController@index      |
| GET    | /admin/activity-logs/export         | activity-logs.export            | ActivityLogController@export     |
| GET    | /admin/activity-logs/{id}           | activity-logs.show              | ActivityLogController@show       |
| GET    | /staff/activity-logs                | staff.activity-logs.index       | ActivityLogController@index      |
| GET    | /staff/activity-logs/export/csv     | staff.activity-logs.export      | ActivityLogController@export     |
| GET    | /staff/activity-logs/{activityLog}  | staff.activity-logs.show        | ActivityLogController@show       |
| GET    | /doctors/activity-logs              | doctors.activity-logs.index     | ActivityLogController@index      |
| GET    | /doctors/activity-logs/export/csv   | doctors.activity-logs.export    | ActivityLogController@export     |
| GET    | /doctors/activity-logs/{activityLog}| doctors.activity-logs.show      | ActivityLogController@show       |
+--------+----------+-----------------------------------+----------------------------------+
```

## Manual Testing Steps

### Test 1: Clinic Admin Access

**Steps:**

1. Login as `clinic_admin`
2. Check sidebar menu - "Activity Logs" should be visible at the bottom
3. Click "Activity Logs" menu item
4. Should redirect to `/admin/activity-logs`
5. Verify the page shows the activity logs list
6. Click on a log entry
7. Should redirect to `/admin/activity-logs/{id}` showing details
8. Go back to list page
9. Click "Export" button
10. Should download CSV file

**Expected Results:**

-   ✅ Menu item visible
-   ✅ Correct URL: `/admin/activity-logs`
-   ✅ Activity logs table displays
-   ✅ Filters work (user type, date range, action type)
-   ✅ Detail page shows full log information
-   ✅ Export downloads CSV file

### Test 2: Staff Access

**Steps:**

1. Login as `staff`
2. Check sidebar menu - "Activity Logs" should be visible at the bottom
3. Click "Activity Logs" menu item
4. Should redirect to `/staff/activity-logs`
5. Verify the page shows the activity logs list
6. Click on a log entry
7. Should redirect to `/staff/activity-logs/{id}` showing details
8. Go back to list page
9. Click "Export" button
10. Should download CSV file

**Expected Results:**

-   ✅ Menu item visible
-   ✅ Correct URL: `/staff/activity-logs`
-   ✅ Activity logs table displays
-   ✅ Filters work (user type, date range, action type)
-   ✅ Detail page shows full log information
-   ✅ Export downloads CSV file

### Test 3: Doctor Access

**Steps:**

1. Login as `doctor`
2. Check sidebar menu - "Activity Logs" should be visible at the bottom
3. Click "Activity Logs" menu item
4. Should redirect to `/doctors/activity-logs`
5. Verify the page shows the activity logs list
6. Click on a log entry
7. Should redirect to `/doctors/activity-logs/{id}` showing details
8. Go back to list page
9. Click "Export" button
10. Should download CSV file

**Expected Results:**

-   ✅ Menu item visible
-   ✅ Correct URL: `/doctors/activity-logs`
-   ✅ Activity logs table displays
-   ✅ Filters work (user type, date range, action type)
-   ✅ Detail page shows full log information
-   ✅ Export downloads CSV file

### Test 4: Patient Access (Should NOT see menu)

**Steps:**

1. Login as `patient`
2. Check sidebar menu

**Expected Results:**

-   ✅ "Activity Logs" menu item should NOT be visible
-   ✅ Direct URL access should be blocked by middleware

### Test 5: Role-Based Access Control

**Test unauthorized access:**

```bash
# Try to access staff routes as clinic_admin (should be blocked)
GET /staff/activity-logs (while logged in as clinic_admin)

# Try to access doctor routes as staff (should be blocked)
GET /doctors/activity-logs (while logged in as staff)

# Try to access admin routes as doctor (should be blocked)
GET /admin/activity-logs (while logged in as doctor)
```

**Expected Results:**

-   ✅ Each role can ONLY access their own routes
-   ✅ Attempting to access other roles' routes returns 403 Forbidden or redirects

## Functionality Testing

### Test 6: Activity Log Creation

**Create logs through various actions:**

1. **Patient Creation (Staff or Admin):**

    - Login as staff or clinic_admin
    - Create a new patient
    - Check Activity Logs
    - Verify `patient_created` log appears with patient details

2. **Consultation Creation (Doctor or Staff):**

    - Login as doctor or staff
    - Create a consultation for a patient
    - Check Activity Logs
    - Verify `consultation_created` log appears

3. **Medical Certificate Creation (Doctor or Staff):**

    - Login as doctor or staff
    - Create a medical certificate
    - Check Activity Logs
    - Verify `medical_certificate_created` log appears

4. **Medicine Procurement (Staff or Admin):**

    - Login as staff or clinic_admin
    - Purchase/procure medicines
    - Check Activity Logs
    - Verify `medicine_procured` log appears

5. **Medicine Usage (Doctor or Staff):**
    - Login as doctor or staff
    - Record medicine usage for patient
    - Check Activity Logs
    - Verify `medicine_used` log appears

**Expected Results:**

-   ✅ Each action creates corresponding activity log
-   ✅ All required fields are populated (date, name, age, gender, college, etc.)
-   ✅ Age is auto-calculated from DOB
-   ✅ Course/section shows combined value

### Test 7: Filtering

**Test each filter:**

1. **User Type Filter:**

    - Select "clinic_admin" → Shows only admin actions
    - Select "staff" → Shows only staff actions
    - Select "doctor" → Shows only doctor actions

2. **Date Range Filter:**

    - Set start date and end date
    - Verify only logs within date range appear

3. **Action Type Filter:**

    - Select "Patient Created" → Shows only patient creation logs
    - Select "Consultation Created" → Shows only consultation logs
    - etc.

4. **Patient Search:**
    - Enter patient name
    - Verify logs for that patient appear

**Expected Results:**

-   ✅ Each filter works independently
-   ✅ Multiple filters can be combined
-   ✅ Results update correctly when filters change

### Test 8: Detail View

**Steps:**

1. Click on any activity log entry
2. Verify detail page shows:
    - ✅ User who performed action
    - ✅ Action type
    - ✅ Date and time
    - ✅ Patient details (name, age, gender, college, address, contact)
    - ✅ Medical information (complaints, diagnosis, informant, consult_mode)
    - ✅ Course/Section
    - ✅ Additional details specific to action type

### Test 9: Export Functionality

**Steps:**

1. Apply some filters (optional)
2. Click "Export to CSV" button
3. Verify CSV file downloads
4. Open CSV file
5. Verify columns match activity log fields
6. Verify data is correctly formatted

**Expected CSV Columns:**

-   Date
-   User Name
-   User Type
-   Action
-   Patient Name
-   Age
-   Gender
-   College
-   Course/Section
-   Address
-   Contact Number
-   Complaints
-   Diagnosis
-   Informant
-   Consult Mode

## Automated Testing (Optional)

Create feature tests for each role:

```php
// tests/Feature/ActivityLogTest.php

public function test_clinic_admin_can_access_activity_logs()
{
    $admin = User::role('clinic_admin')->first();

    $response = $this->actingAs($admin)->get(route('activity-logs.index'));

    $response->assertStatus(200);
    $response->assertSee('Activity Logs');
}

public function test_staff_can_access_activity_logs()
{
    $staff = User::role('staff')->first();

    $response = $this->actingAs($staff)->get(route('staff.activity-logs.index'));

    $response->assertStatus(200);
    $response->assertSee('Activity Logs');
}

public function test_doctor_can_access_activity_logs()
{
    $doctor = User::role('doctor')->first();

    $response = $this->actingAs($doctor)->get(route('doctors.activity-logs.index'));

    $response->assertStatus(200);
    $response->assertSee('Activity Logs');
}

public function test_patient_cannot_access_activity_logs()
{
    $patient = User::role('patient')->first();

    $response = $this->actingAs($patient)->get(route('activity-logs.index'));

    $response->assertStatus(403); // or 302 redirect
}

public function test_activity_log_created_on_patient_creation()
{
    $staff = User::role('staff')->first();

    $patientData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'date_of_birth' => '2000-01-01',
        'gender' => 'Male',
        'college' => 'College of Engineering',
        'course' => 'Computer Science',
        'year_level' => '3rd Year',
        // ... other fields
    ];

    $this->actingAs($staff)->post(route('staff.patients.store'), $patientData);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $staff->id,
        'action' => 'patient_created',
        'patient_name' => 'John Doe',
    ]);
}
```

Run tests:

```bash
php artisan test --filter=ActivityLogTest
```

## Performance Testing

### Test 10: Large Dataset Performance

**Steps:**

1. Seed database with 1000+ activity logs
2. Access activity logs page
3. Verify page loads within acceptable time (< 2 seconds)
4. Test filtering with large dataset
5. Test export with large dataset

**Expected Results:**

-   ✅ Page loads quickly
-   ✅ Pagination works correctly
-   ✅ Filters don't cause timeout
-   ✅ Export completes successfully

## Browser Testing

Test in multiple browsers:

-   ✅ Chrome
-   ✅ Firefox
-   ✅ Safari
-   ✅ Edge

## Mobile Testing

Test responsive design:

-   ✅ Mobile phone (portrait)
-   ✅ Mobile phone (landscape)
-   ✅ Tablet (portrait)
-   ✅ Tablet (landscape)

## Security Testing

### Test 11: SQL Injection Prevention

Try injecting SQL in filters:

```
Patient Name: ' OR '1'='1
Date: 2024-01-01' OR '1'='1
```

**Expected Results:**

-   ✅ No SQL errors
-   ✅ No unauthorized data access
-   ✅ Proper escaping of input

### Test 12: XSS Prevention

Try injecting scripts in patient data:

```
Patient Name: <script>alert('XSS')</script>
Complaints: <img src=x onerror=alert('XSS')>
```

**Expected Results:**

-   ✅ Scripts are escaped and displayed as text
-   ✅ No script execution

## Error Handling

### Test 13: Invalid Routes

Try accessing:

-   `/admin/activity-logs/999999` (non-existent ID)
-   `/staff/activity-logs/abc` (invalid ID format)

**Expected Results:**

-   ✅ Displays 404 error page
-   ✅ Or redirects with error message

## Common Issues Checklist

-   [ ] Route not found → Check route registration with `php artisan route:list`
-   [ ] 403 Forbidden → Check role middleware and user role assignment
-   [ ] 500 Server Error → Check logs in `storage/logs/laravel.log`
-   [ ] Menu not showing → Check `isRole()` helper function
-   [ ] Wrong redirect → Verify route names match in menu.blade.php
-   [ ] Export not working → Check route order (export before show route)
-   [ ] Filters not working → Check request parameters in controller
-   [ ] Age not auto-calculated → Verify `date_of_birth` field exists in patient table

## Test Report Template

```
Activity Logs Testing Report
Date: _______________
Tester: _______________

1. Route Registration: [ ] Pass [ ] Fail
2. Clinic Admin Access: [ ] Pass [ ] Fail
3. Staff Access: [ ] Pass [ ] Fail
4. Doctor Access: [ ] Pass [ ] Fail
5. Patient No Access: [ ] Pass [ ] Fail
6. Role-Based Access Control: [ ] Pass [ ] Fail
7. Activity Log Creation: [ ] Pass [ ] Fail
8. Filtering: [ ] Pass [ ] Fail
9. Detail View: [ ] Pass [ ] Fail
10. Export Functionality: [ ] Pass [ ] Fail
11. Large Dataset Performance: [ ] Pass [ ] Fail
12. Security (SQL/XSS): [ ] Pass [ ] Fail
13. Error Handling: [ ] Pass [ ] Fail

Issues Found:
1. _________________________________
2. _________________________________
3. _________________________________

Overall Status: [ ] All Tests Passed [ ] Some Tests Failed [ ] Major Issues Found
```

## Next Steps After Testing

If all tests pass:

-   ✅ Deploy to staging environment
-   ✅ Conduct user acceptance testing
-   ✅ Train users on new feature
-   ✅ Deploy to production

If tests fail:

-   ❌ Document issues
-   ❌ Fix bugs
-   ❌ Re-test
-   ❌ Repeat until all pass
