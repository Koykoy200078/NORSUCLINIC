# Medical University Clinic — instructions for AI assistants

This file is read by **OpenAI Codex** (and imported by `CLAUDE.md` for **Claude Code**). Both assistants work on this
repository and follow the same rules. Repository / folder name: `NORSUCLINIC`; display name: **Medical University Clinic**.

## 1. Sync between the two assistants — read first

1. `git fetch`, then `git log --oneline -15 origin/changes_v2`.
2. Read [Handover.md](Handover.md) and follow its protocol:
   - add a **START** entry when you begin;
   - add the **END** part only when the work is committed and pushed;
   - write nothing in between;
   - never continue or overwrite the other assistant's open (unpushed) work without asking the owner.
3. The work order is [docs/audit-plan-2026-10-08.md](docs/audit-plan-2026-10-08.md); the project record is
   [docs/STATUS.md](docs/STATUS.md).
4. **Never push without the owner's go-ahead.**

## 2. What this system is and where it runs

- A university clinic records system:
  - patients, queue, consultations (`document_issuances`), prescriptions, medicine inventory (FEFO batches + stock ledger), dispensing;
  - lab requests, medical certificates / excuse slips;
  - Report Generation (Accomplishment Report, inventory), Settings.
- **Production = one clinic server PC** (WAMP: Apache or `php artisan serve`, MySQL, scheduled backups, later Reverb).
  Staff, nurses and doctors use it from **their own devices on the same local network** through a browser.
  - Every link and asset must work from any LAN address (never only `localhost`).
  - The database stays on the server PC.
- **No page may need the internet to load**: no CDN, no third-party script / style / font / image / API. Every library is
  packaged locally (npm + Laravel Mix into `public/`, or `public/assets` / `public/vendor`). The LAN itself is fine.
- The development laptop is separate: `norsu_clinic` there is experimental data (the clinic dump is imported into it).

## 3. Stack (respect these exact versions)

| | |
|---|---|
| PHP | 8.2 at the clinic, 8.3 on the dev laptop (`composer.json` platform pin 8.1 until Reverb raises it) |
| Laravel | **10.50** (no Laravel 11+ APIs: `casts()`, `once()`, `defer()`, `Context`, `bootstrap/app.php` …) |
| Livewire | **3.8** (class-based components; no Livewire 4 features) |
| Tables | rappasoft/laravel-livewire-tables **3.2** (do not upgrade: 3.8 breaks the customised views) |
| Permissions | spatie/laravel-permission **5.11** (middleware namespace `Spatie\Permission\Middlewares`, plural) |
| Tests | **PHPUnit 9.6** (not Pest) |
| Frontend | Bootstrap 5 (Metronic theme), jQuery, Select2, Laravel **Mix** (not Vite) |
| PDF / Excel | barryvdh/laravel-dompdf 3, maatwebsite/excel 3.1 |
| Database | MySQL (strict mode, InnoDB) |

Look up docs for **these** versions (Laravel 10.x, Livewire 3.x), e.g. through Context7 (`/laravel/docs` branch 10.x,
`/livewire/livewire` branch 3.x).

## 4. Skills

Domain skills live in **`.agents/skills/`** (Codex) and **`.claude/skills/`** (Claude Code). The two folders hold the same
16 skills; **when you change a skill, change both copies**. Activate the relevant skill whenever you work in its area.

