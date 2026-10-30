# Upgrade plan: Laravel 10.50 → Laravel 13

**Status:** PLAN FOR THE OWNER'S APPROVAL. Nothing in this plan has been started; no dependency, code or configuration was changed
while it was written (read-only research, 2026-10-30). Rules that apply (AGENTS.md): new or changed dependencies need the owner's
approval; `changes_v2` only unless told otherwise; test-first; real-browser check; `norsu_clinic` left alone (rehearse on scratch copies).

---

## 1. Summary

- **Target:** Laravel **13.39** (released 2026-03-17; PHP 8.3 – 8.5; bug fixes to Q3 2027, security fixes to **2028-03-17**).
  Laravel 12 (PHP 8.2 – 8.5) gets security fixes only until **2027-02-24** and bug fixes ended 2026-08-13, so 12 is a resting
  point at most, not a destination.
- **Route:** one framework step at a time, **10 → 11 → 12 → 13**, each its own verified, reversible, deployable checkpoint,
  after a **preparation stage (U0) done on Laravel 10 itself** that removes the riskiest blockers first. Laravel 11 and 12 both run on
  the clinic's current **PHP 8.2**; only the last step needs **PHP 8.3** on the clinic PC.
- **Why not jump straight to 13:** `composer why-not laravel/framework` lists **6 packages** that block Laravel 11, **9** that block 12 and **13** that
  block 13 (12 direct dependencies plus `laravel/prompts` through them), one of them **abandoned** (`laravelcollective/html`), and every skipped
  version's breaking changes would land in one commit that the 1,039-test suite could only report as "everything is red".
