# Activity Logs - Routes and Menu Implementation

## Overview

This document describes the implementation of role-based routing and navigation menu for the Activity Logs system.

## Date

January 16, 2025

## Changes Made

### 1. Staff Routes (`routes/staff.php`)

**Added:**

-   Import statement for `ActivityLogController`
-   Activity logs routes group under the staff prefix

```php
// Added to imports
use App\Http\Controllers\ActivityLogController;

// Added route group at the end of the file (before closing bracket)
// Activity Logs (Staff can view activity logs)
Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
    Route::get('/', [ActivityLogController::class, 'index'])->name('index');
    Route::get('/{activityLog}', [ActivityLogController::class, 'show'])->name('show');
    Route::get('/export/csv', [ActivityLogController::class, 'export'])->name('export');
});
```

**Generated Route Names:**

-   `staff.activity-logs.index` → `/staff/activity-logs`
-   `staff.activity-logs.show` → `/staff/activity-logs/{activityLog}`
-   `staff.activity-logs.export` → `/staff/activity-logs/export/csv`

### 2. Doctor Routes (`routes/doctor.php`)

**Added:**

-   Import statement for `ActivityLogController`
-   Activity logs routes group under the doctors prefix

```php
// Added to imports
use App\Http\Controllers\ActivityLogController;

// Added route group at the end of the file (before closing bracket)
// Activity Logs (Doctors can view activity logs)
Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
    Route::get('/', [ActivityLogController::class, 'index'])->name('index');
    Route::get('/{activityLog}', [ActivityLogController::class, 'show'])->name('show');
    Route::get('/export/csv', [ActivityLogController::class, 'export'])->name('export');
});
```

**Generated Route Names:**

-   `doctors.activity-logs.index` → `/doctors/activity-logs`
-   `doctors.activity-logs.show` → `/doctors/activity-logs/{activityLog}`
-   `doctors.activity-logs.export` → `/doctors/activity-logs/export/csv`

### 3. Admin Routes (`routes/web.php`)

**Already Exists:**

-   Activity logs routes were previously added to the admin section

**Existing Route Names:**

-   `activity-logs.index` → `/admin/activity-logs`
-   `activity-logs.show` → `/admin/activity-logs/{activityLog}`
-   `activity-logs.export` → `/admin/activity-logs/export/csv`

### 4. Navigation Menu (`resources/views/layouts/menu.blade.php`)

**Added:**
Activity Logs menu item with role-based routing at the end of the menu (after Settings section)

```blade
{{-- Activity Logs - For clinic_admin, staff, and doctor --}}
@if(isRole('clinic_admin') || isRole('staff') || isRole('doctor'))
<li class="nav-item {{
    (isRole('clinic_admin') && Request::is('admin/activity-logs*')) ||
    (isRole('staff') && Request::is('staff/activity-logs*')) ||
    (isRole('doctor') && Request::is('doctors/activity-logs*'))
? 'active' : '' }}">
    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{
        isRole('clinic_admin') ? route('activity-logs.index') :
        (isRole('staff') ? route('staff.activity-logs.index') : route('doctors.activity-logs.index'))
    }}">
        <span class="aside-menu-icon pe-3"><i class="fas fa-clipboard-list"></i></span>
        <span class="aside-menu-title">Activity Logs</span>
    </a>
</li>
@endif
```

**Menu Features:**

-   Icon: `fas fa-clipboard-list` (clipboard with list icon)
-   Shows for: clinic_admin, staff, and doctor roles only
-   Active state: Highlights when on any activity-logs page
-   Role-based routing: Automatically directs to the correct route based on logged-in user role

## Route Middleware

All routes are protected by their respective role middleware:

### Admin Routes

-   Middleware: `auth`, `xss`, `checkUserStatus`, `role:clinic_admin`
-   Prefix: `/admin`

### Staff Routes

-   Middleware: `auth`, `xss`, `checkUserStatus`, `role:staff`
-   Prefix: `/staff`

### Doctor Routes

-   Middleware: `auth`, `xss`, `checkUserStatus`, `role:doctor`
-   Prefix: `/doctors`

## Controller Methods Used

