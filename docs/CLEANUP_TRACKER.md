# NORSUCLINIC — Master Cleanup Tracker

**Project:** NORSUCLINIC Laravel 10 Medical Clinic System  
**Scanned:** April 2, 2026  
**Last Deep-Scan:** April 2, 2026 — Full Phases 1–7 deep-scan audit. 25+ breaking/dead issues found and fixed. All gates passed.  
**Status:** Phase 7 Complete ✅ + Deep-Scan Audit Complete ✅ — All 16 tables dropped. 28 dead migrations removed. Seeders/factories cleaned. Dead routes, controllers, views, JS, configs all cleaned.

---

## LOCKED DECISIONS

| #   | Feature                                                             | Decision              | Notes                                                                                           |
| --- | ------------------------------------------------------------------- | --------------------- | ----------------------------------------------------------------------------------------------- |
| 1   | Prescriptions                                                       | **KEEP (standalone)** | Remove `appointment_id`, bind to patient only. Staff: add/view/update. Doctor: full CRUD + PDF. |
| 2   | Services & Service Categories                                       | **REMOVE**            | Tied to appointments. No other use.                                                             |
| 3   | DoctorSession & ClinicSchedule                                      | **REMOVE**            | No longer needed.                                                                               |
| 4   | Campus / College / Course / YearLevel / Office / Department / Guest | **KEEP**              | Retained as-is. No changes to `users` table edu columns or relationships.                       |
| 5   | WebSockets                                                          | **REMOVE**            | `broadcast` driver already set to `null`.                                                       |

---

## FEATURES TO REMOVE

| Feature                                             | Tracker File                                       | PHP Phase | Status                                                                                                                       |
| --------------------------------------------------- | -------------------------------------------------- | --------- | ---------------------------------------------------------------------------------------------------------------------------- |
| Payment Gateways (PayPal, Stripe, PayTM, Authorize) | [CLEANUP_PAYMENTS.md](CLEANUP_PAYMENTS.md)         | Phase 1   | ✅ Complete                                                                                                                  |
| Transactions                                        | [CLEANUP_PAYMENTS.md](CLEANUP_PAYMENTS.md)         | Phase 1   | ✅ Complete                                                                                                                  |
| Appointments / Bookings                             | [CLEANUP_APPOINTMENTS.md](CLEANUP_APPOINTMENTS.md) | Phase 2   | ✅ Complete                                                                                                                  |
| Services & Service Categories                       | [CLEANUP_APPOINTMENTS.md](CLEANUP_APPOINTMENTS.md) | Phase 2   | ✅ Complete                                                                                                                  |
| DoctorSessions & ClinicSchedule                     | [CLEANUP_APPOINTMENTS.md](CLEANUP_APPOINTMENTS.md) | Phase 2   | ✅ Complete                                                                                                                  |
| Visits / Encounters                                 | [CLEANUP_VISITS.md](CLEANUP_VISITS.md)             | Phase 3   | ✅ Complete                                                                                                                  |
| Patient Smart Cards / QR Codes                      | [CLEANUP_SMART_CARDS.md](CLEANUP_SMART_CARDS.md)   | Phase 4   | ✅ Complete                                                                                                                  |
| WebSockets                                          | [CLEANUP_UNUSED.md](CLEANUP_UNUSED.md)             | Phase 5   | ✅ Complete                                                                                                                  |
| Navigation cleanup                                  | [CLEANUP_NAVIGATION.md](CLEANUP_NAVIGATION.md)     | Phase 6   | ✅ Complete — All dashboard, menu, controller stubs fixed                                                                    |
| Database cleanup migrations                         | [CLEANUP_DATABASE.md](CLEANUP_DATABASE.md)         | Phase 7   | ✅ Complete — 16 tables dropped, prescriptions.appointment_id removed, 28 dead migrations deleted, seeders/factories cleaned |

---

## RETAINED FEATURES

| Feature                                                             | Location                                              | Notes                              |
| ------------------------------------------------------------------- | ----------------------------------------------------- | ---------------------------------- |
| Patients CRUD                                                       | `app/Http/Controllers/PatientController.php`          | Core feature                       |
| Doctors CRUD                                                        | `app/Http/Controllers/UserController.php`             | Core feature                       |
| Staff CRUD                                                          | `app/Http/Controllers/UserController.php`             | Core feature                       |
| Prescriptions                                                       | `app/Http/Controllers/PrescriptionController.php`     | Decoupled from appointments        |
| Medicine Inventory                                                  | `app/Http/Controllers/MedicineController.php`         | Core feature                       |
| Medicine Bills                                                      | `app/Http/Controllers/MedicineBillController.php`     | Core feature (keep payment_type)   |
| Request Documents / Consultation Forms                              | `app/Http/Controllers/RequestDocumentsController.php` | Core feature                       |
| Patient Queue                                                       | `app/Http/Controllers/PatientQueueController.php`     | Core feature                       |
| Roles & Permissions                                                 | `database/seeders/DefaultPermissionSeeder.php`        | Keep 12 remaining permissions      |
| Settings                                                            | `app/Http/Controllers/SettingController.php`          | Core feature                       |
| Front CMS (manage_front_cms)                                        | Banners/sliders only                                  | NOT appointment-related            |
| Notifications                                                       | `app/Notifications/`                                  | Core feature                       |
| Impersonate (Lab404)                                                | `lab404/impersonate`                                  | Admin tool — keep                  |
| Vaccination                                                         | `app/Models/Vaccination.php`                          | Medical data — keep                |
| Specializations                                                     | `app/Models/Specialization.php`                       | Keep (doctor profile)              |
| Country / State / City                                              | `app/Models/`                                         | Keep (address fields)              |
| Campus / College / Course / YearLevel / Office / Department / Guest | `app/Models/`                                         | Keep (student classification data) |
| Qualifications                                                      | `app/Models/Qualification.php`                        | Keep (doctor profile)              |

---

## REMOVED PERMISSIONS

