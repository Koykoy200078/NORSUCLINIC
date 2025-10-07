# Route Quick Reference Guide

Quick navigation guide for all routes in `routes/web.php`

---

## 🗂️ File Structure

```
routes/
├── api.php          - API routes
├── auth.php         - Authentication routes
├── channels.php     - Broadcasting channels
├── console.php      - Console commands
├── patient.php      - Patient-specific routes
├── upgrade.php      - Upgrade routes
└── web.php          - ⭐ ALL WEB ROUTES (Admin, Staff, Doctor)
```

---

## 🔐 Middleware Stack

### **Admin Routes**

```php
'auth', 'checkUserStatus', 'role:clinic_admin'
```

### **Staff Routes**

```php
'auth', 'xss', 'checkUserStatus', 'role:staff'
```

### **Doctor Routes**

```php
'auth', 'xss', 'checkUserStatus', 'role:doctor'
```

---

## 📍 Route Prefixes & Names

| Role   | Prefix     | Route Name Prefix | Example             |
| ------ | ---------- | ----------------- | ------------------- |
| Admin  | `/admin`   | Direct names      | `admin.dashboard`   |
| Staff  | `/staff`   | `staff.`          | `staff.dashboard`   |
| Doctor | `/doctors` | `doctors.`        | `doctors.dashboard` |

---

## 🎯 Common Routes by Feature

### **Dashboard**

-   Admin: `GET /admin/dashboard` → `admin.dashboard`
-   Staff: `GET /staff/dashboard` → `staff.dashboard`
-   Doctor: `GET /doctors/dashboard` → `doctors.dashboard`

### **Patients**

-   Admin: `GET /admin/patients` → `patients.index`
-   Staff: `GET /staff/patients` → `staff.patients.index`
-   Doctor: `GET /doctors/patients` → `doctors.patients.index`

### **Appointments**

-   Admin: `GET /admin/appointments` → `appointments.index`
-   Staff: `GET /staff/appointments` → `staff.appointments.index`
-   Doctor: `GET /doctors/appointments` → `doctors.appointments.index`

### **Medicines**

-   Admin: `GET /admin/medicines` → `medicines.index`
-   Staff: `GET /staff/medicines` → `staff.medicines.index`
-   Doctor: `GET /doctors/medicines` → `doctors.medicines.index`

### **Prescriptions**

-   Admin: `GET /admin/prescriptions` → `prescriptions.show`
-   Staff: `GET /staff/prescriptions` → `staff.prescriptions.show`
-   Doctor: `GET /doctors/prescriptions` → `doctors.prescriptions.show`

---

## 🔑 Permissions Required

### **Admin Permissions**

-   `manage_admin_dashboard` - Access admin dashboard
-   `manage_doctors` - Manage doctors
-   `manage_patients` - Manage patients
-   `manage_appointments` - Manage appointments
-   `manage_staff` - Manage staff
-   `manage_services` - Manage services
-   `manage_specialties` - Manage specializations
-   `manage_doctor_sessions` - Manage doctor schedules
-   `manage_request_documents` - Manage request documents
-   `manage_patient_visits` - Manage patient visits
-   `manage_front_cms` - Manage CMS/front-end
-   `manage_currencies` - Manage currencies
-   `manage_countries` - Manage countries
-   `manage_states` - Manage states
-   `manage_cities` - Manage cities
-   `manage_roles` - Manage roles
-   `manage_settings` - Manage settings
-   `manage_transactions` - View transactions

### **Staff Permissions**

Same as admin, but with limited access on some features

### **Doctor Permissions**

-   `manage_appointments` - Full CRUD on appointments
-   `manage_doctor_sessions` - Manage own sessions
-   `manage_patient_visits` - Manage visits
-   `manage_patients` - Manage patients
-   `manage_services` - Manage services
-   `manage_specialties` - Manage specializations
-   `manage_request_documents` - Manage request documents
-   `manage_medicines` - Manage medicines
-   `manage_doctors_holiday` - Manage own holidays
-   `manage_transactions` - View only

