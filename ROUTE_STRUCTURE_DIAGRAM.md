# Route Structure Diagram

Visual representation of the consolidated route structure in `routes/web.php`

---

## 📊 Before Consolidation

```
routes/
├── web.php (300 lines)
│   ├── Public Routes
│   ├── Admin Routes (with checkImpersonateUser)
│   └── Admin Medicine Routes
│
├── staff.php (200 lines)
│   └── All Staff Routes
│
└── doctor.php (150 lines)
    └── All Doctor Routes

Total: 3 files, ~650 lines, duplications present
```

---

## 📊 After Consolidation

```
routes/
└── web.php (640 lines) ⭐ CONSOLIDATED
    │
    ├── [Lines 1-155] Public & Common Routes
    │   ├── Login Route
    │   ├── Frontend Routes (setLanguage middleware)
    │   ├── Payment Routes (Stripe, PayPal, Paytm)
    │   ├── Enquiry & Subscribe Routes
    │   ├── Dark Mode & Language Switch
    │   ├── Notification Routes
    │   ├── Profile Routes (auth middleware)
    │   └── AJAX Helper Routes (get-states, get-cities)
    │
    ├── [Lines 156-306] STAFF ROUTES ═══════════════════════
    │   │   Prefix: /staff
    │   │   Middleware: auth + xss + checkUserStatus + role:staff
    │   │
    │   ├── Dashboard (staff.dashboard)
    │   ├── Patient Management (permission:manage_patients)
    │   ├── Appointment Management (permission:manage_appointments)
    │   ├── Transaction Management (permission:manage_transactions)
    │   ├── Doctor Management (permission:manage_doctors)
    │   ├── Patient Visits (permission:manage_patient_visits)
    │   ├── Services Management (permission:manage_services)
    │   ├── Specializations (permission:manage_specialties)
    │   ├── Doctor Sessions (permission:manage_doctor_sessions)
    │   ├── Request Documents (permission:manage_request_documents)
    │   ├── Prescription Management
    │   ├── Medicine Management (permission:manage_medicines)
    │   │   ├── Categories
    │   │   ├── Brands
    │   │   ├── Medicines
    │   │   ├── Purchase
    │   │   └── History
    │   ├── Enquiry Management
    │   ├── CMS Management (permission:manage_front_cms)
    │   ├── Settings Management (permission:manage_settings)
    │   ├── Roles Management (permission:manage_roles)
    │   ├── Currencies Management (permission:manage_currencies)
    │   └── Countries Management (permission:manage_countries)
    │
    ├── [Lines 307-335] ADMIN ROUTES ═══════════════════════
    │   │   Prefix: /admin
    │   │   Middleware: auth + checkUserStatus + role:clinic_admin
    │   │   ⚠️ checkImpersonateUser REMOVED
    │   │
    │   ├── Dashboard (permission:manage_admin_dashboard)
    │   ├── Dashboard Patients Data
    │   ├── Logs (Laravel Log Viewer)
    │   ├── Impersonate Routes
    │   ├── Email Verification
    │   ├── Doctor Management (permission:manage_doctors)
    │   ├── Countries Management (permission:manage_countries)
    │   ├── States Management (permission:manage_states)
    │   ├── Cities Management (permission:manage_cities)
    │   ├── Roles Management (permission:manage_roles)
    │   ├── Settings & Schedules (permission:manage_settings)
    │   │   ├── Settings
    │   │   ├── Clinic Schedules
    │   │   └── Holidays
    │   ├── Patient Management (permission:manage_patients)
    │   ├── Request Documents (permission:manage_request_documents)
    │   ├── Doctor Sessions (permission:manage_doctor_sessions)
    │   ├── Specializations (permission:manage_specialties)
    │   ├── Services Management (permission:manage_services)
    │   ├── Staff Management (permission:manage_staff)
    │   ├── Appointments & Transactions (permission:manage_appointments)
    │   │   ├── Appointments Calendar
    │   │   └── Transactions
    │   ├── Currencies Management (permission:manage_currencies)
    │   ├── Patient Visits/Encounters (permission:manage_patient_visits)
    │   ├── CMS/Front Management (permission:manage_front_cms)
    │   │   ├── CMS
    │   │   ├── Banner/Slider
    │   │   ├── Enquiries
    │   │   └── Subscribers
    │   └── Prescription Management
    │
    ├── [Lines 336-483] DOCTOR ROUTES ═══════════════════════
    │   │   Prefix: /doctors
    │   │   Middleware: auth + xss + checkUserStatus + role:doctor
    │   │
    │   ├── Dashboard (doctors.dashboard)
    │   ├── Doctor Dashboard Data
    │   ├── Patient Detail
    │   ├── Appointment Management (permission:manage_appointments)
    │   │   ├── Full CRUD
    │   │   ├── Calendar View
    │   │   ├── PDF Export
    │   │   ├── Status Change
    │   │   └── Payment Status
    │   ├── Doctor Session Management (permission:manage_doctor_sessions)
    │   │   ├── Session Time
    │   │   ├── Sessions CRUD
    │   │   ├── Slot by Gap
    │   │   └── Schedule Edit
    │   ├── Patient Visits (permission:manage_patient_visits)
    │   │   ├── Visits CRUD
    │   │   ├── Problems (Add/Delete)
    │   │   ├── Observations (Add/Delete)
    │   │   ├── Notes (Add/Delete)
    │   │   └── Prescriptions (Add/Delete/Edit)
    │   ├── Patient Appointments View
    │   ├── Doctor Details
    │   ├── Transactions (permission:manage_transactions)
    │   ├── Holiday Management (permission:manage_doctors_holiday)
    │   ├── Prescription Management
    │   ├── Patient Management (permission:manage_patients)
    │   ├── Services Management (permission:manage_services)
    │   ├── Specializations (permission:manage_specialties)
    │   ├── Request Documents (permission:manage_request_documents)
    │   └── Medicine Management (permission:manage_medicines)
    │       ├── Categories
    │       ├── Brands
    │       ├── Medicines
    │       ├── Purchase
    │       └── History
    │
    ├── [Lines 484-520] ADMIN MEDICINE ROUTES ═══════════════
    │   │   Prefix: /admin
    │   │   Middleware: auth + checkUserStatus
    │   │   Note: No specific permission required
    │   │
    │   ├── Medicine Categories
    │   │   ├── CRUD Operations
    │   │   └── Active/Deactive
    │   ├── Medicine Brands
    │   │   └── CRUD Operations
    │   ├── Medicines
    │   │   ├── CRUD Operations
    │   │   ├── Modal View
    │   │   └── Usage Check
    │   ├── Medicine Purchase
    │   │   ├── CRUD Operations
    │   │   ├── Export Excel
    │   │   ├── Get Medicine
    │   │   └── Used Medicine List
    │   └── Medicine History
    │       ├── CRUD Operations
    │       ├── Store Patient
    │       ├── PDF Export
    │       └── Get Medicine Category
    │
    └── [Lines 521-end] Cleanup & Requires
        ├── Delete Old Patients Route
        └── Requires
            ├── auth.php
            ├── patient.php
            └── upgrade.php

Total: 1 file, ~640 lines, NO duplications
```

