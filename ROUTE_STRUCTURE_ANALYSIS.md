# Route Structure Analysis - Role-Based Organization

## Overview

The NORSUCLINIC application uses a well-structured role-based routing system with three main user roles:

1. **clinic_admin** (Admin)
2. **staff**
3. **doctor**

## Route File Structure

### 1. **web.php** - Admin (clinic_admin) Routes

**Prefix**: `/admin`  
**Middleware**: `auth`, `checkUserStatus`, `checkImpersonateUser`, `role:clinic_admin`  
**Route Name Prefix**: None (uses default route names)

**Key Features**:

-   ✅ Primary admin dashboard and management
-   ✅ Permission-based access control for sensitive operations
-   ✅ Impersonation capabilities
-   ✅ Full CRUD access to all resources
-   ✅ Settings, countries, states, cities management
-   ✅ Role and permission management
-   ✅ Front-end CMS management

**Example Route Pattern**:

```php
Route::prefix('admin')->middleware('role:clinic_admin')->group(function () {
    Route::resource('brands', BrandController::class);
    // Results in: /admin/brands with route name: brands.index
});
```

---

### 2. **staff.php** - Staff Routes

**Prefix**: `/staff`  
**Middleware**: `auth`, `xss`, `checkUserStatus`, `role:staff`  
**Route Name Prefix**: `staff.`

**Key Features**:

-   ✅ Staff-specific dashboard
-   ✅ Permission-based access control
-   ✅ Limited management capabilities compared to admin
-   ✅ Can manage patients, appointments, medicines
-   ✅ Can assist doctors with prescriptions and visits
-   ✅ View-only access to some admin features (roles, currencies, countries)

**Example Route Pattern**:

```php
Route::prefix('staff')->name('staff.')->middleware('role:staff')->group(function () {
    Route::resource('brands', BrandController::class);
    // Results in: /staff/brands with route name: staff.brands.index
});
```

---

### 3. **doctor.php** - Doctor Routes

**Prefix**: `/doctors`  
**Middleware**: `auth`, `xss`, `checkUserStatus`, `role:doctor`  
**Route Name Prefix**: `doctors.`

**Key Features**:

-   ✅ Doctor-specific dashboard
-   ✅ Permission-based access control
-   ✅ Manage their own appointments and schedules
-   ✅ Manage patient visits and prescriptions
-   ✅ Manage their own holidays
-   ✅ Can manage patients, medicines, and services
-   ✅ Transaction viewing capabilities

**Example Route Pattern**:

```php
Route::prefix('doctors')->name('doctors.')->middleware('role:doctor')->group(function () {
    Route::resource('brands', BrandController::class);
    // Results in: /doctors/brands with route name: doctors.brands.index
});
```

---

## Module Availability by Role

### ✅ Shared Modules (All 3 Roles)

| Module                 | Admin Route                 | Staff Route                 | Doctor Route                  | Permission Required        |
| ---------------------- | --------------------------- | --------------------------- | ----------------------------- | -------------------------- |
| **Brands**             | `/admin/brands`             | `/staff/brands`             | `/doctors/brands`             | `manage_medicines`         |
| **Categories**         | `/admin/categories`         | `/staff/categories`         | `/doctors/categories`         | `manage_medicines`         |
| **Medicines**          | `/admin/medicines`          | `/staff/medicines`          | `/doctors/medicines`          | `manage_medicines`         |
| **Medicine Purchase**  | `/admin/medicine-purchase`  | `/staff/medicine-purchase`  | `/doctors/medicine-purchase`  | `manage_medicines`         |
| **Medicine History**   | `/admin/medicine-history`   | `/staff/medicine-history`   | `/doctors/medicine-history`   | `manage_medicines`         |
| **Patients**           | `/admin/patients`           | `/staff/patients`           | `/doctors/patients`           | `manage_patients`          |
| **Appointments**       | `/admin/appointments`       | `/staff/appointments`       | `/doctors/appointments`       | `manage_appointments`      |
| **Visits**             | `/admin/visits`             | `/staff/visits`             | `/doctors/visits`             | `manage_patient_visits`    |
| **Prescriptions**      | `/admin/prescriptions`      | `/staff/prescriptions`      | `/doctors/prescriptions`      | Auto-accessible            |
| **Services**           | `/admin/services`           | `/staff/services`           | `/doctors/services`           | `manage_services`          |
| **Service Categories** | `/admin/service-categories` | `/staff/service-categories` | `/doctors/service-categories` | `manage_services`          |
| **Specializations**    | `/admin/specializations`    | `/staff/specializations`    | `/doctors/specializations`    | `manage_specialties`       |
| **Doctor Sessions**    | `/admin/doctor-sessions`    | `/staff/doctor-sessions`    | `/doctors/doctor-sessions`    | `manage_doctor_sessions`   |
| **Request Documents**  | `/admin/request-documents`  | `/staff/request-documents`  | `/doctors/request-documents`  | `manage_request_documents` |
| **Transactions**       | `/admin/transactions`       | `/staff/transactions`       | `/doctors/transactions`       | `manage_transactions`      |

