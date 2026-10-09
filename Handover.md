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
| Last pushed work | Phases 0 and 1 of the plan: `7c6b5c5`, `d00cca3`, `c498798`, then the `handover:` commit of 2026-10-09 (Claude Code) |
| Work in progress (not pushed) | none (a new START entry is opened for Phase 2 right after this push) |
| Next step | Phase 2 of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md): Dispense History shows consultation medicines |
| Pushing | the owner's standing go-ahead (2026-10-09): push `changes_v2` after every verified phase, no need to ask |
| Commands added so far | [docs/commands.md](docs/commands.md): `db:integrity`, `db:restore-foreign-keys`, `inventory:recover-orphan-batches` |
| Project status | [docs/STATUS.md](docs/STATUS.md) (its §8 "10-08 commits not pushed" lines are stale: fixed in plan Phase 10.2) |

---

## 3. Log (newest on top)

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