| Permission               | Seeder                  | Affected Roles         | Status                                                          |
| ------------------------ | ----------------------- | ---------------------- | --------------------------------------------------------------- |
| `manage_appointments`    | DefaultPermissionSeeder | doctor, staff, patient | ✅ Removed (Phase 2)                                            |
| `manage_patient_visits`  | DefaultPermissionSeeder | doctor, staff, patient | ✅ Removed (Phase 3)                                            |
| `manage_doctor_sessions` | DefaultPermissionSeeder | doctor, staff          | ✅ Removed (Phase 2)                                            |
| `manage_services`        | DefaultPermissionSeeder | doctor, staff, admin   | ✅ Removed (Phase 2)                                            |
| `manage_specialties`     | DefaultPermissionSeeder | admin, doctor, staff   | ✅ **KEPT** — Specialization model retained for doctor profiles |
| `manage_transactions`    | DefaultPermissionSeeder | doctor, staff, patient | ✅ Removed (Phase 1)                                            |

> ✅ `manage_specialties` confirmed KEPT — Specialization model is retained for doctor profiles.

---

## COMPOSER PACKAGES TO REMOVE

### ✅ Removed — Phase 1

```json
"srmklive/paypal": "^3.0",
"stripe/stripe-php": "*",
"anandsiddharth/laravel-paytm-wallet": "^2.0",
"authorizenet/authorizenet": "~1.9",
"gerardojbaez/money": "^2.4.0"
```

### ✅ Removed — Phase 4

```json
"simplesoftwareio/simple-qrcode": "^4.2"
```

### ⬜ Remaining — Phase 5 (WebSockets)

```json
"beyondcode/laravel-websockets": "^1.14",
"pusher/pusher-php-server": "^7.2"
```

> Run after all references removed: `composer remove beyondcode/laravel-websockets pusher/pusher-php-server`

---

## EXECUTION PHASES

| Phase          | Description                                    | Tracker File            | Dependencies     |
| -------------- | ---------------------------------------------- | ----------------------- | ---------------- |
| **Phase 0**    | Create tracker `.md` files                     | This file               | None             |
| **Phase 1** ✅ | Remove payments & transactions                 | CLEANUP_PAYMENTS.md     | None             |
| ✅ Gate 1      | Validate before Phase 2                        | See below               | Phase 1 done ✅  |
| **Phase 2** ✅ | Remove appointments, services, doctor sessions | CLEANUP_APPOINTMENTS.md | After Phase 1    |
| ✅ Gate 2      | Validate before Phase 3                        | See below               | Phase 2 done ✅  |
| **Phase 3** ✅ | Remove visits / encounters                     | CLEANUP_VISITS.md       | Independent      |
| ✅ Gate 3      | Validate before Phase 4                        | See below               | Phase 3 done ✅  |
| **Phase 4** ✅ | Remove smart cards                             | CLEANUP_SMART_CARDS.md  | Complete         |
| ✅ Gate 4      | Validate before Phase 5                        | See below               | Phase 4 done ✅  |
| **Phase 5**    | Remove WebSockets                              | CLEANUP_UNUSED.md       | Independent      |
| ✅ Gate 5      | Validate before Phase 6                        | See below               | Phase 5 done     |
| **Phase 6**    | Navigation & dashboard cleanup                 | CLEANUP_NAVIGATION.md   | After Phases 1–5 |
| ✅ Gate 6      | Validate before Phase 7                        | See below               | Phase 6 done     |
| **Phase 7** ✅ | Database cleanup migrations                    | CLEANUP_DATABASE.md     | After Phase 6    |
| ✅ Gate 7      | Validate before Phase 8                        | See below               | Phase 7 done ✅  |
| **Phase 8**    | Final verification                             | —                       | After all phases |

---

## PHASE GATE VALIDATION (Run after EVERY phase)

Run these commands in order after completing each phase. **Do NOT proceed to the next phase if any command errors.**

### Gate Commands (copy-paste ready)

```bash
# Step 1 — Autoloader: catch missing/deleted classes immediately
composer dump-autoload

# Step 2 — Route check: catch undefined controllers / actions
php artisan route:list 2>&1 | Select-String -Pattern "ERROR|error|Exception"

# Step 3 — Config check: catch missing config keys or broken service providers
php artisan config:cache

# Step 4 — View cache: catch broken @include / @extends / missing views
php artisan view:cache

# Step 5 — Clear all caches after validation
php artisan optimize:clear

# Step 6 — Check Laravel log for new errors
Get-Content storage\logs\laravel.log -Tail 30
```

### What Each Gate Catches

| Gate   | After Phase            | Key Risks To Catch                                                                                     |
| ------ | ---------------------- | ------------------------------------------------------------------------------------------------------ |
| Gate 1 | Phase 1 (Payments)     | Deleted Transaction/PaymentGateway classes still imported in UserController, helpers, seeders          |
| Gate 2 | Phase 2 (Appointments) | Deleted Appointment/Service/DoctorSession still used in DashboardRepository, Patient/Doctor models nav |
| Gate 3 | Phase 3 (Visits)       | Deleted Visit classes still in PatientService, duplicate web.php routes fixed                          |
| Gate 4 | Phase 4 (Smart Cards)  | Broken `@include` for renamed skeleton; QrCode facade removed from config                              |
| Gate 5 | Phase 5 (WebSockets)   | Missing Pusher/Echo references in bootstrap.js or broadcasted events                                   |
| Gate 6 | Phase 6 (Navigation)   | Broken `@can` guards, missing Livewire component tags, null dashboard data                             |
| Gate 7 | Phase 7 (Database)     | Migration runs cleanly; no FK violations; `prescriptions.appointment_id` actually dropped              |

### Extra Check for Gate 2 (Prescription decoupling)

```bash
# Verify prescription create/edit routes still exist and point to correct controller
php artisan route:list --name=prescription
```

### Extra Check for Gate 7 (Database)

```bash
# Confirm dropped tables are gone
php artisan db:show
# Or check specific table
php artisan db:table prescriptions
```

---

## PHASE 8 — VERIFICATION CHECKLIST

After all phases are complete:

