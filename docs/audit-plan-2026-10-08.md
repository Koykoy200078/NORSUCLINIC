# Medical University Clinic — Audit & work plan (2026-10-08)

**Status:** Phases 0–7, the Phase 5.10 report follow-up and Phase 8 improvements **8.1–8.6** are complete. **Next:** Phase 9 final verification, including runtime / second-device LAN checks. **Branch:** `changes_v2` (base `develop`). Details and checkpoints are in [Handover.md](../Handover.md).
**Plan re-audited:** 2026-10-08, corrections listed in [§0](#0-plan-re-audit-2026-10-08)

**Rules for this work:** inline checking only (no agent workflows) · test-first (failing test → fix → re-verify) · ask when
something is unclear · each verified phase is committed and pushed to `changes_v2` (the owner's standing go-ahead of 2026-10-09; never force-push, never merge into `develop` without asking) · every module is checked twice (code pass + live pass),
then everything is re-checked once more at the end. **Two AIs (Claude Code and OpenAI Codex) share this work**: before
starting, read `git log origin/changes_v2` and [Handover.md](../Handover.md). Write a START entry there when beginning and the END
part only when the work is pushed, nothing in between.

Legend: ✅ done and verified · 🟡 partly done / unfinished · ⏳ pending (known, not started) · 🆕 new (found / requested
2026-10-08, not started) · 🔒 needs the owner's decision · ℹ️ by design / accepted · 💡 suggestion for later

This plan builds on [STATUS.md](STATUS.md) (the running status record) and every earlier audit (sources in [§10](#10-sources)).
**The commands this plan adds** (`db:integrity`, `db:restore-foreign-keys`, ...) are logged, with where and when to run each,
in [commands.md](commands.md).
The final project name is **Medical University Clinic** (replaces "NORSU Clinic" in what users see; technical names such as
the `norsu_clinic` database and the repository folder stay).

**How the system runs (owner, 2026-10-08):** production runs on **one clinic server PC** (web server, MySQL, backups and
later the Reverb server); staff, nurses and doctors use it from **their own devices on the same LAN** through a browser. So:
- links and assets must work from any LAN address, never only `localhost`;
- the server PC's app port (and later the Reverb port) must be reachable on the LAN;
- the database stays on the server PC.

The network is the clinic's **local network** (LAN), not air-gapped. "Offline" means **no page
needs the internet to load**: no CDN, no third-party script / style / font / image links. Every library is packaged locally (npm +
Laravel Mix into `public/`, or `public/assets` / `public/vendor`). The real-time server (Reverb) also runs on the LAN.

**Databases on the development laptop (owner, 2026-10-08):** `norsu_clinic` is the **only working database**: its data is
experimental. It holds the clinic dump, is used for live checks, and upgrade rehearsals re-import the dump **into it**. The
automated tests wipe their database on every run, so they create and use their **own throwaway schema automatically** (no
manual setup) and never touch `norsu_clinic`.

---

## 0. Plan re-audit (2026-10-08)

Every factual claim of the first version was checked again against git, the code, the live database and the clinic dump.

| Claim in the first version | Checked against | Result |
|---|---|---|
| "last pushed docs commit `fd1b43a`" | `git log origin/changes_v2` | ❌ **Wrong**: everything up to `327aabc` is pushed. STATUS.md §1 / §8 ("committed 10-08, not pushed") is stale too; it gets fixed in Phase 10 |
| "338 tests" | test files, then the Phase 0 baseline run (2026-10-09) | ❌ **Stale**: the owner's 3 commits added 28 tests (16 + 6 + 6) = **366**; with the 11 tests added in Phase 0.2 the suite has **377** (3,994 assertions, 1 skipped) |
| "about half of the 14 permissions have no screen for doctor / staff" | `routes/*.php`, `RoleRepository::getPermissions()` | ✏️ **Made precise**: the Roles form shows 13 (it hides `manage_admin_dashboard`); **8 of them do nothing for staff and 9 for doctors** |
| "Illness / services migration fails on an InnoDB server" | reasoning only → rehearsal R0 (Phase 0.5, 2026-10-09) | ✅ **Confirmed**: error 1824 "Failed to open the referenced table 'document_issuances'" at `2026_10_02_090000`, leaving a half-built schema; on a MyISAM default the migration succeeds but creates MyISAM tables without foreign keys |
| "Unreachable staff route groups (May finding C)" | `routes/staff.php` | ✅ **Already fixed**: no settings / roles / countries / CMS groups remain; the plan item is removed |
| "PHP 8.2.26 at the clinic" | dump header | ✏️ That is phpMyAdmin's PHP on the clinic PC (almost certainly the same WAMP PHP). Confirmed on site before Phase 8 |
| "local server was WAMP MySQL 8.4 without a password" | earlier notes only | ✏️ Worded as "earlier notes"; only the current server (MySQL 9.7.1, password set) is verified |
| "Patients has 8 buttons in one cell" | `patients/components/action.blade.php` | ✏️ Up to 8 links depending on role and state |
| "8 consultations" | dump | ✅ all 8 `document_issuances` are consultation forms |
| ledger = consultation medicines; Stock-out = 6 dispenses + 1 return | dump rows 62–69 | ✅ |
| MyISAM (59 tables), 0 foreign keys vs 47 on a fresh install | dump + `information_schema` | ✅ |
| Reverb works with Laravel 10.50 | Packagist | ✅ latest Reverb 1.12.0 needs PHP ^8.2 and `illuminate ^10.47 … ^13` |
| `notifications` table can back a notification bell | fresh schema | ✅ it is a custom table (`title`, `type`, `read_at`, `user_id`) |

---

## 1. Where things stand (organised)

### 1.1 What happened most recently

| When | What | Result |
|---|---|---|
| 2026-09-30 → 10-02 | Full re-audits (pass 1, pass 2, R3), remediation, Accomplishment Report, route/access audit, "Unfinished" pass, Queue ↔ consultation fixes | ✅ on `origin/changes_v2` |
| 2026-10-02 evening | "Pending" cleanup (R3-L5 / L6 / L7 / M9) | ✅ `d427976` |
| 2026-10-08 | Landing-page Dashboard 403 + link audit (14 role-blind links) | ✅ `d8dd314` |
| 2026-10-08 | Owner loaded the **real clinic dump** (`Downloads/10082026.sql`, MySQL 9.1.0, at migration 108) and hit errors upgrading it. Owner's commits `14d3793`, `be792ac`, `327aabc` made the 15 newer migrations tolerate zero dates (`App\Support\LegacyMysqlMigration::withoutZeroDateChecks`) and read through the write connection (+28 tests) | 🟡 partial; root cause in §1.3. All pushed |
| 2026-10-08 | Owner reported: Dispense History misses consultation medicines; Settings › Manage User roles changes do not apply | 🆕 root causes in §1.4, §1.5 |
| 2026-10-08 | Owner asked for: all modules re-checked, ⋮ action menus, WebSockets instead of timers, deep DRY refactor + dead-code removal, improvements, this ordered checklist | 🆕 planned |
| 2026-10-08 | Owner asked for: project name **Medical University Clinic**; College of Law **CL → COL**; **Guest** column; **yearly** Accomplishment Report; **yearly Medicine Inventory** report (left from last year, additions, Q1–Q4 remaining); **staff = nurse**, so remove Role Designation, Assigned Station and Shift Schedule | 🆕 planned (Phases 3 and 4) |

### 1.2 Environment (verified 2026-10-08)

| Item | Finding | Impact |
|---|---|---|
| Database server | **MySQL 9.7.1** with a root password (earlier notes: WAMP MySQL without a password) | `.env` updated by the owner; memory notes updated in Phase 10 |
| `.env` | `APP_ENV=production`, `APP_DEBUG=false`, `DB_STRICT=true`, `APP_NAME="NORSU Clinic"` | ✅ name changed in Phase 4.1 (the development `.env` says "Medical University Clinic"; the clinic copy's `.env` is edited by hand, §9) |
| Test schema | `norsu_clinic_test` (named in `phpunit.xml`) **did not exist** on this server | ✅ since Phase 0.2 the test run creates its own throwaway schema automatically; no manual database setup |
| `norsu_clinic` | *On 2026-10-08:* fresh install: 1 admin, no clinical data, 123 migrations, all InnoDB, 47 foreign keys. *Since Phase 1.8 (2026-10-09):* the **upgraded clinic dump**: 12 real users + 4 QA accounts, 126 migrations, all InnoDB, 46 of 47 foreign keys | the only working database |
| PHP | 8.3.31 locally; dump header says 8.2.26; `composer.json` platform pin = 8.1.0 | ✅ Reverb needs ≥ 8.2: `composer.json` now requires `^8.2`, platform pin 8.2.0 (Phase 7, 2026-10-19) |
| Framework | Laravel 10.50.3, Livewire 3.8.10, spatie/laravel-permission 5.11.1 | ✅ |

### 1.3 Clinic-data upgrade: root cause (🆕)

The clinic dump: 12 users, 7 patients, 8 consultations, 5 consultation-medicine lines, 52 medicines, 57 batches, 69 stock-ledger
rows (the ledger matches the consultation medicines exactly), 1 pending prescription, 6 queue entries.

1. **All 59 clinic tables are `ENGINE=MyISAM`.** WAMP's `my.ini` sets the default engine, and `config/database.php` has
   `'engine' => null`. As a result:
   - **0 foreign keys** at the clinic (a fresh install has 47). `2026_09_30_130000` / `141000` find no key and silently
     skip, so the clinic schema drifts from a fresh install;
   - **every safeguard that relies on transactions or row locks does nothing at the clinic**: FEFO stock deduction
     (`lockForUpdate`), stock restores, queue call-next race, unique-number retry, all-or-nothing saves;
   - `2026_10_02_090000_create_illness_and_service_tables` creates tables with foreign keys to `document_issuances`.
     **Confirmed by rehearsal R0 (Phase 0.5):** on a server whose default engine is InnoDB it fails with error 1824 and
     leaves a half-built schema; on a MyISAM server it creates MyISAM tables without foreign keys;
   - earlier rehearsals built the old schema through our own migrations (InnoDB), so they never saw this.
2. **Zero date:** `sale_medicines.expiry_date = '0000-00-00 00:00:00'` (placeholder line of the prescription mirror).
   `121000` makes the column nullable, but the zero value stays.
3. The owner's zero-date wrapper is still needed while tables are rebuilt; it is reused, not replaced.

*All three are fixed by Phase 1 (migrations `2026_09_30_110000`, `121500`, `2026_10_08_120000`, config `DB_ENGINE`) and
rehearsed on the real dump under both engines.*

### 1.4 Dispense History misses consultation medicines (✅ fixed in Phase 2)

`app/Livewire/MedicineDispenseTable.php` lists only `medicine_bills` (manual dispense records + dispensed prescriptions).
Medicines added in a consultation by the nurse ("Nursing") or the doctor ("Plan") go to `consultation_medicines` and the
stock ledger (`DocumentIssuanceController::handleMedicineDeduction` / `handleMedicineUpdates`), never to `medicine_bills`, so
they appear only on the Stock-out tab.

### 1.5 Manage User roles "does not apply" (✅ fixed in Phase 3)

Saving works (the role's `saved` event clears the Spatie cache; the file cache is kept per database). What breaks the
owner's expectation:

- the Roles form shows 13 permissions; **8 do nothing for staff and 9 for doctors** (no route uses them for that role);
- `manage_staff_dashboard` is checked nowhere; Reports, Notifications and the dashboards ignore permissions; the doctor's
  Queue routes are not permission-gated although the menu hides the Queue item;
- edits to **Clinic Admin** are silently ignored; the **Patient** role cannot sign in;
- the **Nurse role has no accounts** (and an empty name in the clinic data): nurses are `Staff` accounts with the Nurse
  designation, so editing the Nurse role does nothing;
- staff are further limited by the hard-coded designation / station maps in `app/helpers.php`, which the Roles page never
  mentions. **The owner's decision to remove designations and stations (§2.1) removes this whole layer.**

### 1.6 Other facts used by this plan

- **Duplicate medicines in the clinic data:** #41 = #42 (Kremil-S 178/233/30 mg), #50 = #55 (Laradin / loratadine 10 mg, #55
  already dispensed); near-duplicate categories ("Antihistamine" / "Antihistamines").
- **Row actions:** 23 different action views (up to 8 links in one Patients cell); `components/crud/action-buttons.blade.php`
  exists but is barely used.
- **Auto-refresh today:** `setInterval` / `setTimeout` fetch loops in `patient_queue/index` and `patient_queue/doctor_view`,
  `wire:poll.30s` in Report Generation.
- **Consultation "Add medicines":** a plain `<select>` grouped by category showing the brand name only, duplicated in
  `document_issuances/forms/consultation_form.blade.php` and `document_issuances/edit.blade.php`. Select2 is already bundled.
- **Name:**
  - "NORSU Clinic" / "Norsu Clinic" is in `APP_NAME`, the `clinic_name` setting (clinic value "Norsu Clinic"), landing
    pages, PDFs (certificates, excuse slips, lab requests, dispense records), the registration e-mail, `lang/en/messages.php`
    and seeders;
  - "Negros Oriental State University" (12 places) stays.
- **College of Law** is stored as `College of Law (CL)` (the report takes the code from the brackets); seeded by `CollegeSeeder`.
- **Guests** column of the Accomplishment Report appears only when it has data (`AccomplishmentReportBuilder` L63).
- **Report Generation › Inventory** tab lists current stock only (`ReportQueries::inventory`); there is no yearly view.
  The stock ledger keeps `balance_after` per batch with dates, so a year / quarter balance can be rebuilt.
- **Staff designation / station / shift** touch 22 files, `canStaffAccessModule()` is called 63 times and `staff.module`
  guards 23 routes. One clinic account is "Head of University Health Services"; two are nurses.
- **Dead code already gone:** `MedicineBill`, `SaleMedicine`, `PatientService`, `PrescriptionService`, `PatientQueueUpdated`,
  `routes/debug-profile.php`, the `Staff` model, `MedicineAvailabilityController`. **Still present:** `fullcalendar` + `izitoast`
  (npm), `FixDashboardPermissions` (gives users direct permissions), `PerformanceMonitor`, `WarmUpCache`, `MedicineSeeder` /
  `DefaultStaffSeeder` (commented out), `routes/upgrade.php` (one admin `lang-js` route: check), `paytm` remnants,
  currency / amount-to-word helpers.

---

## 2. Decisions

### 2.1 Taken on 2026-10-08

| Topic | Decision |
|---|---|
| Dispense History | Consultation medicines show as **read-only rows** (source "Consultation"); changes and returns stay in the consultation |
| Roles | **Fix the current permissions** so each one consistently controls its menu, pages, buttons and dashboard links; the Roles page shows only what applies to each role |
| **Staff = Nurse** | **One role "Staff (Nurse)"**. The separate `nurse` role is merged into it. **Role Designation, Assigned Station and Shift Schedule are removed** from forms, lists, profiles and access rules; access comes **only** from Manage User roles. The old columns stay hidden in the database for history (can be dropped later). *Replaces the earlier "role follows designation" decision.* |
| Working database | **`norsu_clinic` only** (experimental laptop data): back it up, import the clinic dump into it, and rehearse the upgrade by re-importing the dump into it as often as needed. No extra working databases |
| Test database | The test run creates and wipes **its own throwaway schema automatically**; it never touches `norsu_clinic` |
| "Offline" | The system runs on the **LAN**; offline = **nothing needs the internet to load** (no CDN / third-party links; all packages local). Not air-gapped |
| Real-time | **Laravel Reverb** (self-hosted WebSocket server, PHP, LAN-only); when it is down a "Live updates off" badge shows and the open table refreshes every 60 s until it reconnects |
| Refactor | **Deep, URL-safe**: one shared route definition (every URL and route name identical, proven by a `route:list` diff), one role-route helper, one ⋮ action component, big controllers split, dead code removed |
| Project name | **"Medical University Clinic" — clinic name only**: every place users see "NORSU Clinic". The university name, address, logo / favicon, e-mail address and technical names stay |
| Accomplishment Report | College of Law code **CL → COL**; **Guest** column always shown; new **yearly** version: same illness / service rows with **Q1, Q2, Q3, Q4 and Year total** |
| Medicine Inventory (Report Generation) | New **yearly** report: per medicine, **left from last year**, **additional (stock-in this year)**, and **remaining at the end of Q1, Q2, Q3, Q4** (Q4 = year-end remaining = next year's "left from last year"), in each medicine's own unit |
| Improvements | Queue display board · Notification center · Stock disposal & count · Duplicate patient merge · Duplicate medicine / stock merge (dump as the sample) · Searchable "Add medicines" (name / generic / strength / category / SKU) |

### 2.2 Defaults applied unless the owner says otherwise

- Merges (patients, medicines, categories / generics) are **admin-only**, with preview, confirm and an audit snapshot; the
  merged-away record is archived, never deleted.
- **Medicine compatibility (owner, 2026-10-20; confirmed again 2026-10-22, together with "tickets restart at 1 each clinic day"):** require the same recorded generic ingredient and unit (case and surrounding spaces ignored). Different strengths may remain in separate batches. Missing ingredient / unit blocks confirmation until corrected.
- Queue display board = a kiosk page opened with a secret link set in Settings; queue numbers and rooms only (**no patient
  names**); offline chime.
- Notifications go to the users who can use the module.
- The seven pages whose JavaScript never loads (`@section('scripts')`) are switched on one at a time with a browser check,
  Add-to-queue first.
- After the Staff = Nurse change, nurses see every module the **Staff (Nurse)** role allows (today's Staff defaults include
  Inventory, Dispensing, Doctors and Specializations). The data migration does **not** change permissions; the admin reviews the
  role once in Manage User roles. Reports and Notifications are open to every staff account.
- "Prepared by" on reports = the user's name + role ("Staff (Nurse)", "Doctor", …) instead of the designation.
- Windows firewall and MySQL server settings are **not** changed by Claude; the steps are written for the owner.

### 2.3 Still standing (do not re-ask)

One shared queue · a consultation is deleted only by the admin or its creator · doctors view / edit patients only ·
prescriptions in the doctor's own name, staff only on a verbal order · lab-request deletion rules · Accomplishment Report
counting rules (see [STATUS.md §6](STATUS.md#6-needs-your-decision-)) · the printed form codes "NORSU-CLINIC-FORM-01 / 02" keep their names (owner, 2026-10-15) · the working database `norsu_clinic` is left alone while Phase 4 is checked: its two data migrations are rehearsed on a scratch copy of the dump (owner, 2026-10-15).

### 2.4 Still waiting for the owner (from STATUS §6)

🔒 rotate the DB password (old one in git history) · 🔒 Laravel 11/12 upgrade (framework / medialibrary advisories) ·
🔒 rappasoft tables 3.8 port · 🔒 npm runtime advisories / Vite migration · 🔒 merge `changes_v2` into `develop` · 🔒 PHI
columns in `activity_logs` and log retention · 🔒 untrack `public/messages.js` or keep committing it · 🔒 drop the hidden
designation / station / shift columns later (after the clinic runs without them) · 🔒 who may read the raw activity log
(Notifications & Alerts › System notifications: patient names, contact numbers, complaints): open to every staff account and
doctor since Phase 3, as §2.2 says for Reports and Notifications; one line limits it again.

---

## 3. Ordered phase checklist

Each phase ends with: full test suite green → re-verification of the phase's items → this file, STATUS.md and
[commands.md](commands.md) updated → one commit → push to `changes_v2` (standing go-ahead of the owner, 2026-10-09) with the
`handover:` commit.

### Phase 0 — Safety nets and baselines
- [x] 0.1 Back up the current `norsu_clinic` (`php artisan db:backup`) and keep the file.
  *Done 2026-10-09:* it was the fresh install (1 user, 123 migrations, 64 InnoDB tables + 1 view, 47 foreign keys); the
  backup (1.6 MB, ends with the "Dump completed" marker) was verified, and a copy is kept outside the repository and outside
  the 30-day retention of `db:backup`.
- [x] 0.2 Test database without manual setup. The test bootstrap (`tests/CreatesApplication.php`):
  - creates its throwaway schema (`norsu_clinic_test`) when it is missing;
  - keeps the guard that refuses to run against `norsu_clinic`.
  Then run the full suite once for the exact baseline count (one phpunit process at a time).
  *Done 2026-10-09 (test-first):*
  - `ensureTestSchemaExists()` creates the schema only after MySQL answers "Unknown database", only for a name the guard accepts, and once per PHP process. The guard became a testable function, `isSafeTestDatabase()` (plain word characters ending in `_test`, or in-memory SQLite);
  - new regression test `tests/Feature/Regression/TestDatabaseBootstrapTest.php` (11 tests: a missing schema is created with utf8mb4 / utf8mb4_unicode_ci and left alone on a second run; 10 guard cases). RED first (the existing DB tests failed with `Unknown database 'norsu_clinic_test'`), then GREEN;
  - proven end to end: with the schema dropped, a plain `vendor/bin/phpunit` created it again and passed; with `DB_DATABASE=norsu_clinic` the run aborts with exit code 1 before anything is dropped, and `norsu_clinic` was untouched;
  - **baseline:** 377 tests, 3,994 assertions, 1 skipped (the opt-in link crawl), all green, 16 min on a busy machine (366 earlier tests + the 11 new ones). `phpunit.xml` and `AGENTS.md` no longer say "create the schema once".
- [x] 0.3 Snapshots for later comparison: `php artisan route:list --json`; link crawl (`$env:LINK_CRAWL='1'; vendor/bin/phpunit --filter LinkCrawlTest`); route × role matrix ([route-access-audit-2026-10-02.md](route-access-audit-2026-10-02.md)).
  *Done 2026-10-09 on `5155bf5` plus the Phase 0 changes (none of them touches a route or a page):*
  - `route:list --json`: **472 routes** (admin 181, staff 121, doctors 105, the rest public / auth / vendor);
  - link crawl: 16 kinds of user, **1,143 requests, 1,093 pages, 0 broken links, 0 crashes, 0 lazy-table failures** (7 min 19 s). The only external links are the two plain text links in the privacy policy (accepted, §4.12);
  - route × role matrix: the 2026-10-02 harness was lost with its session, so it was rebuilt (`RouteRoleMatrixTest`, same fixtures and users as the link crawler): **441 routes × 18 users = 7,938 requests, 0 server errors**. The panels stay separated (`admin/*` answers 200 only to the administrator, `doctors/*` only to the doctor, `staff/*` only to staff accounts) and a deactivated doctor is sent to the login page everywhere (22.8 min). `compare-matrix.php` lists every cell that differs between two matrices, so Phases 3, 6 and 9 can show exactly which access changed. After Phase 3 the 12 designation / station users are replaced by Staff (Nurse) users with and without permissions;
  - the three snapshots are JSON files kept with the rehearsal tools (outside the repository, see 0.4).
- [x] 0.4 Rehearsal runner (script kept outside the repo; the dump contains patient data). It:
  - drops and re-imports the dump into **`norsu_clinic`**;
  - records row counts and checksums per table;
  - runs `migrate` with the default engine forced to MyISAM (as at the clinic) or InnoDB, one after the other;
  - runs the integrity checks.
  Repeatable as often as needed; each run starts from the dump.
  *Done 2026-10-09:* `php rehearse.php --label=<name> --engine=MyISAM|InnoDB|server` (about 25 s per run). It refuses to
  run unless the app is configured for exactly `norsu_clinic`. Per run it keeps `before.json` / `after.json` (per table:
  engine, rows, `CHECKSUM TABLE`, a content signature over the columns that existed before, zero dates; foreign keys;
  migrations), the `migrate` log, and a report; after a successful `migrate` it runs a second one (must do nothing). The
  engine is forced with a PDO init command (`SET SESSION default_storage_engine`), so no server setting is changed. Two
  runs of the same dump started from byte-identical snapshots. `db:integrity` (1.5) will add the domain checks (ledger,
  stock-out, `inventory:reconcile`).
  **Where it lives:** `C:\Users\Franc\.claude\projects\C--Projects-NORSUCLINIC\tools\rehearsal\` (outside the repository
  because of the patient data): `rehearse.php`, `artisan-with-engine.php`, `inspect-db.php`, `restore-backup.php`,
  `RouteRoleMatrixTest.php`, `compare-matrix.php`, `analyze-matrix.php`, plus `runs/` (R0 results), `backups/` (the 0.1
  backup) and `baselines/` (0.3 snapshots). Run `php <path>\rehearse.php --label=R1 --engine=InnoDB`.
- [x] 0.5 Rehearsal **R0** on the current code: record the exact errors under both engines (confirms or refutes §1.3).
  *Done 2026-10-09: **§1.3 is confirmed.***

  | | Default engine MyISAM (as at the clinic) | Default engine InnoDB (as on the dev laptop) |
  |---|---|---|
  | Dump after import | 59 MyISAM tables + the `used_medicines_view`, 44,558 rows, **0 foreign keys**, migrations stop at 108, **1 zero date** (`sale_medicines.expiry_date`) | same |
  | `migrate` | **succeeds** (15 migrations) | **fails** at `2026_10_02_090000_create_illness_and_service_tables` |
  | Error | none | `SQLSTATE[HY000] 1824 Failed to open the referenced table 'document_issuances'` (the foreign key `consultation_illnesses → document_issuances` cannot point at a MyISAM table) |
  | State afterwards | 64 tables, **all MyISAM, still 0 foreign keys** (a fresh install has 47), zero date kept; the 5 new tables are MyISAM; `colleges` 10 → 11, `settings` 26 → 28; no other existing row changed; a second `migrate` does nothing | **half-built**: 10 migrations done, `illness_systems`, `illnesses`, `consultation_illnesses` created with 2 foreign keys, migrations stop at 118; a plain retry would fail with "table already exists" |

  So at the clinic the upgrade "works" but silently leaves a schema without foreign keys or transactions, and on a server
  whose default engine is InnoDB it fails half-way. Phase 1 (convert to InnoDB first) fixes both. `norsu_clinic` was
  restored from the 0.1 backup afterwards (real import: 1.8).

### Phase 1 — Clinic-data upgrade
*Phase 1 is done and tested (2026-10-09; the follow-up for the orphan batches brings the suite to 429 tests; route list unchanged). Shared code: `App\Support\LegacySchemaUpgrade` (the upgrade steps),
`SchemaInspector` (read-only schema queries), `ForeignKeyCatalog` (the 47 keys), `SchemaParity` (schema comparison),
`App\Services\DatabaseIntegrityChecker` and `InventoryConsistency` (the checks). The migrations are thin wrappers around them.*

- [x] 1.1 `config/database.php`: `'engine' => env('DB_ENGINE', 'InnoDB')` (+ `.env.example`) so new tables are InnoDB on any server.
  *Done:* test creates a table while the session default engine is MyISAM and expects InnoDB (RED first: it came out MyISAM).
- [x] 1.2 Migration `2026_09_30_110000_convert_legacy_tables_to_innodb`: every MyISAM table → InnoDB (zero-date wrapper; views skipped; no-op on InnoDB databases). It sorts first, so the clinic runs it before the other new migrations.
  *Done:* converts MyISAM / Aria base tables; rows (zero dates included), indexes and the next auto-increment value are kept, views untouched, a second run does nothing. Mutation check: without the zero-date wrapper MySQL 9.7 refuses with error 1292 on a zero date. On the dump: 59 tables converted.
- [x] 1.2a *(found by R0)* Make `2026_10_02_090000_create_illness_and_service_tables` safe to re-run: skip a table that already exists, so a database left half-built by an earlier failed attempt (R0 under InnoDB leaves 3 tables and 2 foreign keys behind) can continue instead of failing with "table already exists". Test with a database in exactly that state.
  *Done:* each table is created only when missing (the lists were already `firstOrCreate`). The test reproduces R0's half-built state, finishes it, and runs a third time without duplicating the lists (RED first: "table already exists").
- [x] 1.3 Migration `2026_09_30_121500_normalize_zero_dates`: zero dates → NULL where the column allows NULL (runs after `121000`); reports the rest.
  *Done:* zero and half-zero dates (`2027-00-15`) in every date / datetime / timestamp column of a real table; a NOT NULL column is only reported. On the dump: `sale_medicines.expiry_date`, 1 row.
- [x] 1.4 Migration `2026_10_08_120000_restore_missing_foreign_keys`: adds each of the 47 reference foreign keys that is missing, **only** when the column types match and no orphan rows exist; otherwise skips and prints the orphan ids. Never changes data.
  *Done:* `ForeignKeyCatalog` lists the 47 keys; `ForeignKeyCatalogTest` compares it with a freshly migrated schema, so a future migration cannot drift from it. A key is added only when both tables are InnoDB, the column types match and no row points at a missing parent; it is skipped with the reason otherwise. Also `php artisan db:restore-foreign-keys`, to run the same step again after fixing data by hand. On the dump: 40 added by the migration, 6 more created by the other migrations now that their tables are InnoDB (= 46), **1 skipped on purpose** (see "Found by the rehearsal" below).
- [x] 1.5 Read-only command `php artisan db:integrity` (non-zero exit on errors). It checks:
  - engines and schema parity with a fresh install (tables, columns, keys);
  - orphans, zero dates and duplicate numbers / keys;
  - ledger vs batches vs medicine totals, and consultation medicines vs the ledger;
  - medicines with stock but no ledger rows (needed by the yearly inventory report);
  - direct permissions on non-admin users;
  - duplicate medicine / patient candidates;
  - dry-run counts of `text:repair-entities` and `phone:normalize`.
  *Done (both commands are described in [commands.md](commands.md): what they do, where and when to run them):* 12 checks (each an error or a warning, see `DatabaseIntegrityChecker`); `--compare-fresh` builds a fresh install in a throwaway schema (all migrations), compares tables / columns / indexes / keys / views / engines with this database and drops the throwaway schema. The stock checks were taken out of `inventory:reconcile` into `InventoryConsistency` and shared (characterization test first; the command prints exactly what it printed before).
- [x] 1.6 Regression tests for 1.1–1.5 (pattern `tests/Feature/Regression/LegacyColumnDefaultsMigrationTest.php`, which creates and drops its own small fixture schemas automatically).
  *Done, 52 new tests* (47 + 4 for the orphan-batch recovery, `OrphanBatchRecoveryTest`, + 1 found by the pre-push review: `--compare-fresh` refuses to replace an existing schema) on throwaway schemas (`tests/Concerns/UsesFixtureDatabase.php`) and the suite schema: `LegacyInnoDbConversionTest` 4, `IllnessTablesMigrationRerunTest` 1, `LegacyZeroDatesTest` 3, `LegacyForeignKeysTest` 9, `ForeignKeyCatalogTest` 2, `SchemaParityTest` 4, `DatabaseIntegritySchemaTest` 8, `DatabaseIntegrityDataTest` 9, `DatabaseIntegrityCommandTest` 4, `DatabaseIntegrityFreshComparisonTest` 1, `InventoryReconcileCommandTest` 2.
- [x] 1.7 Rehearse on `norsu_clinic` (re-import the dump each time) until clean under **both** engines. Pass when:
  - every table is InnoDB and the foreign keys are 47/47 (or each skip is explained);
  - counts and checksums are identical before and after;
  - ledger = batches = totals;
  - Stock-out shows the 6 dispenses + 1 return;
  - `inventory:reconcile` is clean and a second `migrate` does nothing.
  *Done, rehearsals R1 and R2, each under a MyISAM and an InnoDB default engine (identical results):* `migrate` succeeds (18 migrations: the 15 older + the 3 new; the InnoDB default no longer fails); 64 tables, all InnoDB; **46 of 47 foreign keys, the 47th explained**; the structure equals a fresh install (`db:integrity --compare-fresh`) except that one key; counts unchanged except the intended `colleges` 10 → 11 and `settings` 26 → 28 (new rows from the migrations) and the one zero date → NULL; ledger = batches = totals; the Stock-out view shows 6 dispenses + 1 return and the ledger nets to the 38 units of the 5 consultation lines; `inventory:reconcile` says "Inventory is consistent"; a second `migrate` does nothing.
- [x] 1.8 Final import of the dump into `norsu_clinic`, `migrate`, `db:integrity`: this is the working data for every later phase. Create QA test accounts on it (credentials kept in a git-ignored local file; real users' passwords untouched).
  *Done:* `norsu_clinic` holds the upgraded clinic data. Four QA accounts (`qa.admin`, `qa.doctor`, `qa.nurse` = nurse at medical consultation, `qa.head` = clinic head at front desk, all `@qa.test`) were created with random passwords stored only in `storage/app/qa-accounts.json` (git-ignored); each signs in; the 12 real users' password hashes are unchanged (compared by fingerprint before / after). After Phase 3 these accounts are re-created for the Staff (Nurse) role.

**Found by the rehearsal (for the owner):**
1. ✅ **4 stock batches belonged to medicines that were deleted** (#1: 20 units, #2: 40, #28: 250 + 240; only ever stocked in, no dispenses). They blocked the foreign key `medicine_batches.medicine_id`. **Owner decision (2026-10-09): re-create them as placeholders, keep the history.** Done by the new command `php artisan inventory:recover-orphan-batches --apply` ([commands.md](commands.md)): each medicine is re-created under its old id as "Unknown medicine #N", and its stock is **written off through the ledger** (a medicine has no inactive flag, and every picker lists medicines with available stock, so a placeholder holding stock could have been dispensed). Result on the clinic data: 47 of 47 foreign keys, `db:integrity` 0 errors, structure identical to a fresh install, the Stock-out view unchanged (7 rows). Rehearsal **R3** (both engines) ran the whole path dump → `migrate` → recovery → `db:integrity --compare-fresh` with 0 errors. Rename the placeholders if you know what they were.
2. The duplicate medicines named in §1.6 are confirmed by `db:integrity` (#41 / #42, #50 / #55): merged in Phase 8.2.
3. `phone:normalize` has 8 records waiting (dry run); `text:repair-entities` has nothing to do. Run `phone:normalize` with `--apply` after a backup on the clinic copy.
4. Every `*_id` column was also scanned for rows pointing at nothing, even where no foreign key exists: the clinic data has none besides those batches.

### Phase 2 — Fix: Dispense History shows consultation medicines
- [x] 2.1 Database view `dispense_history_view`:
  - dispense records + dispensed prescriptions (today's rule), plus one row per non-deleted consultation with medicines (`CONS-{id}`, date the medicines left stock, patient, given by, total quantity, Plan / Nursing);
  - read-only model `DispenseHistoryEntry`.
  *Done:* migration `2026_10_09_140000_create_dispense_history_view` (a `UNION ALL` of two branches; `id` is `B<bill id>` / `C<consultation id>` because the sources number their rows independently; the consultation branch sums `consultation_medicines` only for consultation forms and lists the Plan / Nursing parts; a deleted consultation gave its stock back, so it is not listed and comes back when restored; "Dispensed At" of a consultation = when the consultation was recorded). `doctor_id` is set only when the person who recorded it is a doctor (a nurse has none). Model `App\Models\DispenseHistoryEntry` (read-only, string key). `lines` is a reserved word in MySQL: the sale-lines sub-select is `bill_lines`.
- [x] 2.2 Dispense History table on the view: **Source** column + filter, name search, actions per source (consultation rows: View only).
  *Done:* `MedicineDispenseTable` now reads the view. New **Source** column (badge, plus "Plan + Nursing" under consultations) and a **Source** filter in the header (All / Dispense Record / Prescription / Consultation), word search on patient and on doctor / recorded-by (name or email), sort on every column. The doctor column is titled "Doctor / Recorded by". Actions: a manual dispense record keeps View / Edit / Delete; a consultation row and a prescription row get **View only** (they belong to their own record; before, the prescription row showed Edit / Delete buttons that were then refused). Archived patients keep their history and displayed name, with no patient link that would return 404.
- [x] 2.3 Read-only "consultation dispense" detail page inside Dispensing; link to the consultation only for users who can open consultations.
  *Done:* `dispense-records.consultation` (3 new routes: admin, `staff.`, `doctors.`; 472 → 475 routes, the diff against the Phase 0 snapshot is exactly these three) → `DispenseRecordController::showConsultation` and `medicine-history/consultation.blade.php`: patient, recorded at / by, the medicine lines (dosage, Plan / Nursing, instructions, quantity) and the total. No clinical notes on this page. 404 for a deleted consultation or a document that is not a consultation. The "View Consultation" button needs `manage_request_documents` (+ the `consultations` module for staff / nurse roles, so a pharmacist does not get a dead link and a clinic head can open it). *Since Phase 3 the button is drawn when `canUseModule('consultations')`, i.e. the role has `manage_request_documents`, for every role.*
- [x] 2.4 Tests:
  - rows for nurse- and doctor-added medicines;
  - edit / remove a line / delete / restore a consultation;
  - pending vs dispensed prescription;
  - search, sort and filter; access.
  Live check on clinic data: consultations 3 / 6 / 7 show 9 / 14 / 15 units.
  *Done:* `tests/Feature/Regression/DispenseHistoryTest` (15 tests / 112 assertions): doctor- and nurse-recorded consultations, no medicines, edit / remove lines, delete + restore, manual vs dispensed vs pending prescriptions, source filter, word search, sorting both ways, View-only consultation / prescription actions, role access and 404s. Takeover review added regressions for non-consultation documents, archived patient links and the clinic head's consultation link. All five mutations fail: document type, archived link, deleted consultation, prescription status and consultation access key.

  Live check on `norsu_clinic`: refreshing the view changed no stock or ledger rows; consultations 3 / 6 / 7 show **9 / 14 / 15** units. Headless Chrome over `192.168.2.13:8000` and `172.22.208.1:8000`: admin, doctor and clinic head pass source filtering, search, tab switching and all three detail pages, with no JavaScript errors, failed requests or internet dependencies. The QA nurse has no dispensing module under the existing policy: the menu is hidden and the list / detail pages correctly return 403 (Phase 3 removes this designation layer). Admin / doctor / clinic head have a working "View Consultation" link; the pharmacist's hidden link is regression-tested. `db:integrity --compare-fresh`: 0 errors / 3 existing warnings, schema identical to a fresh install.

  **Final verification (2026-10-09):** `LINK_CRAWL=1 php vendor/bin/phpunit` — **444 tests / 4,304 assertions**, all passing, none skipped (9 min 20 sec, 190 MB). The 16-user crawl made 1,143 requests over 1,093 pages: no broken links, crashes, lazy-table failures or capped crawls. Inline review and `git diff --check` passed. No new Artisan commands; [commands.md](commands.md) was checked and remains unchanged. Next: Phase 3.

  **Re-check after Phase 3 (2026-10-11, the owner asked to go back and double-check Phase 2):** the view, model, table, column partials, controller, routes and tests were read again against this plan and against the Phase 3 changes, then verified by data, by browser and by mutation.
  - *Data:* an oracle written independently of the view (plain SQL on the base tables) compared the view row by row. On the **real clinic data**: 3 consultation rows with **9 / 14 / 15 units** (38 in all, equal to the Stock-out view's net 38), the one pending prescription correctly left out. On a **scratch data set** with all three sources, an archived patient, a deleted consultation and a pending and a cancelled prescription: every check passed. The view joins `patients` and `doctors` by `user_id`, which the schema does not force to be unique (the data has no duplicates).
  - *Live* (scratch schema, admin / doctor / Staff (Nurse) sessions side by side): source filter, search (patient, doctor / recorded by, number, e-mail), every sort in both directions, paging, the filter returning to page 1, the three detail pages, 404 for a deleted, non-consultation or unknown id, deleting a manual dispense record from the history (stock +6 and a ledger adjustment), all 13 links on the page answer 200, **Manage User roles variants** (without documents: no "View Consultation" button and 403 on the consultation itself; without patients: no patient links; without doctors: no doctor links; without medicines: 403 everywhere, also for the Livewire update of an already open table), only requests to the same address, no horizontal page scroll at phone width.
  - *Defects found and fixed, test-first:*
    1. **Rows repeated and went missing between pages** whenever more rows than one page shared the sort value. Every manual dispense record of a day is saved at 00:00:00, so a busy day is a run of identical dates, and MySQL orders ties differently for each page query (reproduced: 60 same-day records listed only 48 different rows over 6 pages, 23 of them repeated). The table now always sorts by the record number and then the row id last (`MedicineDispenseTable::applySorting()`), so equal dates list the newest record first; a test pages through 60 same-day rows, also sorted by quantity.
    2. **The patient's own history page** (Patients › History, card "Medicine Dispense History") had the same gap this phase closed in the tab, and a worse one: it listed every `medicine_bills` row of the patient, so **pending and cancelled prescriptions were shown as dispensed**, and the medicines recorded in consultations were missing. It now reads the same view (dispense records, dispensed prescriptions, consultation medicines), with the Source badge and the same number as the tab.
    3. **A test that never tested "newest first"**: it back-dated the consultation with `update(['created_at' => ...])`, which is silently ignored (the column is not fillable), so both rows tied and the expected order only held by luck of the tie order. Now a real back-date with a check.
    4. Smaller: the source filter had no accessible name (`aria-label`); stale designation / station comments in the doctor and patient column partials.
  - *New tests* (`DispenseHistoryTest` now has 19 tests / 149 assertions, was 15 / 112): paging with ties, the patient history page, the real prescription-dispense flow (pending → dispensed, with the units handed out and the dispense date), the rendered row values, an invalid filter value is ignored. **13 mutations** (the last sort keys, the dispensed rule, deleted consultations, the Plan / Nursing parts, the quantity sum, the manual-record rule, both patient-page queries, the dispense date, the filter's name and its validation) each make a test fail. Full suite: `LINK_CRAWL=1` full suite **474 tests / 4,421 assertions**, all passing, none skipped (13 min 22 sec, 208 MB); the 15-user link crawl inside it (also run alone): 1,612 requests / 1,532 pages, no broken link, crash, lazy-table failure or capped crawl.
  - *Measured, not changed:* the view is built from scratch for every query (and the table runs a count and a page), about **35 ms per 1,000 history rows**: ~0.35 s at 10,000 rows, ~1.7 s at 50,000 (measured on 50,000 consultation rows of scratch data; the dispense-record side joins about as much per row). Fine for years at this clinic's volume (the clinic data has 8 consultations); the options are in §8.
  - *Observations, for the owner:* manual dispense records have no "recorded by" (the table has no such column, so that cell shows NA, as before Phase 2); a consultation's "Dispensed At" is when the consultation was recorded (a medicine added by a later edit keeps that date).

### Phase 3 — Fix: Manage User roles really applies + Staff = Nurse
- [x] 3.1 **Staff = Nurse, one role.** A data migration:
  - moves any account holding the `nurse` role to `staff`, then retires the `nurse` role (kept unassignable, hidden from the Roles screen and the staff form);
  - names the Staff role **"Staff (Nurse)"**;
  - leaves permissions as they are.
  The staff form loses its role picker.
  *Done:* migration `2026_10_09_170000_unify_staff_nurse_access`. It labels `staff` "Staff (Nurse)" and moves every account of the `nurse` role (archived ones too) to `staff`. The `nurse` role stays in the table as a default role: the Roles screen cannot delete it, its list row is hidden and its edit page answers 404. Role permissions, the hidden staff profiles and archived accounts are untouched; running it twice changes nothing. On a **fresh install it creates nothing** (only the older `nurse` role exists then, the seeder creates "Staff (Nurse)"); it creates `staff` only when nurse accounts would otherwise have no role to move to. The same migration (a) removes the permissions given **straight to non-admin users** (3.6) and (b) gives the **two permissions that had an empty label at the clinic** (`manage_patients`, `manage_request_documents`) their labels, so the Roles screen is not blank. The staff form has no role picker: the request accepts only the `staff` role and the repository always assigns it. (`StaffNurseMigrationTest`, 4 tests, plus the migration test and the staff-form test in `RoleModuleAccessTest`.)
- [x] 3.2 **Remove Role Designation, Assigned Station and Shift Schedule** from:
  - the staff create / edit forms, `CreateStaffRequest` / `UpdateStaffRequest`, `ValidStaffDesignationStationPair`;
  - `StaffRepository`, `StaffController`, `StaffTable` columns, staff show page, profile;
  - the Accomplishment Report "Prepared by" (role instead of designation).
  The columns and lookup tables (`staff_profiles.role_designation_id / assigned_station_id / shift_schedule`, `staff_designations`, `clinic_stations`) stay in the database, unused and hidden (🔒 drop later).
  *Done:* all of the above; `ValidStaffDesignationStationPair` is deleted. `StaffRepository` now saves only the form's fields and the **fixed country code 63** (the first version of the rewrite dropped it; `PhilippinePhoneTest` caught it). Saving a staff account leaves a legacy profile row exactly as it was (tested). "Prepared by" shows the signed-in user's name and the label of their role ("Staff (Nurse)", "Doctor", "Clinic Admin").
- [x] 3.3 **Remove the designation / station access layer:**
  - `getStaffDesignationModuleMap`, `getStaffStationModuleMap`, `getStaffDesignationStationMap` and `canStaffAccessModule` / `canStaffAccessAnyModule` (63 calls);
  - the `staff.module` middleware (`EnsureStaffModuleAccess`, 23 route guards);
  - `StaffDesignationSeeder` / `ClinicStationSeeder` from the seed run;
  - `StaffModuleAccessPolicyTest` (replaced by 3.7);
  - the obsolete JSON maps in `docs/` (`staff-*.json`).
  *Done:* the seven helpers (those four plus `canStaffDesignationWorkAtStation` and `getStaffProfileForUser`) and all 63 calls are gone; `EnsureStaffModuleAccess`, its `staff.module` alias and its guards are deleted (the 23 guards covered 123 routes); the two seeders are no longer in `DatabaseSeeder` (the classes stay, only the legacy test helper `makeStaff()` still uses them); `StaffModuleAccessPolicyTest` and `StaffModuleRoutingTest` are deleted; the three `docs/staff-*.json` maps are deleted (they also held staff names and e-mails, flagged by the 09-30 re-audit). The May designation audit carries a "superseded" note.
- [x] 3.4 One helper `canUseModule($module)`: the module's permission for the signed-in role. Map:
  - patients and queue → `manage_patients`;
  - consultations, certificates, prescriptions and lab → `manage_request_documents`;
  - inventory and dispensing → `manage_medicines`;
  - doctors → `manage_doctors`; specializations → `manage_specialties`;
  - settings, roles and CMS → their own permissions;
  - dashboard always on; reports and notifications open to every signed-in staff / doctor / admin (labelled on the Roles page).
  It replaces the 63 `canStaffAccessModule` calls (menu, dashboards, buttons) and matches the route middleware.
  *Done:* `canUseModule(string|array $module)` in `app/helpers.php`, map and lists in `App\Support\ModuleAccess`. An unknown module key is **denied** (a typo hides a link, it never opens one). The "Doctors" menu entry of a doctor opens the doctor's own profile (a doctor has no list); `getDoctorsMenuUrl()` leaves the entry out when the doctor record is missing instead of crashing every page (found in review). `ModuleAccessMapTest` proves the map and the routes agree.
- [x] 3.5 Routes agree with the menu: the doctor Queue gets `permission:manage_patients`; every staff / doctor group carries the permission of its module.
  *Done:* `route:list` against the Phase 2 snapshot: **475 routes, none added or removed**; 136 routes changed middleware: 123 staff / API routes lost the `staff.module` guard (and `role:staff|nurse` became `role:staff`), 13 doctor routes gained a permission (queue ×5, document look-ups ×4, doctor profile, queue consultation, prescription dispense, medicine list). **Every staff and doctor route now carries a permission except the two dashboards and the Reports / Notifications routes**, which are open on purpose; `ModuleAccessMapTest` fails when a new route has none. Dispensing a prescription now needs `manage_medicines` as well (the route and the controller both check it).
- [x] 3.6 Honest Roles page:
  - shows only the permissions that apply to the role; the server drops the rest;
  - Clinic Admin is shown locked with an explanation; Patient read-only ("patients do not sign in");
  - corrected descriptions; role-aware Discard link; the select-all script works after Turbo navigation;
  - direct per-user permissions on non-admins are reported (`db:integrity`) and cleared, so the role is the only source.
  *Done:* the form lists the five permissions a staff or doctor panel uses (`manage_patients`, `manage_request_documents`, `manage_medicines`, `manage_doctors`, `manage_specialties`) each with a plain description, and the server drops anything else (`manage_staff_dashboard`, `manage_admin_dashboard` and the admin-only ones check nothing for these roles). A role can now be saved with no permission at all (before, "required" blocked revoking the last one). Clinic Admin and Patient are read-only (the form request and the repository both refuse, so a second caller cannot get around the first), the retired `nurse` role is hidden. The index shows "Dashboard, Reports & Notifications always available". The select-all box is tri-state and re-initialised on `turbo:load`. The migration removed the existing direct permissions of non-admin users; `db:integrity` still reports any new one ([commands.md](commands.md)).
- [x] 3.7 Tests:
  - for every role × permission: revoke in the Roles screen, then on the next page load the menu item is gone, its pages answer 403 and dashboard links are hidden; re-grant brings them back (run with the real **file** cache);
  - Clinic Admin lock; nurse-role merge; staff form without the three fields;
  - link crawler reworked: one Staff (Nurse) user with the default permissions and one per permission removed, instead of every designation × station pair.
  Live check with admin, doctor and a nurse side by side.
  *Done:* `RoleModuleAccessTest` (the 10 role × permission cases, the permission filter and "clear every box" case, the read-only roles at both layers, the nurse merge, the staff form, route permissions, prescription dispensing, a doctor without a doctor record, a missing permission row, a legacy designation / station that no longer limits anybody, the Reports global search, the form, its explanations and the list contents), `StaffNurseMigrationTest`, `ModuleAccessMapTest`; the three designation tests were restated for permissions (`ConsultationModuleTest` H-02, `PatientQueueTest`, `QueueConsultationAttachmentTest`, which also got the doctor-queue button test) and the two designation-pair loops of `RoleAwareLinksTest` became permission variants, so they check the "hidden" side again. `LinkCrawlTest` now crawls Staff (Nurse) with the defaults, with a legacy profile and with each of the five permissions removed, and the doctor with the defaults, with `manage_doctors` added and with each default permission removed.
  **Review of the inherited work and its fixes (2026-10-09):** the first full run on the inherited tree had 13 failing tests. Causes: the migration created a `staff` role on a fresh database (9 `ReportGenerationTest` errors "role already exists"; it now creates nothing there), the staff save dropped the country code (a real regression), and three tests still encoded the old designation policy. Also found and fixed in review: the doctor menu crash, the blank permission labels at the clinic, the designation-pair loops that no longer proved the "hidden" side, stale comments; a **missing permission row** in an old database made every page a 500 (`canUsePermission()` now reads it as "not allowed"); the permission-variant link crawl found **"Record form" / "View Form" buttons on the doctor queue** that answered 403 without the documents permission; the **Reports global search** listed patients, prescriptions and medicines for roles that lost those modules (it now searches and links only the modules the role may use); the live check found the Roles screen's explanation boxes sliding away after 5 s and the select-all box showing the wrong state on load (the dead `roles/create-edit.js` reset it; removed). The mutation run found two layers that could not be told apart by the tests (the form request vs. the repository, the route vs. the controller) and one vacuous assertion (the lazy roles table); all three got tests of their own.

  **Final verification (2026-10-09/10):** `LINK_CRAWL=1 php vendor/phpunit/phpunit/phpunit`: **470 tests / 4,384 assertions**, all passing, none skipped (16 min 53 sec, 208 MB). The 15-actor link crawl inside it (guest, admin, Doctor ×6, Staff (Nurse) ×7) made 1,611 requests over 1,531 pages: no broken link, crash, lazy-table failure or capped crawl (its first permission-variant run had found the doctor-queue "Record form" defect). **Permission oracle matrix** (a scratch harness kept outside the repository): every staff, doctor and API route (230) called as Staff (Nurse) with the defaults and with each of the five permissions removed, and as Doctor with the defaults, with `manage_doctors` added and with each default permission removed, plus a guest, an administrator, a patient and a deactivated doctor: 3,680 requests, **0 server errors, no 200 for a guest / patient / deactivated doctor, and every one of the 223 cells whose permission was missing answered 403** (8 stock-in cells answered 404 only because the harness had no stock-in row; with a row they answer 403, checked separately). A second run with ONE doctor and ONE nurse for all variants (record ownership constant): 2,760 requests, 0 server errors and **no status that changes for a reason the route's own permission does not explain**; the remaining 403s on routes whose permission is satisfied are the existing owner / role rules (a consultation is deleted only by the admin or its creator, staff never delete prescriptions or lab requests, the reset-password route refuses a non-doctor target). **Rehearsal R4 on the real clinic dump** (MyISAM and InnoDB server defaults): 20 migrations (the 18 of Phase 1, the Phase 2 view, the Phase 3 migration), 64 tables all InnoDB plus 2 views, 47 of 47 foreign keys after the orphan recovery, a second `migrate` does nothing, `db:integrity --compare-fresh` 0 errors and the 3 existing warnings; the only content changes were `roles` (the Staff label), `permissions` (the two blank labels) and the Phase 1 zero date; the clinic data has no nurse-role accounts and no direct permissions on non-admins, so `model_has_roles` / `model_has_permissions` did not change. **22 mutations of production code** (the permission helper, route middleware, each migration step, the permission filter, the read-only roles at both layers, menus, buttons, the global search, the missing-permission case ...) each make at least one test fail; the first run found two-layer defences that hid each other, a vacuous assertion on a lazy table and a stale test filter, and each got its own test. **Live browser check** on the upgraded clinic data (three sessions side by side: `admin.localhost`, `doctor.localhost`, `nurse.localhost`): the Roles list shows 4 roles with labels, locks on Clinic Admin and Patient and no Nurse; Edit Staff (Nurse) lists the five permissions with descriptions and the select-all box is right on load (the dead `roles/create-edit.js` reset it, and the explanation boxes slid away after 5 s: both found live and fixed); removing Manage Patients takes Patients and Queue off the nurse's next page, `/staff/patients` answers 403, the Reports global search drops its Patient card, and **a Livewire action on an already-open Patients page answers 403**; re-granting restores everything; the same for Manage Medicines on the Doctor role (Inventory, Dispensing and the expiry alert disappear, dispensing answers 403) and `manage_doctors` added to the Doctor role (the Doctors menu entry opens the doctor's own profile); the staff list has no designation / station columns, Add Staff has no role / designation / station / shift field, and the nurse's Dispense History shows the three consultation rows (15 / 14 / 9 units). The roles were put back to their defaults afterwards; no console error. No new Artisan commands: [commands.md](commands.md) was updated for the direct-permission clearing only. Next: Phase 4.

### Phase 4 — Owner's requested changes (name, reports)
- [x] 4.1 **Project name "Medical University Clinic"** (clinic name only):
  - `APP_NAME` (`.env`, `.env.example`);
  - the `clinic_name` setting through a migration (only when it still holds the old default "NORSU / Norsu Clinic");
  - `SettingTableSeeder`, `DefaultUserSeeder` / `DefaultSliderSeeder` texts;
  - hard-coded "NORSU Clinic" in views (landing pages, headers, titles), PDFs and prints (certificates, excuse slips, lab requests, dispense records), the registration e-mail, `lang/en/messages.php`, `app/helpers.php`;
  - this repository's docs titles.
  Kept: "Negros Oriental State University", address, logo / favicon, e-mail address, `norsu_clinic` database, backup file prefix, folders.
  Test: rendered pages and PDFs contain no "NORSU Clinic" (link crawler assertion).
  *Done (2026-10-12/15):*
  - `APP_NAME="Medical University Clinic"` in `.env.example` and in the development `.env`. **On the clinic copy the server's own `.env` is changed by hand** (it is not in git): `APP_NAME` titles the guest pages and also names the **session cookie**, so everybody signs in once more after the change (§9 step 5).
  - Migration `2026_10_12_091000_rename_clinic_to_medical_university_clinic`: the `clinic_name` setting becomes "Medical University Clinic" **only while it still holds the old default** ("Norsu Clinic" in any letter case, extra spaces ignored); a name somebody typed is never replaced. The default landing-slider text "Prioritizing your wellness at NORSU Clinic." becomes "Prioritizing your wellness at Medical University Clinic" (55 characters, exactly the slider form's maximum); an edited slider is never touched. It clears the settings cache (the name is cached for an hour, so the pages would have kept the old name) and `down()` reverses it. Running it twice changes nothing.
  - `SettingTableSeeder`, `DefaultSliderSeeder`, `lang/en/messages.php` (the text only; the key `welcome_to_norsu_clinic` stays), the registration e-mail (subject fallback, title, sub-title "Negros Oriental State University"), the landing page image text (now `getAppName()`) and `RECODE_PROJECT_GUIDE.md`'s title.
  - Tests: `ClinicNameInSourceTest` (3) scans `.env.example`, `app`, `resources/views`, `resources/assets/js`, `lang`, `database/seeders`, `database/migrations`, `routes` and `config` for the old name in any letter case (only the replacing migration may name it); `ClinicNameAndCollegeMigrationTest` covers the data side; the link crawl now fails on any crawled page that still says "NORSU Clinic".
  - *Kept on purpose:* "Negros Oriental State University", "NORSU ID" / "NORSU community" (the university), the e-mail address, `norsu_logo.png`, the `norsu_clinic` database, the backup file prefix and the folders.
  - **Decision (owner, 2026-10-15):** the printed form codes "NORSU-CLINIC-FORM-01 / 02" (consultation view page, medical certificate and excuse-slip prints and PDFs, five views) are document control numbers, not the clinic name, and **stay unchanged**.
- [x] 4.2 **College of Law CL → COL:**
  - a migration renames `College of Law (CL)` → `College of Law (COL)` and the saved snapshot text on old consultations;
  - `CollegeSeeder` updated;
  - the report column header reads **COL**.
  Test on the clinic data.
  *Done (2026-10-12/15):* migration `2026_10_12_090000_rename_college_of_law_cl_to_col` renames `colleges.college_name` and the saved snapshot text in `document_issuances.college`, `lab_requests.college` and `activity_logs.college` (only the exact text "College of Law (CL)"; each table and column is checked first, so it also runs on a database that lacks them; one transaction; `down()` reverses it). `CollegeSeeder` creates "College of Law (COL)"; the report column header is the text in the parentheses, so it reads **COL**. Tests: `ClinicNameAndCollegeMigrationTest` (the college and every saved copy renamed, another college untouched, safe to run again and on a database without the college, the seeder and the heading). On the clinic data (a scratch copy of the dump) the one law-college row is renamed; no consultation, lab request or activity-log line held the old text, and no "(CL)" is left anywhere.
- [x] 4.3 **Guest column always shown** in the Accomplishment Report (label "Guest"), even when it is 0 ("Unspecified" still appears only when used); screen = PDF = Excel = CSV (`AccomplishmentReportBuilder`, `AccomplishmentReportTest` updated).
  *Done (2026-10-12/15):* the columns are the colleges, **F&S** and **Guest** (label "Guest", was "Guests/Others"), then **Unspecified** only when a visit with no college is in the report. One column list feeds the screen, the PDF, the Excel sheet and the CSV, so all four show Guest, at 0 when nobody is in it, even for a report with no consultation at all. Tests in `AccomplishmentReportTest`: Guest always present, Unspecified only when used, and the four outputs agree.
- [x] 4.4 **Yearly Accomplishment Report:**
  - a "Year" choice in the report;
  - rows = the same illnesses by body system and services; columns = **Q1, Q2, Q3, Q4, Year total**;
  - counted by consultation date with the same counting rules and filters;
  - one builder for screen, PDF (8.5×13), Excel and CSV, with Prepared by / Noted by.
  The college-by-college matrix stays on the existing (monthly / date-range) version.
  *Done (2026-10-12/15):* a **Report** choice on the Accomplishment Report tab: "By college, for the dates below" (the report as before) or "Yearly 2026: Q1, Q2, Q3, Q4 and year total" (the years run from the first consultation to this year). The yearly report has the same illness and service rows, the same counting rules and every filter of the screen (patient type, which starts on Walk-in, campus, college, course, year level, department, office, gender, consult mode, age group, body system, illness, service, medicine given, nurse / encoder, pregnancy, chronic condition). The columns are Q1 (January - March), Q2, Q3, Q4 and **YEAR TOTAL**; a consultation is counted in the quarter of its **visit date**, the same date the dates filter uses (the requested date, else the day it was created). The typed dates are replaced by January 1 to December 31 (the date fields and the period buttons are hidden while a year is chosen); `ReportFilters::year()` accepts exactly four digits between 1990 and 2100 and ignores anything else. The same builder serves the screen, the PDF (8.5×13, Prepared by / Noted by), the Excel sheet and the CSV; `ActivityLogController::accomplishment` is unchanged except for the file name, which carries `<year>_yearly_`; the college-by-college matrix is untouched. Tests (`AccomplishmentReportTest`, 21 in all): each visit lands in the quarter of its consultation date (the edges of every quarter and the day before / after the year, and an old consultation without a date), the same rows / rules / filters as the college report (each filter's count equals the by-college count for the same year), the year replaces the dates and junk years are ignored, an empty year is complete with zeros, the Report choice and the year list on the screen, and the screen, PDF, Excel and CSV show the same numbers (both tables of the screen and of the PDF end in YEAR TOTAL).
- [x] 4.5 **Yearly Medicine Inventory report** (Report Generation › Inventory › Yearly). Pick a year; one row per medicine (name, generic, strength, unit) with:
  - **Left from last year** = stock on 1 January (= last year's Q4 remaining);
  - **Additional** = stock received during the year (Stock-In / medicine form);
  - **Q1, Q2, Q3, Q4 remaining** = stock at the end of each quarter (Q4 = year-end remaining; a quarter in the future is blank).
  Details:
  - built from the stock ledger (each batch's `balance_after` at the quarter end), so returns, disposals and counts are counted correctly;
  - quantities in each medicine's own unit (tablet / box / bottle);
  - expired stock still on the shelf counted with an "expired" note until disposed;
  - screen, PDF, Excel and CSV from one builder;
  - check rule: left + additional − used − disposed ± adjustments = Q4 remaining, and for the current year Q-now = today's stock.
  Tests with ledger rows across a year boundary; live check on the clinic data (2026: left 0, additional = the stock-ins).
  *Done (2026-10-12/15):* on Report Generation › Medicine Inventory a **Report** choice: "Current stock" (the list as before) or "Yearly 2026: left, added and remaining per quarter" (the years run from the first stock movement to this year). One row per medicine that had stock or a stock movement that year: name, generic, strength and unit (the medicine's own), **Left from last year** (stock on January 1), **Additional** (received during the year), **Q1, Q2, Q3, Q4 remaining** (stock at the end of March, June, September and December; the quarter in progress shows today's stock and says "(to date)", a quarter that has not started is blank) and **Notes**. `YearlyInventoryReportBuilder` reads the **stock ledger** (`medicine_transactions`): the stock of a batch at a moment is the `balance_after` of its last ledger line up to that moment, so returns, disposals and corrections are counted as they happened and a past year can be reproduced years later; the batches' present quantities are read only to compare the current year with the shelf. Stock past its expiry date that was not disposed is still counted (good on its expiry day, expired from the next day) and noted per quarter ("Counted but expired and not disposed yet: Q3 20"). The **check rule** runs for every medicine: left + additional − used − disposed ± adjustments must equal the year-end remaining, and every ledger line's units must agree with the movement of its batch balance (so two wrong lines that cancel each other out are still caught); a medicine that does not add up is flagged in Notes (red) and counted in the header line instead of being hidden; for the current year the year-end figure is also compared with the shelf today and a difference is reported in Notes. In the yearly view the stock list's export, search box and status filter are not offered (the report has its own downloads, which carry the year). One result array feeds the screen, the PDF (8.5×13, Prepared by / Noted by), the Excel sheet and the CSV (`ActivityLogController::inventoryYearly`; **3 new routes** `activity-logs.inventory-yearly`, `staff.activity-logs.inventory-yearly`, `doctors.activity-logs.inventory-yearly`, open to everyone who may see Reports and listed in `ModuleAccessMapTest`'s open routes; route list 475 → 478). A missing or invalid year means this year; names that look like formulas are made plain text in the Excel and CSV files. Tests (`YearlyInventoryReportTest`, 14): a finished year across the year boundaries (stock-in, dispense, negative and positive adjustment, disposal, the first and the last second of the year), the rows (only medicines with stock or movement, sorted by name, a medicine whose only line moved nothing is no row), the year in progress and a future quarter, the shelf comparison, the add-up rule, expired stock and its day boundary, a ledger line that does not add up and two that cancel each other, the screen = PDF = Excel = CSV (with the year in the download links), the report screen, its year list and the controls that go away, the access rules and the formula-looking names. On the clinic data the ledger starts in 2026, so the first yearly report (2026) has "left from last year" 0, 55 medicines, 37,957 units added and a year end of 37,369 units, equal to the shelf, with no medicine flagged.

  **Final verification (2026-10-15):** `LINK_CRAWL=1` full suite: **507 tests, 4,717 assertions, all passing, none skipped** (43 min 42 sec, 234 MB: the machine was busy, an idle run takes about 13 min). The 15-actor link crawl inside it (guest, admin, Doctor ×6, Staff (Nurse) ×7) made 1,613 requests over 1,533 pages: no broken link, crash, lazy-table failure, capped crawl or page that still shows the old name. **48 mutations of production code (the name migration, the college migration, the seeders, the registration mail, the language file, the landing page, the Guest column, the quarter bucketing, the yearly dates, the year validation, the CSV header and file name, the screen controls of the yearly views, every ledger cut and boundary, signed adjustments, the expiry boundary, the shelf comparison, the sort order, formula protection, the download links ...) each make at least one test fail. Two runs (32 + 16): 7 mutations first survived (an empty-row guard, the shelf comparison, the stock-list controls that must disappear in the yearly view, the expiry-day boundary and the year in the download links); each got a test of its own and now fails one.** Rehearsal on a **scratch copy of the real clinic dump** (the dump rewritten into a throw-away schema, MyISAM and InnoDB server defaults; `norsu_clinic` itself was left untouched at the owner's request, 2026-10-15): 22 migrations (the 20 earlier ones plus the 2 of Phase 4), 64 InnoDB tables and 2 views, 47 of 47 foreign keys after the orphan recovery, a second `migrate` does nothing, `db:integrity --compare-fresh` 0 errors and the 3 existing warnings. The content changes were `roles`, `permissions`, `sale_medicines` (the Phase 1 zero date) and `sliders` (the default text), plus the `clinic_name` setting and the college row: afterwards `clinic_name` is "Medical University Clinic", college #10 is "College of Law (COL)", no "(CL)" is left anywhere and the only "norsu" texts left are the e-mail address, the logo / favicon paths and two user e-mails. **Live browser check** on that scratch copy of the clinic data (own servers on 127.0.0.1; admin in the browser pane, doctor and nurse over HTTP): the page titles and Settings › General show "Medical University Clinic" (the guest pages hold no old-name text); the Accomplishment Report by college has the **COL** and **Guest** columns (Guest at 0); choosing "Yearly 2026" gives Q1-Q4 and YEAR TOTAL on both tables, hides the date fields and the period buttons, and the PDF / Excel / CSV links carry the year; Medicine Inventory › "Yearly 2026" lists 55 medicines (left 0, additional 37,957, year end 37,369) with the stock-list export, search and status filter gone, and Reset returns to the current stock list; the PDF (text read back in content order and in table mode), the Excel file (62 non-empty rows, no formula cell) and the CSV (56 lines) match the screen; a junk year falls back to this year, an unknown format answers 404, a guest is sent to the login page; the doctor and the nurse open both yearly reports and their downloads through their own role routes; no console error and only same-origin requests. An **independent oracle** on the same data: the yearly Accomplishment Report equals the by-college report for the same dates (8 consultations in quarters 2 / 1 / 5, 104 row totals, services 5), the yearly inventory's additional, used and disposed equal the ledger sums for all 55 medicines, every medicine adds up, and the year-end figure equals the units on the shelf for every medicine. Observed, not changed: the yearly inventory PDF takes about 10 s for these 55 medicines (about 38 s for 220, linear: DomPDF), well inside the 120 s limit of the clinic PC. **The working database `norsu_clinic` was deliberately left alone** (owner, 2026-10-15): the two Phase 4 data migrations are not applied there yet, so it still says "Norsu Clinic" and "College of Law (CL)" until `php artisan migrate` is run. No new Artisan commands: [commands.md](commands.md) was checked and is unchanged. Next: Phase 5, when the owner says go.

### Phase 5 — progress 2026-10-15: cross-cutting Pass A sweeps (checkpoint A)

Before the module-by-module passes, the Pass A items that can be checked across **all** modules at once were done test-first
(33 new tests, 6 new files under `tests/Feature/Regression/`; **30 mutations of production code: 29 fail a test, 1 is equivalent**
(ordering by `doctors.id` resolves rappasoft's own alias, so removing that tie-breaker line changes nothing: documented, not chased)).

| Pass A item | What was found | What was done (test) |
|---|---|---|
| 3 validation matches columns | no `max` on most text fields; a long value failed the INSERT in strict mode with a database error page (patients, users / staff / doctors, categories, generics, countries, states, cities, barangays, specializations, roles, sliders, qualifications, medicines, stock-in, consultation and lab-request text fields, `medicine_bills.note`) | every rule now fits its column (`FormRulesFitColumnsTest` reads the rules and the real columns and compares them; `ConsultationFieldLimitsTest`, `LabRequestFieldLimitsTest`); the dispense-record note column is widened by the **additive migration `2026_10_15_100000_widen_medicine_bill_note`** (not applied to `norsu_clinic`: the owner asked to leave it alone) |
| 2 all-or-nothing saves | role create, the settings save and the Front CMS save wrote several rows without a transaction | wrapped in transactions; the CMS save also clears the settings cache (`SavesAreAllOrNothingTest`, 4) |
| 4 sort / paging | a list sorted by a value that can repeat (date, status, name) could show a row twice or never between pages | one trait `SortsByUniqueKey` adds the model key as the last sort key to every rappasoft list (the dispense list has its own unique key); `ListPagingTest` (6) pages every list and compares the union with the full set |
| 5 PDFs / exports | the dispense-record PDF crashed on a missing note | `?? ''`; `PdfNastyDataTest` (5): quotes, `<`, `&`, emoji / accents, 150-character names, missing data in the prescription, lab, certificate, consultation and dispense outputs |
| 6 stale caches | the dispense-form medicine list, the category list, the doctor lists, the expiring-stock badge and the PDF header settings were cached 5-60 minutes and not dropped when the data changed | model `booted()` hooks and `SettingsService::clearCache()` now drop them (`StaleCachesTest`, 5) |
| 6 N+1 | the doctor, staff and medicine lists ran one query per row | eager loads (`user`, `roles`, `media`, `batches`); `ListQueryCountTest` (6) shows the query count does not grow from 3 to 10 rows |
| 7 no internet | not re-audited in this sweep; the link crawl (unchanged) still reports no third-party link |

**Test harness:** `set_time_limit(120)` inside `DocumentIssuanceController::exportPdf()` applies to the whole phpunit process, so
once a test exported a consultation PDF every later test hit the 120-second limit; `tests/TestCase.php` now resets the limit after
every test. (Web requests are not affected: the limit ends with the request.)

**Scans kept for 5.19 / 5.21 (not changed yet):** `app/helpers.php` has 49 functions; 8 are never used outside the file
(`filterLangChange`, `getBadgeStatusColor`, `getCurrentLanguageName`, `getStatusBadgeColor`, `getStatusClassName`,
`getStatusColor`, `getUserLanguages`, `getYearDate`) and 10 are used once. `.env.example` has 37 keys; `SESSION_PATH` and
`QUERY_DETECTOR_ENABLED` are read by no `env()` call; no `env()` call exists outside `config/`.

**Leads for the module passes:** the Lab request list builder pre-orders, so its header sorts may not apply (5.8);
`UpdateGenericRequest` has an unrelated `generics.email` unique rule (5.6); `Cache::put('settings')` is dead (5.16);
authorisation at record level and the live passes with each role (Pass B) are still to do in every row.

**Verified:** `LINK_CRAWL=1` full suite **540 tests, 4,857 assertions, all passing** (13 min 35 sec); the link crawl inside it:
15 actors, 1,613 requests, no broken link, crash or lazy-table failure. The working database `norsu_clinic` was not touched.

### Phase 5 — progress 2026-10-16: 5.1 Dashboard (Pass A and Pass B done)

| Pass A item | What was found | What was done (test) |
|---|---|---|
| 6 stale caches / figures that disagree | the figures and today's list were cached for 60 s while "registered today" was read fresh: a new patient raised "registered today" but not "Total patients", and the list lagged both; three more cache keys and two repository loads fed nothing (the views never used them) | all 60 s caches and the dead loads removed (`getData`, `getStaffData`, `getDoctorData`); the figures are three indexed COUNT queries read fresh (`figures_follow_new_patients_and_doctors_at_once`, `active_doctors_leaves_out_deactivated_and_archived_doctors`) |
| 3 / 4 figures = the module lists | "Total patients" and "registered today" counted patient **accounts** by the account's date; the Patients list and the doctor dashboard count patient **records** | both count patient records with a live account by the record's own date, the same as the Patients module (`figures_count_patient_records_like_the_patients_module_does`) |
| 4 Day / Week / Month tabs | **Month** was "this month of any year" (`whereMonth` without the year); the week ended at 23:59:00 (a patient at 23:59:30 on Sunday was missed); the tabs silently cut every range at **5** rows (the first view showed all of today, the Day tab only 5) | real ranges (Monday 00:00 to Sunday 23:59:59, this month of this year), the 50 newest and the real total ("Showing the 50 newest of 55"), an unknown range answers an empty list (`day_week_and_month_ranges_are_the_real_ones`, `range_is_not_cut_at_five_and_says_how_many_there_are`, `unknown_range_answers_with_an_empty_list`) |
| 1 authorisation / role-blind links | the **staff** dashboard's Day / Week / Month tabs called the **admin-only** route (`/admin/dashboard-patients`, 403 for staff: the tabs never worked) and built admin patient links in the browser | a staff route `staff.patientData.dashboard` (open to every staff member like the dashboard, listed in `ModuleAccessMapTest`), one script loader using `panelRoute()`, and the **server** decides each row's link: admin page, staff page, or none for a staff member without Manage Patients (the name is then plain text, also in the JavaScript row template) (`staff_tabs_have_their_own_endpoint_and_the_admin_one_stays_admin_only`, `every_row_carries_the_link_its_own_role_may_open`, `tabs_script_uses_the_panel_route_and_the_link_the_server_sent`) |
| 5 / display | the tabs showed **UTC** times (a JSON date parsed in the browser: 09:05 showed as 01:05 AM) | the server formats the time in the clinic's timezone for both the first view and the tabs (`registration_time_is_the_clinic_clock_in_the_list_and_in_the_tabs`) |
| robustness | a patient record whose account was archived on its own made the whole dashboard fail (`null->university_id_number`); the highlighted Day tab sat over the Month pane | such rows are left out; the first pane is the Day pane (`patient_whose_account_was_archived_on_its_own_does_not_break_the_dashboard`, `first_view_is_the_day_tab_and_its_rows_sit_in_the_day_pane`) |

**Verified:** `DashboardTest` 15 tests / 81 assertions; 22 mutations of production code each fail a test (one first survived: an inactive doctor was never created in a test; it got its own). `LINK_CRAWL=1` full suite **571 tests / 6,066 assertions, all passing** (16 min 13 sec); crawl 1,621 requests, no broken link, crash or lazy-table failure; route list 478 → 479 (`staff.patientData.dashboard`). **Pass B** on a scratch copy of the real clinic data (nurse, doctor and admin sessions; `norsu_clinic` untouched): the figures equal an independent SQL oracle (7 patients, 3 today, 6 this month and 7 for "month of any year", 2 active doctors, 55 medicines, 0 queued); the staff tabs now answer (Day 3, Week 3, Month 6) with staff links and 09:05 AM style clock times; with Manage Patients taken off the Staff role the names are plain text in the first view and in the tab rows; admin tabs and links unchanged; every request is same-origin and answers 200, no console error of ours.
**Observed, not changed (owner question, STATUS §6):** the dashboards are open to every signed-in staff account and doctor by the plan's rule, so a staff account **without** Manage Patients still sees today's registrations (name, e-mail) and a doctor sees the medicine and patient totals; the names are not links for them. One line hides the list. `Hash::check('123456', ...)` runs on every dashboard load (5.18). `PatientRepository::store()` still clears the removed cache keys (5.2).

### Phase 5 — progress 2026-10-16: 5.2 Patients (Pass A and Pass B done)

| Pass A item | What was found | What was done (test, `PatientsPassATest`) |
|---|---|---|
| 4 list filter | the date-range filter of the list sent the last day as **midnight**, so patients registered on the last (or only) day were never listed ("Today" showed nobody); a value that is not a date range (the property can be set from the browser) crashed the list | the range runs from the start of the first day to the **end** of the last day, a backwards range is turned round, anything else is ignored (`date_filter_includes_the_whole_last_day_of_the_range`, `date_filter_that_is_not_a_date_range_is_ignored`) |
| 2 / 7 registration save | the welcome e-mail was sent **inside the database transaction** (a slow mail server would hold the database, a rolled-back registration could still have mailed) and was attempted even when the patient has **no e-mail address** (a failed send logged with the address) | sent after the commit and only with an address; the failure log keeps the patient number, not the address (`welcome_mail_is_sent_after_the_patient_is_committed_and_only_when_there_is_an_address`); the cache keys of the old dashboard are no longer cleared (`registration_code_no_longer_clears_cache_keys_the_dashboard_stopped_using`) |
| 1 record-level authorisation | "resend verification e-mail" accepted **any** user id (admin and staff accounts included) and answered 500 for a patient without an address | only patient and doctor accounts (like the verified toggle next to it), and a clear 422 refusal when there is no address (`resending_the_verification_mail_is_only_for_patient_and_doctor_accounts_with_an_address`) |
| 6 N+1 | the history page's **compare table** ran 2 queries per consultation for the plan / nursing medicines and 1 for the nurse in charge | loaded once in the controller; the view uses them (`history_page_does_not_query_once_per_record`: 3 and 12 records of each kind give the same number of queries) |
| 3 validation | the date of birth, gender and every pick-list id (campus, college, course, year level, office, department, vaccination, country, province, city, barangay) were **not validated**: "not-a-date", a date in 1800 or in the future, or an id that exists nowhere reached the database (a SQL error page, or a dangling id the reports would count) | rules on the create and the edit form for all 15 (`forms_refuse_dates_and_ids_the_database_would_not_take_or_that_point_nowhere`, 15 cases x register and edit) |
| privacy | a refused or failed patient save wrote the **whole form** (names, allergies, comorbidities, immunization record, as typed) into `laravel.log` | only the names of the refused fields, the exception class and the place are logged (`refused_or_failed_patient_save_does_not_write_the_patients_data_to_the_log`) |

Pinned as characterization (already right): registration ignores fields the form never sends (type, status, archived, verified), a failed registration or update saves nothing, archive then restore brings back the patient, the account, the history page and the audit rows, an update may keep the patient's own e-mail and ID (any case) but not another patient's, an archived patient's, or take them from an archived account, and pages of a patient with markup, emoji, accents and no address open and show the name as text.

**Verified:** `PatientsPassATest` 14 tests / 139 assertions; 18 mutations of production code each fail a test. `LINK_CRAWL=1` full suite **585 tests / 6,205 assertions, all passing** (13 min 56 sec); crawl 1,621 requests, no problems; no route added. **Pass B** on a scratch copy of the real clinic data (nurse, doctor and admin sessions; `norsu_clinic` untouched): a patient registered through the real create form (province → city → barangay selects, 897 barangays; all ids saved correctly); the real edit form of a legacy patient saved unchanged; the "Today" range of the list shows the patient registered today; archive and restore through the real buttons (archived rows offer Restore only); the history page with the compare table (nurse names shown); the doctor sees View / Edit / Create Prescription but no Add, Archive, Restore or Reset; with Manage Patients taken off the Staff role the list, create, edit and history answer 403 and the menu entry is gone.
**Observed, not changed (for later rows / Phase 6):** the route `delete-old-patients` has no page that uses it (it archives every patient account without a patient record; admin-only, POST, audited by the middleware): dead code, a Phase 6.6 candidate; the reset-password endpoint returns the raw exception text on failure although patients cannot sign in any more (the portal is retired) and the password reset to `123456` is audited by `AuditAdministrativeActions`; **every repository rethrows the raw exception message** (a database error quotes the SQL and the values) to the browser: handle once in the exception handler (a cross-cutting item for the later Pass A sweeps); the helper `getWeekDate()` (Sunday to Saturday) is only used as the list's "no filter" value, while the dashboard week runs Monday to Sunday.

### Phase 5 — progress 2026-10-16: 5.3 Queue (Pass A and Pass B done)

The module had been hardened before (R3 passes: atomic call / complete, a closed entry cannot be reopened, one place per patient under a lock, yesterday's leftovers closed, archived patients cannot be queued, the consultation attachment follows the patient's latest form; `PatientQueueTest`, `QueueConsultationAttachmentTest`). This pass looked at what those tests did not reach.

| Pass A item | What was found | What was done (test, `QueuePassATest`) |
|---|---|---|
| 6 stale caches | the **sidebar badge** was cached for 30 s and for everybody: a patient added or called a moment ago did not change it | read fresh on every page (one small COUNT) (`sidebar_badge_follows_every_change_of_the_queue_at_once`: add, add, complete, cancel) |
| 3 / 4 figures that disagree | "how many are in the queue" was computed four times, four ways: the badge and both dashboards counted entries whose patient record was gone (an account archived on its own) and the queue screens did not | one scope, `PatientQueue::openToday()` (waiting or in progress, queued today, live patient), used by the badge, the admin and the doctor dashboard (`badge_the_dashboards_and_the_queue_screen_count_the_same_people`: the counts are read before the screens close yesterday's leftovers, so they do not depend on it) |
| robustness | the doctor's "Current patient" card crashed the whole screen (500) for an in-progress entry that has no call time (a row from an older version, an edited row) | it falls back to the time the patient was queued |
| 6 N+1 | none found: 3 and 12 entries run the same number of queries on the staff and doctor screens and on both auto-refresh partials | pinned (`queue_pages_do_not_query_once_per_entry`) |

**Verified:** `QueuePassATest` 3 tests / 38 assertions; 8 mutations of production code each fail a test (one first survived because the queue screen closes yesterday's entries before the badge was read; the test now reads the counts first). `LINK_CRAWL=1` full suite **588 tests / 6,243 assertions, all passing** (15 min 15 sec); crawl 1,621 requests, no problems; no route added. **Pass B** on a scratch copy of the real clinic data (nurse, doctor and admin sessions; `norsu_clinic` untouched): add through the real form (a priority and a regular patient; adding the same patient again is refused with "already in the queue"), the badge follows at once, the screens list the priority patient first, the doctor calls the next patient (the "Current patient" card shows the clock time) and completes the consultation, the nurse's today list shows the completed entry with its times, cancelling through the edit form works and reopening the closed entry is refused, and without Manage Patients all seven queue routes answer 403 and the menu entry is gone.
**Observed, not changed:** the add-to-queue page script (select2 search and the "latest consultation" preview) never loads (`@section('scripts')`): plan 5.22, the owner decision in STATUS §6 ("queue page first"; its endpoint `api/patient/{id}/latest-consultation` exists but the script hard-codes the `/api/` URL); the add form puts every active patient into one plain list (a Phase 5 later look when the clinic has thousands of patients); deleting an entry removes it for good in any status and queue changes (add, call, complete, cancel, delete) leave no audit rows (only `added_by` and the times are kept): an owner question for the audit trail; the screens poll every 5 seconds even in a hidden tab (replaced by Reverb in Phase 7); the `queue_number` accessor is unused (and would query once per row); `scheduled_at` is read by the Reports "appointments" list but nothing in the app ever sets it (row 5.10); the sidebar badge shows the priority count (red) when anyone with priority waits, otherwise the total.

### Phase 5 — progress 2026-10-16: 5.4 Consultations (Pass A and Pass B done)

Earlier passes had already covered the text limits, the list sort key, the PDF with awkward data, the "Others" names (5.23) and the access rules of `ConsultationAccessTest` / `ConsultationModuleTest`; this pass looked at what those tests did not reach.

| Pass A item | What was found | What was done (test, `ConsultationPassATest`) |
|---|---|---|
| 1 authorisation (record level) | the patient's **History page** is a Patients page, but it drew and read every clinical part for anyone with Manage Patients: consultations (with the assessment and plan), certificates, excuse slips, prescriptions and the dispense history, although those belong to other permissions | each part follows its own module's permission (`canUseModule()`), in the view **and** in the controller (a part the role may not open is neither drawn nor read; a certificates-only role does not load consultation medicines) (`test_the_history_page_shows_consultations_certificates_prescriptions_and_dispensing_only_to_roles_that_may_open_them`: Manage Patients alone, + documents, + medicines, admin; headings and a query check) |
| 3 validation vs columns | the visit date (`requested_at`), consult mode, campus and office were saved unchecked on create and on edit (a date years ahead, a made-up mode, an id of nothing) | `CONSULTATION_VALUE_RULES` (date format, 1900 to one year ahead, `physical` / `virtual`, campus and office must exist) on the store **and** the update (`test_the_form_refuses_dates_and_ids_the_database_would_not_take`) |
| 4 / 6 figures and caches | the sidebar's **incomplete consultations** warning was cached for 60 s, counted a blank only when it was spaces (a tab or a new line passed as "filled in"), counted certificates too, and was computed for every role although only consultation users see it | one scope `DocumentIssuance::incompleteConsultations()` (consultations only; blank = no visible character, `NOT REGEXP '[^[:space:]]'`), no cache, computed only for a role that can use consultations; the list label and the doctor alert use the same definition (`missingAssessmentOrPlan()`) (`test_incomplete_means_the_same_on_the_badge_the_list_and_the_scope_and_the_badge_is_fresh`, `test_the_doctors_own_alert_and_the_row_flag_use_the_same_definition`; the history test's query check pins that a role without consultations never reads the table) |
| robustness | the doctor's **edit form asked for** a pregnancy status, a nursing intervention and a vaccination (`required`) that the nurse's form never asks for, so the doctor could not save an Assessment and a Plan on a nurse's record without inventing them | the three `required` attributes removed; the server never required them (`test_the_edit_form_does_not_ask_for_more_than_the_form_that_created_the_record`: RED first, then the doctor saves the assessment and plan) |
| owner decision | the "previous visit" lookup on the new-consultation form copied the last visit's **vital signs** (BP, pulse, temperature, respiration, O2) into today's form, so an old reading could be saved as today's | the lookup returns the history fields and the height / weight only; today's vitals stay blank (owner: "Leave today's vitals blank", 2026-10-16) (`test_the_previous_visit_lookup_carries_height_and_weight_but_never_the_vital_signs_of_the_visit`) |
| 5 PDF / awkward data | the show, edit and PDF pages with markup, emoji, accents, no illness / services / medicines | pinned (already right: `test_the_pages_of_records_with_missing_or_awkward_data_open`) |
| 6 N+1 | none found: the show and edit pages run the same number of queries for 1 and 8 medicines | pinned (`test_the_show_and_edit_pages_do_not_query_once_per_medicine`) |
| 2 all-or-nothing, stock | create / update / delete / restore are transactions; stock moves only through `MedicineInventoryService` | pinned (already right: `ConsultationInventoryTest`) |

**Verified:** `ConsultationPassATest` 8 tests / 125 assertions; 29 mutations of production code (C01 to C29): every one fails a test except C13 (equivalent: the lookup's SELECT excludes the vitals as well) and C14 (the matched text appears in two places; the other place is covered); six view-gate mutations first survived because the controller already hands those parts no data, so the test now also asserts the visible headings. `LINK_CRAWL=1` full suite **596 tests / 6,368 assertions, all passing** (13 min 43 sec); crawl 1,622 requests, no problems; no route added (the first full run found one old test, `ConsultationClassificationTest`, that saved a visit with a department id that does not exist; the new rule refuses such an id, so the test now creates a real department). **Pass B** on a scratch copy of the real clinic data (nurse, doctor and admin; `norsu_clinic` untouched): the nurse's new consultation through the real form (patient search → previous-visit lookup fills the history and the height / weight and leaves today's vitals empty), the sidebar warning goes 4 → 5 at once, the doctor opens the record from the list and saves the Assessment and Plan on the real edit page, the warning follows, an admin deletes the record from the patient's History page and restores it from Settings › Deleted records, and a Staff role without the documents permission sees the patient's record and the dispense history only (with the documents permission: consultations, certificates, compare table and prescriptions, not the dispense history); no console errors.
**Observed, not changed:** a consultation has no delete button on its own list or view page (only on the patient's History page; the DELETE route and the restore in Settings work); "paste an image URL to download it" in the consultation form is a feature that fetches from the internet (client side, only when someone pastes a link: not a page dependency); the consultation PDF raises the PHP time limit to 120 s for large forms; the dashboards show patient data to every staff account (owner question in STATUS §6, from row 5.1).

### Phase 5 — progress 2026-10-18: 5.5 Prescriptions (Pass A and Pass B done)

The module had been hardened before (R3 passes: a doctor writes only in their own name and changes only their own prescriptions, staff write one only on a recorded verbal order and never delete, a cancelled prescription cannot be dispensed, a dispensed one is closed, two strengths of one medicine, the dispense lock; `PrescriptionControlTest`, `PrescriptionStrengthsTest`, `PrescriptionInventoryTest`, `StaleCachesTest`, `ListQueryCountTest`, `PdfNastyDataTest`). This pass looked at what those tests did not reach (`PrescriptionPassATest`).

| Pass A item | What was found | What was done |
|---|---|---|
| 1 authorisation | the endpoint that creates a **catalog medicine with opening stock** (`prescription.medicine.store`, built for a "new medicine" pop-up that no page includes any more) needed only the documents permission: a nurse or doctor without Manage Medicines could create inventory | it needs the Inventory permission (`canUseModule('inventory')`); a role that has it keeps the endpoint (`creating_a_catalog_medicine_from_the_prescription_pages_needs_the_medicines_permission`) |
| 1 authorisation (buttons) | the list drew **Delete for staff** (always refused with 403) and **Edit and Delete on a dispensed prescription** (always refused) | each button is drawn only when it works: no Delete for staff or patients, no Edit or Delete on a dispensed prescription, PDF and View stay (`the_list_offers_only_the_buttons_the_role_may_use_on_that_row`) |
| 3 validation vs columns | the visit date was any text PHP can read as a date (year 1066, three years ahead, "05/10/2026") on create and on edit | `Y-m-d`, after 1899, at most one year ahead, with plain messages (`the_form_refuses_a_visit_date_and_numbers_the_database_would_not_take`, `an_edit_checks_the_same_values_as_a_new_prescription`) |
| robustness | the **View page crashed (500)** when the clinic logo setting is missing (`$data['logo']`) | it falls back to the clinic logo file, like the other pages (`markup_in_a_prescription_is_shown_as_text_on_every_page` ran into it) |
| robustness | the PDF of a prescription that does not exist answered "could not be generated" (500) | 404 like every other page (`a_missing_prescription_is_a_404_not_a_server_error_on_every_page`) |
| 2 all-or-nothing | not tested before | pinned: a new prescription that fails on its second medicine leaves no prescription, line or dispense record; an edit that fails leaves the old lines (`a_prescription_that_fails_half_way_is_saved_not_at_all_and_an_edit_that_fails_changes_nothing`) |
| 6 N+1 | none found: the View, Edit and PDF pages run the same number of queries for 2 and 10 medicines | pinned (`the_show_edit_and_print_pages_do_not_query_once_per_medicine`) |
| 5 / 7 | markup in the problem, advice and instructions is shown as text; no page loads anything from the internet; an archived patient cannot get a prescription (404) | pinned |

**Verified:** `PrescriptionPassATest` 9 tests / 63 assertions; 12 mutations of production code each fail a test (one first survived: the date-format rule; the test now also tries "05/10/2026"). `LINK_CRAWL=1` full suite ****605 tests / 6,431 assertions, all passing** (14 min 44 sec); crawl 1,622 requests, no problems**; no route added. **Pass B** on a scratch copy of the real clinic data (nurse, doctor and admin; `norsu_clinic` untouched): the doctor wrote a prescription through the real form (a year-1066 date is refused with the new message and the typed values stay; with today's date it saves; the markup typed in the problem and instructions shows as text), the list shows the doctor only their own prescription with Edit / PDF / Cancel / Delete, the nurse sees all prescriptions without a Delete button, cancels and reactivates through the real button, opens the PDF (200) and gets 404 for a number that does not exist, the administrator deletes a prescription through the real delete pop-up, and with the documents permission taken off the Staff role every prescription page and the new-medicine endpoint answer 403 and the menu entries are gone.
**Observed, not changed:** the prescription form fills a blank weight, pulse, temperature, blood pressure and height from the patient's latest consultation on save and again on every edit (the form shows those values, so they are visible; a deliberately emptied field is refilled): the plan's consultation decision ("leave today's vitals blank") was taken for the consultation form only, an owner question for prescriptions; saving twice by a double click creates two prescriptions (no duplicate guard); a doctor who asks for a prescription number that is not theirs gets 403 even when it does not exist; `PrescriptionRepository::createPrescription()` / `prescriptionUpdate()` / `getPatients()` / `getMedicines*()` are unused (Phase 6.6); the Dispense / verification side is row 5.7.

### Phase 5 — progress 2026-10-18: 5.6 Inventory (Pass A and Pass B done)

The module had been hardened before (R3 / Phase 1 / Sweep A: delete guards for medicines, categories and generics, stock-in edit and delete reverse exactly their batch, a stock-in number that survives a race, the opening stock goes through the Stock-In register, expiry sync, yearly inventory report, orphan-batch recovery, field limits, stale pick-lists, the medicine list N+1; `InitialStockRegisterTest`, `InventoryExpiryTest`, `PrescriptionInventoryTest`, `InventoryReconcileCommandTest`, `StaleCachesTest`, `ListQueryCountTest`). This pass looked at what those tests did not reach (`InventoryPassATest`).

| Pass A item | What was found | What was done |
|---|---|---|
| 2 all-or-nothing | on the medicine form (new and edit) the **generic and the category it names were created before the transaction opened**: a refused form (for example stock without an expiry date) left a new generic and a new category behind | the whole form, including those two, runs inside the transaction (`a_medicine_form_that_is_refused_leaves_no_new_generic_or_category_behind`, new and edit) |
| 3 validation vs columns | the stock-in form and the medicine form took any date that has the right shape: a medicine made in the future, an expiry in the year 1066 or 2306 (an expiry typo makes a batch look valid for centuries; a year-1066 expiry made it look expired) | manufacturing date not in the future, and no year before 1990 or after 2100, on the stock-in rows and on the medicine form (new and edit), with plain messages (`stock_in_refuses_a_manufacturing_or_expiry_date_that_cannot_be_real`, `the_medicine_form_refuses_the_same_impossible_dates`) |
| 3 validation vs columns (**found live**) | **saving a new medicine through the real form failed** whenever the Reorder Level box was left empty (it is optional, its placeholder says "e.g., 10"): the browser sends it blank, Laravel turns that into null, and the column is NOT NULL (default 0): "Column 'reorder_level' cannot be null" (the same on the edit form and on the prescription pop-up endpoint). The tests missed it because they left the key out instead of sending it blank | a blank reorder level is saved as 0 (`the_medicine_form_saves_when_the_optional_numbers_are_left_blank_as_the_real_form_sends_them`, `PrescriptionPassATest`); and a new guard, `BlankFieldsFitColumnsTest`, lists every form rule that lets a field stay blank where the column cannot hold null: it found only the medicine reorder level, the **queue status** (a blank status was written as null; now refused with a message) and fields already handled by their controllers (listed with the reason) |
| 6 N+1 | the **medicines-by-category pick-list** (consultation form, dispensing) ran two queries for every medicine (22 queries for 10 medicines) and the **Stock-In list** ran one for every row (the "N medicines" badge: 12 queries for 10 rows) | one query for the batches of all listed medicines (same answer: only strengths that have not expired); the Stock-In list loads its lines with the rows (`the_medicines_by_category_list_does_not_query_twice_per_medicine`, `…_offers_only_the_strengths_that_have_not_expired`, `the_stock_in_list_does_not_query_once_per_row_…`) |
| 4 list search | the Stock-In list searched only the stock-in number, so a nurse typing the medicine's name found nothing | the search also finds a stock-in by the name, brand or generic of a medicine inside it (same test) |
| 1 authorisation | not tested before | pinned: with the medicines permission taken away, staff and doctors get 403 on every way of changing inventory (medicine create / edit / delete, stock-in, categories, generics, the medicine detail) (`every_way_of_changing_the_inventory_needs_the_medicines_permission`) |
| 5 exports | the Stock-In Excel export with an empty register and with a medicine whose name has markup, quotes, accents and an emoji | pinned (`the_stock_in_export_opens_with_awkward_data`) |
| 3 taxonomy | a category or generic keeps its own name on edit but cannot take another one's (any case) | pinned (`a_category_or_generic_can_keep_its_own_name_but_not_take_another_ones`) |

**Verified:** `InventoryPassATest` 10 tests / 94 assertions and `BlankFieldsFitColumnsTest` 2 tests; 17 mutations of production code each fail a test. `LINK_CRAWL=1` full suite ****617 tests / 6,530 assertions, all passing** (22 min 49 sec); crawl 1,622 requests, no problems**; no route added. **Pass B** on a scratch copy of the real clinic data (nurse, doctor and admin; `norsu_clinic` untouched): the nurse's new-medicine form is refused for a manufacturing date in 2031 with the plain message and the typed values stay, and no generic or category is left behind; **the first real save failed with the blank reorder level (the defect above, found here), and after the fix it saves** (generic, category, 20 units, stock-in number); the new Stock-In (through the real form) is refused for a future manufacturing month and saved with a good one; the Stock-In list finds a stock-in by the brand and by the generic of the medicine inside it and shows nothing for a name that is not there; the medicine pick-list (consultation form) answers in about a second for the 53 medicines of the clinic data and shows the new medicine with 25 units; the doctor's list has no Add or Action buttons but every doctor inventory route answers 200 (the observation below); with the medicines permission taken off the Staff role the inventory, stock-in, categories, generics, new-medicine and dispensing pages answer 403 and their menu entries are gone, while the read-only pick-list stays available for the consultation form.
**Observed, not changed:** the medicine form (and its pop-up endpoint) has a **"Unit Cost" field** stored on each batch, although the project rule is "no prices, amounts, currency or payment anywhere": an owner question (STATUS §6); the Medicines, Categories and Generics lists hide the Add and Action buttons from **doctors** although the doctor routes allow every action to a doctor who has Manage Medicines (the plan's rule is that the permission decides): an owner question; the Stock-In Excel export lists only the number and the date (the screen shows the number and the count of medicines); two medicines can have the same name and strength (the clinic data already holds two such pairs, db:integrity warns): no guard was added because one medicine may hold several strengths as batches; `MedicineAvailabilityRepository::getCategoryList()` is built wrong (returns an empty list) and nothing uses it, like `getMedicines*()` of the prescription repository (Phase 6.6); the older `medicine-history` views still carry price fields (dead pages, Phase 6.6).

### Phase 5 — progress 2026-10-18: 5.6 follow-up (owner decisions) and 5.7 Dispensing (Pass A and Pass B done)

**Owner decisions of 2026-10-18 on row 5.6 (done, `InventoryOwnerDecisionsTest`):** the **"Unit Cost" box is gone** from the medicine form (nothing in the app read the value; the stock batch column stays untouched and is no longer written; the stock-in rules, the pop-up endpoint, the batch model and the stock service no longer carry it); **doctors are read-only in the inventory**: their routes allow only the lists and detail pages of medicines, stock-in, categories and generics (the write routes are removed from `routes/doctor.php`), their pages draw no "new" form, Edit or Delete button, the prescription pop-up that creates a medicine answers 403 for them.

Row 5.7 had been hardened before (R3 / Phase 2 / Sweep A: a prescription dispenses once and only when active, FEFO with the batch recorded, a manual record's delete / edit returns the stock, the Dispense History view and its paging, the stock-out view, PDF without a note; `PrescriptionControlTest`, `PrescriptionInventoryTest`, `StockOutTabTest`, `DispenseHistoryTest`, `PdfNastyDataTest`). This pass looked at what those tests did not reach (`DispensingPassATest`).

| Pass A item | What was found | What was done |
|---|---|---|
| 1 authorisation | the **quick "new patient" pop-up of the dispense form** (`dispense-records.store-patient`) sat inside the medicines permission only: a pharmacist without Manage Patients could register patients, and so could a doctor | it needs the Patients module and is closed to doctors (`registering_a_patient_from_the_dispense_form_needs_the_patients_permission_and_is_not_for_doctors`) |
| 1 authorisation (record level) | the Verify Prescription list showed **every doctor's prescriptions to a doctor account that has no doctor profile** | such an account sees none (`the_verify_list_searches_…_a_doctor_without_a_profile_sees_nothing`) |
| 4 list search | the Verify Prescription list had **no search** | it finds a prescription by patient, doctor or prescription number (same test; 3 mutations) |
| 3 validation vs columns | the manual dispense record's date accepted any text readable as a date (year 1066, 3 years ahead, "05/10/2026"), on create and on edit | `Y-m-d H:i:s`, `Y-m-d H:i` (the date picker's format) or `Y-m-d`, after 1899 and at most tomorrow (`the_manual_dispense_record_refuses_a_date_…`, `editing_a_manual_dispense_record_checks_the_date_…`) |
| 6 N+1 | the dispense form's **medicines-of-a-category list** ran two queries for every medicine (23 queries for 10 medicines) | one query for the batches of all listed medicines, same answer: only strengths that have not expired (`the_dispense_forms_medicines_of_a_category_do_not_query_twice_per_medicine`, `…_offers_only_the_strengths_that_have_not_expired`) |
| 2 all-or-nothing | not tested before | pinned: a manual record whose second line asks for more than the shelf holds saves no record and moves no stock; a refused edit moves no stock |

**Verified:** `DispensingPassATest` 6 tests / 40 assertions and `InventoryOwnerDecisionsTest` 3 tests; 22 mutations of production code each fail a test (one first survived: the date rule of the edit request; it got its own test). `LINK_CRAWL=1` full suite ****626 tests / 6,619 assertions, all passing** (16 min 14 sec); crawl 1,604 requests, no problems**; the route list loses the doctors' inventory write routes (no route added). **Pass B** on a scratch copy of the real clinic data (nurse, doctor and admin; `norsu_clinic` untouched): the Verify Prescription list searched by patient and by doctor (and "no data" for a name that is not there); dispensing a prescription whose medicine has no unexpired stock is refused with the exact reason and changes nothing; after a Stock-In through the real form the dispense works, and the prescription then shows in Dispense History (source Prescription, 14 units) and Stock Out; a manual dispense record saved through the real pop-up form (patient, category, medicine, strength with its available units, quantity, the picker's date) shows in the history; its PDF and detail page open, a missing id is a 404; the doctor's inventory page has no Add, Edit or Delete, no "new" forms, and the doctor's write URLs answer 404.
**Observed, not changed:** **doctors can still add, edit and delete manual dispense records** from the Dispensing screen (the pop-up and the row buttons are drawn for them and their routes allow it) while the Verify tab never offers them the Dispense button: an owner question (STATUS §6); the quick patient pop-up validates the payload before it checks the permission (an incomplete payload answers 422 before 403; a complete one answers 403); manual dispense records have no "recorded by" (no column); a raw database message can still reach the browser through the repository's `UnprocessableEntityHttpException` (the cross-cutting item); the older `medicine-history` partials `amount`, `balance_amount`, `discount`, `paid_amount`, `payment_status` and the price fields of `fields.blade.php` are dead pages with prices (Phase 6.6).

### Phase 5 — progress 2026-10-18: 5.8 Lab requests (Pass A and Pass B done)

The module had been hardened before (R3 / Sweep A: the state machine and its terminal states, soft delete with the audit snapshot and the "Deleted records" restore, only the creator or the admin deletes a pending or cancelled request, unique request numbers, the filename of the PDF, text limits that fit the columns; `LabRequestDeleteTest`, `LabRequestFieldLimitsTest`, `PdfNastyDataTest`, `ListQueryCountTest`). This pass looked at what those tests did not reach (`LabRequestPassATest`).

| Pass A item | What was found | What was done |
|---|---|---|
| 4 list sort (the open lead) | **the header sorts of the list did nothing visible**: the builder pre-ordered by date, so a click on Patient Name, Request # or Status was always applied after the date and could not change the order | the date order is the table's default sort, so the headers sort and a second click reverses (`the_headers_of_the_lab_list_sort_it_and_the_search_finds_a_test_name`; the data has date, id and name orders that all differ) |
| 4 list search | a nurse typing the test she asked for ("Vitamin D") found nothing | the search also finds a request by the name of a test in it (same test) |
| 3 validation vs columns | the date of the request accepted any text readable as a date (year 1066, three years ahead, "05/10/2026") on create and edit; the results typed on the edit page were written **unchecked**: a result status outside the four the column holds ended in a database error after the header was saved, and the texts had no limit | `Y-m-d`, after 1899, at most a year ahead, with plain messages ("The date of the request ..."); each result is checked (status one of pending / normal / abnormal / critical, value and notes up to 10,000 characters); the edit saves nothing when one is refused (`the_request_date_must_be_a_real_date_...`, `results_typed_on_the_edit_page_are_checked_...`) |
| 2 all-or-nothing / concurrency | a status button pressed on a **stale page** checked the old status and wrote over a request that had meanwhile moved on (a finished request could be reopened) | the status is read again under a lock inside a transaction before it is written (`a_status_button_pressed_on_a_stale_page_cannot_reopen_a_request_that_has_moved_on`; the test makes the row change right after the request is read) |
| 1 authorisation / privacy | the new-request form copied **any account** into the form (a doctor, staff member or the administrator: name, contact, address) when `?user_id=` named one | only a patient is copied (`the_new_request_form_prefills_only_from_a_patient_not_from_any_account`) |
| 5 / 6 | the PDF of a missing request is a 404; markup in a result is shown as text; the workflow refuses a transition it does not allow and a finished request stays finished | pinned (`a_status_change_that_the_workflow_does_not_allow_is_refused_...`) |

**Verified:** `LabRequestPassATest` 6 tests / 48 assertions; 11 mutations of production code each fail a test (one first survived: the test data had the date order equal to the id order; now they differ; the lock itself, `lockForUpdate()`, cannot be proven by a single-process test, the re-read is). `LINK_CRAWL=1` full suite ****632 tests / 6,667 assertions, all passing** (16 min 34 sec); crawl 1,604 requests, no problems**; no route added. **Pass B** on a scratch copy of the real clinic data (nurse, doctor and admin; `norsu_clinic` untouched): a request through the real form (patient found in the search box, two ready-made tests and a custom one; a year-1066 date refused with the new message and the typed values kept, then saved), results typed on the real edit page (status "abnormal", markup shown as text on the detail page), the status form walked pending → collected → processing → completed (each step offers only the allowed next statuses; after "completed" the status form and the edit page are gone), the PDF opens and a missing number is a 404, the list finds the request by a test name and shows "No items" for text that is not there, the doctor sees it with View and Print only, and with the documents permission taken off the Staff role the list, detail, create, PDF and patient search answer 403 and the menu entry is gone.
**Observed, not changed:** the filter in the list's address (`?status=`) and the search text are kept in the address bar (`?table-search=`), which is harmless; the creator-only delete rule is by design (R3-M6).

### Phase 5 — progress 2026-10-18: 5.9 Certificates (Pass A and Pass B done)

The certificates (medical certificate and excuse slip) share the consultation controller; what row 5.4 checked for consultations (text limits, the visit date, record access, delete / restore, the PDF with awkward data: `PdfNastyDataTest`, `DeletedRecordsTest`, `ConsultationAccessTest`) already covered them. This pass looked at what only the certificate forms do (`CertificatePassATest`).

| Pass A item | What was found | What was done |
|---|---|---|
| 3 validation vs columns | the certificate forms name their boxes differently from the consultation form (the consultation form's `vital_signs_pr`, `_temp` ... are `vital_signs_*_2` here, the pressure is two boxes `vital_signs_bp_2` / `vital_signs_bp_22` joined with a slash, the excuse slip has a list `subjects[n][subject / teacher]`), so **none of the limits of row 5.4 applied to them**: a long pulse, temperature or teacher failed the whole save with only "An error occurred", a joined pressure could pass the 191 characters of its column, and `subjects` could be any text (a JSON column) | every one of those boxes is checked (pressure parts 90 characters each, the others 191, `subjects` a list of at most 30 rows with a subject and a teacher of 191 characters), on create and on edit (`the_certificate_boxes_with_their_own_names_are_checked_against_their_columns`, `the_same_checks_apply_when_a_certificate_is_edited`) |
| 3 validation (the patient) | the patient (`user_id`) was only "an integer": a number that points at no account was saved on the certificate | it must be an existing account (same tests) |
| robustness | a pressure left blank in both boxes was saved as "/" | saved as nothing (`a_blood_pressure_left_blank_is_saved_as_nothing_not_as_a_slash`) |
| 5 PDF / print | the printable page and the PDF of both documents with markup in the name, the diagnosis and the subjects | pinned: the text is shown as text, the PDF opens (`a_certificate_with_its_subjects_saves_shows_and_prints`) |

**Verified:** `CertificatePassATest` 4 tests / 50 assertions; 10 mutations of production code each fail a test. `LINK_CRAWL=1` full suite ****636 tests / 6,717 assertions, all passing** (43 min 29 sec: the machine was busy); crawl 1,604 requests, no problems**; no route added. **Pass B** on a scratch copy of the real clinic data (nurse, doctor and admin; `norsu_clinic` untouched): a medical certificate through the real form (patient found in the search box, the doctor picked and his licence numbers filled in, all the vital-sign boxes), and an excuse slip with subjects and a teacher; both appear in the Certificate list, their detail pages show the values (markup typed in the diagnosis shown as text), the edit page shows the saved values and subjects, the print page and the PDF open; and with the documents permission taken off the Staff role the list, detail, edit, PDF, create and the "last certificate" lookup answer 403 while the patient's History page still opens.
**Observed, not changed:** the real certificate form marks every vital-sign box as required in the browser (a blank pressure only reaches the server from another client); the 403 text on a certificate that a user may not delete says "consultation".

### Phase 5 — progress 2026-10-18: 5.10 Report Generation (Pass A and Pass B done)

The module had been hardened before (Accomplishment Report with its rules and the "Others" names, yearly reports, the CSV that carries what the screen shows and neutralises spreadsheet formulas, the Excel exports that do the same, the search by words, the permission of each tab, the global search that follows the modules a role may use; `AccomplishmentReportTest`, `OtherNamesReportTest`, `ReportCsvParityTest`, `ReportListsTest`, `SearchBehaviourTest`, `YearlyInventoryReportTest`). This pass looked at what those tests did not reach (`LowStockDefinitionTest`, `ReportsPassATest`).

| Pass A item | What was found | What was done |
|---|---|---|
| figures that disagree (the main finding) | **"low stock" had three definitions**: the sidebar badge (a minimum alert or an alert percentage of a stable base, only for medicines that ever held stock), the Report inventory tab and its CSV (`available <= minimum alert` only, a medicine with no alert set counted as 0) and the colour of the quantity in the medicine list (its own percentage base). The badge could say 1 while the report listed 5 and the list warned about 4 | one definition in the model, `Medicine::scopeLowStock()` (SQL) and `isLowStock()` (PHP, same rule), plus `stockStatus()` ("Out of Stock" / "Low Stock" / "Healthy"), used by the sidebar, the report's Low Stock filter, its status column and CSV, and the medicine list; a grid of 11 cases (below, at, above the minimum, ran out with and without an alert, never had stock, percentage cases, no alert) must give the same medicines in all four places (`LowStockDefinitionTest`) |
| 6 N+1 | the Report inventory screen asked the database for each medicine's nearest expiry (`$medicine->batches()` inside the row loop) although the batches were loaded with the list | it reads the loaded batches (same test: the number of queries does not grow with the rows); the logs, visits and dispensing tabs were measured and have no per-row query (`the_report_tabs_do_not_query_once_per_row`) |
| 3 / 4 date ranges | both ends of a range on the Activity Log and Patient Visits | pinned: the first and the last day are in (`a_date_range_includes_its_first_and_its_last_day_on_every_list`) |
| 1 authorisation | the downloads of every tab for staff and doctors with no module permission at all, and a guest | pinned (`every_download_follows_the_tab_permission_and_a_stranger_gets_none`) |

**Verified:** `LowStockDefinitionTest` 3 tests / 21 assertions and `ReportsPassATest` 3 tests / 19 assertions; 12 mutations of production code each fail a test (one first survived: the case "percentage with nothing to compare to" did not tell the right base from a wrong one; it now does). `LINK_CRAWL=1` full suite ****642 tests / 6,757 assertions, all passing** (17 min 18 sec); crawl 1,604 requests, no problems**; no route added. **Pass B** on a scratch copy of the real clinic data (nurse and admin; `norsu_clinic` untouched): the Low Stock view of the Medicine Inventory report lists one medicine (Laradin, 295 units, reorder at 300), the sidebar badge says 1 and the medicine list warns "Low stock! Only 295 left (Min: 300)" for that same medicine; the CSV of the inventory has 55 medicines, 1 "Low Stock" and 3 "Out of Stock", the Low Stock filter downloads 1 row; every tab (Activity Logs with a date range, Patient Visits, Accomplishment Report, Dispensing, Schedules, Global Search) loads without an error; the downloads (CSV of each tab, Accomplishment as CSV / Excel / PDF, the yearly inventory PDF) answer 200 with the right type.
**Observed, not changed:** the **Schedules (appointments) tab is always empty**: it lists queue entries that have a `scheduled_at`, and nothing in the system ever sets it (an owner question: remove the tab, STATUS §6); the doctor's header bell reads the `notifications` table on every page although nothing ever writes to it (dead; Phase 6.6).

### Phase 5 — follow-up 2026-10-20: 5.10 Accomplishment Report Guest / F&S (done)

- [x] Owner-requested addition to row **5.10**, before proceeding to Phase 8.1: count consultations whose **Informant** says Guest / guest, Faculty / faculty or Staff / staff, as well as those with a saved **Patient Type**. Previously only `patient_type_id` determined the report column; an older consultation with just Informant could land in a college / Unspecified instead of Guest / F&S.
- [x] Owner decision for conflicting fields: an exact Informant label **Guest**, **Faculty** or **Staff** takes priority (case-insensitive, surrounding spaces ignored); otherwise use the consultation's saved Patient Type. Guest enters **Guest**, Faculty / Staff enter **F&S**, and the remaining visits keep the existing college / Unspecified rule. One column per consultation per illness / service, including when both fields agree. The patient's current profile is not consulted and no clinical record is edited.
- [x] Shared SQL classification in `ConsultationPatientType` drives illness, service and typed "Others" groups and the Patient Type filter. Patient Visits and Accomplishment Report therefore select the same visits; monthly / yearly screens and PDF / Excel / CSV retain their existing counting rules and agree with the corrected classification. Existing date, mode, document type and soft-delete restrictions remain in force.

**Verified:** 28 new regression cases in `AccomplishmentReportTest`; initial focused reproduction failed, final report set **72 tests / 2,652 assertions** passes. Eight deliberate faults detected: missing Informant, case sensitivity, missing trim, missing Staff, wrong precedence, missing saved-type fallback, old direct-ID filter and double counting; sources restored byte-for-byte. Full suite with crawl **774 tests / 8,212 assertions**, passing (**05:31.907**); **1,609 requests / 15 variants**, zero broken / crashed / lazy-table / capped results. All **459 source routes** unchanged; access matrix **6,288 requests / 393 routes / 16 variants** identical to baseline, zero server errors or access violations. Context7 Laravel **10.x** query grouping and bound raw-expression documentation checked.

**Pass B:** Chrome with admin, Staff (Nurse) and doctor sessions against ten synthetic April consultations: **Guest 3 / F&S 6 / student college 1**, for illness, service and both typed "Others" sub-rows. Explicit Guest / Faculty / Staff labels, mixed case / spaces, missing types and conflicting fields covered. Live Patient Type filters each return the expected three visits, yearly Guest gives Q2 / year total **3**, CSV values agree, XLSX downloads succeed for all three roles and PDF succeeds for admin. Automated tests also read actual XLSX cells and PDF / Excel template cells. Final run has no JavaScript errors, external network requests or server failures. An initial isolated Apache request failed to load its application key; a dedicated scratch configuration stabilised the test server and the complete browser run passed. Clinic database unchanged; no migration, dependency, asset or Artisan-command change. Synthetic server, schema, access file and scratch configuration removed after checking. A second-device LAN check remains in Phase 9.

### Phase 5 — progress 2026-10-19: 5.11 Notifications & Alerts (Pass A and Pass B done)

The "Notifications & Alerts" menu has three entries: **Low stocks alert** (the Medicine Inventory report filtered to low stock: row 5.10 gave it the one definition), **Expiry medicine alert** (the inventory page) and **System notifications** (the Activity Log tab). The alerts on the Inventory entry of the sidebar are the other half of this module (`NotificationsPassATest`).

| Pass A item | What was found | What was done |
|---|---|---|
| 6 stale caches | the sidebar's four alerts (expired stock, expiring in 7 days, in 30 days, low stock) were computed once and **kept for five minutes for everybody**; nothing cleared them. After a stock-in, a dispense or a batch passing its date the badge was wrong for up to five minutes (the same family as the queue badge in row 5.3 and the consultation badge in row 5.4) | counted when the page is drawn (`the_sidebar_follows_a_stock_change_at_once_not_after_five_minutes`; live: a stock-in that lifts the one low medicine above its minimum took the badge off on the very next page) |
| 6 / robustness | the counts were computed for every role that opens any page, although only a role with the inventory permission sees the Inventory entry | counted only for a role that can open the inventory; a nurse without it reads no medicine or batch table on a page (`a_role_that_cannot_open_the_inventory_does_not_pay_for_the_alert_counts`) |
| figures that disagree | the rules lived inside the menu view (7 and 30 days, "a batch counts while it holds units", "a medicine counts once") | one class, `App\Support\InventoryAlerts` (`counts()`, `CRITICAL_DAYS` / `WARNING_DAYS`), pinned on the boundaries: expired yesterday, today (critical), +7, +8, +30, +31, an empty expired batch, a medicine with two expired batches counts once, a percentage-only low stock (`the_alert_counts_follow_the_expiry_boundaries_...`, `a_medicine_with_two_batches_counts_once_...`) |
| 1 authorisation | the menu entries per permission | pinned: Low stocks alert and System notifications for every staff account and doctor (reports and notifications are open by the plan's rule), Expiry medicine alert only with the inventory permission; all three pages open (`the_notification_menu_entries_follow_the_permissions`) |

**Verified:** `NotificationsPassATest` 5 tests / 21 assertions; 10 mutations of production code each fail a test (two first survived: the "counts once" test had only one expired batch, and the low-stock test could not tell the old rule from the new; both cases were added). `LINK_CRAWL=1` full suite ****647 tests / 6,778 assertions, all passing** (18 min 58 sec); crawl 1,604 requests, no problems**; no route added. **Pass B** on a scratch copy of the real clinic data (nurse; `norsu_clinic` untouched): the sidebar shows "Low Stock: 1" (Laradin 295, minimum 300); a Stock-In of 100 units for it through the real form takes the badge away on the next page (it would have stayed for five minutes); all three Notifications & Alerts entries open (200); with the medicines permission taken off the Staff role the Inventory and Dispensing entries, the badges and the Expiry entry are gone, and Low stocks alert and System notifications still open. (The Stock-In first went to the other "Laradin" (#50): the clinic data holds the duplicate medicines #50 / #55 that `db:integrity` warns about; the merge tool is plan 8.2.)
**Observed, not changed:** the raw activity log behind System notifications is open to every staff account and doctor (owner question in STATUS §6); the doctor's header bell reads the `notifications` table on every page although nothing in the system ever writes to it (dead, Phase 6.6); the expiry colours of the medicine list use their own 7 / 30 day literals (same numbers as `InventoryAlerts`).

### Phase 7 — progress 2026-10-19: real-time updates with Laravel Reverb (done)

**How it works.** Every INSERT / UPDATE / DELETE that reaches the database is looked at once (`App\Support\Realtime`, one listener on the connection: Eloquent, the query builder and raw statements all count, so no controller can forget it). The written table decides the module(s) (`Realtime::TABLES`). When the surrounding transaction commits, one notice per module (`App\Events\ModuleChanged`: the module name and the time, nothing else) goes out on the private channel `module.<name>`; a rolled-back save announces nothing. The browser (`resources/assets/js/realtime/realtime.js`, Echo + pusher-js bundled by Mix) connects to the page's own host and listens to the channels of the modules its user may use (`routes/channels.php` uses `canUseModule()`, the same rule as menus, routes and buttons). On a notice for a module the current page shows (`Realtime::PAGES`, by URL) it fires the window event `clinic:live-refresh` (the two queue pages redraw their partial), Livewire's `refresh` (every table; `ReportGeneration`, `Dashboard` and `StaffDashboard` listen too), and redraws the sidebar badges from the new route `GET /live/menu` (queue, consultations "needs assessment", inventory alerts). Notices that arrive together become one refresh, with a random 0-1.2 s spread so ten open screens do not all ask in the same instant.

| Plan item | What was done |
|---|---|
| 7.1 spike | `laravel/reverb` 1.12 (react/* and pusher-php-server as its dependencies; no existing package changed), PHP requirement `^8.2` and platform 8.2.0, `laravel-echo` 2.6 and `pusher-js` 8.6; Reverb runs on Windows PHP 8.3, a WebSocket client connected and received a notice published from PHP |
| 7.2 server | `ModuleChanged`, the channels, `Realtime` (listener, table → module map, after-commit, rollback clears, per-application guard), `config/broadcasting.php` `reverb` connection with 1 s connect / 2 s total timeouts; **a stopped Reverb never fails a save**: the publish is wrapped, the first failure writes one warning and marks real-time "down" for 30 s (the clinic server shares the mark through the cache), during which nothing is tried |
| 7.3 browser | one connection per window (`window.clinicRealtime`), the badge "Live updates off" in the header while it is down (not while still connecting), the 60 s fallback refresh only while disconnected, an immediate refresh when the connection returns after an outage; the queue pages lost their 5-second countdown, Auto-Refresh toggle and error `location.reload()`; the report page lost `wire:poll.30s` |
| 7.4 modules | the table map covers patients, queue, consultations, certificates, prescriptions, lab, inventory (medicines, batches, stock-in, categories, generics), dispensing (bills, sale lines, ledger), doctors, specializations, notifications (activity log) and the Settings lists; `PAGES` maps every module page and both dashboards |
| 7.5 startup | `startup-dev-environment.ps1` / `.bat` start `php artisan reverb:start` in the same terminal (skipped when the port is already in use); `.env.example` has the keys; the one-time firewall rule for TCP 8080 is in [commands.md](commands.md) |
| a defect found by the live check | **the queue refreshed itself for ever**: `PatientQueue::closeStaleEntries()` sent an UPDATE on every page draw (almost always changing nothing), and every write is announced, so each redraw caused the next. It now asks `exists()` first. `test_opening_a_screen_announces_nothing` opens every main page as admin, Staff (Nurse) and doctor and fails if any of them announces a write |

**Verified:** `RealtimeServerTest` 11 tests / 107 assertions (a notice per save and per module, a query-builder UPDATE counts, three writes in one transaction are one notice, a rollback announces nothing and the next save still does, the payload is `module` + `at` only, channel access per permission incl. the user channel and names that are no channel, the page / module maps, the layout's config, `/live/menu` for signed-in users only, no `wire:poll` / `setInterval` left in views or scripts, opening a screen announces nothing, a save with Reverb down succeeds and is announced once); 18 mutations of production code each fail a test (two first survived: a channel name outside the module list was accepted, and one mutation was equivalent so it was rewritten to the real per-process flag). `LINK_CRAWL=1` full suite **658 tests / 6,885 assertions, all passing** (17 min 9 sec); crawl 1,606 requests, no problems; one route added (`GET /live/menu`). The first full run found two older queue tests that read the sidebar badge by its old markup (the badge now sits in a container the script redraws); their pattern was updated. **Live check** on a scratch copy of the clinic data with nurse, doctor and admin sessions on separate hosts: the nurse added patients to the queue and the doctor's open screen redrew without a reload, with the sidebar badge following (also from a write made outside the browser: a Livewire table and the report page redrew with one `/livewire/update` and one `/live/menu` request); with nobody changing anything the screens made no requests; Reverb stopped: the badge appeared within about 6 seconds, the nurse's save answered normally (one warning in the log), the doctor's screen caught up by the 60 s fallback; Reverb started again: the screens reconnected by themselves (about a minute, pusher's retry back-off) and went live again. Scratch schema dropped, `norsu_clinic` untouched.
**Observed, not changed:** the plain pages without a redraw hook (a patient's history page, forms) do not redraw themselves on a notice, they show the new data on the next page load; real-time on a LAN needs TCP 8080 open in the clinic PC's firewall (owner action, see commands.md); `REVERB_APP_*` must be set to private random values in the clinic's own `.env`.

### Phase 5 — progress 2026-10-19: 5.12 Settings › Staff (Pass A and Pass B done)

The module is the administrator's list of staff accounts (create, edit, reset the password to the default, archive). It had been hardened before (staff accounts only, one role, archived e-mail conflicts explained, phone normalised, the list's search / query count / last sort key: `AccountAndAccessTest`, `PhilippinePhoneTest`, `SearchBehaviourTest`, `ListQueryCountTest`, `ListPagingTest`); this pass pinned the rest and found two defects (`StaffPassATest`).

| Pass A item | What was found | What was done |
|---|---|---|
| 3 validation vs columns | **gender was only "required"**: the column is an integer, so `abc` or `9` reached the database (a 422 error page with SQL text, or a nonsense value saved) | `required|integer|in:1,2` on create and on edit (`gender_must_be_one_of_the_two_values_of_the_form`); every other field was measured against its column and pinned at its limit (`every_field_is_checked_against_its_column`: names 191, employee id 100, password length and confirmation, phone) |
| 2 retention (the main finding) | **archiving a staff member erased who had made their records**: the author relations (`PatientQueue::addedBy`, `DocumentIssuance::creator` and `nursingInCharge`, `LabRequest::creator`, `Prescription::dispensedBy`, `MedicineTransaction::user`, `ActivityLog::user`) skipped archived accounts, so the queue, the lab request page, the visits report and the dispensing report printed "N/A", "System" or a dash for everything an archived nurse had done | the seven relations name archived accounts too (`withTrashed()`); the archived account itself still cannot sign in or be managed (`records_keep_the_name_of_the_staff_member_who_made_them_after_the_account_is_archived`, `an_archived_staff_member_is_signed_out_at_once_and_cannot_be_managed`) |
| 1 authorisation | every route for a guest, a Staff (Nurse), a doctor and a patient | pinned: none reaches any of the eight routes, nothing is created, archived or reset (`only_the_administrator_manages_staff_accounts`) |
| 2 all-or-nothing | a role that cannot be given | no half-created account is left (`a_staff_account_is_not_left_behind_when_its_role_cannot_be_given`); a blank password on edit keeps the old one and an edit always ends with the one `staff` role (`leaving_the_password_blank_keeps_it_and_an_edit_always_ends_with_the_staff_role_only`) |
| 3 / 4 duplicates, list | e-mail and employee id in another case; the list's scope | pinned: refused whatever the case, its own values on its own form accepted; the list shows active staff only, no doctor, patient or archived account (`the_list_shows_active_staff_only`) |
| 5 / 7 output | names typed with HTML on the show and edit pages | shown as text (`a_name_typed_with_html_is_shown_as_text`) |
| audit trail | create, edit, archive and reset leave a line without any password (`every_change_to_a_staff_account_leaves_a_line_in_the_trail_without_a_password`); a reset password puts the nurse on the dashboard until she changes it (`a_reset_password_is_the_default_one_for_that_account_only_and_is_kept_on_the_dashboard`); a new account signs in with the password it was given (`a_new_staff_account_can_sign_in_with_the_password_it_was_given`) | pinned |

**Verified:** `StaffPassATest` 13 tests / 172 assertions; 9 mutations of production code each fail a test (the two gender rules and each of the seven author relations). `LINK_CRAWL=1` full suite **671 tests / 7,057 assertions, all passing** (18 min 3 sec); crawl 1,606 requests, no problems; no route added. **Pass B** on a scratch copy of the real clinic data with admin and nurse sessions: the list shows the five staff accounts; a Staff (Nurse) opening the page gets 403; the administrator added a staff member through the form ("Staff created successfully"), that account signed in with its password and landed on the Staff dashboard with the Staff (Nurse) menu, added a patient to the queue, was archived from the list (confirmation, "Deleted!", the row left the list), its next request went to the login page, and the administrator's queue page still said "Added by: Live Checker". Scratch schema dropped, `norsu_clinic` untouched.
**Observed, not changed:** the show page's Back button goes to the previous page (not to the list); the staff form has no field for the profile picture (the input is hidden) although the save code accepts one; the reset-password endpoint tells the administrator the default password in clear text (the product's choice).

### Phase 5 — progress 2026-10-20: 5.13 Settings › Doctors (Pass A and Pass B done)

The module is the doctor list and the create / edit form (`UserController` doctor actions, `UserRepository`, `CreateUserRequest`, `UpdateUserRequest`; the same pages are open to a Staff (Nurse) with Manage Doctors). The access guards of the side endpoints (status, password reset, e-mail verification, impersonation) had been fixed earlier (CRUD-AUTH-1 to 3, M-08, H-08) and are pinned here with the rest (`DoctorsPassATest`).

| Pass A item | What was found | What was done |
|---|---|---|
| 3 validation vs columns (the main finding) | the **create form accepted a doctor with no e-mail address and no password** (both "nullable"): the account could not sign in and the edit form then demanded an e-mail; **gender** was only "required"; **specializations** only "required" (an id that does not exist reached the database); **experience** any number (negative, 500); the address lines, country / state / city and **blood type** unchecked; the **qualifications** (a JSON text) unchecked, so a long degree or year came back as a "422" page with the SQL error | one shared rule set for create and edit (`Concerns/DoctorFormRules`): e-mail and password required on create, gender 1 or 2, at least one existing specialization, experience 0 to 80, address lines 191 (the column), country / state / city / barangay must exist, blood type one of the eight, every qualification has its degree, university and year (191 each); the column-fit sweep (`FormRulesFitColumnsTest`) agrees |
| 1 authorisation (mass assignment) | **the whole request reached the account**: the create form saved every fillable field it was sent (`university_id_number`, language, time zone, campus, emergency contact ... ) and the edit form saved the whole patient-record column list, so a crafted request could set a doctor's language to a value no translation has or take a university id | only the doctor form's own columns (`User::DOCTOR_FORM_FIELDS`) are saved, on create and edit; the password is changed on its own screen, not through the edit form (`only_the_fields_of_the_doctor_form_reach_the_account`, `the_edit_form_checks_the_same_fields_and_keeps_to_its_own_columns`) |
| 1 authorisation | every write route for a guest, a Staff (Nurse) without Manage Doctors, a doctor and a patient; the status toggle and the password reset only act on doctor accounts | pinned (`only_accounts_with_the_doctors_permission_manage_doctors_and_only_doctor_accounts_are_touched`); a deactivated doctor is sent back to the login page on the very next request (`deactivating_a_doctor_keeps_the_account_but_stops_it_signing_in`) |
| 2 retention | a doctor with prescriptions cannot be archived (nothing of them is lost); one without can, and the consultations they made still name them (the 5.12 `withTrashed()` relations); the archived account no longer resolves for an open session | pinned (`archiving_a_doctor_signs_them_out_keeps_their_consultations_named_and_leaves_audit_lines`, `a_doctor_with_prescriptions_cannot_be_archived_and_nothing_of_them_is_lost`) |
| 4 / 5 / 7 list, output, trail | the list shows doctors only and not archived ones; a name typed with HTML is shown as text; create, edit, status, reset and archive each leave a line in the trail without any password; a new doctor signs in with the password it was given | pinned |

**Verified:** `DoctorsPassATest` 11 tests / 184 assertions; 14 mutations of production code each fail a test (one first survived: the qualification length case was refused by the database, not by the rule; the test now fails on any `SQLSTATE` in the answer). `LINK_CRAWL=1` full suite **682 tests / 7,241 assertions, all passing** (27 min 58 sec); crawl 1,610 requests, no problems; no route added. **Pass B** on a scratch copy of the real clinic data with admin, doctor and nurse sessions: the list shows the two real doctors; the administrator added a doctor through the form ("Doctor created successfully"); that doctor signed in and landed on the Doctors dashboard; the administrator switched the status off in the list and the doctor's next request went to the login page; the administrator archived the account from the list (confirmation, "Deleted!", the row left the list); a Staff (Nurse) opened the Doctors list. Scratch schema dropped, `norsu_clinic` untouched.
**Observed, not changed:** archiving a doctor **deletes the `doctors` row** (licence numbers, specializations), the address and the photo, and archives only the user; `php artisan user:restore` then brings back an account that has no doctor record (the consultations and prescriptions are not affected, but the doctor cannot be edited or listed): an owner question in STATUS §6.

### Phase 5 — progress 2026-10-20: doctor archive decision and 5.14–5.22 (done)

The owner approved retaining a doctor's profile, address and photo on archive, including its licence reservation. Archive now hides the account while retaining those records and the specialization links; doctor lists, cache warm-up and old management URLs exclude archived accounts. `user:restore` brings the same complete profile back. Prescription create/edit validation also excludes retained archived doctor IDs, while restored profiles become selectable again. The existing prescription safeguard remains.

| Row / Pass A area | Finding and result |
|---|---|
| 5.14 Manage User roles | A new label such as `Clinic_Admin` passed display-label validation but collided with the internal identifier and caused a server error. It is refused before saving. Form fields, permission IDs, locked roles, archived role members, list search, escaped text, stable ordering and audit rows are covered; real Staff and Doctor permission revocation removes the menu link and gives 403, and re-grant restores access. |
| 5.15 Specializations | Create/edit validation, case-insensitive duplicates, search, escaped names, all five endpoints' permissions, audit rows and protection of links to retained archived doctors are pinned; create/edit popups exercised live. No production defect found. |
| 5.16 System settings | Saves now use only validated fields of the selected section; arbitrary settings keys cannot be overwritten. Both contact-section spellings receive the same validation, including JSON requests. Specialty IDs and contact fields are checked, the city list uses the selected province, blank optional text saves as empty text and absent uploads preserve existing images. The last two issues were reproduced through the real General form. Report lists and deleted-record restoration were exercised live. |
| 5.17 CMS / backups / locations | CMS text and encoded rich text are checked before saving. Country codes and province parent IDs are validated. Creating a province crashed on missing `Province::STATE_ARRAY`; it now uses the existing `State::STATE_ARRAY`, preserving the supported-country rule. Real CMS editors and all four location create popups work. The current MySQL backup service successfully dumped to a temporary file and restored the approved scratch schema; the temporary dump was removed. Backup page checked; existing backup files were left alone. |
| 5.18 Auth / profile | JSON headers allowed writes through the default-password gate; writes are now refused until the password changes. Invalid profile input is no longer copied into application logs. Live: failed sign-in, account archive/session expiry, complete doctor restore, reset through the doctor list, forced password-change modal, subsequent access and admin impersonation/return. Existing authentication and account regressions remain in the full suite. |
| 5.19 Helpers | All 49 functions reviewed with caller and test searches. Eight have no production or test caller: `getBadgeStatusColor`, `getStatusBadgeColor`, `getStatusColor`, `getStatusClassName`, `getUserLanguages`, `getCurrentLanguageName`, `getYearDate`, `filterLangChange`; `getExpiringMedicinesCount` is test-only. Internal permission/module helpers and overlapping route/badge helpers are Phase 6 candidates; nothing removed during this audit. |
| 5.20 Routes | 461 routes, no duplicate method/URL entries. Legacy medicine-history redirects in Staff and Doctor had duplicate group-only names: four explicit unique names added, preserving URLs/methods. The queue preview now returns clinical details only with the documents permission; the queue page uses its named API route. No route added or removed. Full route/access regressions and crawl passed. |
| 5.21 Config / server / backup | All 44 `.env.example` keys checked against `config/*`: `SESSION_PATH` and `QUERY_DETECTOR_ENABLED` remain unused candidates for Phase 6. No `env()` outside config. The real built-in server router tests run in the full suite; dump/restore on the current MySQL server succeeded. No dependency or configuration change. |
| 5.22 Page scripts | All seven pages now use the layout's `page_js` section. Queue search/preview, lab custom-test add/count, generic create/edit/index and medicine create/edit were checked in a real browser. Five sections contained only comments or were empty; no additional script imports were introduced. |

**Verified:** `LINK_CRAWL=1` full suite **703 tests / 7,835 assertions, all passing** (05:42.479); crawl 1,610 requests across 15 actor variants, no problems. **21 new tests** across `RolesPassATest`, `SpecializationsPassATest`, `SettingsPassATest`, `RemainingAuditPassATest`, `DoctorsPassATest` and `DefaultPasswordGateTest`. Twelve regressions detected the reverted production fixes; the two later blank-settings/route-name regressions were separately mutation-checked, with byte-for-byte source restoration. Province creation was tested failing before its fix, then passing. The archived-doctor prescription selection regression also detected its reverted validation fix. The column-rule audit now recognises an exact string `size` as a valid upper bound, confirmed against Laravel 10 documentation through Context7; older settings fixtures now create real specialization records. `npm run dev` succeeded; final diff check clean.

**Pass B:** admin, Staff (Nurse), Doctor and roles with permissions removed on an explicitly approved temporary local copy of the clinic data. Browser runs reported no JavaScript errors or external asset requests. The scratch server was bound only to localhost because automatic approval review rejected LAN-wide exposure; a second-device LAN check remains for Phase 9. The scratch database, temporary dump and synthetic access file were removed; `norsu_clinic` was not changed. No new Artisan command; the cache/restore behavior is recorded in [commands.md](commands.md).

### Phase 5 — Every module, two passes each

**Pass A (code)** for each module:
1. every route and Livewire action is authorised (role, permission, the record itself);
2. saves, edits, deletes and restores are all-or-nothing (real transactions now), stock moves only through `MedicineInventoryService`, soft deletes, cascades and the audit trail;
3. validation matches the database columns (strict mode);
4. list search / sort / filter / paging, and screen = export; **a list sorted by a value that can repeat needs a unique last sort key**, otherwise rows repeat or vanish between pages (found on Dispense History in the Phase 2 re-check; the Stock-out and other lists sorted by a date or a status have not been checked and probably need it too);
5. PDFs / exports with special characters, long text and missing data;
6. N+1 queries and stale caches;
7. nothing needs the internet to load: no CDN or third-party script / style / font / image link; every library is local.

**Pass B (live)**: the module used in the browser on the clinic data with each role that has it (admin, doctor, Staff (Nurse),
and a Staff (Nurse) account with permissions removed): console errors, dead links, empty states, wrong totals.

Every defect gets a failing test, then the fix, then both passes again.

| # | Module / area | Pass A | Pass B | Re-check (Phase 9) | Findings |
|---|---|---|---|---|---|
| 5.1 | Dashboard (admin / staff / doctor) | [x] | [x] | [ ] | Done 2026-10-16: fresh figures that match the module lists, real Day / Week / Month ranges, staff tabs and links, clinic-time display (block above); owner question in STATUS §6 |
| 5.2 | Patients (list, create, edit, history, archive / restore, reset password, email verification) | [x] | [x] | [ ] | Phase 2 re-check: the history page's dispensing card now lists what the Dispense History tab lists. Sweep A (10-15): field limits fit the columns, unique last sort key. Done 2026-10-16: date-filter last day, welcome mail after commit, resend-verification scope, history-page N+1, value validation of dob / gender / ids, no patient data in the log (block above) |
| 5.3 | Queue (add, call next, complete, cancel, record form, doctor view, attachment sync) | [x] | [x] | [ ] | Done 2026-10-16: sidebar badge and both dashboards use one fresh count, the doctor's current-patient card no longer crashes (block above); add-queue page script = 5.22 |
| 5.4 | Consultations (create / edit by nurse and doctor, Plan / Nursing medicines, images, PDF, delete / restore, illness & services) | [x] | [x] | [ ] | Sweep A (10-15): text limits fit the columns, list sort key, PDF with awkward data. Item 5.23 done (10-16): illness and service "Others" names required when ticked, saved tidied and restored on edit. Done 2026-10-16: the patient's History page shows each clinical part only to roles that may open it, value validation of date / mode / campus / office, one fresh definition of "incomplete", the doctor can save the Assessment and Plan on a nurse's record, today's vitals are not copied from the last visit (block above) |
| 5.5 | Prescriptions (create / edit, verbal order, strengths, activate / deactivate, PDF, dispense) | [x] | [x] | [ ] | Done 2026-10-18: new-medicine endpoint needs the Inventory permission, list buttons follow the rules, visit-date check, View page without a logo setting, 404 for a missing PDF, all-or-nothing and N+1 pinned (block above); owner question: vitals auto-filled on prescriptions; the dispense side is 5.7 |
| 5.6 | Inventory (medicines, categories, generics, stock-in, batches, expiry, low-stock, reconcile) | [x] | [x] | [ ] | Sweep A (10-15): field limits, stale pick-lists and expiry badge cleared on change, medicine list N+1 removed. Done 2026-10-18: the medicine form is all-or-nothing including its new generic / category, impossible dates refused, the pick-list and Stock-In list run a fixed number of queries, Stock-In search finds a medicine (block above); owner questions: "Unit Cost" field, doctors' buttons |
| 5.7 | Dispensing (verify prescriptions, dispense history, stock-out, manual dispense record, PDF) | [x] | [x] | [ ] | Dispense History re-checked 2026-10-11 (paging ties fixed); a manual dispense record has no "recorded by". Sweep A (10-15): note column widened (migration), PDF without a note no longer crashes Done 2026-10-18: the dispense form's quick-patient pop-up needs the Patients permission, the Verify list has a search and no leak to a profile-less doctor, the dispense date is checked, the category pick-list has a fixed number of queries (block above); owner question: doctors and manual dispense records |
| 5.8 | Lab Requests (state machine, items, PDF, soft delete / restore, numbers) | [x] | [x] | [ ] | Sweep A (10-15): text limits, list sort key. Open: the list builder pre-orders, check the header sorts Done 2026-10-18: the header sorts of the list work, the search finds a test name, the request date and the typed results are validated, a status change is re-read under a lock, the new-request form copies only a patient (block above) |
| 5.9 | Certificates (medical certificate, excuse slip, PDF, edit) | [x] | [x] | [ ] | Done 2026-10-18: the certificate forms' own boxes (`vital_signs_*_2`, pressure parts, `subjects`) and the patient are validated, a blank pressure is saved as nothing (block above) |
| 5.10 | Report Generation (Patient Visits filters, Accomplishment Report monthly + yearly, Medicine Inventory current + yearly, dispensing, global search, activity log, all downloads) | [x] | [x] | [ ] | 5.23 done (10-16): typed "Others" sub-rows across all formats. Passes complete 10-18: shared low-stock definition and no inventory-report per-row query. Owner follow-up complete 10-20 (block above): Informant-first Guest / F&S classification, saved-type fallback and consistent Patient Type filters, tested across screen / downloads. Phase 9 re-check remains; owner question: the always-empty Schedules tab |
| 5.11 | Notifications & Alerts (low stock, expiry, system notifications) | [x] | [x] | [ ] | Done 2026-10-19: the sidebar alerts are counted fresh and only for roles with the inventory, in one class with pinned boundaries (block above) |
| 5.12 | Settings › Staff (no designation / station / shift) | [x] | [x] | [ ] | Done 2026-10-19: gender is validated, and records keep the name of the staff member who made them after the account is archived (block above) |
| 5.13 | Settings › Doctors | [x] | [x] | [ ] | Done 2026-10-20: the doctor forms validate every field the account or its address uses and only the form's own columns reach the account (block above) |
| 5.14 | Settings › Manage User roles | [x] | [x] | [ ] | Sweep A (10-15): role create is a transaction, name limits  Done 2026-10-20; see the progress block above. |
| 5.15 | Settings › Specializations | [x] | [x] | [ ] |  Done 2026-10-20; see the progress block above. |
| 5.16 | Settings › System settings (General incl. clinic name, Report lists, Deleted records) | [x] | [x] | [ ] | Sweep A (10-15): settings save is a transaction, PDF-header settings cache cleared on save  Done 2026-10-20; see the progress block above. |
| 5.17 | Settings › Front CMS, Backup data, Locations (countries / states / cities / barangays) | [x] | [x] | [ ] | Sweep A (10-15): CMS save is a transaction and clears the settings cache, location name limits  Done 2026-10-20; see the progress block above. |
| 5.18 | Auth, profile, default-password gate, impersonation, failed sign-ins | [x] | [x] | [ ] |  Done 2026-10-20; see the progress block above. |
| 5.19 | `app/helpers.php` (every function: used? duplicated? tested?) | [x] | — | [ ] | Sweep A (10-15): 8 helpers unused outside the file (list above), 10 used once; nothing removed yet  Done 2026-10-20; see the progress block above. |
| 5.20 | `routes/*` (middleware stack per group, dead routes, duplicates) | [x] | — | [ ] |  Done 2026-10-20; see the progress block above. |
| 5.21 | `config/*`, `.env` keys actually read, `server.php` (`BuiltInServerRouterTest`), backup command on MySQL 9.7 | [x] | — | [ ] | Sweep A (10-15): `.env.example` keys checked (2 never read), no `env()` outside `config/`  Done 2026-10-20; see the progress block above. |

- [x] 5.22 The seven pages whose JavaScript never loads (`@section('scripts')`): Add-to-queue first, then medicines create / edit,
  lab request edit, generics create / edit / index, one page at a time with a browser check.

- [x] 5.23 **"Others" records what was actually done / found** (owner request 2026-10-15, with screenshots of Create Consultation
  Form and the Accomplishment Report). The owner wants the written name kept and shown beneath its parent "Others" row.
  *Before this change:* on the consultation form an illness "Others" line already had a "Specify … illness" box
  (`illness_other[<id>]`, stored in `consultation_illnesses.other_text`, 150 characters); a **Services rendered** "Others" line
  (`service_types.is_other`) had **no box** and `consultation_services` had no text column; the Accomplishment Report
  (`AccomplishmentReportBuilder`, rows carry `is_other`) printed one "Others" row per system / category with only the count.
  *Acceptance rules (test-first):*
  1. **Services "Others" gets a "Specify other service" box** alongside the line, like the illness one: required
     when ticked (a ticked "Others" with no text is refused with a clear message), max 150 characters (the column width: the
     strict-mode rule of Phase 5 Pass A item 3), trimmed, saved all-or-nothing with the rest of the consultation, filled again on
     edit, and cleared when the line is un-ticked. Migration: `consultation_services.other_text` `string(150)` nullable (new
     migration file, additive; existing rows stay NULL = "no name given"). Same on the consultation **edit** form, the doctor and
     the nurse panels, and the PDF / view page of the consultation (they already print the illness names, add the service text).
  2. **Illness "Others"**: keep the existing box; make it required when ticked too (same rule and message style), so an "Others" count
     in the report always has a name behind it. Rows saved before this rule with an empty name are reported under "(no name given)".
  3. **Accomplishment Report** (monthly and yearly, screen = PDF = Excel = CSV, the one builder): directly under each **"Others" row**
     list the typed names as indented **sub-rows** (for example "Others" → "Dengue fever", "Conjunctivitis"), each with its **own count
     per college column and its own total**; the "Others" row itself keeps the total of all its sub-rows, so the system / category
     totals and the grand totals do not change. Rules: case, repeated whitespace and punctuation at either end do not distinguish names
     ("dengue" / "Dengue " / "DENGUE." are one line; "aa" / "aA" are another; "dengue  fever" / "dengue fever" are another).
     Show the most common spelling, with ties going to the first one entered; sort by count (highest first) then name, with unnamed legacy entries last. A name is
     counted once per consultation; the Guest column and the yearly Q1-Q4 view behave the same way; the report's existing filters
     (college, period, consult mode, illness / service picks) apply to the sub-rows too; an "Others" with no names shows no sub-rows.
     Special characters, formula-looking names (`=SUM(...)`) and very long text must survive in the PDF, Excel (plain text) and CSV.
  4. **Tests:** consultation create / edit saves and refuses (empty, over 150, un-ticked clears); the report's sub-rows equal an
     independent query of the stored names for screen, PDF, Excel and CSV; the sum of sub-rows equals the "Others" count; totals
     unchanged; yearly and monthly; both roles; mutation-check each rule. **Live check** on a scratch copy of the clinic data with the
     browser (record a consultation with two different "Others" names in each list, open the report, download the three formats).
  *Owner decisions (2026-10-15):* a ticked "Others" with no text is **refused**. Merge capitalization variants ("aa" / "aA"),
  trim surrounding whitespace and collapse repeated spaces inside a name ("dengue  fever" / "dengue fever"); ignore end
  punctuation ("dengue" / "Dengue " / "DENGUE."). Word boundaries remain meaningful: "aa" and "a a" are different names.
  The report shows names only, never patient details.
  **Done and verified (2026-10-15/16; code and tests by Claude Code, independent re-checks by OpenAI Codex, then a full re-check by Claude Code
  after the owner handed the open work back):**
  - *What it does:* every services "Others" line has a "Specify other service" box (migration `2026_10_15_110000_add_other_text_to_consultation_services`,
    `consultation_services.other_text`, 150 characters, additive, existing rows stay "no name given"); a ticked "Others" line, illness or service,
    **must carry a name** (server rule `requireOtherNames()` on create and edit of a consultation form, a message naming the body system / category;
    the box is `required` while its line is ticked, typing a name ticks the line, and a hidden empty box is revealed instead of blocking the save
    silently); names are saved tidied (`App\Support\OtherName::clean`: trimmed, runs of spaces / tabs / line breaks one space) and cleared when the line
    is un-ticked; the Accomplishment Report (`AccomplishmentReportBuilder`, one result for screen, PDF, Excel and CSV; monthly and yearly) lists the
    names as indented sub-rows under each "Others" row (`'others'`) with their own count per column and total, merged by `OtherName::key`
    (case, repeated spaces and end punctuation ignored), the most common spelling shown (a tie goes to the first one typed), busiest first, consultations
    saved without a name last as "(no name given)"; the "Others" row keeps the sum, so section and grand totals are unchanged; the CSV has one
    "Others: <name>" line per name; the consultation view / PDF and the Patient Visits list and CSV show "Others: <name>" for services as they did for illnesses.
  - *Tests:* `OtherNamesSaveTest` (7 tests / 69 assertions) and `OtherNamesReportTest` (9 / 1,059): the helper, the save and refusal rules for illnesses and
    services on both panels, the limits, un-ticking, edit replacing, a refused edit saving nothing, the form's boxes and script, the sub-rows with the three
    spellings, the filters, the yearly quarters, a college that no longer exists, the screen, CSV, Excel (formula-looking names stay plain text) and PDF.
  - *Mutations:* 34 mutations of production code, 32 fail a test; the two survivors (the illness filter and the service filter inside the names queries)
    are equivalent, because the final row selection of the report already applies those filters (they only save work).
  - *Rehearsal:* the dump rewritten into a scratch schema (server default engine) and under MyISAM / InnoDB defaults (Codex): 132 migrations, 64 InnoDB
    tables, 47 of 47 foreign keys, second `migrate` does nothing, `db:integrity --compare-fresh` 0 errors / 3 existing warnings; the new migration is the last one.
  - *Live checks (scratch copy of the clinic data; nurse, doctor and admin; Codex on `norsu_clinic_others_scratch`, Claude Code on `norsu_clinic_p5_scratch`):*
    the boxes appear for all 11 illness and both service "Others" lines; typing ticks and requires; ticking, hiding the line with the illness search and
    saving reveals the box; a consultation saved through the real form kept "Lump on right LEG." and "Talk on hygiene" tidied; the edit page shows them
    again; the report shows `lump on right leg 4 / aa 2 / Sprain 1` (7 under Others, from seven spellings) and the three downloads carry the same
    names and totals; PDF pages inspected; no off-site request, no console error of ours (the only ones: a Livewire poll after a server was stopped and a
    consultation photo that is not in the dump).
  - *Full suite:* `LINK_CRAWL=1` **556 tests / 5,985 assertions, all passing** (39 min 16 sec: the machine was busy); the 15-actor link crawl made 1,614 requests,
    no broken link, crash or lazy-table failure.
  - *Not applied to `norsu_clinic`* (owner: leave it alone): the service-text migration, the note migration and the two Phase 4 data migrations. No routes or
    Artisan commands added ([commands.md](commands.md) unchanged). Observed, not changed: the Accomplishment Report PDF took about 35 s on the busy machine
    (DomPDF), inside the clinic PC's 120 s limit. Modules 5.4 / 5.10 still need their remaining passes.

### Phase 6 — progress 2026-10-20: 6.1 shared route definitions (done)

`App\Support\PanelRoutes` holds the common module definitions; `routes/web.php`, `staff.php` and `doctor.php` call them inside their existing panel groups and in their original order. It keeps the doctor patient/inventory restrictions, admin queue PUT-only update and binding names, lookup permissions, extra dispensing permission, report-export paths and legacy redirects explicit. Admin-only settings and shared account/public routes stay in `web.php`.

**Verified:** pre-refactor contract captured from `af66ff0`; four new `SharedPanelRoutesTest` tests pin all **394 panel routes** (including registration order, methods, actions, middleware, constraints and redirect defaults), reusable group registration, stock-out redirect targets and lookup precedence. RED for the missing shared registrar, then GREEN; four deliberate permission/method/constraint/redirect mutations each failed and the source was restored byte-for-byte. Focused access checks: **41 tests / 528 assertions**. Full suite with `LINK_CRAWL=1`: **707 tests / 7,865 assertions**, all passing (06:15.434); crawl **1,610 requests / 15 actor variants**, zero problems. The complete `route:list --json` is byte-identical (**461 routes**). Before/after matrix: **393 routes × 16 account/permission variants = 6,288 requests** each, identical response cells and permission sets, zero server errors or access violations (203 missing-permission, 3,637 other-panel and 1,179 guest/patient/inactive checks). Backup create/import/delete actions are excluded from this disposable-schema matrix; unbound fixture IDs may answer 404 before a role check. Inline review and diff check clean. Context7 Laravel **10.x** route-group/resource documentation used. No database, schema, asset or command change; no browser UI was changed in this step.

### Phase 6 — progress 2026-10-20: 6.2 shared role routing and default password (done)

`getRouteByRole()` delegates to `getRouteNameByRole()`; `redirectByRole()` shares the URL selection and keeps query parameters and flash messages. The helper recognizes the doctor's existing `doctors.doctors.detail` profile alias. Removed nine private controller route getters and three repeated patient-history redirect branches; shared views now use the helpers for links and form actions. The retired patient prescription cancel destination remains explicit because it differs from the list route. Dashboard metadata and the default-password gate reuse the dashboard helper while retaining the admin-by-type rule. `User::DEFAULT_PASSWORD` supplies hashes, checks, validation, seeding, reset messages and the browser confirmation text.

**Verified:** eight new `RoleRoutingHelpersTest` cases (RED for missing helper/constant and profile alias, then GREEN); five deliberate regressions detected (prefix, alias, query, password and validation), all sources restored byte-for-byte. Focused link/password/route checks **34 tests / 186 assertions**, then final helper/default-gate checks **15 / 69**. `npm run dev` succeeded; generated translation output restored. Browser: **45 real pages** as admin/staff/doctor, form actions in the correct panel, doctor/staff reset prompts, zero JavaScript errors or external asset requests. Used fresh synthetic fixtures; their server, database and temporary access file were removed. Full suite with crawl: **715 tests / 7,906 assertions**, all passing (05:52.401); **1,610 crawl requests / 15 variants**, zero problems. All **461 routes** remain byte-identical. Matrix **6,288 requests / 393 routes / 16 variants**, identical to the Phase 6 baseline with zero crashes or access violations. Inline review and diff check clean; Context7 Laravel **10.x** redirect/query/flash documentation used. No working-database, schema, dependency or Artisan-command change.

### Phase 6 — progress 2026-10-20: 6.3 shared row-action menus (done)

`<x-row-actions>` supplies a named toggle, visible action labels, keyboard navigation and fixed-position menus that escape scrolling tables. Replaced 22 row-action templates/components, the queue row/card groups and four patient-history groups. The original 23-view inventory included `medicine-availabilities/action.blade.php`, which is the page's Add button; it remains a visible header button. Existing routes, permission/record guards, confirmation flows and JavaScript hooks remain. Delete/archive is last. Empty or unauthorized menus are omitted, including prescription-only patient access. Medicine Delete uses the server's existing five-source history rule; eager existence flags avoid per-row queries. The doctor's Stock-In menu keeps View only.

**Verified:** 12 new `RowActionsTest` cases, **12 tests / 45 assertions**; RED/GREEN for the shared menu and the optional custom-action title; five deliberate regressions detected (module gate, empty slot, medicine history guard, eager history flags and optional-label fallback), all sources restored byte-for-byte. `npm run dev` succeeded; generated translations restored. Chrome: **102 recorded menu-entry clicks** across admin/staff/doctor, list/history/queue states, plus actual archive/filter/restore of a synthetic patient and doctor inventory checks. Save/destructive confirmations were cancelled in the entry walk-through; Phase 5 covered their completed workflows. Keyboard arrows/Enter/Escape and table clipping passed at 1,100 and 440 pixels with Bootstrap 5.1.3; no browser errors, external network requests or server failures. The isolated Apache process, synthetic browser schema and private access file were removed; no clinic records were copied or changed. Full suite with crawl: **727 tests / 7,951 assertions**, all passing (12:34.011); **1,610 crawl requests / 15 variants**, zero problems. All **461 routes** remain identical. Matrix **6,288 requests / 393 routes / 16 variants**, identical to the baseline with zero crashes or access violations. Inline review and diff check clean. Context7 Laravel **10.x** Blade/existence-query and Bootstrap dropdown documentation used. No dependency, schema or Artisan-command change.

**Runtime observation for Phase 9:** Windows PHP 8.3.31's CLI server intermittently returned a truncated HTTP 200 document (64,256 bytes, ending in the inline route script); isolated Apache on localhost served the complete pages consistently. A [PHP upstream partial-write fix](https://github.com/php/php-src/pull/23969) describes matching behavior; that is a possible explanation, not a verified version diagnosis. No PHP upgrade or application workaround was introduced. Re-check the supported clinic runtime and second-device/LAN access in Phase 9.

### Phase 6 — progress 2026-10-20: 6.4 shared consultation medicine picker (done)

Consultation create/edit use one locally bundled `custom/consultation-medicine-picker.js`, configured by `<x-consultation-medicine-picker>`. Safe JSON configuration keeps the panel-specific catalog URL, doctor-only Plan access, edit mode and saved medicine rows. Preserved field names/IDs, category/dosage choices, quantity limits and warnings, existing-stock credit, out-of-stock fallback, saved-row IDs, edit removal confirmation and duplicate checks per section. Certificate/excuse-slip forms emit no picker configuration and request no medicine catalog. Removed the edit script's unused `allMedicinesData` assignment and no-op fetch wrapper (it made no additional request). Dispensing and prescription pickers retain their separate category/Select2/template contracts and extra fields; forcing this consultation-row structure onto them would change their behavior.

**Verified:** characterization captured from the original scripts before extraction; the standalone `tests/Feature/Regression/consultation-medicine-picker.cjs` compares four browser states against the committed synthetic DOM fixture (`consultation-medicine-picker.json`), including cross-section allowance, duplicate rejection and removal behavior. Run it with an available Playwright module (`PLAYWRIGHT_MODULE` supports a bundled runtime) and installed Chrome. Three new PHPUnit integration cases / **53 assertions** (RED for missing shared configuration, then GREEN) cover all panels, saved/out-of-stock rows, hostile text and forms without a picker. Focused consultation/access/stock checks **31 tests / 295 assertions**. Five isolated JavaScript fault copies and three PHP configuration mutations detected; application JavaScript unchanged by fault checks and PHP source restored byte-for-byte. Build completed before final page checks; generated translations restored. Chrome: **10 actual form pages** across admin/staff/doctor, saved and out-of-stock rows and correct limits, zero browser errors, external network requests or server failures. Isolated Apache, synthetic browser schema and access file removed; working clinic database unchanged. Full suite with crawl **730 tests / 8,004 assertions**, all passing (12:24.721); **1,610 crawl requests / 15 variants**, zero problems. Complete **461 routes** identical; **6,288-cell matrix / 393 routes / 16 variants** identical, zero errors/access violations. Inline review and diff check clean. Context7 Laravel **10.x** safe JSON documentation and the installed Laravel 10.50 encoder checked. No dependency, schema or Artisan-command change.

### Phase 6 — progress 2026-10-20: 6.5 consultation controller services (done)

`DocumentIssuanceController` is **1,472 lines, down from 2,695**. Extracted `ConsultationPatientService` (matching/profile synchronization), `ConsultationMedicineService` (validated rows and inventory-service operations), `ConsultationImageService` (private uploads and rollback/after-commit cleanup) and `DocumentIssuanceNavigationService` (success destinations/messages). `ConsultationInput` shares the two nullable conversions across the controller and services. Controller validation, authorization, transactions and error handling stay at their original boundaries. Medicine usage keeps the existing audit trait and reference models. No matching, stock, image, role or redirect policy changed.

**Verified:** three new compound characterizations passed against the original controller (**3 / 40**) before extraction: failed creation rolls back the new walk-in patient/document/stock/photo; failed edit retains the original photo and stock and removes the new photo; successful create/update/delete preserves panel, history destination, flash text and module override. Eleven input data cases were RED for the missing helper, then GREEN. Final boundary/helper checks **14 / 62**; broader consultation/certificate/history/PDF checks **137 tests / 1,135 assertions**, all passing. Static comparison: **38 moved method bodies** identical after declared name/call substitutions. Seven deliberate regressions detected (transaction rollback, photo cleanup, stock deduction, matching, photo commit order, module override, zero normalization), all source files restored byte-for-byte. Full suite with crawl **744 tests / 8,066 assertions**, all passing (04:54.309); **1,610 requests / 15 variants**, zero problems. All **461 source routes** identical. Matrix **6,288 requests / 393 routes / 16 variants**, identical to baseline with zero errors/access violations. Inline review and diff check clean; Context7 Laravel **10.x** constructor resolution and test/config-cache documentation used. Working clinic database unchanged; no dependency, schema, asset or Artisan-command change.

**Audit execution note:** generated local configuration/route caches appeared during this work. The database guard stopped a run whose cached configuration pointed at `norsu_clinic`, before test migrations; clearing that generated configuration resolved it. A generated route cache also assigns random names to unnamed routes, so source-manifest and final suite/matrix checks use absent, ignored audit cache paths through `APP_CONFIG_CACHE` and `APP_ROUTES_CACHE`, alongside explicit `DB_DATABASE=norsu_clinic_test`. The guard remains unchanged. Use equivalent isolated cache paths for subsequent audit checks and synthetic browser servers; do not let a cached development configuration override their database selection.

### Phase 6 — progress 2026-10-20: 6.6 caller-backed cleanup and Livewire lifecycle (done)

Removed **38 files**, eight uncalled helpers and three uncalled prescription-repository methods. The only route changes are retirement of **GET/HEAD `/lang-js`** and **POST `/delete-old-patients`**, including the unused controller handler and audit pattern; **461 → 459 routes**, every remaining route entry identical. Retired `fix:dashboard-permissions` and `performance:monitor`; kept `cache:warmup` and CLI `lang:js`. Commands and retirement guidance are recorded in [commands.md](commands.md).

Caller evidence (searched application classes, Blade aliases/includes, JavaScript selectors/imports, route registrations, configuration, scripts and tests before deletion):

| Candidate | Evidence and result |
|---|---|
| FullCalendar / iziToast | No application imports or global calls. Removed the two npm declarations, their orphaned lock entries and the standalone embedded FullCalendar JS/CSS blocks. Lockfile stays version 2, with no added packages or remaining-package changes. Other bundled libraries stay intact. |
| Maintenance routes / commands | No page, application or operator-script caller for the two routes or retired command signatures. `cache:warmup` has real tests and current settings/doctor-cache behavior; CLI `lang:js` is used by the build. |
| Old scripts / bridge | Doctor/patient appointment detail files appeared only in Mix entries; no view loaded them. Neither the Livewire/Turbolinks bridge nor its Blade counterpart had an include/import. Removed all four scripts, the unused Blade bridge and obsolete Mix entries. |
| Unused classes / views | Class-name plus kebab-case Blade/Livewire alias searches found no caller for four dashboard sidebar components and their seven views/skeletons, `BannerTable` (the active table is `SliderTable`), `CreateSliderRequest`, the unconfigured custom media path generator, and four unused Blade component classes (AppLayout/CreateForm/EditForm/ErrorBox). Removed their three unused form/error views; retained the real app layout and used guest layout. The vendor's default media path generator remains configured. |
| Old billing / action scaffolds | No production caller for the generic CRUD action component (only its own regression case), legacy JSRender action templates, root medicine-history action view, five financial columns or commented-out billing fields include. Removed those files, the obsolete component-only test and stale include comments. Active dispensing views/columns and `patients.components.action_prescription` retain real callers and stay. |
| Helpers / repository | No production or test caller for `getBadgeStatusColor`, `getStatusBadgeColor`, `getStatusColor`, `getStatusClassName`, `getUserLanguages`, `getCurrentLanguageName`, `getYearDate`, `filterLangChange`, or `PrescriptionRepository::{getMedicinesQuantity,createPrescription,prescriptionUpdate}`. Removed these and their unused imports/commented legacy update. Current dispensing still calls `getPatients` / `getMedicines`; stock-in still calls `getCategoryList`, so those stay. |
| Financial formatting / seed examples / configuration | Price-only functions have no caller. Preserved the exact `addCommas` implementation in `custom/number-format.js` for three inventory quantity displays; removed the old price script and frontend entry. Removed the two commented DatabaseSeeder examples and unused `.env.example` keys `SESSION_PATH` / `QUERY_DETECTOR_ENABLED` (no config reader). Paytm and amount-to-words code were already absent; the last unused currency-symbol view is removed. |
| Designation / station | Remaining models, profile relationships, schema catalog and seeders have migration, archival/restoration or historical-profile test callers. They retain data and are not an access layer; no schema/data is dropped. The legacy notification bell likewise has an actual view/helper caller; notification-center improvements remain Phase 8.5. |

Shared Select2 initialization now registers **`livewire:init` → `Livewire.hook('morphed')`**, initializes only controls inside the changed component (including a select as the root), retains intact widgets and repairs removed containers. The old `livewire:load` / `window.livewire.hook('message.processed')` code is gone. Removed duplicate category/doctor `element.init` initializers (and the uncalled enquiry selector); the category filter now uses `wire:ignore`, like the doctor filter. Its close handler had targeted a nonexistent element ID; it now uses the actual Bootstrap dropdown instance, closing the menu and expanded state together. The real-page regression waits for a completed `morphed` callback before asserting refresh/selection/reset, rather than treating an HTTP response as a finished DOM update.

Standalone `select2-widgets.cjs` first failed against the original hook, then passed search/selection, repeated morphs, scope, retained instances, stale-container repair and root-control handling. `livewire-select2.cjs` checks five supported doctor/category list flows as admin/staff/doctor, including 440px mobile width, refresh/selection/reset and row-menu access; it takes an optional `QA_BASE_URL`, private `QA_ACCOUNTS_FILE` (`accounts[role].email/password`) and `PLAYWRIGHT_MODULE`. No account values are printed. The quantity formatter's characterization passes against both the original and extracted function. Regenerated both tracked IDE helpers with installed `laravel-ide-helper` **2.15.1**, using `--nowrite --reset` against the dedicated test schema; model source files unchanged, helper syntax valid and generated trailing whitespace normalized.

**Verified:** maintenance tests RED/GREEN (**3 / 12**); focused account/role/history/cache/menu checks **79 / 735**, final maintenance/settings/dashboard/picker checks **14 / 140**. Twelve deliberate regressions detected (two retired routes, two retired commands, five widget lifecycle faults, and three browser-only category faults for ignored DOM, duplicate initialization and the wrong close selector); all PHP mutations restored, final browser faults never changed application files/assets. Build passed; generated translations restored. Chrome: **25 real pages** (15 lists/inventory/settings/profile and 10 consultation/certificate/excuse forms), plus the five completed-morph filter flows; no JavaScript errors, external network requests or server failures. Standalone picker DOM characterization, widget lifecycle and quantity checks pass. Full suite with crawl **746 tests / 8,073 assertions**, passing (**04:50.051**); **1,609 requests / 15 variants**, zero broken/crashed/lazy/capped results (one fewer guest page after retiring the translation endpoint). Matrix **6,288 requests / 393 routes / 16 variants**, identical to the Phase 6 baseline with zero errors/access violations. Source route manifest exactly **459** after the two declared removals; stale normal route cache cleared. Context7 Livewire **3.x** lifecycle/ignored DOM, Select2, npm and IDE-helper documentation checked against installed versions. Working clinic database unchanged; no migration or new dependency.

**Validation execution:** interrupting an earlier suite for the final asset rebuild left its own partially migrated `norsu_clinic_test_fresh_compare` schema. Its create timestamps matched the interrupted run and its clinical tables were empty. The unchanged comparison guard refused to replace it, and the leftover-schema check failed. Removed only that confirmed test residue after the run ended, then reran the affected checks (**10 tests / 22 assertions**) and the complete final suite/matrix sequentially. Configuration/route-cache isolation remains in force. The isolated Apache server, fresh synthetic browser schema and private access file were removed after browser verification; no clinic records were copied or changed.

### Phase 6 — Deep DRY refactor + dead-code removal (behaviour unchanged)
Each step is guarded by: full suite + `route:list --json` diff (identical unless a change is listed) + link crawl + matrix.
- [x] 6.1 **Routes:** one shared definition per module with per-panel differences; `web.php`, `staff.php` and `doctor.php` use it. Every URL and route name stays identical.
- [x] 6.2 **Role routing:** the per-controller `getIndexRoute()` / `resolve*Route()` copies and the ~130 view ternaries `isRole(..) ? route(a) : ..` → `getRouteByRole()` / `redirectByRole()`; overlapping helpers merged; default password `123456` in one constant.
- [x] 6.3 **⋮ action menu:**
  - one component `<x-row-actions>` (dropdown not clipped by the table, keyboard-usable, delete last);
  - an item is drawn only when `canUseModule()` / the record rules allow it;
  - existing JavaScript hooks are kept;
  - it replaces all 23 action views, the queue rows and the patient-history tables;
  - every action is clicked once in the browser.
- [x] 6.4 **One medicine picker** script shared by consultation create / edit (and dispense record / prescription forms where it fits).
- [x] 6.5 Split `DocumentIssuanceController` (2,596 lines in the original inventory; 2,695 at extraction) into services (consultation medicines, walk-in patient matching, images, routing); characterization tests first.
- [x] 6.6 **Dead / broken code sweep** (each removal backed by a search showing nothing uses it):
  - unreferenced classes, views, routes, JavaScript files and helpers;
  - `fullcalendar`, `izitoast`, the commented-out seeders;
  - `FixDashboardPermissions`, `PerformanceMonitor`, `WarmUpCache` and the `routes/upgrade.php` `lang-js` route (check each);
  - `paytm` and currency / amount-to-word leftovers;
  - anything left over from the designation / station removal;
  - **Livewire 2 JavaScript that never runs on Livewire 3**: `livewire:load` + `window.livewire.hook('message.processed')` in `resources/assets/js/custom/custom.js` (Select2 is not re-initialised after Livewire updates) and `doctors/detail.js`; `beforePushState` / `beforeReplaceState` in `livewire-turbolinks.js`. Port to `livewire:init` + `Livewire.hook('commit', …)` or remove, with a browser check;
  - regenerate `_ide_helper*`.

### Phase 7 — Real-time updates with Laravel Reverb (replaces every timer)
**Done 2026-10-19** (owner's request: do Phase 7 before the rest of Phase 5; it approves the new packages). Details in "Phase 7 — progress 2026-10-19" below.
- [x] 7.1 **Spike**:
  - [x] composer's PHP pin raised to 8.2 (`require` `^8.2`, platform 8.2.0) and `laravel/reverb` 1.12 added;
  - [x] `laravel-echo` and `pusher-js` added (bundled by Mix, no CDN);
  - [x] Reverb runs on Windows next to `artisan serve`; a client on the page's own host connects and receives a notice sent from PHP.
- [x] 7.2 **Server:**
  - [x] private channels per module (only users who can use the module may listen) + one per user (`routes/channels.php`);
  - [x] one `ModuleChanged` event carrying the module and the time only (**no patient data, no ids**: every screen asks the server again), sent after the save commits (`App\Support\Realtime`);
  - [x] a stopped Reverb **never** makes a save fail (one warning in the log, then 30 s without trying).
- [x] 7.3 **Browser:**
  - [x] one Echo setup (`resources/assets/js/realtime/realtime.js`, connects to the same host the page came from, no fixed IP);
  - [x] tables refresh themselves on their module's events; the queue pages reuse their partial refresh;
  - [x] "Live updates off" badge + 60 s fallback only while disconnected;
  - [x] all `setInterval` / `setTimeout` reloads and `wire:poll` removed (a test fails if one comes back).
- [x] 7.4 Wire every module: Queue, Consultations (+ "needs assessment" badge), Prescriptions / verify, Inventory (+ badges), Dispensing (history / stock-out), Lab, Certificates, Reports (incl. yearly reports), Notifications, Dashboards, Patients, Settings lists (`Realtime::TABLES` and `Realtime::PAGES`).
- [x] 7.5 Startup script starts Reverb; firewall rule for its port written down for the owner (`docs/commands.md`, "Real-time updates").
- [x] 7.6 Tests:
  - [x] an event per save;
  - [x] channel access denied without the module;
  - [x] a save still succeeds with Reverb down.
  Live check:
  - [x] the nurse adds to the queue → the doctor's screen updates without a reload;
  - [x] stop Reverb → badge + fallback;
  - [x] start it → live again.

### Phase 8 — Improvements (each with tests + a live check)
- [x] 8.1 **Searchable "Add medicines"** (consultation create / edit, nurse and doctor), completed 2026-10-20 (checks below):
  - search by brand, generic name, strength, category and SKU;
  - each option shows stock in hand and nearest expiry;
  - out-of-stock / expired options are disabled.
- [x] 8.2 **Merge duplicate medicines / stock** (admin; sample: #41 / #42, #50 / #55):
  - preview, then move batches, ledger, consultation / prescription / dispense / stock-in lines to the kept medicine;
  - recompute totals; archive the duplicate; audit snapshot;
  - the yearly inventory report (4.5) shows the merged history under the kept medicine;
  - the same tool merges duplicate categories / generics.
  - Owner approved matching generic ingredient and unit; different strengths stay separate. Completed 2026-10-21; verification below.
- [x] 8.3 **Merge duplicate patients** (admin), completed 2026-10-21 (verification below):
  - candidates by name + birth date + sex, university ID, phone;
  - preview, then move consultations, prescriptions, queue, lab requests, dispense records, addresses and images;
  - archive the merged-away patient with "merged into #…"; audit snapshot.
- [x] 8.4 **Stock disposal & count** (2026-10-21; verification below):
  - dispose expired / damaged stock per batch with a reason;
  - record a physical count per batch with a preview of the differences;
  - both through the inventory service only (ledger types `disposal` / `adjustment`), so the yearly report (4.5) shows them.
- [x] 8.5 **Notification center** (2026-10-21; verification below):
  - bell with per-user read / unread alerts (low stock, expiring / expired stock, new queue entry for doctors, consultation needing assessment), using the existing `notifications` table;
  - pushed live; "mark all read".
- [x] 8.6 **Queue display board** (2026-10-21; verification below):
  - owner approved stable tickets restarting at 1 each clinic day (2026-10-21); queue movement must not renumber a ticket;
  - waiting-area screen on a secret link: now serving / next numbers and rooms, no names;
  - chime on call; live via Reverb.

### Phase 8 — progress 2026-10-20: 8.1 searchable Add medicines

The shared consultation picker now uses the already bundled **Select2 4.x** widget. Searches match all entered words across display / brand name, saved and linked generic names, available strengths, category and SKU, ignoring case and accents. Results display stock in hand and nearest usable expiry; unknown dates (including the legacy opening-balance sentinel) display "Not recorded". Text is inserted with DOM / jQuery text methods, so medicine labels cannot become HTML. The widget supports keyboard search / selection / Escape, has accessible control names, and fits a 440px viewport.

The existing role-aware catalog route accepts `include_unavailable=1` for this picker. It includes disabled expired / out-of-stock choices and uncategorized legacy medicines; existing callers still receive their categorized, dispensable-only list. Availability and strength choices come from positive, unexpired batches **as of today**, including the expiry date itself, rather than a potentially stale stored total. Aggregate-only medicines retain their legacy stock fallback only when no batches exist. Generic metadata and batches are loaded in fixed queries; reading the catalog does not change inventory.

On edit, saved row IDs, sections, strengths and quantity credits remain intact, including a now-empty / expired saved line that can be retained, reduced or removed. Changing to another medicine no longer borrows the previous medicine's quantity credit. Plan medicines remain doctor-only; duplicate guards and removal confirmation retain their previous behavior. Add buttons wait for the catalog; a failed load shows a message instead of allowing empty rows. No route, migration, dependency or Artisan-command change.

**Verification:** three new PHP catalog cases (metadata / expiry / read-only stock, unavailable / legacy choices, fixed query count) plus the updated six-panel configuration contract. Focused consultation / inventory checks **26 tests / 233 assertions**, passing. New standalone Chrome regression covers create / edit × staff / doctor, every search field, mixed case / accents / multiple words, no match, disabled choices, escaped labels, keyboard, saved expired strengths, correct edit quantity credits and loading / failure handling. The earlier picker DOM / stock / ID / permissions / duplicate / removal contract still passes unchanged. **14 deliberate faults detected**: five PHP catalog faults (availability inclusion, stale stock, expiry boundary, nearest expiry, N+1) and nine browser-only fault copies (five search-field omissions, enabled unavailable options, wrong edit credit, missing saved strength, missing loading guard). Controller bytes restored after every PHP fault; one transient Windows restoration error was repaired and the whole mutation / focused sequence rerun successfully. Browser faults never changed application source or built assets. Context7 Laravel **10.x** eager loading and Select2 matching / templates / disabled-option documentation checked against installed code. `npm run dev` passed; generated translations restored.

**Live check:** six real pages, admin / Staff (Nurse) / doctor create and edit, using a fresh synthetic database: each search field returns the correct medicine with **12 usable units** and its nearest expiry; a new 500mg line is capped at **7**, its saved line at **10** including the 3 already used, and a saved empty line at **4**. Expired 100mg stock is absent from strength choices; expired / empty medicines are visible but disabled for new rows. Desktop and 440px search / selection / Escape checks pass; zero JavaScript errors, external requests or server failures. Working `norsu_clinic` unchanged; no clinic data copied. Second-device LAN validation remains Phase 9.

**Final safety nets:** full suite with crawl **777 tests / 8,248 assertions**, passing (**05:30.075**); crawl **1,609 requests / 15 variants**, zero broken / crashed / lazy-table / capped results. All **459 source routes** unchanged; access matrix **6,288 requests / 393 routes / 16 variants** identical to baseline, zero server errors or access violations. Browser search tests reset to a no-match state between fields; reopened keyboard-selected widgets are exercised with actual keystrokes, as advised by Context7 Playwright documentation. Synthetic server, schema, access file and cached configuration removed after checking. No new Artisan command.

### Phase 8 — progress 2026-10-21: 8.2 duplicate medicines / stock

Admin-only **Merge duplicates** links on Inventory, Categories and Generics open a shared preview / confirmation page. The retained record keeps its name and details; the duplicate stays as an archive with `merged_into_id`, `merged_at` and `merged_by`, and leaves ordinary lists, binding and medicine pickers. Integrity duplicate-candidate warnings exclude completed medicine archives while still reporting active duplicates, including on a database awaiting the merge migration. Ordinary unused-record deletion is unchanged; retained records with archive links cannot be deleted. A database without the new columns shows an update-needed message. Migration `2026_10_20_190000_add_inventory_merge_archives` adds the archive columns, three self-referencing foreign keys and `medicine_batches.original_batch_number`; it does not merge records by itself. The current schema catalog is **50 keys** (previously 47). The working `norsu_clinic` has not been migrated or changed.

Medicine previews show recorded ingredient / unit / default strength / SKU, linked-row counts, usable and total stock, and each incoming batch's proposed reference. Both medicines must have the same generic ingredient and unit; missing values or mismatches block confirmation. Different strengths remain distinct. Stock batches retain their IDs, quantities, expiry dates and ledger links / dates; only a colliding batch number gets a distinct previewed reference, with its original retained. Aggregate-only legacy stock receives opening batches through `MedicineInventoryService` on confirmation. Totals are recomputed there. The yearly inventory combines both dated histories under the retained medicine, without inventing a stock movement.

All consultation, prescription, dispense, stock-in and legacy usage references move while retaining their rows and instructions. An unset batch / clinical-line strength is frozen to the source medicine's saved strength (or “N/A”), so moving it to a different default does not change its meaning. Distinct saved consultation / prescription lines that converge to the same medicine and strength remain editable; new duplicates are still refused, and the prescription warning follows the same rule. Returns from a moved consultation still reach the original batch even after expiry. Category / generic merges update IDs and saved text labels, including legacy null-ID rows; preview counts and audit IDs use the transfer's SQL comparison, so accent / case matching follows the database consistently. An old medicine form resolves an archived label to the retained record instead of recreating the duplicate.

Confirmation requires a checkbox and an authenticated encrypted preview bound to the actor, identities, current data and date, expiring after 15 minutes. Missing / changed / expired / tampered / replayed previews are rejected. Reopening an archived HTML preview renders an explanation without a confirm token; it cannot redirect back to itself. JSON preview errors retain their 422 response. Parent and linked rows are locked in a transaction with deadlock retry; stock / clinical writes use the same parent lock and reject an archived ID. Audit creation is part of the transaction, so audit failure rolls back the whole merge. The audit holds inventory identities, moved row IDs and batch renames, with no copied clinical text. Cache clearing runs after commit. Existing SQL-based Reverb notifications cover the changed tables.

**Verification:** 20 new PHP cases. Initial HTTP / browser regressions failed, then passed; review added failing regressions for legacy-label SQL matching, a self-redirecting stale HTML preview and archived duplicate-candidate warnings, all fixed. Final focused **80 tests / 678 assertions**, passing (**02:34.118**); full safety-net results are below. **19 faults detected**, 15 PHP and four script-copy faults (compatibility, stale / actor checks, archive visibility, references, batch collisions, ledger dates, rollback, stock totals, stale labels, label collation, HTML recovery, archived candidate warnings and new / historical duplicate guards). Seven PHP mutation targets restored byte-for-byte; script faults use ignored copies. Original and searchable picker standalone contracts still pass; the new prescription warning contract passes. Context7 Laravel **10.x** transaction, locking, deadlock retry and authenticated encryption guidance checked against installed code. Asset build, PHP syntax and inline review / diff checks pass. Interrupted earlier full runs are discarded; a build/test overlap was corrected and the focused run repeated after the build finished.

**Live checks:** fresh synthetic data only. Desktop / 440px medicine preview and confirmation, category / generic merges, archived options / URLs, Staff and doctor access boundaries, saved consultation rows, and saved prescription instructions / duplicate warning pass. Combined stock is **17 total / 13 usable / 4 expired**. Two admins merging the same source produce one success and one 422; simultaneous merge / stock-in also produces one success and one 422, with quantities, audit and ledger identities checked afterwards. Yearly screen and CSV show one retained row with **20 additions / 17 remaining**, including 4 expired. Zero JavaScript errors, external requests or server failures. Post-merge `db:integrity --compare-fresh`: all **50 keys**, **0 errors / 0 warnings**, structure identical to a fresh install. Second-device LAN checks remain Phase 9.

**Final safety nets:** `XDEBUG_MODE=off LINK_CRAWL=1 php vendor/bin/phpunit`: **797 tests / 8,424 assertions**, all passing (**07:48.118**, none skipped). Crawl **1,614 requests / 15 variants**, zero broken / crashed / lazy-table / capped results. Access matrix **6,320 requests / 395 routes / 16 variants**, zero server errors or access violations; all **6,288 prior cells** and role permission sets are identical, with only the two new admin merge routes added. Source manifest **459 → 461**, every prior entry unchanged. The contract fixture adds exactly those two entries while preserving the prior 394 panel entries and registration order; four contract tests / 30 assertions pass. The first full run exposed the expected stale fixture, which was updated and the complete suite rerun successfully. Final verification used `XDEBUG_MODE=off` to avoid debugger overhead. Synthetic server, schema, access file and cached configuration removed; working `norsu_clinic` unchanged. No new dependency or Artisan command; commands.md records the new integrity-key count and pending deployment migration. Next: **8.3 duplicate patients**.

### Phase 8 — progress 2026-10-21: 8.3 duplicate patients

Admin-only **Patients › Merge duplicates** suggests up to 100 pairs by normalized name / birth date / sex, university ID (including the legacy patient identifier) and phone. Suggestions do not merge automatically; the administrator chooses the duplicate and the record to keep, reviews identity / medical summaries and linked-record counts, then confirms. A shared phone is only a suggestion. Only active patient-only accounts can merge; archived, already merged, shared-account or staff / doctor / admin identities are rejected. A database awaiting the migration shows an update-needed message.

**Owner decision:** the selected kept record retains its identity, current address / picture and medical summary. Source identity / contacts / affiliation, medical summary, insurance / primary care details stay in the source archive and are accessible from merge history and the kept patient's page. Addresses move as labelled history without replacing or changing the kept current address; source profile files move to a separate preserved collection, retaining IDs, file paths and bytes even when the kept profile picture is later replaced. Chained merges retain the first source provenance and link all archives to the final kept patient. Archived-list badges say “Merged into #…”. Patient and user restoration refuse merged sources; ordinary archival / restoration remains supported.

Both patient identity paths transfer atomically: `document_issuances.user_id` (consultations, certificates, excuse slips) and `lab_requests.patient_user_id`; `prescriptions.patient_id`, `medicine_bills.patient_id` and `patient_queues.patient_id`. Archived clinical rows are included, keeping their archive state. Document IDs, saved clinical text / demographics, authors, consultation images, child records and stock / ledger links remain intact. Open queue attachment points at the latest live consultation. Multiple open queue entries today block confirmation until resolved through the queue controls; no entry is silently dropped.

An actor-bound encrypted preview expires after 15 minutes and fingerprints both identities and linked rows. Changed / missing / tampered / expired / replayed / other-actor submissions are refused. Parent and linked rows lock in a transaction with deadlock retry; new clinical references and patient edits acquire the same parent locks and reject merged sources. Audit insertion is inside the transaction and must actually persist: both an exception and a silently cancelled insert roll back the transfer / archive. Audit stores source / target IDs, moved IDs and fingerprint, without copying clinical text or credentials. Lookup caches clear after commit; existing SQL-based real-time notifications cover the writes. Old HTML preview URLs render an explanation without a confirmation token.

**Verification:** 17 new PHP cases; initial missing-tool RED, final focused **67 tests / 437 assertions** GREEN. Eight deliberate faults detected (document transfer, account archival, fingerprint, actor binding, queue conflict, media ownership, restore guard and stale clinical references); three mutation targets restored byte-for-byte. Review's cancelled-audit regression failed before the persisted-audit check and passes afterwards. Two older tests now use actual patient fixtures instead of staff / admin accounts; schema output expects 52 keys. Context7 Laravel **10.x** transactions, locking, retries and soft-delete guidance checked against installed code. Thirty changed PHP files pass syntax checks; inline review and diff checks pass. No dependency, JS / CSS, asset-build or new Artisan-command change.

**Browser / data:** desktop / 440px preview, confirmation, source review, complete original details, archive badge / hidden Restore action, archived binding and stale HTML recovery pass. A changed source rejects an old confirmation with 422; a new preview succeeds. Two admins confirming the same pair produce one 200 and one 422. Staff / doctor valid-CSRF submissions are denied. Zero browser errors, external requests or server failures. Post-merge IDs, summaries, queue attachment, audit count and unchanged stock verified. Synthetic `db:integrity --compare-fresh`: all **52 keys**, **0 errors / 0 warnings**, identical to fresh. Only fresh synthetic data used; temporary server, schema, access file and caches removed. Working `norsu_clinic` unchanged. Migration `2026_10_21_112000_add_patient_merge_archives` adds patient merge metadata, historical-address provenance and two foreign keys; pending on the working database, along with earlier deferred migrations. `user:restore` refuses merged patient archives; commands.md records that behavior and the schema change.

**Final safety nets:** **814 tests / 8,554 assertions**, all passing (**05:52.386**, `XDEBUG_MODE=off`, none skipped); crawl **1,618 requests / 15 variants**, no problems; source routes **461 → 463**, all prior entries unchanged; matrix **6,352 requests / 397 routes / 16 variants**, all 6,320 prior cells and role permissions identical, zero access violations or server errors. The route contract adds exactly two admin / Manage Patients entries while preserving prior 396 panel entries and order. Next: **8.4 stock disposal / physical count**; 8.5–8.6 and Phase 9 second-device LAN checks remain.

### Phase 8 — progress 2026-10-21: 8.4 stock disposal / physical count

Inventory now links **Stock disposal and physical count** for administrators and Staff with Manage Medicines, following existing inventory write access; doctors retain inventory viewing. Select a medicine and enter only the batches being checked. Count mode records actual units remaining (including zero); expired / damaged disposal records units to remove. A reason is mandatory. Preview lists recorded / resulting quantities and signed differences; explicit confirmation follows. Expired disposal requires a past expiry date, including the established rule that stock is still usable on its expiry date. Missing batches, another medicine's batches, fractional / negative / oversized quantities and excessive disposals are rejected. Batch identity, strength and expiry never change. Aggregate-only legacy stock must first have identifiable batches recorded; no batch or quantity is invented by reading the form.

All stock writes use `MedicineInventoryService::recordBatchCount`, under the same medicine / batch locks as dispensing and merges. Changes create `disposal` or signed-direction `adjustment` ledger evidence with positive moved units, correct remaining balance, actor, reason and audit reference; totals / usable stock and lookup caches update. An unchanged count creates an audit without a zero ledger movement. The audit must persist and is atomic with every selected batch, ledger and total. Thrown or cancelled audit / ledger creation rolls back the operation. Actor-bound encrypted previews expire in 15 minutes, fingerprint stock / ledger / date and consume a one-time audit nonce, so even unchanged counts cannot replay. Changed stock requires a fresh preview; stale HTML confirmation returns to the GET form rather than a POST-only URL. Yearly report calculations already account for these ledger types and reproduce the resulting stock equation.

**Verification:** eight new PHP cases, initial missing-route RED → GREEN; stale HTML recovery failed before its fix and passes. Focused **26 tests / 247 assertions** (stock controls, yearly report and route contracts). Six deliberate faults detected: quantity, disposal type, fingerprint, one-time confirmation, expiry boundary and cancelled-audit acceptance; two targets restored byte-for-byte. Ten changed PHP files pass syntax; inline review / diff checks pass. Context7 Laravel **10.x** transaction, locking, retries and authenticated encryption guidance used. No migration, dependency, JS / CSS or Artisan command added.

**Browser / data:** Chrome administrator counts in both directions, expired disposal, Staff damaged disposal, desktop / 440px confirmation, two simultaneous confirmations (**200 + 422**), stale HTML recovery and doctor write denial pass. Zero browser errors, external HTTP requests or server failures. Synthetic stock / expiry totals, four exact audits and the yearly disposal / adjustment equation checked after the writes. Synthetic `db:integrity --compare-fresh`: **52 keys, 0 errors / 0 warnings**, matching fresh. Fresh synthetic data only; server, schema, QA access and browser caches removed; working `norsu_clinic` unchanged. Six new admin / Staff routes preserve all prior entries and the previous 398 panel fixture records / order. **822 tests / 8,616 assertions** passing (**05:03.948**, `XDEBUG_MODE=off`, none skipped); crawl **1,626 requests / 15 variants**, zero failures; routes **463 → 469**, all prior entries unchanged; matrix **6,448 requests / 403 routes / 16 variants**, all prior 6,352 cells and permissions identical, zero server errors or access violations. Next: **8.5 notification center**, then **8.6 queue board** with the owner's approved stable daily tickets; Phase 9 LAN checks remain.

### Phase 8.5 — Notification center, completed 2026-10-21

Every clinic role now has a bell and notification center, with per-user unread / read / full-history filters, individual read actions and mark-all-read. Unread totals include all active alerts, while the bell previews twenty. Alerts reuse the inventory thresholds and 30-day expiry window; doctors also receive waiting-queue and incomplete Assessment / Plan alerts. Titles / details contain stock information or record references, with no patient names or clinical content. Role permissions filter retrieval and actions; another user's alerts cannot be read or marked. Active conditions retain read state; resolved conditions remain in history, and recurrence creates a fresh alert.

Nullable alert metadata extends the existing notifications table without changing legacy rows. Refreshes serialize on each recipient, deduplicate active conditions, preserve read state while updating details and evaluate usable batch stock as of today. Source commits register one refresh at HTTP termination, after insert IDs have been read; rollback registers none. This avoids synchronous notification INSERTs corrupting PDO lastInsertId inside QueryExecuted. Consumed termination work cannot rerun on later requests. Alert failures are contained; `notifications:refresh` runs through the existing scheduler every minute, including expiry boundaries without a new stock write. No dependency or external service was added. The existing Reverb signal refreshes each browser's own bell and center. Earlier installations keep their legacy bell until migrated.

**Verification:** initial RED → GREEN; focused 25 / 211, then browser-found inventory destination regression 5 / 33 (stock links open the actual Inventory route). Five deliberate faults detected, targets restored exactly. Full suite / crawl **828 tests / 8,658 assertions**, all passing (**05:36.674**, Xdebug off, none skipped); crawl **1,682 requests / 15 variants**, no failures. Routes **469 → 472**, three center routes, all prior entries unchanged; panel fixture 404 → 407, prior order preserved. Matrix **6,496 / 406 routes / 16 variants**, all previous 6,448 cells / role permissions identical, zero server errors / access violations. Seventeen changed PHP files pass syntax; inline review, asset build and diff checks pass. Context7 Laravel 10 commit / notification guidance used.

**Browser / data:** real Chrome Admin, Staff and Doctor centers receive true Reverb updates while open; individual reads stay private, read history / mark-all / 440px pass, with no JS errors, external HTTP requests or server failures. Synthetic integrity: **52 keys, 0 errors / 0 warnings**, fresh structure parity. Own HTTP / Reverb listeners, schema, QA credentials and browser cache removed; working `norsu_clinic` unchanged. One migration and one documented Artisan command added; apply migrations and run the existing scheduler on deployment. Next: **8.6 secret queue board**, with the approved stable daily tickets.

**Follow-up 2026-10-22 (rehearsal of the real clinic dump, an independent review of Phases 5.14–8.6):** the synthetic upgrade rehearsals could not show it, but on the real dump `db:integrity --compare-fresh` finished with `ERROR Structure is identical to a fresh install – notifications: index (user_id) is not in a fresh install` (exit 1, no data problem). The clinic's `notifications` table has an explicit key `notifications_user_id_foreign (user_id)` from the old dump; MySQL drops the key it creates implicitly for a foreign key when the alert migration's `unique (user_id, event_key)` covers it, but never an explicit one. New migration `2026_10_22_090000_drop_redundant_notifications_user_index` drops only that redundant single-column key, and only while another key starting with `user_id` backs the foreign key (a fresh install and a copy without the alert index are left alone; `down()` restores the key so the alert migration can still be rolled back). `NotificationsLegacyIndexMigrationTest` (7 tests) rebuilds the clinic's table shape; three deliberate faults (never drop, drop without a backing key, no restore on rollback) are each caught. Found on the way and fixed with the owner's approval: on MySQL 9.7.1 an alert row whose `event_key` is NULL was not checked against `users` while the UNIQUE `(user_id, event_key)` index backed the foreign key (rows with a key were refused, ON DELETE CASCADE worked; a fresh install had the same gap). New migration `2026_10_22_080000_reorder_notifications_alert_unique_index` (sorted before the drop migration; Codex's pushed alert migration is untouched) adds a plain `user_id` index when missing, swaps the unique pair to `(event_key, user_id)` and leaves every row alone, so the clinic copy and a fresh install end in the same structure (`PRIMARY`, `notifications_user_id_foreign (user_id)`, `notifications_event_key_user_id_unique (event_key, user_id)`) with the key enforced for NULL-key rows too; the drop migration is then a no-op and is safe in either order. `NotificationsAlertIndexOrderMigrationTest` (13 cases: six migration paths, rows kept, uniqueness, an old index under another name, second run, untouched copies, rollback chain); seven deliberate faults in the new code are each caught. Shared helpers: `Tests\Concerns\BuildsLegacyNotificationsTable` and `App\Support\LegacyIndexes`.

### Phase 8.6 — Secret queue display board, completed 2026-10-21

Admin **Queue › Waiting-area board** generates an encrypted secret link for the waiting-area browser. Rotating it immediately revokes the old HTTP link and signals connected displays to clear their panels. The standalone page uses local assets and shows today's serving / next ticket numbers and safe room codes only. It loads no authenticated layout, patient names, IDs, notes or clinical fields. Free-text room labels appear publicly as “Clinic room”; codes such as 12 / 203A remain readable. Responses disable caching, referrers and indexing. The public Reverb channel uses a hash of the unguessable link; events carry only changed / revoked signals, and the browser refetches the sanitised state.

Tickets follow the owner's decision: start at 1 each clinic day and remain stable through priority changes, calls, completion, deletion and patient-history moves. A date-keyed InnoDB sequence serializes allocations; a unique date / ticket index guards duplicates. Existing queue rows are backfilled by date, creation time and ID, with no changes to prior columns. Ordinary queue screens show the same ticket. Cancelled / historical / archived-patient entries stay off the public board; reading it never changes clinical rows. A server-timed midnight refresh clears the previous day even without a queue event.

The optional sound button unlocks local WebAudio; a new call plays one two-tone chime. Initial loads and repeated refreshes stay silent. Disconnected boards use the 60-second fallback, reconnect fetches current state, and transient HTTP failures retry. No dependency or Artisan command was added. One migration and a local Mix bundle were added; deployment uses the normal migrate / asset-build steps and existing Reverb server.

**Verification:** seven new PHP cases, initial RED → GREEN; focused **7 / 62**, with an additional daily-accessor assertion included in the final suite. Seven deliberate faults detected (allocation, immutable ticket, historical date, secret validation, room privacy, public scope and post-commit delivery); sources restored exactly. Browser-found hidden-panel CSS regression failed before its fix and passes. **836 tests / 8,727 assertions**, all passing (**15:20.436**, Xdebug off, none skipped); crawl **1,683 requests / 15 variants**, no failures. Routes **472 → 476**, only two admin settings and two public secret-read routes; all previous entries unchanged. Panel contract **407 → 409**, prior order preserved. Matrix **6,528 / 408 routes / 16 variants**, all 6,496 prior cells / permissions identical, zero server errors / access violations. Seventeen PHP syntax checks, build, inline review / diff checks and Context7 Laravel **10.x** public-channel / explicit-payload guidance pass.

**Browser / data:** real Chrome anonymous board, Admin / Staff / Doctor actions, concurrent additions yielding tickets **[1,2,3]**, stable priority order, safe rooms, one chime per call / no initial or repeated chime, second display through `localhost` alongside `127.0.0.1`, 440px board / Admin queue, live revocation and old-link 404 all pass. No patient data in HTML / JSON / WebSocket messages; no JS errors, external HTTP requests or server failures. Actual Reverb stop: clinical completion succeeds; the configured fallback callback fetches correctly while disconnected; restart restores the connection and subsequent live calls. Final tickets / counter / statuses and unchanged stock verified.

Synthetic upgrade rehearsals under normal and session-only MyISAM defaults preserve all prior queue columns; yesterday receives **[1,2]**, today **[1]**, and the new counter stays InnoDB. Both final integrity checks: **52 keys, 0 errors / 0 warnings**, fresh parity. Own HTTP / Reverb listeners, synthetic schemas, access and cache removed; working `norsu_clinic` unchanged. **Phase 8 is complete. Next: Phase 9 final verification, including actual clinic runtime / second-device LAN checks; these local hostname checks do not substitute for that.**

### Phase 9 — Final verification, again
- [x] 9.1 Full suite · link crawl (incl. users with permissions removed) · route × role matrix · `route:list` diff reviewed. **Done 2026-10-22:** suite with `LINK_CRAWL=1` 863 tests / 8,808 assertions (code identical to the pushed HEAD apart from docs); `route:list` identical to Codex's 8.6 manifest (476 routes: method, URI, name, action, middleware); route × role matrix 6,528 requests / 408 routes / 16 variants run with `MATRIX_SAME_USER=1` (the baseline's mode; without it 31 doctor-ownership cells differ only because the four extra doctor variants are not the record creator): every cell, the permission middleware and the actor permission sets identical to the 8.6 baseline, 0 server errors, 0 access violations.
- [x] 9.2 Fresh rehearsal from the dump on the final code (both engines) · `db:integrity` exit 0. **Done 2026-10-22:** real clinic dump in a throwaway schema, session default engine InnoDB and MyISAM (forced by a PDO init command, verified): 30 migrations, a second `migrate` does nothing, orphan-batch recovery, `db:integrity --compare-fresh` exit 0 (0 errors, the 3 expected warnings), identical to a fresh install.
- [ ] 9.3 Second full live walk-through of every module (the "Re-check" column in Phase 5) with two browser sessions for real-time. **Partly done 2026-10-22** (built-in browser, scratch copy of the real data, PHP built-in server through the project's `server.php`, Reverb, `admin.` / `nurse.` / `doctor.localhost` for three separate sessions): every parameter-free GET page of each panel fetched in its own role's session, 69 / 50 / 41 pages, no server error, no truncated page, no console error; the nurse's add-to-queue through the real form appeared on the doctor's open queue screen without a reload (Reverb connected, ticket 1), the doctor got the scoped "new waiting queue entry" alert with no patient name and the nurse did not. **Found:** `/admin/logs` (unlinked legacy log viewer, `AdminLogViewerController`) loads seven CDN resources (Bootstrap 4.0.0, DataTables, jQuery, Font Awesome from public hosts) and breaks the offline rule; the newer `/log-viewer` is self-contained. **Still open:** the interactive module-by-module workflows (consultation, prescription, dispensing, lab, certificates, reports, settings) were not re-clicked, and a second LAN device / the actual clinic runtime cannot be done from this PC.

### Phase 10 — Documents, memory, commits
- [ ] 10.1 This file kept current (boxes ticked, findings filled in per module); [commands.md](commands.md) has every command added by Phases 1–9.
- [ ] 10.2 [STATUS.md](STATUS.md):
  - fix the stale "not pushed" lines; counts and timeline;
  - clinic deployment checklist (§9 below).
- [ ] 10.3 Memory notes: clinic MyISAM upgrade, Reverb real-time, RBAC (`canUseModule`, Staff = Nurse, no designation / station), inventory (dispense history view, yearly report), reports (COL, Guest, yearly), local MySQL 9.7 + test schema.
- [ ] 10.4 One commit per phase, pushed after it is verified (standing go-ahead of the owner, 2026-10-09). Each push ends with a `handover:` commit that closes the START entry in [Handover.md](../Handover.md) (END part + current checkpoint).

---

## 4. Status of everything recorded so far, by workflow

Compiled from STATUS.md, the audit documents and the memory notes. 🆕 rows are new on 2026-10-08 and are scheduled in §3.

### 4.1 Upgrade / migrations / database
| Item | Status |
|---|---|
| Strict SQL mode, widened clinical text, NOT NULL defaults (09-30) | ✅ |
| Zero-date tolerance in the 15 new migrations (owner, 10-08) | ✅ (kept, reused) |
| Clinic tables MyISAM, 0 foreign keys, transactions void | ✅ Phase 1: converted by migration; 47 of 47 keys (the 4 orphan batches were recovered, owner decision A) |
| Illness / services migration on an InnoDB server with clinic data | ✅ Phase 1 (conversion first; migration re-runnable) |
| Zero date left in `sale_medicines.expiry_date` | ✅ Phase 1 (set to NULL) |
| Rehearsal on the **real** dump under both engines | ✅ R0 (the problem), R1 / R2 (the fix), each under MyISAM and InnoDB |
| Test schema missing on MySQL 9.7 → tests create their own automatically | 🆕 Phase 0.2 |
| Unique stock-in numbers, lab-request soft delete, Stock-out view from the ledger | ✅ |

### 4.2 Patients
| Item | Status |
|---|---|
| Doctors view + edit only; staff / admin add / archive / restore / reset | ✅ |
| Archived-account message on create / edit; name search; CSV with names | ✅ |
| `storePatient()` crash, role-blind patient links | ✅ |
| Congested row actions → ⋮ menu | 🆕 Phase 6.3 |
| Duplicate patient merge | ✅ Phase 8.3: admin preview / confirmation, preserved history / source archive / audit |
| Pass A / Pass B re-check | 🆕 Phase 5.2 |

### 4.3 Queue
| Item | Status |
|---|---|
| Call-next race, stale entries, archived patients, attachment sync, New / Previous form labels, Record form button | ✅ |
| Doctor queue routes not permission-gated | 🆕 Phase 3.5 |
| Add-to-queue page JavaScript never loads (patient search + latest-consultation preview) | ⏳ → Phase 5.22 |
| Timer polling → WebSocket | ✅ Phase 7 (2026-10-19) |
| Queue display board | ✅ Phase 8.6 (2026-10-21), stable daily tickets / secret link / safe rooms / chime / Reverb |

### 4.4 Consultations
| Item | Status |
|---|---|
| Stock returned to the right batch, duplicate lines, soft delete + restore, audit snapshot, illness / services | ✅ |
| Certificate / excuse-slip edit crash (10-08) | ✅ |
| Searchable "Add medicines" (name / generic / strength / category / SKU) | ✅ Phase 8.1 (2026-10-20): local search, usable stock / nearest expiry, disabled unavailable choices; shared picker from 6.4 |
| Controller split (2,596 lines) | 🆕 Phase 6.5 |
| Mass "Classify" for old visits | 🔒 later (one-at-a-time exists) |
| Consultation autosave drafts | 💡 §6 |

### 4.5 Prescriptions
| Item | Status |
|---|---|
| Own-name rule, verbal orders, deactivated not dispensed, two strengths, dispense lock | ✅ |
| Pass A / Pass B re-check | 🆕 Phase 5.5 |

### 4.6 Inventory
| Item | Status |
|---|---|
| FEFO, expired stock not counted, expiry alert, medicine-form stock through Stock-In, reconcile command | ✅ |
| Medicine create / edit page JavaScript never loads; generics pages too | ⏳ → Phase 5.22 |
| Duplicate medicine / stock merge | ✅ Phase 8.2 (2026-10-21): admin preview / confirmation, matching ingredient + unit, preserved batches / history, archive / audit and category / generic merges |
| Stock disposal & physical count | 🆕 Phase 8.4 |

### 4.7 Dispensing
| Item | Status |
|---|---|
| Stock-out counted pending prescriptions / patient "N/A" (R3-M3) | ✅ |
| **Dispense History misses consultation medicines** | ✅ Phase 2 (re-checked 2026-10-11; the patient history card fixed too) |

### 4.8 Lab Requests
| Item | Status |
|---|---|
| State machine, delete rules, soft delete + Deleted records, PDF `/` crash, unique numbers | ✅ |
| Lab request edit page JavaScript never loads | ⏳ → Phase 5.22 |

### 4.9 Certificates
| Item | Status |
|---|---|
| `?module=certificates` plural, edit crash, certificates-only staff search | ✅ |
| "NORSU Clinic" on certificate / excuse-slip PDFs and prints → "Medical University Clinic" | ✅ Phase 4.1 (the printed form codes "NORSU-CLINIC-FORM-0x" stay: owner decision, 2026-10-15) |
| Pass A / Pass B re-check | 🆕 Phase 5.9 |

### 4.10 Report Generation / Notifications & Alerts
| Item | Status |
|---|---|
| Patient Visits filters, Accomplishment Report (screen = PDF = Excel = CSV), Classify path | ✅ |
| College of Law code CL → COL | ✅ Phase 4.2 |
| Guest column always shown | ✅ Phase 4.3 |
| Yearly Accomplishment Report (Q1–Q4 + Year total) | ✅ Phase 4.4 |
| Yearly Medicine Inventory report (left from last year, additional, Q1–Q4 remaining) | ✅ Phase 4.5 |
| "Prepared by" shows the designation → role | 🆕 Phase 3.2 |
| `wire:poll.30s` → WebSocket | ✅ Phase 7 (2026-10-19) |
| Notification center (per-user, read / unread, live) | ✅ Phase 8.5 (2026-10-21) |
| Saved report filters / one-click monthly report | 💡 §6 |

### 4.11 Settings and access
| Item | Status |
|---|---|
| Roles name lock (P2-H1), seeder no longer resets Roles (R3-M7), admin 403 cache poisoning, `staff.module:a,b` bug | ✅ |
| **Manage User roles changes not applying** (§1.5) | ✅ Phase 3 |
| Nurse role with no accounts (May finding B) | ✅ Phase 3.1 (merged into Staff (Nurse)) |
| Role Designation, Assigned Station, Shift Schedule | ✅ removed in Phase 3.2 / 3.3 (columns and lookup tables stay, hidden) |
| Unreachable staff route groups (May finding C) | ✅ already gone (checked 10-08) |
| Notifications submenu linking to Inventory (May finding D) | ✅ gated; replaced by `canUseModule` in 3.4 |
| `reports` key in the station map (May finding E); pharmacist on the triage station (May finding A) | ℹ️ moot — designations and stations removed |
| `MedicineAvailabilityController` dead (staff audit #9) | ✅ removed 10-02 |
| Project name in Settings › General (`clinic_name`) | ✅ Phase 4.1 (a data migration; not yet run on the working database `norsu_clinic`) |

### 4.12 Security, operations, deployment (from STATUS §5 / §6)
| Item | Status |
|---|---|
| R3-L10 stale docs (`guide.md`, RECODE guide, empty `CLAUDE.md`) | ⏳ |
| R3-L11 startup script prints `127.0.0.1` but binds the LAN IP; root `.htaccess` absolute `/public/` | ⏳ |
| R3-L15 "Forgot password" dead end and reveals whether an e-mail exists | ⏳ |
| R3-L17 sessions last 120 idle minutes and survive closing the browser | ⏳ |
| R3-L19 backups: 30-day retention, unencrypted, half-restore needs manual recovery | ⏳ |
| M-16 lock file not re-tested on PHP 8.4 | ⏳ |
| `server.php` built-in server hardening (owner's commits) | ✅ — `BuiltInServerRouterTest` re-run in 5.21 |
| Rotate DB password; Laravel 11/12; rappasoft 3.8; npm advisories; merge to `develop` | 🔒 §2.4 |
| Hard-coded root-relative URLs in browser code (`/csrf-token`, `/change-language`) | ⏳ low (link audit §3.2) |
| No CDN / third-party asset on any page (offline rule) | ⏳ re-checked by the crawler and an internet-unplugged browser pass (Phases 5, 9) |
| Livewire 2 JavaScript leftovers (`livewire:load`, `window.livewire.hook`, `beforePushState`) never run on Livewire 3 → Select2 not re-initialised after Livewire updates (found 2026-10-08 while adapting the skills) | 🆕 Phase 6.6 |
| Every page works from another LAN device (not only on the server PC itself) | ⏳ Pass B is done from a second device / LAN address, Phase 9 |
| Two plain external reference links in the `/privacy-policy` text | ℹ️ text links, nothing loads from them (link audit §3.4) |
| Default-password admin redirect by `users.type` instead of role | ℹ️ not reachable with current data (link audit §3.3) |
| STATUS.md says the 10-08 commits are not pushed | 🆕 stale, fixed in 10.2 |

### 4.13 Tests and tooling
| Item | Status |
|---|---|
| 429 tests (338 at 10-08 13:00 + 28 from the owner's commits + 11 for the test-schema bootstrap + 52 for Phase 1) + opt-in link crawl | ✅ green on MySQL 9.7 (2026-10-09, 4,190 assertions, 1 skipped = the crawl, 6.5 min idle); route list identical to the Phase 0 snapshot (472, 0 differences) |
| Route × role matrix | ✅ rebuilt and run 2026-10-09 (7,938 requests, 0 server errors, no access leak); previous run 10-02 |
| Rehearsal runner on the real dump; `db:integrity` | ✅ runner (Phase 0.4, now also runs `db:integrity --compare-fresh`); `db:integrity` + `db:restore-foreign-keys` (Phase 1.5) |
| Role × permission effect test; crawler without designation × station actors | ✅ Phase 3.7 (`RoleModuleAccessTest`, `ModuleAccessMapTest`, `LinkCrawlTest` with permission variants) |

---

## 5. How "double check again and again" is done

| Net | When | Proves |
|---|---|---|
| Regression test per defect | while fixing | the bug cannot come back |
| Full phpunit suite | end of every step / phase | nothing else broke |
| Rehearsal on the real dump (MyISAM + InnoDB) + `db:integrity` | Phases 1, 9 | the clinic upgrade keeps every row and ends identical to a fresh install |
| Report checks (yearly inventory: left + additional − out = remaining; yearly accomplishment: Q1+Q2+Q3+Q4 = Year = existing report for the same year) | Phase 4, 9 | the new reports add up |
| `route:list --json` diff | Phases 6, 9 | the refactor changed no URL, name, method or middleware |
| Link crawl (admin, doctor, Staff (Nurse), permission-removed users, lazy tables) | Phases 3, 5, 6, 9 | no visible link answers 403 / 404 / 5xx; no "NORSU Clinic" left on screen; **no page loads a script, style, font or image from another host** (no CDN / third-party) |
| Browser with the internet unplugged (LAN only) | Pass B, Phase 9 | every page, PDF and the live updates work without internet |
| Route × role matrix | Phases 0, 9 | no access leak, no server error |
| Live browser walk-through on clinic data | Pass B of each module, Phase 9 | the screens behave for real users |

---

## 6. Improvements

**Adopted** (Phase 8): searchable Add medicines · duplicate medicine / stock merge · duplicate patient merge · stock disposal &
count · notification center · queue display board. **Also adopted** (Phase 4): yearly Accomplishment Report · yearly Medicine
Inventory report.

**Suggested for later** (owner to choose):
1. Idle-session timeout warning on shared clinic PCs (R3-L17).
2. Saved report filters and a one-click monthly Accomplishment Report.
3. Clean-up of old failed sign-in rows and activity-log retention.
4. Backup restore test and encrypted backups (R3-L19).
5. Consultation autosave drafts.
6. Mass "Classify" for old visits (only if many exist).
7. Reorder alert from the yearly inventory report (average quarterly use vs remaining).

---

## 7. Where the work will touch (main files)

- **Upgrade:** `config/database.php`, new migrations, `app/Support/LegacyMysqlMigration.php` (reused), new `app/Console/Commands/DatabaseIntegrity.php` and `RestoreForeignKeys.php` (documented in [commands.md](commands.md)).
- **Dispensing:** `app/Livewire/MedicineDispenseTable.php`, new `app/Models/DispenseHistoryEntry.php`, `resources/views/medicine-history/*`, `app/Http/Controllers/DispenseRecordController.php`.
- **Access / staff:**
  - `app/helpers.php`, `resources/views/layouts/menu.blade.php`, `routes/{web,staff,doctor,channels}.php`;
  - `RoleController`, `RoleRepository`, `resources/views/roles/fields.blade.php`;
  - `StaffRepository`, `StaffController`, `Create/UpdateStaffRequest`, `app/Rules/ValidStaffDesignationStationPair.php`, `app/Livewire/StaffTable.php`, `resources/views/staffs/*`;
  - `app/Http/Middleware/EnsureStaffModuleAccess.php`, `tests/Feature/StaffModuleAccessPolicyTest.php`.
- **Name:** `.env(.example)`, settings migration, `SettingTableSeeder`, landing / PDF / print / e-mail views, `lang/en/messages.php`.
- **Reports:** `app/Services/Reports/AccomplishmentReportBuilder.php`, `ReportQueries.php`, `app/Livewire/ReportGeneration.php`, `resources/views/livewire/report-generation.blade.php`, `resources/views/activity_logs/reports/*`, `database/seeders/CollegeSeeder.php`.
- **Consultations / stock:** `DocumentIssuanceController` (split), `document_issuances/forms/consultation_form.blade.php`, `document_issuances/edit.blade.php`, `MedicineController`, `app/Services/MedicineInventoryService.php`.
- **UI:** `resources/views/components/` (⋮ menu), the 23 `*action*.blade.php` views.
- **Real-time:** `patient_queue/index|doctor_view.blade.php`, `livewire/report-generation.blade.php`, `AppServiceProvider`, `webpack.mix.js`, `package.json`, `composer.json`, `startup-dev-environment.ps1`.
- **Tests:** `tests/Feature/Regression/*`, `tests/Feature/Audit/LinkCrawlTest.php`.

---

## 8. Open questions for later phases

None blocking right now. Questions that may come up during the work are asked when reached, not guessed:

- ~~exact wording of the PDF / print headers after the name change~~ (Phase 4.1: nothing depended on the old text length; the printed form codes were kept by the owner's decision of 2026-10-15);
- whether any medicine's unit in the clinic data is wrong (the yearly report shows each medicine's own unit, e.g.
  "tablet" vs "box");
- the Reverb port number if 8080 is already used on the clinic PC;
- **Dispense History speed at volume** (Phase 2 re-check): ~35 ms per 1,000 history rows for every click. Nothing to do while the history stays under about 20,000 rows (~0.7 s). Beyond that: either an index on the consultation dates plus per-source paging, or a maintained history table (every write path would have to keep it current). Decide when the clinic's volume warrants it.

---

## 9. Clinic copy — what will change for deployment (when the phases are done)

1. Back up the clinic database (`php artisan db:backup`).
2. Pull `changes_v2`; `composer install --no-dev` (PHP 8.2 needed for Reverb).
3. `php artisan migrate`. It:
   - converts the MyISAM tables to InnoDB, clears zero dates and restores the foreign keys (skipped ones are listed);
   - renames the clinic to "Medical University Clinic" and College of Law to COL;
   - merges the nurse role into Staff (Nurse);
   - adds inventory merge archives / original batch references and three archive foreign keys (Phase 8.2); it does not merge medicines, categories or generics automatically. Use the admin preview / confirmation tool after the integrity check.
   - swaps the alert unique index on `notifications` to `(event_key, user_id)` and keeps a plain `user_id` index (Phase 8.5 follow-up), so the user foreign key is enforced for every row and `db:integrity --compare-fresh` finishes without an error on the real data.
   It also runs every earlier step in STATUS §7.
   Then, once: `php artisan inventory:recover-orphan-batches` (dry run) and `--apply` (the clinic data has 4 stock batches of
   deleted medicines; this re-creates the medicines as placeholders, writes their stock off through the ledger and adds the
   last foreign key; see [commands.md](commands.md)).
4. `php artisan db:integrity` (add `--compare-fresh` once): must finish without errors. The other output is warnings
   (duplicate medicines, 8 phone numbers: `php artisan phone:normalize`, then `--apply` after a backup).
5. `npm ci && npm run prod`; set `APP_NAME="Medical University Clinic"` in the server's own `.env` (it is not in git; it titles the guest pages and names the session cookie, so everybody signs in once more).
6. Start through the startup script (now also starts Reverb); allow the Reverb port through the Windows firewall (owner).
7. Admin: review the **Staff (Nurse)** role in Manage User roles (nurses now get exactly what it allows).
8. Optional: set `default-storage-engine=InnoDB` in WAMP's `my.ini` (owner).
9. The remaining STATUS §7 steps (University Physician in Settings, default-password notice, `inventory:reconcile`, …).

---

## 10. Sources

[STATUS.md](STATUS.md) · [commands.md](commands.md) (the log of the commands this plan adds) · [link-audit-2026-10-08.md](link-audit-2026-10-08.md) ·
[route-access-audit-2026-10-02.md](route-access-audit-2026-10-02.md) ·
[accomplishment-report-2026-10.md](accomplishment-report-2026-10.md) · [full-reaudit-2026-10-01.md](full-reaudit-2026-10-01.md) ·
[full-reaudit-2026-09-30.md](full-reaudit-2026-09-30.md) · [full-reaudit-2026-09-30-pass2.md](full-reaudit-2026-09-30-pass2.md) ·
[remediation-status-2026-09-30.md](remediation-status-2026-09-30.md) ·
[staff-designation-station-access-audit-2026-05-05.md](staff-designation-station-access-audit-2026-05-05.md) ·
[audit-staff.md](audit-staff.md) · [module-crud-audit-2026-05-05.md](module-crud-audit-2026-05-05.md) ·
[automation-deep-audit-2026-05-18.md](automation-deep-audit-2026-05-18.md) · [audit-2026-04-29.md](audit-2026-04-29.md) ·
the project memory notes · the clinic dump `Downloads/10082026.sql` (not copied into the repo: it contains patient data) ·
the owner's yearly medicine inventory sketch (2026-10-08: Paracetamol, 150 left from 2026, 500 additional in 2027, Q1–Q4,
Q4 = remaining at year end).
