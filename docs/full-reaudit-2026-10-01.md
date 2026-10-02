# NORSUCLINIC Full Workspace Re-Audit — 2026-10-01

| | |
|---|---|
| Branch / commit audited | `changes_v2` @ `1145357` (after all 2026-09-30 remediation phases B0–H) |
| Deployment context | University clinic of Negros Oriental State University. **LAN-only, plain HTTP, offline.** Actors: clinic administrator, doctors, staff/nurses (designation + station scoped). **Patients, students and guests do not log in** — they are records only. Laravel 10.50.3, PHP 8.3, MySQL 9.7 / MariaDB. |
| Builds on | `audit-2026-04-29.md`, `audit-staff.md`, `module-crud-audit-2026-05-05.md`, `staff-designation-station-access-audit-2026-05-05.md`, `automation-deep-audit-2026-05-18.md`, `full-reaudit-2026-09-30.md` (pass 1, IDs C-/H-/M-/L-), `full-reaudit-2026-09-30-pass2.md` (IDs P2-), `remediation-status-2026-09-30.md`. Those stay as history; §3 records what still holds. |
| Focus | hidden bugs, data loss, data corruption, broken/dead code, incorrect configuration, access control, LAN/offline deployment — and re-verification of every earlier fix |
| Method | Inline code reading of every route file, middleware, provider, controller, repository, service, trait, the key models, Livewire components, migrations, seeders, console commands, config and the relevant Blade/JS — no sub-agents. Every new finding was then **reproduced** with throwaway probe tests on a dedicated `norsu_clinic_test` schema (§9). |

New IDs in this report use the **`R3-`** prefix (third full re-audit) so they never collide with earlier IDs.

---

## 0. How to read this report

Evidence tags:

- **LIVE** — reproduced end-to-end on this commit (HTTP request through the real middleware stack, or the real service/Livewire component) against the `norsu_clinic_test` schema. The observed values are quoted.
- **TOOL** — output of a tool run on this commit (`phpunit`, `composer audit`, `npm audit`, `artisan route:cache/config:cache/view:cache`, `php -l`, a runtime introspection script).
- **CODE** — traced line by line, not executed.

Severity scale (same as the earlier reports):

| Severity | Meaning |
|---|---|
| **Critical** | RCE, unauthenticated access to patient data, or silent destruction/corruption of clinical or inventory records in normal use. |
| **High** | Access-control bypass / privilege misuse, data corruption on a plausible action, medication-safety defects, a broken safety net. |
| **Medium** | Wrong results, broken features, audit/retention gaps, configuration hazards. |
| **Low** | Dead code, hygiene, cosmetic or edge-case defects. |

---

## 1. Scope and verification environment

**Code inventory:** 247 PHP files in `app/` (26,800 lines), 401 Blade views, 117 migrations, 35 seeders, 9 route files (469 registered routes), 19 test files.

**What was run (all on the developer machine, nothing pushed):**

| Check | Result |
|---|---|
| `php artisan route:list` | 469 routes, boots clean |
| `php -l` on every PHP file in `app/ config/ database/ routes/` | **0 syntax errors** |
| `php artisan view:cache` / `route:cache` / `config:cache` | all succeed (caches cleared again afterwards) |
| `vendor/bin/phpunit` (full suite, schema `norsu_clinic_test`) | **OK — 106 tests, 485 assertions** (matches the remediation claim) |
| Audit probe suite (§9) | **16 probes, all reproduce** the defects/claims they describe |
| `composer audit --locked` | 6 advisories in 2 packages + 1 abandoned package (§7) |
| `npm audit` | runtime: 5 (3 high, 2 low); with build tool-chain: 68 (9 critical, build-only) (§7) |
| Backup service (`DatabaseBackupService::createDump`) on the MySQL 9.7 client | **works**: valid `-- MySQL dump` header, `MYSQL_PWD` accepted, `.partial` → final rename OK, path strings compare equal (no self-compare in the "unchanged" check) |
| Offline rule (`OFFLINE_RULES.md`) | **holds** — no CDN, Google Fonts, `ipinfo.io` or other internet asset in views, JS, CSS or SCSS |

**Environment observations made during the audit (not code defects, but read them):**

1. When the audit started, the local `norsu_clinic` database had **9 pending migrations** (the 2026-09-30 schema changes) while the code already depended on them (e.g. stock-in writes `purchased_medicines.batch_id`). At **07:50:24** the database was re-created by a `migrate:fresh --seed` that the audit did **not** run (another process/session on this machine). It is now fully migrated with only the seeded admin. Any clinic copy that is upgraded must run `php artisan migrate` **before** use (see the remediation checklist), otherwise stock-in fails.
2. A `npm run prod` build was running at the start of the audit; `webpack.mix.js` deletes `public/webfonts` and `public/messages.js` (both tracked in git) before every build, so the working tree temporarily showed them as deleted (see R3-L9). The build finished and the tree is clean again.
3. The audit created the schema **`norsu_clinic_test`** (the dedicated test schema documented in `phpunit.xml`); it holds only throwaway test data and can be kept for running the test suite or dropped.

---

## 2. Executive summary

The 2026-09-30 remediation holds up well: **all six pass-1 Critical findings are fixed and re-verified**, the test suite runs and passes, the log viewer is locked down, uploads are validated, strict SQL is on, and the inventory engine (`MedicineInventoryService`) is sound for stock-in, prescription dispensing and manual dispense records. What remains is concentrated in **three areas**: (1) the *consultation-medicine* path, which still bypasses the batch-exact logic the rest of inventory now uses; (2) *prescription control* (who may author, edit and dispense, and what "deactivated" means); and (3) *Livewire components*, where the staff designation/station limits and the role/permission re-check do not apply.

| Severity | New this audit | Earlier findings still open / regressed |
|---|---|---|
| Critical | 0 | 0 |
| High | 6 | 1 (P2-H3 fix ineffective → merged into R3-H6) |
| Medium | 11 | 3 (P2-M1, P2-M3 doc part, P2-M11) |
| Low | 19 | several L-01/L-06/L-09/P2-L3 leftovers |

**The ten things to fix first**

