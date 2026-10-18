# Handover — Claude Code ⇄ OpenAI Codex

Two AI assistants work on this project: **Claude Code** and **OpenAI Codex**. This file is how they stay in sync.
It is **not** a change log. It records only two moments of a work session:

- **START**: when an AI begins a piece of work;
- **END**: when that work is **committed and pushed** (the push is the checkpoint).

Nothing is written while work is in progress, half-done or not started yet.

---

## 1. Protocol (both AIs, every session)

### Before doing anything
1. `git fetch` then read what is pushed:
   ```bash
   git log --oneline -15 origin/changes_v2
   ```
2. Read **§2 Current checkpoint** and the newest entries in **§3 Log** (newest on top).
3. Run `git status`, then decide:

   | What you see | What to do |
   |---|---|
   | Working tree clean and the newest entry is an **END** | Safe to start from the pushed commit it names |
   | The newest entry is a **START with no END** (work in progress, not pushed) | The other AI stopped mid-work. **Do not continue or overwrite it.** Ask the owner whether to continue it, finish it or discard it |
   | Uncommitted changes that no open START explains | Ask the owner before touching anything |

### When you start
4. Add one **START** entry at the top of §3: date / time, AI name, branch, the pushed commit you start from, what you will do (link the plan item, e.g. `docs/audit-plan-2026-10-08.md` Phase 1.2).
   Do not commit or push the START entry by itself; it travels with your END.

### While working
5. Write nothing here. Progress lives in the code, the tests and the plan's checkboxes (`docs/audit-plan-2026-10-08.md`).

### When you finish (after a verified phase; the owner's standing go-ahead to push, 2026-10-09)
6. Verify first (full test suite and the checks the plan names for that item).
7. Turn your START into a finished entry (add the **END** part):
   - commits pushed (hashes from `git log`);
   - what is done;
   - how it was verified;
   - **where to continue** (the next plan item);
   - anything left open.
8. Update **§2 Current checkpoint**.
9. Commit `Handover.md` as the last commit of the push (message: `handover: <short summary>`), then push.

### If you must stop without pushing
10. Leave the START entry open (no END). That open entry is the signal to the other AI that unpushed work is in the working tree.

### Never
- Never force-push, never push another branch, never merge into `develop` without asking. (Pushing `changes_v2` after a
  verified phase needs no further question: standing go-ahead of the owner, 2026-10-09.)
- Never rewrite or delete the other AI's entries; add a new entry instead.
- Never paste secrets (`.env` values, passwords) or patient data here.

---

## 2. Current checkpoint

| | |
|---|---|
| Branch | `changes_v2` (base `develop`) |
| Last pushed work | Plan row 5.11 Notifications & Alerts: the `Plan 5.11` commit, then this `handover:` commit (Claude Code, 2026-10-19); before it row 5.10 Report Generation: `f409c58`, then this `handover:` commit (Claude Code, 2026-10-18); before it row 5.9 Certificates: code and tests `564437d` (its commit message says "handover", written before the records were ready), records in the commit after it (Claude Code, 2026-10-18); before it row 5.8 Lab requests: `746707e`, then this `handover:` commit (Claude Code, 2026-10-18); before it row 5.7 Dispensing with the row 5.6 owner decisions: `6a93b65`, then this `handover:` commit (Claude Code, 2026-10-18); before it row 5.6 Inventory: `36f9cf4`, then this `handover:` commit (Claude Code, 2026-10-18); before it row 5.5 Prescriptions: `25e1ddb`, then this `handover:` commit (Claude Code, 2026-10-18); before it row 5.4 Consultations: `6682265`, then this `handover:` commit (Claude Code, 2026-10-18); before it row 5.3 Queue `71c14fe` / `8a74e35` (2026-10-16), row 5.2 Patients `7a74078` / `f7f6212`, row 5.1 Dashboard `e33cfc7` / `6de3e2b` and plan item 5.23 ("Others" names): `a0fea49`, handover `eed070f` (Claude Code, 2026-10-16); before it Phase 5 checkpoint A: `2462ee8` (cross-cutting code sweeps, 33 new tests), then this `handover:` commit (Claude Code, 2026-10-15); before it Phase 4: `125de63` (name "Medical University Clinic", COL, Guest column, yearly Accomplishment Report, yearly Medicine Inventory report), after the Phase 2 re-check `ffda2df`, Phase 3 `b945e64`, Phase 2 `c993859` (Codex) and the Phases 0 / 1 checkpoint `4957fd9`; then this `handover:` commit (Claude Code, 2026-10-15) |
| Work in progress (not pushed) | none |
| Next step | Phase 5 module passes of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md): rows 5.1 to 5.11 and item 5.23 are done, **5.12 Settings (General)** is next; the owner's request "5.7 to 5.11" is complete (the owner asked for 5.7 to 5.11 in a row) |
| Pushing | the owner's standing go-ahead (2026-10-09): push `changes_v2` after every verified phase, no need to ask |
| Commands added so far | [docs/commands.md](docs/commands.md): `db:integrity`, `db:restore-foreign-keys`, `inventory:recover-orphan-batches` |
| Project status | [docs/STATUS.md](docs/STATUS.md): current checkpoint, tests, features and next phase updated (Phase 4); `norsu_clinic` is not yet migrated for Phase 4 (see the entry below); §8 retains an older snapshot to reconcile in plan Phase 10.2 |

---

## 3. Log (newest on top)

### 2026-10-19 00:30 +08:00 — Claude Code — START → END (pushed 2026-10-19)
- **Branch / from:** `changes_v2` @ `615f283` (row 5.10 and its handover are pushed; working tree clean except the build-generated `public/messages.js`).
- **Work:** Phase 5 row 5.11 Notifications & Alerts (low stock, expiry, system notifications) of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md), the last part of the owner's request "5.7 Dispensing to 5.11 Notifications & Alerts" (2026-10-18): Pass A test-first, Pass B live per role on a scratch copy of the clinic data (`norsu_clinic` untouched), full suite, records, push.
- **END:** pushed on the owner's standing go-ahead. This closes the owner's request "5.7 Dispensing to 5.11 Notifications & Alerts" (5.7 `6a93b65`, 5.8 `746707e`, 5.9 `564437d` + records `ad50db1`, 5.10 `f409c58`, 5.11 below).
  - **Commits:** the `Plan 5.11` commit (code, tests, records), then this `handover:` commit.
  - **Done (plan 5.11, Pass A and Pass B):** the four alerts of the sidebar's Inventory entry (expired, expiring within 7 / 30 days, low stock) were cached for five minutes for everybody and never cleared; they are counted when the page is drawn and only for a role that can open the inventory (`App\\Support\\InventoryAlerts`, boundaries pinned: today is critical, +7 critical, +8 and +30 warning, empty batches ignored, a medicine counts once, low stock by one rule); the menu entries follow the permissions.
  - **Verified:** `LINK_CRAWL=1` full suite ****647 tests / 6,778 assertions**, all passing (18 min 58 sec)**; crawl 1,604 requests, no problems; no route added. 5 new tests (`NotificationsPassATest`); 10 mutations of production code each fail a test (two first survived and their cases were strengthened). Pass B on a scratch copy of the real clinic data (the sidebar badge follows a real Stock-In on the next page; with and without the medicines permission). Scratch schema dropped, `norsu_clinic` untouched.
  - **Where to continue:** row 5.12 Settings (General), then 5.13 to 5.22 of the plan's table.
  - **No new Artisan commands;** [docs/commands.md](docs/commands.md) unchanged.
  - **Left open (STATUS §6):** owner questions: doctors and manual dispense records (5.7), the always-empty Schedules tab (5.10), the vitals on a prescription (5.5), the raw activity log; the duplicate medicines #50 / #55 (merge tool, plan 8.2) made a live stock-in go to the wrong twin once.