---

## 🔑 Middleware Key

| Symbol | Middleware             | Description                  |
| ------ | ---------------------- | ---------------------------- |
| 🔐     | `auth`                 | User must be authenticated   |
| 🛡️     | `xss`                  | XSS protection               |
| ✅     | `checkUserStatus`      | User account must be active  |
| 👤     | `role:clinic_admin`    | Admin role required          |
| 👤     | `role:staff`           | Staff role required          |
| 👤     | `role:doctor`          | Doctor role required         |
| 🔒     | `permission:*`         | Specific permission required |
| ⚠️     | `checkImpersonateUser` | **REMOVED** from all routes  |

---

## 📈 Route Count by Section

```
┌──────────────────────┬───────┬──────────┐
│ Section              │ Lines │ Routes   │
├──────────────────────┼───────┼──────────┤
│ Public Routes        │  155  │   ~30    │
│ Staff Routes         │  150  │   ~70    │
│ Admin Routes         │  148  │   ~60    │
│ Doctor Routes        │  147  │   ~65    │
│ Admin Medicine       │   36  │   ~25    │
├──────────────────────┼───────┼──────────┤
│ TOTAL                │  636  │  ~250    │
└──────────────────────┴───────┴──────────┘
```

---

## 🎯 Permission Matrix

| Feature         | Admin   | Staff      | Doctor  |
| --------------- | ------- | ---------- | ------- |
| Dashboard       | ✅      | ✅         | ✅      |
| Patients        | ✅ Full | ✅ Full    | ✅ Full |
| Appointments    | ✅ Full | ✅ Full    | ✅ Full |
| Doctors         | ✅ Full | ✅ Full    | ❌      |
| Staff           | ✅ Full | ❌         | ❌      |
| Services        | ✅ Full | ✅ Full    | ✅ Full |
| Specializations | ✅ Full | ✅ Full    | ✅ Full |
| Doctor Sessions | ✅ Full | ✅ Full    | ✅ Own  |
| Visits          | ✅ Full | ✅ Full    | ✅ Full |
| Prescriptions   | ✅ Full | ✅ Full    | ✅ Full |
| Medicines       | ✅ Full | ✅ Full    | ✅ Full |
| Transactions    | ✅ Full | ✅ View    | ✅ View |
| Settings        | ✅ Full | ✅ Limited | ❌      |
| Roles           | ✅ Full | ✅ View    | ❌      |
| Currencies      | ✅ Full | ✅ View    | ❌      |
| Countries       | ✅ Full | ✅ View    | ❌      |
| CMS             | ✅ Full | ✅ Full    | ❌      |
| Enquiries       | ✅ Full | ✅ Full    | ❌      |
| Holidays        | ✅ Full | ✅ Full    | ✅ Own  |
| Impersonate     | ✅ Only | ❌         | ❌      |