1. **R3-H1** — Editing/deleting a consultation returns its medicines to the *earliest unexpired* batch, not the batch they came from; if that batch has expired the units come back as a **new `RETURN-*` batch dated 2099-12-31 ("never expires")** and become dispensable. LIVE: expired 5 units → `available_quantity` 5.
2. **R3-H2** — The same medicine+dosage can be added twice in a consultation; on edit the rows collapse and **removing a row deducts more stock**. LIVE: intended stock 97 / recorded 3 → actual **94 / 6**.
3. **R3-H3** — A **deactivated** prescription stays in the pharmacy queue and is dispensed. LIVE.
4. **R3-H4** — Prescription authorship is taken from the form: a pharmacist can create a prescription **in any doctor's name** and dispense it; doctor B can **rewrite and re-assign** doctor A's prescription (`update()` lacks the check `edit()` has). LIVE.
5. **R3-H5** — `medicines.available_quantity` is never recalculated when a batch expires: expired stock is shown and offered as available, the low-stock math is wrong, and **there is no "expired stock still on shelf" alert**. LIVE.
6. **R3-H6** — Staff designation limits do not apply inside Livewire components: a nurse sees the full **activity log (patient names, diagnoses, contacts)** and can export it; a certificates-only front-desk account can list **consultations**. The P2-H3 "persistent middleware" fix is **ineffective** (wrong class namespace). LIVE/TOOL.
7. **R3-M1** — Nurses/triage officers get **403** on the medicine list the consultation form needs, so they cannot record nursing-intervention medicines. LIVE.
8. **R3-M5 / R3-M6** — Consultations, certificates, lab requests (even **completed, with results**) and pending prescriptions are **hard-deleted**, and those deletions write **no audit row**. LIVE.
9. **R3-M3** — The dispensing "Stock-out" tab counts **pending** (not yet dispensed) prescriptions as stock out and always shows the patient as "N/A". LIVE.
10. **R3-M2** — Staff/nurse "Profile" saves show *"User profile updated successfully"* but **nothing is saved**. LIVE.

---

## 3. Status of the previous audits (re-verified on `1145357`)

✅ fixed and verified · ⚠️ partly fixed · ❌ still open / regressed · ℹ️ accepted / informational

### 3.1 Pass 1 (`full-reaudit-2026-09-30.md`)

| ID | Claim | Status | Evidence on this commit |
|---|---|---|---|
| C-01 | consultation upload → RCE, public photos | ✅ | `consultation_images.*` = `file\|mimes:jpeg,jpg,png,gif,webp\|max:5120`; stored on private disk `storage/app/consultation_images` with random names; served only through the authenticated `showImage` route with `nosniff`. Defence-in-depth `.htaccess` rules are Apache-only → see R3-M9. |
| C-02 | Livewire RCE + 98 advisories | ✅ | Livewire **v3.8.10**; `composer audit` → 6 advisories left (§7). |
| C-03 | blank allergy wipes patient history | ✅ | `syncPatientProfileFromConsultationData` builds the payload with `array_filter(... !== null)`; regression test passes. |
| C-04 | deleting an in-use medicine cascades | ✅ | `medicineHasHistory()` blocks the delete; FKs recreated as `RESTRICT` (migration `2026_09_30_141000`). |
| C-05 | public `/log-viewer` | ✅ LIVE | guest **401**, staff **403**, admin **200** on `/log-viewer/api/files`. |
| C-06 | `xss` purifier corrupts text / passwords | ✅ | middleware removed from the kernel; `text:repair-entities` command for old rows. |
| H-01 | patient mass-assigns `users.type` | ✅ | `changePassword` updates `password` only; patient portal disabled entirely. |
| H-02 | `?module=` / `document_type` bypass | ⚠️ | route-level fix holds (stored type decides). **List-level bypass through the Livewire table** remains → R3-H6. |
| H-03 | walk-ins auto-merged into wrong patient | ✅ | exact-token name match + DOB + sex; >1 candidate → error; auto-link never rewrites identity. |
| H-04 / H-05 | stock-in edit/delete corrupts ledger | ✅ | `purchased_medicines.batch_id` + `reverseStockIn()` against that batch; regression tests pass. |
| H-06 | patients edit/delete prescriptions | ✅ | `routes/patient.php` not loaded; patient login rejected. |
| H-07 | dispensed prescription editable | ✅ | `update`/`destroy` refuse dispensed prescriptions (small race → R3-L6). |
| H-08 | deleting a doctor deletes prescriptions | ✅ | blocked when prescriptions exist; FK `RESTRICT`. |
| H-09 | backups never run / unsafe | ✅ LIVE | startup script starts `schedule:work`; dump verified on MySQL 9.7; restore takes a `pre-restore` copy and accepts only real dumps. Retention/atomicity notes → R3-L19. |
| H-10 | AJAX errors returned as 500 | ✅ | handler maps exception types to 401/403/404/419/422. |
| H-11 | test suite does not run | ✅ TOOL | 106 tests / 485 assertions pass on MySQL (`_test`-guarded). |
| H-12 | 9,000 history numbers / infinite loop | ✅ | 6-digit space, stored-value uniqueness check, unique index. |
| H-13 | secrets / PHI in git | ℹ️ | `test_search.json` gone; DB password still in **git history** (accepted — rotate it). |
| H-14 | query-builder updates bypass `$fillable` | ⚠️ | patient and self-profile paths use allow-lists; **doctor update still mass-assigns the request** → R3-L1. |
| H-15 | staff binding any user / any role | ✅ | `assertStaffAccount()`; role limited to `staff`/`nurse` by validation. |
| M-01 | prescription dispense record reversal | ✅ | prescription-owned records cannot be edited/deleted from dispensing. |
| M-02 | dispensing ignores dosage | ✅ | `deductStockFefo(..., $line->dosage)`. |
| M-03 | batch-number expiry overwrite / collisions | ✅ | expiry-mismatch guard; random suffix. |
| M-04 | reports read legacy table, ungrouped OR, CSV injection | ✅ | ledger-based, grouped search, `putCsvRow()` neutralises formulas. (Stock-out **tab** is a separate view → R3-M3.) |
| M-05 | audit log overwritten | ✅ | append-only. **Coverage gaps** remain → R3-M5. |
| M-06 | queue preview API never authenticated | ✅ LIVE | nurse → 200. |
| M-07 | admin `medicine-history` redirect 404 | ✅ | `/admin/dispense-records`. |
| M-08 | impersonation leave 403 / CSRF | ✅ | POST to start, leave outside admin group, admins not impersonable. (Actions are logged as the impersonated user → R3-M5.) |
| M-09 | storage configuration | ✅ | `local` disk = `storage/app`; files deleted after commit on **update** (not on delete → R3-M6). |
| M-10 | deployment config traps | ✅ | `FORCE_HTTPS` config flag, `APP_DEBUG=false`, debugbar dev-only. |
| M-11 | performance traps | ✅ | session-cached default-password check, search limits, indexes. |
| M-12 | nurse role half-supported | ✅ | `isRole('staff')` also matches `nurse`. |
| M-13 | soft-deleted users block re-registration | ✅ (staff/doctors) | clear "belongs to an archived account" error; patient create still uses plain `unique` (generic message). |
| M-14 | consultation validation / spoofed creator | ✅ | `document_type in:`; creator = `auth()->id()`. |
| M-15 | lab request not transactional, any user as patient | ✅ | `DB::transaction`; `exists:users,id,type=PATIENT`. |
| M-16 | lock not installable on PHP 8.4 | ℹ️ | not re-tested (audit host is PHP 8.3). |
| M-17 | nurse-in-charge list always empty | ✅ | `where('type', User::STAFF)`. |
| M-18 | doctor create/update rollback, media files | ✅ | rollback + `clearMediaCollection`. |
| L-01 | dead code | ⚠️ | still: `MedicineAvailabilityController`, `StateController`, `getPrescriptionRoute()`, `updateAccountant()` … → R3-L7. |
| L-02 | missing translation keys | ✅ | spot-checked keys exist. |
| L-03 | odd duplicate routes | ⚠️ | extra unnamed `POST countries/{country}`, `POST states/{state}` remain (harmless). |
| L-04 | `requested_at` null crash | ✅ | `?->format()`. |
| L-05 | number generators race | ✅ | unique index on history numbers; availability/lab numbers still check-then-insert (low). |
| L-06 | root one-off scripts, stale `guide.md` | ⚠️ | scripts removed; `guide.md` still documents a non-existent `automation.ps1` → R3-L10. |
| L-07 / L-08 | password on command line / PHI in logs | ✅ | `MYSQL_PWD`; `LOG_LEVEL=warning` drops the `Log::info` request dumps. |
| L-09 | root `.htaccess` absolute `/public/` | ❌ | unchanged → R3-L11. |
| L-10 – L-14 | misc | ✅ / ℹ️ | L-11 doctor-ownership on PDF added; L-12 queue lock added; L-14 admin-only GET utilities remain. |