### 2026-10-18 21:30 +08:00 — Claude Code — START → END (pushed 2026-10-18)
- **Branch / from:** `changes_v2` @ `a20c979` (row 5.9 and its records are pushed; working tree clean except the build-generated `public/messages.js`).
- **Work:** Phase 5 row 5.10 Report Generation (Patient Visits filters, Accomplishment Report monthly + yearly, Medicine Inventory current + yearly, dispensing, global search, activity log, all downloads) of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md), part 4 of the owner's request "5.7 Dispensing to 5.11 Notifications & Alerts" (2026-10-18). Includes one definition of "low stock" for the sidebar, the report, its CSV and the medicine list. Pass A test-first, Pass B live per role on a scratch copy of the clinic data (`norsu_clinic` untouched), full suite, records, push.
- **END:** pushed on the owner's standing go-ahead.
  - **Commits:** `f409c58` plan row 5.10 (code, tests, records), then this `handover:` commit.
  - **Done (plan 5.10, Pass A and Pass B):** "low stock" had three definitions (sidebar badge, Report inventory tab and CSV, colour in the medicine list); one now, `Medicine::scopeLowStock()` / `isLowStock()` / `stockStatus()`, used by all four and pinned on a grid of 11 cases; the Report inventory screen read each medicine's batches with its own query (now the loaded ones); the other tabs' date ranges, per-row queries and download permissions were measured and pinned (already right).
  - **Verified:** `LINK_CRAWL=1` full suite **642 tests / 6,757 assertions**, all passing (17 min 18 sec); crawl 1,604 requests, no problems; no route added. 6 new tests (`LowStockDefinitionTest` 3, `ReportsPassATest` 3); 12 mutations of production code each fail a test (one first survived and the case was strengthened). Pass B on a scratch copy of the real clinic data (the same medicine, Laradin, is the one low-stock medicine in the badge, the report, the CSV and the list; every tab and download answers). Scratch schema dropped, `norsu_clinic` untouched.
  - **Where to continue:** row 5.11 Notifications & Alerts (found while reading: the sidebar's expiry / low-stock alerts are cached for 5 minutes for everybody and never cleared).
  - **No new Artisan commands;** [docs/commands.md](docs/commands.md) unchanged.
  - **Left open (STATUS §6):** owner question: the Schedules tab is always empty.

### 2026-10-18 17:30 +08:00 — Claude Code — START → END (pushed 2026-10-18)
- **Branch / from:** `changes_v2` @ `117c88e` (row 5.8 and its handover are pushed; working tree clean except the build-generated `public/messages.js`).
- **Work:** Phase 5 row 5.9 Certificates (medical certificate, excuse slip, PDF / print, edit) of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md), part 3 of the owner's request "5.7 Dispensing to 5.11 Notifications & Alerts" (2026-10-18): Pass A test-first, Pass B live per role on a scratch copy of the clinic data (`norsu_clinic` untouched), full suite, records, push.
- **END:** pushed on the owner's standing go-ahead.
  - **Commits:** `564437d` (the code and the tests; the message says "handover ... pushed" because the records step failed after the commit was started, no force-push was done) and the records commit that follows it.
  - **Done (plan 5.9, Pass A and Pass B):** the medical certificate and excuse slip name their boxes differently from the consultation form (`vital_signs_*_2`, the pressure parts, `subjects[][]`), so the limits of row 5.4 never applied to them: a long pulse, temperature or teacher failed the save with a general error and `subjects` could be any text; they are validated now (create and edit), the patient must be an existing account, a blank pressure is saved as nothing instead of "/".
  - **Verified:** `LINK_CRAWL=1` full suite **636 tests / 6,717 assertions**, all passing (43 min 29 sec: busy machine); crawl 1,604 requests, no problems; no route added. 4 new tests (`CertificatePassATest`); 10 mutations of production code each fail a test. Pass B on a scratch copy of the real clinic data (a medical certificate and an excuse slip with subjects through the real forms, list, detail, edit, print page and PDF, doctor and 403 without the documents permission). Scratch schema dropped, `norsu_clinic` untouched.
  - **Where to continue:** row 5.10 Report Generation, then 5.11 Notifications & Alerts. Found while reading for 5.10: **three different definitions of "low stock"** (sidebar badge, the Report inventory tab and its CSV, the medicine list badge) and a per-row query in the Report inventory view; they are unified in 5.10 and 5.11 uses the same one.
  - **No new Artisan commands;** [docs/commands.md](docs/commands.md) unchanged.
  - **Left open:** nothing new for the owner.

### 2026-10-18 13:30 +08:00 — Claude Code — START → END (pushed 2026-10-18)
- **Branch / from:** `changes_v2` @ `6b08e32` (row 5.7 and its handover are pushed; working tree clean except the build-generated `public/messages.js`).
- **Work:** Phase 5 row 5.8 Lab requests (create / edit, results, status workflow, PDF, delete / restore) of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md), part 2 of the owner's request "5.7 Dispensing to 5.11 Notifications & Alerts" (2026-10-18): Pass A test-first, Pass B live per role on a scratch copy of the clinic data (`norsu_clinic` untouched), full suite, records, push.
- **END:** pushed on the owner's standing go-ahead.
  - **Commits:** `746707e` plan row 5.8 (code, tests, records), then this `handover:` commit.
  - **Done (plan 5.8, Pass A and Pass B):** the header sorts of the Lab list work (the builder pre-ordered by date, so no click could change the order; the open lead from checkpoint A) and its search finds a request by a test name; the request date must be a real date (1900 to a year ahead) with plain messages; the results typed on the edit page are validated (status one of the four, value and notes up to 10,000 characters; a wrong status ended in a database error after the header was saved); a status change is re-read under a lock in a transaction (a stale page could reopen a finished request); the new-request form copies only a patient (it copied any account named by `?user_id=`).
  - **Verified:** `LINK_CRAWL=1` full suite **632 tests / 6,667 assertions**, all passing (16 min 34 sec); crawl 1,604 requests, no problems; no route added. 6 new tests (`LabRequestPassATest`); 11 mutations of production code each fail a test (one first survived because the test data had equal date and id order). Pass B on a scratch copy of the real clinic data with nurse, doctor and admin (request through the real form with a refused year-1066 date, results on the edit page, the status workflow to completed, PDF, search by test, doctor read-only, 403 without the documents permission). Scratch schema dropped, `norsu_clinic` untouched.
  - **Where to continue:** row 5.9 Certificates, then 5.10 Reports, 5.11 Notifications & Alerts.
  - **No new Artisan commands;** [docs/commands.md](docs/commands.md) unchanged.
  - **Left open:** nothing new for the owner.

### 2026-10-18 10:30 +08:00 — Claude Code — START → END (pushed 2026-10-18; part 1 of the owner's "5.7 to 5.11" request: owner decisions on 5.6 + row 5.7)
- **Branch / from:** `changes_v2` @ `cba7a58` (row 5.6 and its handover are pushed; working tree clean except the build-generated `public/messages.js`).
- **Work:** on the owner's instruction (2026-10-18: Unit Cost → remove it (nothing else reads it); doctors → read-only in the inventory; "continue to the next plan item, 5.7 Dispensing to 5.11 Notifications & Alerts"): (1) the two answered owner questions of row 5.6 (Unit Cost box removed from the medicine form and its saves; doctors' inventory write routes removed, buttons and forms hidden), then (2) plan rows **5.7 Dispensing, 5.8 Lab requests, 5.9 Certificates, 5.10 Reports, 5.11 Notifications & Alerts** of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md), one after the other, each: Pass A test-first, Pass B live per role on a scratch copy of the clinic data (`norsu_clinic` untouched), full suite, records, push (one push per row; each push closes its own entry and the next row opens a new START).
- **END (this push: the 5.6 owner decisions and row 5.7):** pushed on the owner's standing go-ahead.
  - **Commits:** `6a93b65` plan row 5.7 with the 5.6 owner decisions (code, tests, records), then this `handover:` commit.
  - **Done:** **owner decisions on 5.6:** the "Unit Cost" box is removed from the medicine form and no longer saved (nothing read it; the column stays), doctors are read-only in the inventory (write routes removed from `routes/doctor.php`, no new / edit / delete forms or buttons, the prescription pop-up that creates a medicine is closed to them). **Row 5.7 Dispensing:** the dispense form's quick new-patient pop-up needs the Patients permission and is closed to doctors; the Verify Prescription list has a search (patient, doctor, number) and a doctor account without a doctor profile sees none; the manual dispense record's date must be a real date (create and edit); the dispense form's category medicine list no longer queries per medicine; an atomic-save test pins that a failed line saves nothing.
  - **Verified:** `LINK_CRAWL=1` full suite **626 tests / 6,619 assertions**, all passing (16 min 14 sec); crawl 1,604 requests, no problems (fewer: the doctors' inventory write routes are gone). 9 new tests (`DispensingPassATest` 6, `InventoryOwnerDecisionsTest` 3); 22 mutations of production code each fail a test (one first survived and got its own test). Pass B on a scratch copy of the real clinic data with nurse, doctor and admin sessions (Verify list search, a refused dispense with the exact reason, a successful dispense after a real Stock-In, the manual dispense record through the real pop-up, PDF, the doctor's read-only inventory). Scratch schema dropped, `norsu_clinic` untouched.
  - **Where to continue:** row 5.8 Lab requests, then 5.9 Certificates, 5.10 Reports, 5.11 Notifications & Alerts (all asked for by the owner).
  - **No new Artisan commands;** [docs/commands.md](docs/commands.md) unchanged.
  - **Left open (STATUS §6):** owner question: should doctors also be read-only in Dispensing (they still get the Add / Edit / Delete of manual dispense records); the "Unit Cost" and the "doctors' inventory buttons" questions are answered and removed.

