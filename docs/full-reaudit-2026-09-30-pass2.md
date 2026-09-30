# NORSUCLINIC Full Workspace Re-Audit — Pass 2 (2026-09-30)

> **Deployment context (read first):** This workspace is deployed for **local-network (LAN) sharing only — never exposed to the public internet — and must run over plain HTTP** (no TLS/HTTPS on the clinic LAN). Several findings below exist *specifically* because the code or the recommended config assumes HTTPS or internet access. Those are called out with a **[LAN/HTTP]** tag.

| | |
|---|---|
| Branch audited | `changes_v2` @ `8661955` (push target: **`changes_v2` only** — `develop` is intentionally untouched) |
| Relationship to earlier docs | A second independent full pass. It **re-verifies** the findings in `full-reaudit-2026-09-30.md` (pass 1) and adds findings that pass 1 did not cover, especially LAN/HTTP-specific ones. Pass 1 remains the detailed reference for its own IDs (C-01…L-14); this doc does not restate all of their prose. |
| Focus | hidden bugs, data loss, data corruption, broken/dead code, incorrect configuration, LAN/HTTP-only deployment correctness |
| Method | inline code reading (no sub-agents) + live reproduction on a throwaway MariaDB 10.11 database with synthetic accounts, served over HTTP the same way `php artisan serve` does |

---

## 0. How to read this report

Evidence tags: **LIVE** = reproduced end-to-end on a running copy of this commit and the result is quoted; **TOOL** = output of a tool run on this commit; **CODE** = traced line-by-line, not executed. New IDs in this pass use the **P2-** prefix so they never collide with pass 1's IDs.

Severity: **Critical** = RCE, unauthenticated data access, silent destruction/corruption of clinical or inventory data in normal use, or *the app not working at all in its intended deployment*. **High** = privilege escalation / lockout / data corruption on a plausible action / broken safety net. **Medium** = wrong results, broken feature, config hazard. **Low** = dead code, hygiene.

---

## 1. Verification environment

