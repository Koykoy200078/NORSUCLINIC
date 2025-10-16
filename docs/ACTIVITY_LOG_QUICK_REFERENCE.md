# Activity Logs - Quick Reference & Commands

## Quick Commands

### 1. Clear All Caches

After adding routes and menu changes, clear the Laravel caches:

```powershell
# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Or clear everything at once
php artisan optimize:clear
```

### 2. View All Routes

Check that activity logs routes are registered:

```powershell
# View all activity log routes
php artisan route:list --name=activity-logs

# View all staff routes
php artisan route:list --name=staff

# View all doctor routes
php artisan route:list --name=doctors

# View all routes with specific controller
php artisan route:list | Select-String "ActivityLog"
```

### 3. Database Commands

```powershell
# Check if migration exists
php artisan migrate:status

# Run migrations (if needed)
php artisan migrate

# Check activity logs table in database
php artisan tinker
>>> \App\Models\ActivityLog::count()
>>> \App\Models\ActivityLog::latest()->first()
>>> exit
```

### 4. Testing Commands

```powershell
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=ActivityLogTest

# Run with coverage
php artisan test --coverage
```

---

## Quick Access URLs

### Clinic Admin

```
http://your-domain.com/admin/activity-logs
http://your-domain.com/admin/activity-logs/{id}
http://your-domain.com/admin/activity-logs/export
```

### Staff

```
http://your-domain.com/staff/activity-logs
http://your-domain.com/staff/activity-logs/{id}
http://your-domain.com/staff/activity-logs/export/csv
```

### Doctor

```
http://your-domain.com/doctors/activity-logs
http://your-domain.com/doctors/activity-logs/{id}
http://your-domain.com/doctors/activity-logs/export/csv
```

---

## Route Names Reference

### Clinic Admin Routes

| Method | Route Name             | URL                           | Description      |
| ------ | ---------------------- | ----------------------------- | ---------------- |
| GET    | `activity-logs.index`  | `/admin/activity-logs`        | List all logs    |
| GET    | `activity-logs.show`   | `/admin/activity-logs/{id}`   | Show log details |
| GET    | `activity-logs.export` | `/admin/activity-logs/export` | Export to CSV    |

### Staff Routes

| Method | Route Name                   | URL                               | Description      |
| ------ | ---------------------------- | --------------------------------- | ---------------- |
| GET    | `staff.activity-logs.index`  | `/staff/activity-logs`            | List all logs    |
| GET    | `staff.activity-logs.show`   | `/staff/activity-logs/{id}`       | Show log details |
| GET    | `staff.activity-logs.export` | `/staff/activity-logs/export/csv` | Export to CSV    |

### Doctor Routes

| Method | Route Name                     | URL                                 | Description      |
| ------ | ------------------------------ | ----------------------------------- | ---------------- |
| GET    | `doctors.activity-logs.index`  | `/doctors/activity-logs`            | List all logs    |
| GET    | `doctors.activity-logs.show`   | `/doctors/activity-logs/{id}`       | Show log details |
| GET    | `doctors.activity-logs.export` | `/doctors/activity-logs/export/csv` | Export to CSV    |

---

## Using Routes in Blade Templates

### Generate URL

```blade
{{-- Clinic Admin --}}
<a href="{{ route('activity-logs.index') }}">Activity Logs</a>

{{-- Staff --}}
<a href="{{ route('staff.activity-logs.index') }}">Activity Logs</a>

{{-- Doctor --}}
<a href="{{ route('doctors.activity-logs.index') }}">Activity Logs</a>

{{-- Role-based (automatic) --}}
<a href="{{
    isRole('clinic_admin') ? route('activity-logs.index') :
    (isRole('staff') ? route('staff.activity-logs.index') : route('doctors.activity-logs.index'))
}}">Activity Logs</a>
```

### Check Current Route

```blade
{{-- Check if on activity logs page --}}
@if(Request::is('admin/activity-logs*'))
    <li class="active">Activity Logs</li>
@endif

@if(Request::is('staff/activity-logs*'))
    <li class="active">Activity Logs</li>
@endif

@if(Request::is('doctors/activity-logs*'))
    <li class="active">Activity Logs</li>
@endif
```

