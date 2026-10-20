# Medical University Clinic — Project status

**Last updated:** 2026-10-20 — Phase 6.1–6.4 shared routes, role routing, default-password constant, row-action menus and consultation medicine picker completed. Phases 0–5 and 7 are verified; Phase 6.5 is next. Exact commits are in [Handover.md](../Handover.md).

This is the single place to see what has been done, what is in progress, what is still open, and what needs a
decision. The detail of each finding lives in the audit documents listed in [§9](#9-where-the-detail-is).

Legend: ✅ done and verified · 🟡 partly done / unfinished · ⏳ pending (known, not started) · 🔒 needs your decision ·
ℹ️ accepted / by design

---

## 1. At a glance

| | |
|---|---|
| Committed on `changes_v2` (ahead of `develop`) | Current checkpoint and earlier completed phases; exact work and handover commits are in [Handover.md](../Handover.md). |
| **Checkpoint pushed** | Phases 0–5 and 7, plus Phase 6.1–6.4 on `changes_v2`: shared module routes, role routing, row-action menus and consultation medicine picker; one default-password constant; complete 461-route manifest and 6,288-cell access matrix preserved. See [Handover.md](../Handover.md) for commits and verification. |
| **2026-10-08 fixes pushed** | `d427976`: "Pending" cleanup (R3-L5 / L6 / L7 / M9, +23 tests); `d8dd314`: landing-page / link-audit fixes (+12 tests + the opt-in crawler). Both are on `origin/changes_v2`; §8 retains an older snapshot to reconcile in plan Phase 10.2 |
| Test suite (`LINK_CRAWL=1 php vendor/bin/phpunit`, dedicated `norsu_clinic_test`) | **730 tests / 8,004 assertions, all passing**, 2026-10-20 (12:24.721); includes the real built-in server router, permission/access regressions, 394-panel-route contract, shared role/password helpers, row-menu rules and picker configuration. The standalone picker browser regression also passes. |
| **Link audit** (`LINK_CRAWL=1`, permissions removed and lazy tables loaded) | **1,610 requests across 15 actor variants; 0 broken links, crashes, lazy-table failures or capped crawls**, 2026-10-20. |
| Route × role audit | **Phase 6.1–6.4 before/after oracle matrix** (2026-10-20): 393 admin / staff / doctor / API routes × 16 account/permission variants = **6,288 requests each**, identical responses and permission sets; **0 server errors or access violations**. Covers revoked permissions, panel separation and guest/patient/inactive accounts; backup file-changing actions excluded. Earlier Phase 3 and Phase 0 results remain in the audit plan. |
| Real database `norsu_clinic` | since 2026-10-09 it holds the **clinic dump, upgraded**: 128 migrations (last `2026_10_09_170000_unify_staff_nurse_access`), all 64 tables InnoDB, **47 of 47 foreign keys** (the 4 stock batches of deleted medicines #1 / #2 / #28 were recovered, see §6), 12 real users + 4 QA accounts (`qa.admin`, `qa.doctor`, `qa.nurse` = a plain Staff (Nurse) account, `qa.legacy` = one with the hidden designation / station profile) (`@qa.test`, passwords only in the git-ignored `storage/app/qa-accounts.json`). `php artisan db:integrity --compare-fresh` after Phase 3: **0 errors**, structure identical to a fresh install, the 3 existing warnings (duplicate medicines #41/#42 and #50/#55, 8 phone numbers to normalise); rehearsed from the dump under both engines (Phase 3 changes only the content of `roles` and `permissions`; the one other content change in the whole 20-migration upgrade is the Phase 1 zero-date fix). **Phase 4's two data migrations (clinic name + default slider text, College of Law COL) were rehearsed on a scratch copy of the dump but are NOT applied to this database**: you asked to leave it alone (2026-10-15), so it still says "Norsu Clinic" and "College of Law (CL)" until `php artisan migrate` is run |
| Last backup taken before a schema change | `norsuclinic_backup-2026-10-02_08-20-32.sql` (1.6 MB) |
| Open findings | Phase 5 defects fixed and verified. The remaining owner decisions are listed in §6; deep refactoring/dead code are Phase 6, improvements Phase 8 and final re-checks Phase 9. |
| Next plan item | **Phase 6.5 — split DocumentIssuanceController**, then 6.6. All Phase 5 module passes and 5.22/5.23 are complete; Phase 9 re-checks remain. Phases 0–4 and 7 were completed earlier. |

---

## 2. Timeline (what happened)

| Date | Work | Result |
|---|---|---|
| 2026-04 → 05 | Earlier audits: general (04-29), staff (05-05), module CRUD (05-05), automation (05-18) | findings folded into later passes |
| 2026-09-30 | Full re-audit pass 1 + pass 2 (LAN/HTTP-only) and remediation phases B0–H | ✅ 14 commits; 106 tests; all 6 Criticals fixed ([remediation-status-2026-09-30.md](remediation-status-2026-09-30.md)) |
| 2026-10-01 | Third-pass re-audit `R3-*` ([full-reaudit-2026-10-01.md](full-reaudit-2026-10-01.md)): 6 High, 11 Medium, 19 Low | report written |
| 2026-10-01 | Staff/nurse + doctor workflow pass (Patients, Queue, Consultations, Prescriptions, Lab requests, Stock-out) — owner decisions taken | ✅ all 6 Highs + R3-M1/M2/M3/M8/M10 + L2/L4/L18; 183 tests |
| 2026-10-01 | Name search fixed in every module; Export CSV carries names; **ACCOMPLISHMENT REPORT** (filters, illness/service picks on consultations, PDF/Excel/CSV) | ✅ [accomplishment-report-2026-10.md](accomplishment-report-2026-10.md); 234 tests |
| 2026-10-02 | Admin got **403 on `/admin/dashboard`** → root cause found (shared file cache overwritten by test-schema commands) and prevented | ✅ |
| 2026-10-02 | Route/access/data audit across every role + remaining re-audit items (M4, M5, M6, M7, M11, Lows) | ✅ [route-access-audit-2026-10-02.md](route-access-audit-2026-10-02.md); 274 tests |
| 2026-10-02 | `docs/STATUS.md` created; work committed and pushed as a checkpoint | ✅ `53935c5`, `6a35d83`, `1d475e2` on `origin/changes_v2` |
| 2026-10-02 | **"Unfinished" pass** (§4): lab requests soft-deleted + Settings › Deleted records (view / restore), failed sign-ins in the trail, patient-type lookups by code, archived-account message on patient create / edit, unique numbers that survive a race, report → "Classify" path for old consultations | ✅ 292 tests; matrix 3,990 requests clean |
| 2026-10-02 | **Queue ↔ consultation check** (nurse adds a patient to the queue, the new consultation must reach the doctor's screen): checked in a real browser with a nurse and a doctor signed in at the same time, and fixed what was found | ✅ 302 tests |
| 2026-10-02 | Committed and pushed the "Unfinished" pass and the Queue ↔ consultation fixes as the second checkpoint | ✅ `13a78d4` + docs on `origin/changes_v2` |
| 2026-10-02 (evening) | **"Pending" cleanup** (interrupted by a usage limit; found uncommitted on 10-08, committed the same day): R3-L7 dead code removed and the real `storePatient()` crash fixed, R3-L6 two strengths of one medicine + dispensed-race lock, R3-L5 medicine-form stock goes through the Stock-In register, R3-M9 `server.php` router repeats the `.htaccess` protections for `php artisan serve`, 404 page image, duplicate `countries` / `states` POST routes | ✅ code + tests done, committed 2026-10-08 (see §8) |
| 2026-10-08 | **Landing page "Dashboard" 403**: hero button hard-wired to `/admin/patients`, header button fell back to the admin dashboard. One helper (`getDashboardRouteName()`) now decides, and a new **link audit** (opt-in crawler, signs in as 16 kinds of user, loads lazy tables) found 13 more role-blind links / a certificate-edit crash | ✅ [link-audit-2026-10-08.md](link-audit-2026-10-08.md); fixes + tests, committed 2026-10-08 |
| 2026-10-09 | **Audit plan, Phase 0** (safety nets and baselines; [audit-plan-2026-10-08.md](audit-plan-2026-10-08.md)): the test run creates its own throwaway schema (+ a test for it and for the guard); `norsu_clinic` backed up; rehearsal runner for the clinic dump; **rehearsal R0 confirmed the clinic-upgrade problem** (an InnoDB default fails with error 1824 at `2026_10_02_090000` and leaves a half-built schema; a MyISAM default succeeds but leaves 0 foreign keys); baseline suite, route list, link crawl and route × role matrix | ✅ done, committed and pushed as `7c6b5c5` |
| 2026-10-09 | **Audit plan, Phase 1 — clinic-data upgrade** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md)): new tables are always InnoDB (`DB_ENGINE`); migrations convert the clinic's 59 MyISAM tables to InnoDB, turn zero dates into NULL and add the missing foreign keys (never changing a row; a key blocked by orphan rows is skipped and explained); the illness-table migration can finish a half-built database; new commands `db:integrity [--compare-fresh]` and `db:restore-foreign-keys`. Rehearsed on the real dump under both engines: the upgraded schema equals a fresh install. Follow-up on the owner's decision: `php artisan inventory:recover-orphan-batches --apply` re-creates the 3 deleted medicines as "Unknown medicine #N" and writes their 550 units off through the ledger, which lets the last foreign key in (47 of 47, `db:integrity` 0 errors) | ✅ done, tested, committed (`d00cca3` + follow-up) and pushed |
| 2026-10-09 | **Audit plan, Phase 2 — Dispense History shows consultation medicines** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md)): read-only `dispense_history_view`, Source column / filter, word search and sorting, View-only consultation / prescription rows, and a consultation medicine detail page (3 routes). Codex completed Claude Code's open work with owner approval and fixed non-consultation rows, archived patient links and the clinic head's consultation link. Live clinic totals 3 / 6 / 7 = 9 / 14 / 15 units; stock unchanged. Chrome checks pass for admin / doctor / clinic head; QA nurse is correctly blocked by the existing designation policy (Phase 3 removes it) | ✅ 15 regression tests / 112 assertions; five mutation checks; browser and integrity checks passed; full-suite result in §1 |
| 2026-10-09/10 | **Audit plan, Phase 3 — Manage User roles apply; Staff = Nurse** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md)): changing a role in Settings › Manage User roles now changes menus, dashboards, buttons and routes on the next page, for every permission. One helper `canUseModule()` and one map (`App\Support\ModuleAccess`) replace the 63 designation / station checks; every staff and doctor route carries the permission of its module (only the dashboards and Reports / Notifications are open on purpose); the `staff.module` middleware and the designation / station maps are gone. Staff = Nurse: one role "Staff (Nurse)" (the `nurse` role is retired, its accounts moved), and Role Designation, Assigned Station and Shift Schedule are removed from the staff form, list, profile and reports (the hidden columns stay). The Roles screen offers only the five permissions that apply, Clinic Admin and Patient are locked, a role can be saved with none, and direct per-user permissions were cleared. Review of the inherited work found and fixed: a migration that broke a fresh database, a dropped country code, a doctor-menu crash, blank permission labels at the clinic and designation-era tests that no longer proved the "hidden" side | ✅ done, tested, committed and pushed |
| 2026-10-11 | **Phase 2 re-checked** at the owner's request ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) Phase 2): the Dispense History view was compared row by row with the base tables on the clinic data and on a scratch data set with all three sources, and used live with admin / doctor / Staff (Nurse) sessions and role-permission variants. Two real defects were fixed test-first: **rows repeated and went missing between pages** when many rows shared a date (every manual dispense record of a day is saved at 00:00:00; 60 same-day records listed only 48 different rows), and the **patient's history page** showed pending and cancelled prescriptions as dispensed and lacked the consultation medicines (it now lists what the tab lists). Also a test that never tested "newest first", a missing accessible name on the source filter and stale comments. 13 mutations each fail a test. Measured, not changed: the history costs about 35 ms per 1,000 rows for every click (plan §8) | ✅ done, tested, committed and pushed |
| 2026-10-12 → 15 | **Audit plan, Phase 4 — the owner's requested changes** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md)): the clinic is called **Medical University Clinic** (a migration changes the `clinic_name` setting and the default slider text only while they still hold the old default; seeders, e-mail, language file, landing page and `.env.example` follow; the link crawl now fails on any page that still says "NORSU Clinic"); **College of Law CL → COL** (a migration of the college and of every saved copy of its name); the Accomplishment Report always has a **Guest** column; new **yearly Accomplishment Report** (Q1-Q4 + year total, same rows / rules / filters) and **yearly Medicine Inventory report** (per medicine: left from last year, additional, remaining at the end of each quarter; built from the stock ledger with an add-up check), both as screen = PDF = Excel = CSV. Three new routes, no new Artisan command. The printed form codes "NORSU-CLINIC-FORM-0x" stay as they are (your decision, 2026-10-15); the working database `norsu_clinic` was left alone, so the two data migrations were rehearsed on a scratch copy of the dump | ✅ done, tested, committed and pushed |
| 2026-10-15 | **Audit plan, Phase 5 — checkpoint A, cross-cutting code sweeps** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) "Phase 5 — progress"): every list now has a unique last sort key (no row repeats or vanishes between pages), every form rule fits its database column (no more database error page on long text), role / settings / Front CMS saves are transactions, PDFs survive awkward and missing data, the pick-lists, expiry badge and PDF-header settings no longer go stale, and the doctor, staff and medicine lists no longer query once per row; 33 new tests, 30 mutations (29 fail a test, 1 equivalent). New migration `2026_10_15_100000_widen_medicine_bill_note` (**not applied to `norsu_clinic`**, like the two Phase 4 data migrations). The owner's "Others" request (services get a text box; typed illness / service names listed under "Others" in the Accomplishment Report) is plan item 5.23, not started | ✅ 540 tests / 4,857 assertions; link crawl 1,613 requests, 0 problems |
| 2026-10-16 | **Audit plan, item 5.23 — "Others" records what was done / found** (owner request 2026-10-15): a services "Others" line gets a name box (new column `consultation_services.other_text`, migration `2026_10_15_110000_add_other_text_to_consultation_services`, **not applied to `norsu_clinic`**), a ticked "Others" line (illness or service) must carry a name, names are saved tidied, and the Accomplishment Report (monthly and yearly; screen, PDF, Excel, CSV) lists them as sub-rows under "Others" with their own counts, merging spellings that differ only by case, spaces or end punctuation; section and grand totals unchanged. Written by Claude Code, re-verified by OpenAI Codex and again by Claude Code after the owner handed it back (mutations 32 of 34 fail a test, 2 equivalent; rehearsal on the dump; live checks with nurse, doctor and admin) | ✅ 556 tests / 5,985 assertions; link crawl 1,614 requests, 0 problems |
| 2026-10-16 | **Audit plan, row 5.1 — Dashboard (Pass A and Pass B)** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) "Phase 5 — progress 2026-10-16"): the figures and today's list are read fresh and count patient records like the Patients module (they were cached 60 s and counted accounts), Month is this month of this year (it was any year), the week includes its last minutes, the tabs show the 50 newest and the real total (they cut at 5), the **staff** dashboard's Day / Week / Month tabs work (they called the admin-only route and always answered 403) with links decided by the server, times are the clinic's clock (they showed UTC, 8 hours early), and a patient whose account was archived on its own no longer breaks the page; new staff route `staff.patientData.dashboard` (route list 479); 22 mutations all fail a test | ✅ 571 tests / 6,066 assertions; link crawl 1,621 requests, 0 problems; live check with nurse, doctor and admin on a scratch copy of the clinic data |
| 2026-10-16 | **Audit plan, row 5.2 — Patients (Pass A and Pass B)** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) "Phase 5 — progress 2026-10-16: 5.2"): the list's date range includes its last day (it stopped at midnight, so "Today" showed nobody) and ignores junk, the welcome e-mail is sent after the registration is committed and only when there is an address, "resend verification" works only for patient and doctor accounts, the history page's compare table loads medicines and nurses once (it ran a query per consultation), the forms validate the date of birth, gender and all pick-list ids (they reached the database unchecked), and a refused or failed save no longer writes the patient's data into the log; no route added; 18 mutations all fail a test | ✅ 585 tests / 6,205 assertions; link crawl 1,621 requests, 0 problems; live check with nurse, doctor and admin on a scratch copy of the clinic data |
| 2026-10-16 | **Audit plan, row 5.3 — Queue (Pass A and Pass B)** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) "Phase 5 — progress 2026-10-16: 5.3"): the sidebar badge was cached for 30 s for everybody and the badge, the admin dashboard and the doctor dashboard counted the queue differently from the queue screens (entries without a live patient): one scope `PatientQueue::openToday()` and a fresh count now; the doctor's current-patient card no longer crashes for an in-progress entry without a call time; no route added; 8 mutations all fail a test | ✅ 588 tests / 6,243 assertions; link crawl 1,621 requests, 0 problems; live check with nurse, doctor and admin on a scratch copy of the clinic data |
| 2026-10-16 → 18 | **Audit plan, row 5.4 — Consultations (Pass A and Pass B)** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) "Phase 5 — progress 2026-10-16: 5.4"): the patient's History page (a Patients page) drew and read every clinical part for anyone with Manage Patients; each part (consultations and compare table, certificates, excuse slips, prescriptions, dispense history) now follows its own module's permission, in the page and in the queries; the visit date, consult mode, campus, college, course, year level, department and office are checked on create and on edit; the sidebar "incomplete consultations" warning is one fresh definition (consultations only, a blank made of tabs or new lines counts as blank, computed only for roles that can open consultations); the doctor's edit form no longer demands a pregnancy status, nursing intervention and vaccination that the nurse's form never asks for, so the Assessment and Plan of a nurse's record can be saved; on your decision the "previous visit" lookup no longer copies the last visit's vital signs into today's form (history fields and height / weight only); no route added; 29 mutations, all fail a test except 2 equivalent | ✅ 596 tests / 6,368 assertions; link crawl 1,622 requests, 0 problems; live check with nurse, doctor and admin on a scratch copy of the clinic data |
| 2026-10-18 | **Audit plan, row 5.5 — Prescriptions (Pass A and Pass B)** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) "Phase 5 — progress 2026-10-18: 5.5"): the endpoint that creates a catalog medicine with opening stock now needs the Inventory permission (it needed only the documents permission), the list draws Delete / Edit only where they work (staff never delete; a dispensed prescription is closed), the visit date must be a real date from 1900 to a year ahead, the View page no longer crashes when the clinic logo setting is missing, a missing prescription's PDF is a 404, and all-or-nothing saves and the page queries are pinned by tests; no route added; 12 mutations all fail a test | ✅ 605 tests / 6,431 assertions; link crawl 1,622 requests, 0 problems; live check with nurse, doctor and admin on a scratch copy of the clinic data |
| 2026-10-18 | **Audit plan, row 5.6 — Inventory (Pass A and Pass B)** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) "Phase 5 — progress 2026-10-18: 5.6"): the medicine form created its generic and category before its transaction, so a refused form left them behind (now all or nothing); the stock-in and medicine forms refuse a manufacturing date in the future and any year before 1990 or after 2100; the medicine pick-list (consultation form, dispensing) ran two queries per medicine and the Stock-In list one per row (now a fixed number); the Stock-In search finds a stock-in by the medicine inside it; every way of changing the inventory is pinned to the medicines permission; no route added; MUTANTS mutations all fail a test | ✅ 617 tests / 6,530 assertions; link crawl 1,622 requests, 0 problems; live check with nurse, doctor and admin on a scratch copy of the clinic data |
| 2026-10-18 | **Audit plan, row 5.7 — Dispensing (Pass A and Pass B) and the owner decisions on row 5.6** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) "Phase 5 — progress 2026-10-18: 5.6 follow-up and 5.7"): on your answers, the **Unit Cost** box is removed (nothing read it) and **doctors are read-only in the inventory** (their write routes are gone; no new / edit / delete forms or buttons); in Dispensing, the quick "new patient" pop-up of the dispense form no longer lets a pharmacist without the Patients permission, or a doctor, register patients, the Verify Prescription list has a search and no longer hands every prescription to a doctor account without a doctor profile, the manual dispense record's date must be a real date, and the category medicine list of the dispense form no longer queries per medicine; 22 mutations all fail a test | ✅ 626 tests / 6,619 assertions; link crawl 1,604 requests, 0 problems; live check with nurse, doctor and admin on a scratch copy of the clinic data |
| 2026-10-18 | **Audit plan, row 5.8 — Lab requests (Pass A and Pass B)** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) "Phase 5 — progress 2026-10-18: 5.8"): the list's header sorts did nothing (the list was pre-ordered by date), the search could not find a test name, the request date accepted any readable date, the results typed on the edit page were saved unchecked (a wrong status ended in a database error), a status button pressed on a stale page could reopen a finished request, and the new-request form copied any account (doctor, staff, admin) into the form; all fixed; no route added; 11 mutations all fail a test | ✅ 632 tests / 6,667 assertions; link crawl 1,604 requests, 0 problems; live check with nurse, doctor and admin on a scratch copy of the clinic data |
| 2026-10-18 | **Audit plan, row 5.9 — Certificates (Pass A and Pass B)** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) "Phase 5 — progress 2026-10-18: 5.9"): the medical certificate and excuse slip name their boxes differently from the consultation form, so none of the row 5.4 limits applied to them: a long pulse, temperature, pressure part or teacher failed the whole save with a general error, the list of subjects could be any text and the patient could be a number that points at no account; all are checked now (create and edit), and a blank blood pressure is saved as nothing instead of "/"; print page and PDF with markup pinned; no route added; 10 mutations all fail a test | ✅ 636 tests / 6,717 assertions; link crawl 1,604 requests, 0 problems; live check with nurse, doctor and admin on a scratch copy of the clinic data |
| 2026-10-18 | **Audit plan, row 5.10 — Report Generation (Pass A and Pass B)** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) "Phase 5 — progress 2026-10-18: 5.10"): "low stock" had three definitions (the sidebar badge, the Report inventory tab with its CSV, and the colour in the medicine list) so the numbers disagreed; there is now one (`Medicine::scopeLowStock()` / `isLowStock()`) used by all four, pinned on a grid of cases; the inventory report no longer asks the database once per medicine; date ranges, downloads and per-row queries of the other tabs are pinned (they were already right); no route added; 12 mutations all fail a test | ✅ 642 tests / 6,757 assertions; link crawl 1,604 requests, 0 problems; live check with nurse and admin on a scratch copy of the clinic data |
| 2026-10-19 | **Audit plan, row 5.11 — Notifications & Alerts (Pass A and Pass B)** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) "Phase 5 — progress 2026-10-19: 5.11"): the four alerts of the sidebar's Inventory entry (expired, expiring in 7 and 30 days, low stock) were kept for five minutes for everybody and never cleared; they are counted when the page is drawn, only for a role that can open the inventory, by one class (`InventoryAlerts`) whose boundaries are pinned; the three entries of the Notifications & Alerts menu follow the permissions; no route added; 10 mutations all fail a test | ✅ 647 tests / 6,778 assertions; link crawl 1,604 requests, 0 problems; live check with a nurse (with and without the medicines permission) on a scratch copy of the clinic data |
| 2026-10-19 | **Audit plan, Phase 7 — real-time updates with Laravel Reverb** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) "Phase 7 — progress 2026-10-19"), done before the rest of Phase 5 at the owner's request, which also approved the new packages (`laravel/reverb`, PHP requirement raised to 8.2, `laravel-echo`, `pusher-js`, all bundled locally): every committed save is announced (module name and time only, no patient data) on a private channel that only users of that module may listen to, and the open screens redraw by themselves: the queue pages, every table, the dashboards, the report page and the sidebar badges; a stopped Reverb never fails a save (the header shows "Live updates off" and the page refreshes every 60 seconds until it is back); every `wire:poll` and timer reload is gone (a test fails if one returns); the startup scripts start Reverb. A defect found by the live check: the queue's clean-up sent an UPDATE on every page draw, which would have made the screens refresh each other for ever (now it asks first; a test opens every main page and fails if a read announces a write). New route `GET /live/menu` (route list +1); no new Artisan command of ours (`reverb:start` is the package's, documented in [commands.md](commands.md)); 18 mutations all fail a test | ✅ 658 tests / 6,885 assertions; link crawl 1,606 requests, 0 problems; live check with nurse, doctor and admin on a scratch copy of the clinic data (nurse adds to the queue → doctor's screen redraws without a reload; Reverb stopped → badge, normal saves, 60 s fallback; started → live again) |
| 2026-10-20 | **Audit plan 5.14–5.22 and doctor archive decision:** role-name collisions refused; form-scoped settings and blank optional fields fixed; CMS/location validation and province creation repaired; default-password JSON write bypass closed; profile logs and queue-preview clinical data restricted; duplicate redirect names fixed; seven page script sections repaired; helpers/config reviewed | ✅ 703 tests / 7,835 assertions; crawl 1,610 requests; 15 revert checks; real browser checks on approved scratch clinic data; scratch copy removed, working database unchanged |
| 2026-10-20 | **Audit plan, row 5.13 — Settings › Doctors (Pass A and Pass B)** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) "Phase 5 — progress 2026-10-20: 5.13"): the create form accepted a doctor with no e-mail and no password, gender / specializations / experience / address / blood type / qualifications were unchecked (a bad value came back as a "422" page with SQL text), and **the whole request reached the account** (a crafted request could set the language, university id ...): one shared rule set for create and edit, and only the doctor form's own columns are saved; the access guards of the side endpoints, archiving and its audit lines are pinned; no route added; 14 mutations all fail a test | ✅ 682 tests / 7,241 assertions; link crawl 1,610 requests, 0 problems; live check with admin, doctor and nurse sessions on a scratch copy of the clinic data (create through the form, the doctor signs in, status off → login page, archive from the list) |
| 2026-10-19 | **Audit plan, row 5.12 — Settings › Staff (Pass A and Pass B)** ([audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) "Phase 5 — progress 2026-10-19: 5.12"): gender was only "required" although the column is an integer (now `integer|in:1,2` on create and edit); **archiving a staff member erased who had made their records** (queue, consultations, lab requests, dispensing, ledger and trail printed "N/A", "System" or a dash for an archived author: the seven author relations now name archived accounts too); authorisation of all eight routes, field limits, duplicates in any case, all-or-nothing, the list's scope, HTML in names, the audit lines and sign-in of a new account are pinned; no route added; 9 mutations all fail a test | ✅ 671 tests / 7,057 assertions; link crawl 1,606 requests, 0 problems; live check with admin and nurse sessions on a scratch copy of the clinic data (create through the form, the new account signs in, archive from the list, its session ends, its queue entry still names it) |

