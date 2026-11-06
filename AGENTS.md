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
4. **Pushing:** the owner's standing go-ahead (2026-10-09, "always push it, don't ask again"): after every phase that is
   verified (full suite green, docs updated) commit it and push `changes_v2`, then continue. Never force-push, never push
   another branch, never merge into `develop` without asking.

## 2. What this system is and where it runs

- A university clinic records system:
  - patients, queue, consultations (`document_issuances`), prescriptions, medicine inventory (FEFO batches + stock ledger), dispensing;
  - lab requests, medical certificates / excuse slips;
  - Report Generation (Accomplishment Report, inventory), Settings.
- **Production = one clinic server PC** (WAMP: Apache or `php artisan serve`, MySQL, Reverb for real-time updates). **The database is backed up every day at 6:00 PM Philippine time** by the scheduler that the startup script runs
  in the server's one terminal (`db:backup --force` into `storage/app/backups/scheduled`; the startup script takes a missed one at the next start). Take one
  by hand too (`php artisan db:backup` or Settings > Backups) before every `migrate` or data change, and copy the folder to another disk.
  **Time zone: Philippine time** (Laravel `Asia/Manila`; the database connection is pinned to `+08:00`, an offset on purpose: WAMP's MySQL has no named zones).
  Staff, nurses and doctors use it from **their own devices on the same local network** through a browser.
  - Every link and asset must work from any LAN address (never only `localhost`).
  - The database stays on the server PC.
- **No page may need the internet to load**: no CDN, no third-party script / style / font / image / API. Every library is
  packaged locally (npm + Laravel Mix into `public/`, or `public/assets` / `public/vendor`). The LAN itself is fine.
- The development laptop is separate: `norsu_clinic` there is experimental data (the clinic dump is imported into it).

## 3. Stack (respect these exact versions)

| | |
|---|---|
| PHP | **8.3** (`composer.json` requires `^8.3`, platform pin 8.3.0, because Laravel 13 needs it). The clinic PC still runs 8.2 until the owner installs 8.3 there: `changes_v2` and the Laravel 12 checkpoint (`3c8c434` on `upgrade/laravel-13`) run on 8.2, the Laravel 13 commits do not |
| Laravel | **13.40**, kept in the classic structure (`app/Http/Kernel.php`, `app/Exceptions/Handler.php`, `config/*.php`; no slim `bootstrap/app.php`). Carbon **3**, Symfony **7.4** (Symfony 8 needs PHP 8.4). The forgery check is `App\Http\Middleware\PreventRequestForgery` (Laravel 13's name for `VerifyCsrfToken`). `config/cache.php` sets `serializable_classes` to `false`: cache only arrays and scalars, never a Collection or model. Work is on the branch `upgrade/laravel-13` (see `docs/laravel-13-upgrade-plan.md`) |
| Livewire | **3.8** (class-based components; no Livewire 4 features) |
| Tables | the project's own list base `App\Livewire\Tables\TableComponent` on plain Livewire 3 (no table package; see the `livewire-development` skill) |
| Permissions | spatie/laravel-permission **6.25** (middleware namespace `Spatie\Permission\Middleware`, singular) |
| Tests | **PHPUnit 12.5** (not Pest; data providers and `#[Test]` are attributes, not doc-comment annotations) |
| Forms | spatie/laravel-html **3.13**: `html()->text('name', $value)->forgetAttribute('id')->attributes([...])` in Blade (no `Form::` / `Html::`; `laravelcollective/html` is gone and a test keeps it gone). spatie draws `id` = name by default; the views add `->forgetAttribute('id')` where the old builder drew none, so check an id before relying on it in JS |
| Frontend | Bootstrap 5 (Metronic theme), jQuery, Select2, Laravel **Mix** (not Vite) |
| PDF / Excel | barryvdh/laravel-dompdf 3, maatwebsite/excel 3.1 |
| Database | MySQL (strict mode, InnoDB) |

Look up docs for **these** versions (Laravel 13.x, Livewire 3.x), e.g. through Context7 (`/laravel/docs` branch 13.x,
`/livewire/livewire` branch 3.x).

## 4. Skills

Domain skills live in **`.agents/skills/`** (Codex) and **`.claude/skills/`** (Claude Code). The two folders hold the same
23 skills: the 16 below plus 7 that Laravel Boost added on 2026-11-06 (`debug-using-debugbar`, `deploying-laravel-cloud`,
`deploying-to-cloud`, `infer-conventions`, `medialibrary-development`, `tailwindcss-development`, `testing-best-practices`;
`.github/skills/` holds Boost's Copilot copies). **When you change a skill, change both copies.** Activate the relevant skill
whenever you work in its area.

| Skill | Use for |
|---|---|
| `laravel-best-practices` | any Laravel PHP code (Boost's generic version since 2026-11-06; the text adapted to this project is in git at `18d3a9f`) |
| `livewire-development` | Livewire components and the list base `TableComponent` (Boost's generic Livewire 3 version since 2026-11-06; the text adapted to this project is at `18d3a9f`) |
| `laravel-permission-development` | roles, permissions, menus / routes / buttons access (adapted to spatie 6.25 + this project's access model) |
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

**Deliberately not included** (wrong stack for this project): Pest, Flux UI, Fortify, PostgreSQL (the `pest-testing`,
`fluxui-development`, `fortify-development` and `postgres-pro` folders came from the owner's other project and are not
committed). Boost's `deploying-to-cloud`, `deploying-laravel-cloud` and `tailwindcss-development` are in the folders because
`boost.json` lists them, but they do not apply here (no Laravel Cloud; the pages are Bootstrap 5 and Mix). Copies of the
others are in the owner's other project if the stack ever changes.

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
- **Boost block:** the `<laravel-boost-guidelines>` section at the end of this file and of `CLAUDE.md` is generic Laravel
  guidance written by `php artisan boost:install` and rewritten by `boost:update` (dev tool; off under `APP_ENV=production`).
  Where it disagrees with this file (a test for every change, Vite / `npm run build` where this project uses Mix, Context7
  for the versions in §3, Laravel Cloud), **this file wins**.

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
- **Access:** a role's permissions (Settings > Manage User roles) are the only thing that decides what a Staff (Nurse) or
  Doctor may open. `canUseModule($module)` (menus, dashboards, buttons) and the routes' `permission:` middleware use the same
  five permissions (`App\Support\ModuleAccess`). Staff = Nurse = the `staff` role "Staff (Nurse)"; the `nurse` role is
  retired and the designation / station / shift layer is gone (Phase 3). Give permissions to roles, never to a user.
- **Passwords:** `User` does not hash automatically: `Hash::make()`.
- **No prices, amounts, currency or payment** anywhere (free clinic).
- **Decisions already taken** (do not re-ask): `docs/STATUS.md` §6 and `docs/audit-plan-2026-10-08.md` §2.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.3. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

=== spatie/laravel-medialibrary/core rules ===

## Media Library

- `spatie/laravel-medialibrary` associates files with Eloquent models, with support for collections, conversions, and responsive images.
- Always activate the `medialibrary-development` skill when working with media uploads, conversions, collections, responsive images, or any code that uses the `HasMedia` interface or `InteractsWithMedia` trait.

</laravel-boost-guidelines>