- `composer install --ignore-platform-req=php` (the lock file still can't install on PHP ≥ 8.4 — pass-1 **M-16**), MariaDB 10.11.
- `php artisan migrate:fresh --seed` → **PASS** (108 migrations, all seeders) — re-confirmed on a fresh DB.
- `php artisan config:cache && route:cache && view:cache` → **all PASS** (395 Blade views compile, 507 routes cache). Good: production caching works.
- Served from `public/` over HTTP; front-end assets stubbed in the git-ignored `public/js|css|assets` folders so pages render (they are built by `npm run prod`, absent here). All 34 admin pages returned 200 in pass 1.
- Everything below was done in an isolated `norsu_clinic_audit2` database with made-up accounts; it was dropped afterward and nothing was pushed.

---

## 2. Executive summary

**New this pass:** 2 Critical, 4 High, 8 Medium, 4 Low. **Plus:** all 6 Critical and the spot-checked High findings from pass 1 still reproduce on `8661955` (§4).

**Top of the list for a LAN/HTTP clinic deployment:**

1. **P2-C1 [LAN/HTTP]** — Setting `APP_ENV=production` (exactly what the project guide §13.7 tells you to do for the clinic server) makes the app **force every URL, form action and redirect to `https://`**. On the HTTP-only LAN the login form posts to `https://<ip>` and every asset/redirect points at `https://`, none of which the server answers → **the app is effectively unusable after deploy** (broken styling, login can't submit, redirects fail). (LIVE)
2. **P2-C2 [LAN/HTTP]** — **Anonymous full admin takeover on the LAN.** `MAIL_MAILER=log` (guide's setting, since there's no internet SMTP) writes the password-reset email — including the reset link and token — into `storage/logs/laravel.log`; the **publicly reachable** `/log-viewer` (pass-1 **C-05**) lets anyone read that log; the token then resets the admin password. Whole chain done with no credentials. (LIVE)
3. **P2-H1** — Renaming any role in the Roles UI rewrites its internal `name`, and every admin is instantly **locked out with 403** (the `clinic_admin` role no longer exists under that name). (LIVE)
4. **P2-H4** — Clinical free-text is **silently truncated** by undersized DB columns: prescription *advice* / *problem_description* are `varchar(100)` but validated at `max:2000`; consultation *allergies/comorbidities/maintenance* are `varchar(191)`. A 341-char advice was cut to 100 chars ("…STOP IF RASH APPEARS" lost); an allergy list lost "SULFA (anaphylaxis)". (LIVE)
5. **P2-H2** — Running `php artisan db:seed` a second time (a natural thing to do after adding reference data) **duplicates every settings row and throws on the admin user**, after which the Settings screen silently stops saving. (LIVE)

Pass-1 criticals that remain unfixed and were re-verified live this pass: **C-01** (upload → RCE), **C-03** (consultation wipes allergies), **C-05** (public log viewer), **C-06** (input purifier corrupts clinical text and locks users out of passwords with `& < >`), plus **H-01** (patient self-escalates `users.type`).

---

## 3. LAN / HTTP-only deployment findings (new focus)

### P2-C1 — Production mode forces HTTPS and takes the app offline on the HTTP LAN  **[LAN/HTTP]** · Critical · LIVE
- **Location:** `app/Providers/AppServiceProvider.php:49-57`.
  ```php
  if (config('app.env') === 'production') { URL::forceScheme('https'); }
  if (env('FORCE_HTTPS', false) || $this->isNgrokRequest()) { URL::forceScheme('https'); }
  ```
- **Evidence (LIVE):** with `APP_ENV=production`, `APP_URL=http://192.168.1.50`, served over HTTP, the login page came back with `<form method="POST" action="https://192.168.1.50:8765/login">` and every asset URL as `https://…`; after login the redirect `Location` was `https://192.168.1.50:8765/admin/dashboard`. On a LAN with no TLS, every one of those requests fails (connection refused / SSL error). The app is effectively down.
- **Why it matters here:** the project guide §13.7 explicitly instructs `APP_ENV=production` for the clinic server, and §13.7 "Security on LAN" even says *"Set `SESSION_SECURE_COOKIE=false` (HTTP-only LAN, no HTTPS needed)"* — the code contradicts the guide. `isNgrokRequest()` also trusts the client `Host` header (`str_contains($host,'ngrok')`), so any client can force HTTPS by sending that Host.
- **Fix:** drive the scheme from an explicit config flag, not `APP_ENV`. e.g. add `'force_https' => env('FORCE_HTTPS', false)` to `config/app.php` and use only that; keep `APP_ENV=production` (needed for `config:cache`, no debug pages) without forcing TLS. Remove the ngrok Host sniff. Then re-run `config:cache`.

### P2-C2 — Anonymous LAN admin takeover via log-mailed reset token + public log viewer  **[LAN/HTTP]** · Critical · LIVE
- **Chain:** `POST /forgot-password` (open to anyone) → Laravel sends the reset mail → `MAIL_MAILER=log` writes the full email (with `reset-password/<64-hex-token>?email=…`) into `storage/logs/laravel.log` → `GET /log-viewer/api/logs?file=…` (no auth — pass-1 **C-05**) returns that log text → `POST /reset-password` with the leaked token sets a new admin password.
- **Evidence (LIVE):** ran the four steps against the seeded `admin@norsuclinic.com`; step 2 returned the reset token in the log JSON (slashes escaped as `\/`), step 3 returned `302 → /login`, and logging in with the attacker-chosen password reached `/admin/dashboard`.
- **Why it matters here:** on a LAN with no internet, `MAIL_MAILER=log` is the guide's recommended setting, so reset tokens *always* land in a file that `/log-viewer` serves to unauthenticated clients. Any student/staff device on the clinic Wi-Fi can take the admin account.
- **Fix:** the single highest-value fix is closing `/log-viewer` (pass-1 **C-05**: register a `viewLogViewer` gate / `auth`+`role:clinic_admin` middleware, or remove the package and set `LOG_VIEWER_ENABLED=false`). Additionally, on a no-email deployment, disable the password-reset routes (or gate them to admin-initiated resets), since a reset link that only ever goes to a log file is not usable by real patients anyway.

### P2-M1 — Uploaded logo/favicon URLs are stored with the server's own host → broken on other LAN clients  **[LAN/HTTP]** · Medium · LIVE
- **Location:** `app/Repositories/SettingRepository.php:70-84` stores `$media->getUrl()` (an absolute URL built from `APP_URL`) into the `logo`/`favicon` setting; views render it via `asset(getAppLogo())`.
- **Evidence (LIVE):** with `APP_URL=http://localhost`, uploading a logo stored `http://localhost/uploads/1/logo.png`; `asset(getAppLogo())` then emits `http://localhost/uploads/1/logo.png` for **every** client, so a nurse's PC loads the logo from *its own* localhost and gets a broken image. On the LAN the host must be the server's IP, and if the IP changes (DHCP) all stored media URLs break. The media path is also the enumerable `uploads/{media_id}/…` because `CustomPathGenerator` is never registered (no `config/media-library.php`).
- **Fix:** store a **relative** path (`/uploads/…`) or the media id and resolve the URL at render time; register `CustomPathGenerator` (or a hashed path); pin `APP_URL` to the static LAN IP/hostname and document that changing it requires re-saving media. Related pass-1 finding: **M-09** (public disk rooted at `public/uploads`).

### P2-M2 — `intl-tel-input` util script path is never built → phone number validation is broken  **[offline asset]** · Medium · CODE
- **Location:** `resources/assets/js/custom/phone-number-country-code.js:40` and `settings/settings.js:47`: `utilsScript: "../../public/assets/js/inttel/js/utils.min.js"`. `webpack.mix.js` never copies any file to `public/assets/js/inttel/…`; the only copy in the repo is `assets/js/intl/js/utils.min.js` (a different, non-served path).
- **Impact:** without the utils script, `intl.isValidNumber()` / `getValidationError()` don't work; the blur handler indexes `errorMap[undefined]` and shows a broken/blank validation message. Phone fields never validate. Also both files call `https://ipinfo.io` for geo-IP, which is unreachable offline (dormant today because `initialCountry` is set, but it will hang the JSONP `always` callback handling on each init).
- **Fix:** copy `utils.min.js` into the built `public/` path via `webpack.mix.js` and point `utilsScript` at the real served URL; drop the `ipinfo.io` geo lookup for offline use.

### P2-M3 — Guide's `CACHE_STORE=database` has no cache table → 500 on every request  **[LAN/HTTP]** · Medium · LIVE
- **Location:** project guide §13.7 recommends `CACHE_STORE=database`; there is **no `create_cache_table` migration** in `database/migrations/`.
- **Evidence (LIVE):** setting `CACHE_STORE=database` and hitting `/login` returned **500** — `SQLSTATE[42S02]... Table 'cache' doesn't exist`. Helpers cache heavily (`getExpiringMedicinesCount`, permissions, settings), so the site is down.
- **Fix:** either keep `CACHE_STORE=file` (works, and `.env.example` already uses it) or ship Laravel's cache-table migration if database cache is really wanted. Correct the guide. (Same class of issue: `QUEUE_CONNECTION=database` in `.env.example` with no `jobs` table — latent only because nothing is queued at runtime.)

### P2-L1 — Anyone on the LAN can self-register and squat identifiers; the account can't log in  **[LAN/HTTP]** · Low→Medium · CODE
- **Location:** `routes/auth.php` exposes `GET/POST /register`; `app/Http/Controllers/Auth/RegisteredUserController.php:37-55` creates a patient with **no password** (`'password' => Hash::make(...)` is commented out, column is nullable) and `email_verified_at = null`, while the `email_verified` setting is seeded to `1`.
- **Impact:** (1) a self-registered patient can never authenticate (no password + verification required + no SMTP to verify) — a dead-end feature; (2) registration is open to any device on the clinic network and lets a stranger consume a real `university_id_number` (unique), blocking the real student's later registration. There is no rate limit on `/register`.
- **Fix:** for a staff-provisioned clinic system, remove public registration (or gate it), and never create login accounts without a usable credential.

---

## 4. Re-verification of pass-1 findings (double-check)

Re-run live on `8661955` this pass unless marked CODE:

| Pass-1 ID | Claim | Re-verified? |
|---|---|---|
| **C-01** | consultation image upload executes as PHP | ✅ LIVE — uploaded `probe.php`, anonymous GET returned `AUDIT-PROBE-42` (probe deleted) |
| **C-03** | consultation with blank allergy nulls patient allergies/comorbidities/maintenance | ✅ LIVE — `PENICILLIN (anaphylaxis)` → `NULL` |
| **C-05** | `/log-viewer` readable without login | ✅ LIVE — `GET /log-viewer/api/files` → 200 (and now weaponized in P2-C2) |
| **C-06** | `xss` middleware corrupts clinical text; password with `&`/`<`/`>` locks user out | ✅ LIVE — `My&Pass123` login fails, `My&amp;Pass123` succeeds |
| **C-02** | Livewire 3.5.4 unauthenticated RCE (CVE-2025-54068) + 97 advisories | ✅ TOOL — `composer audit` unchanged (no dependency bump landed) |
| **C-04** | deleting an in-use medicine cascades away batches/ledger/prescription lines | ⏳ CODE — code path unchanged since pass 1 (not re-exploited this pass) |
| **H-01** | patient escalates own `users.type`, then reads other patients' lab requests | ✅ LIVE — `type` 4→3 via change-password; `LabRequestTable` then leaked another patient's row |
| **H-02** | staff bypass module guard with `?module=` / `document_type=` | ✅ CODE/LIVE (pass 1) — middleware `EnsureStaffModuleAccess:66-89` unchanged |
| **H-04/H-05** | stock-in edit/delete corrupts batch ledger / expiry | ⏳ CODE — unchanged since pass 1 |
| **H-06** | patients can edit/delete own (dispensed) prescriptions | ⏳ CODE — `routes/patient.php` unchanged |
| **H-09** | scheduled backup never runs; manual backup fails on MariaDB & leaves partial file | ✅ TOOL — MariaDB `mysqldump` still rejects `--column-statistics=0`/`--set-gtid-purged=OFF` (exit 7, 0-byte file left) |
| **H-10** | AJAX 401/403/419 rendered as HTTP 500 | ✅ CODE — `Handler.php:37` still keys on `getCode()` |
| **H-11** | test suite errors out (SQLite vs MySQL-only view SQL) | ✅ TOOL — 29 errors / 1 failure, unchanged |

Nothing from pass 1 has been fixed on this branch (the only new commit was the pass-1 report itself). All pass-1 remediation items still stand.

---

## 5. New High findings

### P2-H1 — Renaming a role locks every admin out of the system · High · LIVE
- **Location:** `app/Repositories/RoleRepository.php:71-84` — `update()` derives the stored `name` from the user-entered display name: `$str = str_replace(' ', '_', strtolower($input['display_name']))`, then `$role->update(['name' => $str, ...])`. All authorization keys off `name` (`role:clinic_admin`, `hasRole('clinic_admin')`, `getDashboardURL()`).
- **Evidence (LIVE):** as admin, `PUT /admin/roles/2` with `display_name="Clinic Administrator"` (re-sending its current permissions) changed the role's `name` from `clinic_admin` to `clinic_administrator`. Immediately afterward the same admin session got **403** on `/admin/dashboard` and **403** on `/admin/roles` — i.e. no admin can even reach the Roles screen to undo it. Recovery requires DB access. (Sandbox role restored afterward.)
- **Why:** the four seeded roles are `is_default=1`, but `update()` doesn't protect the immutable `name` of default roles; it rewrites `name` on every edit, including a pure permission change.
- **Fix:** never derive/rewrite `name` for existing roles — edit only `display_name` and permissions; for default roles, forbid renaming `name` entirely.

### P2-H2 — Re-running seeders duplicates config rows and breaks Settings saves · High · LIVE
- **Location:** `database/seeders/SettingTableSeeder.php`, `DefaultUserSeeder.php:28`, `DefaultSpecializationSeeder`, etc. use plain `::create()` / `insert()` (not `firstOrCreate`/`updateOrCreate`); `DatabaseSeeder` calls them unconditionally.
- **Evidence (LIVE):** on an already-seeded DB, `php artisan db:seed` **duplicated all 26 settings rows to 41** and doubled `specializations`, then **aborted** with `UniqueConstraintViolationException: Duplicate entry 'admin@norsuclinic.com'`. After the duplication, `SettingRepository::update()` writes to `Setting::where('key',$key)->first()` (the *first* duplicate) while `SettingsService::get()` reads `Setting::pluck('value','key')` (last-wins) — so editing the clinic name saved to one row but the app kept showing the old value (LIVE: set "NORSU Main Clinic (edited)", app still showed "Norsu Clinic").
- **Impact:** an operator re-seeding after adding a lab test or diagnosis silently corrupts settings and makes the Settings page appear to "not save". Partial seed also leaves the run half-applied.
- **Fix:** make every seeder idempotent (`updateOrCreate` keyed on a stable column); add a unique index on `settings.key`.

### P2-H4 — Clinical free-text silently truncated by undersized columns · High · LIVE
- **Location (column vs. validation):**
  - `prescriptions.advice` `varchar(100)`, `prescriptions.problem_description` `varchar(100)` — validated `max:2000` (`CreatePrescriptionRequest:28-29`).
  - `document_issuances.allergies / comorbidities / admissions_surgeries / maintenance / nursing_intervention` `varchar(191)`, `lab_requests.clinical_indication` `varchar(191)`, `prescriptions_medicines.comment` `varchar(191)` — form input is unbounded/large.
- **Evidence (LIVE):** a 341-char `advice` (well under `max:2000`) was stored as **100 chars**, dropping the trailing "STOP IF RASH APPEARS." An allergy string was stored at **191 chars**, dropping "SULFA (anaphylaxis)". With `database.strict=false` (`config/database.php:59`) MySQL truncates instead of erroring, so the write "succeeds" and the clinician never sees a warning.
- **Impact:** loss of the *end* of allergy lists, advice and diagnoses — exactly where "stop if…" and additional allergens live. Silent clinical-data loss.
- **Fix:** widen these columns to `TEXT` (or match the validated max), and set `database.strict = true` so over-length writes fail loudly instead of truncating. Align every clinical field's column length with its validation rule.

### P2-H3 — Livewire action methods rely entirely on page-load guards; no per-action authorization · High(fragile)→Medium · CODE
- **Location:** `vendor/livewire/livewire/.../PersistentMiddleware.php:14-23` lists only auth-type middleware as persistent — **not** `role`, `permission`, `staff.module`, or `checkUserStatus`. So `POST /livewire/update` re-checks only that the user is logged in, not the RBAC middleware that guarded the original page. Component actions with no internal check, e.g. `app/Livewire/MedicineCategoryTable.php:107 changeStatus($id)` (toggles a category with no `@can`/policy and a null-deref if `$id` is unknown) and `PatientTable`/`DoctorTable` filter setters, are protected *only* because the least-privileged role that can render the component is already limited.
- **Impact today:** limited — a patient can't load the admin inventory page, so can't obtain that component's signed snapshot. But the pattern is fragile: any component reused on a lower-privilege page, or any future action method, is unprotected by default, and `checkUserStatus` (disabled/unverified logout) is not re-applied on Livewire updates, so a disabled user keeps acting until their session cookie expires.
- **Fix:** authorize inside each action method (`abort_unless(auth()->user()->can('manage_medicines'), 403)`), add `checkUserStatus` to Livewire's persistent middleware, and add a null guard in `changeStatus`.

---

## 6. New Medium findings

| ID | Finding | Location | Evidence | Fix |
|---|---|---|---|---|
| P2-M4 | `changeLanguage` puts an **arbitrary, unvalidated locale** from the request into the session (`languageName`); `SetLanguage` then `App::setLocale()`s it. A bad value makes every `__()` fall back to keys → UI breaks for that user until the session is cleared (self-DoS; also a stored value an attacker can set for themselves). Only `en` exists. | `Front/FrontController.php:122`, `Http/Middleware/SetLanguage.php` | CODE | validate against `array_keys(User::LANGUAGES)`; ignore unknown values |
| P2-M5 | `GenericController::destroy` **silently nulls `generic_id` on every medicine** using the generic, then deletes it — no confirmation, no "in use" block (unlike Category/Specialization which do block). Medicines lose their generic linkage without warning. | `GenericController.php:126-134` | CODE | block deletion when medicines reference it, or warn+confirm |
| P2-M6 | `MedicineCategoryTable::changeStatus` toggles a category's active flag with **no authorization check and a null-dereference** if `$id` doesn't resolve (`$category->is_active` on null). | `MedicineCategoryTable.php:107-118` | CODE | authorize + `findOrFail` |
| P2-M7 | Historical **destructive migrations** re-run on a *populated legacy* DB would lose data: `year_levels` is `truncate()`d then re-inserted with hardcoded IDs and **no remap of existing `users.year_level_id`** (`2025_10_21_200025`); 16 legacy tables incl. `visits` dropped (`2026_04_02_000000`); `campus_address`/`permanent_address` and `institutional_email` columns dropped with no copy-forward. `migrate:fresh` is unaffected; risk is only an in-place upgrade of an old DB. | migrations listed | CODE/TOOL | for any real legacy upgrade, add data-migration steps before the drops; document that these are fresh-install-only |
| P2-M8 | `config/database.php` `'strict' => false` — MySQL runs in non-strict mode, so over-length/invalid writes truncate or coerce silently (this is what makes **P2-H4** silent). | `config/database.php:59` | CODE | set `strict => true`; fix any resulting over-length columns |
| P2-M9 | Logging: default `single` channel at `debug` with **no rotation** writes everything (incl. PHI — pass-1 **L-08**) to one ever-growing `laravel.log`. On a small clinic server this fills the disk over time and there's no cap. | `config/logging.php:54-64`, `.env.example LOG_LEVEL=debug` | CODE | use the `daily` channel with retention; set `LOG_LEVEL=warning` in production; stop logging request bodies |
| P2-M10 | `config/cors.php` `allowed_origins => ['*']` on `api/*`. Low impact on a closed LAN, but combined with `/api/medicines` being reachable by any logged-in role (pass-1 **M-06**) it's needless exposure. | `config/cors.php:22` | CODE | restrict to the app's own origin, or drop CORS (no cross-origin clients on LAN) |
| P2-M11 | `php artisan serve` (the clinic startup scripts' launcher) is **single-threaded** — it serializes requests. LIVE: 8 concurrent `/admin/patients` requests (8 clinic PCs) finished in a 2.8 s staircase (0.46 s → 2.77 s) vs 0.4 s served alone. Fine for the built-in dev server but the guide's own §13.7 recommends Nginx/Apache; the startup scripts contradict it by using `artisan serve`. | `startup-dev-environment.*`, guide §13.7 | LIVE | deploy behind Nginx/Apache + PHP-FPM (or `php artisan octane`), not `artisan serve`, for multi-user LAN |

---

## 7. New Low findings

| ID | Finding | Location |
|---|---|---|
| P2-L2 | `.env.example` mixes Laravel-11 keys into this Laravel-10 app (`CACHE_STORE`, `SESSION_ENCRYPT`, `APP_MAINTENANCE_DRIVER`, `MAIL_SCHEME`, `BCRYPT_ROUNDS`), and ships `APP_DEBUG=true` — a stray `APP_ENV=local` deploy would expose stack traces on the LAN. | `.env.example` |
| P2-L3 | `getBadgeColor()` reads `Auth::user()->dark_mode` with no null guard; any Blade using it on a guest/CLI context fatals. `getBadgeStatusColor()`/`getStatusClassName()` index fixed arrays by a raw `$status`/`$index` with no bounds check. | `app/helpers.php` (badge helpers) |
| P2-L4 | Two `patient_queue/*_backup.blade.php` and `doctor_view_polling_backup.blade.php` views are dead (not routed) and still call `new Notification()`/`Notification.requestPermission()` (Web Notifications need a secure context — won't work on HTTP anyway) and a non-existent `/api/patient-queue/data`. Dead code + LAN/HTTP mismatch. | `resources/views/patient_queue/*backup*.blade.php` |
| P2-L5 | `SESSION_SECURE_COOKIE` unset (defaults false) is correct for HTTP LAN, but should be **explicitly** `false` in the deployed `.env` so a future copy-paste of a "secure" template doesn't silently break sessions over HTTP. Document it. | `.env.example`, guide §13.7 |

---

## 8. What is actually correct (verified, so it isn't re-flagged)

To show coverage, these were checked and found sound:

- **CSRF:** every raw `POST` Blade form carries `@csrf`/`_token` (0 missing across 395 views); jQuery `$.ajaxSetup` and all `fetch()` write calls send `X-CSRF-TOKEN`. No offline-broken CDN/Google-Fonts references in views (OFFLINE_RULES.md is honored there).
- **Location deletes** (`countries/states/cities/barangays`) correctly block on child rows and on `addresses` before deleting, so the `ON DELETE CASCADE` chain can't silently wipe geography.
- **Notifications:** `readNotification` is owner-checked (`abort_unless($notification->user_id === getLogInUserId())`).
- **Migrations + seeders** run clean on a fresh DB; **route/config/view caching** all succeed (so a cached production build boots).
- **Staff module guard** covers 124/124 `staff/*` routes; **designation↔station pairing** is enforced at validation.
- Password reset uses a hashed token table; login is rate-limited (`LoginRequest::ensureIsNotRateLimited`, 5/min).

---

## 9. Suggested fix order for the LAN/HTTP deployment

1. **Make it run at all & lock the front door (today):** P2-C1 (don't force HTTPS in production), P2-C2 + pass-1 C-05 (close `/log-viewer`; disable password-reset routes on a no-email box), pass-1 C-01 (uploads off the web root + type validation), P2-M3 (`CACHE_STORE=file` or add the cache table).
2. **Stop silent data loss:** P2-H4 + P2-M8 (widen clinical columns, `strict=true`), pass-1 C-03 (don't null patient history on blank fields), pass-1 C-06 (drop the input purifier), pass-1 C-04 / H-04 / H-05 (medicine & inventory).
3. **Stop lockouts & escalation:** P2-H1 (role rename), P2-H2 (idempotent seeders + unique `settings.key`), pass-1 H-01 (patient `type` mass-assign), P2-H3 (per-action authz).
4. **Config & deploy hygiene:** P2-M1/M2/M9/M10/M11, P2-L2/L4/L5; deploy behind Nginx/Apache, not `artisan serve`; rotate logs; pin `APP_URL` to the static LAN IP.
5. **Dependencies & tests:** pass-1 C-02 (upgrade Livewire/dompdf/phpspreadsheet/Laravel), H-11 (get the test suite running on MySQL) and add regressions for the items above.

---

## 10. Reproduction notes

All against a throwaway `norsu_clinic_audit2` DB with synthetic accounts, served over HTTP; dropped afterward; nothing pushed.

- `APP_ENV=production` + HTTP → login form action and post-login redirect are `https://…` (P2-C1).
- `POST /forgot-password` → token appears in `storage/logs/laravel.log` → readable via anonymous `GET /log-viewer/api/logs` → `POST /reset-password` → admin login with new password (P2-C2).
- `PUT /admin/roles/{clinic_admin}` with a new display name → role `name` changes → admin `GET /admin/dashboard` returns 403 (P2-H1).
- Second `php artisan db:seed` on a seeded DB → settings 26→41 rows, aborts on duplicate admin email; later Settings edits don't take effect (P2-H2).
- 341-char `advice` stored as 100 chars; allergy list truncated at 191 chars dropping "SULFA" (P2-H4).
- `CACHE_STORE=database` with no cache table → `GET /login` returns 500 (P2-M3).
- Uploaded logo stored as `http://localhost/uploads/1/logo.png` and rendered verbatim to all clients (P2-M1).
