# NORSUCLINIC Full Workspace Re-Audit — 2026-09-30

| | |
|---|---|
| Branch / commit audited | `changes_v2` @ `683bd5f` |
| Supersedes (as the current status record) | `audit-2026-04-29.md`, `audit-staff.md`, `module-crud-audit-2026-05-05.md`, `staff-designation-station-access-audit-2026-05-05.md`, `automation-deep-audit-2026-05-18.md` (those files are kept for history; §3 records what still holds) |
| Focus | hidden bugs, data loss / data corruption, broken or dead code, incorrect configuration, security, prior-audit regressions |
| Method | inline code reading of every controller, repository, service, middleware, model, route file, migration and the key views/JS, plus live reproduction (see §1) |

---

## 0. How to read this report

Every finding has an **ID**, a **severity**, a **location** (`file:line`) and an **evidence tag**:

- **LIVE** — reproduced end-to-end against a running copy of this commit (MariaDB 10.11 + PHP built-in server, throwaway accounts in an isolated audit database). The observed result is quoted.
- **TOOL** — produced by a tool run on this commit (`composer audit`, `npm audit`, `phpunit`, `route:list`, `migrate:fresh`).
- **CODE** — verified by tracing the code path line by line; not executed.

Severity scale:

| Severity | Meaning |
|---|---|
| **Critical** | Remote code execution, unauthenticated access to patient data, or silent destruction/corruption of clinical or inventory records in normal use. Fix before the next deployment. |
| **High** | Privilege escalation / access-control bypass, data corruption that needs an unusual-but-plausible action, or a broken safety net (backups, tests). |
| **Medium** | Incorrect results, broken features, performance traps, configuration hazards. |
| **Low** | Dead code, cosmetic defects, hygiene. |

---

## 1. Scope and verification environment

**Code inventory audited:** 237 PHP files in `app/` (24,993 lines), 395 Blade views (31,668 lines), 45 JS files in `resources/assets/js`, 108 migrations, 35 seeders, 10 route files (507 registered routes), config, root scripts, and the git history.

**Live verification setup (sandbox only, nothing pushed):**