---

## 🔄 Route Naming Convention

### Staff Routes

```
Pattern: staff.{resource}.{action}
Examples:
  - staff.dashboard
  - staff.patients.index
  - staff.appointments.create
  - staff.medicines.show
```

### Doctor Routes

```
Pattern: doctors.{resource}.{action}
Examples:
  - doctors.dashboard
  - doctors.patients.index
  - doctors.appointments.create
  - doctors.prescriptions.show
```

### Admin Routes

```
Pattern: {resource}.{action} OR admin.{action}
Examples:
  - admin.dashboard
  - patients.index
  - appointments.create
  - medicines.show
```

---

## 🚀 Quick Navigation Guide

### Finding Routes in web.php

1. **Public Routes**: Line 1-155

    - Search: `Route::get('/')`

2. **Staff Routes**: Line 156-306

    - Search: `// STAFF ROUTES`

3. **Admin Routes**: Line 307-335

    - Search: `// ADMIN ROUTES`

4. **Doctor Routes**: Line 336-483

    - Search: `// DOCTOR ROUTES`

5. **Admin Medicine**: Line 484-520
    - Search: `// ADMIN MEDICINE ROUTES`

---

## 📋 Route Group Structure

```php
// Pattern used throughout
Route::prefix('{role}')
    ->name('{role}.')
    ->middleware(['auth', 'xss', 'checkUserStatus', 'role:{role}'])
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', ...)

        // Feature Groups with Permissions
        Route::middleware('permission:{permission}')->group(function () {
            Route::resource('{resource}', ...);
            // Additional custom routes
        });

    });
```

---

## ✨ Benefits of This Structure

1. **Clear Separation** - Visual dividers between sections
2. **Consistent Organization** - Similar structure across all roles
3. **Permission Grouping** - Easy to see what requires what permission
4. **Maintainability** - Easy to find and update routes
5. **Scalability** - Easy to add new features
6. **Documentation** - Self-documenting code structure

---

**Last Updated:** October 7, 2025  
**File:** routes/web.php  
**Total Lines:** ~640  
**Total Routes:** ~250  
**Sections:** 4 major (Staff, Admin, Doctor, Admin Medicine)
