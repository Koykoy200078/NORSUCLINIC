# Deployment runbook: the Laravel 13 upgrade (U0 + U1 + U2 + U3, one window) to the clinic PC

Written 2026-11-05 for Laravel 12; **rewritten 2026-11-06 after the owner decided to deploy U0 to U3 together, in one window** (the file keeps its
old name so the links to it keep working). It extends [STATUS.md](STATUS.md) section 7 and
[laravel-13-upgrade-plan.md](laravel-13-upgrade-plan.md) section 8; it does not replace them. **Nothing in this file has been run on the
clinic PC.** Everything below was checked on the development laptop only.

## 0. Read this first

- **Owner's decision, 2026-11-14: the clinic server PC is updated by `git pull`; no zip bundle and no new deploy tag are made any more.** The
  bundles that used to be built on the laptop were deleted the same day. Git does not hold `vendor\` or the built scripts and styles, so after
  the pull the PC makes them itself (Composer, Node 22): section 2.2. The tags already pushed stay, as fixed points to check out.
- What the U0 – U3 window deploys: the tag **`laravel-13.42-excel4-ready`** (`bcf7ad7`, 2026-11-13, with Laravel Excel 4) on the branch
  **`changes_v3`** (the former `upgrade/laravel-13`, renamed on 2026-11-12; `changes_v2` stays at the clinic's current code, `71e43ae`, plus a
  docs-only commit `07aa585` pointing to `changes_v3`): **Laravel 13.42**, Laravel Excel 4 with PhpSpreadsheet 5, Guzzle 8, Carbon 3, PHPUnit 12.5,
  Symfony 7.4, in-house list tables, `spatie/laravel-html` forms, Livewire 3.8, permission 6, medialibrary 11. **The clinic PC must run PHP 8.3 or
  newer** (the Laravel 13 `vendor\` refuses PHP 8.2 with a clear message): install it beforehand, section 2.0. The upgrade stages add **no
  migration** and change **no `.env` key**. (The older tags `laravel-13.42-ready`, Laravel Excel 3.1, and `laravel-13-ready`, Laravel 13.40, are
  earlier checkpoints of the same work.)
- **Later, in its own window: U4 (Laravel Mix → Vite) and everything after it, the tip of `changes_v3`** (`git pull`, section 9): the asset
  build `build-assets.mjs` on Vite 8 (Laravel Mix gone), the 2026-11-13 module audit, the DRY pairs pass and all the owner's answers of
  2026-11-14 (STATUS, the 2026-11-14 paragraphs). No migration and no PHP package change after `laravel-13.42-excel4-ready`; 513 routes; one
  optional `.env` key (`SESSION_EXPIRE_ON_CLOSE`, true when it is missing). **Not in the U0 – U3 window** (owner's decision 13): run section 9
  on another day, after that window was kept.
- **Owner's decision, 2026-11-06: U0 to U3 go out together in one window** (not U0 to U2 first). The fallback if PHP 8.3 cannot be installed in
  time is the commit `3c8c434` (Laravel 12.71 with the Laravel 13 groundwork, runs on PHP 8.2); section 8 says how (check it out and run
  section 2.2 on PHP 8.2).
- **Owner's answer, 2026-11-05: the clinic PC runs `changes_v2`, so this is Case A (code only).** Section 3 step 0 proves it on the PC before
  anything is changed; if it says otherwise, stop and treat it as Case B. The text for Case B stays below only as the fallback.
- **The size of the jump depends on what the clinic PC runs today, and the repository cannot say.** `develop` stopped on 2025-09-27
  (Laravel 10.42); `changes_v2` is 246 commits and 81 new migrations ahead of it, and STATUS section 7 still lists the clinic
  deployment of `changes_v2` as *to do*. So there are two cases:

| | Case A: the clinic already runs `changes_v2` (tip `71e43ae`) | Case B: the clinic runs older code (`develop` era) |
|---|---|---|
| Code | 33 commits (tables, forms, cleanup and the framework hops 10 → 11 → 12 → 13) | 246 + 33 commits |
| Database | `php artisan migrate` says "Nothing to migrate" | **81 migrations**: MyISAM to InnoDB, zero dates, foreign keys, backfills (STATUS section 7, steps 3 to 3h) |
| Window | 60 to 90 minutes (one hop from Laravel 10 to 13, plus the PHP switch) | 2.5 hours or more (set it from the rehearsal time) |
| Rehearsal on a copy of the real data | strongly recommended | **required** |
| Rollback | restore the folder copy | restore the folder copy **and** the database backup |

- **Found out the hard way during the upgrade (so it is in the steps below):** a stale `bootstrap/cache/packages.php` stops the site from
  booting after `vendor/` changes; `npm run prod` wipes `public/assets, backend, css, fonts, js, vendor, web, mix-manifest.json`
  (never `public/uploads`); Node 26 breaks the build (use Node 22); a browser tab opened before the deployment fails on its next click
  until it is reloaded. **From Laravel 13:** cache files written by the old code hold Collection objects that Laravel 13 will not read back, so
  `php artisan optimize:clear` is required (step 10), and the Windows firewall rule of the startup script follows the path of `php.exe`, so a new
  PHP folder needs its rule again (step 11).

## 1. Decisions needed from the owner (before a date is set)

| # | Question | Recommendation |
|---|---|---|
| 1 | What does the clinic PC run now? | **Answered: `changes_v2` (Case A).** Proved again on the PC in section 3 step 0. |
| 2 | Window: date, start time, who runs it, who is on the phone | After the 6:00 PM backup has run and the clinic is closed; Case B on a Friday evening or a weekend, so Monday is not the first test. |
| 3 | How the code reaches the PC | **Decided 2026-11-12 by the owner:** the code is on the branch **`changes_v3`** with the tag `laravel-13.42-excel4-ready` (since 2026-11-13, with Laravel Excel 4; `laravel-13.42-ready` and `laravel-13-ready` are the earlier, still valid pairs) (all pushed); `changes_v2` stays at the clinic's code `71e43ae` plus a docs-only pointer commit `07aa585` (it was merged that day and put back on his word). In the window the PC checks out the tag (step 5); until then **nobody checks out `changes_v3` on the clinic PC** (it needs PHP 8.3). The PC then runs `git pull --ff-only` on `changes_v2`. Merging into `develop` stays a separate pull request (STATUS section 6). |
| 4 | Does the clinic PC have internet and Node 22 for the window? | **Answered 2026-11-14 by the owner: the PC is updated by `git pull`** (it reaches GitHub), so no bundle is carried. Section 2.2 also needs **Composer** and **Node 22** on the PC: check both in section 2.3, before the window. |
| 5 | A fresh backup of the clinic database on the laptop, for the rehearsal | **Answered 2026-11-05: the owner skips the rehearsal.** The first run of Laravel 13 on the clinic's real records is therefore the window itself: keep the rollback copy (step 4) and the cut-off time (step 13) strict, and run the whole smoke checklist. Section 2.1 stays as the optional procedure if this changes. |
| 6 | **U0 to U3 in one window, or U0 to U2 first?** | **Answered 2026-11-06: together, in one window.** So PHP 8.3 must be installed on the PC before the window (section 2.0), and the window is the first run of Laravel 13 on the clinic's real records. |
| 7 | Who installs PHP 8.3 on the clinic PC, and when? | **Open.** Any day before the window; it goes next to PHP 8.2 and nothing is switched until step 5.5 of the window. |

## 2. Before the window (on the laptop)

### 2.0 PHP 8.3 on the clinic PC (the owner's side; any day before the window, nothing is switched yet)

1. Install **PHP 8.3 (8.3.36 or newer; 8.4 and 8.5 also work)** in WampServer next to 8.2, with the same extensions as in section 2.3
   (`pdo_mysql, mbstring, intl, gd, zip, fileinfo, openssl, sodium, curl`). Check `php -m` of the new version. Do **not** make it the active
   version yet: the old code needs 8.2 until step 5.5 of the window.
2. **The firewall rule follows the program.** The startup script puts `php` from the Windows PATH (or WampServer's active PHP) on the
   LAN firewall rule by its file path. A new PHP folder is a new `php.exe`: the old rule does not cover it, so other devices are blocked until the
   script has added its rule. After the switch, start the script and check that the firewall line says `[ok]` and a second device opens the
   page; if not, run it as administrator once.
3. Which PHP the startup script uses (its header): the `php` found in the Windows PATH when `mysql` is there too, otherwise WampServer's
   active PHP version (`wampmanager.conf`), otherwise its newest. So "switching" means changing the PATH entry or WampServer's active
   version, and `php -v` in a **new** terminal must then say 8.3.x. If Apache serves the site instead of `php artisan serve`, switch Apache's
   PHP version too.

### 2.1 Rehearsal on a copy of the real data (optional: the owner skipped it on 2026-11-05)

The real-dump rehearsal of the upgrade is still **not done** (the clinic dump is not on the laptop). The laptop's `norsu_clinic` currently holds 69
empty tables (found 2026-11-06), so the `CREATE DATABASE` below fails: use another name (for example `norsu_clinic_rehearsal` and
`DB_DATABASE=norsu_clinic_rehearsal` in the process environment, never in the committed files) or drop the empty one on the owner's say-so. Do it with a
backup taken on the clinic PC (`php artisan db:backup`, or Settings > Backups > Download):

```powershell
# in the project folder on the laptop; mysql.exe is in C:\Program Files\MySQL\MySQL Server 9.7\bin
mysql -u root -p -e "CREATE DATABASE norsu_clinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"   # fails if it exists: stop and look
cmd /c "mysql -u root -p norsu_clinic < C:\path\to\clinic-backup.sql"      # asks for the password; nothing is printed
git checkout laravel-13.42-excel4-ready   # the tag on branch changes_v3
composer install                       # normal install, no --no-scripts
php artisan db:backup                  # safety copy of the imported data
Measure-Command { php artisan migrate --force }      # write down the time and every warning line
php artisan migrate --force            # must say "Nothing to migrate"
php artisan inventory:recover-orphan-batches         # dry run first (STATUS 3a), then --apply
php artisan db:integrity --compare-fresh             # expected: 0 errors; the duplicate-medicine and phone warnings are known
php artisan inventory:reconcile
```

In Case A `migrate` must say "Nothing to migrate" at once and `db:integrity --compare-fresh` must exit 0 (the structure equals a fresh
install); if a migration is pending, the clinic is not on the `changes_v2` tip: stop and ask. Then sign in with the QA accounts (`storage/app/qa-accounts.json`, git-ignored; never print the passwords) as admin, Staff (Nurse) and
Doctor and walk the smoke checklist of section 4. **Go only if:** `migrate` ran without an error, `db:integrity` exits 0, and the tour is clean.
Write the `migrate` time here and size the window from it. Delete the imported data from the laptop afterwards.

### 2.2 Updating the clinic PC by `git pull` (the owner's way; no bundle since 2026-11-14)

The owner updates the clinic server PC by pulling the code (decided 2026-11-14). Git does not hold `vendor\` or the built scripts and styles
(`public\assets, backend, css, fonts, js, web` and `public\mix-manifest.json`), so the PC makes them itself right after the pull, in the project
folder, with **PHP 8.3** first on the PATH (8.2 for the Laravel 12 fallback), **Composer**, **Node 22** (not 26) and internet for the downloads:

```powershell
git fetch --tags ; git status                  # must be clean
git checkout <tag>                             # a window that stops at a tag; for the newest code: git checkout changes_v3 ; git pull --ff-only
composer install --no-dev --optimize-autoloader     # never --no-scripts
Remove-Item bootstrap\cache\packages.php, bootstrap\cache\services.php -ErrorAction SilentlyContinue
npm ci ; npm run prod                          # Laravel Mix before U4, build-assets.mjs (Vite) from U4 on
git checkout -- public/messages.js             # the build rewrites this tracked file; putting it back keeps the next pull clean
php artisan vendor:publish --tag=log-viewer-assets --force
php artisan optimize:clear
```

`composer install` is only needed when `composer.lock` changed, and the build only when `package-lock.json`, `build-assets.mjs` (`webpack.mix.js`
before U4) or `resources\` changed, but running both every time is safe. A build deletes and rewrites the build folders of `public\` (never
`public\uploads`). Until 2026-11-14 a zip bundle of `vendor\` and the build output was made on the laptop instead (so the PC would need no
internet and no Node); the owner chose the pull, the bundles were deleted, and their records stay in the Handover and the upgrade plan.

### 2.3 Look at the clinic PC (5 minutes, nothing is changed)

`php -v` (8.2.x until the switch) and the 8.3 folder from section 2.0 (`<wamp>\bin\php\php8.3.x\php.exe -v` and `-m`; needs `pdo_mysql, mbstring, intl, gd, zip,
fileinfo, openssl, sodium, curl` in **both** versions), free disk space (at least the size of the
project folder, for the rollback copy), `.\startup-dev-environment.ps1 -CheckOnly`, and the PC's own `.env`: it must not set
`FILESYSTEM_DISK` or `MEDIA_DISK` to `s3` (the upgrade removed the unused `s3` disk, so such a line would break file storage):
`Select-String .env -Pattern '^(FILESYSTEM_DISK|MEDIA_DISK|AWS_)'` should print nothing, or only `local` / `public`. If Composer is installed:
`composer check-platform-reqs`.

Also the password cost (found in the 2026-11-06 re-check, nothing is changed by looking): `Select-String .env -Pattern '^BCRYPT_ROUNDS'` should print nothing or
`BCRYPT_ROUNDS=10` (the default in `config/hashing.php`), and the stored hashes should start with the same cost
(`php artisan tinker --execute="echo DB::table('users')->selectRaw('SUBSTRING(password,1,7) p, COUNT(*) n')->groupBy('p')->get();"` prints only the first 7 characters,
for example `$2y$10$`). If the two differ, nothing breaks: Laravel 11+ re-hashes each password at that user's next sign-in (the password stays valid, only
`users.password` is rewritten). Align the `.env` value with the stored cost before the window if you want no such writes.

### 2.4 Freeze and tell people

No commits to the branch after the tag. Tell the nurses and doctors the time the system is closed and that every open browser page must be
reloaded afterwards (Ctrl+F5).

## 3. The window

Stop at the first step that fails. Times are for Case A; add the rehearsal `migrate` time in Case B. **Steps 0 to 5 run on the old PHP 8.2; the switch to PHP 8.3
is step 5.5, and everything after it runs on 8.3.**

| Step | Do | Check |
|---|---|---|
| 0 | **Prove Case A, changing nothing:** `git log -1 --format="%h %ad %s" --date=short` ; `php artisan about --only=environment` ; `php artisan migrate:status --pending` ; `php -v` | tip `71e43ae` (or a commit of `changes_v2`), Laravel 10.50.x, **no pending migration**, PHP 8.2.x. Anything else: stop. |
| 1 | Be sure the 6:00 PM backup exists in `storage\app\backups\scheduled` today. Everybody signed out. | file dated today |
| 2 | In the server window press **Ctrl+C** (stops the web server, Reverb and the scheduler). | window closed |
| 3 | `php artisan db:backup`, then **copy that `.sql` to a USB stick / another disk**. | size is not 0; copied |
| 4 | Copy the whole project folder (with `vendor`, `.env`, `storage`) to `<folder>-before-laravel-13`. | the copy opens |
| 5 | Code (section 2.2): `git fetch --tags` ; `git status` (must be clean) ; `git checkout laravel-13.42-excel4-ready` (the tag on `changes_v3`; since 2026-11-12 the Laravel 13 code is NOT on `changes_v2`). | `git log -1` shows `bcf7ad7` |
| 5.5 | **Switch the PC to PHP 8.3** (WampServer > PHP > Version, or the PATH entry; section 2.0). Open a **new** terminal. | `php -v` says 8.3.x there, and the web server uses it too. Do not run `artisan` between steps 5 and 6: the new code sits on the old `vendor\`. |
| 6 | Vendor (section 2.2), on PHP 8.3: `composer install --no-dev --optimize-autoloader` (**never `--no-scripts`**). Delete `bootstrap\cache\packages.php` and `services.php`, then `php artisan package:discover`. | no "Class ... not found"; `php artisan --version` says Laravel Framework 13.42.0 |
| 7 | Front end (section 2.2), with Node 22: `npm ci ; npm run prod` ; `git checkout -- public/messages.js` ; `php artisan vendor:publish --tag=log-viewer-assets --force`. | `public\mix-manifest.json` exists |
| 8 | `.env`: Case A nothing. Case B: STATUS section 7 steps 3d and 4 (`BROADCAST_DRIVER=reverb`, `REVERB_HOST=127.0.0.1`, `REVERB_PORT=8080`, `REVERB_SCHEME=http`, `APP_NAME="Medical University Clinic"`; leave `REVERB_APP_ID/KEY/SECRET` empty). | |
| 9 | Case B only: `php artisan migrate --force` and watch every line; then STATUS 3a (`inventory:recover-orphan-batches`, dry run, then `--apply`). Case A: `php artisan migrate --force` must print "Nothing to migrate". **Never run `migrate:rollback`, `migrate:fresh`, `migrate:refresh`, `migrate:reset` or `db:wipe`.** | |
| 10 | `php artisan optimize:clear` (**required**: the cache files written by the old code hold Collection objects that Laravel 13 does not read back, so the patient, doctor and medicine lists of the dispensing and prescription forms would be empty or fail for up to 10 minutes) ; `php artisan db:integrity` (0 errors) ; `php artisan permission:cache-reset` ; `php artisan notifications:refresh` | |
| 11 | Start `startup-dev-environment.bat`. | web, Reverb, scheduler **and firewall** `[ok]` (the rule follows `php.exe`'s path: the new PHP folder needs its own, section 2.0 point 2), LAN address printed, browser opens |
| 12 | Smoke checklist (section 4) from **a second device** on the LAN address; every browser tab opened before the window is reloaded (Ctrl+F5). | all green |
| 13 | **Keep or roll back**, within the window (decide the cut-off time in advance, for example 60 minutes after step 9). | |

## 4. Smoke checklist (about 15 minutes)

1. Sign in as admin, as a nurse and as a doctor, the nurse and doctor from the second device. Each lands on its dashboard.
2. One list per module opens and loads rows: patients, medicine inventory, dispensing, prescriptions. Search and sort one of them. **Open a dispensing form and a prescription form: the patient, doctor and medicine lists are filled** (they are the cached lists that Laravel 13 rebuilds after step 10).
3. The header does **not** show "Live updates off". Add a patient to the queue on one device: it appears on the other with no reload (then remove it).
4. Open a past consultation and export its PDF. In Report Generation download the Accomplishment report as PDF and as Excel; both open.
   Export a medical certificate's PDF: **both logos (university and clinic) are at the top**. Before this upgrade they were missing from the
   certificate and excuse-slip PDFs (DomPDF refused the embedded pictures; fixed 2026-11-12), so staff will see them for the first time.
5. Change your own profile photo, then remove it. Activity logs then show "Changed own profile".
6. Settings > Backups: take a backup; the list shows it at the right Philippine time; download works.
7. Settings > Manage User roles opens. The nurse is refused (403) on an admin-only page.
8. `php artisan db:integrity` 0 errors ; `php artisan schedule:list` shows three tasks, among them `db:backup --force` at 18:00 ; `storage\logs\laravel-<today>.log` has no ERROR line.

## 5. Rollback (decide fast; nobody has used the system since step 2)

- **Case A (no migration ran):** Ctrl+C ; **switch the PC back to PHP 8.2** (WampServer or the PATH; `php -v` in a new terminal) *before* anything else, because the old `vendor\` needs it and the Laravel 13 `vendor\` refuses to run on 8.2 ; rename the project folder to `<folder>-failed` and rename `<folder>-before-laravel-13` back ; `php artisan optimize:clear` ; start with the startup script and check that its firewall line says `[ok]` for the PHP 8.2 `php.exe`.
- **Case B (migrations ran):** the same, **and** restore the step 3 backup into the database (Settings > Backups > Restore on the old code, or `mysql` import). The old code is not guaranteed to run on the converted tables.
- `git revert` and `migrate:rollback` are **not** rollback methods here.
- After a rollback write down which step failed and the exact message; do not retry the same day.

## 6. After the window

- Next morning: yesterday's 6:00 PM backup exists in `storage\app\backups\scheduled` ; `db:integrity` ; the logs (`laravel-*.log`, `scheduler*.log`, `reverb*.log`) hold no errors.
- Everybody signs in again if `APP_NAME` changed (the session cookie name follows it). Remind the doctors and staff still on `123456`.
- Keep `<folder>-before-laravel-13` and the USB backup for 7 days, then tidy up. Keep PHP 8.2 installed until the folder copy is deleted (it is the rollback).
- Records: a Handover entry (what ran, times, warnings), STATUS section 7 ticked, then the pull request into `develop`.
- Optional, **not part of the window**: older month-only stock sits on the 1st of its month. When the owner wants it corrected: `php artisan db:backup`, `php artisan inventory:month-only-expiry` (dry run: read the table and the units that "would be usable again"), then `--apply`, then `php artisan db:integrity`. See [commands.md](commands.md#inventorymonth-only-expiry).

## 7. Not verified, and why

- The upgrade has **not** been rehearsed on a copy of the clinic's real data (the dump is not on the laptop): section 2.1 is that rehearsal.
- Laravel 13 has run only on the laptop's PHP 8.3.36 and 8.4.27 (suite, live check, clean-room install); **PHP 8.5 and the full-core Symfony 8 were not tried**, and
  the clinic PC's own PHP 8.3 build (extensions, Wamp setup) is unknown until section 2.0 is done.
- Nothing has been tested from a real second LAN device; the runtime check of STATUS section 7 step 5b is part of the smoke checklist. On the laptop, a page opened at the laptop's own LAN address could not open the live-updates connection in the embedded browser (it opens from `localhost`, and a direct protocol test of Reverb on the LAN address connects), so **smoke step 3 on the real second device is the deciding test**; if "Live updates off" shows there, check the firewall rule for TCP 8080 first. Phone and tablet widths were checked on the laptop (the wide lists overflow a phone screen exactly as before).
- The clinic PC itself (PHP extensions, free disk, Windows firewall, Node, internet) has not been looked at: section 2.3.
- Report exports were not compared file by file with the pre-upgrade output; their numbers are pinned by tests and the live check proved the files open.

## 8. Laravel 13 / PHP 8.3 specifics, and the fallback (written 2026-11-06; nothing here has been run on the clinic PC)

The steps above already include the U3 differences (PHP 8.3 installed first, switch at step 5.5, Composer and the build on the PC, `optimize:clear`, firewall,
dispensing / prescription form check, rollback to PHP 8.2). What is left to know:

- Laravel 13 (13.42 in `laravel-13.42-excel4-ready` and `laravel-13.42-ready`, 13.40 in `laravel-13-ready`) needs **PHP 8.3 or newer**; the commits from the U3 hop (`315894a`) on need it, the commits up to `3c8c434` (Laravel 12.71, groundwork) do not.
  The hop adds **no migration** and changes **no `.env` key** (the session cookie and cache names are pinned in `config/`).
- **What is new for the people who run it:** nothing visible. The forgery check has a new name (`PreventRequestForgery`). A browser may send
  `Sec-Fetch-Site: same-origin` on `localhost` pages (Laravel 13 lets that through without a token); Chromium sends no such header on a plain `http://`
  LAN address (checked on the laptop at 192.168.2.11), so there the token still decides, as before. Debugbar 4 has six fewer `_debugbar/*` dev routes.