1. `composer install` from `composer.lock` → **fails on PHP 8.4** (`nette/schema 1.3.0`, `nette/utils 4.0.4` require PHP ≤ 8.3). Installed with `--ignore-platform-req=php` for the audit only (see M-16).
2. MariaDB 10.11 → `php artisan migrate:fresh --force` (**108 migrations, PASS**) and `php artisan db:seed --force` (**PASS**).
3. PHP built-in server (same mechanism as `php artisan serve`, which the clinic's startup script uses), `APP_ENV=local`.
4. Front-end assets were not compiled; empty placeholder files were created in the git-ignored `public/js|css|assets` folders so that pages render. All 34 main admin pages then returned HTTP 200.
5. `vendor/bin/phpunit`, `composer audit --locked`, `npm audit --package-lock-only`, `php artisan route:list --json`, `php artisan route:cache`.
6. The temporary `.env`, placeholder assets, upload probe and audit database were removed/kept out of git after the audit.

---

## 2. Executive summary

| Severity | Count |
|---|---|
| Critical | 6 |
| High | 15 |
| Medium | 18 |
| Low | 14 |

**The ten things to fix first**

1. **C-01** — Consultation image upload accepts any file into `public/uploads` → uploaded `.php` **executed on the server** (LIVE: `AUDIT-PROBE-42`). Also exposes clinical photos to anyone without login.
2. **C-02** — `livewire/livewire v3.5.4` is affected by **CVE-2025-54068 (unauthenticated RCE)**; plus 97 other advisories in the lock file.
3. **C-03** — Saving a consultation with a blank allergy field **erases the patient's recorded allergies, comorbidities, maintenance meds and surgical history** (LIVE: `PENICILLIN (anaphylaxis)` → `NULL`).
4. **C-04** — Deleting a medicine that is in use **cascades away its batches, the whole stock ledger, prescription lines and consultation medicine records**; the "in use" check reports "not used" (LIVE).
5. **C-05** — `/log-viewer` (opcodesio) is reachable **without login**: list, read, download and delete application logs, which contain patient data (LIVE: HTTP 200).
6. **C-06** — The `xss` middleware HTML-encodes/strips all staff/doctor/patient input: `Allergy: penicillin <severe>` is stored as `Allergy: penicillin `, `BP > 140/90` as `BP &gt; 140/90`, and **passwords containing `& < >` are changed before hashing → user locked out** (LIVE).
7. **H-01** — A patient can mass-assign their own `users.type` through the change-password endpoint and then **list every patient's lab requests** (LIVE).
8. **H-02** — Staff designation/station restrictions on consultations are **bypassed with `?module=certificates` or `document_type=medical_certificate`** (LIVE: front-desk staff downloaded and edited a consultation).
9. **H-04 / H-05** — Stock-in edit/delete updates the paperwork but not the batch ledger, or deducts from the wrong batch → **expired stock is shown as valid until 2028** (LIVE).
10. **H-09** — Scheduled hourly backups **never run** (no scheduler is wired; the `automation.ps1` described in the previous audit does not exist), and the manual backup/restore has consistency gaps.

---

## 3. Status of the previous audits

| Prior claim (source) | Status on `683bd5f` | Evidence |
|---|---|---|
| Sidebar `@can('manage_patients')` wraps orders 2–3 (04-29) | ✅ Holds | `resources/views/layouts/menu.blade.php:85` |
| GET `/login` only in web routes; POST in auth routes (04-29) | ✅ Holds | route list |
| Duplicate `patient-queue.call-next` removed (04-29) | ✅ Holds | no duplicate method+URI in 507 routes |
| Dashboard helper uses named routes (04-29) | ✅ Holds | `app/helpers.php` `getDashboardURL()` |
| Previously public routes now require auth (`update-dark-mode`, notification reads, location lookups, `delete-old-patients` POST, `lang-js`) (04-29) | ✅ Holds | route list; but see C-05 for **new** unauthenticated `log-viewer/*` routes |
| "No remaining hardcoded redirects" (04-29) | ⚠️ Partly | admin `Route::redirect('medicine-history','/dispense-records')` → 404 (M-07); hardcoded `/api/patient/...` in `patient_queue/create.blade.php:96` |
| Legacy billing routes (`medicine.bill.pdf`, `store.patient`, `get-medicine-category`) replaced by redirects (04-29) | ℹ️ Superseded | routes no longer exist; nothing references them (checked) |
| `medicine-history` legacy URLs redirect to dispense records (04-29) | ❌ **Regressed for admin** | M-07 (LIVE 404). Staff/doctor redirects are correct |
| `MedicineBill` / `SaleMedicine` models retained for legacy rows (04-29) | ℹ️ Superseded | both classes deleted; no code references the classes (string `model_type` still handled in `MedicineDispenseTable`) |
| Payment/billing columns removed from create migrations (04-29) | ✅ Holds | migrations |
| `migrate:fresh` + `db:seed` verified end-to-end (04-29) | ✅ **Re-verified** | TOOL: 108 migrations + seeders pass on MariaDB 10.11 |
| Staff role receives `manage_front_cms/settings/roles/countries` (04-29) | ℹ️ Superseded | current seed gives staff: `manage_doctors, manage_medicines, manage_patients, manage_request_documents, manage_specialties, manage_staff_dashboard` (consistent with 05-05 route removal) |
| Every `staff/*` route has `staff.module` (05-05) | ✅ **Re-verified** | 124/124 staff routes carry `EnsureStaffModuleAccess` — **but** the middleware can be steered by request input (H-02) |
| Designation↔station pairing enforced (05-05) | ✅ Holds | `ValidStaffDesignationStationPair` used in `CreateStaffRequest` / `UpdateStaffRequest` |
| Admin-like staff route groups removed (05-05) | ✅ Holds | route list |
| Notifications "Expiry medicine alert" guarded by inventory access (05-05) | ✅ Holds | `menu.blade.php:259` |
| `StaffModuleAccessPolicyTest` 5 passed (05-05) | ✅ Re-verified | `OK (5 tests, 156 assertions)` — the rest of the suite is broken (H-11) |
| Non-doctor `plan` medicines ignored server-side (05-05 hotfix) | ✅ Holds | `DocumentIssuanceController::canCurrentUserManagePlanMedicines()` |
| Staff-module items 1–8 resolved (audit-staff) | ✅ Spot-verified | `StaffRepository::model()` = `User`, transactional `delete()`, no re-query in `show()`, doctor reset → `UserController::resetPassword` |
| Item 9: `MedicineAvailabilityController` unrouted (audit-staff) | ⏳ Still open | L-01 |
| Patient prescriptions "CRUD" (module-crud) | ⚠️ Partly fixed | `store` blocked; **update / destroy / edit still allowed for own prescriptions** (H-06) |
| Consolidated `automation.ps1` / `automation.bat` added (05-18) | ❌ **Missing** | files not in repo; `guide.md` still points to them |
| `db:backup` implemented and scheduled (05-18) | ⚠️ Partly | command exists, but nothing runs `schedule:run` (H-09) |
| Legacy startup scripts removed (05-18) | ❌ Reintroduced | `startup-dev-environment.bat/.ps1` are back (hardcoded `C:\wamp64`) |
| Plain-text DB password removed (05-18) | ⚠️ Only from HEAD | still present in git history (H-13) |
| Root one-off PHP utilities to be migrated (05-18) | ⏳ Still open | L-08 |
| Pharmacist assigned to `triage_area` (05-05 data issue) | ❓ Not verifiable | production data not available to this audit |

---

## 4. Critical findings

### C-01 — Unrestricted upload into the web root → remote code execution and public clinical photos
- **Location:** `app/Http/Controllers/DocumentIssuanceController.php:322-360` (store), `:1547-1616` (update, `handleImageUpdates`); `config/filesystems.php:33-44` (`local` and `public` disks both rooted at `public_path('uploads')`).
- **Evidence (LIVE):** A consultation was posted with `consultation_images[]=probe.php` (content `<?php echo "AUDIT-PROBE-" . (6*7);`). The file was written to `public/uploads/consultation_images/Upload_Probe/<timestamp>/probe.php`; an **unauthenticated** `GET` of that URL returned `AUDIT-PROBE-42`, i.e. the server executed it. Probe removed afterwards.
- **Why:** the only server-side check is size ≤ 5 MB; the client file name and extension are kept (`getClientOriginalName()`); the folder is under `public/`; both Apache (WAMP) and `php artisan serve` execute `.php` there. The patient name from the form is also used in the folder path (`str_replace(' ', '_', $data['name'])`), so `../` in a name can place files outside the intended folder.
- **Impact:** any authenticated staff/doctor/admin account (or anyone who steals one) can take over the server and the whole database. Independently, every consultation photo is downloadable by anyone on the network who knows/guesses the URL (name + timestamp), without logging in.
- **Fix:** validate `consultation_images.*` with `image|mimes:jpg,jpeg,png,webp|max:5120`; store on a **private** disk under `storage/app` with a random file name (`$file->hashName()`); serve through an authenticated controller that checks the viewer's access; never build paths from free-text names. Move the existing `public/uploads/consultation_images` tree out of `public/` and add a `.htaccess`/server rule denying script execution in `public/uploads` as defence in depth.

### C-02 — Dependency with unauthenticated RCE (Livewire) and 97 further advisories
- **Location:** `composer.lock`.
- **Evidence (TOOL):** `composer audit --locked` → **98 advisories across 24 packages**. Most relevant:

| Package (locked) | Worst advisory | Notes |
|---|---|---|
| `livewire/livewire` v3.5.4 | **CVE-2025-54068 — critical, unauthenticated RCE via property-update hydration** | Livewire powers every data table in the app. Fixed in 3.6.4+. |
| `phpoffice/phpspreadsheet` 1.29.0 | 2 critical, 15 high (incl. patch bypass CVE-2026-45034) | used by `maatwebsite/excel` exports |
| `mtdowling/jmespath.php` 2.7.0 | critical code injection (CVE-2026-54133) | pulled in by `aws/aws-sdk-php` (S3 not used — consider removing `league/flysystem-aws-s3-v3`) |
| `phenx/php-svg-lib` 0.5.0 / `dompdf/dompdf` v2.0.3 | critical restriction bypass; local file read via SVG (CVE-2026-56722) | all PDFs |
| `laravel/framework` v10.42.0 | 2 high, 2 medium | upgrade to latest 10.48.x at minimum |
| `symfony/process` v6.4.2 | CVE-2024-51736 command hijack **on Windows** | `DatabaseBackup` uses `Process` on the WAMP host |
| `league/commonmark`, `guzzlehttp/guzzle`, `symfony/http-foundation`, `symfony/mime`, `spatie/laravel-medialibrary`, `spatie/image-optimizer`, `nesbot/carbon`, … | high/medium | see `composer audit` |
| `laravelcollective/html` | **abandoned** (replacement: `spatie/laravel-html`) | used in almost every form |

  `npm audit --omit=dev` (runtime JS): `postcss` (high), `lodash-es` (high), `nanoid` (high), `quill 2.0.3` (XSS in HTML export), `@hotwired/turbo` (low). Build-time devDependencies add 50 more (9 critical, mostly the webpack 4-era toolchain behind `laravel-mix`).
- **Fix:** `composer update` within the Laravel 10 line (`livewire/livewire:^3.6.4`, `dompdf/dompdf:^3.1.6`, `phpoffice/phpspreadsheet` latest 1.x/2.x, `laravel/framework:^10.48`), re-run `composer audit`; plan the Laravel 11/12 upgrade (Laravel 10 is out of security support). Run `npm audit fix` for runtime packages.

### C-03 — Consultation save wipes the patient's allergy / comorbidity / medication history
- **Location:** `app/Http/Controllers/DocumentIssuanceController.php:773-778` (`syncPatientProfileFromConsultationData`), called from `prepareConsultationPatient()` on every consultation store.
- **Evidence (LIVE):** patient record before: `allergies='PENICILLIN (anaphylaxis)'`, `maintenance='Metformin 500mg'`, `comorbidities='Diabetes'`. Admin posted a consultation for that patient with the allergy/maintenance fields left blank (a normal case for a walk-in or when the pre-fill did not load). After: all three columns **`NULL`**.
- **Why:** the payload is built as `'allergies' => normalizeNullableString($data['allergies'] ?? null)` and written unconditionally with `$patient->update($patientPayload)`; blank means "set to NULL", not "leave unchanged". The form pre-fills these values only when a patient is selected through the search box or the page is opened from patient history; the auto-match path (H-03) never pre-fills.
- **Impact:** loss of safety-critical clinical data (drug allergies). Later prescribers will not see the allergy.
- **Fix:** only include a field in `$patientPayload` when the submitted value is non-empty (as the `$userUpdates` block above it already does), or merge with the existing value; add an audit-log entry whenever a master-record clinical field changes; add a regression test.

### C-04 — Deleting an in-use medicine destroys stock ledger and clinical history
- **Location:** `app/Http/Controllers/MedicineController.php:186-201` (`destroy`), `:322-338` (`checkUseOfMedicine`); FK cascades in migrations (`prescriptions_medicines.medicine`, `consultation_medicines.medicine_id`, `medicine_batches.medicine_id` → `medicine_transactions.batch_id`, `used_medicines.medicine_id`, all `ON DELETE CASCADE`).
- **Evidence (LIVE):** created medicine *AuditMed* with a 50-unit batch + ledger entry, one prescription line and one consultation-medicine row. `GET /admin/medicines-uses-check/1` answered `result:false` ("not in use"), so the UI shows a plain "are you sure?" prompt. `DELETE /admin/medicines/1` → success. Afterwards: batches **0**, `medicine_transactions` **0**, prescription lines **0** (the prescription header remained, now with no medicines), consultation medicine rows **0**.
- **Why:** `destroy()` hard-deletes the medicine and additionally deletes the *first* stock-in line and the *first* dispense line it finds; the DB cascades do the rest. `checkUseOfMedicine()` only looks at `sale_medicines`/`purchased_medicines`, not at batches, prescriptions or consultations; and `if ($result)` is always true because `$result` is an array.
- **Fix:** never hard-delete medicines that have any history: return 422 if batches/transactions/prescriptions/consultation medicines exist, or switch to soft-delete / `is_active=false`. Change the FKs from `CASCADE` to `RESTRICT` (new migration). Remove the manual "delete first purchased/sale line" code.

### C-05 — Log viewer is public: read, download and delete logs without login
- **Location:** `config/log-viewer.php` (package `opcodesio/log-viewer` v3.1.11); no `viewLogViewer` gate or `LogViewer::auth()` callback anywhere in `app/`.
- **Evidence (LIVE):** without any session: `GET /log-viewer/api/files` → **200** with the file list; `GET /log-viewer/api/logs?file=…` → **200** with log entries; `GET …/download/request` → **200**. `DELETE /log-viewer/api/files/{id}` and `POST …/delete-multiple-files` are behind the same (empty) authorization — not executed.
- **Why:** in this package version, `AuthorizeLogViewer` only enforces something if a gate/callback is registered; none is.
- **Impact:** application logs contain patient data (`Log::info('Stock-In Input Data', $input)`, `Destroy method called … request_all`, exception messages with names and SQL) and can be wiped by anyone on the LAN, destroying forensic evidence.
- **Fix:** remove the package (there is already an authenticated `rap2hpoutre` viewer at `/admin/logs`) or register `Gate::define('viewLogViewer', fn ($u) => $u?->hasRole('clinic_admin'))` and add `auth` to `log-viewer.middleware` / `api_middleware`; set `LOG_VIEWER_ENABLED=false` by default.

### C-06 — `xss` middleware corrupts clinical text and locks users out
- **Location:** `app/Http/Middleware/XSS.php:95` (`$purifier->purify($value)` with `HTML.Allowed = ''`); applied to every `staff/*`, `doctors/*`, `patients/*` route and to `/profile/*`, `/change-user-password`; **not** applied to `admin/*` or to `/login`.
- **Evidence (LIVE, HTMLPurifier 4.18 as configured):**

| Submitted | Stored |
|---|---|
| `BP > 140/90` | `BP &gt; 140/90` (rendered as the literal text `&gt;` through `{{ }}` and in PDFs) |
| `Temp < 37 and HR > 100` | `Temp &lt; 37 and HR &gt; 100` |
| `x<y and z>w` | `xw` (**text deleted**) |
| `Allergy: penicillin <severe>` | `Allergy: penicillin ` (**severity deleted**) |
| `Tom & Jerry` | `Tom &amp; Jerry` |

  Password lockout: patient changed their password to `My&Pass123` via `PUT /change-user-password` → login with `My&Pass123` **fails**; login with `My&amp;Pass123` succeeds. Any password containing `&`, `<` or `>` set through a purified route becomes unusable. The same applies to passwords set by doctors/staff for new patients (`staff/patients`, `doctors/patients`).
- **Impact:** silent corruption/loss of clinical notes typed by doctors and nurses (the people who type `<` and `>` most), different stored data depending on whether an admin or a staff member typed it, and account lockouts.
- **Fix:** remove the global purifier middleware. Rely on Blade's `{{ }}` output escaping (already used everywhere) and, where rich HTML is really needed (CMS pages), purify **on output** with an allow-list. Never transform password fields. Then write a one-off data migration that decodes `&lt; &gt; &amp;` in affected text columns (after review — deleted text cannot be recovered).

---

## 5. High findings

### H-01 — Self-service mass assignment lets a patient change `users.type` and read all lab requests
- **Location:** `app/Http/Controllers/UserController.php:258-275` (`changePassword`: `$user->update($input)` with the whole request); `app/Livewire/LabRequestTable.php:53` (patient scoping uses `type`, not role); `User::$fillable` includes `type`, `status`, `email_verified_at`, `university_id_number`, `employee_id`.
- **Evidence (LIVE):** logged in as patient A (`type=4`), `PUT /change-user-password` with valid password fields **plus `type=3`** → `{"success":true}`; DB now `type=3`. With the same session, `LabRequestTable::builder()` returned **patient B's** lab request (1 row, owner 3); after restoring `type=4` it returned 0 rows.
- **Fix:** `$user->update(['password' => Hash::make($input['new_password'])])` only; scope all patient data by role/ownership (`isRole('patient')` + `patient_user_id = auth()->id()`), never by the mutable `type` column; remove `type`/`status`/`email_verified_at` from `$fillable` and set them explicitly where needed.

### H-02 — Staff module restrictions bypassed through request input
- **Location:** `app/Http/Middleware/EnsureStaffModuleAccess.php:66-89` (`resolveDocumentIssuanceModule` trusts `?module=` first, then `document_type` from the request body, and only then the actual record); `DocumentIssuanceController.php:1177` (`update` takes `document_type` from input).
- **Evidence (LIVE):** front-desk "Clinic Staff" user (allowed: certificates, **not** consultations):
  - `GET /staff/document-issuances/1` (a consultation) → **403** (correct).
  - `GET /staff/document-issuances/1/export-pdf?module=certificates` → **200** (consultation PDF downloaded).
  - `PUT /staff/document-issuances/1` with `document_type=medical_certificate&name=Tampered Name` → **302**, and the consultation row's `name` became `Tampered Name`.
- **Fix:** when a route model is present, resolve the module **only** from the stored `document_type`; ignore `module`/`document_type` input for show/edit/update/destroy/export. In `update()`, never read `document_type` from the request.

### H-03 — Walk-in consultations are auto-merged into the wrong patient and overwrite their identity
- **Location:** `DocumentIssuanceController.php:437-617` (`findMatchingPatientForConsultationData`, `isWildcardTokenMatch`), `:676-822` (`syncPatientProfileFromConsultationData`).
- **Evidence (LIVE, matcher called directly):** `"Ana Cruz"` vs existing `"Juliana Cruzado"` → **MATCH**; `"Mark Reyes"` vs `"Marko Reyesa"` → MATCH; `"Jose Santos"` vs `"Joseph Santos"` → MATCH. Matching only additionally requires the same DOB and gender. (CODE) On a match the existing user's first/middle/last name, contact, DOB, gender, emergency contact, campus/college/course/year, vaccination, allergies… are overwritten from the form, and the new consultation is filed under that person.
- **Impact:** two people's medical histories merged; the original patient is silently renamed. Very hard to detect or undo.
- **Fix:** never auto-link without explicit confirmation; if a probable match exists, show it to the user and require a click. Require exact normalized full-name match (or university ID) for any automatic link, and never overwrite identity fields of an existing patient from a consultation form.

### H-04 — Editing a stock-in changes the document but not the inventory
- **Location:** `app/Repositories/MedicineAvailabilityRepository.php:180-340` (`updatePurchaseMedicine`).
- **Evidence (LIVE):** stock-in of 10 × MedA (expiry 2027-01-01). Edited the line to MedB, expiry 2029-06-30, same quantity. Result: MedA still **10**, MedB **0**; the stock-in line says MedB/2029-06-30; the batch ledger still says MedA/2027-01-01.
- **Why:** only the quantity *difference* is applied, to the new medicine, via a batch number derived from the row index; medicine/dosage/expiry changes are ignored for the ledger. New-row branch still does the manual `quantity` bump that INV-4 removed elsewhere. Removed rows and quantity reductions use FEFO across all batches (see H-05) and ignore dosage.
- **Fix:** treat an edited line as "reverse the old batch exactly, then record the new line" (target the batch created by that line — store `batch_id` on `purchased_medicines`); block edits that would make any batch negative; remove the manual bump.

### H-05 — Deleting/reducing a stock-in removes stock from the wrong batch (expired stock looks valid)
- **Location:** `app/Http/Controllers/StockInController.php:131-156`, `MedicineAvailabilityRepository.php:232-239, 309-326`, both calling `deductStockFefo(..., TYPE_ADJUSTMENT)`.
- **Evidence (LIVE):** MedC batches before deleting the 2028 stock-in: `2026-12-01=5, 2028-12-01=10`. Expected after delete: `2026-12-01=5, 2028-12-01=0`. Actual: **`2026-12-01=0, 2028-12-01=5`**.
- **Impact:** the five units physically on the shelf expire on 2026-12-01, but the system believes they are good until 2028 and will dispense them after expiry. FEFO/expiry alerts become wrong.
- **Fix:** reverse against the specific batch created by that stock-in line (link `purchased_medicines` → `medicine_batches`), not FEFO.

### H-06 — Patients can edit and delete their own prescriptions (including dispensed ones)
- **Location:** `routes/patient.php:16-21` (resource `update`/`destroy`, `edit`, `prescription-medicine`, `active-deactive`); `PrescriptionController.php:342` (`update`) and `:564` (`destroy`) only check ownership.
- **Evidence (LIVE):** patient `DELETE /patients/prescriptions/1` on their **dispensed** prescription → `{"success":true,"message":"messages.flash.prescription_deleted"}`, row gone. (CODE) `update` lets the patient change medicines, quantities, doctor and even `patient_id` (validated only as "exists").
- **Fix:** patients should be read-only: restrict patient routes to `show`, `prescription-medicine-show`, `prescription-pdf`; add `abort_if(isRole('patient'), 403)` to `edit/update/destroy/prescreptionMedicineStore/activeDeactiveStatus` as defence in depth.

### H-07 — Editing a prescription after it was dispensed rewrites the dispense record without touching stock
- **Location:** `PrescriptionController.php:342-452` (`update`), `:564-591` (`destroy`), `resources/views/prescriptions/*` (no status gate).
- **Evidence (CODE):** `update()` has no `status === dispensed` guard; it deletes and re-creates `prescriptions_medicines` and the linked `sale_medicines` (dispense items) with the new quantities, while the stock that was deducted by `MedicineInventoryService::dispensePrescription()` stays as it was. Deleting a dispensed prescription leaves its dispense record orphaned (and hidden from the dispensing history list, which requires the prescription to exist).
- **Fix:** make dispensed prescriptions immutable (or require an explicit "reverse dispense" that restores stock and logs it).

### H-08 — Deleting a doctor deletes all of that doctor's prescriptions
- **Location:** `UserController.php:161-192` (`destroy` hard-deletes the `doctors` row); `database/migrations/2023_08_01_050432_create_prescriptions_table.php:46` (`prescriptions.doctor_id → doctors ON DELETE CASCADE`, confirmed in the live schema; `prescriptions_medicines` cascades from prescriptions).
- **Impact:** removing a resigned doctor's account erases every prescription they wrote for every patient.
- **Fix:** deactivate instead of delete (`status=0`), soft-delete `Doctor`, and change the FK to `RESTRICT`/`SET NULL` in a new migration. Same pattern applies to `patient_queues.added_by → users CASCADE`.

### H-09 — Backups: scheduled job never runs; manual backup/restore unsafe
- **Location:** `app/Console/Kernel.php:15-20`; `app/Console/Commands/DatabaseBackup.php`; `app/Http/Controllers/BackupController.php:50-100, 121-160`; root `startup-dev-environment.*`; `guide.md`.
- **Evidence (CODE/LIVE):**
  - Nothing in the repository runs `php artisan schedule:run`/`schedule:work` (no Task Scheduler setup script; `automation.ps1` does not exist although `guide.md` and the 05-18 audit describe it). → **the hourly `db:backup` never executes.**
  - The Kernel comment says "only if changes detected" — there is no change detection; if it did run, it would keep 720 full dumps (hourly × 30 days).
  - Manual backup (`BackupController::create`) uses `--skip-lock-tables` **without** `--single-transaction` → not a consistent snapshot while the clinic is working. It also passes `--column-statistics=0 --set-gtid-purged=OFF`; **LIVE:** MariaDB 10.11 `mysqldump` (WAMP can ship MariaDB) answers `unknown variable 'column-statistics=0'` / `unknown variable 'set-gtid-purged=OFF'` and exits 7. The failed run still leaves a **0-byte `.sql` file**, and a dump that dies midway leaves a partial file (LIVE: 890-byte file on exit 2); the controller does not delete it, so the Backups page lists it as a valid backup.
  - Restore (`import`) replays an uploaded SQL file straight into the live DB with no automatic safety backup first and no confirmation step.
  - Scheduled backups go to `storage/app/backups/scheduled`, which the Backups page does not list.
- **Fix:** add a documented Windows Task Scheduler entry (or a `schedule:work` service) and verify it; align manual backup with the command (`--single-transaction --routines --triggers --events`, detect MariaDB, delete the file on failure); take an automatic pre-restore backup; list both folders; keep daily + weekly retention.

### H-10 — Exception handler turns every AJAX 401/403/404/419/429 into HTTP 500
- **Location:** `app/Exceptions/Handler.php:37-66` (uses `$exception->getCode()`, which is `0` for `HttpException`, `AuthenticationException`, `TokenMismatchException`, `AuthorizationException`).
- **Evidence (LIVE):** AJAX `POST /admin/backups/create` with a wrong CSRF token → `{"message":"CSRF token mismatch."}` with **HTTP 500**. `resources/assets/js/custom/custom.js:68` only recovers from stale tokens on **419** → the recovery code is dead and users lose the form they were filling. Unauthenticated JSON calls also return 500.
- **Fix:** use `$exception->getStatusCode()` for `HttpExceptionInterface`, map `AuthenticationException`→401, `AuthorizationException`→403, `TokenMismatchException`→419, `ValidationException`→422, otherwise fall back to `parent::render()`.

### H-11 — The automated test suite does not run
- **Location:** `phpunit.xml` (forces `sqlite :memory:`); migrations using MySQL-only SQL (`CREATE OR REPLACE VIEW used_medicines_view …`).
- **Evidence (TOOL):** `Tests: 36, Assertions: 158, Errors: 29, Failures: 1` — every test using `RefreshDatabase` dies on `near "OR": syntax error`; only `StaffModuleAccessPolicyTest` (5) and the unit example (1) pass. The safety guard in `tests/CreatesApplication.php` (refuses non-SQLite) is good and should stay.
- **Fix:** run tests against a dedicated MySQL/MariaDB test schema (guard by database **name**, e.g. must end in `_test`), or make the view migrations driver-aware; then add regression tests for C-03, C-04, H-01, H-02, H-04/H-05.

### H-12 — Dispense history number space is only 9,000 → eventual infinite loop
- **Location:** `app/helpers.php:571-579` (`generateUniqueHistoryNumber()`: `random_int(1000, 9999)` retried until unused); used by every dispense record and every prescription.
- **Evidence (CODE):** each prescription and each manual dispense creates one `medicine_bills` row. After ~9,000 records every request that creates one loops forever (PHP timeout / hung worker); long before that, collisions make it progressively slower. There is no unique index on `history_number` (only a plain index), so concurrent requests can also create duplicates.
- **Fix:** derive the number from the auto-increment ID (e.g. `HIS` + zero-padded ID) or a date-based sequence; add a unique index.

### H-13 — Secrets and personal data committed to git
- **Location:** git history of `auto-backup-database.bat` (last present in `7e25d82`): a **non-empty `DB_PASS=` value**; `test_search.json` (repo root) contains what appears to be a real patient search response (full name, date of birth, mobile number, emergency-contact name/number, university ID). `docs/staff-current-effective-access.json` contains staff names/e-mails (appear to be test accounts).
- **Impact:** anyone with repository access has the DB password (unless rotated) and a patient's personal data; this is reportable under the Philippine Data Privacy Act if the record is real.
- **Fix:** rotate the MySQL password; delete `test_search.json` (and purge it and the old scripts from history with `git filter-repo` if the repository is shared); keep test fixtures synthetic.

### H-14 — Query-builder updates on `users` bypass `$fillable` and the `hashed` cast
- **Location:** `app/Repositories/PatientRepository.php:363` (`$patient->user()->update(Arr::except($input, [...]))`), `app/Repositories/UserRepository.php:272` (patient self-profile), `:318` (doctor self-profile).
- **Evidence (CODE):** `->user()->update()` is an Eloquent **builder** update: every request key that is not in the exclusion list is written to `users` directly, including `password` (stored **in plain text**, which then makes `Hash::check` throw on login), `archived_at`, `remember_token`, `employee_id`, etc. The self-profile paths exclude even fewer keys (`status`, `email_verified_at`, `university_id_number` are writable by the patient/doctor themselves). Any unexpected form key causes an "Unknown column" SQL error.
- **Fix:** load the model and call `$user->fill(Arr::only($input, $allowed))->save()` with an explicit allow-list per form.

### H-15 — Staff management binds any user, and any role can be assigned
- **Location:** `app/Http/Controllers/StaffController.php:80-128` (`show/edit/update/destroy(User $staff)` — not scoped to `type = STAFF`), `app/Repositories/StaffRepository.php:73, 109` (`assignRole/syncRoles($input['role'])` with any role ID from `Role::pluck`).
- **Evidence (CODE):** `/admin/staffs/{id}/edit` + update for a doctor, patient or the admin itself forces `type=STAFF` and replaces its roles; `DELETE /admin/staffs/{ownId}` soft-deletes the logged-in admin. A "staff" can be given the `doctor` or `clinic_admin` role, producing accounts that crash doctor pages (`getLogInUser()->doctor->id` on null).
- **Fix:** scope binding (`abort_unless($staff->type === User::STAFF, 404)`), prevent self-deletion, restrict the role list to `staff`/`nurse`.

---

## 6. Medium findings

| ID | Finding | Location | Evidence | Fix |
|---|---|---|---|---|
| M-01 | Deleting/editing a **prescription-derived** dispense record "restores" stock that was never deducted (pending prescription) → phantom stock; restored units go into a `2099-12-31` "never expires" batch because prescription dispense items carry no dosage/expiry | `DispenseRecordController.php:256-277`, `MedicineBillRepository.php:58-130`, `PrescriptionController.php:200-204, 439-443` | CODE | only allow edit/delete of manual dispense records (`model_type = DispenseRecord`); record batch/expiry on prescription dispense items at dispense time |
| M-02 | Dispensing a prescription ignores the prescribed dosage — FEFO may hand out 250 mg batches for a 500 mg order | `MedicineInventoryService.php:148` (`deductStockFefo` called without `$dosage`) | CODE | pass `$line->dosage` |
| M-03 | Batch-number handling: stock-in with an existing batch number **overwrites the expiry of all existing units** of that batch; auto numbers (`BATCH-{id}-{YmdHis}`, `RESTORE-…`, `RETURN-…`) collide within the same second → silent merge (stock-in) or unique-key failure that rolls back the whole edit (restore/return) | `MedicineInventoryService.php:49, 78, 323`; `DocumentIssuanceController.php:1836` | CODE | reject expiry mismatch for an existing batch; use UUID/sequence suffixes |
| M-04 | Reports are wrong: the **Dispensing** report tab and CSV read `used_medicines`, which no current code writes (real data is in `medicine_transactions`/`used_medicines_view`); CSV export `where(...)->orWhere(...)` is not grouped so a search term overrides date/user/type filters (and pulls non-consultation documents into "visits"); `chunk()` ordered by non-unique `created_at` can skip/duplicate rows; no CSV formula-injection escaping | `ReportGeneration.php:127`, `ActivityLogController.php:64, 86, 110` | CODE | read from the transaction ledger/view; wrap search in `where(fn…)`; use `chunkById`; prefix cells starting with `= + - @` |
| M-05 | Audit trail is overwritten: `logActivity()` uses `updateOrCreate` keyed on action+subject, so every edit replaces the previous log row (who changed a consultation/patient/certificate/lab request and when is lost). Logs also duplicate PHI (address, contact, complaints, diagnosis) | `app/Traits/LogsActivity.php:89` | CODE | make all clinical-record logs append-only; store only IDs + changed field names |
| M-06 | API routes: `/api/patient/{id}/latest-consultation` is in the stateless `api` group with `auth:web` → never authenticated (LIVE: logged-in admin gets "Unauthenticated", so the queue-creation preview in `patient_queue/create.blade.php:96` never works); `/api/medicines` is readable by **patients** (LIVE 200); `/debug-profile-data` debug route is live for all users (LIVE 200) | `routes/api.php:25, 67`; `routes/debug-profile.php:7` | LIVE | move the endpoint into `web.php` under the role groups with ownership checks; delete the debug route and the unused `/api/medicines` |
| M-07 | Admin `medicine-history` redirect goes to `/dispense-records` (no `/admin`) | `routes/web.php:289-290` | LIVE: 302 → 404 | `Route::redirect('medicine-history', '/admin/dispense-records')` or `redirect()->route('dispense-records.index')` |
| M-08 | Impersonation: once impersonating, **"Leave" returns 403** (route sits behind `role:clinic_admin`, and the session user is now the target) — the admin must log out; `GET /admin/impersonate/{id}` is CSRF-able and allows impersonating other admins | `routes/web.php:118-119`, `UserController.php:328-345` | LIVE: leave → 403 | move `impersonate-leave` outside the admin role group (auth only); make impersonate a POST; implement `canImpersonate/canBeImpersonated` |
| M-09 | Storage configuration: the **`local` disk is rooted at `public/uploads`** (anything meant to be private is web-served); `CustomPathGenerator` is never registered (no `config/media-library.php`); consultation image files are unlinked **inside** the DB transaction (a later rollback leaves records pointing at deleted files; new uploads stay orphaned); `consultation_images` is double-JSON-encoded (model `array` cast + `json_encode`) | `config/filesystems.php:33-35`, `DocumentIssuanceController.php:367, 1566, 1612` | CODE/LIVE (stored value inspected) | `local` → `storage_path('app')`; register or delete the generator; delete files after commit; assign arrays directly to the cast attribute |
| M-10 | Deployment configuration traps: `APP_ENV=production` forces `https` URLs (breaks the HTTP LAN deployment, so the clinic stays on `local`); `env('FORCE_HTTPS')` is read outside config and is ignored after `php artisan optimize` (run by the startup script); `.env.example` ships `APP_DEBUG=true` (LIVE: error pages dumped source code and view data); `barryvdh/laravel-debugbar` is a production dependency; the ngrok check trusts the client `Host` header | `app/Providers/AppServiceProvider.php:50-57`, `.env.example`, `composer.json` | LIVE/CODE | add `FORCE_HTTPS` to `config/app.php`; decide scheme via config not env; ship `APP_DEBUG=false`; move debugbar to `require-dev` |
| M-11 | Performance: `ForceAdminDefaultPasswordChange` runs a bcrypt verify on **every** admin request (LIVE: ~235 ms each at `BCRYPT_ROUNDS=12`, including Livewire/AJAX calls); `sale_medicines` has **no index** on `medicine_bill_id`/`medicine_id` (LIVE schema) while the dispensing table sums it per row; settings save updates every non-admin user one by one; `searchUsers` (document issuance and lab request) return unbounded result sets with many eager loads (empty query → all patients); `User::$appends` `profile_image` triggers a media query per serialized user | `ForceAdminDefaultPasswordChange.php:32`, `SettingController.php:68`, `LabRequestController.php:499`, `DocumentIssuanceController.php:2043` | LIVE/CODE | cache a "must change password" flag in the session or a column; add indexes; single `UPDATE`; `limit(20)` + minimum query length |
| M-12 | `nurse` role is half-supported: many controllers only check `isRole('staff')`, so a nurse-role user is redirected to admin route names after saving (403) and cannot dispense/toggle prescriptions (`activeDeactiveStatus`, `dispense`) | e.g. `PatientController.php:37-48`, `PrescriptionController.php:540-557, 593, 620` | CODE | use `getRouteByRole()` everywhere; check `isRole('staff') \|\| isRole('nurse')` |
| M-13 | Soft-deleted users keep their unique email/university ID/employee ID/contact, so the person cannot be re-registered and no visible record explains why; staff delete hard-deletes the staff profile while the user is only soft-deleted (restore loses designation/station) | `StaffRepository.php` `delete()`, unique rules in `User::$rules`/requests | CODE | add `whereNull('archived_at')` to unique rules or provide a restore path; soft-delete the profile |
| M-14 | Consultation/certificate store & update have almost no server-side validation: unknown `document_type` → nothing saved but "created successfully" shown; `document_creator_id` is taken from the request (author can be spoofed); failures echo raw exception messages | `DocumentIssuanceController.php:85-157, 203, 280` | CODE | FormRequest with `document_type in:consultation_form,medical_certificate,excuse_slip` and field rules; always use `auth()->id()` |
| M-15 | Lab request create/update are not wrapped in a transaction (partial request without items possible); `patient_user_id` accepts any user, not only patients | `LabRequestController.php:81-205, 241-370` | CODE | `DB::transaction`; `Rule::exists('users','id')->where('type', User::PATIENT)` |
| M-16 | `composer.lock` is not installable on PHP ≥ 8.4 (`nette/schema`, `nette/utils`); runs on WAMP PHP 8.1–8.3 only | `composer.lock` | TOOL | `composer update nette/*` (and see C-02) |
| M-17 | Edit-consultation "Nurse in charge" list is always empty: `User::where('type', 'staff')` compares an integer column with a string | `DocumentIssuanceController.php:1127` | CODE | `where('type', User::STAFF)` |
| M-18 | Doctor create/update catch blocks never `rollBack()`; doctor delete removes media **rows** but leaves files; `! empty('profile')` literal (always true) | `UserRepository.php:112-151, 153-207`, `UserController.php:168-175` | CODE | add rollback; use `clearMediaCollection`; fix condition |

---

## 7. Low findings

| ID | Finding | Location |
|---|---|---|
| L-01 | Dead code: `MedicineAvailabilityController` and `StateController` (unrouted); `getPrescriptionRoute()` (builds non-existent `admin.prescriptions.*` names, unused); `MedicineBillRepository::getDoctors(): Doctor` (wrong return type, unused); `MedicineAvailabilityRepository::updateAccountant()` (references undefined `Accountant`, `updateProfileImage`, `removeFile`); `PrescriptionController::showModal` (unrouted, null-deref); unused Livewire components (`BannerTable`, `ProvinceTable`, `MedicineAvailabilityTable`, four `*DashboardSidebarTable`); backup views calling non-existent `/api/patient-queue/data`; Breeze leftovers (`layouts/navigation.blade.php` → `users.index`); `fronts/sliders/create` view (→ `sliders.index`); legacy `Staff` model / `staff` table | various |
| L-02 | 16 translation keys used in `app/` do not exist in `lang/en/messages.php` (users see raw keys, e.g. `messages.flash.prescription_deleted`, `messages.flash.medicine_deleted`, `messages.flash.not_allow_access_record`) | `lang/en/messages.php` |
| L-03 | Duplicate/odd route & index definitions: unnamed `Route::redirect` inside named groups creates two routes named `staff.` and two named `doctors.`; extra unnamed `POST countries/{country}` and `POST states/{state}`; duplicate indexes on `medicine_bills` (`patient` + `patient_id`, `doctor` + `doctor_id`) and `prescriptions` | routes, migrations |
| L-04 | Views assume `requested_at` is never null (`->format()` on a nullable date column) — legacy/imported rows crash show/edit | `document_issuances/view.blade.php:175`, `edit.blade.php:191` |
| L-05 | `generateUniqueAvailabilityNumber` / `generateUniqueLabRequestNumber` check-then-insert without DB uniqueness (availability) → race duplicates | `app/helpers.php:559-567, 1043-1057` |
| L-06 | Root one-off scripts still in the project root: `check_guards.php`, `check_roles.php`, `check_user_status.php`, `dump_permissions.php`, `convert-location-data.php`, `cleanup-database.sql`, `test_search.json`; `guide.md` documents a non-existent `automation.ps1`; `CLAUDE.md` is empty | repo root |
| L-07 | `DatabaseBackup` hard-codes a `C:\wamp64` fallback; both backup paths pass the DB password on the process command line (visible in the process list) | `DatabaseBackup.php:134`, `BackupController.php:65-73` |
| L-08 | PHI written to the application log (`Stock-In Input Data`, `Destroy method called … request_all`, consultation medicine logs with patient names) | `StockInController.php:68`, `DocumentIssuanceController.php:1984-1990` |
| L-09 | Root `.htaccess` rewrites to an absolute `/public/` (breaks when deployed in a sub-folder); npm scripts use Windows-only `set NODE_OPTIONS=` | `.htaccess`, `package.json` |
| L-10 | `.env.example` uses `QUEUE_CONNECTION=database` but there is no `jobs` table migration (latent; nothing is queued today). `.env.example` also mixes Laravel 11 keys (`CACHE_STORE`, `BROADCAST_CONNECTION`, `LOG_STACK`, `MAIL_SCHEME`, `APP_MAINTENANCE_DRIVER`) into a Laravel 10 app (cache/broadcast configs handle both; the rest are ignored) | `.env.example` |
| L-11 | `PrescriptionController::convertToPDF` returns raw exception text and does not apply the doctor-ownership check that `show/edit` apply | `PrescriptionController.php:782-903` |
| L-12 | Patient queue: duplicate-entry check is not atomic (double click can queue twice); `callNext`/`complete` accept any current status | `PatientQueueController.php:68-103, 212-237` |
| L-13 | `MedicineController::checkUseOfMedicine` always returns the "already in use" message text (`if ($result)` tests an always-non-empty array; only the `result` flag is right). Consultation-image folders are keyed by patient name, so different patients with the same name share a parent folder (clean-up only removes it when empty, so no loss today — fragile) | `MedicineController.php:332`, `DocumentIssuance.php:199-247` |
| L-14 | `lang-js` regeneration is a GET that writes files (admin only); `rap2hpoutre` viewer supports GET `?del`/`?delall` (CSRF-able log deletion by an admin click) | `routes/upgrade.php`, `routes/web.php:115` |

---

## 8. Remediation plan (suggested order)

1. **Immediately (same day):** C-01 (move uploads private + validate type; scan `public/uploads` for non-image files), C-05 (disable `log-viewer`), C-02 (upgrade Livewire ≥ 3.6.4, dompdf, phpspreadsheet, Laravel 10.48.x), rotate the DB password (H-13).
2. **Data-loss stoppers (this week):** C-03, C-04 (+ FK `RESTRICT` migration), C-06 (remove purifier middleware; data clean-up script), H-03, H-06, H-07, H-08.
3. **Access control:** H-01, H-02, H-14, H-15, M-06, M-08.
4. **Inventory integrity:** H-04, H-05, M-01, M-02, M-03, H-12. After fixing, run a reconciliation report: for each medicine compare `SUM(medicine_batches.quantity)` with the ledger, and list batches whose expiry differs from their stock-in line.
5. **Safety nets:** H-09 (working scheduled + verified backups), H-11 (test suite on MySQL) with regression tests for everything above, H-10.
6. **Correctness & hygiene:** remaining Medium and Low items.

---

## 9. Reproduction notes (for re-verification)

All commands were run against a **throwaway** database (`norsu_clinic_audit`) with synthetic accounts — never against clinic data.

```bash
# dependencies (PHP 8.4 sandbox needed the platform flag; WAMP PHP ≤ 8.3 does not)
composer install --ignore-platform-req=php
composer audit --locked
npm audit --package-lock-only --omit=dev

# schema + seed on MariaDB/MySQL
php artisan migrate:fresh --force && php artisan db:seed --force

# tests (currently 29 errors / 1 failure — H-11)
vendor/bin/phpunit

# routes
php artisan route:list --json   # 507 routes; log-viewer/* and _ignition/* have no auth middleware
```

Live probes used (server started from `public/` with Laravel's `server.php` router, same as `php artisan serve`):

- `GET /log-viewer/api/files` without cookies → 200 (C-05)
- `PUT /change-user-password` as a patient with extra `type=3` → DB `users.type` 4 → 3 (H-01)
- `PUT /change-user-password` with `new_password=My&Pass123`, then login → fails (C-06)
- `GET /staff/document-issuances/{consultation}/export-pdf?module=certificates` as front-desk staff → 200 (H-02)
- `POST /admin/document-issuances` (consultation, blank allergies, existing patient) → patient allergies `NULL` (C-03)
- `DELETE /admin/medicines/{id}` on a medicine with stock, prescription and consultation usage → all cascaded away (C-04)
- `POST /admin/document-issuances` with `consultation_images[]=probe.php` → `GET /uploads/.../probe.php` executed (C-01; probe deleted)
- `GET /admin/medicine-history` → 302 `/dispense-records` → 404 (M-07)
- AJAX with a bad CSRF token → HTTP 500 instead of 419 (H-10)
- `GET /admin/impersonate/{id}` then `GET /admin/impersonate-leave` → 403 (M-08)