- **The work is dominated by four things, not by Laravel itself** (Laravel 13 is documented as a "minimal breaking changes" release):
  1. the abandoned form builder (`laravelcollective/html`, **832 lines in 83 view files**) → a drop-in fork, no view edits;
  2. the table package `rappasoft/laravel-livewire-tables` 3.2 → 3.9/3.10 (**24 customised views to port**, 24 table components);
  3. `spatie/laravel-permission` 5 → 6 (middleware namespace rename, in the Kernel, the Livewire persistent middleware and one test)
     and `spatie/laravel-medialibrary` 10 → 11;
  4. **PHP 8.3 on the clinic PC** (the owner's side) for the last step.
- **What the upgrade buys:** the 8 `composer audit` advisories on Laravel 10.50 / medialibrary 10.15 / symfony-mime disappear (none was
  reachable here, see STATUS section 6, but Laravel 10 receives no more fixes), a supported framework until 2028, and PHP 8.3+.
- **What it deliberately does not do:** no slim `bootstrap/app.php`, no Vite, no Livewire 4, no Pest, no spatie/laravel-html rewrite.
  Laravel 11+ is documented as compatible with the Laravel 10 application structure (`app/Http/Kernel.php`, providers, `config/*.php` stay).

## 2. Where we are (measured 2026-10-30)

| Item | Now |
|---|---|
| Framework | `laravel/framework` **v10.50.3** (10.50.4 exists); 177 locked packages |
| PHP | clinic **8.2**, laptop 8.3.31 (also installed here: 8.2.31, 8.4.15); `composer.json` requires `^8.2`, platform pin `8.2.0` |
| Livewire / tables | Livewire **3.8.10**, `rappasoft/laravel-livewire-tables` **3.2.8** (held back: "3.8 breaks the customised views") |
| Permissions / media | `spatie/laravel-permission` **5.11.1**, `spatie/laravel-medialibrary` **10.15.0** |
| Dates | `nesbot/carbon` **2.73** |
| Tests | PHPUnit **9.6.37**; 137 test classes, ~1,080 test methods, 1,039 tests / 10,089 assertions, 10 – 19 min per full run |
| Application structure | classic: `app/Http/Kernel.php`, `app/Exceptions/Handler.php`, 5 service providers, own `config/*.php` |
| Front end | Laravel Mix + jQuery + Bootstrap 5 (not touched by a framework upgrade) |

## 3. What blocks Laravel 13 today (evidence)

`composer why-not laravel/framework ^13.0` plus Packagist metadata for every direct dependency:

| Package | Installed | Supports up to | Needed for 13 | Size |
|---|---|---|---|---|
| `laravelcollective/html` | 6.4.1, **abandoned** (replacement named: `spatie/laravel-html`) | Laravel 10 | a maintained **drop-in fork** keeping `Collective\Html` and the `Form` / `Html` facades: `prabowosd/laravel-collective-html` v1.1.2 (L10–13, 2026-05) or `rdx/laravelcollective-html` 6.10 (L11–13, `replace`s the original, 2026-03) | S (no view edits) |
| `rappasoft/laravel-livewire-tables` | 3.2.8 | Laravel 11 | **3.9 / 3.10** (L10–13, Livewire ^3, PHP 8.1+; 3.10 released 2026-10-20, 3.9 2026-10-09) | **L** |
| `spatie/laravel-permission` | 5.11.1 | Laravel 10 | **6.25** (L8–13, PHP ^8.0): one jump covers every hop; 7.x / 8.x need PHP 8.4 / 8.3 | M |
| `spatie/laravel-medialibrary` | 10.15.0 | Laravel 10 | **11.20** for 11 and 12, **11.23+** for 12 and 13 (PHP ^8.2) | M |
| `mariuzzo/laravel-js-localization` | 1.11.2 | Laravel 11 | **2.1.0** (L11–13, PHP ^8.2) | S |
| `laravel/tinker` | 2.11.1 | Laravel 12 | **3.0** | S |
| `barryvdh/laravel-debugbar` (dev) | 3.16.5 | Laravel 12 | 4.x | S |
| `spatie/laravel-ignition` (dev) | 2.9.1 | Laravel 12 | 2.12 | S |
| `rap2hpoutre/laravel-log-viewer` | 2.5.0 | Laravel 12 | 3.1 (L12–13) – or drop it: `opcodesio/log-viewer` is also installed | S |
| `barryvdh/laravel-ide-helper` (dev) | 2.15.1 | Laravel 10 | 3.x | S |
| `beyondcode/laravel-query-detector` (dev) | 1.8.0 | Laravel 10 | 2.x | S |
| `laravel/breeze` (dev) | 1.29.1 | Laravel 10 | not used by the app code: remove | S |
| `nunomaduro/collision` (dev) | 6.4.0 | – | 8.x | S |
| `phpunit/phpunit` (dev) | 9.6.37 | Laravel 10 | 10.5 now; **^11.5.50 or ^12.5.8** with 13 (Laravel 13 *suggests* 11.5.50 / 12.5.8 / 13; the upgrade guide names 12) | M |
| `doctrine/dbal` | 3.10.6 | – | not needed from Laravel 11: remove | S |

**Already compatible, no change:** `livewire/livewire` 3.8.x (L10–13; **stays on Livewire 3**), `maatwebsite/excel` 3.1.70 (L5.8–13), `barryvdh/laravel-dompdf`
3.1.2, `laravel/reverb` 1.12, `tightenco/ziggy` 1.8.2, `lab404/laravel-impersonate`, `laracasts/flash`, `mews/purifier`, `opcodesio/log-viewer`,
`guzzlehttp/guzzle`, `league/flysystem-aws-s3-v3`, `ezyang/htmlpurifier`.

Laravel itself needs: **PHP ≥ 8.3**, `nesbot/carbon` ^3.8.4, `symfony/*` ^7.4 || ^8, `laravel/prompts` ^0.3, `brick/math`, PHPUnit as above.
Laravel 11 and 12 need PHP ≥ 8.2; Laravel 11 still allows Carbon 2, Laravel 12 requires Carbon 3.

## 4. Code that the upgrade touches (scanned, not guessed)

| # | Finding | Where | Needed |
|---|---|---|---|
| C1 | `Spatie\Permission\Middlewares\*` (plural) becomes `…\Middleware\*` in permission v6 | `app/Http/Kernel.php` (2), `app/Providers/AppServiceProvider.php` (Livewire persistent middleware, 2), `tests/.../ConsultationAccessTest.php` (2) | rename; the permission matrix proves it |
| C2 | `App\Models\Role extends Spatie\Permission\Models\Role` (v6 changed model / contract signatures); `App\Models\Permission` is a plain model, not spatie's | `app/Models/Role.php`, `config/permission.php` | check signatures; migrations stay (tables exist) |
| C3 | **Carbon 3** (`diffIn*` returns signed floats; required from Laravel 12): 5 call sites, 62 `Carbon::` static calls, 0 in tests | `MedicineController:219`, `QueueBoardService:66`, `medicines/templates/columns/expiration.blade.php:32`, `patient_dashboard/index.blade.php:173` (`$expiry->diffInDays(now()) <= 30` would be **always true** for a future date once signed), `patient_queue/doctor_view_partial.blade.php:8` | characterization tests first, then fix |
| C4 | Laravel 11 changes `->change()`: every modifier not restated is **dropped**. 3 legacy migrations use it (`medicine_bills` price columns 2023-07-29; `request_documents.examined_on` 2025-10-16; `purchased_medicines.expiry_date` 2025-10-21) | `database/migrations/*` | they have already run on the clinic DB; they matter for a **fresh install / the test schema**: restate the modifiers, prove it with `db:integrity --compare-fresh` against the real-dump rehearsal |
| C5 | Laravel 13 `cache.serializable_classes` is `false` by default: cached PHP objects come back unusable. **13 cache writes**, several store Eloquent collections (`Setting::all()->keyBy`, doctors, patients, medicines, categories lists) | `SettingRepository:96`, `SettingsService:21`, `MedicineBillRepository`, `PrescriptionRepository`, `WarmUpCache`, … | decide per key: store arrays, or list the classes in `config/cache.php`; test with the real `file` store |
| C6 | Laravel 13 `VerifyCsrfToken` → `PreventRequestForgery` (adds `Sec-Fetch-Site` origin checking; the old name stays as a deprecated alias) | `app/Http/Middleware/VerifyCsrfToken.php`, `Kernel.php`, `SharedPanelRoutesTest` | rename; test every form, Livewire call and AJAX from the LAN address, `localhost` and `127.0.0.1` |
| C7 | Laravel 13 default cache prefix / session cookie name change (hyphen instead of underscore) | `config/session.php`, `config/cache.php` are the app's own | keep them explicit so nobody is logged out; assert it in a test |
| C8 | Laravel 11: `InvokeDeferredCallbacks` middleware belongs at the start of `Kernel::$middleware` when the old Kernel is kept | `app/Http/Kernel.php` | add (harmless; `defer()` is unused) |
| C9 | PHPUnit 12 removes doc-comment metadata: **15 `@dataProvider` in 13 files**, no `@test`/`@group`/`@depends`/`@covers`, **0 mocks**, providers already static | `tests/**` | → `#[DataProvider]`; `phpunit.xml` migrated with `--migrate-configuration` |
| C10 | 24 customised rappasoft views (`pagination` ~88 changed lines, `table` ~85, `datatable` ~60, `search-field` ~56, `toolbar` ~27, bootstrap-4 pagination ~27, the rest 1 – 17), **35 published views are byte-identical copies** of 3.2.8; 24 components extend `LivewireTableComponent`, 3 extend `DataTableComponent` directly | `resources/views/vendor/livewire-tables/**`, `app/Livewire/**` | delete the 35 copies first, then port the 24 |
| C11 | Laravel 13 MySQL `DELETE … JOIN … ORDER BY … LIMIT` is compiled in full | any `delete()` on a joined or limited query | grep and test; low |
| C12 | Two log-viewer packages are installed | `composer.json` | keep one (cleanup), fewer packages to port |

**Not affected:** `doctrine/dbal` use (none in code), `$dates` / `HasUuids` / `Schema::getColumnType` helpers (none), JavaScript and Mix, `maatwebsite/excel`,
`dompdf`, Reverb, the mysql 9 server, the Windows startup script.

## 5. The route, stage by stage

Every stage ends with the **gates of section 6**, a commit, and the Handover protocol. A stage is only started when the previous one is green
and (for U1 – U3) when the owner says so.

### U0: preparation on Laravel 10 (PHP 8.2; no framework change; each item is deployable on its own)
| ID | Work | Notes |
|---|---|---|
| U0.1 | Baseline and safety: `db:backup`, copy the whole project folder (with `vendor/`) off the PC, git tag, snapshots (`route:list --json`, link crawl, permission matrix, `db:integrity --compare-fresh` on a scratch copy of the dump), record the 1,039 / 10,089 numbers | the rollback point of the whole upgrade |
| U0.2 | `laravel/framework` 10.50.3 → ^10.50.4 | patch release; proves the toolchain |
| U0.3 | Replace `laravelcollective/html` by the fork that supports 10 – 13 (`prabowosd/laravel-collective-html` ^1.1; **read its diff against 6.4.1 before adopting**; pin the exact version) | same namespace and facades: no view edits; all 83 files exercised by the link crawl and the screens |
| U0.4 | Delete the 35 identical published rappasoft views; upgrade `rappasoft/laravel-livewire-tables` ~3.2.0 → ^3.9 / 3.10; port the 24 customised views and the base class (`LivewireTableComponent`, `SearchesByWords`, `SortsByUniqueKey`) | the long pole; framework independent (3.9 / 3.10 run on Laravel 10); lazy tabs, filters, bulk actions, pagination, per-page, search debounce, Bootstrap 5 look; `ListPagingTest`, the link crawl (loads lazy tables) and a browser pass of every table as admin / staff / doctor |
| U0.5 | PHPUnit 9.6 → 10.5: `phpunit.xml` migrated, 15 `@dataProvider` → `#[DataProvider]`, `nunomaduro/collision` / `mockery` as needed | test-only change; Laravel 10 supports PHPUnit ^10.0.7 |
| U0.6 | Dev cleanup: remove `laravel/breeze`, unused `laravel/sail` / `ide-helper` / `query-detector` if nobody uses them, keep one log viewer | fewer packages to carry through three majors |
| U0.7 | Readiness tests on Laravel 10 (so later failures point at the framework, not at us): characterization tests for the 5 Carbon call sites (days to expiry sign and size, queue refresh seconds, dashboard "expiring soon"), cache round-trip tests for the 13 cache keys, the three `->change()` columns' shape | written and green **before** any hop |

### U1: Laravel 11.57 (PHP 8.2)
`laravel/framework ^11.57`, `spatie/laravel-permission ^6.25` (+ rename C1, check C2), `spatie/laravel-medialibrary ^11.20` (conversions, collection and
`registerMediaCollections` API per its upgrade guide), `mariuzzo/laravel-js-localization ^2.1`, `nunomaduro/collision ^8`, remove `doctrine/dbal`, add
`InvokeDeferredCallbacks` (C8), restate the three `->change()` migrations (C4). PHPUnit 10.5 stays; Carbon 2.x is still allowed.

### U2: Laravel 12.70 (PHP 8.2)
`laravel/framework ^12.70`; **Carbon 3** (`nesbot/carbon ^3.8.4`) with the fixes proven by the U0.7 tests (C3); check image validation (SVG no longer accepted by `image`, our
uploads use `mimes:jpeg,jpg,png` so no effect expected), `Schema::getTableListing`, the `local` disk root (the app owns
`config/filesystems.php`, so no change expected). This is the **safe resting point** if PHP 8.3 is not available yet (security fixes until 2027-02-24).

### U3: Laravel 13.39 (PHP ≥ 8.3 on the clinic PC)
Prerequisite: PHP 8.3 (or 8.4) installed on the clinic PC with the same extensions (`pdo_mysql`, `mbstring`, `intl`, `gd`, `zip`, `fileinfo`, `openssl`, `sodium`, `curl`),
`composer.json` `php` → `^8.3` and the platform pin → `8.3.0`. Then `laravel/framework ^13.39`, `laravel/tinker ^3.0`, `barryvdh/laravel-debugbar ^4`,
`spatie/laravel-ignition ^2.12`, `spatie/laravel-medialibrary ^11.23`, `rap2hpoutre/laravel-log-viewer ^3.1` (if kept), PHPUnit **^11.5.50 (PHP ≥ 8.2) or ^12.5.8 (PHP ≥ 8.3)**,
CSRF rename (C6), cache prefix / cookie names pinned (C7), `serializable_classes` decision (C5), MySQL `DELETE JOIN` check (C11), the container now injects `null` for a nullable class parameter with a default (scanned: 3 such parameters, all in private helper methods that are called directly, so no expected effect), Bootstrap-3 pagination view names if referenced.

### U4 (optional, separate decisions, not part of this upgrade)
slim `bootstrap/app.php` structure; Vite instead of Mix; Livewire 4; moving the 83 form files to `spatie/laravel-html` (the maintained replacement, removes the fork);
Pest. Each one only on the owner's request.

## 6. Gates (every stage, in this order)

1. **Test-first for anything we change**; the stage's readiness tests are green before the hop and stay green after it.
2. **Full suite with `LINK_CRAWL=1`** on **PHP 8.2.31 and 8.3.31** (8.4 once at the end): all green, none skipped, the 1,039 + new numbers recorded.
3. **Route contract:** `route:list --json` diff against the U0.1 snapshot (only intended changes), `panel-routes.json` pinned test unchanged, no duplicate names.
4. **Permission matrix** (route × role, `MATRIX_SAME_USER=1`): 0 server errors, no new LEAK / OVER lines beyond the known 5 + 20.
5. **Real-dump rehearsal on a scratch schema** (never `norsu_clinic`): migrate the clinic dump under the new framework, a second `migrate` does nothing, `db:integrity --compare-fresh` exit 0 with 57 keys (this is what catches the `->change()` differences), `inventory:reconcile` consistent.
6. **Real-browser pass** (built-in browser, admin / staff / doctor, three hostnames): sign in / out, dashboards, every table (filters, sort, pagination, per-page, search, lazy tabs), every form (Collective), consultation → prescription → dispensing, Meds Only, count sheets, Reports (screen, PDF, Excel, CSV), uploads (profile, logo, consultation photo), impersonation, language switch, Reverb live updates and the "Live updates off" check, notifications; from a **second LAN address** as well as `localhost`.
7. **Exports and files:** a PDF and an Excel of each kind byte-compared in content (text and numbers) with the pre-upgrade output; uploads written, read, deleted.
8. **Ops:** `php artisan optimize` / caches, `schedule:list`, one real `schedule:test` run of the 6 PM backup + a restore of that dump into a scratch schema, `startup-dev-environment.ps1 -CheckOnly` and a real start, Windows firewall report.
9. **Security:** `composer audit` (target: 0 advisories at the end of U3; recorded at every stage), `composer validate`, `check-platform-reqs`, no `APP_DEBUG` leak, error log empty after the browser pass.
10. **Mutation checks** on every guard we add or change (the existing runner), survivors become tests.
11. **Records:** plan block, STATUS, Handover START → END with commit hashes, `docs/commands.md` only if a command changes, memory notes; **push `changes_v2` or the upgrade branch only as decided in section 9**.

## 7. Risk register

| # | Risk | Likelihood / impact | Mitigation |
|---|---|---|---|
| R1 | PHP 8.3 cannot be installed on the clinic PC | medium / blocks U3 only | U1 and U2 run on 8.2; stop at 12.70 (secure until 2027-02-24); ask now |
| R2 | The form-builder fork is one person's project (supply chain, future breakage) | medium / medium | pin the exact version, review its diff against 6.4.1 once, keep the move to `spatie/laravel-html` as a planned U4 item |
| R3 | rappasoft 3.9 / 3.10 changes markup or behaviour of the 24 customised views (this is why 3.2 was held) | **high** / high (every list screen) | do it first and alone (U0.4), on Laravel 10; delete the 35 identical copies; link crawl + browser pass per table and role; screenshots before / after |
| R4 | Permission v6 cache / contract changes alter who may open what | low / **critical** | permission matrix + `RoleMatrixP3`-style oracle + the existing access tests; roles are read from the DB, tables unchanged |
| R5 | Carbon 3 sign / float changes (expiry warnings, dashboard "expiring soon", queue refresh) | medium / medium | U0.7 characterization tests before U2 |
| R6 | `->change()` migrations produce a different schema on a fresh install | medium / medium (tests, new installs; the clinic DB is already migrated) | restate modifiers; `--compare-fresh` against the real dump |
| R7 | Laravel 13 stops unserializing cached Eloquent objects: pages break only after the cache is warm | medium / medium | U0.7 cache round-trip tests; convert to arrays or allow-list; clear caches at deploy |
| R8 | `PreventRequestForgery` rejects legitimate requests from the LAN address or another hostname | low-medium / high (nobody can save) | test from every address form in gate 6; keep the old alias until proven |
| R9 | Hidden breakage in transitive packages (Symfony 6 → 7.4 / 8, Carbon, Flysystem) | medium / medium | both PHP versions in every gate; error log scan; `composer why` review of each major bump |
| R10 | Upgrade pushed to `changes_v2` and pulled on the clinic PC half-done | medium / high | decision 3 below: separate branch until a stage is complete and approved; deploy only tagged stages |
| R11 | Rollback needs internet (`composer install`) on an offline clinic PC | medium / high | before each stage copy the whole project folder including `vendor/`; rollback = restore the folder + the DB backup |
| R12 | Test runs are slow (10 – 19 min, two PHP versions, several per stage) | certain / schedule only | run in the background while writing records; never two phpunit at once |
| R13 | Unknowns inside the three package majors (medialibrary 11 conversions, rappasoft 3.9+, js-localization 2) | medium / medium | read each upgrade guide at the start of its stage (Context7 / vendor changelog) and add the findings to this plan |

## 8. Rollback and deployment

- **Rollback of a stage:** no stage changes the database schema (no new migration is needed by any hop; package migrations are not re-published), so rollback is code only:
  `git revert` / checkout of the previous tag **and** the pre-stage copy of `vendor/`, then `php artisan optimize:clear`. The DB backup taken at the start of the stage is the safety net, not the plan.
- **Deployment of a stage on the clinic PC (owner or with help):** stop the server window → `php artisan db:backup` and copy the file off the PC → copy the project folder → pull the tagged commit → (U3 only: switch the PC to PHP 8.3 first) → `composer install --no-dev --optimize-autoloader` (needs internet once, or ship `vendor/` from the laptop) → `php artisan optimize:clear` → start with `startup-dev-environment.bat` → a short checklist: sign in, a list, a form, a PDF, the Reports tab, "Live updates" on, one backup. Users will have to sign in again if the cache / session store is cleared; the session cookie name is kept.
- **Windows startup script:** runs unchanged (it only uses `php artisan …`); re-checked in gate 8.

## 9. Decisions needed from the owner

1. **Target and stop points:** go to **Laravel 13** (recommended), with Laravel 12 on PHP 8.2 as a stable resting point if PHP 8.3 is delayed?
2. **Dependency approval** (AGENTS.md requires it): the list in section 3 / 5, in particular the form-builder fork, rappasoft 3.9 / 3.10, permission 6, medialibrary 11, PHPUnit 10.5 → 11 / 12.
3. **Branch strategy:** (a) a long-lived branch `upgrade/laravel-13` pushed to origin, merged into `changes_v2` when a stage is complete and approved (needs your approval, the rules forbid pushing other branches); or (b) directly on `changes_v2` with each verified step pushed (the standing go-ahead), accepting R10.
4. **Forms:** drop-in fork now and `spatie/laravel-html` later (recommended), or rewrite the 83 files for `spatie/laravel-html` now (about 832 lines, no fork risk, much more work)?
5. **PHP on the clinic PC:** can PHP 8.3 (or 8.4) be installed there (WAMP add-on), by whom and when; does the PC have internet for `composer install`, or do we ship `vendor/`?
6. **Window and freeze:** a feature freeze on `changes_v2` during U0 – U3 (only the upgrade and urgent fixes), and a maintenance window for each deployment (about an hour each)?
7. **Scope fence:** confirm that U4 items stay out of this upgrade.

## 10. Order of work if approved

U0.1 → U0.2 → U0.3 → U0.7 → U0.5 → U0.6 → **U0.4** (alone, longest) → *deploy U0 if wanted* → U1 → U2 → *PHP 8.3 on the clinic PC* → U3.
U0.7 comes before U0.4 and U0.5 so that every later red test is attributable. A realistic size: U0 is the biggest block (rappasoft), U1 medium, U2 small to medium, U3 medium plus the
clinic's PHP work; calendar time is set by the 10 – 19 minute full-suite runs (two PHP versions per stage) and by the owner's availability for the clinic PC, not by typing.

## 11. Evidence (reproducible, read-only)

- `composer why-not laravel/framework ^11.0 | ^12.0 | ^13.0` (PHP 8.3.31, composer.phar): the blocking packages of section 3; for 13 also `php ^8.3 but 8.2.0 is installed`.
- Packagist metadata (`repo.packagist.org/p2/<package>.json`) for every direct dependency: supported Laravel / PHP constraints, abandoned flags, release dates.
- `laravel/framework` 10.50.4 / 11.57.1 / 12.70.2 / 13.39.0 (all released 2026-10-27): PHP ^8.1 / ^8.2 / ^8.2 / ^8.3; Carbon ^2.67 / ^2.72.6 | ^3.8.4 / ^3.8.4 / ^3.8.4; PHPUnit suggested ^9.5.8|^10.0.7 / ^10.5.35|^11.3.6|^12.0.1 / ^10.5.35|^11.5.3|^12.0.1 / ^11.5.50|^12.5.8|^13.0.3.
- Laravel docs (13.x branch and 11.x / 12.x upgrade guides, Context7): PHP 8.3 minimum, dependency list, `PreventRequestForgery`, cache prefixes, `serializable_classes`, Container nullable defaults, pagination view names, MySQL `DELETE JOIN`; 11.x: Carbon 3 `diffIn*`, `->change()` modifiers, Doctrine DBAL removal, structure not required to change, `InvokeDeferredCallbacks`; 12.x: Carbon 3 required, `HasUuids`, `Schema::getTableListing`. `spatie/laravel-permission` v6 upgrade guide: middleware namespace, migrations, model signatures.
- Project scans: 832 `Form::` / `Html::` lines in 83 files; rappasoft published views 59 = 35 identical + 24 customised; 24 + 3 table components; 15 `@dataProvider` and no other PHPUnit annotation, 0 mocks; 5 `diffIn*` call sites; 3 `->change()` migrations; 13 `Cache::` writes; `Spatie\Permission\Middlewares` in 3 files; lint of all 1,108 PHP files clean on 8.2, 8.3 and 8.4 (one deprecation inside a test closure on 8.4).
