# ROLE PERMISSIONS SEEDER - IMPLEMENTATION GUIDE

**Date**: October 7, 2025  
**Purpose**: Ensure staff and doctor roles get correct default permissions on every migration  
**Status**: ✅ COMPLETE

---

## Problem Solved

Previously, when re-migrating the database, staff and doctor roles would lose their permissions or have incorrect permissions assigned. This seeder ensures that the correct permissions are **always** set based on the current production database configuration.

---

## What Was Implemented

### 1. ✅ Created `RolePermissionsSeeder.php`

**Location**: `database/seeders/RolePermissionsSeeder.php`

**Purpose**: Sets default permissions for staff and doctor roles based on current production database state.

**Features**:

-   ✅ Syncs permissions for `doctor` role (7 permissions)
-   ✅ Syncs permissions for `staff` role (13 permissions)
-   ✅ Ensures `clinic_admin` has all permissions
-   ✅ Updates existing users with these roles
-   ✅ Cleans up old permissions before assigning new ones
-   ✅ Provides detailed console output during seeding

### 2. ✅ Updated `DatabaseSeeder.php`

Added `RolePermissionsSeeder` to the seeder chain to run after default permissions are created.

**Execution Order**:

```php
1. DefaultPermissionSeeder        // Creates all permissions
2. DefaultRoleSeeder              // Creates all roles
3. DefaultMedicinePermissionSeeder // Adds medicine permissions
4. DefaultAssignPermissionSeeder  // Basic permission assignments
5. RolePermissionsSeeder         // ⭐ Sets final correct permissions
6. Other seeders...
```

### 3. ✅ Created Helper Script

**Location**: `get_current_permissions.php`

**Purpose**: Easily extract current permissions from database to update the seeder.

---

## Current Permission Configuration

### Doctor Role (7 permissions)

```php
[
    'manage_appointments',
    'manage_doctor_sessions',
    'manage_doctors_holiday',
    'manage_medicines',
    'manage_patient_visits',
    'manage_request_documents',
    'manage_transactions',
]
```

### Staff Role (13 permissions)

```php
[
    'manage_appointments',
    'manage_doctor_sessions',
    'manage_doctors',
    'manage_doctors_holiday',
    'manage_medicines',
    'manage_patient_visits',
    'manage_patients',
    'manage_request_documents',
    'manage_services',
    'manage_specialties',
    'manage_staff',
    'manage_staff_dashboard',
    'manage_transactions',
]
```

### Clinic Admin Role (All 21 permissions)

```php
[
    'manage_admin_dashboard',
    'manage_appointments',
    'manage_cities',
    'manage_countries',
    'manage_currencies',
    'manage_doctor_sessions',
    'manage_doctors',
    'manage_doctors_holiday',
    'manage_front_cms',
    'manage_medicines',
    'manage_patient_visits',
    'manage_patients',
    'manage_request_documents',
    'manage_roles',
    'manage_services',
    'manage_settings',
    'manage_specialties',
    'manage_staff',
    'manage_staff_dashboard',
    'manage_states',
    'manage_transactions',
]
```

---

## Key Differences Between Roles

| Permission               | Clinic Admin | Staff | Doctor |
| ------------------------ | ------------ | ----- | ------ |
| manage_admin_dashboard   | ✅           | ❌    | ❌     |
| manage_appointments      | ✅           | ✅    | ✅     |
| manage_cities            | ✅           | ❌    | ❌     |
| manage_countries         | ✅           | ❌    | ❌     |
| manage_currencies        | ✅           | ❌    | ❌     |
| manage_doctor_sessions   | ✅           | ✅    | ✅     |
| manage_doctors           | ✅           | ✅    | ❌     |
| manage_doctors_holiday   | ✅           | ✅    | ✅     |
| manage_front_cms         | ✅           | ❌    | ❌     |
| manage_medicines         | ✅           | ✅    | ✅     |
| manage_patient_visits    | ✅           | ✅    | ✅     |
| manage_patients          | ✅           | ✅    | ❌     |
| manage_request_documents | ✅           | ✅    | ✅     |
| manage_roles             | ✅           | ❌    | ❌     |
| manage_services          | ✅           | ✅    | ❌     |
| manage_settings          | ✅           | ❌    | ❌     |
| manage_specialties       | ✅           | ✅    | ❌     |
| manage_staff             | ✅           | ✅    | ❌     |
| manage_staff_dashboard   | ✅           | ✅    | ❌     |
| manage_states            | ✅           | ❌    | ❌     |
| manage_transactions      | ✅           | ✅    | ✅     |

---

## Usage Instructions

### When Re-migrating Database

**Standard Migration**:

```bash
php artisan migrate:fresh --seed
```

This will:

1. Drop all tables
2. Run all migrations
3. Run all seeders (including `RolePermissionsSeeder`)
4. Set correct permissions for staff and doctor roles

**Output Example**:

```
Setting up role permissions...
Processing doctor role...
  ✓ Assigned 7 permissions to doctor role
  ✓ Updated permissions for 5 user(s) with doctor role
Processing staff role...
  ✓ Assigned 13 permissions to staff role
  ✓ Updated permissions for 10 user(s) with staff role
Processing clinic_admin role...
  ✓ Assigned all 21 permissions to clinic_admin role
  ✓ Updated permissions for 2 admin user(s)
Role permissions setup completed successfully!
```

### Running Just the Permission Seeder

If you want to reset permissions without re-migrating everything:

```bash
php artisan db:seed --class=RolePermissionsSeeder
```

---

## Updating Permissions in the Future

### Step 1: Modify Permissions in Database

