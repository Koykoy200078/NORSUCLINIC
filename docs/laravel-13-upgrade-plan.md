# Upgrade plan: Laravel 10.50 → Laravel 13

**Status: APPROVED by the owner on 2026-10-30** (answers in section 9). **Stop point (owner, later the same day): the work goes to Laravel 12.70 on PHP 8.2 and
then STOPS; the owner will say when to continue to Laravel 13 (stage U3).** The work happens on the branch **`upgrade/laravel-13`** (created and pushed
with the owner's approval); `changes_v2` stays the clinic's line and is only touched for urgent fixes and for merging a finished, approved stage. Rules
that still apply (AGENTS.md): test-first; **a live browser check on every change and every stage (section 12)**; `norsu_clinic` left alone (rehearse on scratch
copies); no force-push; no merge into `develop`. Progress is recorded in the plan's stage table (section 5) and in Handover.md.

---

## 1. Summary

- **Target:** Laravel **13.39** (released 2026-03-17; PHP 8.3 – 8.5; bug fixes to Q3 2027, security fixes to **2028-03-17**). **Scope of the work now: 10 → 11 → 12.70, then stop**
  (owner's decision); Laravel 13 (stage U3) starts only when the owner says so. Laravel 12 (PHP 8.2 – 8.5) gets security fixes until **2027-02-24**, which is a safe
  resting point for the clinic's PHP 8.2.
- **Route:** one framework step at a time, **10 → 11 → 12 (→ 13 later)**, each its own verified, reversible, deployable checkpoint,
  after a **preparation stage (U0) done on Laravel 10 itself** that removes the riskiest blockers first. Laravel 11 and 12 both run on
  the clinic's current **PHP 8.2**; only the last step (13) needs **PHP 8.3** on the clinic PC.
- **Why not jump straight to 13:** `composer why-not laravel/framework` lists **6 packages** that block Laravel 11, **9** that block 12 and **13** that
  block 13 (12 direct dependencies plus `laravel/prompts` through them), one of them **abandoned** (`laravelcollective/html`), and every skipped
  version's breaking changes would land in one commit that the 1,039-test suite could only report as "everything is red".
- **The work is dominated by four things, not by Laravel itself** (Laravel 13 is documented as a "minimal breaking changes" release):
  1. the abandoned form builder (`laravelcollective/html`, **832 lines in 83 view files**) → **rewritten for the official `spatie/laravel-html`** (owner's decision: no
     third-party fork), done on Laravel 10 with an equivalence test that renders old and new side by side;
  2. the table package `rappasoft/laravel-livewire-tables` 3.2 (held back because later versions break the 24 customised views) → **replaced by in-house plain Livewire 3 tables**
     (one shared base class + one Blade partial; owner's decision: no table package), protected by a golden-master test taken on the current tables;
  3. `spatie/laravel-permission` 5 → 6 (middleware namespace rename, in the Kernel, the Livewire persistent middleware and one test)
     and `spatie/laravel-medialibrary` 10 → 11;
  4. **PHP 8.3 on the clinic PC** (the owner's side) for stage U3 only.
- **Also in scope (owner's request):** remove packages that are proven unused and move legacy code to the newer equivalents, with evidence per item (section 13).
- **Standing rule (owner):** every UI-touching commit and every stage gets a **live browser check**; a failed one blocks the commit and the push (section 12).
- **What the upgrade buys:** the 8 `composer audit` advisories on Laravel 10.50 / medialibrary 10.15 / symfony-mime disappear (none was
  reachable here, see STATUS section 6, but Laravel 10 receives no more fixes), a supported framework until 2027 (12) or 2028 (13), and fewer third-party packages.
- **What it deliberately does not do:** no slim `bootstrap/app.php`, no Vite, no Livewire 4, no Pest.
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
| `laravelcollective/html` | 6.4.1, **abandoned** (replacement named: `spatie/laravel-html`) | Laravel 10 | **`spatie/laravel-html` ^3.13** (L10–13, PHP ^8.2): the 83 view files are rewritten (owner's decision); no fork, no `Collective\Html` left | **L** (832 lines, mechanical but wide) |
| `rappasoft/laravel-livewire-tables` | 3.2.8 | Laravel 11 | **removed**: replaced by in-house plain Livewire 3 tables (owner's decision); no table package remains | **L** (26 components, 59 published views) |
| `spatie/laravel-permission` | 5.11.1 | Laravel 10 | **6.25** (L8–13, PHP ^8.0): one jump covers every hop; 7.x / 8.x need PHP 8.4 / 8.3 | M |
| `spatie/laravel-medialibrary` | 10.15.0 | Laravel 10 | **11.20** for 11 and 12, **11.23+** for 12 and 13 (PHP ^8.2) | M |
| `mariuzzo/laravel-js-localization` | 1.11.2 | Laravel 11 | **2.1.0** (L11–13, PHP ^8.2) | S |
| `laravel/tinker` | 2.11.1 | Laravel 12 | **3.0** | S |
| `barryvdh/laravel-debugbar` (dev) | 3.16.5 | Laravel 12 | 4.x | S |
| `spatie/laravel-ignition` (dev) | 2.9.1 | Laravel 12 | 2.12 | S |
| `rap2hpoutre/laravel-log-viewer` | 2.5.0 | Laravel 12 | **remove**: its route and controller are already gone (`LegacyLogViewerRemovedTest`), only a provider line in `config/app.php` is left; `opcodesio/log-viewer` stays | S |
| `barryvdh/laravel-ide-helper` (dev) | 2.15.1 | Laravel 10 | **remove** (unused) | S |
| `beyondcode/laravel-query-detector` (dev) | 1.8.0 | Laravel 10 | **remove** (unused) | S |
| `laravel/breeze` (dev) | 1.29.1 | Laravel 10 | **remove** (unused) | S |
| `nunomaduro/collision` (dev) | 6.4.0 | – | 8.x | S |
| `phpunit/phpunit` (dev) | 9.6.37 | Laravel 10 | 10.5 now; **^11.5.50 or ^12.5.8** with 13 (Laravel 13 *suggests* 11.5.50 / 12.5.8 / 13; the upgrade guide names 12) | M |
| `doctrine/dbal` | 3.10.6 | – | not needed from Laravel 11: remove | S |
| `league/flysystem-aws-s3-v3` (+ `aws/aws-sdk-php`) | 3.35.3 | – | **remove**: used nowhere (only the stock `s3` disk block in `config/filesystems.php`); the clinic is offline | S |

**Already compatible, no change:** `livewire/livewire` 3.8.x (L10–13; **stays on Livewire 3**), `maatwebsite/excel` 3.1.70 (L5.8–13), `barryvdh/laravel-dompdf`
3.1.2, `laravel/reverb` 1.12, `tightenco/ziggy` 1.8.2, `lab404/laravel-impersonate`, `laracasts/flash`, `mews/purifier`, `opcodesio/log-viewer`.
`guzzlehttp/guzzle` and `ezyang/htmlpurifier` also work but are redundant direct requirements (section 13).

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
| C9 | PHPUnit 12 removes doc-comment metadata: **15 `@dataProvider` in 13 files and 14 `/** @test */` in 2 files** (`ReportCrudIntegrationTest`, `ReportGenerationTest`; found while preparing U0.5, the first scan had missed them), no `@group` / `@depends` / `@covers`, **0 mocks**, providers already static | `tests/**` | → `#[DataProvider]` and `#[Test]` (a method named without `test_` would silently stop being a test); `phpunit.xml` migrated with `--migrate-configuration` |
| C10 | rappasoft: 24 customised views (`pagination` ~88 changed lines, `table` ~85, `datatable` ~60, `search-field` ~56, `toolbar` ~27, bootstrap-4 pagination ~27, the rest 1 – 17) and **35 byte-identical copies** of 3.2.8. **26 table components**: 24 extend `LivewireTableComponent`, **`LabRequestTable` and `DocumentIssuanceTable` extend `DataTableComponent` directly**; the four `*Dashboard*Table`, `Dashboard`, `StaffDashboard`, `ReportGeneration`, `MedicineScreen`, `MedicineDispensingScreen` are plain Livewire. API actually used: `Column::make` 124, `sortable` 79, `view` 70, `searchable` 62, `format` 14, `label` 12, `hideIf` 11, `html` 5; `setPrimaryKey` 26, `setThAttributes` 18, `setBuilder` 5, `setDefaultSort` 4, `setEmptyMessage` 3, `setTdAttributes` 3, `setTableAttributes` 2, `setColumnSelectStatus` 2, `setAdditionalSelects` 2, `setQueryStringStatus` 2; **no rappasoft Filter classes** (header filters are app components); tests touch tables through `set('search')`, `call('sortBy')`, `call('gotoPage')`, `getRows()`, `getComputedPageName()`, `builder()` | `resources/views/vendor/livewire-tables/**`, `app/Livewire/**`, `webpack.mix.js` 77-88, `resources/js/app.js`, `layouts/app.blade.php`, `config/livewire-tables.php`, `AGENTS.md`, both `livewire-development` skills | **replace by an in-house base class** keeping the same public surface (stage U0.4) |
| C11 | Laravel 13 MySQL `DELETE … JOIN … ORDER BY … LIMIT` is compiled in full | any `delete()` on a joined or limited query | grep and test; low |
| C12 | Two log-viewer packages are installed; the old one has no route left | `composer.json`, `config/app.php:178` | remove `rap2hpoutre/laravel-log-viewer` (U0.6) |
| C13 | **Composer 2.10 security blocking**: every `laravel/framework` 10.x / 11.x is flagged, so `composer update` / `require` / `remove` cannot resolve; `--no-security-blocking` resolves it. Blocking does not apply to `composer install` from the lock file | laptop only | use the flag in U0 – U1 (nothing weakened in `composer.json`); at 12.70 it is not needed and `composer audit` must be clean |
| C14 | Laravel 11 changed `float()` / `double()` and `->change()` (every modifier not repeated is dropped). **Checked 2026-10-30: a fresh install today has no `float` column left** (the 2025-12 migrations dropped the price columns; only `double` / `decimal` remain), so the type risk is small; the 4 legacy `->change()` migrations still run on a fresh install | `database/migrations/*` | guard = `tests/Feature/Upgrade/FreshInstallSchemaShapeTest.php`, which pins type / nullability / default / extra / collation / comment of all 702 columns of a fresh install (fixture captured on Laravel 10); the real-dump proof stays `db:integrity --compare-fresh` exit 0 |
| C15 | Password rehash on login (Laravel 11): `config/hashing.php` pins bcrypt rounds to 10, so nothing is rehashed | `config/hashing.php` | readiness test: a login leaves the stored hash unchanged (U0.7) |
| C16 | Asset build: `webpack.mix.js` deletes `public/assets, backend, css, fonts, js, vendor, web, mix-manifest.json` **when it is loaded**; deployment runs `npm ci && npm run prod` | `webpack.mix.js` | build only after the Mix / JS / layout edits; prove on a fresh clone; no hand-built partial runs |
| C17 | medialibrary: no `addMediaConversion`, no media event listeners (v11 breaking changes do not apply); `config/media-library.php` is **not published** (package defaults apply) | models with media, `config/` | compare the v10 and v11 defaults for disk, path generator and queue (U1) |
| C18 | Unused packages and legacy code found read-only on 2026-10-30 (see section 13): S3 adapter, old log viewer, 4 dev packages, redundant `guzzle` / `htmlpurifier` requirements, Turbo (never bundled), `handlebars` / `flatpickr` / `bootstrap-daterangepicker` / `datatables` / `lodash` npm entries, Tailwind (exists for rappasoft's views), `resources/js/bootstrap.js` using an undefined `window.axios`, 28 Livewire `$listeners` | `composer.json`, `package.json`, `webpack.mix.js`, `resources/js/*` | U0.6 (now) and U0.8 (after tables and forms), each proven at removal time |

**Not affected:** `doctrine/dbal` use (none in code), `$dates` / `HasUuids` / `Schema::getColumnType` helpers (none), JavaScript and Mix, `maatwebsite/excel`,
`dompdf`, Reverb, the mysql 9 server, the Windows startup script.

## 5. The route, stage by stage

Every stage ends with the **gates of section 6**, a commit, and the Handover protocol. A stage is only started when the previous one is green.
**The work stops after U2 (Laravel 12.70); U3 starts only when the owner says so.**

### U0: preparation on Laravel 10 (PHP 8.2; no framework change; each item is deployable on its own)

Order: **U0.1 ✅ → U0.2 → U0.5 → U0.6 → U0.7 → U0.4 → U0.3 → U0.8** → deploy U0 in the owner's window (`npm ci && npm run prod`, backup first).
U0.7 comes before U0.4 / U0.3 so that every later red test is attributable.

| ID | Work | Notes |
|---|---|---|
| U0.1 | Baseline and safety: git tag `pre-laravel-13-upgrade` (local), `route:list --json` (515 routes), permission matrix (2,892 requests), `composer.json` / `composer.lock` copies, `db:integrity --compare-fresh` on the live DB (exit 0), numbers 1,039 / 10,089 | **done 2026-10-30**; reproducible from commit `71e43ae`; the rollback point of the whole upgrade |
| U0.2 | `laravel/framework` 10.50.3 → 10.50.4: `composer update laravel/framework --no-security-blocking` | patch release; proves the toolchain; suite on 8.3.31, changed tests on 8.2.31 |
| U0.5 | PHPUnit 9.6 → 10.5 (+ `nunomaduro/collision` ^7): `phpunit --migrate-configuration` keeping the `_test` schema guard, 15 `@dataProvider` in 13 files → `#[DataProvider]` | 0 mocks, no other annotation, providers already static; 1,039 / 10,089 unchanged on both PHP versions |
| U0.6 | **Remove proven-unused packages** that do not depend on tables or forms (section 13, part A) | one commit per group; suite + clean-room install / build + live browser smoke tour |
| U0.7 | Readiness tests on Laravel 10 (green before any hop): `CarbonBehaviourTest` pins 4 of the 5 Carbon call sites (queue-board refresh seconds, medicine-modal days to expiry, inventory expiry badge bands, doctors' average wait); `FreshInstallSchemaShapeTest` pins all 702 columns of a fresh install; `PasswordHashStabilityTest` (a login leaves the hash unchanged). **Not in U0.7:** the cache round-trip for the 13 `Cache::` keys moves to the start of U3 (it only matters for Laravel 13's `serializable_classes`); the 5th Carbon site, `patient_dashboard/index.blade.php:173`, sits in a view that cannot render today (`patientDashboard()` passes no `$usedMedicines`, patients do not sign in) and is handled in U0.8 as dead code | so later failures point at the framework, not at us |
| U0.4 | **Tables → in-house plain Livewire 3** (details below) | the long pole; framework independent |
| U0.3 | **Forms: rewrite the 83 view files from `Form::` / `Html::` (Collective) to `spatie/laravel-html`** (`html()->…`), then `composer remove laravelcollective/html`. (1) an **equivalence harness** renders every distinct `Form::` call found in the views (264 label, 157 text, 108 hidden, 88 select, 48 close, 40 open, 31 submit, 24 textarea, 24 button, 15 number, 9 password, 8 model, 7 email, 4 checkbox) through both builders while Collective is still installed and compares the normalised HTML (attributes, `id` rules, old-input and model binding, select placeholders and selected values, `files`, method spoofing, CSRF); (2) a script converts the calls file by file, every file reviewed by hand; (3) differences the harness finds are fixed or documented (Collective only adds an `id` to a field that has a label of the same name; spatie sets `id` = name) and every JavaScript / CSS selector that relied on an id is checked under `resources/assets/js/**`; (4) link crawl + **live browser check of every converted form** as admin / staff / doctor after each module group; (5) the Collective package is removed in the same stage, with a guard test (no `Form::` / `Html::` left) | no fork to trust; spatie 3.13 runs on Laravel 10 – 13 |
| U0.8 | **Post-removal sweep and legacy → new** (section 13, part B): Tailwind, `resources/js/app.js` and `bootstrap.js`, turbo leftovers, dead-code candidates, `config/app.php` providers / aliases, Livewire `$listeners` → `#[On]` | runs after U0.4 and U0.3 because they free these items |

#### U0.4 in detail

- **Contract** (the API actually used, finding C10): keep the class `App\Livewire\LivewireTableComponent` and every child's name and namespace, so the 36 `<livewire:…>` / `@livewire` uses in 31 views do not change.
  The new base is plain `Livewire\Component` + `WithPagination`, `#[Lazy]` (placeholder `livewire.loading_skeleton`), with public `$search`, `sortBy($field)` (asc → desc → none), `gotoPage`, `getRows()`,
  `getComputedPageName()`, `builder()`, `$perPage` (10 / 25 / 50, default 10, search debounce 500 ms), `$emptyMessage`, `$showFilterOnHeader` / `$FilterComponent` / `$showButtonOnHeader` / `$buttonComponent`,
  listeners `refresh` / `resetPage` / `changeFilter` / `changeStatusFilter`; config methods `setPrimaryKey, setThAttributes, setTdAttributes, setTableAttributes, setDefaultSort, setEmptyMessage, setBuilder,
  setAdditionalSelects, setColumnSelectStatus, setQueryStringStatus, resetPage, resetPagination`; a new `App\Support\Tables\Column` with `make, label, sortable, searchable (+ callback), view, format, html, hideIf`.
  The two existing concerns become plain query steps: **`SearchesByWords`** (via `App\Support\SearchTerm`, per-column callbacks get one word) and **`SortsByUniqueKey`** (ORDER BY always ends with the model key).
1. **U0.4a golden master on rappasoft first:** a data-driven test (reflection over the 26 components) with a fixed seed: page 1 / 2, sorted asc / desc, searched, per-page 25 → JSON fixtures of headers + per-row cell text + order under `tests/Fixtures/tables/`; green before anything is touched. Existing nets: `ListPagingTest`, `ListQueryCountTest`, `LinkCrawlTest` (loads lazy tables), the many `*PassATest` list tests.
2. **U0.4b the new base next to the old one:** `resources/views/livewire/tables/table.blade.php` reusing the customised markup (table, toolbar with search field + per-page dropdown + header filter / button slots, the Bootstrap-5 pagination view); unit tests for every behaviour and **mutation checks** (word search, tie-break, per-page clamp, lazy placeholder, empty message, refresh, header slots).
3. **U0.4c convert in batches** (each: golden master + the three nets + **live browser check of every table in the batch**): (i) lookups: Barangay, City, Countries, Province, State, Specialization, Slider, Role, MedicineCategory(+Details), MedicineGeneric(+Details); (ii) inventory: Medicine, StockIn, StockOut, UsedMedicine, MedicineAvailability, MedicineDispense; (iii) people: Staff, Doctor, Patient; (iv) clinical: Prescription, PrescriptionVerification, **DocumentIssuance and LabRequest (own markup today)**, MedsOnly.
4. **U0.4d remove:** `composer remove rappasoft/laravel-livewire-tables --no-security-blocking` (and `blade-ui-kit/blade-heroicons` if nothing else uses it); delete `resources/views/vendor/livewire-tables/**` (59 views), `config/livewire-tables.php`, the 3 `.copy` lines in `webpack.mix.js`, the import in `resources/js/app.js`, the 4 tags in `layouts/app.blade.php`; drop the skip in `ViewThemeClassesTest`; update `AGENTS.md` and **both** `livewire-development` skill copies; guard test: nothing extends or imports `Rappasoft\…`; `optimize:clear`; **fresh clone → `composer install --no-dev` → `npm ci && npm run prod` → suite** to prove no stale build hides a gap.

### U1: Laravel 11.57 (PHP 8.2)
`laravel/framework ^11.57`, `spatie/laravel-permission ^6.25` (+ rename C1, check C2), `spatie/laravel-medialibrary ^11.20` (default-config diff C17, collection and `registerMediaCollections` API per its upgrade guide),
`mariuzzo/laravel-js-localization ^2.1` (config + regenerated `messages.js`, diff reviewed), `nunomaduro/collision ^8`, remove `doctrine/dbal`, add `InvokeDeferredCallbacks` (C8),
restate the legacy float / `->change()` migrations (C4, C14) so a fresh install keeps the clinic's column types (proof: `--compare-fresh` exit 0). PHPUnit 10.5 stays; Carbon 2.x is still allowed. Live browser check: full tour.

### U2: Laravel 12.70 (PHP 8.2) — **the stop point**
`laravel/framework ^12.70`; **Carbon 3** (`nesbot/carbon ^3.8.4`) with the fixes proven by the U0.7 tests (C3); image validation (our uploads use `mimes:` lists, no effect expected), `Schema::getTableListing`,
the `local` disk root (the app owns `config/filesystems.php`, no change expected); remove every `--no-security-blocking`, `composer audit` clean; `AGENTS.md` section 3 and both skill copies updated to "Laravel 12.x, PHP 8.2";
optional **U2c: PHPUnit 11.5** as its own commit. Live browser check: full tour. **The work stops here until the owner says to continue.** Security fixes run to 2027-02-24.

### U3: Laravel 13.39 (PHP ≥ 8.3 on the clinic PC) — **on hold until the owner says so**
Prerequisite: PHP 8.3 (or 8.4) installed on the clinic PC with the same extensions (`pdo_mysql`, `mbstring`, `intl`, `gd`, `zip`, `fileinfo`, `openssl`, `sodium`, `curl`),
`composer.json` `php` → `^8.3` and the platform pin → `8.3.0`. Then `laravel/framework ^13.39`, `laravel/tinker ^3.0`, `barryvdh/laravel-debugbar ^4`,
`spatie/laravel-ignition ^2.12`, `spatie/laravel-medialibrary ^11.23`, PHPUnit **^11.5.50 (PHP ≥ 8.2) or ^12.5.8 (PHP ≥ 8.3)**,
CSRF rename (C6), cache prefix / cookie names pinned (C7), `serializable_classes` decision (C5), MySQL `DELETE JOIN` check (C11), the container now injects `null` for a nullable class parameter with a default (scanned: no such container-resolved parameter, so no expected effect), Bootstrap-3 pagination view names if referenced.

### U4 (optional, separate decisions, not part of this upgrade)
slim `bootstrap/app.php` structure; Vite instead of Mix; Livewire 4; Pest. The owner confirmed on 2026-10-30 that these stay **out** of this upgrade; each one only on his request.

### Progress

| Stage | State |
|---|---|
| U0.1 baseline | **done 2026-10-30** (tag `pre-laravel-13-upgrade` local; `route:list`, matrix, lock-file copies; `db:integrity --compare-fresh` exit 0) |
| U0.2 Laravel 10.50.4 | not started |
| U0.5 PHPUnit 10.5 | not started |
| U0.6 unused packages (part A) | not started |
| U0.7 readiness tests | not started |
| U0.4 in-house tables (replaces rappasoft) | not started |
| U0.3 forms → spatie/laravel-html | not started |
| U0.8 sweep and legacy → new (part B) | not started |
| U1 Laravel 11 | not started |
| U2 Laravel 12 (**stop point**) | not started |
| U3 Laravel 13 | **on hold (owner's go needed)** |

## 6. Gates (every stage, in this order)

1. **Test-first for anything we change**; the stage's readiness tests are green before the hop and stay green after it.
2. **Full suite with `LINK_CRAWL=1`** on **PHP 8.2.31 and 8.3.31** (8.4 once at the end): all green, none skipped, the 1,039 + new numbers recorded.
3. **Route contract:** `route:list --json` diff against the U0.1 snapshot (only intended changes), `panel-routes.json` pinned test unchanged, no duplicate names.
4. **Permission matrix** (route × role, `MATRIX_SAME_USER=1`): 0 server errors, no new LEAK / OVER lines beyond the known 5 + 20.
5. **Real-dump rehearsal on a scratch schema** (never `norsu_clinic`): migrate the clinic dump under the new framework, a second `migrate` does nothing, `db:integrity --compare-fresh` exit 0 with 57 keys (this is what catches the `->change()` differences), `inventory:reconcile` consistent.
6. **Live browser check (mandatory, always; section 12):** built-in browser, admin / staff / doctor, `localhost` and a second LAN address, on a scratch schema: console clean, no 4xx / 5xx and no request leaving the LAN, every changed page and the interactions of section 12; sign in / out, dashboards, every table (filters, sort, pagination, per-page, search, lazy tabs), every form, consultation → prescription → dispensing, Meds Only, count sheets, Reports (screen, PDF, Excel, CSV), uploads (profile, logo, consultation photo), impersonation, language switch, Reverb live updates and the "Live updates off" check, notifications. A failed live check blocks the commit and the push even when the suite is green.
7. **Exports and files:** a PDF and an Excel of each kind byte-compared in content (text and numbers) with the pre-upgrade output; uploads written, read, deleted.
8. **Ops:** `php artisan optimize` / caches, `schedule:list`, one real `schedule:test` run of the 6 PM backup + a restore of that dump into a scratch schema, `startup-dev-environment.ps1 -CheckOnly` and a real start, Windows firewall report.
9. **Security:** `composer audit` (target: 0 advisories at the end of U3; recorded at every stage), `composer validate`, `check-platform-reqs`, no `APP_DEBUG` leak, error log empty after the browser pass.
10. **Mutation checks** on every guard we add or change (the existing runner), survivors become tests.
11. **Records:** plan block, STATUS, Handover START → END with commit hashes, `docs/commands.md` only if a command changes, memory notes; **push `changes_v2` or the upgrade branch only as decided in section 9**.

## 7. Risk register

| # | Risk | Likelihood / impact | Mitigation |
|---|---|---|---|
| R1 | PHP 8.3 cannot be installed on the clinic PC | medium / blocks U3 only | U1 and U2 run on 8.2; stop at 12.70 (secure until 2027-02-24); ask now |
| R2 | The forms rewrite changes markup or behaviour somewhere among 83 files (ids, old-input, model binding, selected values, file uploads, hidden fields, JavaScript selectors) | medium / **high** (a form that does not save) | the equivalence harness before conversion; every converted file reviewed; link crawl; **live browser check** of every form as every role; the existing form-posting tests stay green; no fork is introduced |
| R3 | The in-house table base differs from rappasoft's behaviour somewhere (search, sorting ties, paging, lazy loading, header filters, markup) | medium / **high** (every list screen) | golden-master fixtures taken on the current tables before any change; `ListPagingTest` / `ListQueryCountTest` / link crawl; unit tests with mutation checks for the base; batch-by-batch conversion with a **live browser check** per table and role |
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
| R14 | Composer security blocking stops every `update` / `require` / `remove` on Laravel 10.x / 11.x | certain / schedule | `--no-security-blocking` on the laptop for U0 – U1 only; nothing weakened in `composer.json`; not needed at 12.70 (gate: `composer audit` clean) |
| R15 | Laravel 11 changes `->change()` (and `float()`) and a fresh install gets other column definitions than the clinic DB | low / medium (a fresh install today has only `double` / `decimal` floats) | whole-schema fixture test (U0.7), restate any legacy migration it flags (U1), `db:integrity --compare-fresh` exit 0 |
| R16 | Removing a package that looks unused breaks something hidden (theme bundles supply `Handlebars`, `flatpickr`, `DataTable`; Tailwind may style more than rappasoft's views) | medium / medium | evidence per item (section 13), proof again at removal time (grep, clean-room build, browser globals, before / after screenshots), one commit per group |
| R17 | The in-house tables and spatie forms are new code that nobody else maintains | low / medium | tests with mutation checks, golden master and equivalence harness stay in the suite; the code is small and follows the app's own conventions |

## 8. Rollback and deployment

- **Rollback of a stage:** no stage changes the database schema (no new migration is needed by any hop; package migrations are not re-published), so rollback is code only:
  `git revert` / checkout of the previous tag **and** the pre-stage copy of `vendor/`, then `php artisan optimize:clear`. The DB backup taken at the start of the stage is the safety net, not the plan.
- **Deployment of a stage on the clinic PC (owner or with help):** stop the server window → `php artisan db:backup` and copy the file off the PC → copy the project folder → pull the tagged commit → (U3 only: switch the PC to PHP 8.3 first) → `composer install --no-dev --optimize-autoloader` (needs internet once, or ship `vendor/` from the laptop) → `php artisan optimize:clear` → start with `startup-dev-environment.bat` → a short checklist: sign in, a list, a form, a PDF, the Reports tab, "Live updates" on, one backup. Users will have to sign in again if the cache / session store is cleared; the session cookie name is kept.
- **Windows startup script:** runs unchanged (it only uses `php artisan …`); re-checked in gate 8.

## 9. The owner's decisions (2026-10-30)

| # | Question | Answer |
|---|---|---|
| 1 | Target and stop points | **Laravel 13 is the target; the work STOPS at Laravel 12.70 on PHP 8.2** (also the fallback if PHP 8.3 is delayed); the owner says when to continue to 13 |
| 2 | Dependency changes (section 3 and 5) | **Approved** |
| 3 | Branch strategy | **A new branch for the upgrade: `upgrade/laravel-13`**, pushed to origin. `changes_v2` is not used for upgrade work until a finished stage is approved for merging |
| 4 | Forms | **Rewrite the 83 view files for the official `spatie/laravel-html`** (no fork) |
| 5 | PHP 8.3 on the clinic PC | **Yes**, it can be installed (and the PC can reach the internet for `composer install`); who and when is agreed before U3 |
| 6 | Feature freeze and maintenance windows | **Not decided: the owner asked for recommendations (below)** |
| 7 | Scope fence (slim structure, Vite, Livewire 4, Pest stay out) | **Confirmed** |
| 8 | Tables (changed mid-session) | **Replace `rappasoft/laravel-livewire-tables` by in-house plain Livewire 3 tables** (shared base class + one Blade partial); no table package |
| 9 | Live browser checks | **Always**: a mandatory gate for every UI-touching commit and every stage (section 12) |
| 10 | Unused packages and legacy code | **Remove proven-unused packages and move legacy code to the newer equivalents**, inside the scope fence (section 13) |

### Recommendation for question 6 (freeze and windows)

- **Freeze:** no hard freeze of `changes_v2`. The upgrade lives on its own branch, so the clinic's line keeps working. Ask for a **soft freeze only on the files the upgrade rewrites**: no new feature work on the **form views** (while U0.3 runs) and on the **table components / table views** (while U0.4 runs). Urgent fixes are always fine; they are merged from `changes_v2` into `upgrade/laravel-13` at the start of every stage (`git merge changes_v2`), so conflicts are small and found early. A full freeze is only worth it for the last day before a deployment.
- **Windows:** deploy at stage boundaries, **not** after every commit: **(a) U0** (forms and tables are visible changes, no framework change), **(b) U1 + U2 together** (Laravel 12 on PHP 8.2, the quiet middle), **(c) U3** (Laravel 13 plus the PHP 8.3 switch, the longest). Plan about **30 – 60 minutes for (a) and (b)** and **1 – 2 hours for (c)** (PHP install, `composer install`, smoke test), always **right after the 6:00 PM backup** has run (so a fresh dump exists; take another by hand and copy it off the PC) and outside clinic hours. Do (c) first as a **dry run on a copy of the clinic folder** (same PC, other port) before the real switch. Each window ends with the short smoke checklist of section 8 and a decision to keep or roll back within the window.
- **Who:** the owner (or whoever manages the clinic PC) for the PHP 8.3 install and the deployments; the preparation, the stages and the rehearsals stay on the laptop.

## 10. Order of work

U0.1 ✅ → U0.2 → U0.5 → U0.6 → U0.7 → **U0.4** (in-house tables, alone) → **U0.3** (forms) → U0.8 → *deploy U0 (owner's window)* → U1 → U2 → **stop** → *(owner's go)* → *PHP 8.3 on the clinic PC* → U3.
U0.7 comes before U0.4 and U0.3 so that every later red test is attributable; U0.4 and U0.3 touch different views and are kept as separate commits. A realistic size: U0 is the biggest block (tables and forms), U1 medium, U2 small to medium;
calendar time is set by the 10 – 19 minute full-suite runs (two PHP versions per stage) and by the owner's availability for the clinic PC, not by typing.

## 11. Evidence (reproducible, read-only)

- `composer why-not laravel/framework ^11.0 | ^12.0 | ^13.0` (PHP 8.3.31, composer.phar): the blocking packages of section 3; for 13 also `php ^8.3 but 8.2.0 is installed`.
- Packagist metadata (`repo.packagist.org/p2/<package>.json`) for every direct dependency: supported Laravel / PHP constraints, abandoned flags, release dates.
- `laravel/framework` 10.50.4 / 11.57.1 / 12.70.2 / 13.39.0 (all released 2026-10-27): PHP ^8.1 / ^8.2 / ^8.2 / ^8.3; Carbon ^2.67 / ^2.72.6 | ^3.8.4 / ^3.8.4 / ^3.8.4; PHPUnit suggested ^9.5.8|^10.0.7 / ^10.5.35|^11.3.6|^12.0.1 / ^10.5.35|^11.5.3|^12.0.1 / ^11.5.50|^12.5.8|^13.0.3.
- Laravel docs (13.x branch and 11.x / 12.x upgrade guides, Context7): PHP 8.3 minimum, dependency list, `PreventRequestForgery`, cache prefixes, `serializable_classes`, Container nullable defaults, pagination view names, MySQL `DELETE JOIN`; 11.x: Carbon 3 `diffIn*`, `->change()` modifiers, Doctrine DBAL removal, structure not required to change, `InvokeDeferredCallbacks`; 12.x: Carbon 3 required, `HasUuids`, `Schema::getTableListing`. `spatie/laravel-permission` v6 upgrade guide: middleware namespace, migrations, model signatures.
- Project scans: 832 `Form::` / `Html::` lines in 83 files; rappasoft published views 59 = 35 identical + 24 customised; 24 + 3 table components; 15 `@dataProvider` and no other PHPUnit annotation, 0 mocks; 5 `diffIn*` call sites; 3 `->change()` migrations; 13 `Cache::` writes; `Spatie\Permission\Middlewares` in 3 files; lint of all 1,108 PHP files clean on 8.2, 8.3 and 8.4 (one deprecation inside a test closure on 8.4).

## 12. Live browser checks — always (owner's standing rule)

**Rule:** no commit that changes anything a user can see or use, and no stage boundary, is "done" on green tests alone. A **live check in the built-in browser** is a mandatory gate. It also applies to "invisible" steps (framework and package hops): they get the smoke tour. A failed live check blocks the commit and the push even when the whole suite is green.

- **Setup:** the `.claude/launch.json` entry `laravel` (`php artisan serve`, `localhost:8000`) serves the branch; because `norsu_clinic` is left alone, checks run on a **scratch schema** (a rehearsal copy of the clinic dump, env overrides + guard as in the Phase 9 / 11 recipes); a `laravel-scratch` entry (port 8001) is added if needed. Logins are the QA accounts the rehearsal tooling creates on the scratch schema; passwords are never printed. Roles: admin, Staff (Nurse), doctor. Addresses: `localhost` and the laptop's LAN IP; at a deployment window the clinic PC from a second device.
- **Every check records:** the page loads (200, no blank lazy table); **no console error**; **no 4xx / 5xx** (Livewire `/livewire/update` 200, every built asset 200 — a 404 is how a removed package shows up) and **no request leaving the LAN**; a screenshot of the changed screen (kept outside the repository: the scratch schema holds real patient data); no new error in `storage/logs/laravel.log`.
- **Interactions — tables:** skeleton → rows, two-word search, sort asc / desc, per-page 10 / 25 / 50, page 2, header filters and buttons, empty message, row-actions menu, refresh after create / edit / delete, tab switching on `MedicineScreen` / `MedicineDispensingScreen`. **Forms:** render, required-field validation with old-input re-population, create, edit (model binding, selected values, Select2, date pickers, file upload, modals posted by JavaScript), delete / confirm. A quick pass at phone (375) and tablet (768) width.
- **When:** U0.2 / U0.5 / U0.6 / U0.7 → smoke tour (3 roles, every sidebar page, one table, one form, one PDF, the Live updates badge); each U0.4c batch → every table of the batch × the roles allowed, plus before / after screenshots; U0.4d → full tour after the clean-room rebuild; each U0.3 module group → every form of the group; each U0.6 / U0.8 removal → the pages that used the removed thing plus the smoke tour (login-page console first); U1 and U2 → full tour (every menu page, every table, every form once, PDF / Excel download, upload, Backups page, Live updates); before every push of the branch and at each deploy window → smoke tour from a second device and the keep / roll back decision.
- **Rules around it:** anything the live check finds that no test caught gets a failing test first, then the fix, and joins the golden-master / equivalence harness. The result (pages, roles, addresses, console / network clean, screenshots taken) is written in the stage table and in the Handover END entry.

## 13. Unused packages and legacy → new

Principle: **evidence before removal.** Every row was checked read-only on 2026-10-30 (`composer why`, grep over `app/ config/ routes/ resources/ database/ tests/`, the Mix lists); it is proven again at removal time, in its own commit, with the suite, a clean-room install / build and a live browser check. Anything not proven stays and is reported. Every `composer remove` uses `--no-security-blocking` until the framework reaches 12.70.

### Part A — U0.6 (independent of tables and forms)

| Item | Evidence | Action |
|---|---|---|
| `rap2hpoutre/laravel-log-viewer` | route and controller are gone (`LegacyLogViewerRemovedTest`); only the provider line `config/app.php:178` remains | `composer remove`, delete the provider line; `opcodesio/log-viewer` stays as the one viewer (live check as a non-admin: `/log-viewer` still refused) |
| `league/flysystem-aws-s3-v3` (+ `aws/aws-sdk-php`) | no use outside the stock `s3` disk block in `config/filesystems.php`; offline rule | `composer remove`, delete the `s3` disk block |
| `laravel/breeze`, `laravel/sail`, `barryvdh/laravel-ide-helper`, `beyondcode/laravel-query-detector` | dev-only; no use in app or tests (re-grep first) | `composer remove`; delete `_ide_helper.php` leftovers and `config/*` entries if any |
| `guzzlehttp/guzzle`, `ezyang/htmlpurifier` | no direct use in code; both are pulled in by other packages (Reverb → pusher; mews/purifier, phpspreadsheet) | drop the redundant direct requirements **only if** `composer why` still shows dependents afterwards |
| `laravel/tinker` | in `require` though it is a dev tool | move to `require-dev` (`^2.11` on Laravel 11 / 12) |
| `"version": "8.5.1"` in `composer.json` | template leftover | remove the key |
| `mockery/mockery`, `fakerphp/faker`, `tightenco/ziggy` | Mockery is needed by the framework's `artisan()->expects…`; Faker feeds factories / seeders / tests; Ziggy serves `@routes` in two layouts | **keep** (recorded so nobody removes them later) |
| npm `@hotwired/turbo` + `resources/assets/js/turbo.js` | in no Mix list and no layout: never bundled; `turbo:load` listeners and `data-turbo-eval` are inert (`meds-only.js` also listens to `DOMContentLoaded`) | remove package and file; drop the inert listeners / attributes file by file in U0.8 |
| npm `handlebars`, `flatpickr`, `bootstrap-daterangepicker` | never imported or copied by Mix; pages get them from the theme bundles | first prove in the browser (`typeof Handlebars`, `typeof flatpickr`, `$.fn.daterangepicker` on the pages that use them), then remove |
| npm `datatables` | only `mix.copy("node_modules/datatables/media/images", …)`; the `DataTable` global comes from the theme bundle | remove if no CSS refers to those images; drop the copy line |
| keep (npm) | `jquery`, `quill`, `apexcharts`, `bootstrap-datepicker`, `jsrender` (required in `custom.js`), `laravel-echo`, `pusher-js` (Reverb), `laravel-mix`, `sass`, `sass-loader`, `resolve-url-loader` | unchanged |

### Part B — U0.8 (after U0.4 and U0.3 free these) and legacy → new

| Item | Evidence / plan |
|---|---|
| Tailwind: `tailwindcss`, `@tailwindcss/postcss`, `tailwind.config.js` (v3-style `purge` key that v4 ignores), `resources/css/app.css`, the Mix `postCss` step, the `css/app.css` links in 5 layouts | exists for rappasoft's own views; the app is Bootstrap (`ViewThemeClassesTest`). After U0.4d: compare the built `css/app.css` against the classes the remaining views use and take before / after screenshots with the stylesheet disabled in the live browser; remove only when nothing visible changes, otherwise keep and document why |
| `autoprefixer`, `postcss`, `postcss-import` | no `postcss.config.js` refers to them; Mix bundles its own PostCSS | remove together with Tailwind if the proof above holds |
| `resources/js/app.js` + `resources/js/bootstrap.js` + npm `lodash` | after U0.4d `app.js` only sets the jQuery globals and loads `bootstrap.js`, whose `window.axios.defaults…` line has no `axios` behind it (probable console error on the login page, to be confirmed live); `window._` (lodash) has no known user | confirm with the console and a grep for `_.` usage; repair or delete deliberately; the guest layout keeps jQuery |
| Dead-code candidates from the tech-debt list | re-verify each (`MedicineBill` / `SaleMedicine` models, `PatientService` / `PrescriptionService`, `PatientQueueUpdated`, the `checkImpersonateUser` alias, unloaded `channels.php`, the Paytm leftovers in `VerifyCsrfToken` and `lang`, `fix:dashboard-permissions`, the unused `/api/user` token guard, stale `izitoast` / `fullcalendar` assets): remove only what grep, routes and tests prove dead; the rest stays listed |
| `config/app.php` providers and aliases | remove entries of removed packages, and manual entries for packages that already auto-discover, one at a time |
| Livewire `$listeners` → `#[On]` | 28 files use `$listeners`, 0 use `#[On]`; the table components are rewritten in U0.4 (the new base is born with attributes); convert the rest one by one with their component tests as the net, behaviour-neutral |
| Already planned elsewhere | Collective → spatie (U0.3), rappasoft → in-house (U0.4), `@dataProvider` → `#[DataProvider]` (U0.5), `Middlewares` → `Middleware` (U1), `doctrine/dbal` dropped and legacy `->change()` migrations restated (U1), Carbon 3 (U2), medialibrary 11 / js-localization 2 (U1) |
| Not included (needs a separate OK) | squashing the 138 migrations into a schema dump; the `casts()` method style; the slim `bootstrap/app.php` structure, Vite, Livewire 4, Pest (scope fence) |