### 🔒 Admin-Only Modules

| Module                 | Route                     | Permission Required |
| ---------------------- | ------------------------- | ------------------- |
| **Doctors Management** | `/admin/doctors`          | `manage_doctors`    |
| **Staff Management**   | `/admin/staffs`           | `manage_staff`      |
| **Countries**          | `/admin/countries`        | `manage_countries`  |
| **States**             | `/admin/states`           | `manage_states`     |
| **Cities**             | `/admin/cities`           | `manage_cities`     |
| **Roles**              | `/admin/roles`            | `manage_roles`      |
| **Settings**           | `/admin/settings`         | `manage_settings`   |
| **Currencies**         | `/admin/currencies`       | `manage_currencies` |
| **CMS**                | `/admin/cms`              | `manage_front_cms`  |
| **Enquiries**          | `/admin/enquiries`        | `manage_front_cms`  |
| **Subscribers**        | `/admin/subscribers`      | `manage_front_cms`  |
| **Clinic Schedules**   | `/admin/clinic-schedules` | `manage_settings`   |
| **Impersonation**      | `/admin/impersonate/{id}` | Auto (admin only)   |
| **Logs**               | `/admin/logs`             | Auto (admin only)   |

### 👨‍⚕️ Doctor-Specific Features

| Feature                         | Route                            | Permission Required      |
| ------------------------------- | -------------------------------- | ------------------------ |
| **Doctor Dashboard**            | `/doctors/dashboard`             | Auto                     |
| **Doctor Holiday Management**   | `/doctors/holidays`              | `manage_doctors_holiday` |
| **Doctor Appointment Calendar** | `/doctors/appointments-calendar` | `manage_appointments`    |
| **Doctor Schedule Edit**        | `/doctors/doctor-schedule-edit`  | `manage_doctor_sessions` |

### 👥 Staff-Specific Features

| Feature                   | Route                          | Permission Required   |
| ------------------------- | ------------------------------ | --------------------- |
| **Staff Dashboard**       | `/staff/dashboard`             | Auto                  |
| **Appointments Calendar** | `/staff/appointments-calendar` | `manage_appointments` |
| **View-Only Roles**       | `/staff/roles`                 | `manage_roles`        |
| **View-Only Currencies**  | `/staff/currencies`            | `manage_currencies`   |
| **View-Only Countries**   | `/staff/countries`             | `manage_countries`    |

---

## Route Naming Convention Summary

### Admin Routes (clinic_admin)

```
Route Name Pattern: {module}.{action}
Examples:
- brands.index      → GET    /admin/brands
- brands.create     → GET    /admin/brands/create
- brands.store      → POST   /admin/brands
- brands.show       → GET    /admin/brands/{id}
- brands.edit       → GET    /admin/brands/{id}/edit
- brands.update     → PATCH  /admin/brands/{id}
- brands.destroy    → DELETE /admin/brands/{id}
```

### Staff Routes

```
Route Name Pattern: staff.{module}.{action}
Examples:
- staff.brands.index      → GET    /staff/brands
- staff.brands.create     → GET    /staff/brands/create
- staff.brands.store      → POST   /staff/brands
- staff.brands.show       → GET    /staff/brands/{id}
- staff.brands.edit       → GET    /staff/brands/{id}/edit
- staff.brands.update     → PUT    /staff/brands/{id}
- staff.brands.destroy    → DELETE /staff/brands/{id}
```

### Doctor Routes

```
Route Name Pattern: doctors.{module}.{action}
Examples:
- doctors.brands.index      → GET    /doctors/brands
- doctors.brands.create     → GET    /doctors/brands/create
- doctors.brands.store      → POST   /doctors/brands
- doctors.brands.show       → GET    /doctors/brands/{id}
- doctors.brands.edit       → GET    /doctors/brands/{id}/edit
- doctors.brands.update     → PATCH  /doctors/brands/{id}
- doctors.brands.destroy    → DELETE /doctors/brands/{id}
```

---

## Middleware Stack by Role

### Admin (clinic_admin)

```php
'auth'                    // Ensures user is authenticated
'checkUserStatus'         // Ensures user account is active
'checkImpersonateUser'    // Checks impersonation status
'role:clinic_admin'       // Ensures user has clinic_admin role
'permission:{name}'       // Additional permission checks for specific resources
```

### Staff

```php
'auth'                    // Ensures user is authenticated
'xss'                     // XSS protection
'checkUserStatus'         // Ensures user account is active
'role:staff'              // Ensures user has staff role
'permission:{name}'       // Permission-based access control
```

### Doctor