- [ ] `php artisan route:list` — no routes for removed features
- [ ] `php artisan config:cache` — no missing config keys
- [ ] `php artisan view:cache` — no broken `@include` directives
- [ ] `php artisan optimize` — runs cleanly
- [ ] Login as **clinic_admin** — no broken nav links
- [ ] Login as **doctor** — no broken nav links or dashboard stats
- [ ] Login as **staff** — no broken nav links
- [ ] Login as **patient** — no broken nav links
- [ ] Create a prescription without appointment — form works
- [ ] View patient profile — no appointment tab or stats
- [ ] `composer install` — no missing packages
- [ ] No PHP errors in `storage/logs/laravel.log`

---

## PHASE 1–3 DEEP-SCAN AUDIT LOG

**Performed:** April 2, 2026

### Phase 3 — Visits (completed + verified)

| Item                                                                                                           | Action            | File                                                                                                               |
| -------------------------------------------------------------------------------------------------------------- | ----------------- | ------------------------------------------------------------------------------------------------------------------ |
| `VisitController`, `PatientVisitController`                                                                    | Deleted           | `app/Http/Controllers/`                                                                                            |
| `Visit`, `VisitProblem`, `VisitObservation`, `VisitNote`, `VisitPrescription`                                  | Deleted           | `app/Models/`                                                                                                      |
| `VisitTable`, `DoctorVisitTable`, `PatientVisitTable`                                                          | Deleted           | `app/Livewire/`                                                                                                    |
| `VisitRepository`, `PatientVisitRepository`                                                                    | Deleted           | `app/Repositories/`                                                                                                |
| `CreateVisitRequest`, `UpdateVisitRequest`, `CreateVisitPrescriptionRequest`, `UpdateVisitPrescriptionRequest` | Deleted           | `app/Http/Requests/`                                                                                               |
| `resources/views/visits/`, `resources/views/patient_visits/`                                                   | Deleted           | `resources/views/`                                                                                                 |
| Visit route blocks (2 duplicates) + `VisitController` import                                                   | Removed           | `routes/web.php`                                                                                                   |
| Visit route block + `VisitController` import                                                                   | Removed           | `routes/doctor.php`                                                                                                |
| Visit route block + `VisitController` import                                                                   | Removed           | `routes/staff.php`                                                                                                 |
| Patient visit routes + `PatientVisitController` import                                                         | Removed           | `routes/patient.php`                                                                                               |
| `getVisitRoute()`                                                                                              | Removed           | `app/helpers.php`                                                                                                  |
| `visits()->delete()` cascade + `visits(): HasMany` relation                                                    | Removed           | `app/Models/Patient.php`                                                                                           |
| `getPatientWithHistory()` visits eager-load                                                                    | Removed           | `app/Services/PatientService.php`                                                                                  |
| `deletePatient()` `visits()->delete()`                                                                         | Removed           | `app/Services/PatientService.php`                                                                                  |
| `getPatientStatistics()` total_visits/last_visit                                                               | Stubbed to 0/null | `app/Services/PatientService.php`                                                                                  |
| `hasMedicalRecords()` `visits()->exists()`                                                                     | Removed           | `app/Services/PatientService.php`                                                                                  |
| `Visit::wherePatientId()` guard in `destroy()`                                                                 | Removed           | `app/Http/Controllers/PatientController.php`                                                                       |
| `Visit::whereDoctorId()` guard in `destroy()` _(deep-scan find)_                                               | Removed           | `app/Http/Controllers/UserController.php`                                                                          |
| `manage_patient_visits` permission                                                                             | Removed           | `DefaultPermissionSeeder`, `RolePermissionsSeeder`, `DefaultAssignPermissionSeeder`, `StaffDoctorPermissionSeeder` |
| Commented-out visit nav blocks                                                                                 | Removed           | `menu.blade.php`, `sub_menu.blade.php`                                                                             |

### Phase 2 — Orphaned Files (found in deep-scan, removed April 2 2026)

| Item                                                                | Reason Missed                          | Action                                                                                                                             |
| ------------------------------------------------------------------- | -------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| `AppointmentController.php`                                         | Not in original Phase 2 delete list    | Deleted                                                                                                                            |
| `PatientAppointmentController.php`                                  | Not in original Phase 2 delete list    | Deleted                                                                                                                            |
| `AppointmentRepository.php`                                         | Not in original Phase 2 delete list    | Deleted                                                                                                                            |
| `Appointment.php` (model)                                           | Not in original Phase 2 delete list    | Deleted                                                                                                                            |
| `resources/views/patient_appointments/`                             | Not in original Phase 2 delete list    | Deleted                                                                                                                            |
| `resources/views/appointment_pdf/invoice.blade.php`                 | Not in original Phase 2 delete list    | Deleted                                                                                                                            |
| `WeekDay.php` `doctorSession()` relation                            | DoctorSession model deleted in Phase 2 | Removed relation + PHPDoc                                                                                                          |
| `PrescriptionTable.php` `$appointMentId` filter                     | Prescription decoupled in Phase 2      | Removed dead property + filter                                                                                                     |
| `prescriptions/add-button.blade.php` route call with appointment ID | Prescription decoupled in Phase 2      | Fixed route to not pass ID                                                                                                         |
| `roles/fields.blade.php` — dead permission groups                   | Permissions removed across phases      | Removed `manage_appointments`, `manage_patient_visits`, `manage_doctor_sessions`, `manage_services`, `manage_transactions` entries |

### Gate 3 Final Validation Results

| Check                                                                        | Result                                        |
| ---------------------------------------------------------------------------- | --------------------------------------------- |
| `composer dump-autoload`                                                     | ✅ 9709 classes, no errors                    |
| `php artisan route:list`                                                     | ✅ No visit/appointment routes, no exceptions |
| `php artisan config:cache`                                                   | ✅ Clean                                      |
| `php artisan view:cache`                                                     | ✅ Clean                                      |
| Scan: `App\Models\{Visit,Appointment,Service,DoctorSession,...}` in `app/**` | ✅ Zero matches                               |
| Scan: removed helper calls in `app/**` and `resources/views/**`              | ✅ Zero matches                               |
| Scan: removed route controllers in `routes/**`                               | ✅ Zero matches                               |