| Skill | Use for |
|---|---|
| `laravel-best-practices` | any Laravel PHP code (adapted to Laravel 10 and this project's rules) |
| `livewire-development` | Livewire components and rappasoft tables (adapted to Livewire 3) |
| `laravel-permission-development` | roles, permissions, menus / routes / buttons access (adapted to spatie 5.11 + this project's access model) |
| `laravel-specialist`, `php-pro` | general Laravel / PHP work (their Laravel 11+/Pest examples do not apply — follow §3) |
| `test-master` | test design (write PHPUnit, not Pest) |
| `debugging-wizard` | errors, stack traces, root-cause work |
| `code-reviewer` | reviewing diffs before a push |
| `security-reviewer`, `secure-code-guardian` | security audits and secure implementation |
| `database-optimizer`, `sql-pro` | MySQL queries, indexes, migrations (ignore PostgreSQL-only parts) |
| `spec-miner` | understanding undocumented legacy code |
| `api-designer` | only if an API is added (the app has almost none) |
| `playwright-expert` | browser end-to-end checks |
| `the-fool` | challenging a plan or decision before committing to it |

**Deliberately not included** (wrong stack for this project): Pest, Flux UI, Tailwind, Fortify, Laravel Cloud deploy,
PostgreSQL. Copies are in the owner's other project if the stack ever changes.

## 5. How to work

- **Conventions first:** follow existing code conventions; check sibling files for structure, naming and comment
  density; reuse existing helpers and components before writing new ones; descriptive names.
- **Approval needed for:** new base folders, new or changed dependencies (composer / npm), dropping data, pushing.
- **Test-first:** every change is covered by a test. Write the failing test, see it fail, fix, see it pass.
  - Run the **full suite** (`vendor/bin/phpunit`, about 5–12 min) before calling work done. Never run two phpunit
    processes at once (they share the test schema).
  - Mutation-check a regression test: revert the fix and confirm the test fails.
  - UI changes also get a real browser check (ideally from a second LAN device / address).
  - Opt-in link crawl: `LINK_CRAWL=1 vendor/bin/phpunit --filter LinkCrawlTest`.
- **Ask the owner** when a requirement is unclear instead of guessing.
- **Frontend:** after JS / CSS changes run `npm run dev` (or `npm run prod`); built assets are not in git (except
  `public/messages.js`, see STATUS §8).
- **PHP style:** curly braces on every control structure, type hints on new code, StyleCI (`.styleci.yml`) style.
- **Docs:** create documentation only when asked. The maintained records are `Handover.md`, `docs/STATUS.md`, the
  audit plan and [docs/commands.md](docs/commands.md), the **log of new artisan commands** (what each does, whether it
  changes data, where and when to run it): add or update a row there in the same commit that adds or changes a command.
- **Replies:** concise; say what was verified and how.

## 6. Data and secrets

- Databases on the dev laptop:
  - `norsu_clinic` is the only working database (experimental data);
  - tests use their own throwaway schema `norsu_clinic_test` (`tests/CreatesApplication.php` creates it when it is missing; it
    is wiped on every run) and must **never** run against `norsu_clinic` (the same file enforces it).
- Never print or commit `.env` values or passwords. The clinic dump (`Downloads/10082026.sql`) contains patient data:
  never copy it into the repository or into documents.
- After a migration or a data change on `norsu_clinic`, run `php artisan db:integrity` (read-only; `--compare-fresh` also
  compares the structure with a freshly built install). QA logins on the clinic data are in the git-ignored
  `storage/app/qa-accounts.json`; never print the passwords.

## 7. Domain rules that cause bugs when forgotten

- **Stock:** change it only through `App\Services\MedicineInventoryService` (FEFO; never edit `medicine_batches.quantity` or
  `medicines.available_quantity` directly).
- **Class ≠ table:**
  - `DispenseRecord` → `medicine_bills`, `DispenseRecordItem` → `sale_medicines`, `StockIn` → `medicine_availabilities`;
  - `PrescriptionMedicine` → `prescriptions_medicines` (its FK column is `medicine`);
  - `StockOutView` → `used_medicines_view` (a view).
- **Soft deletes:** `users` / `patients` use `archived_at`; consultations / certificates / lab requests use `deleted_at`.
- **Links:** named routes + role-aware helpers (`getRouteByRole()`, `getDashboardURL()`); unprefixed route names are admin
  routes; never hard-code `/admin/...`.
- **Passwords:** `User` does not hash automatically: `Hash::make()`.
- **No prices, amounts, currency or payment** anywhere (free clinic).
- **Decisions already taken** (do not re-ask): `docs/STATUS.md` §6 and `docs/audit-plan-2026-10-08.md` §2.
