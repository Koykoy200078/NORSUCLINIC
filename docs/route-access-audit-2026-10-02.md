# Route / access / data audit - 2026-10-02

Asked for: "double check again and again" the routes and module access for the admin, staff/nurse and doctor, look for
bugs, data loss and corrupted code, find out why the administrator got **403 "User does not have the right permissions"**
on `/admin/dashboard`, and finish the remaining items of the re-audit (`full-reaudit-2026-10-01.md`).
Worked inline (no sub-agents), scripts + tests, nothing guessed.

## 1. The administrator's 403 (root cause: my own live-check commands)

The admin's role and permissions were correct in the database. The **cached** permission list was not: Spatie keeps the
permission table in the file cache (`spatie.permission.cache`), and when I loaded test data with `artisan db:seed` against the
test schema (`norsu_clinic_test`) the command used the **same cache folder** as the real app and overwrote that entry
with the test schema's permission ids (15-24 instead of 5-14). The real admin holds ids 5-14, so
`manage_admin_dashboard` (13 in the real table, 23 in the cache) never matched -> 403. Ids 1-4 matched, which is why only the
admin-only permissions failed.

* Fixed now: `php artisan permission:cache-reset` + `cache:clear`. The admin can open the dashboard again.
* Fixed for good: `config/cache.php` gives every database **its own cache folder**
  (`storage/framework/cache/data/<DB_DATABASE>`), so a command run against another schema (a test copy, a restored
  backup) can no longer poison the cache of the real one.
* Nothing in the real database was touched by the live checks (verified: no test users, no test consultations).

## 2. How it was checked

| Check | Result |
|---|---|
| PHP syntax, all 525 PHP files | 0 errors |
| All 342 Blade views compile | 0 errors |
| UTF-8 / BOM / null bytes / merge markers / mixed line endings / truncated files (956 files) | none (5 empty placeholder files, harmless) |
| Every route's controller, method and middleware alias exists (472 routes) | all exist |
| **Every route x every kind of user** - guest, admin, doctor, patient, deactivated doctor, 12 staff designation+station pairings, 2 nurse-role accounts: **3,975 requests**, each in a rolled-back transaction | see section 3 |
| Every link on each role's dashboard / sidebar opened as that role (160 links, 16 roles) | all open (200/302); no dead link, no 403 |
| Every staff pairing x the routes that choose their module from the request (consultations vs certificates, activity-log tabs) | now matches the policy exactly (`StaffModuleRoutingTest`) |
| Read-only integrity sweep of the real database (accounts vs roles, staff profiles, permissions, stock vs batches, orphan rows, settings) | **no problems found** |
| Risky patterns (raw SQL with variables, unescaped output, `eval`/`exec`, GET routes that change data) | none that matter (2 harmless GETs: `update-dark-mode`, `impersonate-leave`) |

Result of the 3,975-request matrix **after** the fixes below: 0 server errors, 0 places where a role could reach another
role's pages, 0 differences from the staff designation/station policy. A deactivated account is sent to the login page everywhere.

## 3. Bugs found and fixed (each has a regression test)

| # | Finding | Fix |
|---|---|---|
| 1 | **`staff.module:a,b` only checked `a`.** Laravel splits middleware parameters at the comma, `EnsureStaffModuleAccess` accepted one parameter, so the second module was ignored. Front-desk / records staff (certificates) could not search for a patient when issuing a certificate (403); the pharmacist could not load the medicine list; a consultations-only nurse could not read a patient's latest consultation. (The R3-M1 fix had the same hole.) | the middleware takes every listed module (any one is enough) |
| 2 | `/medical-doctors` (public page) crashed for everybody when a doctor had no specialisation | null-safe view |
| 3 | `?module=certificates` (plural) showed the consultation list and answered 403 to certificates-only staff; only `certificate` worked | both spellings accepted |
| 4 | Email-verification and password-confirmation pages sent doctors / staff to the admin dashboard (403) | each role goes to its own dashboard |
| 5 | Saving Settings without a section -> server error | validated (422) |
| 6 | Settings > General could not be saved at all (validator wanted a `language` field the form no longer has) | fixed earlier today (accomplishment report work) |
| 7 | `?section=` in Settings was put into a view name unchecked | only the real pages, otherwise 404 |
| 8 | A password change that failed validation flashed the typed passwords into the session | never flashed |
| 9 | Lab-request PDF: a `/` in the patient name made the download fail with a server error | file name sanitised |
| 10 | `getBadgeColor()` crashed with nobody signed in | null-safe |
| 11 | Doctor update mass-assigned the whole request (password, verified flag, dark mode ...) and could delete **any** doctor's qualification | only the doctor form's columns; only the doctor's own qualifications |

## 4. Re-audit items finished in this pass

| ID | Status | What changed |
|---|---|---|
| R3-M4 | fixed | Logo / favicon are shown through the address the visitor used, so they no longer point at `localhost` on other PCs |
| R3-M5 | fixed | Administrative changes now leave a trail line (`AuditAdministrativeActions`, written after the response): accounts, roles/permissions, settings & report lists, backups, password resets and changes, master data (medicines, categories, locations, specialisations), manual dispense records, own profile; plus sign-in / sign-out and impersonation start/stop. Field **names** only, never values; failed attempts are not logged as changes; every row made while impersonating carries `impersonated_by` |
| R3-M6 | fixed for clinical records | Deleting a consultation / certificate / excuse slip now sets `deleted_at` (migration `2026_10_02_130000`): it disappears from lists, search, reports and the patient history, but the row **and its photos** stay and it can be restored. Lab requests were already limited to pending/cancelled with an audit snapshot |
| R3-M7 | fixed | `db:seed` no longer wipes roles or gives users private copies of the defaults: each default role->permission pair is applied once (remembered in settings key `default_role_permissions_applied`); what the administrator revoked stays revoked, new defaults are added once |
| R3-M11 | fixed | A doctor / staff / nurse who still has the default password `123456` can open nothing but their dashboard (same as the administrator) until they change it; AJAX/Livewire calls and impersonation are not blocked |
| R3-L1, L8, L12, L13, L14, L16 | fixed | see section 3 and `webpack.mix.js` below |
| R3-L9 | fixed | `npm run prod` no longer deletes the tracked `public/webfonts` and `public/messages.js` before building |
| R3-L3 | by design | a role must keep at least one permission (the form validates it) |
| R3-M9, R3-L10, L11, L15, L17, L19 | still open (deployment / documentation) | serve through Apache instead of `artisan serve`; stale guides; backup retention; session lifetime |
| R3-L5, L6, L7 | still open (design / dead code) | stock from the medicine form bypasses Stock-In; prescription duplicate check by medicine id; dead views/commands |

## 5. What you have to do

```
php artisan migrate       # adds deleted_at to document_issuances (already applied on the copy used for this audit)
```
* Restart the server through the startup script (it clears and rebuilds the caches). Until then a running server may still hold the old routes.
* Doctors and staff whose password is still `123456` will be sent to their dashboard and must change it - tell them.
* After a re-seed nothing needs to be re-granted any more.
* Do **not** run `artisan` commands with `DB_DATABASE=norsu_clinic_test` and the file cache on the same machine without this fix: it is now safe, because the cache folder follows the database.

## 6. Tests added

`RouteAccessAuditTest`, `StaffModuleRoutingTest`, `RolePermissionsSeederTest`, `DefaultPasswordGateTest`, `LogoUrlTest`,
`AuditTrailTest`, `ClinicalRecordRetentionTest`, `LowSeverityAuditTest`. Suite: **274 tests, all passing**.
