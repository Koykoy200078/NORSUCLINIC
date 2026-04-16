# NORSU Clinic Management System — Recode Project Guide & Feature Tracker

> **Status:** Deep-scan completed April 15, 2026  
> **Source project:** `c:\Projects\NORSUCLINIC`  
> **Target stack:** Laravel 13 · Livewire Starter Kit (single-file, built-in auth) · Spatie Laravel Permission · Tailwind CSS · Alpine.js · Pest  
> **Deployment:** Local server only — no cloud, no internet. Accessible only within the same local area network (LAN). All configuration must target local IP / hostname, not a public domain.  
> **Rules:** No hardcoded routes/URLs — use named routes + helper functions everywhere. Admin controls all role-permission assignments. All route groups are dynamic and role-based. No payment system — remove all payment fields, payment status, payment type, and billing/pricing logic throughout the recode.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Tech Stack — Recode Target](#2-tech-stack--recode-target)
3. [User Roles & Permissions Matrix](#3-user-roles--permissions-matrix)
4. [Complete Permissions List](#4-complete-permissions-list)
5. [Route Architecture (Dynamic RBAC)](#5-route-architecture-dynamic-rbac)
6. [Features Master List](#6-features-master-list)
    - 6.1 [Authentication & Profile](#61-authentication--profile)
    - 6.2 [Dashboards (per Role)](#62-dashboards-per-role)
    - 6.3 [User Management](#63-user-management)
    - 6.4 [Doctor Management](#64-doctor-management)
    - 6.5 [Staff Management](#65-staff-management)
    - 6.6 [Patient Management](#66-patient-management)
    - 6.7 [Patient Queue Management](#67-patient-queue-management)
    - 6.8 [Consultation / Request Documents](#68-consultation--request-documents)
    - 6.9 [Prescription Management](#69-prescription-management)
    - 6.10–6.13 [Medicine Screen — Single Tabbed Page](#610-medicine-inventory-stock-in--stock-out) _(Tabs: Medicines · Categories · Generics · History)_
        - 6.10 [Tab: Medicine Inventory (Stock In / Stock Out)](#610-medicine-inventory-stock-in--stock-out)
        - 6.11 [Tab: Medicine Categories](#611-medicine-categories)
        - 6.12 [Tab: Medicine Generics](#612-medicine-generics)
        - 6.13 [Tab: Medicine History (Dispensing Records)](#613-medicine-history-dispensing-records)
    - 6.14 [Activity Logs](#614-activity-logs)
    - 6.15 [Specializations](#615-specializations)
    - 6.16 [Roles & Permission Manager](#616-roles--permission-manager)
    - 6.17 [Settings](#617-settings)
    - 6.18 [CMS / Front Page Management](#618-cms--front-page-management)
    - 6.19 [Location Management](#619-location-management)
    - 6.20 [Academic Data Management](#620-academic-data-management)
    - 6.21 [Notifications](#621-notifications)
    - 6.22 [Dark Mode](#622-dark-mode)
    - 6.23 [Diagnoses Reference Data](#623-diagnoses-reference-data)
    - 6.24 [Vaccination Reference Data](#624-vaccination-reference-data)
    - 6.25 [Guest / Walk-in Module](#625-guest--walk-in-module)
    - 6.26 [Offices & Departments](#626-offices--departments)
7. [Database Schema Summary](#7-database-schema-summary)
8. [Models & Relationships](#8-models--relationships)
9. [Livewire Components (to recode)](#9-livewire-components-to-recode)
10. [Helper Functions (must keep/recode)](#10-helper-functions-must-keeprecode)
11. [Middleware Stack](#11-middleware-stack)
12. [Seeders & Initial Data](#12-seeders--initial-data)
13. [Recode Architecture Guidelines](#13-recode-architecture-guidelines)
    - 13.7 [Local Network Deployment](#137-local-network-deployment)
14. [Recode Progress Tracker](#14-recode-progress-tracker)

---

## 1. Project Overview

**NORSU Clinic Management System** is a university clinic management application for Negros Oriental State University (NORSU). It manages:

- University student, faculty patients and guests
- Doctor and clinic staff workflows
- Patient queuing and consultation
- Medicine stock-in / stock-out inventory
- Prescription creation and dispensing
- Request document generation (consultation forms, medical certificates)
- Activity/audit logging
- Role-based access control via Spatie

---

## 2. Tech Stack — Recode Target

**Project bootstrapped with:**

```
laravel new NORSUCLINIC
  Starter kit  : Livewire
  Auth provider: Laravel's built-in authentication
  Components   : Single-file Livewire components
  Teams        : No
  Testing      : Pest
  Laravel Boost: Yes
```

| Layer             | Current (source)                  | Target (recode)                                           |
| ----------------- | --------------------------------- | --------------------------------------------------------- |
| Framework         | Laravel (≈9-10)                   | **Laravel 13**                                            |
| Starter kit       | None                              | **laravel/livewire-starter-kit** (pre-installed)          |
| Frontend reactive | Livewire + 3rd-party tables       | **Livewire 3 — single-file components** (pre-installed)   |
| Auth scaffolding  | Custom blade auth                 | **Laravel built-in auth** (pre-installed via starter kit) |
| RBAC              | Spatie Laravel Permission         | **Spatie Laravel Permission (latest)**                    |
| CSS               | Bootstrap 5 + custom              | **Tailwind CSS** (pre-installed via starter kit)          |
| JS                | jQuery + Alpine                   | **Alpine.js** (pre-installed via starter kit)             |
| Tables            | rappasoft/laravel-livewire-tables | **Livewire native** (no 3rd party)                        |
| PDF               | barryvdh/laravel-dompdf           | barryvdh/laravel-dompdf                                   |
| Media             | Spatie Media Library              | Spatie Media Library                                      |
| Excel export      | Maatwebsite/Laravel-Excel         | Maatwebsite/Laravel-Excel                                 |
| Icons             | Font Awesome 5                    | Heroicons (via Blade Icons)                               |
| Testing           | PHPUnit                           | **Pest** (configured on install)                          |
| AI coding assist  | —                                 | **Laravel Boost** (installed)                             |

---

## 3. User Roles & Permissions Matrix

Four system roles managed via Spatie:

| Permission               | `clinic_admin` | `staff` | `doctor` | `patient` | Rationale                                                            |
| ------------------------ | :------------: | :-----: | :------: | :-------: | -------------------------------------------------------------------- |
| manage_admin_dashboard   |       ✅       |   ❌    |    ❌    |    ❌     | Admin-only system overview                                           |
| manage_staff_dashboard   |       ✅       |   ✅    |    ❌    |    ❌     | Staff operational dashboard                                          |
| manage_doctors           |       ✅       |   ✅    |    ❌    |    ❌     | Staff registers/manages doctor accounts; doctors cannot manage peers |
| manage_staff             |       ✅       |   ❌    |    ❌    |    ❌     | Only admin creates/manages staff accounts                            |
| manage_patients          |       ✅       |   ✅    |    ✅    |    ❌     | Core clinical work for staff and doctors                             |
| manage_medicines         |       ✅       |   ✅    |    ✅    |    ❌     | Staff handles stock; doctors prescribe/dispense during consultation  |
| manage_specialties       |       ✅       |   ✅    |    ✅    |    ❌     | All clinical roles can add/view specializations                      |
| manage_request_documents |       ✅       |   ✅    |    ✅    |    ✅     | Staff/doctors create forms; patients view their own only             |
| manage_roles             |       ✅       |   ❌    |    ❌    |    ❌     | Admin-only — controls who can access what                            |
| manage_settings          |       ✅       |   ❌    |    ❌    |    ❌     | Admin-only — clinic configuration                                    |
| manage_countries         |       ✅       |   ❌    |    ❌    |    ❌     | Admin-only — reference data                                          |
| manage_provinces         |       ✅       |   ❌    |    ❌    |    ❌     | Admin-only — reference data                                          |
| manage_cities            |       ✅       |   ❌    |    ❌    |    ❌     | Admin-only — reference data                                          |
| manage_front_cms         |       ✅       |   ✅    |    ❌    |    ❌     | Staff updates clinic announcements/front page                        |

> **Rule:** `clinic_admin` always gets ALL permissions. Admin can assign/revoke per-role permissions through the Roles & Permission Manager UI.

### Default permissions summary per role

**`clinic_admin`** — All 14 permissions (full system control)

**`staff`** (nurse / receptionist):

- `manage_staff_dashboard`, `manage_doctors`, `manage_patients`, `manage_medicines`, `manage_specialties`, `manage_request_documents`, `manage_front_cms`

**`doctor`**:

- `manage_patients`, `manage_medicines`, `manage_specialties`, `manage_request_documents`

**`patient`**:

- `manage_request_documents` (view/download their own consultation forms and medical certificates only)

---

## 4. Complete Permissions List

```php
// All permissions in the system (from DefaultPermissionSeeder + DefaultMedicinePermissionSeeder)
'manage_doctors'
'manage_patients'
'manage_staff'
'manage_settings'
'manage_specialties'
'manage_countries'
'manage_provinces'
'manage_cities'
'manage_roles'
'manage_admin_dashboard'
'manage_staff_dashboard'
'manage_front_cms'
'manage_request_documents'
'manage_medicines'
```

---

## 5. Route Architecture (Dynamic RBAC)

### Recode Rule: All routes must be dynamic — no hardcoded role checks in views

**Route prefixes per role:**

| Role         | Prefix      | Example               |
| ------------ | ----------- | --------------------- |
| clinic_admin | `/admin`    | `/admin/dashboard`    |
| staff        | `/staff`    | `/staff/dashboard`    |
| doctor       | `/doctors`  | `/doctors/dashboard`  |
| patient      | `/patients` | `/patients/dashboard` |

**Pattern for all route groups (recode):**

```php
// Use role middleware + permission middleware per group
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'role:clinic_admin'])
    ->group(function () {
        Route::middleware('permission:manage_patients')
            ->group(function () {
                Route::resource('patients', PatientController::class);
            });
    });
```

**Helper for dashboard redirect:**

```php
// getDashboardURL() helper — returns correct dashboard by role
// Never hardcode '/admin/dashboard' in controllers/views
// Always use: redirect(getDashboardURL())
```

**Dynamic route helper usage in views:**

```blade
{{-- Never hardcode role prefix in blade files --}}
{{-- Use helper to get the correct prefixed route --}}
{{ route(getRoleRoutePrefix() . '.patients.index') }}
{{-- OR use the role-aware route helper --}}
{{ roleRoute('patients.index') }}
```

---

## 6. Features Master List

---

### 6.1 Authentication & Profile

**Source files:** `routes/auth.php`, `app/Http/Controllers/Auth/`, `resources/views/auth/`

| Feature                    | Description                                                   |
| -------------------------- | ------------------------------------------------------------- |
| Login                      | Email + password login with role-based redirect               |
| Password change            | Change own password                                           |
| Profile edit               | Update name, contact, address, avatar                         |
| Email notification toggle  | Enable/disable email notifications                            |
| Default password detection | Warns user to change `123456` default password on first login |
| Resend email verification  | Admin/Staff can resend email verification to users            |
| Email verified toggle      | Admin/Staff can manually mark email as verified               |
| Dark mode toggle           | Per-user dark mode persisted in DB                            |

---

### 6.2 Dashboards (per Role)

**Source:** `app/Http/Controllers/DashboardController.php`, `resources/views/dashboard/`, `resources/views/doctor_dashboard/`, `resources/views/patient_dashboard/`, `resources/views/staff_dashboard/`

#### Admin Dashboard (`/admin/dashboard`)

- Permission: `manage_admin_dashboard`
- Statistics widgets: total patients, doctors, staff, medicines low-stock
- Recent patient list (AJAX filterable)
- Livewire sidebar table: recent consultations / queue

#### Doctor Dashboard (`/doctors/dashboard`)

- Today's patient queue (linked directly to queue module)
- Recent prescriptions
- Upcoming appointments/consultations
- Livewire sidebar: recent documents

#### Staff Dashboard (`/staff/dashboard`)

- Permission: `manage_staff_dashboard`
- Queue overview
- Today's count: patients seen, medicines dispensed
- Livewire sidebar table

#### Patient Dashboard (`/patients/dashboard`)

- View own prescriptions
- View own consultation documents
- Change default password prompt

---

### 6.3 User Management

**Source:** `app/Http/Controllers/UserController.php`, `app/Models/User.php`

| Field                              | Details                                 |
| ---------------------------------- | --------------------------------------- |
| first_name, middle_name, last_name | Full name                               |
| email                              | Unique login credential                 |
| contact                            | Phone number                            |
| emergency_contact_name / no        | Emergency info                          |
| dob                                | Date of birth                           |
| gender                             | Male/Female                             |
| blood_type                         | O+, A+, B+, AB+, O-, A-, B-, AB-        |
| status                             | Active/Inactive                         |
| campus_id                          | University campus (FK)                  |
| college_id                         | College/Faculty (FK)                    |
| course_id                          | Course/Program (FK)                     |
| year_level_id                      | Year level (FK)                         |
| vaccination_id                     | Vaccination status (FK)                 |
| type                               | Internal type flag                      |
| dark_mode                          | Boolean                                 |
| role (via Spatie)                  | clinic_admin / staff / doctor / patient |
| profile photo                      | Via Spatie Media Library                |
| address (morphOne)                 | Linked Address model                    |

**Features:**

- CRUD for all user types through respective role controllers
- Reset password (admin/staff can reset to default)
- Status toggle (active/inactive)
- Profile image upload

---

### 6.4 Doctor Management

**Source:** `app/Models/Doctor.php`, `app/Http/Controllers/UserController.php` (handles doctor CRUD)

| Field                    | Details                                |
| ------------------------ | -------------------------------------- |
| user_id                  | FK to users                            |
| experience               | Years of experience                    |
| specializations (pivot)  | Many-to-many via doctor_specialization |
| qualifications (hasMany) | Doctor qualifications list             |

**Features:**

- CRUD doctors (via `manage_doctors` permission)
- Assign multiple specializations
- Add/remove qualifications
- Toggle doctor active/inactive status
- Reset doctor password
- Staff can create/edit/delete doctors; Doctors cannot manage other doctors

---

### 6.5 Staff Management

**Source:** `app/Models/Staff.php`, `app/Http/Controllers/StaffController.php`

**Features:**

- CRUD staff (only `clinic_admin` via `manage_staff`)
- Staff role assignment
- Reset staff password
- Status toggle

---

### 6.6 Patient Management

**Source:** `app/Models/Patient.php`, `app/Http/Controllers/PatientController.php`

| Field                                  | Details                    |
| -------------------------------------- | -------------------------- |
| patient_unique_id                      | Auto-generated unique ID   |
| user_id                                | FK to users                |
| blood_type                             | Enum constants             |
| gender                                 | Male/Female                |
| campus / college / course / year_level | Academic affiliation       |
| address (morphOne)                     | Physical address           |
| vaccination status                     | Via vaccination_id on user |

**Features:**

- CRUD patients (Admin, Staff, Doctor with `manage_patients`)
- View patient history (all consultations, prescriptions)
- Reset patient password
- Patient profile with photo
- Email verification management
- Patient unique ID auto-generation
- Patient blood type, gender, academic affiliation tracking

---

### 6.7 Patient Queue Management

**Source:** `app/Models/PatientQueue.php`, `app/Http/Controllers/PatientQueueController.php`, `resources/views/patient_queue/`

| Field                       | Details                                       |
| --------------------------- | --------------------------------------------- |
| patient_id                  | FK to patients                                |
| added_by                    | FK to users (staff who added)                 |
| room_number                 | Room assignment                               |
| is_priority                 | Boolean priority flag                         |
| status                      | waiting / in_progress / completed / cancelled |
| notes                       | Notes for doctor                              |
| latest_consultation_id      | FK to request_documents                       |
| has_consultation_attachment | Boolean                                       |
| called_at                   | Timestamp when called                         |
| completed_at                | Timestamp when completed                      |

**Features:**

- Add patient to queue (Staff, Admin)
- View queue list (all roles)
- AJAX auto-refresh with countdown badge (5s interval, toggleable)
- Manual refresh button
- Call next patient (changes status to `in_progress`)
- Mark consultation complete (changes status to `completed`)
- Priority patient flag
- Doctor-specific queue view (filtered to their patients)
- View consultation form attached to queue entry
- Delete queue entry
- Edit queue entry (room number, notes, priority)

**Auto-refresh logic (client-side):**

- `localStorage` persists auto-refresh ON/OFF state across page loads
- Countdown badge with color stages: green (5s) → teal (4s) → yellow (3s) → orange (2s) → red (1s)
- Spin animation on manual refresh icon
- Prevents stacked concurrent fetch requests

---

### 6.8 Consultation / Request Documents

**Source:** `app/Models/RequestDocuments.php`, `app/Http/Controllers/RequestDocumentsController.php`, `resources/views/requests/`

**Document Types:**

1. `consultation_form` — Full consultation record
2. `medical_certificate` — Medical certificate for clearance

| Field                                          | Details                                 |
| ---------------------------------------------- | --------------------------------------- |
| document_creator_id                            | Who created the document                |
| user_id                                        | Target patient/user                     |
| name                                           | Patient name snapshot                   |
| age, gender                                    | At time of consultation                 |
| date_of_birth                                  | Patient DOB                             |
| address                                        | Address snapshot                        |
| religion                                       | Religion                                |
| patient_contact                                | Contact number                          |
| campus, college, course, year_level            | Academic info                           |
| Department/Office                              | For non-student staff/employees         |
| informant                                      | Who gave information                    |
| emergency_contact                              | Emergency contact                       |
| requested_at                                   | Date requested                          |
| complaints                                     | Chief complaints                        |
| covid_vaccination                              | Vaccination status                      |
| comorbidities                                  | Existing conditions                     |
| allergies                                      | Known allergies                         |
| admissions_surgeries                           | Previous admissions/surgeries           |
| maintenance                                    | Maintenance medications                 |
| pregnancy_status, lmp_aog                      | Pregnancy info (female)                 |
| vital_signs_bp/pr/temp/rr/o2_sat/height/weight | All vitals                              |
| pertinent_exam                                 | Physical exam findings                  |
| assessment                                     | Doctor's assessment                     |
| plan                                           | Treatment plan                          |
| consult_mode                                   | Walk-in / teleconsult                   |
| nursing_intervention                           | Nursing notes                           |
| nursing_incharged_id                           | Nurse on duty                           |
| examined_on                                    | Date/time examined                      |
| request_of                                     | Requested by                            |
| complaints_diagnosis                           | Diagnosis                               |
| medical_cert_remarks                           | Medical cert notes                      |
| doc_lic_no, doc_prt_no                         | Doctor license/PRT                      |
| document_type                                  | consultation_form / medical_certificate |
| note                                           | Additional notes                        |
| consultation_images (media)                    | Attached images via Spatie Media        |
| consultation_medicines (hasMany)               | Medicines dispensed during consultation |

**Features:**

- Create consultation form with full vitals, history, assessment
- Create medical certificate
- Search users (patients, guests, staff, doctors) for form creation
- Get last consultation pre-fill (auto-populates previous consultation data)
- Export to PDF (barryvdh/dompdf)
- View/edit/delete documents
- Attach photos/images to consultation (Spatie Media Library)
- Link to patient history
- Consultation medicines recording (ConsultationMedicine model)
- Activity log on create

---

### 6.9 Prescription Management

**Source:** `app/Models/Prescription.php`, `app/Models/PrescriptionMedicineModal.php` (rename to `PrescriptionMedicine` in recode), `app/Http/Controllers/PrescriptionController.php`

| Field                                        | Details               |
| -------------------------------------------- | --------------------- |
| patient_id                                   | FK to patients        |
| doctor_id                                    | FK to doctors         |
| food_allergies                               | Food allergy notes    |
| tendency_bleed                               | Bleeding tendency     |
| heart_disease, high_blood_pressure, diabetic | Medical history flags |
| surgery, accident, others                    | Past events           |
| medical_history                              | General history notes |
| current_medication                           | Current meds          |
| female_pregnancy, breast_feeding             | OB info               |
| health_insurance, low_income                 | Socio-economic        |
| reference                                    | Referral source       |
| status                                       | Active/Inactive       |
| plus_rate                                    | Pulse rate            |
| temperature                                  | Body temperature      |
| problem_description                          | Chief complaint       |
| test                                         | Lab test ordered      |
| advice                                       | Doctor advice         |

**Prescription Medicines (pivot model):**

- medicine_id
- dosage
- frequency
- duration
- instructions

**Features:**

- Create prescription for a patient (linked to specific patient)
- Add multiple medicines to a prescription
- Remove medicines from prescription
- Toggle prescription active/inactive status
- View prescription medicines modal
- Export prescription to PDF
- Doctor and Staff can create/edit prescriptions
- Patient can view own prescriptions only

---

### 6.10 Medicine Inventory (Stock In / Stock Out)

> 🗂️ **Single-screen layout:** Sections 6.10 – 6.13 are **all rendered on one page** (`/admin/medicines` or `/staff/medicines`) using a tabbed UI. The four tabs are:
>
> - **Tab 1 — Medicines** (master list + stock levels)
> - **Tab 2 — Categories** (medicine category CRUD)
> - **Tab 3 — Generics** (generic name CRUD)
> - **Tab 4 — History** (dispensing records)
>
> Implemented as a single Livewire 3 parent component (`MedicineScreen`) that renders the active tab sub-component. Tab state persists in the URL query string (`?tab=medicines`, `?tab=categories`, `?tab=generics`, `?tab=history`) so deep-linking and browser back/forward work correctly.

**Source:** `app/Models/Medicine.php`, `app/Models/MedicineAvailability.php`, `app/Models/PurchasedMedicine.php`, `app/Models/UsedMedicine.php`, `app/Models/ConsultationMedicine.php`, `app/Http/Controllers/MedicineController.php`, `app/Http/Controllers/MedicineAvailabilityController.php`

#### Medicine Master (`medicines` table)

| Field                  | Details                       |
| ---------------------- | ----------------------------- |
| category_id            | FK to categories              |
| generic_id             | FK to generics                |
| name                   | Medicine name                 |
| salt_composition       | Active ingredients            |
| description            | General description           |
| side_effects           | Known side effects            |
| currency_symbol        | Currency display              |
| quantity               | Total quantity ever added     |
| available_quantity     | Current available stock       |
| minimum_stock_alert    | Absolute minimum before alert |
| stock_alert_percentage | % below which to alert        |

#### Stock IN — Medicine Availability (`medicine_availabilities` + `purchased_medicines`)

Medicine Availability = one stock-in batch record

> ⚠️ **No-payment rule:** `tax`, `total`, `net_amount`, `payment_type`, `discount`, `payment_note` fields from the source model are **excluded** in the recode. Only `availability_no` and date/quantity tracking are kept.

| Field           | Details                     |
| --------------- | --------------------------- |
| availability_no | Auto-generated batch number |

Purchased Medicines (line items of availability):

| Field                      | Details                                    |
| -------------------------- | ------------------------------------------ |
| medicine_availabilities_id | FK to availability batch                   |
| medicine_id                | FK to medicine                             |
| dosage                     | Dosage form/strength                       |
| manufacturing_date         | Manufacture date                           |
| expiry_date                | Expiry date (varchar for flexible formats) |
| quantity                   | Qty received                               |
| amount                     | Cost amount                                |
| tax                        | Tax %                                      |

**Stock IN Features:**

- Create availability batch (stock-in event)
- Add multiple medicines per batch
- Track dosage, manufacturing date, expiry date per item
- Auto-increment `available_quantity` on medicine upon save
- Export medicine availability to Excel (Maatwebsite)
- View stock-in history (medicine-availability list)

#### Stock OUT — Used Medicine (`used_medicines`)

| Field       | Details                                 |
| ----------- | --------------------------------------- |
| medicine_id | FK to medicine                          |
| patient_id  | FK to patient (if dispensed to patient) |
| quantity    | Amount used/dispensed                   |
| used_for    | Purpose/notes                           |

**Stock OUT Channels:**

1. **Consultation Medicine** — dispensed during a consultation (`consultation_medicines` table)
2. **Dispensing Record** — dispensed and recorded as a dispensing record (`dispense_records` + `dispense_record_items`)
3. **Used Medicine** — direct usage log

**Stock OUT Features:**

- View used medicine list (read from `stock_out_view` — DB view)
- Auto-decrement `available_quantity` on medicine when dispensed
- Low stock alerts (minimum_stock_alert + stock_alert_percentage)
- Track which patient received which medicine

#### Dispensing Record (`dispense_records` + `dispense_record_items`)

> ⚠️ **No-payment rule:** `discount`, `net_amount`, `total`, `tax_amount`, `payment_status`, `payment_type` fields are **excluded** in the recode. This module tracks what medicines were dispensed to a patient and when — not billing or payment.

| Field           | Details                     |
| --------------- | --------------------------- |
| dispense_number | Auto-generated dispensing # |
| patient_id      | FK to patients              |
| doctor_id       | FK to doctors               |
| note            | Additional note             |
| dispense_date   | Date of dispensing          |

**Dispensing Record Features:**

- Create dispensing record for patient (no billing/pricing)
- Add multiple medicines to a dispensing record
- Export dispensing record to PDF
- Search patients for record creation
- Get medicine by category (AJAX)

---

### 6.11 Medicine Categories

**Source:** `app/Models/Category.php`, `app/Http/Controllers/CategoryController.php`

**Features:**

- CRUD categories
- Active/Inactive toggle per category
- Category used as classification for medicines

---

### 6.12 Medicine Generics

**Source:** `app/Models/Generic.php`, `app/Http/Controllers/GenericController.php`

> Note: Previously named "Brands" — renamed to Generics in Dec 2025 migration.

**Features:**

- CRUD generic names (e.g., Amoxicillin, Paracetamol)
- Used as FK on Medicine model
- Generic detail view (lists medicines under a generic)

---

### 6.13 Medicine History (Dispensing Records)

**Source:** `app/Http/Controllers/DispenseRecordController.php`, `resources/views/medicine-history/`

**Features:**

- Full CRUD for medicine dispensing records
- PDF export of dispensing record
- Store patient info on dispensing record
- View list of all dispensing events
- Filter by date, patient, doctor

---

### 6.14 Activity Logs

**Source:** `app/Models/ActivityLog.php`, `app/Http/Controllers/ActivityLogController.php`, `app/Traits/LogsActivity.php`

| Field                                     | Details                         |
| ----------------------------------------- | ------------------------------- |
| user_id, user_type, user_name             | Who acted                       |
| action                                    | create / update / delete / view |
| subject_type, subject_id                  | What was acted upon             |
| description                               | Human-readable description      |
| date                                      | Date of action                  |
| patient_name, patient_age, patient_gender | Patient context snapshot        |
| college, address, contact_number          | Extra context                   |
| complaints, diagnosis                     | Medical context                 |
| informant, consult_mode, course_section   | Consultation context            |
| properties                                | JSON blob for extra data        |
| ip_address, user_agent                    | Request metadata                |

**Features:**

- Automatic logging via `LogsActivity` trait on controllers
- View all activity logs (Admin, Doctor)
- Export logs to CSV
- View single log detail
- Filter by date, user, action type

---

### 6.15 Specializations

**Source:** `app/Models/Specialization.php`, `app/Http/Controllers/SpecializationController.php`

**Features:**

- CRUD specializations (e.g., "General Medicine", "Obstetrics")
- Assign specializations to doctors (many-to-many)
- Detail view shows all doctors under a specialization

---

### 6.16 Roles & Permission Manager

**Source:** `app/Models/Role.php`, `app/Http/Controllers/RoleController.php`, `app/Livewire/RoleTable.php`

**Features:**

- View all roles with assigned permissions
- Edit role — assign/revoke individual permissions
- Admin-only feature (`manage_roles` permission)
- Cannot delete default system roles
- Displays all permissions in a checklist UI

---

### 6.17 Settings

**Source:** `app/Models/Setting.php`, `app/Http/Controllers/SettingController.php`, `app/Services/SettingsService.php`

| Key                | Description                      |
| ------------------ | -------------------------------- |
| clinic_name        | App/clinic name                  |
| logo               | Logo image (Spatie media)        |
| favicon            | Favicon image                    |
| tagline            | Clinic tagline                   |
| about              | About text                       |
| email              | Contact email                    |
| phone              | Contact phone                    |
| address            | Clinic address                   |
| country_id         | Default country                  |
| copyrights         | Footer copyright text            |
| about_us_image_1/2 | About page images                |
| email_verified     | Email verification required flag |

**Features:**

- Update all settings via single form
- Logo/favicon upload via Spatie Media Library
- Settings retrieved system-wide via `getSettingValue()` helper
- Cached for performance (`SettingsService`)
- Default country + timezone configuration

---

### 6.18 CMS / Front Page Management

**Source:** `app/Http/Controllers/Front/CMSController.php`, `app/Http/Controllers/Front/SliderController.php`, `resources/views/fronts/`

**Features:**

- Update front page CMS content (about, services, contact info)
- Banner/Slider management (edit only — no add/delete in UI)
- Front public pages: Home, About Us, Services, Doctors, Contact
- Terms & Conditions page
- Privacy Policy page

---

### 6.19 Location Management

**Source:** Countries, Provinces, Cities, Barangays controllers + models

| Level             | Model                    | Admin Permission |
| ----------------- | ------------------------ | ---------------- |
| Country           | `Country`                | manage_countries |
| Province          | `Province` (was `State`) | manage_provinces |
| City/Municipality | `City`                   | manage_cities    |
| Barangay          | `Barangay`               | manage_cities    |

**Features:**

- Full CRUD for all location levels
- Cascading dropdowns (Country → Province → City → Barangay) via AJAX
- City has `type` field (city / municipality)
- Address model morphed to any entity (User, Doctor, Patient)
- Dynamic get-provinces, get-cities, get-barangays routes

---

### 6.20 Academic Data Management

**Source:** `app/Models/Campus.php`, `College.php`, `Course.php`, `YearLevel.php`

> NORSU-specific academic structure linked to patient/user profiles.

| Model     | Table       | Fields                          |
| --------- | ----------- | ------------------------------- |
| Campus    | campuses    | campus_name                     |
| College   | colleges    | college_name, campus_id         |
| Course    | courses     | course_name, college_id         |
| YearLevel | year_levels | year_level (flexible structure) |

**Seeders available:** CampusSeeder, CollegeSeeder, CourseSeeder, YearLevelSeeder

**Features:**

- Cascading dropdowns in patient/user forms (Campus → College → Course → Year Level)
- Seeded with NORSU campus/college data
- Used in consultation forms and request documents for academic context

---

### 6.21 Notifications

**Source:** `app/Models/Notification.php`, `app/Http/Controllers/NotificationController.php`

**Features:**

- Laravel database notifications
- Mark single notification as read
- Mark all notifications as read
- Notification bell in navbar with unread count
- Notifications on queue events, low-stock alerts

---

### 6.22 Dark Mode

**Source:** `app/Http/Controllers/UserController.php`, User model `dark_mode` field

**Features:**

- Toggle dark mode per user
- Persisted in `users.dark_mode` column
- Applied via route `GET /update-dark-mode`
- Tailwind `dark:` utility classes applied globally (via `class="dark"` on `<html>`)

---

### 6.23 Diagnoses Reference Data

**Source:** `app/Models/Diagnose.php`, `database/seeders/DiagnoseSeeder.php`

**Features:**

- Seeded list of common diagnoses
- Used in consultation form autocomplete/dropdown
- Admin-seeded, not user-managed in UI (reference data)

---

### 6.24 Vaccination Reference Data

**Source:** `app/Models/Vaccination.php`, `database/seeders/VaccinationSeeder.php`

**Features:**

- Vaccination status options (e.g., Fully Vaccinated, Not Vaccinated)
- Linked to user profile via `users.vaccination_id`
- Used in consultation form

---

### 6.25 Guest / Walk-in Module

**Source:** `app/Models/Guest.php`

**Features:**

- Record walk-in guests who are not registered patients
- Fields: first_name, last_name, contact, email, purpose, address
- Guest can be selected as subject in consultation/request documents

---

### 6.26 Offices & Departments

**Source:** `app/Models/Office.php`, `app/Models/Department.php`

**Features:**

- Reference data for non-student staff/employees
- Used in consultation forms for office/department context
- Seeded with NORSU office/department data
- Office links to users (hasMany)

---

## 7. Database Schema Summary

```
users                     — core auth + profile
patients                  — patient profile linked to user
doctors                   — doctor profile linked to user
staff                     — staff profile linked to user  (✔ renamed: `staffs` → `staff`, correct English uncountable)
guests                    — walk-in non-registered visitors

addresses                 — polymorphic address for any entity
qualifications            — doctor qualifications
specializations           — medical specializations
doctor_specialization     — pivot: doctors <-> specializations

request_documents         — consultation forms + medical certificates
consultation_medicines    — medicines dispensed during consultation
prescriptions             — prescription records
prescription_medicines    — medicines in a prescription

medicines                 — medicine master list
categories                — medicine categories
generics                  — medicine generic names (was brands)
medicine_availabilities   — stock-in batch header
purchased_medicines       — stock-in batch line items
used_medicines            — stock-out log
stock_out_view            — DB view: aggregated stock-out  (✔ renamed: `used_medicines_view` → `stock_out_view`)
dispense_records          — dispensing record header  (✔ renamed: `medicine_bills` → `dispense_records`)
dispense_record_items     — dispensing record line items  (✔ renamed: `sale_medicines` → `dispense_record_items`)

patient_queues            — patient queue management

activity_logs             — audit trail

settings                  — key-value clinic settings
roles                     — Spatie roles
permissions               — Spatie permissions
model_has_roles           — Spatie pivot
model_has_permissions     — Spatie pivot
role_has_permissions      — Spatie pivot

countries                 — country reference
provinces                 — province reference  (table renamed from `states` in recode)
cities                    — city/municipality reference
barangays                 — barangay reference
campuses                  — NORSU campus list
colleges                  — NORSU college list
courses                   — NORSU course list
year_levels               — year level reference
vaccinations              — vaccination status reference
diagnoses                 — diagnoses reference
offices                   — NORSU office list
departments               — NORSU department list

sliders                   — front page banners
notifications             — Laravel notifications
media                     — Spatie media library
sessions                  — DB sessions
```

### Normalization Audit Notes

> All tables follow 3NF. Key decisions documented below.

| Table                                             | Note                                                                                                                                                                                                              |
| ------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `staff`                                           | Plural fixed: `staffs` → `staff` (English uncountable noun)                                                                                                                                                       |
| `dispense_records`                                | Renamed from `medicine_bills` — no payment semantics in recode                                                                                                                                                    |
| `dispense_record_items`                           | Renamed from `sale_medicines` — no sale/payment semantics in recode                                                                                                                                               |
| `stock_out_view`                                  | Renamed from `used_medicines_view` — DB VIEW, not a table; unions `consultation_medicines` + `dispense_record_items`                                                                                              |
| `request_documents`                               | Snapshot fields (`name`, `age`, `gender`, `address`, etc.) are **intentional historical denormalization** — medical records must reflect patient data at time of consultation, not current values                 |
| `request_documents`                               | `campus_id`, `college_id`, `course_id`, `year_level_id` must be stored as **FK integer IDs** (not text strings) in recode — join at render/PDF time for display                                                   |
| `doctor_specialization`                           | Pivot follows Laravel alphabetical convention (`doctor` before `specialization`) ✅                                                                                                                               |
| `medicine_availabilities` + `purchased_medicines` | Header-detail pattern — properly normalized ✅                                                                                                                                                                    |
| `dispense_records` + `dispense_record_items`      | Header-detail pattern — properly normalized ✅                                                                                                                                                                    |
| `prescriptions` + `prescription_medicines`        | Header-detail pattern — properly normalized ✅                                                                                                                                                                    |
| `addresses`                                       | Polymorphic morphTo (`addressable_type` / `addressable_id`) — acceptable pattern for shared address across User/Doctor/Patient ✅. Fields renamed: `address1` → `present_address`, `address2` → `current_address` |
| `users.time_zone`                                 | **Removed** — system is LAN-only, all users are in Philippine time (Asia/Manila). Timezone hardcoded once in `config/app.php` (`timezone = 'Asia/Manila'`), not stored per user                                   |
| `doctors.twitter_url/linkedin_url/instagram_url`  | **Removed** — social media links are not relevant to a university clinic system                                                                                                                                   |
| `users.language` + `SetLanguage` middleware       | **Removed** — system is English-only. No multi-language support. Default locale set once in `config/app.php` (`locale = 'en'`)                                                                                    |

---

## 8. Models & Relationships

```
User
  hasOne  Doctor
  hasOne  Patient
  hasOne  Staff
  hasOne  Address (morphOne)
  hasMany Qualification
  belongsTo Campus, College, Course, YearLevel, Vaccination
  hasRoles (Spatie)
  hasPermissions (Spatie)

Patient
  belongsTo User
  hasMany   Prescription
  hasMany   RequestDocument (via user_id)
  hasMany   DispenseRecord
  hasMany   PatientQueue
  morphOne  Address

Doctor
  belongsTo     User
  belongsToMany Specialization (pivot: doctor_specialization)
  morphOne      Address

PatientQueue
  belongsTo Patient
  belongsTo User (addedBy)
  belongsTo RequestDocument (latestConsultation)

RequestDocument  (✔ renamed: singular, Laravel model convention)
  belongsTo User (document_creator)
  belongsTo User (patient/subject)
  hasMany   ConsultationMedicine
  media (Spatie — consultation_images)

ConsultationMedicine
  belongsTo RequestDocuments
  belongsTo Medicine

Prescription
  belongsTo Patient
  belongsTo Doctor
  hasMany   PrescriptionMedicine  (was PrescriptionMedicineModal — renamed in recode)

Medicine
  belongsTo Category
  belongsTo Generic
  hasMany   PurchasedMedicine
  hasMany   UsedMedicine
  hasMany   ConsultationMedicine

MedicineAvailability
  hasMany PurchasedMedicine

DispenseRecord  (✔ renamed from MedicineBill — table: dispense_records)
  belongsTo Patient
  belongsTo Doctor
  hasMany   DispenseRecordItem  (✔ renamed from SaleMedicine — table: dispense_record_items)

Address (polymorphic)
  morphTo: User, Doctor, Patient
```

---

## 9. Livewire Components (to recode)

Replace all rappasoft tables with native Livewire 3 components:

| Recode name                    | Source name (old)                                           | Purpose                                         |
| ------------------------------ | ----------------------------------------------------------- | ----------------------------------------------- |
| `AdminDashboardTable`          | `AdminDashBoardTable`                                       | Admin dashboard stats table                     |
| `AdminRecentTable`             | `AdminDashboardSidebarTable`                                | Admin recent records widget                     |
| `DoctorDashboardTable`         | same                                                        | Doctor dashboard table                          |
| `DoctorRecentTable`            | `DoctorDashboardSidebarTable`                               | Doctor recent records widget                    |
| `StaffDashboardTable`          | `StaffDashBoardTable`                                       | Staff dashboard table                           |
| `StaffRecentTable`             | `StaffDashboardSidebarTable`                                | Staff recent records widget                     |
| `PatientDashboardTable`        | same                                                        | Patient dashboard table                         |
| `PatientRecentTable`           | `PatientDashboardSidebarTable`                              | Patient recent records widget                   |
| `PatientTable`                 | same                                                        | Patient list                                    |
| `DoctorTable`                  | same                                                        | Doctor list                                     |
| `StaffTable`                   | same                                                        | Staff list                                      |
| `MedicineScreen`               | _(new)_                                                     | **Parent — single-page tabbed medicine screen** |
| `MedicineTable`                | same                                                        | Tab 1 — Medicine master list                    |
| `StockInTable`                 | `MedicineAvailabilityTable`                                 | Tab 1 — Stock-in batch list                     |
| `StockOutTable`                | `UsedMedicineTable`                                         | Tab 1 — Stock-out / used medicine log           |
| `MedicineCategoryTable`        | same                                                        | Tab 2 — Category list                           |
| `MedicineCategoryDetailsTable` | same                                                        | Tab 2 — Category detail view                    |
| `MedicineGenericTable`         | `MedicineGenericTable` / `MedicineBrandTable`               | Tab 3 — Generic list                            |
| `MedicineGenericDetailsTable`  | `MedicineGenericDetailsTable` / `MedicineBrandDetailsTable` | Tab 3 — Generic detail view                     |
| `MedicineDispenseTable`        | `MedicineBillTable`                                         | Tab 4 — Dispensing record list                  |
| `PrescriptionTable`            | same                                                        | Prescription list                               |
| `RequestDocumentTable`         | same                                                        | Consultation / request document list            |
| `SpecializationTable`          | same                                                        | Specialization list                             |
| `RoleTable`                    | same                                                        | Role list                                       |
| `BannerTable`                  | `SliderTable`                                               | Front page banner list                          |
| `CountryTable`                 | `CountriesTable`                                            | Country list                                    |
| `ProvinceTable`                | `StateTable`                                                | Province list                                   |
| `CityTable`                    | same                                                        | City list                                       |
| `BarangayTable`                | same                                                        | Barangay list                                   |
| `DashboardRouter`              | `Dashboard`                                                 | Redirects to correct role dashboard             |

**Livewire 3 table component pattern (recode guideline):**

```php
// Each table component should have:
// - public $search = '';
// - public $perPage = 10;
// - public $sortField = 'created_at';
// - public $sortDirection = 'desc';
// - Computed property for data()
// - Permission check in mount() via Gate or $this->authorize()
```

---

## 10. Helper Functions (must keep/recode)

All helpers defined in `app/helpers.php` — all views/controllers use these:

| Helper                     | Returns | Description                    |
| -------------------------- | ------- | ------------------------------ |
| `getLogInUser()`           | User    | Current authenticated user     |
| `getLogInUserId()`         | int     | Current user ID                |
| `getAppName()`             | string  | Clinic name from settings      |
| `getAppLogo()`             | string  | Logo URL from settings         |
| `getAppFavicon()`          | string  | Favicon from settings          |
| `getDashboardURL()`        | string  | Role-based dashboard URL       |
| `getProvinces($countryId)` | array   | Provinces for a country        |
| `getCities($provinceId)`   | array   | Cities for a province          |
| `getBarangays($cityId)`    | array   | Barangays for a city           |
| `getBadgeColor($index)`    | string  | Bootstrap badge color by index |
| `getBadgeStatusColor()`    | string  | Badge color by status type     |
| `getSettingValue($key)`    | mixed   | Setting value from DB (cached) |
| `isRole($role)`            | bool    | Check if current user has role |

**Additional helpers to add in recode:**

```php
// roleRoute($name, $params) — prefix a named route with current user's role prefix
// hasPermission($permission) — shorthand for current user permission check
// roleRoutePrefix() — returns 'admin'/'staff'/'doctors'/'patients' for current user
// getDefaultPaginationCount() — returns configured pagination count (from settings or 10)
```

---

## 11. Middleware Stack

| Middleware        | Class                       | Purpose                       |
| ----------------- | --------------------------- | ----------------------------- |
| `auth`            | Laravel built-in            | Require authentication        |
| `role:*`          | Spatie RoleMiddleware       | Restrict by role              |
| `permission:*`    | Spatie PermissionMiddleware | Restrict by permission        |
| `checkUserStatus` | `CheckUserStatus`           | Block inactive users          |
| `xss`             | `XSS`                       | Sanitize XSS inputs           |
| `verified`        | Laravel built-in            | Email verification (optional) |

---

## 12. Seeders & Initial Data

| Seeder                            | Purpose                                               |
| --------------------------------- | ----------------------------------------------------- |
| `DefaultRoleSeeder`               | Creates 4 roles: clinic_admin, staff, doctor, patient |
| `DefaultPermissionSeeder`         | Creates all 14 permissions                            |
| `DefaultMedicinePermissionSeeder` | Assigns manage_medicines to admin/staff/doctor        |
| `DefaultAssignPermissionSeeder`   | Assigns manage_request_documents to doctor/patient    |
| `RolePermissionsSeeder`           | Full role-permission matrix sync                      |
| `DefaultUserSeeder`               | Creates default admin/doctor/patient accounts         |
| `DefaultStaffSeeder`              | Creates default staff account                         |
| `CampusSeeder`                    | NORSU campus list                                     |
| `CollegeSeeder`                   | NORSU college list                                    |
| `CourseSeeder`                    | NORSU course list                                     |
| `YearLevelSeeder`                 | Year levels                                           |
| `VaccinationSeeder`               | Vaccination status options                            |
| `DiagnoseSeeder`                  | Common diagnoses reference                            |
| `OfficeSeeder`                    | NORSU offices                                         |
| `DepartmentSeeder`                | NORSU departments                                     |
| `MedicineSeeder`                  | Sample medicine data                                  |
| `SettingTableSeeder`              | Default settings (clinic name, etc.)                  |
| `CreateCountriesSeeder`           | Countries list                                        |
| `DefaultSpecializationSeeder`     | Medical specializations                               |
| `DefaultSliderSeeder`             | Default front page banners                            |

**Default credentials (seeded):**

```
Admin:   admin@norsuclinic.com   / 123456
Doctor:  doctor@norsuclinic.com  / 123456
Patient: patient@norsuclinic.com / 123456
```

---

## 13. Recode Architecture Guidelines

### 13.1 No Hardcoded Values — Use Helpers Everywhere

```php
// WRONG — hardcoded
return redirect('/admin/dashboard');
<a href="/admin/patients">Patients</a>

// RIGHT — use helpers and named routes
return redirect(getDashboardURL());
<a href="{{ roleRoute('patients.index') }}">Patients</a>
```

### 13.2 Route Structure (all dynamic)

```php
// All routes MUST follow this pattern
Route::prefix(config('roles.prefixes.clinic_admin'))   // 'admin'
    ->name(config('roles.names.clinic_admin') . '.')   // 'admin.'
    ->middleware(['auth', 'role:clinic_admin'])
    ->group(function() {
        // sub-groups by permission
    });
```

Store role prefixes/names in `config/roles.php`:

```php
return [
    'prefixes' => [
        'clinic_admin' => 'admin',
        'staff'        => 'staff',
        'doctor'       => 'doctors',
        'patient'      => 'patients',
    ],
    'names' => [
        'clinic_admin' => 'admin',
        'staff'        => 'staff',
        'doctor'       => 'doctors',
        'patient'      => 'patients',
    ],
];
```

### 13.3 Livewire 3 Component Pattern

```php
// resources/js/livewire.js — NO rappasoft, NO 3rd party table packages
// Every list/table is a Livewire 3 component with:
//   - search, perPage, sortField, sortDirection as public properties
//   - uses #[Computed] attribute for the query
//   - Uses wire:navigate for SPA-like navigation
```

### 13.4 RBAC Admin Control

- Admin UI to assign/revoke permissions per role
- No hardcoded `if ($user->hasRole('admin'))` in views — use `@can` and `@role` directives
- Gate policies for all models
- All navigation menus generated dynamically based on permissions

### 13.5 Helpers File Structure (recode)

```
app/
  helpers.php          — global helper functions
  Helpers/
    DateHelper.php     — date formatting helpers
    RouteHelper.php    — roleRoute(), roleRoutePrefix() (NEW)
    SettingHelper.php  — getSettingValue(), getAppName() etc. (NEW)
```

### 13.6 Settings-Driven Configuration

All configurable values (clinic name, logo, pagination count, timezone, etc.) must come from the `settings` table accessed via helpers — never from `.env` or hardcoded strings in views.

---

### 13.7 Local Network Deployment

> **Production target:** A single Windows or Linux server machine connected to the clinic LAN. All devices (staff PCs, doctor tablets) access the app over the local network via the server’s IP address or hostname. No internet access required.

#### `.env` settings for LAN deployment

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=http://192.168.1.x          # server's static LAN IP (set once, never hardcode elsewhere)

DB_CONNECTION=mysql
DB_HOST=127.0.0.1                   # DB runs on same machine
DB_PORT=3306
DB_DATABASE=norsuclinic
DB_USERNAME=norsuclinic_user
DB_PASSWORD=strong_password_here

SESSION_DRIVER=database             # file sessions break on multi-worker; use DB
CACHE_STORE=database                # no Redis needed on local server
QUEUE_CONNECTION=database           # no Redis/Beanstalk; DB queue is fine

MAIL_MAILER=log                     # no internet = no SMTP; log mails locally
                                    # (or use local SMTP like hMailServer if internal email needed)
```

#### PHP / web server

- **Recommended stack:** PHP 8.3+ + Nginx (or Apache) + MySQL 8 on the server machine
- Bind Nginx/Apache to `0.0.0.0` so all LAN clients can reach it on port 80 (or 8080)
- Assign the server a **static LAN IP** via router DHCP reservation or manual network config
- Optionally set a local hostname (e.g. `norsuclinic.local`) via the router’s local DNS or add to each client’s `hosts` file

#### File / media storage

```dotenv
FILESYSTEM_DISK=local               # store all uploaded files on the server disk
```

- All Spatie Media Library uploads go to `storage/app/public/`
- Run `php artisan storage:link` once after deploy
- Backups: copy the entire `storage/` folder and DB dump to an external drive on a schedule

#### Queue worker & scheduler

LAN servers typically run Windows or a simple Linux box. Use one of:

**Linux (systemd service for queue worker):**

```
[Service]
ExecStart=php /var/www/norsuclinic/artisan queue:work database --sleep=3 --tries=3
Restart=always
```

**Windows (Task Scheduler):**

- Create a task that runs every minute: `php artisan schedule:run` (for scheduler)
- Create a persistent task: `php artisan queue:work database` (for queue jobs)

#### Security on LAN (no internet, still apply basics)

- `APP_DEBUG=false` in production — never expose stack traces on LAN
- CSRF protection is active by default in Laravel — keep it
- Set `SESSION_SECURE_COOKIE=false` (HTTP-only LAN, no HTTPS needed unless you add it)
- Restrict MySQL to `127.0.0.1` — do not expose DB port to the LAN
- Use a dedicated DB user with only the permissions needed (no root)
- Strong admin password; enforce password change on first login (already in feature list)

#### No email / cloud services

- `MAIL_MAILER=log` — all notification emails written to `storage/logs/laravel.log` instead of sent
- No AWS S3, no Pusher, no Stripe, no external APIs
- Livewire polling (queue countdown, auto-refresh) works over LAN without websockets

---

## 14. Recode Progress Tracker

Use this section as a checklist. Mark each item as you recode it.

### Phase 0 — Project Setup

#### 0a — Bootstrap (already done via `laravel new`)

- [x] `laravel new NORSUCLINIC` — Livewire starter kit, built-in auth, single-file components, no teams, Pest, Laravel Boost
- [x] Livewire 3 (single-file) — pre-installed
- [x] Tailwind CSS — pre-installed
- [x] Alpine.js — pre-installed
- [x] Auth scaffolding (login, register, password reset) — pre-installed
- [x] Pest testing framework — configured
- [x] Laravel Boost — installed

#### 0b — Additional packages to install

- [x] `composer require spatie/laravel-permission`
- [x] `composer require spatie/laravel-medialibrary`
- [x] `composer require barryvdh/laravel-dompdf`
- [x] `composer require maatwebsite/excel`

#### 0c — Configuration

- [x] Create `config/roles.php` for dynamic role prefixes/names
- [x] Create base helpers file (`app/helpers.php`) — all helpers implemented
- [x] Autoload helpers in `composer.json` (`files` key)
- [x] Publish Spatie permission migrations and config (`config/permission.php` published)
- [x] Publish Spatie media library migrations
- [x] Configure `filesystems.php` for media storage
- [x] Expand users table migration (`2026_04_15_200000_modify_users_add_clinic_fields.php`) — adds all clinic fields
- [x] Update `User` model — `HasRoles`, `InteractsWithMedia`, all fillable fields, `full_name` accessor, boot sync of `name` column
- [x] Create `LoginResponse` (`app/Http/Responses/LoginResponse.php`) — role-based redirect via `getDashboardURL()`
- [x] Bind `LoginResponse` in `AppServiceProvider::register()`

### Phase 1 — Auth & Core

- [ ] Login page (blade) _(starter kit pre-built — review/customise)_
- [x] Role-based redirect after login (`LoginResponse` binds `getDashboardURL()` — ✅ verified)
- [x] Profile edit page — first_name/middle_name/last_name/contact/dob/gender/blood_type + email ✅
- [x] Password change — `⚡security.blade.php` (starter kit), default-password warning flash ✅
- [x] Dark mode toggle — `⚡appearance.blade.php` persists to `users.dark_mode` DB column ✅
- [x] Default password detection — `CheckDefaultPassword` middleware, alias `checkDefaultPassword`, applied to all role route groups ✅

### Phase 2 — Roles, Permissions & Users

- [x] Run migrations: users table expanded + Spatie roles/permissions tables — ✅ verified
- [x] All seeders run — 4 roles, 14 permissions, role-permission matrix, 4 default users — ✅ verified
- [x] Role manager UI — `pages::admin.role-manager` Livewire SFC, permission checkboxes, `syncPermissions()` save ✅
- [x] User CRUD — `pages::admin.user-table` Livewire SFC, search/filter/paginate, create/edit modal ✅
- [x] Status toggle, reset password, delete actions — all in dropdown per row ✅

### Phase 3 — Doctors & Staff

- [x] Doctor CRUD + Livewire table (`pages::admin.doctor-table`) ✅
- [x] Doctor qualifications add/remove (inline modal, `openQualifications`) ✅
- [x] Doctor specializations assign (many-to-many, multi-checkbox in modal) ✅
- [x] Staff CRUD + Livewire table (`pages::admin.staff-table`, queries `User::role('staff')`) ✅
- [x] Specializations CRUD + Livewire table (`pages::admin.specialization-table`) ✅
- [x] Status toggle, reset password ✅

### Phase 4 — Patients

- [ ] Patient CRUD + Livewire table
- [ ] Patient unique ID generation
- [ ] Patient profile (academic info, blood type, vaccination)
- [ ] Patient history view
- [ ] Patient address management

### Phase 5 — Patient Queue

- [ ] Queue CRUD + Livewire component
- [ ] AJAX auto-refresh with countdown badge
- [ ] Call next / complete queue actions
- [ ] Priority flag
- [ ] Doctor-specific queue view
- [ ] Link consultation to queue entry

### Phase 6 — Consultation / Request Documents

- [ ] Consultation form create/edit/view
- [ ] Medical certificate create/edit/view
- [ ] PDF export (dompdf)
- [ ] Image attachments (Spatie media)
- [ ] Search users (patient/guest/staff) AJAX
- [ ] Get last consultation pre-fill
- [ ] Consultation medicines inline management

### Phase 7 — Prescriptions

- [ ] Prescription CRUD + Livewire table
- [ ] Prescription medicines (add/remove via Livewire)
- [ ] Prescription PDF export
- [ ] Active/Inactive toggle

### Phase 8 — Medicine (Single Tabbed Screen)

> All medicine-related features live at a single route (`/admin/medicines`, `/staff/medicines`) rendered by the `MedicineScreen` Livewire parent component with 4 tabs. Tab state is in URL query string (`?tab=`).

- [ ] `MedicineScreen` parent component — tab routing via `?tab=medicines|categories|generics|history`
- [ ] **Tab 1 — Medicines**
    - [ ] Medicine master CRUD + `MedicineTable` Livewire component
    - [ ] Stock-in (`StockInTable`) — create batch with multiple line items
    - [ ] Auto-increment `available_quantity` on stock-in save
    - [ ] Excel export of stock-in batches
    - [ ] Stock-out log (`StockOutTable`) — read from `stock_out_view`
    - [ ] Auto-decrement `available_quantity` when dispensed
    - [ ] Low-stock alerts (minimum_stock_alert + stock_alert_percentage)
- [ ] **Tab 2 — Categories**
    - [ ] Category CRUD + `MedicineCategoryTable` Livewire component
    - [ ] Active/Inactive toggle
- [ ] **Tab 3 — Generics**
    - [ ] Generic CRUD + `MedicineGenericTable` Livewire component
    - [ ] Generic detail view (lists medicines under a generic)
- [ ] **Tab 4 — History (Dispensing)**
    - [ ] Dispensing record CRUD + `MedicineDispenseTable` Livewire component
    - [ ] Multi-medicine dispensing per record
    - [ ] PDF export of dispensing record
    - [ ] Filter by date, patient, doctor

### Phase 9 — Dashboards

- [ ] Admin dashboard (Livewire, stats, recent records)
- [ ] Doctor dashboard (queue + prescriptions)
- [ ] Staff dashboard (queue stats)
- [ ] Patient dashboard (own records)

### Phase 10 — Location Management

- [ ] Countries CRUD + Livewire table
- [ ] Provinces CRUD + Livewire table
- [ ] Cities CRUD + Livewire table
- [ ] Barangays CRUD + Livewire table
- [ ] AJAX cascading dropdowns (Country → Province → City → Barangay)

### Phase 11 — Academic Data

- [ ] Campus CRUD
- [ ] College CRUD (with campus FK)
- [ ] Course CRUD (with college FK)
- [ ] Year Level CRUD
- [ ] Cascading dropdowns in patient/user forms
- [ ] Vaccination status management
- [ ] Offices management
- [ ] Departments management

### Phase 12 — Settings & CMS

- [ ] Settings page (all fields, logo/favicon upload)
- [ ] `SettingsService` with caching
- [ ] `getSettingValue()` helper
- [ ] CMS update (about, services, contact)
- [ ] Banner/Slider management

### Phase 13 — Notifications & Activity Logs

- [ ] Database notifications (bell icon, unread count)
- [ ] Mark read / mark all read
- [ ] Activity log listing + Livewire table
- [ ] Activity log CSV export
- [ ] `LogsActivity` trait for all controllers

### Phase 14 — Supplementary Features

- [ ] Diagnoses reference data management
- [ ] Guest / walk-in module
- [ ] Specializations CRUD + Livewire table

### Phase 15 — Front Public Pages

- [ ] Home page
- [ ] About Us page
- [ ] Services page
- [ ] Doctors page
- [ ] Contact page
- [ ] Terms & Conditions
- [ ] Privacy Policy

### Phase 16 — QA & Hardening

- [ ] Audit all routes — no hardcoded prefixes in views/controllers
- [ ] Audit all `if hasRole()` in views — replace with `@can` / `@role`
- [ ] Review OWASP Top 10 (XSS, CSRF, SQL injection, auth)
- [ ] Confirm all `manage_*` permissions are checked via route middleware (not just in views)
- [ ] Load testing / N+1 query audit (add missing eager loads)
- [ ] Confirm all helpers used consistently (no raw `Auth::user()->hasRole()` in blade)
- [ ] Run all seeders on fresh DB — verify data is correct
- [ ] Verify PDF exports render correctly
- [ ] Verify Excel exports work

### Phase 17 — Local Server Production Deploy

- [ ] Assign server a static LAN IP (router DHCP reservation or manual NIC config)
- [ ] Set `APP_URL=http://<LAN_IP>` in `.env` — verify `getDashboardURL()` and all named routes resolve correctly
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Set `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`
- [ ] Set `MAIL_MAILER=log` (no internet SMTP)
- [ ] Run `php artisan storage:link` for Spatie Media Library public access
- [ ] Run `php artisan config:cache`, `route:cache`, `view:cache`
- [ ] Set up queue worker as a persistent service (systemd on Linux / Task Scheduler on Windows)
- [ ] Set up scheduler (`php artisan schedule:run` every minute)
- [ ] Restrict MySQL to `127.0.0.1` only — no LAN-exposed DB port
- [ ] Create dedicated MySQL user for the app (no root access)
- [ ] Confirm `storage/` and `bootstrap/cache/` are writable by the web server user
- [ ] Test from a second LAN device — verify the app loads via server IP
- [ ] Test all PDF and Excel exports from a LAN client machine
- [ ] Test file/image uploads (Spatie Media Library) and confirm files persist on disk
- [ ] Set up a regular backup script: DB dump + `storage/` folder copy to external drive

---

_Last updated: April 15, 2026_  
_Generated by deep-scan of `c:\Projects\NORSUCLINIC`_
