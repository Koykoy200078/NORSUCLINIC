---
name: laravel-best-practices
description: "Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code in this project (Laravel 10.50, PHP 8.2 at the clinic / 8.3 on the dev laptop). This includes creating or modifying controllers, models, migrations, form requests, repositories, services, jobs, scheduled commands and Eloquent queries. Triggers for N+1 and query performance issues, caching, authorization and security, validation, error handling, route definitions and architectural decisions. Also use for Laravel code reviews and refactors."
license: MIT
metadata:
  author: laravel (adapted for Medical University Clinic / NORSUCLINIC, Laravel 10)
---

# Laravel Best Practices (Laravel 10 — this project)

Best practices for Laravel, prioritized by impact. Each rule teaches what to do and why. Adapted from the Laravel Boost
skill (written for Laravel 13): every Laravel 11+ API in the rule files is marked and given its Laravel 10 equivalent.
For exact API syntax, look up the **Laravel 10.x** docs (Context7 library `/laravel/docs`, branch `10.x`).

## Consistency First

Before applying any rule, check what the application already does. Laravel offers multiple valid approaches — the best
choice is the one the codebase already uses, even if another pattern would be theoretically better. Inconsistency is
worse than a suboptimal pattern. Check sibling files, related controllers, repositories, models or tests first.

## Project rules that override the generic rules

| Topic | Rule in this project |
|---|---|
| Version | Laravel **10**: `$casts` property (not `casts()`), middleware aliases in `app/Http/Kernel.php`, exceptions in `app/Exceptions/Handler.php`, schedule in `app/Console/Kernel.php`. No `once()`, `defer()`, `Context`, `Concurrency`, `Cache::flexible/memo`, `Uri`, `Exceptions::fake()` |
| Write path | Controller → **Repository** (`app/Repositories/*`, extend `BaseRepository`) holds the business logic; services exist for inventory (`MedicineInventoryService`), settings (`SettingsService`) and reports (`app/Services/Reports/*`) |
| Stock | **Never** change `medicine_batches.quantity` or `medicines.available_quantity` directly — always `MedicineInventoryService` (`recordStockIn`, `deductStockFefo`, `restoreStock*`, `syncMedicineTotals`) |
| Class ≠ table | `DispenseRecord` → `medicine_bills`, `DispenseRecordItem` → `sale_medicines`, `StockIn` → `medicine_availabilities`, `PrescriptionMedicine` → `prescriptions_medicines` (FK column is `medicine`, not `medicine_id`), `StockOutView` → `used_medicines_view` (a view) |
| Soft delete | `users` / `patients` use **`archived_at`**; consultations / certificates / lab requests use `deleted_at`. Raw queries must filter them |
| Routes / URLs | Named routes only; role-aware helpers `getRouteByRole()`, `getDashboardURL()`, `getDashboardRouteName()`. Never hard-code `/admin/...` |
| Passwords | `User` does **not** hash automatically: always `Hash::make()` |
| Network | LAN app: no page may need the internet (no CDN, no third-party asset or API) |
| Money | Free clinic: **no prices, amounts, currency or payment fields**, ever |
| Database | MySQL strict mode; all tables InnoDB; tests on their own `norsu_clinic_test` schema, never on `norsu_clinic` |
| Activity log | Use the `LogsActivity` trait helpers for audit rows; never log passwords or full patient records |

## Quick Reference

### 1. Database Performance → `rules/db-performance.md`
- Eager load with `with()` to prevent N+1 queries; `withCount()` instead of loading relations to count
- Select only needed columns; `chunk()` / `chunkById()` / `cursor()` for large sets
- Index columns used in `WHERE`, `ORDER BY`, `JOIN`
- Never query in Blade templates (the sidebar badges are cached in `layouts/menu.blade.php` — keep it that way)

### 2. Advanced Query Patterns → `rules/advanced-queries.md`
- `addSelect()` subqueries over eager-loading a whole has-many for one value
- Conditional aggregates (`CASE WHEN` in `selectRaw`) over several count queries
- `whereIn` + `pluck()` over `whereHas` for better index usage

