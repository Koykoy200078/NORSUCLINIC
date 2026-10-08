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

### When you finish (only after the owner says to push)
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
- Never push without the owner's go-ahead.
- Never rewrite or delete the other AI's entries; add a new entry instead.
- Never paste secrets (`.env` values, passwords) or patient data here.

---

## 2. Current checkpoint

| | |
|---|---|
| Branch | `changes_v2` (base `develop`) |
| Last pushed work | `9f4ca7f` (AI rules + skills), then the `handover:` commit that added this file (2026-10-08, Claude Code) |
| Work in progress (not pushed) | none |
| Next step | Phase 0 of [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md), **only when the owner says go** |
| Project status | [docs/STATUS.md](docs/STATUS.md) (its "10-08 commits not pushed" lines are stale: everything up to `327aabc` is pushed) |

---

## 3. Log (newest on top)

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