### 2026-10-18 06:30 +08:00 — Claude Code — START → END (pushed 2026-10-18)
- **Branch / from:** `changes_v2` @ `1b7f927` (row 5.5 and its handover are pushed; working tree clean except the build-generated `public/messages.js`).
- **Work:** Phase 5 row 5.6 Inventory (medicines, categories, generics, stock-in, batches, expiry, low-stock, reconcile) of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md), on the owner's go (2026-10-18: "continue to the next plan item, 5.6 Inventory"): Pass A test-first (authorisation incl. record level, all-or-nothing saves, stock only through `MedicineInventoryService`, validation vs columns, list search / sort / export, PDFs, N+1 / caches, no internet), then Pass B live per role on a scratch copy of the clinic data (`norsu_clinic` untouched), full suite, records, push.
- **END:** pushed on the owner's standing go-ahead.
  - **Commits:** `36f9cf4` plan row 5.6 (code, tests, records), then this `handover:` commit.
  - **Done (plan 5.6, Pass A and Pass B):** found live: **a new medicine could not be saved through the real form when the optional Reorder Level box was empty** (a blank is sent as null, the column is NOT NULL: "Column 'reorder_level' cannot be null"; same on edit and on the prescription pop-up endpoint): a blank is now 0; new guard `BlankFieldsFitColumnsTest` (every rule that lets a field stay blank against a column that cannot hold null; it also caught the queue status, now `sometimes|in:`); the medicine form's new generic and category are created inside its transaction; manufacturing date not in the future, no year before 1990, expiry not after 2100 (stock-in rows and medicine form); the medicines-by-category pick-list and the Stock-In list no longer query per medicine / per row; the Stock-In search finds a stock-in by the medicine inside it; every inventory write is pinned to the medicines permission.
  - **Verified:** `LINK_CRAWL=1` full suite **617 tests / 6,530 assertions**, all passing (22 min 49 sec); crawl 1,622 requests, no problems; no route added. 12 new tests (`InventoryPassATest` 10, `BlankFieldsFitColumnsTest` 2); 17 mutations of production code each fail a test (8 first survived because one error message hid the other, the tests now check the message of each rule). Pass B on a scratch copy of the real clinic data with nurse, doctor and admin sessions (the blank-reorder-level defect was found there). Scratch schema dropped, `norsu_clinic` untouched.
  - **Where to continue:** row 5.7 Dispensing.
  - **No new Artisan commands;** [docs/commands.md](docs/commands.md) unchanged.
  - **Left open (STATUS §6, owner questions):** the "Unit Cost" box on the medicine form (rule: no prices anywhere); doctors' inventory buttons hidden while their routes work; also in the plan block: prescriptions fill blank vitals from the latest consultation; unused repository methods (Phase 6.6). The users `status` blank-handling and other forms are covered by the new guard test.

### 2026-10-18 03:00 +08:00 — Claude Code — START → END (pushed 2026-10-18)
- **Branch / from:** `changes_v2` @ `722fffd` (row 5.4 and its handover are pushed; working tree clean except the build-generated `public/messages.js`).
- **Work:** Phase 5 row 5.5 Prescriptions (create / edit, verbal order, strengths, activate / deactivate, PDF, dispense) of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md), on the owner's go (2026-10-18: "continue to the next plan item, 5.5 Prescriptions"): Pass A test-first (authorisation incl. record level, all-or-nothing saves, validation vs columns, list, PDF, N+1 / caches, no internet), then Pass B live per role on a scratch copy of the clinic data (`norsu_clinic` untouched), full suite, records, push.
- **END:** pushed on the owner's standing go-ahead.
  - **Commits:** `25e1ddb` plan row 5.5 (code, tests, records), then this `handover:` commit.
  - **Done (plan 5.5, Pass A and Pass B):** the endpoint that creates a catalog medicine with opening stock (`prescription.medicine.store`) now needs the Inventory permission (it needed only the documents permission); the list draws Delete / Edit only where they work (staff never delete, a dispensed prescription is closed); the visit date must be a real date from 1900 to one year ahead (create and edit); the View page no longer crashes (500) when the clinic logo setting is missing; the PDF of a missing prescription is a 404; all-or-nothing saves and the page queries are pinned by tests.
  - **Verified:** `LINK_CRAWL=1` full suite **605 tests / 6,431 assertions**, all passing (14 min 44 sec); crawl 1,622 requests, no problems; no route added. 9 new tests (`PrescriptionPassATest`); 12 mutations of production code each fail a test (one first survived and the test was strengthened). Pass B on a scratch copy of the real clinic data with nurse, doctor and admin: the doctor's form refuses a year-1066 date and saves a normal one (markup shown as text), the nurse sees no Delete button and cancels / reactivates through the real button, the PDF opens and a missing id gives 404, the administrator deletes through the real pop-up, and without the documents permission every prescription page and the new-medicine endpoint answer 403. Scratch schema dropped, `norsu_clinic` untouched.
  - **Where to continue:** row 5.6 Inventory.
  - **No new Artisan commands;** [docs/commands.md](docs/commands.md) unchanged.
  - **Left open (plan block and STATUS §6):** owner question: a prescription fills blank vitals from the latest consultation on every save; double-click creates two prescriptions; unused repository methods (Phase 6.6); the dispense side is row 5.7.