---

## 3. Completed ✅

### 3.1 Third-pass audit (`R3-*`)

| ID | What it was | Fixed |
|---|---|---|
| R3-H1 | consultation edits/deletes returned medicines to the wrong batch (expired units came back as "2099") | 10-01 |
| R3-H2 | duplicate medicine rows in a consultation over-deducted stock | 10-01 |
| R3-H3 | deactivated prescriptions were still dispensed | 10-01 |
| R3-H4 | prescriptions could be written / re-assigned in another doctor's name | 10-01 |
| R3-H5 | expired stock kept counting as available; no expired-stock alert | 10-01 |
| R3-H6 | staff designation limits did not apply inside Livewire; Spatie middleware namespace wrong | 10-01 |
| R3-M1 | nurses got 403 on the consultation medicine list | 10-01 (completed 10-02, see 3.2 #1) |
| R3-M2 | staff/nurse profile saves silently discarded | 10-01 |
| R3-M3 | Stock-out tab counted pending prescriptions, patient always "N/A" | 10-01 |
| R3-M4 | logo / favicon pointed at `localhost` on other LAN PCs | 10-02 |
| R3-M5 | audit-trail gaps (deletes, prescriptions, accounts, roles, settings, backups, logins, impersonation, **failed and blocked sign-ins**) | 10-01 + 10-02 |
| R3-M6 | clinical records hard-deleted — consultations / certificates / excuse slips **and lab requests** are now soft-deleted (row, photos and tests kept); **Settings › Deleted records** lists them with who deleted them and restores them | 10-01 + 10-02 |
| R3-M7 | `db:seed` reset the permissions set in the Roles screen | 10-02 |
| R3-M8 | stale patient / doctor pick-lists | 10-01 |
| R3-M10 | excuse slips missing from patient history | 10-01 |
| R3-M11 | default password `123456` not enforced for doctors / staff | 10-02 |
| R3-L1 | doctor update mass-assigned the request; any qualification deletable | 10-02 |
| R3-L2 | profile e-mail rejected long TLDs | 10-01 |
| R3-L4 | patient audit rows had empty college / course / address | 10-01 |
| R3-L8 | lab-request PDF crashed on `/` in a patient name | 10-02 |
| R3-L9 | `npm run prod` deleted tracked `public/webfonts`, `public/messages.js` first | 10-02 |
| R3-L12 | failed validation flashed passwords into the session | 10-02 |
| R3-L13 | `getBadgeColor()` crashed with nobody signed in (P2-L3) | 10-02 |
| R3-L16 | unvalidated `?section=` on Settings | 10-02 |
| R3-L14 | hard-coded patient-type ids (lab-request patient search, guest exemption on patient create **and edit**) | 10-02 |
| R3-L18 | queue call-next race; stale queue entries never closed | 10-01 |
| R3-L3 | a role cannot be saved with no permissions | ℹ️ by design (the form requires at least one) |
| R3-L5 | stock typed on the medicine form bypassed the Stock-In register; it now creates a Stock-In entry (and can be reversed from there) | 10-02 |
| R3-L6 | prescription duplicate check was by medicine only (two strengths could not be prescribed) and `update()` did not lock against a dispense happening at the same moment | 10-02 |
| R3-L7 | dead / broken code: the real `storePatient()` crash (undefined `patient_id` key), `/api/medicines`, `MedicineAvailabilityController`, `StateController`, `Staff` model + factory, empty `SyncRolePermissions`, the duplicate `cache:warmup`, `User::staff()` | 10-02 |
| R3-M9 | **mitigated**: `server.php` (used by `php artisan serve`) repeats the `public/.htaccess` upload protections. The single-threaded limit of the built-in server remains (accepted; Apache is still the better deployment) | 10-02 |

### 3.2 Found and fixed outside the audit list

| # | Defect | Date |
|---|---|---|
| 1 | `staff.module:a,b` only checked the first module (certificates-only staff could not search patients; pharmacist could not load the medicine list; consultations-only nurse could not read the latest consultation) | 10-02 |
| 2 | **Admin 403 on the dashboard** — cached permission ids overwritten by commands run against the test schema; cache folder now separate per database | 10-02 |
| 3 | Settings › General could never be saved (validator required a `language` field the form no longer has) | 10-01 |
| 4 | Public `/medical-doctors` crashed when a doctor had no specialisation | 10-02 |
| 5 | `?module=certificates` (plural) showed the consultation list / 403 for certificates-only staff | 10-02 |
| 6 | Email-verification and password-confirmation pages sent doctors / staff to the admin dashboard (403) | 10-02 |
| 7 | Settings save without a section → server error | 10-02 |
| 8 | Name search: full names, "Last, First", wildcards and the Consultations list found nothing | 10-01 |
| 9 | Export CSV had no patient names on some tabs; screen and file used different queries | 10-01 |
| 10 | Queue screens 500 after a queued patient was archived; archived patients could be queued; stale edit page could reopen a finished entry | 10-01 |
| 11 | Doctors had add / archive / restore / reset-password on patients (owner: view + edit only) | 10-01 |
| 12 | Staff "Reset password" key on the patient list always answered 403 | 10-01 |
| 13 | The test suite ran out of memory past ~230 tests (`memory_limit` 512M in `phpunit.xml`) | 10-01 |
| 14 | Pass-1 M-13: registering or editing a patient with the e-mail / university id of an **archived** patient gave a generic "already taken"; now says it belongs to an archived account | 10-02 |
| 15 | Pass-1 L-05: stock-in and lab-request numbers are drawn at random and checked before saving; two simultaneous saves could collide. Unique index on stock-in numbers + automatic retry with a new number (lab requests already had the index) | 10-02 |
| 16 | **Queue:** the doctor's "View Form" opened the patient's whole vitals history (all visits) and ignored which form was attached, so the new form could not be told apart and its complaint was never shown. It now opens the attached consultation itself, and that page has an **Edit / add assessment & plan** button | 10-02 |
| 17 | **Queue:** the attached form kept pointing at a consultation that was later deleted, and the staff screen still said "Has Form". The attachment is now recalculated whenever a consultation is saved, deleted or restored, and both screens require the form to exist | 10-02 |
| 18 | **Queue:** a form from months ago showed as "Form Available" exactly like today's. The doctor and staff screens now say **New form** (recorded for this visit) or **Previous form / Earlier form only**, and the queue message says which was attached | 10-02 |
| 19 | **Landing page "Dashboard" 403** (doctor / staff / nurse pressed it and landed on `/admin/patients`). Both buttons now use `getDashboardRouteName()`; a signed-in user with no dashboard sees no button and is sent to the landing page, never to the admin dashboard | 10-08 |
| 20 | **Editing a medical certificate or excuse slip crashed (500)** for every role: the page script used variables that only exist for consultation forms | 10-08 |
| 21 | **Role-blind links and buttons** found by the link audit: queue-edit Cancel, add-doctor Discard, doctor-list links, prescription / dispensing / dashboard patient and doctor links, activity-log View, patient-list Create Prescription + consultation count, patient-history Edit / PDF / View, consultation-list Create Prescription. Each is now drawn only when the user's module policy lets them open it ([link-audit-2026-10-08.md](link-audit-2026-10-08.md) §2) | 10-08 |

### 3.3 Features delivered

| Feature | Where |
|---|---|
| Word-based search (`SearchTerm`) in every table, search box and picker | all modules |
| Patient Visits filters: period buttons, month, date range, campus, college, course, year level, department, office, patient type, gender, consult mode, age group, body system, illness / not classified, service, medicine given, nurse / encoder, pregnancy, chronic condition | Report Generation |
| **ACCOMPLISHMENT REPORT** tab — illness by body system × college / GS / F&S / Guest (Guest always shown; + Unspecified when used), Other Services, totals, unclassified count, Prepared by / Noted by; PDF (8.5×13), Excel, CSV from one builder. A **Report** choice makes it **yearly**: the same rows, rules and filters with the columns Q1, Q2, Q3, Q4 and YEAR TOTAL (by consultation date) | Report Generation |
| Illness picker (searchable, by body system, "Others: ___") and services checklist on the consultation form; shown on view page and PDF | Consultations |
| Settings › General: University Physician name / title | Settings |
| Settings › **Report lists**: add, rename, regroup, switch off illnesses and services | Settings (admin) |
| Graduate School (GS) college | seed + migration |
| Default-password gate for every staff account type | all panels |
| Administrative audit trail (field names only), sign-in / sign-out, impersonation | Activity Logs |
| Soft delete of consultations / certificates / excuse slips / lab requests | Consultations, Lab requests |
| Settings › **Deleted records** (administrator): who deleted what, and Restore — a restored consultation takes its medicines out of stock again, refused with the reason if the stock is gone | Settings |
| **Record form** button on every queue row (staff with the documents permission, `manage_request_documents`, and the doctor): opens the consultation form already filled in for exactly the queued patient, so the form cannot end up on a namesake or a new duplicate patient and always reaches the queue entry | Queue |
| Accomplishment Report → "Show them and classify" link and a **Classify / Edit** button on every Patient Visits row, for consultations that have no illness picked | Report Generation |
| **Link audit** — `tests/Feature/Audit/LinkCrawlTest.php`: signs in as 16 kinds of user, opens every page of their panel, loads the lazy tables, follows every link two levels deep and fails on any 403 / 404 / 5xx. **Opt-in** (slow): `$env:LINK_CRAWL='1'; vendor/bin/phpunit --filter LinkCrawlTest` | tests |
| **Dispense History** lists consultation medicines too (view `dispense_history_view`, Source column + filter, read-only consultation page); paging never repeats or skips rows that share a date; the patient's history page lists the same (dispensed prescriptions only, with the consultation medicines) | Dispensing, Patients |
| **Manage User roles** change what a Staff (Nurse) or Doctor can open: menu, dashboard links, buttons and routes follow the role's permissions on the next page; the Roles screen shows only what applies | Settings, all panels |
| **Yearly Medicine Inventory report** — a **Report** choice on Medicine Inventory: per medicine, left from last year, additional (stock-in), and the stock remaining at the end of Q1-Q4 in the medicine's own unit; read from the stock ledger, expired stock noted, an add-up check per medicine (and against the shelf for the current year); screen, PDF (8.5×13), Excel and CSV from one builder | Report Generation |
| **Project name "Medical University Clinic"** (Settings › General, landing page, e-mails, prints) and the College of Law written **COL** | all panels |

### 3.4 Earlier passes still holding (re-verified 10-01)

All six pass-1 Criticals (C-01 … C-06) and the High / Medium items of the 2026-09-30 passes are ✅ — see
[full-reaudit-2026-10-01.md §3](full-reaudit-2026-10-01.md). The two that were only partly fixed then are now closed:
H-02 (list-level bypass → R3-H6) and H-14 (doctor mass-assignment → R3-L1).

---

## 4. Unfinished / partly done 🟡

**Nothing is partly done.** The six rows that were here were finished on 2026-10-02 (see §3.1 R3-M5, R3-M6, R3-L14, §3.2 #14–15 and §3.3).
What each fix deliberately does **not** do:

| Area | Limit to know about |
|---|---|
| Old consultations | classified one at a time (the **Classify** button opens the normal edit page); there is no mass-edit screen — worth building only if the clinic has thousands of old visits |
| Deleted records | covers consultations, certificates, excuse slips and lab requests. Patients have their own archive / restore. Lab requests deleted before 2026-10-02 were removed for good (none existed on the development database) |
| Restoring a consultation | gives back the record and takes its medicines out of stock again; it does not re-attach the visit to a queue entry |
| Failed sign-in log | keeps the e-mail that was typed (needed to spot guessing), never the password; there is no clean-up of old rows yet |
| Dispense History at volume | the view is rebuilt for every click: about 35 ms per 1,000 rows (0.35 s at 10,000 rows, 1.7 s at 50,000); fine for years at this clinic's volume, options in plan §8 |
| Manual dispense records | the table has no "recorded by", so that cell shows NA (as before the Phase 2 view); the "Dispensed At" of a consultation is when the consultation was recorded |
| Yearly Medicine Inventory | counts only what the stock ledger knows, by the day each movement was recorded: a year before the ledger's first line is empty, and stock that has no ledger line shows as a difference from the shelf in Notes. The clinic's ledger starts in 2026, so its first yearly report has "left from last year" 0. The PDF takes about 10 s for 55 medicines (DomPDF; about 38 s for 220), fine for this clinic |

---

## 5. Pending — known, not started ⏳

| ID | Item | Kind |
|---|---|---|
| R3-L10 | Stale docs: `guide.md` (`automation.ps1`), `RECODE_PROJECT_GUIDE.md` §13.7/§14 (`CACHE_STORE=database`), empty `CLAUDE.md` | docs |
| R3-L11 | Startup script prints `127.0.0.1:8000` but binds the LAN IP; root `.htaccess` uses an absolute `/public/` (pass-1 L-09) | deployment |
| R3-L15 | "Forgot password" is a dead end (mail goes to the log) and reveals whether an e-mail exists | UX / security |
| R3-L17 | Sessions last 120 idle minutes and survive closing the browser on shared clinic PCs | config |
| R3-L19 | Backups: 30-day retention only, dumps unencrypted, half-restore needs manual recovery | ops |
| M-16 (pass 1) | Lock file not re-tested on PHP 8.4 | check |
| Test-suite speed | runs took 15–16 min while the machine was busy and 3–5 min when idle (latest: 5 min). Machine load, not the code | watch |

---

## 6. Needs your decision 🔒

| Item | Why it waits | Suggested |
|---|---|---|
| **Rotate the database password** | the old password is in git history (`auto-backup-database.bat`) | rotate now; purge history with `git filter-repo` if the repo is shared |
| Laravel 10 advisories (debug-page XSS, signed-URL path, email-rule CRLF) | fixed only in Laravel 12 (PHP 8.2+) | plan a Laravel 11/12 + PHP 8.2 upgrade |
| `spatie/laravel-medialibrary` 10.x advisories | fixed in 11.23 (PHP 8.2+) | upgrade with the above |
| `rappasoft/laravel-livewire-tables` held at 3.2.x | 3.8 breaks the customised table views | port the views in a dedicated task |
| `laravelcollective/html` abandoned | no CVE; used by ~90 forms | leave |
| npm advisories (runtime 5: `nanoid`, `postcss`, `quill`; build tool-chain 68) | mostly build-time only | `npm audit fix` for the runtime set; plan a Vite migration |
| **Merge `changes_v2` into `develop`** | `develop` still carries the 215 GitHub-reported vulnerabilities; the fixes are only on `changes_v2` | open a PR when you are ready; run the deployment checklist on the clinic copy first |
| Mass-edit screen for old consultations | one-at-a-time "Classify" exists now | build only if the clinic has thousands of old visits |
| PHI snapshot columns in `activity_logs`; clean-up of old failed-sign-in rows | needed by the log detail view / security review | decide a retention period |
| **Letterhead address line** "University Medical Clinic, CNPAHS Bldg., Kagawasan Ave., Dumaguete City" (hard-coded in 10 views: the consultation page and PDF, the medical certificate and excuse-slip prints / PDFs / forms, and the registration e-mail's fallback) | it is the printed address block, not "NORSU Clinic", so Phase 4.1 left it; the new name reads "Medical University Clinic" (the words are in a different order) | keep it, or tell me the exact wording to print |
| **Who may see the dashboards' patient data** (found in row 5.1) | the plan's rule opens every dashboard to every signed-in staff account and doctor, so a staff account **without** Manage Patients still sees today's registered patients (name and e-mail, as plain text, no link) and a doctor sees the medicine and patient totals | keep (the rule as written), or hide the "recent patients" list for staff without Manage Patients: one line in `StaffDashBoardTable` / the staff route |
| **Audit trail for the queue** (found in row 5.3) | adding, calling, completing, cancelling and **deleting** a queue entry leave no audit row (only `added_by` and the call / completion times stay), and a delete removes the entry for good in any status | keep, or write the five actions to the audit trail and make the delete a cancel for entries that were already called |
| **Vital signs on a prescription** (found in row 5.5) | when the weight, pulse, temperature, blood pressure or height of a prescription is left blank, the save fills it from the patient's latest consultation, and every edit does it again (a field the doctor deliberately emptied comes back); the form shows the values first. You decided on 2026-10-16 that the **consultation** form leaves today's vitals blank; nothing was decided for prescriptions | keep (a prescription belongs to the latest consultation), or leave blank values blank like the consultation form |
| **Doctors and manual dispense records** (found in row 5.7) | doctors are now read-only in the inventory (your decision), but on the Dispensing screen a doctor with Manage Medicines still gets the "Add Dispense Record" pop-up and the Edit / Delete buttons of manual records, and their routes allow it (stock moves), while the Verify tab never offers them the Dispense button | make doctors read-only in Dispensing too (remove their dispense-record and dispense routes), or keep it |
| **The Schedules (appointments) tab of Report Generation** (found in row 5.10) | the tab lists queue entries that have a `scheduled_at`, and nothing in the system ever sets it (the queue has no appointment feature), so the tab, its CSV and the "Schedules" heading are always empty | remove the tab and its CSV, or add a way to schedule a patient |
| **Who may read the raw activity log** (Notifications & Alerts › System notifications: patient names, contact numbers, complaints) | the plan's rule (§2.2) opens Reports and Notifications to **every** staff account and doctor; since Phase 3 that includes the raw log, which before was limited to some staff designations (R3-H6). It is a one-line change to limit it | keep it for administrators and doctors only (staff see Reports and Low-stock alerts) unless the clinic wants nurses to read it |

### Decisions already taken (do not re-ask)

* **Doctor archive/restore** (owner, 2026-10-20): retain the doctor profile, licence numbers, specialization links, address and photo on archive; exclude archived users from lists, cached pick-lists and management URLs; keep the licence reserved so a restore is complete. Implemented and verified.
* **Seven dead page scripts** (owner, 2026-10-20, row 5.22): enable each with a real browser check; completed.

* Doctors may **view and edit** patients only; add / archive / restore / reset password = staff/nurse + admin.
* **One shared queue.**
* A consultation can be deleted only by the **admin or its creator**; every deletion leaves an audit snapshot.
* Prescriptions: a doctor writes only in their own name; staff only on a recorded **verbal / phone order**; staff cancel, never delete.
* Lab requests: completed / referred / rejected never deletable; collected / processing must be cancelled first.
* Accomplishment report: illness = picker from the clinic list by body system; services = checklist + automatic counts; columns = every college + GS + F&S + Guest (Guest always shown; + Unspecified when used; the yearly version has Q1-Q4 + year total); visit date = consultation date; report defaults to **Walk-in**; Prepared by = signed-in user, Noted by = University Physician in Settings.
* **Project name "Medical University Clinic"** (clinic name only): the university name, logo, e-mail address and technical names (`norsu_clinic` database, backup prefix, folders) stay; the College of Law is written **COL**. The printed form codes "NORSU-CLINIC-FORM-01 / 02" (consultation view page, medical certificate and excuse-slip prints and PDFs) are **not** renamed (owner, 2026-10-15).
* The working database `norsu_clinic` is **left alone** while a phase is checked: migrations are rehearsed on a scratch copy of the clinic dump, and the working database gets them when you run `php artisan migrate` (owner, 2026-10-15).
* Work inline (no agent workflows), test-first, ask when unsure.
* **4 stock batches of deleted medicines** (#1: 20 units, #2: 40, #28: 250 + 240; found by the Phase 1 rehearsal): keep the history — the medicines are re-created as "Unknown medicine #N" placeholders and their stock is written off through the ledger (a medicine has no inactive flag, and every picker lists medicines with stock); done by `php artisan inventory:recover-orphan-batches --apply` (owner, 2026-10-09). Rename the placeholders if you know what they were.
* **Push `changes_v2` after every verified phase commit without asking** (owner, 2026-10-09). Never force-push, never push another branch, never merge into `develop` without asking.

---

## 7. To do on the clinic copy (deployment checklist)

1. Back up the database (`php artisan db:backup`).
2. Pull `changes_v2`, then `composer install` (`--no-dev` in production).
3. `php artisan migrate` — **first converts every MyISAM table to InnoDB** (the clinic's database was created on a MyISAM server), turns zero dates into NULL and adds the missing foreign keys (a key blocked by orphan rows is skipped and printed), then creates the illness / service tables, snapshot columns, GS college, physician settings, the soft-delete columns of consultations and lab requests, a unique index on stock-in numbers (skipped if the copy already holds duplicate numbers), and rebuilds the Stock-out view. It also renames the clinic to "Medical University Clinic" (only a name that still is the old default, and the default slider text) and "College of Law (CL)" to "(COL)" everywhere it was saved. *(Applied on the development database `norsu_clinic` up to Phase 3; the two Phase 4 data migrations are not applied there yet.)*
3a. Once, after `migrate`: `php artisan inventory:recover-orphan-batches` (dry run), then `--apply` — the clinic's data has 4 stock batches of deleted medicines #1 / #2 / #28; this re-creates the medicines as placeholders, writes their stock off through the ledger and lets the last foreign key in (see §6).
3b. `php artisan db:integrity` (add `--compare-fresh` to also compare the structure with a freshly built install, about a minute) — read-only; exit code 1 means an error. Expected on the clinic's data: 0 errors, and the duplicate-medicine and phone warnings. All new commands are described in [commands.md](commands.md).
3c. Admin: open Settings › Manage User roles and **review Staff (Nurse)**: every staff account (nurses included) now gets exactly what that role allows. The defaults give Patients and Queue, Consultations / Certificates / Prescriptions / Lab, Inventory and Dispensing, Doctors and Specializations; untick what nurses should not use. The old `nurse` role is retired (its accounts were moved by `migrate`); Reports and Notifications stay open to every staff account.
3d. **Real-time updates (Phase 7), once:** `composer install` and `npm ci && npm run prod` bring the new packages (step 2 and 4). In the server's own `.env` set `BROADCAST_DRIVER=reverb`, `REVERB_HOST=127.0.0.1`, `REVERB_PORT=8080`, `REVERB_SCHEME=http` and your own private random `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET` (never copy the development ones). Allow inbound TCP 8080 in the Windows firewall of the clinic PC (the PowerShell line is in [commands.md](commands.md), "Real-time updates") and check from another PC with `Test-NetConnection <server-ip> -Port 8080`. The startup script (`startup-dev-environment.bat` or `.ps1`) runs everything in one terminal: it starts `php artisan reverb:start` in the background of that terminal and the web server on all addresses (`localhost`, `127.0.0.1` and the PC's current LAN address, detected each time); keep the terminal open, Ctrl+C stops both. Without it everything still works: pages show "Live updates off" and refresh every minute.
4. `npm ci && npm run prod` (built assets are not in git; this also rebuilds the Roles screen's select-all script). Also set `APP_NAME="Medical University Clinic"` in the server's own `.env` (it is not in git; it titles the guest pages and names the session cookie, so everybody signs in once more).
5. Restart through the startup script (it clears and rebuilds the caches).
6. Settings › General → fill **University Physician** (otherwise "Noted by" is blank).
7. Tell doctors and staff who still use `123456` that they will be kept on their dashboard until they change it.
8. Once, after a backup: `php artisan inventory:reconcile` (look for `RETURN-*` / `2099-12-31` batches left by the old consultation code).
9. From the 2026-09-30 checklist, if not done yet: `consultation-images:secure`, `text:repair-entities`, `phone:normalize` (dry run first), `vendor:publish --tag=log-viewer-assets --force`, rotate the DB password.

---

## 8. Commits and the working tree

**Current checkpoint (2026-10-20):** the doctor archive follow-up and Phase 5 rows 5.14–5.22 are verified on `changes_v2`; exact work and handover commits are recorded in [Handover.md](../Handover.md). No merge into `develop`. No dependency, schema or working-database change in this checkpoint. The older commit table below is historical.

| Commit | Content |
|---|---|
| `1145357` | last commit of the 2026-09-30 remediation |
| `53935c5` | staff/doctor workflow fixes (R3), search + CSV + Accomplishment Report, route / access audit fixes, all migrations and 274 tests (134 files) |
| `6a35d83`, `1d475e2` | docs: re-audit, accomplishment report, route / access audit, this file |
| `13a78d4` | the "Unfinished" pass (lab-request soft delete + Deleted records screen, failed sign-in trail, patient-type codes, archived-account message, unique-number retry, Classify path) and the Queue ↔ consultation fixes (View Form opens the attached form, New / Previous form labels, Record form button, attachment kept in sync on delete / restore); 28 files, 302 tests |
| `fd1b43a` | `docs/STATUS.md` and the re-audit note for the above (last commit) |

**Historical 2026-10-08 work**, now confirmed on `origin/changes_v2` as `d427976` and `d8dd314` (`app/helpers.php` carries one hunk of the first and the rest of the second, so it was split by hunk). Subjects: "Finish the Pending cleanup: strengths, stock register, dead code, built-in server router" and "Landing-page Dashboard follows the signed-in role; link audit and role-blind link fixes". Contents:

| Workstream | Files |
|---|---|
| 2026-10-02 "Pending" cleanup (R3-L5 / L6 / L7 / M9) | deleted: `CacheWarmup`, `SyncRolePermissions` (empty), `MedicineAvailabilityController`, `StateController`, `Staff` model, `StaffFactory`; changed: `DispenseRecordController`, `MedicineController`, `PrescriptionController`, `User`, `MedicineAvailabilityRepository`, `PrescriptionRepository`, `MedicineInventoryService`, `routes/api.php`, `routes/web.php`, `errors/404.blade.php`, `prescriptions/form_v2.blade.php`; new: `server.php`, `PendingCleanupTest`, `PrescriptionStrengthsTest`, `InitialStockRegisterTest`, `BuiltInServerRouterTest` |
| 2026-10-08 landing page + link audit | `app/helpers.php` (`getDashboardRouteName()`, safe fallback), the 14 views in [link-audit §2](link-audit-2026-10-08.md), `AuthenticationTest` + `EmailVerificationTest` (expectation updated); new: `LandingPageDashboardTest`, `RoleAwareLinksTest`, `tests/Feature/Audit/LinkCrawlTest.php`, `docs/link-audit-2026-10-08.md` |
| neither | `public/messages.js` (regenerated by `npm run prod`; still the open "untrack it?" question below) |

Pushed to `origin/changes_v2` on 2026-10-02 (fast-forward, no force), in two rounds. The first went in as two commits (code, docs) instead of the four proposed,
because several files (`DocumentIssuanceController`, `LogsActivity`, `helpers.php`) belong to more than one workstream.

**Generated assets:** the existing local assets were rebuilt successfully. The build-generated `public/messages.js` was restored to the tracked version because no translations changed; generated assets are excluded from this checkpoint.

**Not merged:** `develop` is untouched. The older default-branch advisory numbers are historical, not a current vulnerability scan. Dependency upgrades and merging into `develop` still need the owner's separate approval.

---

## 9. Where the detail is

| Document | Content |
|---|---|
| [full-reaudit-2026-09-30.md](full-reaudit-2026-09-30.md), [-pass2.md](full-reaudit-2026-09-30-pass2.md) | pass 1 / pass 2 findings (C-, H-, M-, L-, P2-) |
| [remediation-status-2026-09-30.md](remediation-status-2026-09-30.md) | what the 2026-09-30 phases fixed; accepted items; deployment checklist |
| [full-reaudit-2026-10-01.md](full-reaudit-2026-10-01.md) | third pass (R3-*), status of earlier audits (§3), remediation status (§11, §12) |
| [accomplishment-report-2026-10.md](accomplishment-report-2026-10.md) | search, CSV, filters, Accomplishment Report: design and counting rules |
| [route-access-audit-2026-10-02.md](route-access-audit-2026-10-02.md) | admin 403 root cause, route × role audit method and results, fixes |
| [audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) | the ordered work plan (Phases 0–10), decisions, findings per phase |
| [commands.md](commands.md) | **log of the new artisan commands** (`db:integrity`, `db:restore-foreign-keys`, ...): what each does, whether it changes data, where and when to run it |
| `tests/Feature/Regression/*` | one regression test file per fixed area |

### How to keep this file current

Update §1 (counts), move rows between §3 / §4 / §5 / §6 as items change, and add a line to §2 for each work session.
