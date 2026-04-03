# Role & Permission Audit Report

**Generated:** 2026-04-03 | **Last Verified:** 2026-04-03  
**Scope:** Roles, permissions, routes, and JS AJAX calls

---

## 1. Roles Overview

| Role           | Display Name | Is Default | Total Routes |
| -------------- | ------------ | ---------- | ------------ |
| `clinic_admin` | Clinic Admin | Yes        | 116          |
| `staff`        | Staff        | Yes        | 116          |
| `doctor`       | Doctor       | Yes        | 93           |
| `patient`      | Patient      | Yes        | 13           |

---

## 2. Permissions in Database

| ID  | Permission Name            | Display Name             |
| --- | -------------------------- | ------------------------ |
| 1   | `manage_medicines`         | Manage Medicines         |
| 2   | `manage_staff_dashboard`   | Manage Staff Dashboard   |
| 3   | `manage_doctors`           | Manage Doctors           |
| 4   | `manage_patients`          | Manage Patients          |
| 5   | `manage_staff`             | Manage Staff             |
| 6   | `manage_settings`          | Manage Settings          |
| 7   | `manage_specialties`       | Manage Specialties       |
| 8   | `manage_countries`         | Manage Countries         |
| 9   | `manage_states`            | Manage States            |
| 10  | `manage_cities`            | Manage Cities            |
| 11  | `manage_roles`             | Manage Roles             |
| 12  | `manage_admin_dashboard`   | Manage Admin Dashboard   |
| 13  | `manage_front_cms`         | Manage Front CMS         |
| 14  | `manage_request_documents` | Manage Request Documents |

---

## 3. Role → Permission Assignments (Live Database)

### clinic_admin

| Permission                 |
| -------------------------- |
| `manage_medicines`         |
| `manage_staff_dashboard`   |
| `manage_doctors`           |
| `manage_patients`          |
| `manage_staff`             |
| `manage_settings`          |
| `manage_specialties`       |
| `manage_countries`         |
| `manage_states`            |
| `manage_cities`            |
| `manage_roles`             |
| `manage_admin_dashboard`   |
| `manage_front_cms`         |
| `manage_request_documents` |

### staff

| Permission                 |
| -------------------------- |
| `manage_medicines`         |
| `manage_staff_dashboard`   |
| `manage_doctors`           |
| `manage_patients`          |
| `manage_specialties`       |
| `manage_request_documents` |

> **Updated 2026-04-03:** `manage_staff` removed from staff role. Only `clinic_admin` can create/edit/delete staff accounts.

### doctor

| Permission                 |
| -------------------------- |
| `manage_medicines`         |
| `manage_patients`          |
| `manage_specialties`       |
| `manage_request_documents` |

### patient

| Permission                 |
| -------------------------- |
| `manage_request_documents` |

---

## 4. Route Access Matrix by Role

### clinic_admin — 116 Routes (`admin/*`)

| Permission Required        | Route Count | Examples                                                |
| -------------------------- | ----------: | ------------------------------------------------------- |
| `manage_admin_dashboard`   |           1 | `admin/dashboard`                                       |
| `manage_cities`            |          14 | `admin/cities/*`, `admin/barangays/*`                   |
| `manage_countries`         |           8 | `admin/countries/*`                                     |
| `manage_doctors`           |           9 | `admin/doctors/*`, `admin/doctor-status`                |
| `manage_front_cms`         |           5 | `admin/cms`, `admin/banner/*`                           |
| `manage_patients`          |          17 | `admin/patients/*`, `admin/patient-queue/*`             |
| `manage_request_documents` |          10 | `admin/request-documents/*`                             |
| `manage_roles`             |           7 | `admin/roles/*`                                         |
| `manage_settings`          |           4 | `admin/settings/*`                                      |
| `manage_specialties`       |           7 | `admin/specializations/*`                               |
| `manage_staff`             |           7 | `admin/staffs/*`                                        |
| `manage_states`            |           8 | `admin/states/*`                                        |
| _(no permission check)_    |          19 | `admin/dashboard-patients`, `admin/impersonate/*`, etc. |

---

### staff — 116 Routes (`staff/*`)

