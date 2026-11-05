# Deployment runbook: the Laravel 12 upgrade (U0 + U1 + U2) to the clinic PC

Written 2026-11-05 for the owner (or whoever runs the clinic PC). It extends [STATUS.md](STATUS.md) section 7 and
[laravel-13-upgrade-plan.md](laravel-13-upgrade-plan.md) section 8; it does not replace them. **Nothing in this file has been run on the
clinic PC.** Everything below was checked on the development laptop only.

## 0. Read this first

- What gets deployed: branch `upgrade/laravel-13` at `f3acc02` (Laravel 12.71, Carbon 3, PHPUnit 11.5, in-house list tables,
  `spatie/laravel-html` forms, Livewire 3.8, permission 6, medialibrary 11). PHP stays **8.2**. The upgrade stages add **no migration**
  and change **no `.env` key**.
- **The size of the jump depends on what the clinic PC runs today, and the repository cannot say.** `develop` stopped on 2025-09-27
  (Laravel 10.42); `changes_v2` is 246 commits and 81 new migrations ahead of it, and STATUS section 7 still lists the clinic
  deployment of `changes_v2` as *to do*. So there are two cases:

| | Case A: the clinic already runs `changes_v2` (tip `71e43ae`) | Case B: the clinic runs older code (`develop` era) |
|---|---|---|
| Code | 23 commits, framework hops only | 246 + 23 commits |
| Database | `php artisan migrate` says "Nothing to migrate" | **81 migrations**: MyISAM to InnoDB, zero dates, foreign keys, backfills (STATUS section 7, steps 3 to 3h) |
| Window | 30 to 60 minutes | 2 hours or more (set it from the rehearsal time) |
| Rehearsal on a copy of the real data | strongly recommended | **required** |
| Rollback | restore the folder copy | restore the folder copy **and** the database backup |

- **Found out the hard way during the upgrade (so it is in the steps below):** a stale `bootstrap/cache/packages.php` stops the site from
  booting after `vendor/` changes; `npm run prod` wipes `public/assets, backend, css, fonts, js, vendor, web, mix-manifest.json`
  (never `public/uploads`); Node 26 breaks the build (use Node 22); a browser tab opened before the deployment fails on its next click
  until it is reloaded.

## 1. Decisions needed from the owner (before a date is set)

| # | Question | Recommendation |
|---|---|---|
| 1 | What does the clinic PC run now? In its project folder: `git log -1 --format="%h %ad %s" --date=short` and `php artisan about --only=environment` | This decides Case A or B above. |
| 2 | Window: date, start time, who runs it, who is on the phone | After the 6:00 PM backup has run and the clinic is closed; Case B on a Friday evening or a weekend, so Monday is not the first test. |
| 3 | How the code reaches the PC | Once the rehearsal passes, the owner approves: `git merge --ff-only upgrade/laravel-13` into `changes_v2` (it is 0 behind, so nothing is rewritten), push it, and tag `laravel-12-ready`. The PC then runs `git pull --ff-only` on `changes_v2`. Merging into `develop` stays a separate pull request (STATUS section 6). |
| 4 | Does the clinic PC have internet and Node 22 for the window? | Do not depend on it: build on the laptop and carry a bundle on a USB stick (section 2.2). |
| 5 | A fresh backup of the clinic database on the laptop, for the rehearsal | Yes (section 2.1). It is patient data: it goes into the laptop's `norsu_clinic` only, never into the repository or a document. |

## 2. Before the window (on the laptop)

### 2.1 Rehearsal on a copy of the real data

The laptop has no `norsu_clinic` database since the reformat, and the real-dump rehearsal of the upgrade is still **not done**. Do it with a
backup taken on the clinic PC (`php artisan db:backup`, or Settings > Backups > Download):