### Phase 4 — Smart Cards / QR Codes (completed + verified)

| Item                                                                                                     | Action                                                | File                                                |
| -------------------------------------------------------------------------------------------------------- | ----------------------------------------------------- | --------------------------------------------------- |
| `smart_patient_cards_skeleton.blade.php`                                                                 | Renamed → `loading_skeleton.blade.php`                | `resources/views/livewire/`                         |
| `SpecializationTable.php`, `MedicineGenericTable.php`, `MedicineBrandTable.php`, `MedicineBillTable.php` | Updated `placeholder()` → `livewire.loading_skeleton` | `app/Livewire/`                                     |
| `resources/views/generate_patient_smart_cards/` (5 files)                                                | Deleted                                               | `resources/views/`                                  |
| `resources/views/smart_card_pdf/smart_card_pdf.blade.php`                                                | Deleted                                               | `resources/views/`                                  |
| `resources/assets/js/smart_patient_cards/`                                                               | Deleted                                               | `resources/assets/js/`                              |
| `@include('generate_patient_smart_cards/components/show_card')`                                          | Removed                                               | `resources/views/patient_dashboard/index.blade.php` |
| Smart card commented button block (lines 25–35)                                                          | Removed                                               | `resources/views/layouts/header.blade.php`          |
| `SimpleSoftwareIO\QrCode\QrCodeServiceProvider::class`                                                   | Removed from providers                                | `config/app.php`                                    |
| `'QrCode' => SimpleSoftwareIO\QrCode\Facades\QrCode::class`                                              | Removed from aliases                                  | `config/app.php`                                    |
| `smart-card-pdf.scss` entry                                                                              | Removed                                               | `webpack.mix.js`                                    |
| `'smart_patient_card' => [...]` block                                                                    | Removed                                               | `lang/en/messages.php`                              |
| `simplesoftwareio/simple-qrcode` (+ `bacon/bacon-qr-code`, `dasprid/enum`)                               | `composer remove`                                     | `composer.json`                                     |

### Phases 1–4 Cross-Phase Deep-Scan — April 2, 2026

Additional orphaned files found and removed during deep-scan reverse-pass:

| Item                                                          | Missed In | Reason                                            | Action  |
| ------------------------------------------------------------- | --------- | ------------------------------------------------- | ------- |
| `resources/views/payments/authorize/index.blade.php`          | Phase 1   | Payment gateway views not tracked                 | Deleted |
| `resources/views/payments/paytm/index.blade.php`              | Phase 1   | Payment gateway views not tracked                 | Deleted |
| `resources/views/livewire/transaction_skeleton.blade.php`     | Phase 1   | Skeleton for deleted Transaction Livewire table   | Deleted |
| `resources/views/holiday/` (8 files)                          | Phase 2   | DoctorHoliday views not in original delete list   | Deleted |
| `resources/views/livewire/appointment_skeleton.blade.php`     | Phase 2   | Skeleton for deleted Appointment Livewire table   | Deleted |
| `resources/views/livewire/doctor_schedule_skeleton.blade.php` | Phase 2   | Skeleton for deleted DoctorSession Livewire table | Deleted |

**Note — `doctor_holiday_skeleton.blade.php`:** This file was found to be actively used as the `placeholder()` skeleton by `PatientTable.php`. It is misleadingly named but KEPT (not broken). Will be renamed in Phase 6 navigation cleanup.

**Note — Remaining appointment stubs (Phase 6 scope):** `DashboardController::getDoctorAppointment()`, dashboard views appointment chart sections, and `fronts/medical_appointment.blade.php` with its front route still exist but are either guarded behind empty-data checks or are front CMS pages. These are scoped to Phase 6 (Navigation & dashboard cleanup) and are NOT breaking anything.

### Gate 4 Final Validation Results

| Check                                                                                                   | Result                                 |
| ------------------------------------------------------------------------------------------------------- | -------------------------------------- |
| `php artisan view:cache`                                                                                | ✅ Blade templates cached successfully |
| `php artisan config:cache`                                                                              | ✅ Configuration cached successfully   |
| Scan: `SmartCard`, `QrCode`, `smart_patient_cards_skeleton`, `generate_patient_smart_cards` in `app/**` | ✅ Zero matches                        |
| Scan: smart card views in `resources/views/**`                                                          | ✅ Zero matches                        |
| Scan: QrCode in `config/app.php`                                                                        | ✅ Zero matches                        |
| `simplesoftwareio/simple-qrcode` in `composer.json`                                                     | ✅ Removed                             |
| Cross-scan: orphaned payment/holiday/skeleton views                                                     | ✅ All deleted                         |

---

## PHASE 5 — WebSockets (Complete ✅)

All WebSocket infrastructure removed. Broadcast driver already set to `null`. `BeyondCode/laravel-websockets` and `pusher/pusher-php-server` composer packages removed.

---

## PHASE 6 — Navigation & Dashboard Cleanup (Complete ✅)

### PHP Livewire Components Cleaned

| Component                          | Change                                                                         |
| ---------------------------------- | ------------------------------------------------------------------------------ |
| `Dashboard.php`                    | Removed `$todayAppointmentCount` prop + mount assignment                       |
| `AdminDashboardSidebarTable.php`   | Removed `$upcomingAppointmentCount`, `$totalAppointmentCount` + entire mount() |
| `DoctorDashboardTable.php`         | Removed 3 appointment count props + assignments from `loadStatistics()`        |
| `DoctorDashboardSidebarTable.php`  | Removed all 3 appointment props + entire mount()                               |
| `PatientDashboardSidebarTable.php` | Removed all 6 appointment props + entire mount()                               |
| `StaffDashboard.php`               | Removed `$todayAppointmentCount` + assignment                                  |
| `StaffDashboardSidebarTable.php`   | Removed `$upcomingAppointmentCount`, `$totalAppointmentCount` + entire mount() |

### Blade Views Cleaned