| Permission Required        | Route Count | Examples                                                                                                                   |
| -------------------------- | ----------: | -------------------------------------------------------------------------------------------------------------------------- |
| `manage_countries`         |           2 | `staff/countries` (read-only: index + show)                                                                                |
| `manage_doctors`           |           9 | `staff/doctors/*`, `staff/doctor-status`                                                                                   |
| `manage_front_cms`         |           5 | `staff/cms`, `staff/banner/*`                                                                                              |
| `manage_medicines`         |          45 | `staff/medicines/*`, `staff/categories/*`, `staff/generics/*`, `staff/medicine-availability/*`, `staff/medicine-history/*` |
| `manage_patients`          |          19 | `staff/patients/*`, `staff/patient-queue/*`                                                                                |
| `manage_request_documents` |           8 | `staff/request-documents/*`                                                                                                |
| `manage_roles`             |           2 | `staff/roles` (read-only: index + show)                                                                                    |
| `manage_settings`          |           3 | `staff/settings/*`                                                                                                         |
| `manage_specialties`       |           7 | `staff/specializations/*`                                                                                                  |
| _(no permission check)_    |          16 | `staff/dashboard`, `staff/prescriptions/*`, etc.                                                                           |

> **Updated 2026-04-03:** `manage_staff` removed from staff permissions. Staff panel has no `staff/staffs/*` routes — staff account management is `clinic_admin` only.

---

### doctor — 93 Routes (`doctors/*`)

| Permission Required        | Route Count | Examples                                                                                                                             |
| -------------------------- | ----------: | ------------------------------------------------------------------------------------------------------------------------------------ |
| `manage_medicines`         |          45 | `doctors/medicines/*`, `doctors/categories/*`, `doctors/generics/*`, `doctors/medicine-availability/*`, `doctors/medicine-history/*` |
| `manage_patients`          |          10 | `doctors/patients/*`                                                                                                                 |
| `manage_request_documents` |           8 | `doctors/request-documents/*`                                                                                                        |
| `manage_specialties`       |           7 | `doctors/specializations/*`                                                                                                          |
| _(no permission check)_    |          23 | `doctors/dashboard`, `doctors/patient-queue/*`, `doctors/prescriptions/*`                                                            |

---

### patient — 13 Routes (`patients/*`)

| Route                                                   | Method             |
| ------------------------------------------------------- | ------------------ |
| `patients/change-default-password`                      | POST               |
| `patients/dashboard`                                    | GET                |
| `patients/dashboard-patients`                           | GET                |
| `patients/patients/{patientId}/prescription-create`     | GET                |
| `patients/prescription-medicine`                        | POST               |
| `patients/prescription-medicine-show/{id}`              | GET                |
| `patients/prescription-pdf/{id}`                        | GET                |
| `patients/prescriptions`                                | POST               |
| `patients/prescriptions/{prescription}`                 | GET / PUT / DELETE |
| `patients/prescriptions/{prescription}/active-deactive` | POST               |
| `patients/prescriptions/{prescription}/edit`            | GET                |

> All patient routes are role-only (no permission middleware — all patients have same access).

---

## 5. JS Route Call Audit

The JS `route()` helper resolves **named routes by name only** — not by current user role. When the same JS file is loaded in both the admin and staff panels, bare names like `route('doctor.status')` always resolve to the first registered route with that name, which is typically the `admin/*` route.

### 5.1 Routes Called in JS → Resolved Route → Role Gate

> **Verified 2026-04-03, Updated 2026-04-04** — All entries confirmed against live route registry.
> All rows marked ✅ have been fixed. Rows marked ⚠️ are remaining known issues.