Make your permission changes manually in the database or through the application UI.

### Step 2: Extract Current Permissions

Run the helper script to see current permissions:

```bash
php get_current_permissions.php
```

### Step 3: Update the Seeder

Open `database/seeders/RolePermissionsSeeder.php` and update the arrays in the `$rolePermissions` variable:

```php
$rolePermissions = [
    'doctor' => [
        // Add or remove permissions here
        'manage_appointments',
        'manage_new_permission',  // NEW
        // ...
    ],
    'staff' => [
        // Add or remove permissions here
        'manage_appointments',
        'manage_another_permission',  // NEW
        // ...
    ],
];
```

### Step 4: Test the Changes

Run the seeder to test:

```bash
php artisan db:seed --class=RolePermissionsSeeder
```

---

## How It Works

### 1. Clean Slate

The seeder first **removes all permissions** from the role using `syncPermissions([])` to ensure a clean state.

### 2. Assign Permissions

Then it assigns **only** the permissions listed in the seeder configuration.

### 3. Update Users

Any existing users with that role get their permissions updated to match.

### 4. Admin Always Has All

The `clinic_admin` role **always** gets all available permissions, regardless of what's in the configuration.

---

## Benefits

### ✅ Consistency

-   Same permissions every time you migrate
-   No manual permission assignment needed
-   All environments (dev, staging, production) have identical permissions

### ✅ Documentation

-   The seeder file serves as documentation of what permissions each role should have
-   Easy to see permission differences between roles
-   Version controlled in Git

### ✅ Maintainability

-   One place to update permissions
-   Clear, readable code
-   Easy to modify in the future

### ✅ Safety

-   Won't accidentally give too many permissions
-   Won't accidentally remove needed permissions
-   Existing user permissions are updated automatically

---

## Related Files

### Seeder Files

-   ✅ `database/seeders/RolePermissionsSeeder.php` - Main seeder (NEW)
-   ✅ `database/seeders/DatabaseSeeder.php` - Updated to include new seeder
-   `database/seeders/DefaultRoleSeeder.php` - Creates roles
-   `database/seeders/DefaultPermissionSeeder.php` - Creates permissions
-   `database/seeders/DefaultAssignPermissionSeeder.php` - Basic assignments
-   `database/seeders/DefaultStaffSeeder.php` - Creates staff users
-   `database/seeders/StaffDoctorPermissionSeeder.php` - Legacy (can be removed)
-   `database/seeders/DefaultMedicinePermissionSeeder.php` - Medicine permissions
-   `database/seeders/DefaultHolidayPermissionSeeder.php` - Holiday permissions

### Helper Scripts

-   ✅ `get_current_permissions.php` - Extract permissions from database (NEW)

### Documentation

-   ✅ `ROLE_PERMISSIONS_SEEDER_GUIDE.md` - This file (NEW)
-   `STAFF_DOCTOR_ROLE_ROUTING_COMPLETE.md` - Role-based routing documentation

---

## Testing Checklist

After running migrations with the new seeder:

### ✅ Verify Doctor Role

```bash
# Login as doctor user
# Check these routes are accessible:
- /doctors/appointments
- /doctors/doctor-sessions
- /doctors/holidays
- /doctors/medicines
- /doctors/visits
- /doctors/request-documents
- /doctors/transactions

# Check these routes are NOT accessible:
- /admin/* (should redirect)
- /staff/* (should get 403)
```

### ✅ Verify Staff Role

```bash
# Login as staff user
# Check these routes are accessible:
- /staff/appointments
- /staff/doctor-sessions
- /staff/doctors
- /staff/holidays
- /staff/medicines
- /staff/visits
- /staff/patients
- /staff/request-documents
- /staff/services
- /staff/specialties
- /staff/dashboard
- /staff/transactions

# Check these routes are NOT accessible:
- /admin/settings (should get 403)
- /admin/roles (should get 403)
- /admin/currencies (should get 403)
```

### ✅ Verify Clinic Admin Role

```bash
# Login as clinic admin user
# Check ALL routes are accessible:
- /admin/* (all admin routes)
- Can access settings
- Can manage roles
- Can manage currencies
```

---

## Troubleshooting

### Issue: Permissions not updating after migration

**Solution**:

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Re-run the seeder
php artisan db:seed --class=RolePermissionsSeeder
```

### Issue: Permission not found error

**Solution**: Make sure the permission exists in `permissions` table. Run:

```bash
php artisan db:seed --class=DefaultPermissionSeeder
```

### Issue: Role not found error

**Solution**: Make sure the role exists in `roles` table. Run:

```bash
php artisan db:seed --class=DefaultRoleSeeder
```

### Issue: Want to see current permissions

**Solution**: Run the helper script:

```bash
php get_current_permissions.php
```

---

## Next Steps

### 1. Optional: Clean Up Legacy Seeders

Consider removing or consolidating these older seeders:

-   `StaffDoctorPermissionSeeder.php` (functionality now in `RolePermissionsSeeder.php`)

### 2. Add to CI/CD Pipeline

Ensure your deployment scripts run:

```bash
php artisan migrate:fresh --seed --force
```

### 3. Document for Team

Share this guide with your team so they understand:

-   How permissions are managed
-   How to update permissions
-   What permissions each role has

---

## Conclusion

✅ **Role permissions are now automatically configured on every migration**

✅ **Staff and doctor roles get exactly the permissions from your production database**

✅ **Easy to update in the future by modifying one seeder file**

✅ **Fully documented and maintainable**

---

**Created By**: GitHub Copilot  
**Date**: October 7, 2025  
**Status**: Ready for Production ✅