| View                                        | Change                                                                            |
| ------------------------------------------- | --------------------------------------------------------------------------------- |
| `admin-dashboard-sidebar-table.blade.php`   | Replaced with `<div></div>`                                                       |
| `staff-dashboard-sidebar-table.blade.php`   | Replaced with `<div></div>`                                                       |
| `doctor-dashboard-sidebar-table.blade.php`  | Replaced with `<div></div>`                                                       |
| `patient-dashboard-sidebar-table.blade.php` | Replaced with `<div></div>`                                                       |
| `staff-dashboard.blade.php`                 | Removed "today appointments" stat card                                            |
| `admin-dash-board-table.blade.php`          | Removed "Total Appointments" `<th>` + `<td>` from all 3 tabs, fixed `colspan` 5→3 |
| `staff-dash-board-table.blade.php`          | Same as admin version                                                             |
| `doctor-dashboard-table.blade.php`          | Removed entire `@if($totalAppointmentCount > 0)` block with 3 stat cards          |

### Menu / SubMenu Cleaned

| File                                      | Change                                                                                                                                       |
| ----------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------- |
| `menu.blade.php` (Doctors `<li>`)         | Removed `admin/doctor-sessions*`, `staff/doctor-sessions*`, `admin/holiday*`, `staff/holiday*`, `doctors/doctor-sessions*` from active check |
| `menu.blade.php` (Settings `<li>`)        | Removed `admin/clinic-schedules*`, `staff/clinic-schedules*` from active check                                                               |
| `sub_menu.blade.php` (Doctors visibility) | Removed `doctor-sessions*` and `holidays*` from show/hide check                                                                              |
| `sub_menu.blade.php` (Settings sub-items) | Removed `admin/clinic-schedules*` from all 5 visibility checks                                                                               |

### Controller Fixed

| File                                          | Change                                                                                                       |
| --------------------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| `DashboardController::getDoctorAppointment()` | Returns `['patients' => []]` directly — no longer calls non-existent `doctorAppointment()` repository method |

---

## PHASES 5–6 CROSS-PHASE DEEP-SCAN AUDIT — April 2, 2026

Additional breaking issues found and fixed during thorough cross-phase re-scan:

| Issue                                                                                                                                                                                                          | Severity                                                  | File                                                       | Fix Applied                                                                                |
| -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------- | ---------------------------------------------------------- | ------------------------------------------------------------------------------------------ |
| `$hasAppointmentData` undefined variable in patient dashboard view — controller never passed it                                                                                                                | **BREAKING** (PHP E_WARNING → exception)                  | `resources/views/patient_dashboard/index.blade.php`        | Removed the entire `@if($hasAppointmentData)…@endif` guard block                           |
| `route('front.appointment.book')` called in front booking view — route deleted in Phase 2                                                                                                                      | **BREAKING** (RouteNotFoundException at render)           | `resources/views/fronts/medical_appointment.blade.php`     | Replaced form section with "unavailable" notice                                            |
| `getAllPaymentStatus()` called in `book_appointment.blade.php` — helper deleted in Phase 1                                                                                                                     | **BREAKING** (call to undefined function)                 | `resources/views/fronts/common/book_appointment.blade.php` | Deleted entire file (only included by medical_appointment)                                 |
| `#adminDashboardTemplate` JSRender had 4 `<td>` columns but Blade `<th>` count was 3                                                                                                                           | **Visual bug** (column mismatch on day/week/month filter) | `resources/views/dashboard/templates/templates.php`        | Removed `{{:appointment_count}}` `<td>` block                                              |
| `dashboard.js` AJAX renders with `appointment_count` property + `colspan="5"` in 3 empty rows                                                                                                                  | **Visual bug** (extra empty cell + wrong empty colspan)   | `resources/assets/js/dashboard/dashboard.js`               | Removed `appointment_count` from 3 data objects, changed `colspan="5"` → `"3"` in 3 places |
| `PatientAppointmentBookMail`, `AppointmentBookedMail`, `DoctorAppointmentBookMail` — mail classes referencing deleted controllers                                                                              | Dead code                                                 | `app/Mail/*.php`                                           | Deleted all 3 mail classes                                                                 |
| `emails/patient_appointment_booked_mail.blade.php`, `appointment_booked_mail.blade.php`, `doctor_appointment_booked_mail.blade.php` — email views referencing `route('cancelAppointment')` which doesn't exist | Dead code (render-time failure if ever sent)              | `resources/views/emails/`                                  | Deleted all 3 email views                                                                  |
| `sub_menu.blade.php` — 5 Settings sub-menu `Request::is()` checks still included `'admin/clinic-schedules*'`                                                                                                   | Dead code                                                 | `resources/views/layouts/sub_menu.blade.php`               | Removed from all 5 occurrences                                                             |

### Items Verified as Non-Breaking (intentionally kept)

| Item                                                                                      | Reason Kept                                                                  |
| ----------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------- |
| `resources/assets/js/appointments/` (5 JS files)                                          | Files still exist on disk; webpack builds without error; dead JS is harmless |
| `resources/assets/js/doctor_sessions/` (2 JS files)                                       | Same — files exist, webpack doesn't error                                    |
| `webpack.mix.js` — entries for appointment/doctor_session JS                              | Files referenced still exist; no build error; Phase 7 scope                  |
| `resources/views/doctors/templates/templates.php` — `sessionTemplateData` JSRender script | Script ID not referenced in any active JS; dead but harmless                 |
| `app/Models/Notification.php` — `APPOINTMENT_*` constants                                 | Class-level constants; never instantiated in removed flows; harmless         |
| `route('medicalAppointment')` in `web.php` + `FrontController::medicalAppointment()`      | Page now shows "unavailable" notice; route is live but safe                  |

### Gate 6 Final Validation Results

| Check                                          | Result                                 |
| ---------------------------------------------- | -------------------------------------- |
| `composer dump-autoload`                       | ✅ 9351 classes, no errors             |
| `php artisan route:list`                       | ✅ No errors, no undefined controllers |
| `php artisan config:cache`                     | ✅ Configuration cached successfully   |
| `php artisan view:cache`                       | ✅ Blade templates cached successfully |
| `php artisan optimize:clear`                   | ✅ Clean                               |
| Scan: `route('cancelAppointment')` in views    | ✅ Zero matches (email views deleted)  |
| Scan: `route('front.appointment.book')`        | ✅ Zero matches                        |
| Scan: `getAllPaymentStatus()`                  | ✅ Zero matches                        |
| Scan: `$hasAppointmentData` undefined variable | ✅ Guard block removed                 |
| Scan: `appointment_count` in JSRender template | ✅ Removed                             |