### 3.2 Pass 2 (`full-reaudit-2026-09-30-pass2.md`)

| ID | Status | Evidence |
|---|---|---|
| P2-C1 production forces HTTPS | ✅ | scheme decided by `config('app.force_https')` only. |
| P2-C2 reset-token leak via log viewer | ✅ | log viewer admin-only (LIVE). Forgot-password itself is a dead end on this deployment → R3-L15. |
| P2-H1 renaming a role locks admins out | ✅ | only `display_name` is editable; `clinic_admin` always keeps every permission. |
| P2-H2 re-seeding duplicates settings | ⚠️ LIVE | no duplication any more, **but re-seeding resets role permissions** → R3-M7. |
| **P2-H3 Livewire per-action authorisation** | ❌ **fix ineffective** (TOOL) | see **R3-H6**. |
| P2-H4 clinical text truncated | ✅ | TEXT columns + strict mode. |
| **P2-M1 logo/favicon absolute URL** | ❌ LIVE | still rendered as `http://localhost/...` to LAN clients → **R3-M4**. |
| P2-M2 intl-tel utils path | ✅ | replaced by the +63 input. |
| **P2-M3 `CACHE_STORE=database` with no cache table** | ⚠️ | `.env.example` is correct, but `RECODE_PROJECT_GUIDE.md` §13.7 still recommends it → R3-L10. |
| P2-M4 – P2-M10 | ✅ | locale validated, generic/category delete guarded, strict mode, daily logs at `warning`, CORS limited to configured origins. P2-M7 (historical destructive migrations) is a fresh-install-only note, unchanged. |
| **P2-M11 `artisan serve` single-threaded** | ❌ | startup scripts still use `php artisan serve` → R3-M9. |
| P2-L1 open self-registration | ✅ | routes commented out. |
| P2-L2 / P2-L4 / P2-L5 | ✅ | |
| **P2-L3 `getBadgeColor()` null user** | ❌ | unchanged (`Auth::user()->dark_mode`) → R3-L13. |

### 3.3 Older audits (04-29, staff, 05-05, 05-18)

| Claim | Status |
|---|---|
| Every `staff/*` route carries `staff.module` | ✅ (route level). Not applied inside Livewire → R3-H6. |
| Designation ↔ station pairing validated | ✅ `ValidStaffDesignationStationPair`. |
| Payment/billing removed | ✅ no payment code paths found. |
| No hardcoded role URLs | ✅ (`getRouteByRole()` everywhere checked). |
| `automation.ps1` consolidated script | ❌ never present; `guide.md` still refers to it (R3-L10). |
| Scheduled `db:backup` | ✅ now started by the startup script (`schedule:work`). |

---

## 4. New High findings

### R3-H1 — Consultation medicine reversals put stock back into the wrong batch; expired units return as "never expires" · High · LIVE
- **Location:** `app/Http/Controllers/DocumentIssuanceController.php:1881-1962` (`restoreMedicineStockAmount`), called from `handleMedicineUpdates()` (edit: removed row or lowered quantity) and `destroy()` (delete). `deductMedicineStock()` (`:1967`) discards the FEFO allocation returned by `deductStockFefo()`; `consultation_medicines` stores no batch or expiry.
- **Why:** the restore searches for the earliest-expiring **unexpired** batch of the same dosage. It never targets the batch the units came from. If none is unexpired, it creates `RETURN-{id}-…` with `expiration_date = 2099-12-31`.
- **Evidence (LIVE):** 5 units stocked in, expiring tomorrow; a doctor's consultation used all 5; three days later the consultation was deleted. Batches afterwards: `BATCH-1-… qty 0 exp 2026-10-02` and **`RETURN-1-… qty 5 exp 2099-12-31`**; `medicines.available_quantity` = **5**. (When a later batch exists, the units are added to that later batch instead, the H-05 problem in a new place.)
- **Impact:** expired medicine is reported as valid and is **dispensable to patients**; FEFO and expiry alerts are wrong for those units. This is the same class of defect H-05/M-01 fixed for stock-in and dispense records, but the consultation path was not converted.
- **Fix:** store the FEFO allocations of each consultation medicine (batch id, dosage, expiry, quantity; e.g. a `consultation_medicine_allocations` table or reuse `medicine_transactions` with `reference_type = DocumentIssuance`) and reverse with `MedicineInventoryService::restoreStock()` against those exact batches, **including expired ones**. Never mint "never expires" stock from a reversal; if the origin is unknown, put it in an *expired/quarantine* batch and flag it.

### R3-H2 — Duplicate medicine rows in a consultation collapse on edit and over-deduct stock · High · LIVE
- **Location:** consultation form JS (`resources/views/document_issuances/forms/consultation_form.blade.php:1576+`, no duplicate guard); store `handleMedicineDeduction()` (`:1999`) keeps every row; edit `handleMedicineUpdates()` (`:1715-1863`) keys both the existing rows and the submitted rows by `"{medicine_id}_{dosage}_{used_for}"`, so duplicates overwrite each other in the maps.
- **Evidence (LIVE):** a consultation with two Ibuprofen 500 mg *plan* rows (3 + 2): stock 95, recorded 5. The doctor removed the second row (meaning 3 used in total). Result: stock **94**, recorded **6** (expected 97 / 3). The untouched first row was never in the map, and the "changed" second row was raised from 2 to 3 with one more unit deducted.
- **Impact:** inventory and the consultation record drift apart on an ordinary edit; repeated edits keep deducting.
- **Fix:** reject duplicate medicine+dosage+used_for rows on the server (as the prescription and dispense paths already do) and in the form; key edits by `consultation_medicines.id` (send the row id from the edit form) instead of a composite string.