### 2026-10-16 17:30 +08:00 — Claude Code — START → END (pushed 2026-10-18)
- **Branch / from:** `changes_v2` @ `8a74e35` (row 5.3 and its handover are pushed; working tree clean except the build-generated `public/messages.js`).
- **Work:** Phase 5 row 5.4 Consultations (create / edit by nurse and doctor, Plan / Nursing medicines, images, PDF, delete / restore, illness & services) of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md), on the owner's go (2026-10-16: "continue to the next plan item, 5.4 Consultations"): Pass A test-first (it also covers the record-level and live per-role checks that item 5.23 left open), then Pass B live on a scratch copy of the clinic data (`norsu_clinic` untouched), full suite, records, push.
- **END:** pushed on the owner's standing go-ahead.
  - **Commits:** `6682265` plan row 5.4 (code, tests, records), then this `handover:` commit.
  - **Done (plan 5.4, Pass A and Pass B):** the patient's History page shows each clinical part (consultations and compare table, certificates, excuse slips, prescriptions, dispense history) only to roles that may open that module, in the page and in the queries (it showed everything to Manage Patients alone); the visit date, consult mode and the campus / college / course / year level / department / office ids are checked on create and on edit; the sidebar "incomplete consultations" warning is one fresh definition (`DocumentIssuance::incompleteConsultations()`: consultations only, blank = no visible character, no cache, computed only for roles with consultations); the doctor's edit form no longer has `required` on pregnancy status, nursing intervention and vaccination, so a doctor can save the Assessment and Plan on a nurse's record; on the owner's decision (2026-10-16, "Leave today's vitals blank") the previous-visit lookup returns the history fields and the height / weight only.
  - **Verified:** `LINK_CRAWL=1` full suite **596 tests / 6,368 assertions**, all passing (13 min 43 sec); crawl 1,622 requests, no problems; no route added. 8 new tests (`ConsultationPassATest`); 29 mutations of production code each fail a test except 2 equivalent ones (six view-gate mutations first survived and the test now also asserts the visible headings); the first full run found an old test (`ConsultationClassificationTest`) that used a department id that does not exist, now fixed. Pass B on a scratch copy of the real clinic data with nurse, doctor and admin: the nurse's consultation through the real form (the previous-visit lookup leaves today's vitals empty, the warning goes 4 → 5), the doctor saves the Assessment and Plan on the real edit page, an admin deletes the record from the patient's History page and restores it in Settings › Deleted records, and a Staff role without the documents permission sees no clinical history. Scratch schema dropped, `norsu_clinic` untouched.
  - **Where to continue:** row 5.5 Prescriptions.
  - **No new Artisan commands;** [docs/commands.md](docs/commands.md) unchanged.
  - **Left open (plan block):** a consultation has no delete button on its own list / view page (only on the patient's History page); the image-URL paste in the consultation form fetches from the internet (client side); owner questions in STATUS §6 (dashboards' patient data, queue audit trail) unchanged.

### 2026-10-16 14:20 +08:00 — Claude Code — START → END (pushed 2026-10-16)
- **Branch / from:** `changes_v2` @ `f7f6212` (row 5.2 and its handover are pushed; working tree clean except the build-generated `public/messages.js`).
- **Work:** Phase 5 row 5.3 Queue (add, call next, complete, cancel, record form, doctor view, attachment sync) of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md), on the owner's go (2026-10-16: "continue to the next plan item, 5.3 Queue"): Pass A test-first, then Pass B live per role on a scratch copy of the clinic data (`norsu_clinic` untouched), full suite, records, push. (The START entry was written together with the END this time.)
- **END:** pushed on the owner's standing go-ahead.
  - **Commits:** `71c14fe` plan row 5.3 (code, tests, records), then this `handover:` commit.
  - **Done (plan 5.3, Pass A and Pass B):** the sidebar badge is read fresh (it was cached for 30 s for everybody); "how many are in the queue" had four definitions (badge, admin dashboard, doctor dashboard, queue screens) and now has one, `PatientQueue::openToday()` (entries without a live patient were counted by three of them and not by the screens); the doctor's "Current patient" card no longer crashes the screen (500) for an in-progress entry without a call time; no N+1 was found (pinned). The earlier R3 hardening (atomic call / complete, no reopening, one place per patient, stale entries, attachment sync) was re-checked and left as it was.
  - **Verified:** `LINK_CRAWL=1` full suite **588 tests / 6,243 assertions**, all passing (15 min 15 sec); crawl 1,621 requests, no problems; no route added. 3 new tests (`QueuePassATest`); 8 mutations of production code each fail a test (one first survived and the test was fixed). Pass B on a scratch copy of the real clinic data with nurse, doctor and admin: add through the real form (priority and regular), the duplicate refused, the badge follows at once, priority first on both screens, the doctor calls and completes, the nurse sees the completed entry with its times, cancel through the edit form works and reopening is refused, and without Manage Patients all seven queue routes answer 403. Scratch schema dropped, `norsu_clinic` untouched.
  - **Where to continue:** row 5.4 Consultations (it also holds the record-level and Pass B work that item 5.23 left open).
  - **No new Artisan commands;** [docs/commands.md](docs/commands.md) unchanged.
  - **Left open (plan block and STATUS §6):** the queue has no audit trail and a delete removes an entry for good in any status (owner question); the add-to-queue page script never loads (plan 5.22, owner decision pending); `scheduled_at` is read by the Reports "appointments" list but never set (row 5.10).

### 2026-10-16 11:40 +08:00 — Claude Code — START → END (pushed 2026-10-16)
- **Branch / from:** `changes_v2` @ the `handover:` commit that follows `e33cfc7` (row 5.1 is pushed; working tree clean except the build-generated `public/messages.js`).
- **Work:** Phase 5 row 5.2 Patients (list, create, edit, history, archive / restore, reset password, email verification) of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md), on the owner's go (2026-10-16: "continue to the next plan item, 5.2 Patients"): Pass A (authorisation, all-or-nothing saves, validation vs columns, list search / sort / export, N+1 and caches, no internet dependency) test-first, then Pass B live per role on a scratch copy of the clinic data (`norsu_clinic` stays untouched), full suite, records, commit and push `changes_v2`.

- **END:** pushed on the owner's standing go-ahead.
  - **Commits:** `7a74078` plan row 5.2 (code, tests, records), then this `handover:` commit.
  - **Done (plan 5.2, Pass A and Pass B):** the list's date range includes its last day (it stopped at midnight: "Today" showed nobody) and ignores junk; the welcome e-mail is sent after the registration commits and only when the patient has an address; "resend verification" works only for patient / doctor accounts (any id was accepted) and refuses a missing address with a 422; the history page's compare table loads medicines and the nurse once (a query per consultation before); the create and edit forms validate the date of birth, gender and the 11 pick-list ids (they reached the database unchecked); a refused or failed patient save logs field names, not the patient's data; the dead dashboard cache keys are no longer cleared by the registration.
  - **Verified:** `LINK_CRAWL=1` full suite **585 tests / 6,205 assertions**, all passing (13 min 56 sec); crawl 1,621 requests, no problems; no route added. 14 new tests (`PatientsPassATest`); 18 mutations of production code each fail a test. Pass B on a scratch copy of the real clinic data with nurse, doctor and admin: registration through the real form (all ids saved), edit of a legacy patient, the list's "Today" range, archive and restore through the real buttons, the history page with the compare table, the doctor's reduced buttons, and 403 on list / create / edit / history without Manage Patients. Scratch schema dropped, `norsu_clinic` untouched.
  - **Where to continue:** row 5.3 Queue.
  - **No new Artisan commands;** [docs/commands.md](docs/commands.md) unchanged.
  - **Left open (recorded in the plan block, for later):** repositories rethrow raw exception messages (database errors quote SQL and values) to the browser: one fix in the exception handler is a cross-cutting item; the route `delete-old-patients` has no page and is a Phase 6.6 dead-code candidate; the reset-password endpoint returns raw exception text and patients can no longer sign in.

### 2026-10-16 08:30 +08:00 — Claude Code — START → END (pushed 2026-10-16)
- **Branch / from:** `changes_v2` @ `eed070f` (item 5.23 and its handover are pushed; working tree clean except the build-generated `public/messages.js`).
- **Work:** Phase 5 row 5.1 Dashboard (admin / staff / doctor) of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md), on the owner's go (2026-10-16: "continue to the next plan item, 5.1 Dashboard"): Pass A (authorisation, stale caches and figures that disagree with the module lists, the Day / Week / Month tabs, N+1, no internet) test-first, then Pass B live per role on a scratch copy of the clinic data (`norsu_clinic` stays untouched), full suite, records, commit and push `changes_v2`.

