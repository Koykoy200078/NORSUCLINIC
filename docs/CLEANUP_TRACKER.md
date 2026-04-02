# NORSUCLINIC — Master Cleanup Tracker

**Project:** NORSUCLINIC Laravel 10 Medical Clinic System  
**Scanned:** April 2, 2026  
**Status:** Phase 4 Complete ✅ — Smart Cards / QR Codes removed. Deep-scan performed across all Phases 1–4: additional orphaned views and skeletons found and deleted. All gates passed.

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

| Feature                                             | Tracker File                                       | PHP Phase | Status                                                       |
| --------------------------------------------------- | -------------------------------------------------- | --------- | ------------------------------------------------------------ |
| Payment Gateways (PayPal, Stripe, PayTM, Authorize) | [CLEANUP_PAYMENTS.md](CLEANUP_PAYMENTS.md)         | Phase 1   | ✅ Complete                                                  |
| Transactions                                        | [CLEANUP_PAYMENTS.md](CLEANUP_PAYMENTS.md)         | Phase 1   | ✅ Complete                                                  |
| Appointments / Bookings                             | [CLEANUP_APPOINTMENTS.md](CLEANUP_APPOINTMENTS.md) | Phase 2   | ✅ Complete                                                  |
| Services & Service Categories                       | [CLEANUP_APPOINTMENTS.md](CLEANUP_APPOINTMENTS.md) | Phase 2   | ✅ Complete                                                  |
| DoctorSessions & ClinicSchedule                     | [CLEANUP_APPOINTMENTS.md](CLEANUP_APPOINTMENTS.md) | Phase 2   | ✅ Complete                                                  |
| Visits / Encounters                                 | [CLEANUP_VISITS.md](CLEANUP_VISITS.md)             | Phase 3   | ✅ Complete                                                  |
| Patient Smart Cards / QR Codes                      | [CLEANUP_SMART_CARDS.md](CLEANUP_SMART_CARDS.md)   | Phase 4   | ✅ Complete                                                  |
| WebSockets                                          | [CLEANUP_UNUSED.md](CLEANUP_UNUSED.md)             | Phase 5   | ⬜ Not started                                               |
| Navigation cleanup                                  | [CLEANUP_NAVIGATION.md](CLEANUP_NAVIGATION.md)     | Phase 6   | 🔄 Partial — Transaction nav items already removed (Phase 1) |
| Database cleanup migrations                         | [CLEANUP_DATABASE.md](CLEANUP_DATABASE.md)         | Phase 7   | ⬜ Not started                                               |

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
| **Phase 7**    | Database cleanup migrations                    | CLEANUP_DATABASE.md     | After Phase 6    |
| ✅ Gate 7      | Validate before Phase 8                        | See below               | Phase 7 done     |
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

_Generated by Phase 0 — April 2, 2026_
_Updated Phase 4 complete + full Phase 1–4 deep-scan audit — April 2, 2026_
