# NORSUCLINIC — Master Cleanup Tracker

**Project:** NORSUCLINIC Laravel 10 Medical Clinic System  
**Scanned:** April 2, 2026  
**Status:** Phase 2 Complete ✅ — Appointments, Services, DoctorSessions, ClinicSchedule, DoctorHoliday removed. Prescriptions decoupled.

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
| Visits / Encounters                                 | [CLEANUP_VISITS.md](CLEANUP_VISITS.md)             | Phase 3   | ⬜ Not started                                               |
| Patient Smart Cards / QR Codes                      | [CLEANUP_SMART_CARDS.md](CLEANUP_SMART_CARDS.md)   | Phase 4   | ⬜ Not started                                               |
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

| Permission               | Seeder                      | Affected Roles                            | Status               |
| ------------------------ | --------------------------- | ----------------------------------------- | -------------------- |
| `manage_appointments`    | DefaultPermissionSeeder L29 | doctor, staff, patient                    | ✅ Removed (Phase 2) |
| `manage_patient_visits`  | DefaultPermissionSeeder L33 | doctor, staff, patient                    | ⬜ Phase 3           |
| `manage_doctor_sessions` | DefaultPermissionSeeder L41 | doctor, staff                             | ✅ Removed (Phase 2) |
| `manage_services`        | DefaultPermissionSeeder L49 | doctor, staff, admin                      | ✅ Removed (Phase 2) |
| `manage_specialties`     | DefaultPermissionSeeder L53 | admin (keep if still use specializations) | ⬜ Phase 2           |
| `manage_transactions`    | DefaultPermissionSeeder L85 | doctor, staff, patient                    | ✅ Removed (Phase 1) |

> ⚠️ **Note:** `manage_specialties` may need review — `Specialization` model is kept for doctor profiles. Confirm before removing this permission.

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

### ⬜ Remaining — Phase 5 (WebSockets) & Phase 4 (QR)

```json
"beyondcode/laravel-websockets": "^1.13",
"pusher/pusher-php-server": "^7.2",
"simplesoftwareio/simple-qrcode": "^4.2"
```

> Run after all references removed: `composer remove beyondcode/laravel-websockets pusher/pusher-php-server simplesoftwareio/simple-qrcode`

---

## EXECUTION PHASES

| Phase          | Description                                    | Tracker File            | Dependencies              |
| -------------- | ---------------------------------------------- | ----------------------- | ------------------------- |
| **Phase 0**    | Create tracker `.md` files                     | This file               | None                      |
| **Phase 1** ✅ | Remove payments & transactions                 | CLEANUP_PAYMENTS.md     | None                      |
| ✅ Gate 1      | Validate before Phase 2                        | See below               | Phase 1 done ✅           |
| **Phase 2** ✅ | Remove appointments, services, doctor sessions | CLEANUP_APPOINTMENTS.md | After Phase 1             |
| ✅ Gate 2      | Validate before Phase 3                        | See below               | Phase 2 done ✅           |
| **Phase 3**    | Remove visits                                  | CLEANUP_VISITS.md       | Independent               |
| ✅ Gate 3      | Validate before Phase 4                        | See below               | Phase 3 done              |
| **Phase 4**    | Remove smart cards                             | CLEANUP_SMART_CARDS.md  | ⚠️ Rename skeleton first! |
| ✅ Gate 4      | Validate before Phase 5                        | See below               | Phase 4 done              |
| **Phase 5**    | Remove WebSockets                              | CLEANUP_UNUSED.md       | Independent               |
| ✅ Gate 5      | Validate before Phase 6                        | See below               | Phase 5 done              |
| **Phase 6**    | Navigation & dashboard cleanup                 | CLEANUP_NAVIGATION.md   | After Phases 1–5          |
| ✅ Gate 6      | Validate before Phase 7                        | See below               | Phase 6 done              |
| **Phase 7**    | Database cleanup migrations                    | CLEANUP_DATABASE.md     | After Phase 6             |
| ✅ Gate 7      | Validate before Phase 8                        | See below               | Phase 7 done              |
| **Phase 8**    | Final verification                             | —                       | After all phases          |

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

_Generated by Phase 0 — April 2, 2026_