- **END:** pushed on the owner's standing go-ahead.
  - **Commits:** `e33cfc7` plan row 5.1 (code, tests, records), then this `handover:` commit.
  - **Done (plan 5.1, Pass A and Pass B):** the figures and today's list are read fresh and count patient records like the Patients module (they were cached 60 s and counted accounts); Day / Week / Month tabs: month of this year (was any year), the week's last minutes included, the 50 newest with the real total (was cut at 5), clinic-clock times (were UTC, 8 hours early); the **staff** dashboard's tabs work (they called the admin-only route and answered 403) through the new route `staff.patientData.dashboard`, with each row's link decided by the server (none for a staff member without Manage Patients); a patient whose account was archived on its own no longer breaks the page; the first pane is the Day pane; the dead repository loads (`getData`, `getStaffData`, `getDoctorData`) are gone.
  - **Verified:** `LINK_CRAWL=1` full suite **571 tests / 6,066 assertions**, all passing (16 min 13 sec); crawl 1,621 requests, no problems; route list 478 → 479. 15 new tests (`DashboardTest`); 22 mutations of production code each fail a test (one first survived and got its own test). Pass B on a scratch copy of the real clinic data with nurse, doctor and admin: the figures equal an independent SQL oracle, the staff tabs answer, the no-Manage-Patients variant shows plain names, same-origin requests only. Assets rebuilt with `npm run dev` (build output, not in git). Scratch schema dropped, `norsu_clinic` untouched.
  - **Where to continue:** row 5.2 Patients.
  - **No new Artisan commands;** [docs/commands.md](docs/commands.md) unchanged.
  - **Left open (owner question, STATUS §6):** the dashboards stay open to every staff account and doctor (the plan's rule), so a staff account without Manage Patients still sees today's registered patients (name, e-mail, no link).

### 2026-10-16 00:15 +08:00 — Claude Code — START → END (pushed 2026-10-16; takeover of Codex's open 5.23 START below)
- **Branch / from:** `changes_v2` @ `faf2d0c` plus the **uncommitted item 5.23 working tree** (the code and tests were written by Claude Code and staged before a usage-limit pause; Codex then added the verification notes to the plan and its START). Taken over on the **owner's explicit instruction** (2026-10-16: "i let OpenAI Codex takeover the last, now i want to continue it, i want to double check it again and again and make sure to align with the plan and Handover"). Codex's START below is preserved unchanged.
- **Work:** re-check item 5.23 ("Others" names on consultations and in the Accomplishment Report) against the plan's acceptance rules (plan 5.23 items 1-4) and against Codex's recorded verification, fix any gap test-first, rerun the full suite with the link crawl, mutation checks and a scratch-copy live check, update the records, then commit and push `changes_v2`.
- **END:** pushed on the owner's standing go-ahead. Codex's START below is **closed by this END**: its recorded checks (plan 5.23, "Done and verified") were re-run and kept; nothing of Codex's was discarded. Codex's untracked browser-snapshot folder `.playwright-mcp/` (page snapshots of the scratch copy of the clinic data) was deleted from the repo folder, never committed.
  - **Commits:** `a0fea49` plan item 5.23 (code, tests, records), then this `handover:` commit.
  - **Done (plan 5.23):** services "Others" lines get a name box (`consultation_services.other_text`, migration `2026_10_15_110000_add_other_text_to_consultation_services`, additive, **not applied to `norsu_clinic`**); a ticked "Others" line (illness or service) must carry a name (server rule on create and edit, `required` box while ticked, typing ticks the line, a hidden empty box is revealed instead of blocking the save silently); names are saved tidied and cleared when un-ticked; the Accomplishment Report (monthly and yearly; screen, PDF, Excel, CSV) lists the typed names as sub-rows under each "Others" row with their own counts, merged when they differ only by case, repeated spaces or end punctuation (owner decisions 2026-10-15); section and grand totals unchanged. New `App\Support\OtherName`.
  - **Verified:** `LINK_CRAWL=1` full suite **556 tests / 5,985 assertions**, all passing (39 min 16 sec: busy machine); link crawl 1,614 requests, no problems. 16 new tests (`OtherNamesSaveTest`, `OtherNamesReportTest`); 34 mutations of production code, 32 fail a test, 2 equivalent (the filters inside the names queries; the row selection already applies them). Rehearsal on a fresh scratch copy of the dump: 24 migrations (132 rows), `db:integrity --compare-fresh` 0 errors / 3 existing warnings. Live check on the scratch copy with doctor, nurse and admin (boxes, tick / require / reveal, saved through the real form, edit page, report screen, CSV / Excel / PDF with the same names and totals); scratch schema dropped, `norsu_clinic` untouched.
  - **Where to continue:** the module-by-module passes of Phase 5 (5.1 Dashboard onward; 5.4 and 5.10 still need their record-level authorisation and Pass B passes).
  - **No new Artisan commands;** [docs/commands.md](docs/commands.md) unchanged.
  - **Left open:** `norsu_clinic` still needs `php artisan migrate --force` (4 pending: the two Phase 4 data migrations, the note widening, the service text column) when the owner decides; the Accomplishment Report PDF took ~35 s on the busy machine (DomPDF), inside the clinic PC's 120 s limit.

### 2026-10-15 — OpenAI Codex — START
- **Branch / from:** `changes_v2` @ `faf2d0c`. The owner asked to continue the current work, authorising completion of the inherited open item 5.23 below.
- **Work:** finish the approved Others names on consultations and the Accomplishment Report: review, remaining mutation checks, scratch migration rehearsal and browser verification, full suite, maintained records, then commit and push. Keep `norsu_clinic` untouched; preserve the printed form codes.

### 2026-10-15 20:10 +08:00 — Claude Code — START (closed 2026-10-16: no module-pass work was done under it; item 5.23 was finished instead, see the entry above)
- **Branch / from:** `changes_v2` @ the `handover:` commit that follows `2462ee8` (checkpoint A is pushed; working tree clean except the build-generated `public/messages.js`).
- **Work:** Phase 5, the module-by-module passes of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md) (rows 5.1 onward: record-level authorisation, then Pass B live per role on a scratch copy of the clinic data; `norsu_clinic` stays untouched), and plan item 5.23 (the "Others" request) inside 5.4 / 5.10 once the owner confirms the two proposed rules.

### 2026-10-15 14:55 +08:00 — Claude Code — START → END (pushed 2026-10-15)
- **Branch / from:** `changes_v2` @ `3350155` (Phase 4 and its handover are pushed; working tree clean except the build-generated
  `public/messages.js`).
- **Work:** Phase 5 of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md), on the owner's go (2026-10-15: "continue to
  phase 5"): every module twice, Pass A (code: authorisation, transactions, validation vs columns, unique last sort keys,
  exports, N+1, no internet dependency) and Pass B (live, each role, on a scratch copy of the clinic data: the owner asked to
  leave `norsu_clinic` alone). Every defect gets a failing test, then the fix, then both passes again. Pushed in verified
  checkpoints (full suite + docs), the plan's table 5.1-5.22 records each module.
- **END (checkpoint A):** pushed on the owner's standing go-ahead.
  - **Commits:** `2462ee8` Phase 5 checkpoint A, then this `handover:` commit.
  - **Done:** the cross-cutting Pass A sweeps (plan "Phase 5 — progress 2026-10-15"): unique last sort key on every list; every form / consultation / lab rule fits its database column (additive migration `2026_10_15_100000_widen_medicine_bill_note`, **not applied to `norsu_clinic`**); role / settings / Front CMS saves are transactions; the dispense-record PDF survives a missing note; stale pick-list / expiry-badge / PDF-header caches dropped on change; N+1 removed from the doctor, staff and medicine lists; `tests/TestCase.php` resets `set_time_limit` after each test (the consultation PDF export sets 120 s for the whole phpunit process and killed the run at test ~427). 33 new tests; 30 mutations (29 fail a test, 1 equivalent).
  - **Verified:** `LINK_CRAWL=1` full suite **540 tests / 4,857 assertions**, all passing (13 min 35 sec); link crawl 1,613 requests, no problems. `norsu_clinic` untouched (still not migrated for Phase 4 or for the note migration).
  - **Owner request recorded, not started:** plan item **5.23** ("Others" lines record the typed name: services get a text box; the Accomplishment Report lists the typed illness / service names as sub-rows under "Others" with their own counts). Two points are my proposals to confirm with the owner before coding (required when ticked; spelling-insensitive grouping).
  - **Where to continue:** the module-by-module passes (5.1 Dashboard onward, Pass A record-level authorisation and Pass B live per role); do 5.23 inside 5.4 / 5.10.
  - **No new Artisan commands;** [docs/commands.md](docs/commands.md) unchanged.