---

## PHASE 7 — Database Schema Cleanup (Complete ✅)

**Migration file:** `database/migrations/2026_04_02_000000_drop_removed_features_tables.php`

### Tables Dropped (15 total)

| Order | Table                 | Phase   |
| ----- | --------------------- | ------- |
| 1     | `visit_problems`      | Phase 3 |
| 2     | `visit_observations`  | Phase 3 |
| 3     | `visit_notes`         | Phase 3 |
| 4     | `visit_prescriptions` | Phase 3 |
| 5     | `visits`              | Phase 3 |
| 6     | `appointments`        | Phase 2 |
| 7     | `service_doctor`      | Phase 2 |
| 8     | `services`            | Phase 2 |
| 9     | `service_categories`  | Phase 2 |
| 10    | `session_week_days`   | Phase 2 |
| 11    | `doctor_sessions`     | Phase 2 |
| 12    | `clinic_schedules`    | Phase 2 |
| 13    | `transactions`        | Phase 1 |
| 14    | `payment_gateways`    | Phase 1 |
| 15    | `currencies`          | Phase 1 |
| 16    | `holidays`            | Phase 2 |

> Note: `appointments` had FK `appointments_service_id_foreign → services`, so it was dropped before `services`.

### Column Cleanup

| Table           | Column                                         | Index                           | Result          |
| --------------- | ---------------------------------------------- | ------------------------------- | --------------- |
| `prescriptions` | `appointment_id` (unsignedBigInteger nullable) | `idx_prescriptions_appointment` | ✅ Both dropped |

### Gate 7 Final Validation Results

| Check                                                   | Result                                 |
| ------------------------------------------------------- | -------------------------------------- |
| `php artisan migrate`                                   | ✅ DONE in 182ms                       |
| `php artisan db:show` — all 15 tables                   | ✅ Zero matches (all gone)             |
| `php artisan db:table prescriptions` — `appointment_id` | ✅ Column and index gone               |
| `composer dump-autoload`                                | ✅ 9351 classes, no errors             |
| `php artisan route:list`                                | ✅ No errors                           |
| `php artisan config:cache`                              | ✅ Clean                               |
| `php artisan view:cache`                                | ✅ Blade templates cached successfully |
| `php artisan optimize:clear`                            | ✅ Clean                               |

---

## PHASE 7 EXTENSION — Database Cleanup (Seeders, Factories, Migrations)

### Seeders Deleted (5 files)

| File                                 | Reason                                               |
| ------------------------------------ | ---------------------------------------------------- |
| `DefaultHolidayPermissionSeeder.php` | Seeded `manage_doctors_holiday` permission (removed) |
| `DefaultClinicSchedulesSeeder.php`   | Was already a stub no-op                             |
| `DefaultServicesSeeder.php`          | Was already a stub no-op                             |
| `DefaultServiceCategorySeeder.php`   | Was already a stub no-op                             |
| `StaffDoctorPermissionSeeder.php`    | Assigned removed permissions                         |

### Factories Deleted (6 files)

| File                         | Reason                           |
| ---------------------------- | -------------------------------- |
| `AppointmentFactory.php`     | Model deleted                    |
| `DoctorSessionFactory.php`   | Model deleted                    |
| `ServicesFactory.php`        | Model deleted                    |
| `ServiceCategoryFactory.php` | Model deleted                    |
| `CurrencyFactory.php`        | Model deleted                    |
| `EncounterFactory.php`       | Referenced Visit model (deleted) |

### Dead Migrations Deleted (28 files)

15 `CREATE TABLE` migrations for dropped tables, 9 `ALTER TABLE` migrations modifying dropped tables, `create_holidays_table`, `run_holiday_seeder` (no-op), and 2 appointment-only performance index migrations.

### Performance Index Migrations Edited (2 files)

| File                                                                | Change                                                  |
| ------------------------------------------------------------------- | ------------------------------------------------------- |
| `2025_10_01_000001_add_performance_indexes.php`                     | Removed appointments/services/transactions index blocks |
| `2025_10_02_173840_add_additional_performance_indexes_oct_2025.php` | Removed visits index block                              |

### Phase 7 Drop Migration Updated

- Added `Schema::dropIfExists('holidays')` — table confirmed absent from DB; now 16 tables total

---

## FULL PHASES 1–7 DEEP-SCAN AUDIT — April 2, 2026

Comprehensive cross-phase deep-scan across ALL code: controllers, models, Livewire, helpers, configs, views, routes, JS, webpack, seeders, migrations, language files.

### BREAKING Issues Found & Fixed

| Issue                                                                                            | Severity  | File                                                  | Fix Applied                                                                  |
| ------------------------------------------------------------------------------------------------ | --------- | ----------------------------------------------------- | ---------------------------------------------------------------------------- |
| `{{ route('front.home.appointment.book') }}` inside HTML comment — Blade processes it at runtime | **CRASH** | `resources/views/fronts/medicals/index.blade.php`     | Removed entire appointment + services section (replaced with Blade comments) |
| `{{ route('serviceBookAppointment') }}` inside HTML comment                                      | **CRASH** | `resources/views/fronts/medical_services.blade.php`   | Removed commented link block                                                 |
| `{{ route('doctorBookAppointment') }}` inside HTML comment                                       | **CRASH** | `resources/views/fronts/medical_doctors.blade.php`    | Removed commented link block                                                 |
| `{{ route('doctorBookAppointment') }}` inside HTML comment                                       | **CRASH** | `resources/views/fronts/medical_about_us.blade.php`   | Removed commented link block                                                 |
| `{{ route('medicalAppointment') }}` inside HTML comment in header                                | **CRASH** | `resources/views/fronts/layouts/header.blade.php`     | Removed commented link                                                       |
| `WeekDay` model references dropped `session_week_days` table                                     | **CRASH** | `app/Models/WeekDay.php`                              | Deleted model (unused by any code)                                           |
| `PerformanceMonitor` queries dropped `appointments` table                                        | **CRASH** | `app/Console/Commands/PerformanceMonitor.php`         | Replaced with `patients` table query                                         |
| `Stripe()` JS init references removed payment service                                            | **DEAD**  | `resources/views/layouts/app.blade.php`               | Removed Stripe JS initialization block                                       |
| Hidden `appointment_id` field — column dropped from prescriptions                                | **DEAD**  | `resources/views/prescriptions/fields.blade.php`      | Removed hidden field                                                         |
| Hidden `appointment_id` field — column dropped from prescriptions                                | **DEAD**  | `resources/views/prescriptions/edit_fields.blade.php` | Removed hidden field                                                         |