| JS File                               | `route()` Call                                        | Resolves To                                  | Role Required  | Status                                                                             |
| ------------------------------------- | ----------------------------------------------------- | -------------------------------------------- | -------------- | ---------------------------------------------------------------------------------- |
| `doctors.js` line 87                  | `route('doctor.status')`                              | `admin/doctor-status`                        | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `doctors.js` line 48                  | `route('add.qualification')`                          | `admin/add-qualification`                    | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `doctors.js` line 100                 | `route('resend.email.verification', id)`              | `admin/email/verification-notification/{id}` | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `doctors.js` line 123                 | `route('emailVerified')`                              | `admin/email-verified`                       | `clinic_admin` | ✅ Fixed 2026-04-03 — routes added + `panelRoute()`                                |
| `staff.js` line 3                     | `route('staffs.destroy', id)`                         | `admin/staffs/{staff}`                       | `clinic_admin` | ✅ Fixed 2026-04-03 — Blade guard + data-delete-url                                |
| `staff.js` line 27                    | `route('resend.email.verification', id)`              | `admin/email/verification-notification/{id}` | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `staff.js` line 11                    | `route('emailVerified')`                              | `admin/email-verified`                       | `clinic_admin` | ✅ Fixed 2026-04-03 — routes added + `panelRoute()`                                |
| `patients.js` line 118                | `route('patients.destroy', id)` _(fallback)_          | `admin/patients/{patient}`                   | `clinic_admin` | ✅ Fixed 2026-04-04 via `panelRoute()`                                             |
| `patients.js` line 247                | `route('resend.email.verification', id)` _(fallback)_ | `admin/email/verification-notification/{id}` | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `patients.js` line 229                | `route('emailVerified')`                              | `admin/email-verified`                       | `clinic_admin` | ✅ Fixed 2026-04-03 — routes added + `panelRoute()`                                |
| `medicine_bill.js`                    | `route('get-medicine', id)`                           | `admin/get-medicine/{medicine}`              | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `medicine_bill.js`                    | `route('get-medicine-category', id)`                  | `admin/get-medicine-category/{category}`     | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `medicine_bill.js`                    | `route('medicine-history.index')`                     | `admin/medicine-history`                     | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `medicine_bill.js`                    | `route('medicine-history.update', id)`                | `admin/medicine-history/{id}`                | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `medicine_bill.js`                    | `route('medicine-history.destroy', id)`               | `admin/medicine-history/{id}`                | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `medicine_bill.js`                    | `route('store.patient')`                              | `admin/medicine-history/store-patient`       | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `medicines.js`                        | `route('check.use.medicine', id)`                     | `admin/medicines-uses-check/{medicine}`      | _(none)_       | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `medicines.js`                        | `route('medicines.show.modal', id)`                   | `admin/medicines-show-modal/{medicine}`      | _(none)_       | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `category.js`                         | `route('active.deactive', id)`                        | `admin/categories/{id}/active-deactive`      | _(none)_       | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `specializations.js`                  | `route('specializations.store')`                      | `admin/specializations`                      | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `specializations.js`                  | `route('specializations.update', id)`                 | `admin/specializations/{id}`                 | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `specializations.js`                  | `route('specializations.destroy', id)`                | `admin/specializations/{id}`                 | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `specializations.js`                  | `route('specializations.edit', id)`                   | `admin/specializations/{id}/edit`            | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `prescriptions.js` line 4             | `route('prescriptions.destroy', id)`                  | `admin/prescriptions/{prescription}`         | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `doctors/create-edit.js` line 55, 269 | `route('doctors.index')`                              | `admin/doctors`                              | `clinic_admin` | ✅ Fixed 2026-04-03 via `panelRoute()`                                             |
| `settings.js` line 76                 | `route('states-list')`                                | `admin/states-list`                          | `clinic_admin` | ✅ Fixed 2026-04-04 via `panelRoute()`                                             |
| `settings.js` line 111                | `route('cities-list')`                                | `admin/cities-list`                          | `clinic_admin` | ✅ Fixed 2026-04-04 via `panelRoute()`                                             |
| `brands.js` line 5                    | `route('brands.destroy', id)`                         | ❌ no route registered                       | N/A            | ✅ Fixed 2026-04-04 — brands routes added to all panels; JS uses `data-delete-url` |

### 5.2 Staff-Specific Named Routes (Correct Counterparts)

These **do exist** in the route registry but are not being called from JS:

| Correct Route Name                | URI                                              | Permission           |
| --------------------------------- | ------------------------------------------------ | -------------------- |
| `staff.doctor.status`             | `staff/doctor-status`                            | `manage_doctors`     |
| `staff.add.qualification`         | `staff/add-qualification`                        | `manage_doctors`     |
| `staff.resend.email.verification` | `staff/email/verification-notification/{userId}` | `manage_patients`    |
| `staff.specializations.store`     | `staff/specializations`                          | `manage_specialties` |
| `staff.specializations.update`    | `staff/specializations/{id}`                     | `manage_specialties` |
| `staff.specializations.destroy`   | `staff/specializations/{id}`                     | `manage_specialties` |
| `staff.specializations.edit`      | `staff/specializations/{id}/edit`                | `manage_specialties` |
| `staff.get-medicine`              | `staff/get-medicine/{medicine}`                  | `manage_medicines`   |
| `staff.get-medicine-category`     | `staff/get-medicine-category/{category}`         | `manage_medicines`   |
| `staff.medicine-history.index`    | `staff/medicine-history`                         | `manage_medicines`   |
| `staff.medicine-history.update`   | `staff/medicine-history/{id}`                    | `manage_medicines`   |
| `staff.medicine-history.destroy`  | `staff/medicine-history/{id}`                    | `manage_medicines`   |
| `staff.store.patient`             | `staff/medicine-history/store-patient`           | `manage_medicines`   |
| `staff.medicines.show.modal`      | `staff/medicines-show-modal/{medicine}`          | `manage_medicines`   |
| `staff.check.use.medicine`        | `staff/medicines-uses-check/{medicine}`          | `manage_medicines`   |
| `staff.active.deactive`           | `staff/categories/{id}/active-deactive`          | `manage_medicines`   |
| `staff.patients.destroy`          | `staff/patients/{patient}`                       | `manage_patients`    |
| `staff.prescriptions.destroy`     | `staff/prescriptions/{prescription}`             | _(none)_             |
| `staff.states-list`               | `staff/states-list`                              | _(none)_             |
| `staff.cities-list`               | `staff/cities-list`                              | _(none)_             |
| `staff.brands.*`                  | `staff/brands/{brand}`                           | `manage_medicines`   |
| `doctors.brands.*`                | `doctors/brands/{brand}`                         | `manage_medicines`   |

### 5.3 Missing Routes (No Equivalent Exists)

| Missing Route                                       | Needed By                                       | Status                                                                |
| --------------------------------------------------- | ----------------------------------------------- | --------------------------------------------------------------------- |
| ~~`staff.emailVerified` / `doctors.emailVerified`~~ | `staff.js`, `doctors.js`, `patients.js`         | ✅ Added 2026-04-03                                                   |
| `staff.staffs.*` CRUD                               | `staff.js` — delete button shown in staff panel | ✅ Admin-only by design — Blade guard added                           |
| ~~`brands.*` (all panels)~~                         | `brands.js`, `brands/action.blade.php`          | ✅ Added 2026-04-04 — all 21 routes registered                        |
| ~~`staff.states-list` / `staff.cities-list`~~       | `settings.js`                                   | ✅ Were already registered; `settings.js` fixed to use `panelRoute()` |

---

## 6. Staff Permission vs Route Gaps

| Permission Assigned to Staff | Staff Routes with This Permission            | Gap                                         |
| ---------------------------- | -------------------------------------------- | ------------------------------------------- |
| ~~`manage_staff`~~           | ❌ Removed 2026-04-03 — admin-only           | **Fixed**                                   |
| `manage_roles`               | `staff.roles.index`, `staff.roles.show` only | Read-only — cannot create/edit/delete roles |
| `manage_medicines`           | 45 routes ✅                                 | Full CRUD available                         |
| `manage_doctors`             | 9 routes ✅                                  | Full CRUD + status toggle                   |
| `manage_patients`            | 19 routes ✅                                 | Full CRUD                                   |
| `manage_specialties`         | 7 routes ✅                                  | Full CRUD                                   |
| `manage_request_documents`   | 8 routes ✅                                  | Full CRUD                                   |
| `manage_staff_dashboard`     | `staff/dashboard` (no perm check)            | Dashboard exists, permission unused         |

---

## 7. Doctor Permission vs Route Gaps