```php
'auth'                    // Ensures user is authenticated
'xss'                     // XSS protection
'checkUserStatus'         // Ensures user account is active
'role:doctor'             // Ensures user has doctor role
'permission:{name}'       // Permission-based access control
```

---

## Permission System

### Shared Permissions (All Roles Can Have)

-   `manage_patients`
-   `manage_appointments`
-   `manage_patient_visits`
-   `manage_medicines`
-   `manage_services`
-   `manage_specialties`
-   `manage_doctor_sessions`
-   `manage_request_documents`
-   `manage_transactions`

### Admin-Only Permissions

-   `manage_admin_dashboard`
-   `manage_doctors`
-   `manage_staff`
-   `manage_countries`
-   `manage_states`
-   `manage_cities`
-   `manage_roles`
-   `manage_settings`
-   `manage_currencies`
-   `manage_front_cms`

### Doctor-Specific Permissions

-   `manage_doctors_holiday` (Doctors manage their own holidays)

---

## Route File Organization

```
routes/
├── web.php          # Admin routes + public routes + shared utilities
├── staff.php        # All staff-specific routes
├── doctor.php       # All doctor-specific routes
├── patient.php      # Patient portal routes
├── auth.php         # Authentication routes
└── upgrade.php      # Upgrade/migration routes
```

---

## Security Features

### 1. **Role-Based Access Control (RBAC)**

-   ✅ Each route file has strict role middleware
-   ✅ Routes are isolated by role prefix
-   ✅ Cannot access other role's routes without proper role

### 2. **Permission-Based Access Control**

-   ✅ Fine-grained permissions within each role
-   ✅ Resources protected by `permission:` middleware
-   ✅ Flexible permission assignment per user

### 3. **Additional Security**

-   ✅ `checkUserStatus` - Prevents disabled accounts from accessing system
-   ✅ `xss` - Cross-site scripting protection for staff and doctors
-   ✅ `checkImpersonateUser` - Special handling for admin impersonation
-   ✅ `auth` - Session-based authentication

---

## Best Practices Implemented

### ✅ Separation of Concerns

-   Each role has its own dedicated route file
-   Clear boundaries between role capabilities

### ✅ Consistent Naming

-   Staff routes: `staff.{module}.{action}`
-   Doctor routes: `doctors.{module}.{action}`
-   Admin routes: `{module}.{action}`

### ✅ Resource Controllers

-   Most modules use `Route::resource()` for CRUD operations
-   Consistent REST patterns across all roles

### ✅ Middleware Stacking

-   Proper order: `auth` → `xss` → `checkUserStatus` → `role:{name}` → `permission:{name}`

### ✅ URL Prefixing

-   Admin: `/admin/*`
-   Staff: `/staff/*`
-   Doctor: `/doctors/*`
-   Patient: `/patients/*`

---

## Role-Aware Routing Pattern Used in Views

All views use this ternary pattern for role-aware navigation:

```php
{{ isRole('clinic_admin') ? route('module.action') :
   (isRole('staff') ? route('staff.module.action') :
   route('doctors.module.action')) }}
```

**Examples**:

```php
// Index routes
{{ isRole('clinic_admin') ? route('brands.index') :
   (isRole('staff') ? route('staff.brands.index') :
   route('doctors.brands.index')) }}

// Create routes
{{ isRole('clinic_admin') ? route('medicines.create') :
   (isRole('staff') ? route('staff.medicines.create') :
   route('doctors.medicines.create')) }}

// Edit routes
{{ isRole('clinic_admin') ? route('categories.edit', $id) :
   (isRole('staff') ? route('staff.categories.edit', $id) :
   route('doctors.categories.edit', $id)) }}
```

---

## Summary

### ✅ **Well-Structured Route Organization**

-   3 separate route files for 3 main roles
-   Clear URL prefixing for each role
-   Consistent route naming conventions

### ✅ **Comprehensive Security**

-   Role-based middleware on all routes
-   Permission-based access within roles
-   User status checking
-   XSS protection for staff and doctors

### ✅ **Flexibility**

-   Permission system allows fine-grained control
-   Shared modules accessible by all roles with proper permissions
-   Role-specific features (doctor holidays, admin impersonation)

### ✅ **Scalability**

-   Easy to add new modules to any role
-   Easy to adjust permissions per role
-   Clear separation makes maintenance easier

---

**Total Route Count**:

-   **Admin Routes**: ~100+ routes
-   **Staff Routes**: ~80+ routes
-   **Doctor Routes**: ~70+ routes

**Role Distribution**:

-   **clinic_admin**: Full system access with all permissions
-   **staff**: Operational access with limited admin features
-   **doctor**: Clinical access focused on patient care

---

**Date**: October 3, 2025  
**Status**: ✅ Production-Ready Route Structure  
**Security**: ✅ Role-based + Permission-based Access Control