### Dead Code Removed

| Issue                                                      | File                                                                | Fix Applied                                   |
| ---------------------------------------------------------- | ------------------------------------------------------------------- | --------------------------------------------- |
| Stripe + PayTM config blocks (packages removed in Phase 1) | `config/services.php`                                               | Removed both config arrays                    |
| 6 `APPOINTMENT_*_MSG` notification constants               | `app/Models/Notification.php`                                       | Removed constants                             |
| Stub appointment/visit stats in `getPatientStatistics()`   | `app/Services/PatientService.php`                                   | Removed 6 dead keys (kept prescriptions only) |
| `manage_doctors_holiday` permission in roles form          | `resources/views/roles/fields.blade.php`                            | Removed from permission groups                |
| Commented doctor session + holiday menu items              | `resources/views/layouts/menu.blade.php`                            | Removed Blade comment blocks                  |
| `getDoctorAppointment()` dead controller method            | `app/Http/Controllers/DashboardController.php`                      | Removed method                                |
| `medicalAppointment()` controller method + route           | `app/Http/Controllers/Front/FrontController.php` + `routes/web.php` | Removed method + route                        |
| `appointment.dashboard` dead route                         | `routes/doctor.php`                                                 | Removed route                                 |
| `DoctorDashboardDataTable` orphan Livewire component       | `app/Livewire/DoctorDashboardDataTable.php`                         | Deleted file                                  |
| `doctor-dashboard-data-table.blade.php` orphan view        | `resources/views/livewire/`                                         | Deleted file                                  |
| `appointment_filter.blade.php` orphan doctor view          | `resources/views/doctors/`                                          | Deleted file                                  |
| `medical_appointment.blade.php` orphan front view          | `resources/views/fronts/`                                           | Deleted file                                  |

### Webpack Bundle Cleanup (22 dead JS entries removed)

Entries removed from `pages.js` bundle:

- `doctor-patient-appointment.js`, `doctor_sessions/*.js` (2), `service_categories.js`
- `services/*.js` (2), `appointments/*.js` (5), `doctor-dashboard.js`
- `doctor_appointments/*.js` (2), `visits/*.js` (4), `clinic_schedule/create-edit.js`
- `fronts/appointments/book_appointment.js`, `patient_visits/patient-visit.js`
- `transactions/*.js` (2), `doctor_holiday/*.js` (3)

Entry removed from `front-pages.js` bundle:

- `fronts/appointments/book_appointment.js`

### Gate Deep-Scan Validation Results

| Check                                                                      | Result                                                           |
| -------------------------------------------------------------------------- | ---------------------------------------------------------------- |
| `composer dump-autoload`                                                   | ✅ 9338 classes, no errors                                       |
| `php artisan route:list --name=appointment`                                | ✅ No routes matching                                            |
| `php artisan route:list` scan for visit/transaction/holiday/doctor_session | ✅ Zero matches (only `medicalServices` front CMS route remains) |
| `php artisan config:cache`                                                 | ✅ Configuration cached successfully                             |
| `php artisan view:cache`                                                   | ✅ Blade templates cached successfully                           |
| Cache clear (all caches)                                                   | ✅ Clean                                                         |

### Items Verified as Non-Breaking (intentionally kept)

| Item                                                                                            | Reason Kept                                                                                            |
| ----------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| `Notification::BOOKED/CHECKOUT/CANCELED/PAYMENT_DONE/REVIEW/LIVE_CONSULTATION` constants        | Used by `getNotificationIcon()` in helpers.php for existing notification records                       |
| `getCurrencyCode()`, `getCurrencyFormat()`, `getCurrentCurrency()`, `getCurrencyIcon()` helpers | Actively used by 20+ medicine/billing views — return hardcoded '₱' values                              |
| `Medicine.currency_symbol` fillable/casts property                                              | Column exists in DB; used by `MedicineController` — not related to deleted Currency model              |
| `resources/assets/js/appointments/` directory (5 JS files)                                      | Files exist on disk; removed from webpack bundles so no longer compiled/loaded                         |
| `resources/assets/js/visits/` directory (4 JS files)                                            | Same — on disk but not compiled                                                                        |
| `resources/assets/js/doctor_sessions/` directory (2 JS files)                                   | Same — on disk but not compiled                                                                        |
| `resources/assets/js/transactions/` directory (2 JS files)                                      | Same — on disk but not compiled                                                                        |
| `resources/assets/js/doctor_holiday/` directory (3 JS files)                                    | Same — on disk but not compiled                                                                        |
| `lang/en/messages.php` — 40+ translation keys for removed features                              | Harmless dead strings; no runtime impact                                                               |
| `lang/en/js.php` — 8 translation keys for removed features                                      | Same                                                                                                   |
| `resources/views/doctors/templates/templates.php` — JSRender template                           | Dead but harmless (script ID not referenced)                                                           |
| `database/seeders/DefaultStaffSeeder.php` — commented `manage_currencies`                       | Just a comment                                                                                         |
| `config/broadcasting.php` — pusher/websockets comments                                          | Documentation strings only; driver is `null`                                                           |
| `resources/views/fronts/medicals/index.blade.php` — how-it-work/about sections in HTML comments | Only contain existing routes/functions like `route('register')`, `route('medicalContact')` — all exist |