| Permission Assigned to Doctor        | Doctor Routes with This Permission | Notes                                                         |
| ------------------------------------ | ---------------------------------- | ------------------------------------------------------------- |
| `manage_medicines`                   | 45 routes ✅                       | Full CRUD                                                     |
| `manage_patients`                    | 10 routes ✅                       | Full CRUD                                                     |
| `manage_specialties`                 | 7 routes ✅                        | Full CRUD                                                     |
| `manage_request_documents`           | 8 routes ✅                        | Full CRUD                                                     |
| _(missing)_ `manage_staff_dashboard` | N/A                                | Doctor has no dashboard permission — uses no-permission route |

---

## 8. Summary of Issues

| #   | Severity  | Area            | Issue                                                                                                     | Status                                                                     |
| --- | --------- | --------------- | --------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------- |
| 1   | ✅ Fixed  | JS/Staff        | `doctors.js` calls `route('doctor.status')` → resolves to admin URI                                       | Fixed 2026-04-03 via `panelRoute()`                                        |
| 2   | ✅ Fixed  | JS/Staff        | `doctors.js` calls `route('add.qualification')` → resolves to admin URI                                   | Fixed 2026-04-03 via `panelRoute()`                                        |
| 3   | ✅ Fixed  | JS/Staff        | `doctors.js` calls `route('resend.email.verification')` → resolves to admin URI                           | Fixed 2026-04-03 via `panelRoute()`                                        |
| 4   | ✅ Fixed  | JS/All          | `staff.js`, `doctors.js`, `patients.js` call `route('emailVerified')` → admin-only, no staff/doctor route | Fixed 2026-04-03 — routes added + `panelRoute()`                           |
| 5   | ✅ Fixed  | JS/Staff        | `staff.js` calls `route('staffs.destroy')` → admin-only. Staff panel shows delete button                  | Fixed 2026-04-03 — Blade guard + data-delete-url                           |
| 6   | ✅ Fixed  | JS/Staff+Doctor | `medicine_bill.js` uses 6 bare route names → all resolve to admin URIs                                    | Fixed 2026-04-03 via `panelRoute()`                                        |
| 7   | ✅ Fixed  | JS/Staff+Doctor | `medicines.js`, `category.js` use bare route names → resolve to admin URIs                                | Fixed 2026-04-03 via `panelRoute()`                                        |
| 8   | ✅ Fixed  | JS/Staff+Doctor | `doctors/create-edit.js` redirects to `route('doctors.index')` → admin URI after save/cancel              | Fixed 2026-04-03 via `panelRoute()`                                        |
| 9   | ✅ Fixed  | JS/Staff+Doctor | `specializations.js` uses bare `specializations.*` routes → admin-only for staff/doctor                   | Fixed 2026-04-03 via `panelRoute()`                                        |
| 10  | ✅ Fixed  | JS/Staff+Doctor | `prescriptions.js`, `prescriptions/create-edit.js` use bare routes → admin URI                            | Fixed 2026-04-03 via `panelRoute()`                                        |
| 11  | ✅ Fixed  | Permissions     | Staff had `manage_staff` permission but no staff-panel routes for managing staff                          | Fixed 2026-04-03 — removed from seeder + DB                                |
| 12  | ✅ Fixed  | JS/Staff        | `patients.js` line 118 fallback `route('patients.destroy')` → admin URI                                   | Fixed 2026-04-04 via `panelRoute()`                                        |
| 13  | ✅ Fixed  | JS/Staff        | `settings.js` `route('states-list')` / `route('cities-list')` → admin URI for staff                       | Fixed 2026-04-04 via `panelRoute()`                                        |
| 14  | ✅ Fixed  | Routes/All      | `brands.*` routes missing — `brands.js`, `brands/action.blade.php` broke at runtime                       | Fixed 2026-04-04 — routes added to all 3 panels; JS uses `data-delete-url` |
| 15  | 🟡 Medium | Permissions     | Staff has `manage_roles` permission but panel routes are read-only                                        | Open                                                                       |
| 16  | 🟠 Low    | Routes          | `manage_staff_dashboard` permission on staff but `staff/dashboard` has no permission check                | Open                                                                       |