### 3. Security → `rules/security.md`
- `$fillable` / `$guarded` on every model; authorize every action (role, permission, record)
- No raw SQL with user input; `{{ }}` escaping; `@csrf` on every form
- Validate MIME type, extension and size for uploads (consultation images are private — served through a route)

### 4. Caching → `rules/caching.md`
- `Cache::remember()`; explicit keys (the `file` store has no tags); `Cache::lock()` / `lockForUpdate()` for races
- Settings: change through `SettingsService::set()` (clears its cache); writing the `Setting` model directly leaves it stale

### 5. Eloquent Patterns → `rules/eloquent.md`
- Correct relationship types with return types; local scopes; global scopes sparingly
- `$casts` property; cast date columns; never hard-code table names

### 6. Validation & Forms → `rules/validation.md`
- Form Request classes; `$request->validated()` only — never `$request->all()` into `create()` / `update()`
- Rules must match the column (length, nullability): MySQL strict mode rejects what used to be truncated

### 7. Configuration → `rules/config.md`
- `env()` only inside config files (this project's `.env` uses `CACHE_DRIVER`, not `CACHE_STORE`)

### 8. Testing Patterns → `rules/testing.md`
- **PHPUnit 9 classes**, not Pest; fixtures in `tests/Concerns/BuildsClinicData.php`; one regression test per fixed bug
- Write the failing test first; see it fail; then fix

### 9. Queue & Job Patterns → `rules/queue-jobs.md`
- Database queue, no always-on worker: keep work synchronous where possible

### 10. Routing & Controllers → `rules/routing.md`
- Each role has its own route file (`web.php` admin, `staff.php`, `doctor.php`) with the stack `auth`, `checkUserStatus`, `role:…`, `permission:…`
- Implicit route model binding; Form Requests; thin controllers

### 11. HTTP Client → `rules/http-client.md`
- Not used (no outside APIs). Never add a call to an internet service

### 12. Events, Notifications & Mail → `rules/events-notifications.md`, `rules/mail.md`
- `MAIL_MAILER=log`: nothing is really e-mailed; do not build features that depend on mail delivery
- Broadcast / real-time events after commit (`ShouldDispatchAfterCommit`)

### 13. Error Handling → `rules/error-handling.md`
- `app/Exceptions/Handler.php` (Laravel 10)

### 14. Task Scheduling → `rules/scheduling.md`
- `app/Console/Kernel.php`; only `db:backup` hourly today

### 15. Architecture → `rules/architecture.md`
- Dependency injection over `app()` where the class already uses DI; default `ORDER BY id DESC`; `mb_*` for UTF-8

### 16. Migrations → `rules/migrations.md`
- `php artisan make:migration`; `constrained()` for foreign keys; reversible `down()`
- Migrations must also upgrade the **clinic's old data** (see `docs/audit-plan-2026-10-08.md` Phase 1): guard
  `Schema::hasTable/hasColumn`, never lose rows, wrap table rebuilds in `App\Support\LegacyMysqlMigration::withoutZeroDateChecks()`

### 17. Collections → `rules/collections.md`

### 18. Blade & Views → `rules/blade-views.md`
- Bootstrap 5 (Metronic) markup; page scripts go in `@push('scripts')` or `@section('page_js')` — **`@section('scripts')` is never output by `layouts/app.blade.php`**

### 19. Conventions & Style → `rules/style.md`
- Laravel naming; `Str` / `Arr` / `Number` helpers; no inline JS/CSS in new Blade code

## How to Apply

Read the relevant rule file directly (do not delegate to sub-agents in this project).

1. Identify the file type and select relevant sections (e.g. migration → §16, controller → §1, §3, §5, §6, §10).
2. Check sibling files for existing patterns — follow those first per Consistency First.
3. Verify API syntax against the **Laravel 10.x** docs.