### 2026-10-12 08:50 +08:00 — Claude Code — START → END (pushed 2026-10-15)
- **Branch / from:** `changes_v2` @ `254c812` (Phases 0-3 and the Phase 2 re-check are pushed; working tree clean except the
  build-generated `public/messages.js`).
- **Work:** Phase 4 of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md), on the owner's go (2026-10-12: "continue to
  phase 4"): 4.1 project name "Medical University Clinic" (clinic name only), 4.2 College of Law CL → COL, 4.3 Guest column
  always shown, 4.4 yearly Accomplishment Report, 4.5 yearly Medicine Inventory report (screen = PDF = Excel = CSV). Test-first,
  rehearsal of the migrations on the real clinic dump, live browser check, then commit and push `changes_v2`.
- **END:** pushed on 2026-10-15 on the owner's standing go-ahead. The owner answered the two open questions on 2026-10-15 (the printed form codes stay; leave `norsu_clinic` alone).
  - **Commits:** `125de63` Phase 4, then this `handover:` commit.
  - **Done (plan 4.1-4.5):**
    - **4.1 name:** "Medical University Clinic" in `.env.example`, the seeders, the language file, the registration e-mail, the landing page and the recode guide's title, and by a data migration for the `clinic_name` setting and the default slider text (only a value that still is the old default; the settings cache is cleared; reversible); the link crawl now fails on any page that still says "NORSU Clinic". Kept: the university name, logo, e-mail address, `norsu_clinic` database, backup prefix, folders and (owner decision) the printed form codes "NORSU-CLINIC-FORM-01 / 02".
    - **4.2 COL:** a migration renames the college and every saved copy of its name (consultations, lab requests, activity log); the seeder and the report header follow.
    - **4.3 Guest:** the Accomplishment Report always has a "Guest" column (Unspecified only when used), in the screen, PDF, Excel and CSV.
    - **4.4 yearly Accomplishment Report:** a "Report" choice on the tab: the same rows, rules and filters with the columns Q1-Q4 and YEAR TOTAL (by consultation date); one builder for screen, PDF (8.5×13), Excel and CSV.
    - **4.5 yearly Medicine Inventory report:** a "Report" choice on Medicine Inventory: per medicine, left from last year, additional, remaining at the end of each quarter in its own unit, read from the stock ledger, expired stock noted, an add-up check per medicine (and against the shelf for the current year); screen, PDF, Excel and CSV from one builder. Three new routes (`activity-logs.inventory-yearly`, `staff.` and `doctors.`; route list 478).
  - **Found and fixed while doing it (test-first):** the mutation checks found 7 weak spots in my first tests (an empty-row guard of the yearly inventory, its comparison with the shelf, the stock-list export / search / status filter that must not show in the yearly view, the expiry-day boundary and the missing year in the download links): each got a test of its own and now fails its mutation. Earlier, the first ledger scenario had only a positive adjustment (a sign mutation would have survived), so a negative adjustment and a "two wrong lines that cancel out" test were added.
  - **Verified:** `LINK_CRAWL=1` full suite: **507 tests / 4,717 assertions**, all passing, none skipped (43 min 42 sec: the machine was busy). The 15-actor link crawl inside it: 1,613 requests / 1,533 pages, 0 broken links, crashes, lazy-table failures, capped crawls or pages that still show the old name. 48 mutations of production code each fail a test. Rehearsal on a scratch copy of the real clinic dump under a MyISAM and an InnoDB default (22 migrations, 64 InnoDB tables, 47 of 47 foreign keys, `db:integrity --compare-fresh` 0 errors / 3 existing warnings, a second `migrate` does nothing; afterwards `clinic_name` = "Medical University Clinic" and college #10 = "College of Law (COL)"). Live browser check on that copy with admin, doctor and nurse sessions: both yearly reports on screen and as PDF, Excel and CSV (the yearly inventory has 55 medicines, left 0, additional 37,957, year end 37,369); a read-only oracle with independent SQL agrees (the yearly Accomplishment Report equals the by-college report for the same dates; additional / used / disposed equal the ledger sums; every medicine adds up; the year end equals the shelf); no console error, same-origin requests only.
  - **No new Artisan commands;** [docs/commands.md](docs/commands.md) checked and unchanged.
  - **Where to continue:** Phase 5 (every module, two passes each), only when the owner says go.
  - **Left open:** 
    - **The working database `norsu_clinic` was left alone at the owner's request (2026-10-15):** the two Phase 4 data migrations (`2026_10_12_090000_rename_college_of_law_cl_to_col`, `2026_10_12_091000_rename_clinic_to_medical_university_clinic`) are not applied there, so it still says "Norsu Clinic" and "College of Law (CL)" until `php artisan migrate` is run (they were rehearsed on a scratch copy of the dump, then the scratch schema was dropped).
    - **Deployment:** set `APP_NAME="Medical University Clinic"` in the clinic server's own `.env` by hand (not in git; it titles the guest pages and also names the session cookie, so everybody signs in once more).
    - The printed form codes "NORSU-CLINIC-FORM-01 / 02" stay as they are (owner decision, 2026-10-15).
    - **Owner question (not blocking):** the hard-coded letterhead address line "University Medical Clinic, CNPAHS Bldg., Kagawasan Ave., Dumaguete City" (10 views: consultation page and PDF, certificate and excuse-slip prints / PDFs / forms, the registration e-mail fallback) was left as it is: it is the address block, not "NORSU Clinic", and its word order differs from the new name (STATUS §6).
    - Observed, not changed: the yearly inventory PDF takes about 10 s for 55 medicines (DomPDF; about 38 s for 220), inside the clinic PC's 120 s limit.
    - The earlier open items are unchanged (Dispense History speed at volume, manual dispense records have no "recorded by", other lists sorted by a repeating value are unchecked: plan Phase 5 Pass A item 4; the raw activity log is readable by every staff account and doctor; dead `nurse` fallbacks).

### 2026-10-11 00:45 +08:00 — Claude Code — START → END (pushed 2026-10-11)
- **Branch / from:** `changes_v2` @ `5b7f3f2` (Phases 0-3 pushed; working tree clean except the build-generated
  `public/messages.js`).
- **Work:** re-check Phase 2 (Dispense History shows consultation medicines, plan 2.1-2.4; started by Claude Code, finished
  by Codex as `c993859`) on the owner's request (2026-10-11: "go back to phase 2 ... double check ... follow the audit plan
  and Handover"). Re-read the view / model / table / detail page / routes / tests against the plan and against the Phase 3
  changes, compare the view with the base tables on the clinic data, mutation-check the tests, live browser check, fix any
  gap test-first, update the plan / STATUS records, then commit and push `changes_v2`.
- **END:** pushed on 2026-10-11 on the owner's standing go-ahead.
  - **Commits:** `ffda2df` Phase 2 re-check, then this `handover:` commit.
  - **Checked and fine:**
    - the view against the base tables, row by row, with an oracle written independently of the view: on the real clinic data
      (3 consultation rows, 9 / 14 / 15 = 38 units, equal to the Stock-out view's net 38; the one pending prescription
      correctly left out) and on a scratch data set with all three sources, an archived patient, a deleted consultation
      and a pending and a cancelled prescription;
    - live (scratch schema, admin / doctor / Staff (Nurse) sessions side by side): source filter, search, every sort,
      paging, the three detail pages and their 404s, deleting a manual record from the history (stock +6, ledger
      adjustment), all links answer 200, role-permission variants (documents / patients / doctors / medicines removed),
      same-origin requests only, no horizontal scroll at phone width;
    - the real prescription-dispense flow puts exactly one row into the history, with the units handed out.
  - **Found and fixed (test-first):**
    - **rows repeated and went missing between pages** when many rows shared a date (every manual dispense record of a day
      is saved at 00:00:00; reproduced: 60 same-day records listed 48 different rows over 6 pages). The table now sorts by
      the record number and the row id last; equal dates list the newest record first;
    - the patient's own history page (Patients › History) listed **pending and cancelled prescriptions as dispensed** and
      lacked the consultation medicines; it now reads the same view as the tab;
    - a test that never tested "newest first" (`created_at` is not fillable, `update()` was silently ignored);
    - the source filter had no accessible name; stale designation comments in two column views.
  - **Verified:** `LINK_CRAWL=1` full suite: 474 tests / 4,421 assertions, all passing, none skipped (13 min 22 sec). The 15-actor link crawl inside it: 1,612 requests / 1,532 pages, 0 broken links, crashes, lazy-table failures or capped crawls. `DispenseHistoryTest` 15 → 19 tests (149 assertions). 13 mutations of production code
    each fail a test. Plan and STATUS updated; plan §8 has the new volume question.
  - **No new Artisan commands.**
  - **Where to continue:** Phase 4 (name "Medical University Clinic", College of Law CL → COL, Guest column, yearly
    Accomplishment Report, yearly Medicine Inventory report), when the owner says go.
  - **Left open:**
    - Dispense History speed at volume: about 35 ms per 1,000 rows for every click (0.35 s at 10,000 rows, 1.7 s at
      50,000); plan §8 lists the options. Nothing to do for this clinic's volume yet;
    - manual dispense records have no "recorded by" (the table has no such column; the cell shows NA, as before);
    - other lists sorted by a repeating value (Stock-out, by date or status) were not checked and probably need the same
      unique last sort key: plan Phase 5, Pass A item 4, row 5.7;
    - the earlier open items (raw activity log readable by every staff account and doctor; dead `nurse` fallbacks; the
      PHP dev server Codex started on `0.0.0.0:8000`) are unchanged.

### 2026-10-09 17:35 +08:00 — Claude Code — START → END (pushed 2026-10-10; takeover of Codex's open Phase 3 START)
- **Branch / from:** `changes_v2` @ `144c83a` plus the **uncommitted Phase 3 working tree** that Codex left (about 65 files:
  `ModuleAccess` + `canUseModule`, migration `2026_10_09_170000_unify_staff_nurse_access`, Roles screen, staff form,
  routes, menus, tests). Taken over on the **owner's explicit approval** (2026-10-09: "another AI takeover … you take
  over phase 3, double check it again and again and verify it, align it on the plan and Handover"). Codex's START below
  is preserved unchanged.
- **Work:** Phase 3 of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md) (3.1–3.7): review every inherited
  change against the plan, fix the gaps test-first, verify (full suite, link crawl, permission oracle matrix, mutation
  checks, rehearsal on the real clinic dump, live browser with admin / doctor / nurse side by side), update the records,
  commit and push `changes_v2`.