```powershell
# in the project folder on the laptop; mysql.exe is in C:\Program Files\MySQL\MySQL Server 9.7\bin
mysql -u root -p -e "CREATE DATABASE norsu_clinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"   # fails if it exists: stop and look
cmd /c "mysql -u root -p norsu_clinic < C:\path\to\clinic-backup.sql"      # asks for the password; nothing is printed
git checkout upgrade/laravel-13        # or the tag
composer install                       # normal install, no --no-scripts
php artisan db:backup                  # safety copy of the imported data
Measure-Command { php artisan migrate --force }      # write down the time and every warning line
php artisan migrate --force            # must say "Nothing to migrate"
php artisan inventory:recover-orphan-batches         # dry run first (STATUS 3a), then --apply
php artisan db:integrity --compare-fresh             # expected: 0 errors; the duplicate-medicine and phone warnings are known
php artisan inventory:reconcile
```

Then sign in with the QA accounts (`storage/app/qa-accounts.json`, git-ignored; never print the passwords) as admin, Staff (Nurse) and
Doctor and walk the smoke checklist of section 4. **Go only if:** `migrate` ran without an error, `db:integrity` exits 0, and the tour is clean.
Write the `migrate` time here and size the window from it. Delete the imported data from the laptop afterwards.

### 2.2 Build the bundle (so the PC needs no internet and no Node)

From a clean clone of the tagged commit, Node **22**:

```powershell
git clone C:\Projects\NORSUCLINIC U2-bundle ; cd U2-bundle ; git checkout <tag>
composer install --no-dev --optimize-autoloader
npm ci ; npm run prod
git checkout -- public/messages.js     # the build rewrites this tracked file
```

