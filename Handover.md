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
| Last pushed work | Phase 2 re-check: `ffda2df` (stable Dispense History paging, the patient history card), after Phase 3 `b945e64`, Phase 2 `c993859` (Codex) and the Phases 0 / 1 checkpoint `4957fd9`; then this `handover:` commit (Claude Code, 2026-10-11) |
| Work in progress (not pushed) | none |
| Next step | Phase 4 of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md): the owner's requested changes (name "Medical University Clinic", COL, Guest column, yearly reports) |
| Pushing | the owner's standing go-ahead (2026-10-09): push `changes_v2` after every verified phase, no need to ask |
| Commands added so far | [docs/commands.md](docs/commands.md): `db:integrity`, `db:restore-foreign-keys`, `inventory:recover-orphan-batches` |
| Project status | [docs/STATUS.md](docs/STATUS.md): current checkpoint, tests, clinic schema and next phase updated; §8 retains an older snapshot to reconcile in plan Phase 10.2 |

---

## 3. Log (newest on top)

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