### R3-H3 — Deactivated prescriptions remain in the pharmacy queue and are dispensed · High · LIVE
- **Location:** `PrescriptionController::activeDeactiveStatus()` (`:633`) toggles `is_active`; `PrescriptionVerificationTable::builder()` (`:57-83`) lists every `status = pending` row regardless of `is_active`; `medicine-dispensing/columns/verify_action.blade.php` shows the dispense button; `PrescriptionController::dispense()` (`:660`) and `MedicineInventoryService::dispensePrescription()` (`:122`) never check `is_active`. `DISPENSE_STATUS_CANCELLED` exists but nothing ever sets it.
- **Evidence (LIVE):** a doctor switched a pending prescription to inactive (`is_active = false`); a pharmacist then dispensed it → status **`dispensed`**, stock 100 → 95.
- **Impact:** a doctor's only way to stop a prescription (other than deleting it) does not stop the pharmacy. This is a medication-safety defect.
- **Fix:** make "deactivate" mean **cancel**: set `status = cancelled` (and `is_active = false`), exclude non-pending/inactive rows from the verification queue, and refuse `dispensePrescription()` unless `status = pending AND is_active = 1` (checked on the locked row).

### R3-H4 — Prescription authorship and ownership are not enforced · High · LIVE
- **Location:** `PrescriptionController::store()` (`:118`) and `update()` (`:348`) take `doctor_id` from the request for every role; `edit()` (`:254-273`) checks that a doctor owns the prescription but `update()`, `activeDeactiveStatus()` and `dispense()` do not; staff routes (`routes/staff.php:93-102`) give the full prescription CRUD to every designation with the `prescriptions` module (clinic head **and pharmacist**).
- **Evidence (LIVE):**
  - a **pharmacist** posted a new prescription with `doctor_id` = Dr. X → created, attributed to Dr. X (`doctor_id 5`), and printable/dispensable;
  - **doctor B** was refused the *edit page* of doctor A's prescription, then sent `PUT /doctors/prescriptions/{id}` directly → the prescription was rewritten (quantity 5 → 50) and **re-assigned to doctor B** (`doctor_id 3 → 4`).