---

## Using Trait in Controllers/Repositories

### Import Trait

```php
use App\Traits\LogsActivity;

class YourController extends Controller
{
    use LogsActivity;

    // Your methods here
}
```

### Log Activities

#### 1. Patient Creation

```php
$this->logPatientCreation($patient, auth()->user());
```

#### 2. Patient Update

```php
$this->logPatientUpdate($patient, auth()->user());
```

#### 3. Consultation Creation

```php
$this->logConsultationCreation($consultation, $patient, auth()->user());
```

#### 4. Medical Certificate Creation

```php
$this->logMedicalCertificateCreation($certificate, $patient, auth()->user());
```

#### 5. Medicine Procurement

```php
$this->logMedicineProcurement($purchase, $medicine, auth()->user(), $details);
```

#### 6. Medicine Usage

```php
$this->logMedicineUsage($medicineBill, $patient, auth()->user(), $details);
```

---

## Filter Parameters

### In Controller (index method)

```php
// Filter by user type
$request->user_type // 'clinic_admin', 'staff', 'doctor'

// Filter by date range
$request->start_date // 'YYYY-MM-DD'
$request->end_date   // 'YYYY-MM-DD'

// Filter by action
$request->action // 'patient_created', 'consultation_created', etc.

// Search by patient
$request->patient_search // patient name
```

### In Blade Template (building filter URLs)

```blade
<form action="{{ route('activity-logs.index') }}" method="GET">
    <select name="user_type">
        <option value="">All Users</option>
        <option value="clinic_admin">Clinic Admin</option>
        <option value="staff">Staff</option>
        <option value="doctor">Doctor</option>
    </select>

    <input type="date" name="start_date">
    <input type="date" name="end_date">

    <select name="action">
        <option value="">All Actions</option>
        <option value="patient_created">Patient Created</option>
        <option value="consultation_created">Consultation Created</option>
        <option value="medical_certificate_created">Medical Certificate</option>
        <option value="medicine_procured">Medicine Procured</option>
        <option value="medicine_used">Medicine Used</option>
    </select>

    <input type="text" name="patient_search" placeholder="Search patient...">

    <button type="submit">Filter</button>
</form>
```

---

## Database Queries

### Using Eloquent

```php
// Get all activity logs
$logs = ActivityLog::all();

// Get paginated logs
$logs = ActivityLog::paginate(15);

// Filter by user type
$logs = ActivityLog::byUserType('staff')->get();

// Filter by action
$logs = ActivityLog::byAction('patient_created')->get();

// Filter by date range
$logs = ActivityLog::dateRange('2024-01-01', '2024-01-31')->get();

// Search by patient name
$logs = ActivityLog::where('patient_name', 'like', '%John%')->get();

// Get logs with user relationship
$logs = ActivityLog::with('user')->get();

// Complex query
$logs = ActivityLog::byUserType('staff')
    ->byAction('consultation_created')
    ->dateRange('2024-01-01', '2024-01-31')
    ->where('patient_name', 'like', '%John%')
    ->orderBy('created_at', 'desc')
    ->paginate(15);

// Count logs
$count = ActivityLog::count();
$staffCount = ActivityLog::byUserType('staff')->count();

// Get latest log
$latest = ActivityLog::latest()->first();

// Get oldest log
$oldest = ActivityLog::oldest()->first();
```

### Using Raw SQL (in Tinker)

```php
// Get all logs
DB::table('activity_logs')->get();

// Count by user type
DB::table('activity_logs')->select('user_type', DB::raw('count(*) as total'))
    ->groupBy('user_type')
    ->get();

// Count by action
DB::table('activity_logs')->select('action', DB::raw('count(*) as total'))
    ->groupBy('action')
    ->get();

// Get logs for today
DB::table('activity_logs')
    ->whereDate('created_at', today())
    ->get();
```

---

## Debugging Tips

### Check Route Registration

```powershell
php artisan route:list --name=activity-logs
```

### Check Middleware

```powershell
php artisan route:list --path=activity-logs
```

### View Laravel Logs

