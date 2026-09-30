# Remediation status – re-audit of 2026-09-30

Branch: `changes_v2` (no PR, `develop` untouched). Scope: LAN-only, HTTP-only deployment; all mail-related findings skipped by request.
Detail of each finding: `full-reaudit-2026-09-30.md` (pass 1) and `full-reaudit-2026-09-30-pass2.md` (pass 2).

## What was done, by phase

| Phase | Area | Result |
|---|---|---|
| B0 | Pages that returned 500 in the baseline crawl | fixed |
| B1 | Consultation / certificate module (staff + doctor): module-override bypass, patient history wiped, name auto-merge, private image storage, creator spoofing, audit rows | fixed, regression tests |
| B2 | Input purifier removed (stored `&amp;`, deleted `<`), long clinical text truncation, strict SQL mode + defaults migrations | fixed; `text:repair-entities` for old rows |
| B3 | Prescriptions, dispensing (FEFO, dosage-aware), patient queue, lab requests | fixed, regression tests |
| B4 | Staff / doctor / nurse account management (scope, password-only change, impersonation, archive/restore, delete guards) | fixed, regression tests |
| B5 | Inventory integrity (stock-in edit/delete, batches, ledger-based reports, medicine delete) | fixed; `inventory:reconcile` |
| C | Security + LAN/HTTP config (handler status codes, log viewer admin-only, CORS, logging, `.env.example`, HTTPS flag) | fixed |
| D | Backups, idempotent seeders, append-only audit log, runnable test suite | fixed |
| E | intl-tel-input replaced by Philippine-only (+63) input; server normalisation/validation; `phone:normalize` | done (commit a6039a1) |
| F | Dependency updates: 98 → 6 advisories; Debugbar dev-only; lock installable on PHP 8.1–8.4 | done (commit 53a2be3) |
| G | Low findings: 50+ missing translation keys added, dead `patient_queue/*_backup` views removed, Windows-only npm scripts fixed; L-04/06/07/08/10/11/13, P2-L2/L5 were already closed in earlier phases | done |
| H | Final regression (below) | passed |

## Final verification

* PHPUnit: **106 tests / 485 assertions pass** (MySQL schema `norsu_clinic_test`, guarded by a `_test` name check).
* Fresh `migrate:fresh --seed`, then `db:seed` again: no errors, nothing duplicated or overwritten. `config:cache`, `route:cache`, `view:cache` all build.
* Crawl of every GET route as admin, doctor and six staff variants: **0 server errors**, 0 error lines in the log (remaining 400/404 are routes called without required parameters / non-existent ids).
* Browser checks: +63 input on staff/doctor/patient/profile/settings/dispensing pages; Livewire tables (render, search, sort) on 9 list pages; no JS errors.
* All PDFs (prescription, consultation, lab request) and exports (stock-in Excel, activity-log CSV) generate valid files as admin, doctor and staff.

## Deliberately not changed (accepted / needs your decision)

| Item | Why | What to do |
|---|---|---|
| Laravel 10.x advisories (debug page XSS, signed-URL path, email-rule CRLF) | fixed only in Laravel 12; the app sends no mail, has debug off, uses no temporary signed URLs | plan a Laravel 11/12 upgrade (needs PHP 8.2+) when convenient |
| spatie/laravel-medialibrary 10.x advisories | fixed only in 11.23 (PHP 8.2+); app only calls `addMedia()` on validated uploads, never URL uploads. Advisory text could not be read in the sandbox – please confirm | upgrade together with PHP 8.2+ |
| `rappasoft/laravel-livewire-tables` held at 3.2.x | 3.8 breaks the customised published table views and `$tableName` visibility | port the views in a dedicated task |
| `laravelcollective/html` abandoned | no CVE; replacing touches every form | leave |
| DB password still in git history (old `auto-backup-database.bat`) | rewriting history affects every clone | **rotate the DB password**; purge with `git filter-repo` if the repo is shared |
| Mail findings (e.g. patient registration mail in `PatientRepository::store`) | out of scope by request | – |
| PHI snapshot columns in `activity_logs` | needed by the log detail view | restrict who can open Activity Logs |
| `errors/404.blade.php` references `assets/img/404-error-image.svg`, which is not in the repo | cosmetic, pre-existing | add the image or change the view |

## Deployment checklist (after pulling `changes_v2`)

1. `composer install` (add `--no-dev` in production).
2. `npm ci && npm run prod` (built assets are not in git).
3. `php artisan migrate` (new migrations: widen clinical text, legacy defaults, unique dispense history number, FK restrictions, batch links, sale_medicines indexes, unique settings key, default role/permission names).
4. `php artisan vendor:publish --tag=log-viewer-assets --force`.
5. Run once, dry-run first where an `--apply` flag exists, after a DB backup:
   `consultation-images:secure`, `text:repair-entities`, `inventory:reconcile`, `phone:normalize`.
6. `php artisan config:cache route:cache view:cache`; start the scheduler (`schedule:work` – the startup scripts now do it) for automatic backups.
7. Rotate the database password.
8. The admin account's seeded placeholder phone was invalid; set a real +63 number on Profile (old installs only).