- **Fallback if PHP 8.3 cannot be installed in time:** deploy the commit `3c8c434` instead (Laravel 12.71 on PHP 8.2; it is the old U0 to U2
  deployment). Check it out and run section 2.2 on PHP 8.2 (no bundle since 2026-11-14), skip section 2.0 and step 5.5, name the rollback copy
  `<folder>-before-laravel-12`, and note that step 10's `optimize:clear` is then still a good idea but not required. U3 would then be a second, smaller
  window later: PHP 8.3 first, then section 3 from step 4.

## 9. The U4 window (Laravel Mix → Vite assets), on a later day than the U0 – U3 window

**What it changes:** only the built files in `public\` (`assets`, `backend`, `css`, `fonts`, `js`, `web`, `mix-manifest.json`) and the code to the
tip of `changes_v3` (`git pull`), which also holds the fixes of the 2026-11-13 module audit (PHP code, views and scripts: the theme switch
is POST only, adding a patient checks the e-mail like editing does, Front CMS shows its about-us pictures) and of the DRY pairs pass (a nurse can
save a change to a medicine generic, the dispense-record edit and consultation-medicines pages show their headings, a failed password reset shows a
generic error; shared views and helpers that change no page) and the 2026-11-14 morning items (no page changes: unused switch views removed, the
build script's staging folder, two Quill tests) and the owner's section 6 answers (a patient cannot be deleted for good; the email-verified
endpoint and the always-empty Schedules tab of Report Generation are gone; the queue actions write audit rows, and Remove keeps a called entry
as cancelled) and the owner's later answers of 2026-11-14 (STATUS, the 2026-11-14 paragraphs). No
migration, no `.env` key needed, no new PHP package (`composer.lock` is the same as in `laravel-13.42-excel4-ready`). Apart from the changes listed above, the pages look and work the same as under Mix: on the laptop every page loads the same scripts and stylesheets in the same order
(the link crawl, 1,367 pages, 15 kinds of user) and the Mix and Vite builds render every element of 13 pages identically, light and dark.

**How it gets there:** `git pull` on the PC, then Composer and the build there (section 2.2): the owner's way since 2026-11-14. The U4 bundles
that were built for this window (the last one `laravel-13-32a8c99.zip`, for the tag `laravel-13.42-vite-decisions-ready`) were deleted; the pull
brings everything on `changes_v3` up to its tip.

| Step | What | Check |
|---|---|---|
| 0 | The U0 – U3 window was kept (section 3 step 13) and the clinic has worked on it for at least a few days. The PC runs PHP 8.3 and the tag `laravel-13.42-excel4-ready` (`git log -1` shows `bcf7ad7`). | as stated |
| 1 | After the 6:00 PM backup, everybody signed out; in the server window **Ctrl+C**. | window closed |
| 2 | Copy the build folders `public\assets, backend, css, fonts, js, web` and `public\mix-manifest.json` to `<folder>-public-before-u4` (this is the rollback). | the copy opens |
| 3 | Code: `git fetch --tags` ; `git status` (must be clean) ; `git checkout changes_v3` ; `git pull --ff-only`. | `git log -1` shows the commit the laptop tested last (Handover, section 2) |
| 4 | `composer install --no-dev --optimize-autoloader` on PHP 8.3 (**never `--no-scripts`**), then delete `bootstrap\cache\packages.php` and `services.php`. | ends without an error |
| 5 | With Node 22: `npm ci ; npm run prod` (it replaces `public\assets, backend, css, fonts, js, web` and `public\mix-manifest.json`, never `uploads`, `webfonts` or `index.php`) ; `git checkout -- public/messages.js` ; `php artisan vendor:publish --tag=log-viewer-assets --force`. | `public\mix-manifest.json` exists; `public\hot` does **not** |
| 6 | `php artisan optimize:clear`, then start `startup-dev-environment.bat`. | web, Reverb, scheduler `[ok]` |
| 7 | From a second device on the LAN address, every browser tab reloaded with **Ctrl+F5**: sign in; dashboard; a list with its search; the new-patient form (date picker, Select2, phone field); a consultation; Report Generation and one Excel and one PDF download; the queue board; dark mode for one user (switch it on and off); Front CMS shows the three about-us pictures; the Live updates badge. | no broken layout, no error message |
| 8 | **Keep or roll back** within 30 minutes. | |

**Rollback (U4 only):** Ctrl+C ; `git checkout laravel-13.42-excel4-ready` ; delete the six build folders and `mix-manifest.json` from `public\` and copy
back the ones from `<folder>-public-before-u4` ; `php artisan vendor:publish --tag=log-viewer-assets --force` ; `php artisan optimize:clear` ; start. `vendor\` can stay (the same
packages; checked 2026-11-14: the Excel 4 code over this `vendor\` boots with all 505 routes and finds its older classes by their path; the newer classes in the class map are never asked for). No database step: U4 changes no data.