---

## 🛠️ Special Routes

### **Impersonation (Admin Only)**

-   `GET /admin/impersonate/{id}` → `impersonate`
-   `GET /admin/impersonate-leave` → `impersonate.leave`

### **Public Routes (No Auth)**

-   `GET /` → `medical` (Front page)
-   `GET /login` → `login`
-   `POST /enquiries` → `enquiries.store`
-   `POST /subscribe` → `subscribe.store`

### **Payment Routes**

-   `GET /medical-payment-success` → `medical-appointment-payment-success`
-   `GET /medical-payment-failed` → `medical-appointment-failed-payment`
-   `GET /paypal-onboard` → `paypal.init`
-   `GET /authorize-onboard` → `authorize.init`
-   `GET /paytm-init` → `paytm.init`

### **AJAX/API-like Routes**

-   `GET /get-states` → `get-state`
-   `GET /get-cities` → `get-city`
-   `GET /get-service` → `get-service`
-   `GET /get-charge` → `get-charge`
-   `GET /get-patient-name` → `get-patient-name`

---

## 📋 Resource Routes

Each role has resource routes for:

-   **Patients** - Full CRUD
-   **Appointments** - Full CRUD (except edit/update for some roles)
-   **Visits** - Full CRUD
-   **Services** - Full CRUD
-   **Service Categories** - Full CRUD
-   **Specializations** - Full CRUD
-   **Doctor Sessions** - Full CRUD
-   **Request Documents** - Full CRUD
-   **Prescriptions** - Full CRUD (custom create/edit)
-   **Medicines** - Full CRUD
-   **Categories** - Full CRUD
-   **Brands** - Full CRUD
-   **Medicine Purchase** - Full CRUD
-   **Medicine History** - Full CRUD

---

## 🔄 Route Naming Patterns

### **Resource Routes**

```php
Route::resource('patients', PatientController::class);
// Creates: index, create, store, show, edit, update, destroy
```

### **Custom Named Routes**

```php
Route::get('dashboard', [Controller::class, 'method'])->name('role.dashboard');
```

### **Nested Routes**

```php
Route::get('appointments/{appointmentId}/prescription-create', ...)
    ->name('role.prescriptions.create');
```

---

## 🧪 Testing Routes

### **Check All Routes**

```bash
php artisan route:list
```

### **Filter by Prefix**

```bash
php artisan route:list | grep "staff."
php artisan route:list | grep "doctors."
php artisan route:list | grep "admin."
```

### **Filter by Method**

```bash
php artisan route:list --method=GET
php artisan route:list --method=POST
```

### **Filter by Name**

```bash
php artisan route:list --name=dashboard
```

---

## 💡 Tips

1. **Route Caching**

    ```bash
    php artisan route:cache    # Cache routes for production
    php artisan route:clear    # Clear route cache
    ```

2. **Route Debugging**

    ```bash
    php artisan route:list --path=staff
    php artisan route:list --path=doctors
    php artisan route:list --path=admin
    ```

3. **Named Route URLs**

    ```php
    route('staff.dashboard')           // /staff/dashboard
    route('doctors.appointments.show', 1) // /doctors/appointments/1
    ```

4. **Current Route Check**
    ```php
    request()->routeIs('staff.*')      // Is any staff route?
    request()->routeIs('doctors.dashboard') // Exact match
    ```

---

## 🔍 Finding Routes in Code

### **In web.php**

```
Line ~150: Start of STAFF ROUTES section
Line ~450: Start of DOCTOR ROUTES section
Line ~170: Start of ADMIN ROUTES section
Line ~340: Start of ADMIN MEDICINE ROUTES section
```

### **Search Tips**

-   Search for `// STAFF ROUTES` to find staff section
-   Search for `// DOCTOR ROUTES` to find doctor section
-   Search for `// ADMIN ROUTES` to find admin section
-   Search for `permission:manage_` to find permission-based routes

---

**Last Updated:** October 7, 2025