- **Impact:** prescriptions (with the named doctor's PRC/S2 licence on the PDF) can be created or altered without that doctor, then dispensed. No audit row records it (R3-M5).
- **Fix:** for doctors, force `doctor_id = auth()->user()->doctor->id` on store/update and apply the ownership check in `update/destroy/activeDeactiveStatus`; for staff, either remove create/update (pharmacist = verify + dispense only, as the 05-05 access matrix intended) or require an explicit "on behalf of / verbal order" flag that is logged.

### R3-H5 — Expired stock keeps counting as available; no alert for expired stock on the shelf · High · LIVE
- **Location:** `MedicineInventoryService::syncMedicineTotals()` (`:558`) recomputes `available_quantity` (unexpired only) **only when stock moves**; `app/Console/Kernel.php` schedules nothing but `db:backup`. Readers of the stale column: `MedicineController::getMedicinesByCategory()`, `PrescriptionController::create()` medicine options, `/api/medicines`, the low-stock badge (`layouts/menu.blade.php:40`), the inventory report/CSV, `getMedicineCategory()` fallback. The expiry badges (`menu.blade.php:18-31`, `getExpiringMedicinesCount()`) count only batches expiring **from today on**, so already-expired stock is never alerted.
- **Evidence (LIVE):** 5 units expiring tomorrow; three days later `available_quantity` was still **5**, the medicine was listed by `/api/medicines`, and `deductStockFefo()` then failed with *"Insufficient stock … (unexpired)"*.
- **Impact:** inventory screens, prescription dropdowns and low-stock alerts over-state usable stock every day after any expiry; staff get no prompt to pull expired stock. Also the low-stock badge counts only `available_quantity > 0`, so **out-of-stock medicines are not counted as low stock** at all.
- **Fix:** schedule a daily `inventory:sync-expiry` (call `syncMedicineTotals()` for every medicine with a batch that expired since the last run), or compute availability from `medicine_batches` in the readers; add an "expired stock on hand" badge/report; include `available_quantity = 0` in the low-stock count.

### R3-H6 — Staff designation/station limits and role/permission checks do not apply inside Livewire components (P2-H3 fix ineffective) · High · LIVE + TOOL
- **Location:**
  - `app/Providers/AppServiceProvider.php:67-71` registers `\Spatie\Permission\Middleware\RoleMiddleware` / `PermissionMiddleware` (singular namespace) as Livewire persistent middleware, but the installed **spatie/laravel-permission 5.11.1** only has `Spatie\Permission\Middlewares\…` (plural), which is what `app/Http/Kernel.php` aliases. Livewire matches by exact class string, so they never match.
  - `app/Livewire/ReportGeneration.php` — public `$tab` / `$status`, `setTab()`, `mount()` defaults to `tab = 'logs'`; `EnsureStaffModuleAccess::resolveActivityLogModule()` treats a request **without** `?tab` as "reports" (allowed to every designation) and only `tab=logs|inventory` as "notifications".
  - `app/Livewire/DocumentIssuanceTable.php:17-18` — public, unlocked `$module` and `$patientId`; `builder()` has no authorisation.
  - `ActivityLogController::export()` / `show()` resolve the module the same way (no `?tab` → "reports").
- **Evidence:**
  - (TOOL) for the admin inventory route, Livewire re-applies only `Authenticate`, `SubstituteBindings`, `CheckUserStatus`; `class_exists('Spatie\Permission\Middleware\RoleMiddleware') === false`.
  - (LIVE) a **nurse** (designation without "notifications"): `/staff/activity-logs?tab=logs` → **403**, but `/staff/activity-logs` → **200 showing the activity log** (patient name present), `/staff/activity-logs/export/csv` → full CSV, `/staff/activity-logs/{id}` → 200.
  - (LIVE) a **front-desk clinic-staff** account (certificates only): consultations index → 403, certificates index → 200; setting the table's `module` to `consultation` through a Livewire update returned the consultation rows.
- **Impact:** the designation matrix (05-05 audit) is cosmetic for reports/notifications and for document lists; activity logs carry PHI snapshots (address, contact number, complaints, diagnosis). A role/permission revoked by the admin keeps working inside an already-open Livewire page.
- **Fix:** change the persistent-middleware classes to `Spatie\Permission\Middlewares\RoleMiddleware` / `PermissionMiddleware` (or reference the kernel aliases); mark `ReportGeneration::$tab`, `DocumentIssuanceTable::$module/$patientId` with `#[Locked]` and authorise inside `setTab()`/`builder()` with `canStaffAccessModule()`; resolve the activity-log module from the **effective** tab (default `logs`), and check it in `export()`/`show()` as well.

---

## 5. New Medium findings

| ID | Finding | Location | Evidence | Fix |
|---|---|---|---|---|
| **R3-M1** | **Nurses cannot load the consultation medicine list.** The consultation form fetches `medicines.by.category`, which for staff sits inside the `staff.module:inventory` group. Nurse/triage/records designations have consultations but not inventory, so they get **403** and cannot record nursing-intervention medicines. | `routes/staff.php:118`; `consultation_form.blade.php:1520`, `edit.blade.php:35` | LIVE: nurse (triage) → **403** | Add a read-only `medicines-by-category` route under `staff.module:consultations,inventory` (or move it out of the inventory group). |
| **R3-M2** | **Staff/nurse profile edits are silently discarded.** `UserRepository::updateProfile()` has branches for `clinic_admin`, `patient`, `doctor` only; staff/nurse fall through, nothing is saved, and the controller still flashes success. | `UserRepository.php:211-322`; `UserController::updateProfile` | LIVE: flash *"User profile updated successfully"*, first name unchanged | Add a staff/nurse branch (`fill(Arr::only($input, SELF_PROFILE_FIELDS))->save()` + address). |
| **R3-M3** | **Stock-out tab is wrong.** `used_medicines_view` includes every `sale_medicines` row, so a pending prescription's placeholder lines appear as stocked out; the patient join uses `mb.model_type = 'App\Models\Patient'` (never written), so the patient is always "N/A"; the expiry shown is the earliest unexpired stock-in date for that medicine, not what was dispensed. | migration `2026_05_05_230000_add_dosage_to_used_medicines_view`; `StockOutTable` | LIVE: a pending 7-unit prescription shows as 7 stocked out, patient `N/A`, while stock is still 100 | Rebuild the view (or the tab) from `medicine_transactions` of type `dispense` (+ consultation allocations), join the patient via `medicine_bills.patient_id`, exclude prescriptions not yet dispensed. |
| **R3-M4** | **Uploaded logo/favicon break on other LAN PCs** (P2-M1 still open). `SettingRepository` stores `$media->getUrl()` (absolute, built from `APP_URL`); views use `asset(getAppLogo())`, which returns absolute URLs unchanged. `.env` here has `APP_URL=http://localhost`. | `SettingRepository.php:77-87`, `CMSController.php:57-75`, `helpers.php:47-74` | LIVE: page for host `192.168.1.50` contains `src="http://localhost/uploads/1/logo.png"` | Wrap `getAppLogo()`/`getAppFavicon()` in `normalizeLocalUrl()` (already used for profile photos/sliders/CMS) or store relative paths; set `APP_URL` to the server's fixed LAN address. |
| **R3-M5** | **Audit-trail gaps.** Activity logging exists only for consultation/certificate create+edit, lab requests, stock-in, patient create+edit. Not logged: consultation/certificate **deletion**, prescription create/edit/delete/**dispense**, manual dispense records, medicine create/delete, patient archive/restore (`logPatientDeletion()` is never called), user/staff/doctor management, **password resets**, role/permission changes, settings, backup create/**restore**/delete, logins. Actions done while impersonating are logged under the impersonated user only. | `app/Traits/LogsActivity.php` callers | LIVE: deleting a consultation added **0** log rows | Log every create/update/delete/dispense/reset/restore with actor, and the impersonator (`session('impersonated_by')`). Inventory movements are already traceable in `medicine_transactions`. |
| **R3-M6** | **Clinical records are hard-deleted.** `DocumentIssuance` (consultations, certificates, excuse slips), `LabRequest` (+ items, **including completed requests with results** — edit is blocked for terminal states, delete is not) and pending prescriptions have no soft delete. The consultation `deleting` hook removes image files **before** the surrounding transaction commits. | `DocumentIssuanceController::destroy`, `DocumentIssuance::boot()` `:199`, `LabRequestController::destroy` `:404`, `LabRequest::boot()` | LIVE: completed lab request → row gone | Add `SoftDeletes` (`archived_at`) to these models, restrict permanent delete to the admin, block deleting terminal lab requests, and delete files in `DB::afterCommit()`. Philippine DPA / clinic record retention expects records to be kept. |
| **R3-M7** | **Re-running `php artisan db:seed` resets role permissions** customised in the Roles screen (`RolePermissionsSeeder` does `syncPermissions([])` then re-grants defaults). The remediation note says re-seeding overwrites nothing. | `database/seeders/RolePermissionsSeeder.php:81-97` | LIVE: permission revoked from `staff` in the UI → returned after `db:seed` | Seed role permissions only when the role is first created (or only add missing permissions); document that `db:seed` is for first install. *(Checked and refuted: the seeder does **not** leave direct per-user grants for staff/doctors; revocation works until a re-seed.)* |
| **R3-M8** | **Stale dropdown caches.** Patient, doctor, category and medicine lists for the prescription and dispensing forms are cached 10 min and never invalidated (`active_patients_prescription`, `active_doctors_prescription`, `active_patients_medicine_bill`, `active_doctors_medicine_bill`, `active_medicine_categories`, `medicines_list`); a deactivated doctor stays selectable, new ones are missing. | `PrescriptionRepository.php:67-82, 258-269`; `MedicineBillRepository.php:194-240` | LIVE: new patient missing from `getPatients()` | Drop the caches (the lists are small) or `Cache::forget()` them in the create/update/status paths. |
| **R3-M9** | **Deployment uses `php artisan serve`, which ignores `public/.htaccess`.** The rules that block scripts under `/uploads`, block legacy `uploads/consultation_images`, and disable listings only work under Apache; the PHP built-in server executes any `.php` in `public/` and serves the legacy image folder directly, and it is single-threaded (P2-M11). | `startup-dev-environment.ps1/.bat`; `public/.htaccess` | CODE | Serve through WAMP Apache (document root = `public/`) or another real web server; until then run `consultation-images:secure` and keep `public/uploads` image-only. |
| **R3-M10** | **Excuse slips never appear in the patient history** — `showMyHistory()` passes only consultations and medical certificates to the view. | `PatientController.php:202-235`; `patients/view_patient.blade.php` | LIVE: history shows `consultation_form`, `medical_certificate` only | Pass and render excuse slips (or all non-consultation documents). |
| **R3-M11** | **Default passwords are not enforced for doctors/staff.** Admin resets set the well-known `123456`; only the clinic admin is forced to change it (`forceAdminPasswordChange`); doctor/staff dashboards just open a dismissible dialog. | `UserController::resetPassword`, `StaffController::resetPassword`, `ForceAdminDefaultPasswordChange`, `doctor_dashboard/index.blade.php:19` | CODE | Generate a random one-time password and force a change for every account type (`must_change_password` flag). |

---

## 6. New Low findings

| ID | Finding | Location |
|---|---|---|
| R3-L1 | Doctor update still mass-assigns the whole request through `$fillable` (`email_verified_at`, `password`, `dark_mode`, … settable with a crafted request); `deletedQualifications` deletes **any** qualification ids (not scoped to the doctor) and qualification JSON is written with arbitrary keys. | `UserRepository.php:154-209`, `addQualification` `:347` |
| R3-L2 | Profile e-mail regex `[a-zA-Z]{2,4}$` rejects real domains with longer TLDs (`.online`, `.school`, `.local`). LIVE: `staff1@test.local` → "The email field format is invalid." | `UpdateUserProfileRequest.php:39` |
| R3-L3 | A role cannot be left with **no** permissions: unticking all sends no `permission_id`, so nothing is synced. | `RoleRepository::update` |
| R3-L4 | Patient activity rows always have empty college/course/year level/address: the trait reads `->name` / `->address` but the columns are `college_name`, `course_name`, `year_level_name`, `address1`. | `LogsActivity.php:98-102, 243-247` |
| R3-L5 | Stock added through the medicine form ("Initial / Additional Batch Stock") bypasses the Stock-In register: no procurement document, no activity row, cannot be edited or reversed from Stock-In. | `MedicineController::recordStockInIfProvided` |
| R3-L6 | Prescriptions: duplicate check is by medicine id only (two strengths of one medicine cannot be prescribed together); `update()` checks "dispensed" without a row lock (tiny race with a concurrent dispense). | `PrescriptionController.php:128, 365` |
| R3-L7 | Dead / broken code: `/api/medicines` (unused; dosage availability read from stock-in paperwork, not batches); `medicine-history/index.blade.php` mounts the deleted `medicine-bill-table`; the add-patient modal has no trigger and its endpoint `storePatient()` calls `createNotification()` → undefined `addNotification()` after creating the patient; `PrescriptionRepository::getPatients()` branch `hasRole('Doctor')` → undefined `getPatientsList()`; `MedicineAvailabilityController`, `StateController` (unrouted); `getPrescriptionRoute()` (non-existent route names); `MedicineAvailabilityRepository::updateAccountant()` (undefined class/helpers) and `getCategoryList()` (always returns `[]`); empty `app/Console/Commands/SyncRolePermissions.php`; two commands both named `cache:warmup`. | various |
| R3-L8 | Lab-request PDF download builds the file name from the free-text patient name; a `/` or `\` makes Symfony `HeaderUtils::makeDisposition()` throw → HTTP 500 (no try/catch). | `LabRequestController::exportPdf` `:502` |
| R3-L9 | `webpack.mix.js` deletes `public/webfonts` and `public/messages.js` before every build although both are tracked in git (`/public/messages.js` is also listed in `.gitignore`); an interrupted/failed build leaves the app without icon fonts and JS translations and shows tracked files as deleted. | `webpack.mix.js:17-31`, `.gitignore` |
| R3-L10 | Stale docs: `guide.md` describes a non-existent `automation.ps1`; `RECODE_PROJECT_GUIDE.md` §13.7/§14 still recommends `CACHE_STORE=database` + `QUEUE_CONNECTION=database` (no cache/jobs table → every request 500); `CLAUDE.md` is empty. | repo root |
| R3-L11 | Startup script prints `http://127.0.0.1:8000` as reachable but `artisan serve` binds only the detected LAN IP; root `.htaccess` rewrites to an absolute `/public/` (breaks in a sub-folder, L-09). | `startup-dev-environment.ps1:195-216`, `.htaccess` |
| R3-L12 | The custom exception handler redirects validation failures with `withInput()` of the **whole** request, bypassing `$dontFlash` — password fields are flashed into the `sessions` table for one request; only the first error message is shown. | `app/Exceptions/Handler.php:72-74` |
| R3-L13 | `getBadgeColor()` dereferences `Auth::user()` without a null guard (P2-L3). | `helpers.php:256` |
| R3-L14 | Lab-request search maps `patients.patient_type_id` to `User::STATUS_AFFILIATION` by numeric id; correct only while seed ids match (student 1, staff 2, faculty 3, guest 4). Same assumption in `CreatePatientRequest` (`patient_type_id != '4'` = guest). | `LabRequestController.php:552`, `CreatePatientRequest.php:52` |
| R3-L15 | "Forgot password" is a dead end on this deployment (`MAIL_MAILER=log` + `LOG_LEVEL=warning` → the reset mail is never visible) and its response reveals whether an e-mail exists. Admin-initiated reset is the real path; consider hiding the link. *(Mail findings were out of scope in the last remediation — informational.)* | `routes/auth.php:26-40` |
| R3-L16 | `SettingController::index` renders `view("setting.$section")` from an unvalidated `?section=` (admin only) → 500 for unknown values; `getCities($states)` is passed an array. | `SettingController.php:44-51` |
| R3-L17 | Sessions on shared clinic PCs stay valid for 120 idle minutes and survive closing the browser (`expire_on_close = false`). Consider a shorter `SESSION_LIFETIME` and `expire_on_close = true` for front-desk machines. | `config/session.php` |
| R3-L18 | Queue: `callNext()` checks status without a lock (two doctors can call the same patient); waiting entries from previous days never auto-close and keep counting in the sidebar badge. | `PatientQueueController.php:216-232` |
| R3-L19 | Backups: retention keeps at most 30 days (no weekly/monthly copy); dumps hold unencrypted PHI; a least-privilege DB user (recommended by `.env.example`) also needs `SHOW_ROUTINE`/`EVENT` for `--routines --events`; a restore that fails half-way leaves a half-restored DB (the `pre-restore` copy must then be uploaded manually). `consultation-images:secure` moves files by default (others default to a dry run); `text:repair-entities --apply` must not be run twice. | `DatabaseBackup.php`, `DatabaseBackupService.php`, `app/Console/Commands/*` |

---

## 7. Dependencies (TOOL)

**Composer (`composer audit --locked`): 6 advisories, 2 packages, 1 abandoned.**

| Package | Advisory | Exposure in this app |
|---|---|---|
| `laravel/framework` 10.50.3 | debug-page XSS (low), temporary signed-URL path confusion (medium), CRLF in default `email` rule (high, ×2) | Low: debug off, no temporary signed URLs, no outgoing mail. Fixed only in Laravel 12 (needs PHP 8.2+). |
| `spatie/laravel-medialibrary` 10.x | CVE-2026-48557 upload-restriction bypass (`FileAdder::defaultSanitizer`, double extension `x.php.jpg`, missing `.php6/.shtml/.htaccess`); CVE-2026-48555 SSRF (URL uploads) | Low: every upload is validated by Laravel `mimes:` before `addMedia()`; double-extension execution needs a legacy Apache `AddHandler`; `artisan serve` only executes files *ending* in `.php`; no URL uploads are used. Upgrade to ≥ 11.23 with the PHP 8.2+/Laravel upgrade. |
| `laravelcollective/html` | abandoned | used by ~90 forms; no CVE. |

**npm:** runtime dependencies (`npm audit --omit=dev`) → **5** (3 high: `nanoid`, `postcss`, `quill 2.0.3` HTML-export XSS — Quill is only the admin CMS editor whose output is purified server-side). Including the Laravel-Mix/webpack build tool-chain → **68** (9 critical), all build-time only and never shipped to browsers. `npm audit fix` for the runtime set; plan a Vite migration for the tool-chain.

---

## 8. Verified correct (so it is not re-flagged)

- **Inventory engine** (`MedicineInventoryService`): stock-in with expiry-mismatch guard and random batch suffixes; FEFO deduction excludes expired batches and honours dosage; prescription dispense locks the prescription row and records the real batch/expiry per line (duplicate medicines are rejected at prescription save, so the allocation map cannot collide); manual dispense records store FEFO allocations and reverse them to the exact batch; stock-in edit/delete reverses the batch the line created and refuses when units were already dispensed.
- **Access control at route level:** every `staff/*` route has `staff.module`; document routes authorise against the stored type; impersonation, staff/doctor/patient account scoping, role allow-list, log viewer, queue API — all hold.
- **Data integrity:** consultation save never clears patient history; auto-link needs an exact name; clinical text columns are `TEXT` and strict SQL is on; medicine/doctor/category/generic/specialisation/location deletes are blocked while in use; FK `RESTRICT` on prescriptions and medicine history.
- **Errors/config:** exception handler status codes; `FORCE_HTTPS` flag; `APP_DEBUG=false`; daily logs at `warning`; CORS empty by default; session cookie not secure-only (correct for HTTP LAN); login rate-limited (5/min per e-mail+IP), patient logins rejected, session regenerated.
- **Build/runtime:** all 401 Blade views compile; routes and config cache; 0 PHP syntax errors; full test suite green; backups work on the installed MySQL 9.7 client; no internet assets.
- **CSRF:** all write routes are POST/PUT/DELETE in the `web` group; the `/csrf-token` recovery endpoint is same-origin only.

---

## 9. Reproduction notes (probe suite)

All probes ran on the dedicated schema `norsu_clinic_test` (`RefreshDatabase`, guarded by the `_test` name check), using the project's own `Tests\Concerns\BuildsClinicData` fixtures and `Carbon::setTestNow()` for expiry scenarios. The probe file was kept outside the repository; the result was **`OK (16 tests, 51 assertions)`** — every probe passes because the behaviour it asserts was observed.

| Probe | Observed |
|---|---|
| consultation deleted after its batch expired (R3-H1, R3-M5) | batches `[BATCH qty 0 exp 2026-10-02, RETURN qty 5 exp 2099-12-31]`; available 5; 0 audit rows |
| deactivated prescription dispensed (R3-H3) | status `dispensed` |
| doctor B updates doctor A's prescription (R3-H4) | `doctor_id 3 → 4`, quantity 50 |
| pharmacist creates prescription for Dr. X (R3-H4) | created with Dr. X's `doctor_id` |
| expired stock still available (R3-H5) | `available_quantity` 5 three days after expiry; listed by `/api/medicines`; FEFO refuses |
| nurse reads activity logs (R3-H6) | `?tab=logs` 403; no tab 200 with PHI; CSV export 200; detail 200 |
| certificates-only staff lists consultations (R3-H6) | builder returns `["CONSULTPROBE Patient"]` after `module` switch |
| Livewire persistent middleware (R3-H6) | re-applied: `Authenticate`, `SubstituteBindings`, `CheckUserStatus` only |
| nurse loads consultation medicine list (R3-M1) | 403 |
| duplicate consultation rows edited (R3-H2) | stock/recorded `[95,5] → [94,6]` (intended `[97,3]`) |
| staff profile update (R3-M2) | "User profile updated successfully", name unchanged |
| stock-out view with a pending prescription (R3-M3) | 1 row, 7 units, patient `N/A`, stock still 100 |
| logo for a LAN client (R3-M4) | `src="http://localhost/uploads/1/logo.png"` |
| completed lab request delete (R3-M6) | row removed |
| re-seed after UI revocation (R3-M7) | permission back on the role |
| new patient vs cached dropdown (R3-M8) | missing; `addNotification()` undefined |
| excuse slip in patient history (R3-M10) | not shown |
| re-verification C-05 / M-06 | log viewer 401/403/200; queue API 200 |

---

## 10. Suggested fix order

1. **Medication & inventory safety (now):** R3-H1, R3-H2, R3-H3, R3-H5 — then run `inventory:reconcile` and correct any `RETURN-*` batches dated 2099-12-31 that came from consultations.
2. **Prescription control & access:** R3-H4, R3-H6 (one-line namespace fix + `#[Locked]` + in-component checks), R3-M1.
3. **Records retention & audit trail:** R3-M6 (soft deletes, block deleting completed lab requests), R3-M5 (log deletes, dispensing, resets, role/settings/backup changes, impersonator).
4. **Correct results:** R3-M3 (stock-out tab), R3-M10, R3-M2, R3-M8, R3-M4 (+ set `APP_URL` to the server's fixed LAN IP).
5. **Deployment & operations:** R3-M9 (serve through Apache with document root `public/`), R3-M11, R3-M7 (make role seeding first-install only), R3-L19, R3-L10/L11 docs and scripts.
6. **Hygiene:** remaining Low items; dependency upgrades with the planned PHP 8.2+/Laravel 12 move (§7).

After each fix, add the corresponding probe from §9 as a permanent regression test (the existing `tests/Feature/Regression` suite already follows this pattern).

---

## 11. Remediation status — staff/nurse + doctor workflow (Patients, Queue, Consultations, Prescriptions, Lab requests, Stock-out)

Worked inline (no sub-agents), test-first. Each fix has a regression test in `tests/Feature/Regression/`
(`PatientModuleTest`, `PatientQueueTest`, `ConsultationInventoryTest`, `ConsultationAccessTest`, `StaffProfileTest`, `PrescriptionControlTest`, `LabRequestDeleteTest`, `InventoryExpiryTest`, `StockOutTabTest`). Full suite after the
work: **OK (183 tests)**. Decisions taken with the clinic owner: doctors may **view and edit** patients but not add,
archive, restore or reset them; the queue stays **one shared queue**; a consultation can be deleted only by the
**clinic admin or the person who recorded it**, and every deletion leaves an audit snapshot.

| ID | Status | What changed |
|---|---|---|
| R3-H1 | ✅ fixed | Consultation edits/deletes return medicines to the batches they came from (read from the stock ledger, last-handed-out first), even when that batch has expired. Untraceable legacy units go to an unexpired batch if one exists, otherwise to an already-expired `RESTORE-*` batch — never a "2099" batch. `MedicineInventoryService::restoreStockToReferencedBatches()`. |
| R3-H2 | ✅ fixed | A duplicated medicine + strength in one section is refused (server and browser). Edit rows carry their row id; rows are matched by id, then by medicine + strength + section. Stock is returned before it is taken out, and instruction-only edits are saved. |
| R3-H5 | ✅ fixed | New `inventory:sync-expiry` (daily 00:05, `--dry-run` available) refreshes `available_quantity` after batches expire; the sidebar also does it once a day when a page is opened, in case the scheduler is not running. The consultation medicine list ignores expired batches. Sidebar: new black *Expired stock on the shelf* badge; the low-stock badge now counts medicines that ran out (not never-stocked catalogue entries). |
| R3-H6 | ✅ fixed (these screens) | Spatie middleware registered under the right namespace (`Middlewares`); `DocumentIssuanceTable::$module/$patientId` are `#[Locked]` and the list is authorised by module; the activity-log screen, its export and detail page apply the designation limit per tab (nurse without "notifications" no longer reads the raw log; the Activity Logs tab falls back to Patient Visits). |
| R3-M1 | ✅ fixed | `staff.medicines.by.category` is now a read-only route under the consultations/inventory modules. |
| R3-M5 | ⚠️ partly | Now logged: patient archive / restore, consultation / certificate / excuse-slip deletion (snapshot), **prescription** create / edit / cancel / reactivate / dispense / delete (with who entered it for the doctor), **lab request** deletion (snapshot). Still open: manual dispense records, user / staff / doctor management, role & permission changes, settings, backups, impersonator. |
| R3-M6 | ⚠️ partly | Consultation deletion limited to admin + creator; photo files deleted only after commit. **Lab requests:** completed / referred / rejected can never be deleted, collected / processing must be cancelled first, pending / cancelled only by the creator or admin. Consultations and certificates are still hard-deleted (the audit snapshot is the safety net). |
| R3-M8 | ✅ fixed | Patient / doctor pick-lists are dropped whenever a patient or user is saved, archived or restored. |
| R3-H3 | ✅ fixed | Switching a prescription off now **cancels** it (`status = cancelled`, `is_active = false`); switching it on returns it to pending. The pharmacy queue hides inactive prescriptions and `dispensePrescription()` refuses them (checked on the locked row). A dispensed prescription cannot be switched. The prescription list now has visible *Cancel* / *Reactivate* buttons (plain form posts, no JS rebuild needed); before, the list had no way to switch a prescription off. |
| R3-H4 | ✅ fixed | A doctor's own id is forced on create and edit, and a doctor cannot update, cancel, dispense or delete another doctor's prescription. **Staff may write or change a prescription only on a recorded verbal / phone order** (required checkbox, validated on the server, logged with who entered it); staff cannot delete (they cancel). The clinic admin may write for any doctor (logged). |
| R3-M3 | ✅ fixed | `used_medicines_view` is rebuilt from the stock ledger (migration `2026_10_01_170000_...`): only real movements, the real patient, the batch's own expiry, and negative rows for units returned. **Run `php artisan migrate`.** |
| R3-M10 | ✅ fixed | Excuse slips appear in the patient history. |
| R3-M2 | ✅ fixed | Staff / nurse "My profile" now saves (it had no branch and still said "updated"); an account with no editable profile now gets a 422 instead of a false success. `UserRepository::updateProfile()`. |
| R3-L2 | ✅ fixed | Profile e-mail rule accepts top-level domains longer than 4 letters (`.school`, `.local`, `.online`). |
| R3-L4 | ✅ fixed | Patient audit rows now record college, course, year level and address. |
| R3-L18 | ✅ fixed | Queue: calling/completing is a single conditional UPDATE; entries left over from earlier days are closed automatically; the sidebar badge counts today only. |

New defects found and fixed while doing this pass (not in the list above):

- Doctors had the full Patients CRUD (add / archive / restore / reset password) → routes and buttons removed for doctors.
- **Queue screens returned HTTP 500 for everyone** once a queued patient had been archived (or the staff member who queued them was archived) → entries of archived patients are closed on archive and hidden; views are null-safe.
- Archived or deactivated patients could be put in the queue → refused.
- A completed/cancelled queue entry could be reopened from a stale edit page → refused.
- A consultation recorded after the patient was queued never reached the doctor's queue screen → attached to the open queue entry.
- The staff "Reset password" key on the patient list always answered 403 (it pointed at the admin-only route) → shown to the admin only.

Not done in this pass: R3-M4 (logo / favicon URLs), R3-M7 (re-seeding resets role permissions), R3-M9 (artisan serve vs Apache), R3-M11 (default passwords) and the Low items not named above.

**Live check (browser, isolated copy on the throwaway `norsu_clinic_test` schema, nothing touched in `norsu_clinic`):**
front-desk staff registered a patient through the real form and put her in the queue; the doctor saw her, pressed *Call Next*, saved a consultation with a medicine and then lowered its quantity (stock went 20 → 16 → 17 in the same batch, ledger rows `dispense 4` / `adjustment 1`); the doctor's patient list had no Add / archive / restore / reset buttons; the edit form sent the saved row id; the duplicate-medicine guard blocked a repeated row in the browser; a staff profile edit persisted; the nurse's medicine list returned 200 and her Reports screen opened on *Patient Visits* with `?tab=logs` answering 403.

**Deploy notes for the clinic copy:** run `php artisan migrate` (rebuilds the Stock-out view), `npm run prod` (the Cancel / Reactivate buttons do not need it, but the prescription status switch script was improved), and once `php artisan inventory:reconcile` to list any `RETURN-*` / `2099-12-31` batches that the old consultation code created - those units need their real expiry checked on the shelf.

**Live check, second round:** the doctor's prescription form showed a locked own-name doctor field; the pharmacist's showed the required verbal-order box; the doctor cancelled a prescription from the list; the pharmacy queue then listed only the pending one; dispensing it produced a correct Stock Out row (medicine, strength, quantity, *Prescription*, patient).

---

## 12. Update 2026-10-02 - remaining items

Closed in the follow-up pass (details, tests and the route x role audit in `docs/route-access-audit-2026-10-02.md`):
**R3-M4** (logo URLs), **R3-M5** (audit trail for administrative changes, sign-in/out, impersonation), **R3-M6** (consultations / certificates / excuse slips are soft-deleted: hidden everywhere, row and photos kept),
**R3-M7** (re-seeding no longer resets roles), **R3-M11** (default password gate for doctors and staff), **R3-L1, L8, L9, L12, L13, L14, L16**.
New defects found and fixed in that pass: `staff.module:a,b` middleware only checked the first module (certificates-only staff could not search patients, the pharmacist could not load the medicine list), the public doctors page crashed for a doctor without specialisation, `?module=certificates` showed the wrong list, auth pages redirected doctors / staff to the admin dashboard, settings save without a section returned a server error.
Still open: R3-M9, R3-L5, L6, L7, L10, L11, L15, L17, L19.