Carry on the USB stick: `vendor\` and the build output `public\assets, backend, css, fonts, js, vendor, web, mix-manifest.json`. **Never**
carry or overwrite `public\uploads` (the patients' photos and logos live there) or `.env`.

### 2.3 Look at the clinic PC (5 minutes, nothing is changed)

`php -v` (8.2.x), `php -m` (needs `pdo_mysql, mbstring, intl, gd, zip, fileinfo, openssl, sodium, curl`), free disk space (at least the size of the
project folder, for the rollback copy), `.\startup-dev-environment.ps1 -CheckOnly`. If Composer is installed:
`composer check-platform-reqs`.

### 2.4 Freeze and tell people

No commits to the branch after the tag. Tell the nurses and doctors the time the system is closed and that every open browser page must be
reloaded afterwards (Ctrl+F5).

## 3. The window

Stop at the first step that fails. Times are for Case A; add the rehearsal `migrate` time in Case B.

| Step | Do | Check |
|---|---|---|
| 1 | Be sure the 6:00 PM backup exists in `storage\app\backups\scheduled` today. Everybody signed out. | file dated today |
| 2 | In the server window press **Ctrl+C** (stops the web server, Reverb and the scheduler). | window closed |
| 3 | `php artisan db:backup`, then **copy that `.sql` to a USB stick / another disk**. | size is not 0; copied |
| 4 | Copy the whole project folder (with `vendor`, `.env`, `storage`) to `<folder>-before-laravel-12`. | the copy opens |
| 5 | Code: `git fetch` ; `git status` (must be clean) ; `git checkout changes_v2` ; `git pull --ff-only` (or `git checkout <tag>`). | `git log -1` shows the tagged commit |
| 6 | Vendor: copy `vendor\` from the bundle over a deleted old `vendor\` (or `composer install --no-dev --optimize-autoloader` with internet; **never `--no-scripts`**). Delete `bootstrap\cache\packages.php` and `services.php`, then `php artisan package:discover`. | no "Class ... not found" |
| 7 | Front end: copy the build folders of the bundle into `public\` (not `uploads`), then `php artisan vendor:publish --tag=log-viewer-assets --force`. | `public\mix-manifest.json` exists |
| 8 | `.env`: Case A nothing. Case B: STATUS section 7 steps 3d and 4 (`BROADCAST_DRIVER=reverb`, `REVERB_HOST=127.0.0.1`, `REVERB_PORT=8080`, `REVERB_SCHEME=http`, `APP_NAME="Medical University Clinic"`; leave `REVERB_APP_ID/KEY/SECRET` empty). | |
| 9 | Case B only: `php artisan migrate --force` and watch every line; then STATUS 3a (`inventory:recover-orphan-batches`, dry run, then `--apply`). Case A: `php artisan migrate --force` must print "Nothing to migrate". **Never run `migrate:rollback`, `migrate:fresh`, `migrate:refresh`, `migrate:reset` or `db:wipe`.** | |
| 10 | `php artisan db:integrity` (0 errors) ; `php artisan optimize:clear` ; `php artisan permission:cache-reset` ; `php artisan notifications:refresh` | |
| 11 | Start `startup-dev-environment.bat`. | web, Reverb, scheduler `[ok]`, firewall `[ok]`, LAN address printed, browser opens |
| 12 | Smoke checklist (section 4) from **a second device** on the LAN address. | all green |
| 13 | **Keep or roll back**, within the window (decide the cut-off time in advance, for example 45 minutes after step 9). | |

## 4. Smoke checklist (about 15 minutes)

1. Sign in as admin, as a nurse and as a doctor, the nurse and doctor from the second device. Each lands on its dashboard.
2. One list per module opens and loads rows: patients, medicine inventory, dispensing, prescriptions. Search and sort one of them.
3. The header does **not** show "Live updates off". Add a patient to the queue on one device: it appears on the other with no reload (then remove it).
4. Open a past consultation and export its PDF. In Report Generation download the Accomplishment report as PDF and as Excel; both open.
5. Change your own profile photo, then remove it. Activity logs then show "Changed own profile".
6. Settings > Backups: take a backup; the list shows it at the right Philippine time; download works.
7. Settings > Manage User roles opens. The nurse is refused (403) on an admin-only page.
8. `php artisan db:integrity` 0 errors ; `php artisan schedule:list` shows three tasks, among them `db:backup --force` at 18:00 ; `storage\logs\laravel-<today>.log` has no ERROR line.

## 5. Rollback (decide fast; nobody has used the system since step 2)

- **Case A (no migration ran):** Ctrl+C ; rename the project folder to `<folder>-failed` and rename `<folder>-before-laravel-12` back ; `php artisan optimize:clear` ; start with the startup script.
- **Case B (migrations ran):** the same, **and** restore the step 3 backup into the database (Settings > Backups > Restore on the old code, or `mysql` import). The old code is not guaranteed to run on the converted tables.
- `git revert` and `migrate:rollback` are **not** rollback methods here.
- After a rollback write down which step failed and the exact message; do not retry the same day.

## 6. After the window

- Next morning: yesterday's 6:00 PM backup exists in `storage\app\backups\scheduled` ; `db:integrity` ; the logs (`laravel-*.log`, `scheduler*.log`, `reverb*.log`) hold no errors.
- Everybody signs in again if `APP_NAME` changed (the session cookie name follows it). Remind the doctors and staff still on `123456`.
- Keep `<folder>-before-laravel-12` and the USB backup for 7 days, then tidy up.
- Records: a Handover entry (what ran, times, warnings), STATUS section 7 ticked, then the pull request into `develop`.
- **U3 (Laravel 13) is a separate window** and needs PHP 8.3 on the clinic PC first.

## 7. Not verified, and why

- The upgrade has **not** been rehearsed on a copy of the clinic's real data (the dump is not on the laptop): section 2.1 is that rehearsal.
- Nothing has been tested from a second LAN device or at phone / tablet widths; the runtime check of STATUS section 7 step 5b is part of the smoke checklist.
- The clinic PC itself (PHP extensions, free disk, Windows firewall, Node, internet) has not been looked at: section 2.3.
- Report exports were not compared file by file with the pre-upgrade output; their numbers are pinned by tests and the live check proved the files open.