- **END:** pushed on 2026-10-10 on the owner's standing go-ahead.
  - **Commits:** `b945e64` Phase 3, then this `handover:` commit.
  - **Done (plan 3.1–3.7):**
    - **Staff = Nurse:** one role "Staff (Nurse)"; the `nurse` role is retired (accounts moved by a migration that creates
      nothing on a fresh install, removes direct per-user permissions and fills two empty permission labels found in the
      clinic data); the staff form, list, show page, requests and "Prepared by" no longer use Role Designation, Assigned
      Station or Shift Schedule (the hidden columns stay).
    - **Role permissions apply everywhere:** `canUseModule()` + `App\Support\ModuleAccess` replace the 63 designation /
      station checks; the `staff.module` middleware is gone; every staff and doctor route carries the permission of its
      module (only the dashboards and Reports / Notifications are open on purpose, enforced by `ModuleAccessMapTest`);
      route list: 475 routes, none added or removed, 136 changed middleware.
    - **Roles screen:** only the five permissions that apply are offered, Clinic Admin and Patient are read-only (request
      and repository both refuse), a role can be saved with none, explanations no longer slide away after 5 s, the
      select-all box shows the right state on load (the dead `roles/create-edit.js` that reset it is removed).
  - **Found and fixed while reviewing Codex's tree** (13 of 453 tests were red at the start): the migration created a
    `staff` role on a fresh database (9 `ReportGenerationTest` errors); the staff save dropped the fixed country code; three
    tests still encoded the designation policy; the doctor menu crashed for a doctor without a doctor record; two
    permissions had empty labels at the clinic; the designation-pair loops in `RoleAwareLinksTest` no longer proved the
    "hidden" side; the permission-variant link crawl found "Record form" / "View Form" buttons on the doctor queue that
    answered 403 without the documents permission; the Reports global search listed patients, prescriptions and medicines
    for roles that lost those modules.
  - **Verified:** `LINK_CRAWL=1` full suite: 470 tests / 4,384 assertions, all passing, none skipped (16 min 53 sec). The 15-actor link crawl inside it (guest, admin, Doctor ×6, Staff (Nurse) ×7): 1,611 requests / 1,531 pages, 0 broken links, crashes, lazy-table failures or capped crawls. Permission oracle matrix (scratch harness): 230 staff / doctor / API routes × 16 role-permission variants = 3,680 requests, and a same-user run of 2,760: 0 server errors, no 200 for a guest / patient / deactivated doctor, all 223 cells whose permission was missing answered 403 (8 stock-in cells 404 only for want of a fixture row; 403 with one), no status change that the route's own permission does not explain. Rehearsal R4 on the real clinic dump under a MyISAM and an InnoDB default: 20
    migrations, 64 InnoDB tables, 47 of 47 foreign keys, `db:integrity --compare-fresh` 0 errors / 3 existing warnings; the
    only content changes were `roles`, `permissions` (two blank labels) and the Phase 1 zero date. 22 mutations of
    production code each fail a test. Live browser (admin, doctor
    and nurse sessions side by side on the upgraded clinic data): removing Manage Patients from Staff (Nurse) takes Patients
    and Queue off the nurse's next page, `/staff/patients` answers 403 and the Reports global search drops its Patient card;
    re-granting restores all of it; the same for Manage Medicines on the Doctor role; no console errors.
  - **No new Artisan commands;** [docs/commands.md](docs/commands.md) was updated for the direct-permission clearing.
  - **Where to continue:** Phase 4 (name "Medical University Clinic", College of Law CL → COL, Guest column, yearly
    Accomplishment Report, yearly Medicine Inventory report).
  - **Left open:**
    - **Owner question:** the raw activity log ("Notifications & Alerts › System notifications": patient names, contact
      numbers, complaints) is now open to every staff account and doctor, as the plan's rule for Reports and Notifications
      says; before, only some staff designations could read it (R3-H6). One line limits it again (STATUS §6).
    - Dead code for Phase 6: `hasRole('nurse')` / `isRole('nurse')` fallbacks, the `StaffDesignation` / `ClinicStation`
      seeders and models (only the legacy test helper `makeStaff()` still uses them), the `getRouteByRole()` nurse branch.
    - The QA accounts were re-created for the Staff (Nurse) model (`qa.nurse` plain, `qa.legacy` with a hidden profile);
      logins are in the git-ignored `storage/app/qa-accounts.json`. The rehearsal tools live outside the repo
      (`C:\Users\Franc\.claude\projects\C--Projects-NORSUCLINIC\tools\rehearsal\`, now with `RoleMatrixP3Test.php` and
      `analyze-p3-matrix.py`).
    - A PHP dev server started by Codex is still listening on `0.0.0.0:8000` (LAN) with the experimental clinic data:
      stop it when not needed.

### 2026-10-09 16:41 +08:00 — OpenAI Codex — START
- **Branch / from:** `changes_v2` @ `144c83a` (Phase 2 pushed; working tree clean).
- **Work:** Phase 3 of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md): Staff = Nurse, remove the
  designation / station / shift UI and access layer, make role permissions govern menus / routes / buttons,
  correct the Roles screen and permission cache updates, replace policy tests and the link-crawl actors, then
  verify the full suite, mutations and live browser before committing and pushing `changes_v2`.

### 2026-10-09 16:05 +08:00 — OpenAI Codex — START → END (pushed 2026-10-09)
- **Branch / from:** `changes_v2` @ `4957fd9`.
- **Work:** take over and finish Claude Code's open Phase 2 with the owner's explicit approval (2026-10-09,
  "Take over and finish Phase 2"): review the existing Dispense History changes, fix any gaps test-first, verify the
  full suite, mutation checks, link crawl and live browser, update the maintained records, then commit and push
  `changes_v2`. Next is Phase 3 of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md).
- **END:** work committed and pushed as `c993859`, followed by this `handover:` commit, on the owner's standing go-ahead.
  Claude Code's original START is preserved; its Phase 2 work is completed under this approved takeover.
  - **Done:** the read-only history view / model, Source column and filter, word search and sorting, per-source actions,
    consultation medicine detail page and three role-specific routes. Review fixes: exclude non-consultation documents,
    retain archived patients' names without dead patient links, and use the correct `consultations` access key for the
    "View Consultation" button (staff and legacy nurse roles). Plan and STATUS updated; no new Artisan commands,
    `docs/commands.md` checked and unchanged.
  - **Verified:** final full suite with `LINK_CRAWL=1`: **444 tests / 4,304 assertions**, all passing, none skipped,
    9 min 20 sec / 190 MB. Phase 2 regression tests: 15 / 112 assertions. Five mutations each fail as expected
    (document type, archived patient link, deleted consultation, prescription status, consultation access key).
    The 16-user crawl made 1,143 requests / 1,093 pages, with no broken links, crashes, lazy failures or capped crawls.
    Route baseline 472 → 475: exactly the three new consultation detail routes, with the existing role / permission
    middleware. Inline review, PHP syntax and `git diff --check` passed.
  - **Live:** Chrome over `192.168.2.13:8000` and `172.22.208.1:8000`: admin / doctor / clinic head pass filtering,
    search, tab switching, all three detail pages and the consultation link; no JavaScript errors, failed requests
    or internet dependencies. QA nurse has no dispensing module under the current designation policy: menu hidden,
    list / details return the expected 403 (Phase 3 changes this). Consultations 3 / 6 / 7 show 9 / 14 / 15 units;
    refreshing the live view changed no stock or ledger rows. `db:integrity --compare-fresh`: 0 errors / 3 existing
    warnings, structure identical to a fresh install; 127 migrations, all 64 tables InnoDB, 47 foreign keys.
  - **Where to continue:** Phase 3 (Manage User roles apply; Staff = Nurse). Phases 3–10 remain pending.
  - **Left open:** the known duplicate-medicine / phone warnings, the designation access restrictions scheduled for
    Phase 3, and the remaining decisions / documentation reconciliation in the plan. No unfinished Phase 2 work.

### 2026-10-09 13:10 +08:00 — Claude Code — START
- **Branch / from:** `changes_v2` @ `4957fd9` (everything of Phases 0 and 1 is pushed)
- **Work:** Phase 2 of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md): Dispense History shows consultation
  medicines (database view `dispense_history_view`, read-only model, Source column and filter, read-only detail page,
  tests, live check on the clinic data). New commands, if any, go into [docs/commands.md](docs/commands.md).

### 2026-10-09 07:28 +08:00 — Claude Code — START → END (pushed 2026-10-09)
- **Branch / from:** `changes_v2` @ `5155bf5`
- **Work:** Phases 0 and 1 of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md) (the owner said go for both):
  - Phase 0: backup, the test run creates its own schema, baselines, rehearsal runner, rehearsal R0 (the clinic dump
    upgrades "fine" on a MyISAM default but leaves 0 foreign keys, and fails with error 1824 on an InnoDB default);
  - Phase 1: clinic-data upgrade (InnoDB conversion, zero dates, foreign keys, re-runnable illness migration,
    `db:integrity`, rehearsals R1–R3, QA accounts), plus the owner's decision on the 4 orphan stock batches.
  The working database `norsu_clinic` now holds the upgraded clinic data (experimental data, backed up first).
- **END:** pushed on the owner's standing go-ahead.
  - **Commits:**
    - `7c6b5c5` Phase 0;
    - `d00cca3` Phase 1 (config `DB_ENGINE`, migrations `2026_09_30_110000`, `2026_09_30_121500`, `2026_10_08_120000`,
      `LegacySchemaUpgrade`, `SchemaInspector`, `ForeignKeyCatalog`, `SchemaParity`, `db:integrity`,
      `db:restore-foreign-keys`, `docs/commands.md`, 47 tests);
    - `c498798` Phase 1 follow-up (`inventory:recover-orphan-batches`, the `--compare-fresh` safety guard, push rule);
    - then the `handover:` commit with this file.
  - **New commands** (all described in [docs/commands.md](docs/commands.md): what they do, whether they change data, where
    and when to run them): `db:integrity [--compare-fresh]`, `db:restore-foreign-keys`,
    `inventory:recover-orphan-batches [--apply]`.
  - **Verified:** full suite 429 tests / 4,190 assertions / 1 skipped, green (6.5 min); route list identical to the Phase 0
    snapshot (472 routes); link crawl and route × role matrix baselines from Phase 0 (Phase 1 changed no route or page);
    rehearsal R3 on the real dump under a MyISAM and an InnoDB default: 18 migrations, all tables InnoDB, 47 of 47
    foreign keys after the recovery, structure identical to a fresh install, `db:integrity` 0 errors / 3 warnings,
    second `migrate` does nothing; inline code review before the push (one fix, the `--compare-fresh` guard).
  - **Where to continue:** Phase 2 (Dispense History shows consultation medicines).
  - **Left open:**
    - warnings on the clinic data: duplicate medicines #41/#42 and #50/#55 (merge tool, Phase 8.2), 8 phone numbers for
      `phone:normalize`;
    - rehearsal tools and the QA-account script live outside the repo
      (`C:\Users\Franc\.claude\projects\C--Projects-NORSUCLINIC\tools\rehearsal\`); QA logins are in the git-ignored
      `storage/app/qa-accounts.json`;
    - `docs/STATUS.md` §8 still has the stale "not pushed" lines (plan Phase 10.2); `public/messages.js` question unchanged.

### 2026-10-08 18:30 +08:00 — Claude Code — START → END (pushed 2026-10-08)
- **Branch / from:** `changes_v2` @ `327aabc`
- **Work:** planning and AI tooling only. It covers:
  - re-audited plan [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md) (Phases 0–10);
  - this handover file;
  - shared AI rules in `AGENTS.md`, imported by `CLAUDE.md`;
  - 16 skills in `.claude/skills/` and `.agents/skills/` (copied from the owner's other project; Laravel best-practices,
    Livewire and permission skills adapted to Laravel 10 / Livewire 3 / spatie 5.11; 6 wrong-stack skills left out);
  - `.claude/launch.json` (owner's copy).
  No application code or database was changed.
- **END:** pushed on the owner's go-ahead.
  - **Commits:**
    - `362841d` docs: audit and work plan;
    - `9f4ca7f` shared AI rules (`AGENTS.md`, `CLAUDE.md`) + 16 skills;
    - then the `handover:` commit with this file.
  - **Verified:** documentation / tooling only (no PHP, JS or database change), so the test suite was not run. Checks done:
    - files scanned for secrets;
    - the two skill folders compared (identical except the 13 generic skills' doc links);
    - every claim in the plan re-checked (plan §0).
  - **Where to continue:** Phase 0 of the plan (backup, test schema created by the test run, baselines, rehearsal of the
    clinic dump on `norsu_clinic`), only when the owner says go.
  - **Left open:**
    - `docs/STATUS.md` still says the 10-08 commits are not pushed (fixed in plan Phase 10);
    - `public/messages.js` question unchanged.

---

## 4. Shared ground rules

All shared rules (where the system runs, stack versions, skills, how to work, data and secrets, domain traps) are in
[AGENTS.md](AGENTS.md). Read it; do not copy its rules here.

New artisan commands are logged in [docs/commands.md](docs/commands.md) (what each does, whether it changes data, where
and when to run it). When your END entry lists what was done, name any command you added and make sure that file has it.