```powershell
# View last 50 lines
Get-Content storage/logs/laravel.log -Tail 50

# Follow logs (real-time)
Get-Content storage/logs/laravel.log -Wait
```

### Test Route Access

```powershell
# Using curl (if available)
curl http://localhost/staff/activity-logs

# Or in browser
http://localhost/staff/activity-logs
```

### Clear Browser Cache

-   Chrome: Ctrl+Shift+Delete
-   Firefox: Ctrl+Shift+Delete
-   Edge: Ctrl+Shift+Delete

### Tinker Commands

```php
php artisan tinker

>>> use App\Models\ActivityLog;
>>> ActivityLog::count()
=> 1

>>> ActivityLog::latest()->first()
=> App\Models\ActivityLog {#...}

>>> User::role('staff')->first()
=> App\Models\User {#...}

>>> Route::getRoutes()->getByName('staff.activity-logs.index')
=> Illuminate\Routing\Route {#...}

>>> exit
```

---

## Common Issues & Solutions

### Issue: Route not found (404)

**Solution:**

```powershell
php artisan route:clear
php artisan route:cache
```

### Issue: View not found

**Solution:**

```powershell
php artisan view:clear
```

### Issue: Permission denied (403)

**Solution:**

-   Check user role assignment
-   Verify middleware in route file
-   Check if user is logged in

### Issue: Menu not showing

**Solution:**

-   Clear view cache: `php artisan view:clear`
-   Check browser cache
-   Verify `isRole()` helper function
-   Check user's role in database

### Issue: Activity logs not being created

**Solution:**

-   Verify trait is imported: `use LogsActivity;`
-   Check logging method is called
-   Verify database connection
-   Check `activity_logs` table exists

### Issue: Export not working

**Solution:**

-   Check route order (export before show)
-   Verify export method in controller
-   Check file permissions for storage

---

## Development Workflow

### 1. Start Development Server

```powershell
php artisan serve
```

### 2. Watch for File Changes (if using npm)

```powershell
npm run dev
# or
npm run watch
```

### 3. Open in Browser

```
http://localhost:8000
```

### 4. Login as Different Roles

-   Clinic Admin: admin@example.com
-   Staff: staff@example.com
-   Doctor: doctor@example.com

### 5. Test Each Route

-   Navigate to Activity Logs
-   Apply filters
-   View details
-   Export CSV

---

## Helpful Aliases (PowerShell)

Add these to your PowerShell profile (`$PROFILE`):

```powershell
# Laravel aliases
function artisan { php artisan $args }
function tinker { php artisan tinker }
function serve { php artisan serve }
function migrate { php artisan migrate }
function clear-cache {
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear
    php artisan config:clear
}
function routes { php artisan route:list $args }
function test { php artisan test $args }

# Activity Logs specific
function activity-routes { php artisan route:list --name=activity-logs }
function activity-test { php artisan test --filter=ActivityLogTest }
```

Usage:

```powershell
artisan route:list
tinker
serve
migrate
clear-cache
routes --name=staff
test
activity-routes
activity-test
```

---

## Additional Resources

### Laravel Documentation

-   [Routing](https://laravel.com/docs/10.x/routing)
-   [Controllers](https://laravel.com/docs/10.x/controllers)
-   [Views](https://laravel.com/docs/10.x/views)
-   [Database](https://laravel.com/docs/10.x/database)

### Project Documentation

-   `ACTIVITY_LOG_IMPLEMENTATION.md` - Initial implementation details
-   `ACTIVITY_LOG_INTEGRATION.md` - Integration with existing code
-   `ACTIVITY_LOG_USAGE_GUIDE.md` - How to use the system
-   `ACTIVITY_LOG_TRAIT_USAGE.md` - Using the LogsActivity trait
-   `ACTIVITY_LOG_ROUTES_MENU.md` - Routes and menu configuration
-   `ACTIVITY_LOG_TESTING_GUIDE.md` - Comprehensive testing guide
-   `ACTIVITY_LOG_COMPLETE_SUMMARY.md` - Complete implementation summary

---

**Last Updated**: January 16, 2025
**Version**: 1.0
**Status**: Production Ready