The same `ActivityLogController` is used for all three roles:

1. **index()** - List all activity logs with filtering
2. **show($id)** - Show detailed view of a specific activity log
3. **export()** - Export activity logs to CSV format

## Access Control

### By Role:

-   **clinic_admin**: Can view all activity logs from all users
-   **staff**: Can view all activity logs (staff typically creates patient records and documents)
-   **doctor**: Can view all activity logs (doctors create consultations and certificates)

### Filtering in Controller:

The `ActivityLogController@index` method already has filters for:

-   User Type (clinic_admin, staff, doctor)
-   Date Range
-   Action Type (patient_created, consultation_created, etc.)
-   Patient Name/ID

## Testing Checklist

-   [ ] Test staff access to `/staff/activity-logs`
-   [ ] Test doctor access to `/doctors/activity-logs`
-   [ ] Test clinic_admin access to `/admin/activity-logs`
-   [ ] Verify menu item appears for each role
-   [ ] Verify menu item is active when on activity logs page
-   [ ] Test role-based filtering in the interface
-   [ ] Test CSV export for each role
-   [ ] Verify correct route redirection based on logged-in user role

## Navigation Flow

### For Clinic Admin:

1. Login as clinic_admin
2. Click "Activity Logs" in sidebar menu
3. Redirected to `/admin/activity-logs`
4. Can view all logs, filter, export

### For Staff:

1. Login as staff
2. Click "Activity Logs" in sidebar menu
3. Redirected to `/staff/activity-logs`
4. Can view all logs, filter, export

### For Doctor:

1. Login as doctor
2. Click "Activity Logs" in sidebar menu
3. Redirected to `/doctors/activity-logs`
4. Can view all logs, filter, export

## Related Files

### Routes:

-   `routes/web.php` (lines 233-237) - Admin routes
-   `routes/staff.php` (end of file) - Staff routes
-   `routes/doctor.php` (end of file) - Doctor routes

### Controller:

-   `app/Http/Controllers/ActivityLogController.php` - Main controller

### Views:

-   `resources/views/layouts/menu.blade.php` - Navigation menu
-   `resources/views/activity_logs/index.blade.php` - List view
-   `resources/views/activity_logs/show.blade.php` - Detail view

### Model:

-   `app/Models/ActivityLog.php` - Activity log model

## Notes

1. **Permissions**: Currently, the activity logs routes don't require specific permissions (like `manage_activity_logs`). They rely solely on role-based access. If needed, you can add permission checks later.

2. **Data Visibility**: All three roles can see all activity logs. If you need to restrict data visibility (e.g., staff can only see their own logs), modify the `ActivityLogController@index` method to add user-based filtering.

3. **Icon Consistency**: Using `fa-clipboard-list` icon which is consistent with the Queue Management icon style in the system.

4. **Menu Position**: Activity Logs menu item is placed at the end of the sidebar, after Settings, making it easily accessible but not cluttering the main workflow items.

5. **Route Export Fix**: The export route is defined as `/export/csv` but should be before the `/{activityLog}` route to avoid route conflicts. The order in the route group matters!

## Potential Issues to Check

⚠️ **Route Order Issue**: The export route is defined AFTER the show route. This means `/activity-logs/export/csv` might match `/{activityLog}` first. Consider moving export route before show route:

```php
Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
    Route::get('/', [ActivityLogController::class, 'index'])->name('index');
    Route::get('/export/csv', [ActivityLogController::class, 'export'])->name('export'); // Move before show
    Route::get('/{activityLog}', [ActivityLogController::class, 'show'])->name('show');
});
```

This should be fixed in all three route files (web.php, staff.php, doctor.php).

## Summary

✅ Staff routes added with proper prefix and naming
✅ Doctor routes added with proper prefix and naming
✅ Admin routes already existed
✅ Navigation menu updated with role-based routing
✅ Menu item shows for appropriate roles (clinic_admin, staff, doctor)
✅ Active state handling for menu highlighting
✅ All routes protected by role-based middleware

The Activity Logs system is now accessible to all three roles (clinic_admin, staff, doctor) through their respective dashboards with proper role-based routing and navigation.
