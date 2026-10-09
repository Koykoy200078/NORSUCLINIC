# Medical University Clinic — Audit & work plan (2026-10-08)

**Status:** Phases 0, 1 and 2 done and tested on 2026-10-09 (Phases 0-1: `7c6b5c5`, `d00cca3`, `c498798`; Phase 2: see
[Handover.md](../Handover.md); all pushed to `origin/changes_v2`); Phase 3 next · **Branch:** `changes_v2` (base `develop`) · **Started from:** `5155bf5` ·
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
| `.env` | `APP_ENV=production`, `APP_DEBUG=false`, `DB_STRICT=true`, `APP_NAME="NORSU Clinic"` | name changes in Phase 4 |
| Test schema | `norsu_clinic_test` (named in `phpunit.xml`) **did not exist** on this server | ✅ since Phase 0.2 the test run creates its own throwaway schema automatically; no manual database setup |
| `norsu_clinic` | *On 2026-10-08:* fresh install: 1 admin, no clinical data, 123 migrations, all InnoDB, 47 foreign keys. *Since Phase 1.8 (2026-10-09):* the **upgraded clinic dump**: 12 real users + 4 QA accounts, 126 migrations, all InnoDB, 46 of 47 foreign keys | the only working database |
| PHP | 8.3.31 locally; dump header says 8.2.26; `composer.json` platform pin = 8.1.0 | Reverb needs ≥ 8.2 → raise the pin (Phase 7) |
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

### 1.4 Dispense History misses consultation medicines (🆕)

`app/Livewire/MedicineDispenseTable.php` lists only `medicine_bills` (manual dispense records + dispensed prescriptions).
Medicines added in a consultation by the nurse ("Nursing") or the doctor ("Plan") go to `consultation_medicines` and the
stock ledger (`DocumentIssuanceController::handleMedicineDeduction` / `handleMedicineUpdates`), never to `medicine_bills`, so
they appear only on the Stock-out tab.