---

## PERFORMANCE IMPROVEMENTS TRACKER

### Completed Performance Fixes

| Fix                                                              | Category              | Details                                                                                                                                                                                                                                                                                                                                                         |
| ---------------------------------------------------------------- | --------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Added `placeholder()` to `LivewireTableComponent` base class** | HIGH — Livewire       | Returns `loading_skeleton` view; all child components now inherit default placeholder                                                                                                                                                                                                                                                                           |
| **Added `placeholder()` to `RequestDocumentTable`**              | HIGH — Livewire       | Was missing completely; now returns `loading_skeleton` for lazy loading                                                                                                                                                                                                                                                                                         |
| **Fixed `PatientTable` wrong placeholder**                       | HIGH — Livewire       | Changed from `doctor_holiday_skeleton` (copy-paste bug) to `staff_skeleton`                                                                                                                                                                                                                                                                                     |
| **Fixed `RequestDocumentTable` N+1 query**                       | HIGH — Performance    | Removed per-row `RequestDocuments::find($row->id)` in Completion Status column; now uses `$row->assessment`/`$row->plan` directly from existing query                                                                                                                                                                                                           |
| **Optimized 4 dashboard queries**                                | MEDIUM — Performance  | Replaced `whereRaw('Date(created_at) = CURDATE()')` with `whereDate('created_at', today())` in: `Dashboard`, `StaffDashboard`, `AdminDashBoardTable`, `StaffDashBoardTable`                                                                                                                                                                                     |
| **Removed unused imports**                                       | LOW — Code quality    | Cleaned `Dashboard.php` (Setting, DashboardRepository, Request), `StaffDashboard.php` (Carbon), `AdminDashBoardTable.php` (Carbon, Request), `StaffDashBoardTable.php` (Request)                                                                                                                                                                                |
| **Cleaned dead commented code**                                  | LOW — Code quality    | Removed 15 lines of dead commented `mountWithPagination()` and `resetPage()` from `LivewireTableComponent`                                                                                                                                                                                                                                                      |
| **Deleted 12 dead JS directories**                               | MEDIUM — Disk space   | Removed: `appointments/`, `visits/`, `doctor_sessions/`, `doctor_appointments/`, `transactions/`, `patient_visits/`, `doctor_holiday/`, `clinic_schedule/`, `service_categories/`, `services/`, `fronts/appointments/`, `fronts/faqs/`                                                                                                                          |
| **Deleted dead JSRender template**                               | LOW — Disk space      | Removed `sessionTemplateData` script from `doctors/templates/templates.php`                                                                                                                                                                                                                                                                                     |
| **Deleted empty `fronts/common/` directory**                     | LOW — Disk space      | Empty directory from removed features                                                                                                                                                                                                                                                                                                                           |
| **Removed 100+ dead translation keys**                           | MEDIUM — Payload size | Cleaned `messages.php`: removed entire `doctor_session`, `service_category`, `holiday`, `appointment`, `doctor_appointment`, `patient_dashboard`, `visit`, `service` blocks + 30+ individual dead keys (payment cards, appointments, scheduling, earnings). Cleaned `js.php`: removed 15+ dead keys (appointment, visit, service, payment, holiday, smart card) |
| **Cleaned dead flash message keys**                              | LOW — Payload size    | Removed: `schedule_*`, `clinic_schedule_*`, `doctor_session_*`, `appointment_*`, `visit_*`, `service_*`, `live_consultation_*`, `review_*`, `observation_*`, `problem_*`, `note_*` flash keys                                                                                                                                                                   |
| **Cleaned dead .env variables**                                  | LOW — Config hygiene  | Removed from `.env` and `.env.example`: `STRIPE_*`, `PAYPAL_*`, `AUTHORIZE_*`, `PAYTM_*`, `AWS_*`, `PUSHER_*`, `MIX_PUSHER_*` (all external services not needed for LAN deployment)                                                                                                                                                                             |
| **Removed dead menu search text**                                | LOW — Code quality    | Removed hidden `d-none` spans with dead `doctor_sessions` text from `menu.blade.php`                                                                                                                                                                                                                                                                            |

### Remaining Low-Priority Items

| Issue                       | Severity | Details                                                                                                              |
| --------------------------- | -------- | -------------------------------------------------------------------------------------------------------------------- |
| **Large JS bundle size**    | MEDIUM   | `pages.js` still contains ALL page JS in one file — loaded on every page. Consider per-feature splitting             |
| **No HTTP caching headers** | MEDIUM   | `mix.version()` IS configured but no explicit `Cache-Control` headers for static assets (Apache/Nginx config needed) |

### Gate Validation Results (Post-Performance Fixes)

- `config:cache` — Configuration cached successfully
- `view:cache` — Blade templates cached successfully
- `route:list` — 450 routes resolved (no errors)
- `composer dump-autoload -o` — 9338 classes loaded (no duplicates)

### LAN/Localhost Deployment Verification

| Category           | Status    | Details                                                    |
| ------------------ | --------- | ---------------------------------------------------------- |
| External CDN Links | CLEAN     | All CSS/JS/Fonts served locally via `asset()` helper       |
| Payment Gateways   | REMOVED   | All Stripe, PayPal, Authorize.net, PayTM configs removed   |
| Cloud Storage      | DISABLED  | S3 config exists in framework but not active (using local) |
| Mail Service       | LAN READY | Using MailHog on `localhost:1025`                          |
| Cache/Session      | LOCAL     | File-based caching and sessions                            |
| Logging            | LOCAL     | File-based logs to `storage/logs/laravel.log`              |
| Asset Versioning   | ENABLED   | `mix.version()` configured for cache busting               |
| Broadcasting       | DISABLED  | Driver set to `log` (no Pusher/WebSocket needed)           |

---

_Generated by Phase 0 — April 2, 2026_
_Updated Phase 4 complete + full Phase 1–4 deep-scan audit — April 2, 2026_
_Updated Phase 7 complete + full Phases 1–7 deep-scan audit — April 2, 2026_
_Updated Performance Improvements — all fixes applied — April 2, 2026_