### 1.5 Manage User roles "does not apply" (🆕)

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
counting rules (see [STATUS.md §6](STATUS.md#6-needs-your-decision-)).

### 2.4 Still waiting for the owner (from STATUS §6)

🔒 rotate the DB password (old one in git history) · 🔒 Laravel 11/12 upgrade (framework / medialibrary advisories) ·
🔒 rappasoft tables 3.8 port · 🔒 npm runtime advisories / Vite migration · 🔒 merge `changes_v2` into `develop` · 🔒 PHI
columns in `activity_logs` and log retention · 🔒 untrack `public/messages.js` or keep committing it · 🔒 drop the hidden
designation / station / shift columns later (after the clinic runs without them).

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
  *Done:* `dispense-records.consultation` (3 new routes: admin, `staff.`, `doctors.`; 472 → 475 routes, the diff against the Phase 0 snapshot is exactly these three) → `DispenseRecordController::showConsultation` and `medicine-history/consultation.blade.php`: patient, recorded at / by, the medicine lines (dosage, Plan / Nursing, instructions, quantity) and the total. No clinical notes on this page. 404 for a deleted consultation or a document that is not a consultation. The "View Consultation" button needs `manage_request_documents` (+ the `consultations` module for staff / nurse roles, so a pharmacist does not get a dead link and a clinic head can open it).
- [x] 2.4 Tests:
  - rows for nurse- and doctor-added medicines;
  - edit / remove a line / delete / restore a consultation;
  - pending vs dispensed prescription;
  - search, sort and filter; access.
  Live check on clinic data: consultations 3 / 6 / 7 show 9 / 14 / 15 units.
  *Done:* `tests/Feature/Regression/DispenseHistoryTest` (15 tests / 112 assertions): doctor- and nurse-recorded consultations, no medicines, edit / remove lines, delete + restore, manual vs dispensed vs pending prescriptions, source filter, word search, sorting both ways, View-only consultation / prescription actions, role access and 404s. Takeover review added regressions for non-consultation documents, archived patient links and the clinic head's consultation link. All five mutations fail: document type, archived link, deleted consultation, prescription status and consultation access key.

  Live check on `norsu_clinic`: refreshing the view changed no stock or ledger rows; consultations 3 / 6 / 7 show **9 / 14 / 15** units. Headless Chrome over `192.168.2.13:8000` and `172.22.208.1:8000`: admin, doctor and clinic head pass source filtering, search, tab switching and all three detail pages, with no JavaScript errors, failed requests or internet dependencies. The QA nurse has no dispensing module under the existing policy: the menu is hidden and the list / detail pages correctly return 403 (Phase 3 removes this designation layer). Admin / doctor / clinic head have a working "View Consultation" link; the pharmacist's hidden link is regression-tested. `db:integrity --compare-fresh`: 0 errors / 3 existing warnings, schema identical to a fresh install.

  **Final verification (2026-10-09):** `LINK_CRAWL=1 php vendor/bin/phpunit` — **444 tests / 4,304 assertions**, all passing, none skipped (9 min 20 sec, 190 MB). The 16-user crawl made 1,143 requests over 1,093 pages: no broken links, crashes, lazy-table failures or capped crawls. Inline review and `git diff --check` passed. No new Artisan commands; [commands.md](commands.md) was checked and remains unchanged. Next: Phase 3.

### Phase 3 — Fix: Manage User roles really applies + Staff = Nurse
- [ ] 3.1 **Staff = Nurse, one role.** A data migration:
  - moves any account holding the `nurse` role to `staff`, then retires the `nurse` role (kept unassignable, hidden from the Roles screen and the staff form);
  - names the Staff role **"Staff (Nurse)"**;
  - leaves permissions as they are.
  The staff form loses its role picker.
- [ ] 3.2 **Remove Role Designation, Assigned Station and Shift Schedule** from:
  - the staff create / edit forms, `CreateStaffRequest` / `UpdateStaffRequest`, `ValidStaffDesignationStationPair`;
  - `StaffRepository`, `StaffController`, `StaffTable` columns, staff show page, profile;
  - the Accomplishment Report "Prepared by" (role instead of designation).
  The columns and lookup tables (`staff_profiles.role_designation_id / assigned_station_id / shift_schedule`, `staff_designations`, `clinic_stations`) stay in the database, unused and hidden (🔒 drop later).
- [ ] 3.3 **Remove the designation / station access layer:**
  - `getStaffDesignationModuleMap`, `getStaffStationModuleMap`, `getStaffDesignationStationMap` and `canStaffAccessModule` / `canStaffAccessAnyModule` (63 calls);
  - the `staff.module` middleware (`EnsureStaffModuleAccess`, 23 route guards);
  - `StaffDesignationSeeder` / `ClinicStationSeeder` from the seed run;
  - `StaffModuleAccessPolicyTest` (replaced by 3.7);
  - the obsolete JSON maps in `docs/` (`staff-*.json`).
- [ ] 3.4 One helper `canUseModule($module)`: the module's permission for the signed-in role. Map:
  - patients and queue → `manage_patients`;
  - consultations, certificates, prescriptions and lab → `manage_request_documents`;
  - inventory and dispensing → `manage_medicines`;
  - doctors → `manage_doctors`; specializations → `manage_specialties`;
  - settings, roles and CMS → their own permissions;
  - dashboard always on; reports and notifications open to every signed-in staff / doctor / admin (labelled on the Roles page).
  It replaces the 63 `canStaffAccessModule` calls (menu, dashboards, buttons) and matches the route middleware.
- [ ] 3.5 Routes agree with the menu: the doctor Queue gets `permission:manage_patients`; every staff / doctor group carries the permission of its module.
- [ ] 3.6 Honest Roles page:
  - shows only the permissions that apply to the role; the server drops the rest;
  - Clinic Admin is shown locked with an explanation; Patient read-only ("patients do not sign in");
  - corrected descriptions; role-aware Discard link; the select-all script works after Turbo navigation;
  - direct per-user permissions on non-admins are reported (`db:integrity`) and cleared, so the role is the only source.
- [ ] 3.7 Tests:
  - for every role × permission: revoke in the Roles screen, then on the next page load the menu item is gone, its pages answer 403 and dashboard links are hidden; re-grant brings them back (run with the real **file** cache);
  - Clinic Admin lock; nurse-role merge; staff form without the three fields;
  - link crawler reworked: one Staff (Nurse) user with the default permissions and one per permission removed, instead of every designation × station pair.
  Live check with admin, doctor and a nurse side by side.

### Phase 4 — Owner's requested changes (name, reports)
- [ ] 4.1 **Project name "Medical University Clinic"** (clinic name only):
  - `APP_NAME` (`.env`, `.env.example`);
  - the `clinic_name` setting through a migration (only when it still holds the old default "NORSU / Norsu Clinic");
  - `SettingTableSeeder`, `DefaultUserSeeder` / `DefaultSliderSeeder` texts;
  - hard-coded "NORSU Clinic" in views (landing pages, headers, titles), PDFs and prints (certificates, excuse slips, lab requests, dispense records), the registration e-mail, `lang/en/messages.php`, `app/helpers.php`;
  - this repository's docs titles.
  Kept: "Negros Oriental State University", address, logo / favicon, e-mail address, `norsu_clinic` database, backup file prefix, folders.
  Test: rendered pages and PDFs contain no "NORSU Clinic" (link crawler assertion).
- [ ] 4.2 **College of Law CL → COL:**
  - a migration renames `College of Law (CL)` → `College of Law (COL)` and the saved snapshot text on old consultations;
  - `CollegeSeeder` updated;
  - the report column header reads **COL**.
  Test on the clinic data.
- [ ] 4.3 **Guest column always shown** in the Accomplishment Report (label "Guest"), even when it is 0 ("Unspecified" still appears only when used); screen = PDF = Excel = CSV (`AccomplishmentReportBuilder`, `AccomplishmentReportTest` updated).
- [ ] 4.4 **Yearly Accomplishment Report:**
  - a "Year" choice in the report;
  - rows = the same illnesses by body system and services; columns = **Q1, Q2, Q3, Q4, Year total**;
  - counted by consultation date with the same counting rules and filters;
  - one builder for screen, PDF (8.5×13), Excel and CSV, with Prepared by / Noted by.
  The college-by-college matrix stays on the existing (monthly / date-range) version.
- [ ] 4.5 **Yearly Medicine Inventory report** (Report Generation › Inventory › Yearly). Pick a year; one row per medicine (name, generic, strength, unit) with:
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

### Phase 5 — Every module, two passes each

**Pass A (code)** for each module:
1. every route and Livewire action is authorised (role, permission, the record itself);
2. saves, edits, deletes and restores are all-or-nothing (real transactions now), stock moves only through `MedicineInventoryService`, soft deletes, cascades and the audit trail;
3. validation matches the database columns (strict mode);
4. list search / sort / filter / paging, and screen = export;
5. PDFs / exports with special characters, long text and missing data;
6. N+1 queries and stale caches;
7. nothing needs the internet to load: no CDN or third-party script / style / font / image link; every library is local.

**Pass B (live)**: the module used in the browser on the clinic data with each role that has it (admin, doctor, Staff (Nurse),
and a Staff (Nurse) account with permissions removed): console errors, dead links, empty states, wrong totals.

Every defect gets a failing test, then the fix, then both passes again.

| # | Module / area | Pass A | Pass B | Re-check (Phase 9) | Findings |
|---|---|---|---|---|---|
| 5.1 | Dashboard (admin / staff / doctor) | [ ] | [ ] | [ ] | |
| 5.2 | Patients (list, create, edit, history, archive / restore, reset password, email verification) | [ ] | [ ] | [ ] | |
| 5.3 | Queue (add, call next, complete, cancel, record form, doctor view, attachment sync) | [ ] | [ ] | [ ] | |
| 5.4 | Consultations (create / edit by nurse and doctor, Plan / Nursing medicines, images, PDF, delete / restore, illness & services) | [ ] | [ ] | [ ] | |
| 5.5 | Prescriptions (create / edit, verbal order, strengths, activate / deactivate, PDF, dispense) | [ ] | [ ] | [ ] | |
| 5.6 | Inventory (medicines, categories, generics, stock-in, batches, expiry, low-stock, reconcile) | [ ] | [ ] | [ ] | |
| 5.7 | Dispensing (verify prescriptions, dispense history, stock-out, manual dispense record, PDF) | [ ] | [ ] | [ ] | |
| 5.8 | Lab Requests (state machine, items, PDF, soft delete / restore, numbers) | [ ] | [ ] | [ ] | |
| 5.9 | Certificates (medical certificate, excuse slip, PDF, edit) | [ ] | [ ] | [ ] | |
| 5.10 | Report Generation (Patient Visits filters, Accomplishment Report monthly + yearly, Medicine Inventory current + yearly, dispensing, global search, activity log, all downloads) | [ ] | [ ] | [ ] | |
| 5.11 | Notifications & Alerts (low stock, expiry, system notifications) | [ ] | [ ] | [ ] | |
| 5.12 | Settings › Staff (no designation / station / shift) | [ ] | [ ] | [ ] | |
| 5.13 | Settings › Doctors | [ ] | [ ] | [ ] | |
| 5.14 | Settings › Manage User roles | [ ] | [ ] | [ ] | |
| 5.15 | Settings › Specializations | [ ] | [ ] | [ ] | |
| 5.16 | Settings › System settings (General incl. clinic name, Report lists, Deleted records) | [ ] | [ ] | [ ] | |
| 5.17 | Settings › Front CMS, Backup data, Locations (countries / states / cities / barangays) | [ ] | [ ] | [ ] | |
| 5.18 | Auth, profile, default-password gate, impersonation, failed sign-ins | [ ] | [ ] | [ ] | |
| 5.19 | `app/helpers.php` (every function: used? duplicated? tested?) | [ ] | — | [ ] | |
| 5.20 | `routes/*` (middleware stack per group, dead routes, duplicates) | [ ] | — | [ ] | |
| 5.21 | `config/*`, `.env` keys actually read, `server.php` (`BuiltInServerRouterTest`), backup command on MySQL 9.7 | [ ] | — | [ ] | |

- [ ] 5.22 The seven pages whose JavaScript never loads (`@section('scripts')`): Add-to-queue first, then medicines create / edit,
  lab request edit, generics create / edit / index, one page at a time with a browser check.

### Phase 6 — Deep DRY refactor + dead-code removal (behaviour unchanged)
Each step is guarded by: full suite + `route:list --json` diff (identical unless a change is listed) + link crawl + matrix.
- [ ] 6.1 **Routes:** one shared definition per module with per-panel differences; `web.php`, `staff.php` and `doctor.php` use it. Every URL and route name stays identical.
- [ ] 6.2 **Role routing:** the per-controller `getIndexRoute()` / `resolve*Route()` copies and the ~130 view ternaries `isRole(..) ? route(a) : ..` → `getRouteByRole()` / `redirectByRole()`; overlapping helpers merged; default password `123456` in one constant.
- [ ] 6.3 **⋮ action menu:**
  - one component `<x-row-actions>` (dropdown not clipped by the table, keyboard-usable, delete last);
  - an item is drawn only when `canUseModule()` / the record rules allow it;
  - existing JavaScript hooks are kept;
  - it replaces all 23 action views, the queue rows and the patient-history tables;
  - every action is clicked once in the browser.
- [ ] 6.4 **One medicine picker** script shared by consultation create / edit (and dispense record / prescription forms where it fits).
- [ ] 6.5 Split `DocumentIssuanceController` (2,596 lines) into services (consultation medicines, walk-in patient matching, images, routing); characterization tests first.
- [ ] 6.6 **Dead / broken code sweep** (each removal backed by a search showing nothing uses it):
  - unreferenced classes, views, routes, JavaScript files and helpers;
  - `fullcalendar`, `izitoast`, the commented-out seeders;
  - `FixDashboardPermissions`, `PerformanceMonitor`, `WarmUpCache` and the `routes/upgrade.php` `lang-js` route (check each);
  - `paytm` and currency / amount-to-word leftovers;
  - anything left over from the designation / station removal;
  - **Livewire 2 JavaScript that never runs on Livewire 3**: `livewire:load` + `window.livewire.hook('message.processed')` in `resources/assets/js/custom/custom.js` (Select2 is not re-initialised after Livewire updates) and `doctors/detail.js`; `beforePushState` / `beforeReplaceState` in `livewire-turbolinks.js`. Port to `livewire:init` + `Livewire.hook('commit', …)` or remove, with a browser check;
  - regenerate `_ide_helper*`.

### Phase 7 — Real-time updates with Laravel Reverb (replaces every timer)
- [ ] 7.1 **Spike** (stop and ask if it fails):
  - raise composer's PHP pin to 8.2 and `composer require laravel/reverb`;
  - `npm i laravel-echo pusher-js` (bundled by Mix, no CDN);
  - confirm Reverb runs on Windows next to `artisan serve` and a second LAN PC connects.
- [ ] 7.2 **Server:**
  - private channels per module (only users who can use the module may listen) + one per user;
  - one `ModuleChanged` event carrying the module and record ids only (**no patient data**), sent after the save commits;
  - a stopped Reverb **never** makes a save fail.
- [ ] 7.3 **Browser:**
  - one Echo setup (connects to the same host the page came from, no fixed IP);
  - tables refresh themselves on their module's events; the queue pages reuse their partial refresh;
  - "Live updates off" badge + 60 s fallback only while disconnected;
  - all `setInterval` / `setTimeout` reloads and `wire:poll` removed.
- [ ] 7.4 Wire every module: Queue, Consultations (+ "needs assessment" badge), Prescriptions / verify, Inventory (+ badges), Dispensing (history / stock-out), Lab, Certificates, Reports (incl. yearly reports), Notifications, Dashboards, Patients, Settings lists.
- [ ] 7.5 Startup script starts Reverb; firewall rule for its port written down for the owner.
- [ ] 7.6 Tests:
  - an event per save;
  - channel access denied without the module;
  - a save still succeeds with Reverb down.
  Live check:
  - the nurse adds to the queue → the doctor's screen updates without a reload;
  - stop Reverb → badge + fallback;
  - start it → live again.

### Phase 8 — Improvements (each with tests + a live check)
- [ ] 8.1 **Searchable "Add medicines"** (consultation create / edit, nurse and doctor):
  - search by brand, generic name, strength, category and SKU;
  - each option shows stock in hand and nearest expiry;
  - out-of-stock / expired options are disabled.
- [ ] 8.2 **Merge duplicate medicines / stock** (admin; sample: #41 / #42, #50 / #55):
  - preview, then move batches, ledger, consultation / prescription / dispense / stock-in lines to the kept medicine;
  - recompute totals; archive the duplicate; audit snapshot;
  - the yearly inventory report (4.5) shows the merged history under the kept medicine;
  - the same tool merges duplicate categories / generics.
- [ ] 8.3 **Merge duplicate patients** (admin):
  - candidates by name + birth date + sex, university ID, phone;
  - preview, then move consultations, prescriptions, queue, lab requests, dispense records, addresses and images;
  - archive the merged-away patient with "merged into #…"; audit snapshot.
- [ ] 8.4 **Stock disposal & count:**
  - dispose expired / damaged stock per batch with a reason;
  - record a physical count per batch with a preview of the differences;
  - both through the inventory service only (ledger types `disposal` / `adjustment`), so the yearly report (4.5) shows them.
- [ ] 8.5 **Notification center:**
  - bell with per-user read / unread alerts (low stock, expiring / expired stock, new queue entry for doctors, consultation needing assessment), using the existing `notifications` table;
  - pushed live; "mark all read".
- [ ] 8.6 **Queue display board:**
  - waiting-area screen on a secret link: now serving / next numbers and rooms, no names;
  - chime on call; live via Reverb.

### Phase 9 — Final verification, again
- [ ] 9.1 Full suite · link crawl (incl. users with permissions removed) · route × role matrix · `route:list` diff reviewed.
- [ ] 9.2 Fresh rehearsal from the dump on the final code (both engines) · `db:integrity` exit 0.
- [ ] 9.3 Second full live walk-through of every module (the "Re-check" column in Phase 5) with two browser sessions for real-time.

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
| Duplicate patient merge | 🆕 Phase 8.3 |
| Pass A / Pass B re-check | 🆕 Phase 5.2 |

### 4.3 Queue
| Item | Status |
|---|---|
| Call-next race, stale entries, archived patients, attachment sync, New / Previous form labels, Record form button | ✅ |
| Doctor queue routes not permission-gated | 🆕 Phase 3.5 |
| Add-to-queue page JavaScript never loads (patient search + latest-consultation preview) | ⏳ → Phase 5.22 |
| Timer polling → WebSocket | 🆕 Phase 7 |
| Queue display board | 🆕 Phase 8.6 |

### 4.4 Consultations
| Item | Status |
|---|---|
| Stock returned to the right batch, duplicate lines, soft delete + restore, audit snapshot, illness / services | ✅ |
| Certificate / excuse-slip edit crash (10-08) | ✅ |
| Searchable "Add medicines" (name / generic / strength / category / SKU) | 🆕 Phase 8.1 (shared picker in 6.4) |
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
| Duplicate medicine / stock merge | 🆕 Phase 8.2 |
| Stock disposal & physical count | 🆕 Phase 8.4 |

### 4.7 Dispensing
| Item | Status |
|---|---|
| Stock-out counted pending prescriptions / patient "N/A" (R3-M3) | ✅ |
| **Dispense History misses consultation medicines** | ✅ Phase 2 |

### 4.8 Lab Requests
| Item | Status |
|---|---|
| State machine, delete rules, soft delete + Deleted records, PDF `/` crash, unique numbers | ✅ |
| Lab request edit page JavaScript never loads | ⏳ → Phase 5.22 |

### 4.9 Certificates
| Item | Status |
|---|---|
| `?module=certificates` plural, edit crash, certificates-only staff search | ✅ |
| "NORSU Clinic" on certificate / excuse-slip PDFs and prints → "Medical University Clinic" | 🆕 Phase 4.1 |
| Pass A / Pass B re-check | 🆕 Phase 5.9 |

### 4.10 Report Generation / Notifications & Alerts
| Item | Status |
|---|---|
| Patient Visits filters, Accomplishment Report (screen = PDF = Excel = CSV), Classify path | ✅ |
| College of Law code CL → COL | 🆕 Phase 4.2 |
| Guest column always shown | 🆕 Phase 4.3 |
| Yearly Accomplishment Report (Q1–Q4 + Year total) | 🆕 Phase 4.4 |
| Yearly Medicine Inventory report (left from last year, additional, Q1–Q4 remaining) | 🆕 Phase 4.5 |
| "Prepared by" shows the designation → role | 🆕 Phase 3.2 |
| `wire:poll.30s` → WebSocket | 🆕 Phase 7 |
| Notification center (per-user, read / unread, live) | 🆕 Phase 8.5 |
| Saved report filters / one-click monthly report | 💡 §6 |

### 4.11 Settings and access
| Item | Status |
|---|---|
| Roles name lock (P2-H1), seeder no longer resets Roles (R3-M7), admin 403 cache poisoning, `staff.module:a,b` bug | ✅ |
| **Manage User roles changes not applying** (§1.5) | 🆕 Phase 3 |
| Nurse role with no accounts (May finding B) | 🆕 Phase 3.1 (merged into Staff (Nurse)) |
| Role Designation, Assigned Station, Shift Schedule | 🆕 removed in Phase 3.2 / 3.3 |
| Unreachable staff route groups (May finding C) | ✅ already gone (checked 10-08) |
| Notifications submenu linking to Inventory (May finding D) | ✅ gated; replaced by `canUseModule` in 3.4 |
| `reports` key in the station map (May finding E); pharmacist on the triage station (May finding A) | ℹ️ moot — designations and stations removed |
| `MedicineAvailabilityController` dead (staff audit #9) | ✅ removed 10-02 |
| Project name in Settings › General (`clinic_name`) | 🆕 Phase 4.1 |

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
| Role × permission effect test; crawler without designation × station actors | 🆕 Phase 3.7 |

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

- exact wording of the PDF / print headers after the name change, if a layout depends on the old text length;
- whether any medicine's unit in the clinic data is wrong (the yearly report shows each medicine's own unit, e.g.
  "tablet" vs "box");
- the Reverb port number if 8080 is already used on the clinic PC.

---

## 9. Clinic copy — what will change for deployment (when the phases are done)

1. Back up the clinic database (`php artisan db:backup`).
2. Pull `changes_v2`; `composer install --no-dev` (PHP 8.2 needed for Reverb).
3. `php artisan migrate`. It:
   - converts the MyISAM tables to InnoDB, clears zero dates and restores the foreign keys (skipped ones are listed);
   - renames the clinic to "Medical University Clinic" and College of Law to COL;
   - merges the nurse role into Staff (Nurse).
   It also runs every earlier step in STATUS §7.
   Then, once: `php artisan inventory:recover-orphan-batches` (dry run) and `--apply` (the clinic data has 4 stock batches of
   deleted medicines; this re-creates the medicines as placeholders, writes their stock off through the ledger and adds the
   last foreign key; see [commands.md](commands.md)).
4. `php artisan db:integrity` (add `--compare-fresh` once): must finish without errors. The other output is warnings
   (duplicate medicines, 8 phone numbers: `php artisan phone:normalize`, then `--apply` after a backup).
5. `npm ci && npm run prod`.
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
