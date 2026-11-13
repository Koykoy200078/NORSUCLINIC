# Deployment runbook: the Laravel 13 upgrade (U0 + U1 + U2 + U3, one window) to the clinic PC

Written 2026-11-05 for Laravel 12; **rewritten 2026-11-06 after the owner decided to deploy U0 to U3 together, in one window** (the file keeps its
old name so the links to it keep working). It extends [STATUS.md](STATUS.md) section 7 and
[laravel-13-upgrade-plan.md](laravel-13-upgrade-plan.md) section 8; it does not replace them. **Nothing in this file has been run on the
clinic PC.** Everything below was checked on the development laptop only.

## 0. Read this first

- What gets deployed: tag **`laravel-13.42-excel4-ready`** (`bcf7ad7`, since 2026-11-13, the owner's second "Rebuild and tag", after the
  Laravel Excel 4 upgrade) with the bundle `laravel-13-bcf7ad7.zip`, on the branch **`changes_v3`** (the former `upgrade/laravel-13`, renamed on 2026-11-12; `changes_v2` stays at the clinic's current code, `71e43ae`, plus a docs-only commit `07aa585` pointing to `changes_v3`). The earlier pairs still work and are the fallbacks if the new bundle is not on the stick:
  tag `laravel-13.42-ready` (`a7d7f96`, the same but with Laravel Excel 3.1 / PhpSpreadsheet 1.30.8) with `laravel-13-a7d7f96.zip`, and tag
  `laravel-13-ready` (`cea3775`, Laravel 13.40) with `laravel-13-bce72ec.zip`:
  **Laravel 13.42** (13.40 in the oldest pair), Laravel Excel 4 with PhpSpreadsheet 5 (3.1 / 1.30.8 in both earlier pairs), Guzzle 8, Carbon 3, PHPUnit 12.5, Symfony 7.4, in-house list tables, `spatie/laravel-html` forms, Livewire 3.8, permission 6,
  medialibrary 11. **The clinic PC must run PHP 8.3 or newer** (the Laravel 13 `vendor\` refuses PHP 8.2 with a clear message): install it
  beforehand, section 2.0. The upgrade stages add **no migration** and change **no `.env` key**.
- **Later, in its own window: U4 (Laravel Mix → Vite).** Tag **`laravel-13.42-vite-dry-ready`** (`ae822d9`, 2026-11-14, the owner's
  "Rebuild and tag" after the DRY pairs pass) with the bundle `laravel-13-ae822d9.zip`: the same code as `laravel-13.42-excel4-ready` plus the new
  asset build (`build-assets.mjs` on Vite 8; Laravel Mix gone) plus the 2026-11-13 module audit (theme switch POST only, the patient e-mail rule,
  the Front CMS pictures, `npm audit fix`) plus the DRY pairs pass (a nurse can save a generic edit, the dispense pages show their headings, a
  failed password reset shows a generic error, shared views and helpers; no migration, no `.env` key, no package). Its `vendor\` has the same
  packages as `laravel-13-bcf7ad7.zip`; what changes is the PHP code and the build output in `public\`. Fallbacks: tag
  `laravel-13.42-vite-audit-ready` (`d67dc6f`) with `laravel-13-d67dc6f.zip` (without the DRY pairs pass), and tag `laravel-13.42-vite-ready`
  (`32d49d8`) with `laravel-13-32d49d8.zip` (without the audit either). **Not in the U0 – U3 window** (owner's decision 13): run section 9 on another day, after that window was kept.
- **Owner's decision, 2026-11-06: U0 to U3 go out together in one window** (not U0 to U2 first). The fallback if PHP 8.3 cannot be installed in
  time is the commit `3c8c434` (Laravel 12.71 with the Laravel 13 groundwork, runs on PHP 8.2); section 8 says how. The older bundle
  `laravel-12-37f5a62.zip` still fits that commit (`composer.lock`, `package-lock.json`, `webpack.mix.js` and `resources\` do not differ between
  `37f5a62` and `3c8c434`; checked 2026-11-06).
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
| 4 | Does the clinic PC have internet and Node 22 for the window? | Do not depend on it: build on the laptop and carry a bundle on a USB stick (section 2.2). |
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

### 2.2 Build the bundle (so the PC needs no internet and no Node)

From a clean clone of the tagged commit, **PHP 8.3** (the Laravel 13 `vendor\` is built for it) and Node **22**:

```powershell
git clone C:\Projects\NORSUCLINIC U3-bundle ; cd U3-bundle ; git checkout <tag>
composer install --no-dev --optimize-autoloader     # with PHP 8.3 first on the PATH
npm ci ; npm run prod
git checkout -- public/messages.js     # the build rewrites this tracked file
```

**The bundle for the window (built 2026-11-13, the owner's second "Rebuild and tag", after the Laravel Excel 4 upgrade):**
`storage\app\deploy-bundle\laravel-13-bcf7ad7.zip` (50,287,578 bytes = 48 MB, 9,568 files; git ignores that folder), with
`laravel-13-bcf7ad7.zip.sha256` beside it (SHA-256 `CC02B8920A8479C38EF758BFD9DFB6325BE068429BA605EDCCBBFA6388ED10F3`), for the tag
**`laravel-13.42-excel4-ready`** (`bcf7ad7`). Same layout and recipe as the bundle below (one folder `laravel-13-bcf7ad7\` with `README.txt`,
`SHA256SUMS.txt`, `vendor\` and the `public\` build output; forward slashes inside the zip; no `.env`, `public\uploads`, `storage\` or dev
package). Made from a clean clone of the tag with PHP 8.3.36 (`composer install --no-dev --optimize-autoloader`: **119** packages, exactly the
lock's `packages` block, with `maatwebsite/excel` 4.0.6, `phpoffice/phpspreadsheet` 5.11.0 and `phpoffice/wmf` 0.2.1) and Node 22.23.4 (`npm ci`,
`npm run prod`; only `public/messages.js` changed and was put back). **Checked:** the zip hash matches the `.sha256` file (also after copying it
into the bundle folder); the 9,567 files listed in `SHA256SUMS.txt` all match and nothing in the zip is unlisted (unzipped with `Expand-Archive`);
laid over a fresh clone of `laravel-13.42-excel4-ready` it boots **Laravel 13.42.0** on PHP 8.3.36 with **505 routes**, all views compile (380),
`composer check-platform-reqs --no-dev` is clean, `composer audit --locked --no-dev` finds no advisories, all 799 files named in
`mix-manifest.json` exist, and its `vendor\` writes and reads back a two-sheet xlsx (Excel 4.0.6 / PhpSpreadsheet 5.11.0); **PHP 8.2.35
refuses it** with "Your Composer dependencies require a PHP version >= 8.3.0". It stays valid for later commits while
`git diff --stat bcf7ad7..<commit> -- composer.lock package-lock.json webpack.mix.js build-assets.mjs resources` prints nothing (or the `packages` block /
build-input proof below), otherwise rebuild.

**2026-11-13: stage U4 (Laravel Mix → Vite) is on `changes_v3` after `a5d1e21`** (plan section 5, U4): `package-lock.json` changed (Mix and its
725 packages out, `vite` 8.3.8 in) and `webpack.mix.js` was replaced by `build-assets.mjs`, so **the commits after `a5d1e21` no longer fit
`laravel-13-bcf7ad7.zip`**. The U0 – U3 window deploys the tag `laravel-13.42-excel4-ready` with that bundle as planned; U4 gets its own bundle and
tag in step U4.6 (done 2026-11-13: `laravel-13.42-vite-ready` + `laravel-13-32d49d8.zip`; after the module audit of the same evening rebuilt as `laravel-13.42-vite-audit-ready` + `laravel-13-d67dc6f.zip`, and after the DRY pairs pass on 2026-11-14 as `laravel-13.42-vite-dry-ready` + `laravel-13-ae822d9.zip`, section 9) and its own later window. The commands do not change: `npm ci ; npm run prod` builds the same folders (`assets, backend, css,
fonts, js, web`, `mix-manifest.json`; `public\vendor` is still cleared, so step 7 still republishes the log-viewer assets).

**The first fallback bundle (built 2026-11-12, the owner's first "Rebuild and tag"):** `storage\app\deploy-bundle\laravel-13-a7d7f96.zip`
(49,583,793 bytes = 47 MB, 9,387 files; git ignores that folder), with `laravel-13-a7d7f96.zip.sha256` beside it (SHA-256
`2BC0C3297FB4FD442CB39E9C2A4022C3419BC37393390B4BEABF9BBA3F426B82`), for the tag **`laravel-13.42-ready`** (`a7d7f96`). Same layout as before
(one folder `laravel-13-a7d7f96\` with `README.txt`, `SHA256SUMS.txt`, `vendor\` and the `public\` build output; the paths inside the zip use
forward slashes, which `Expand-Archive` and Explorer read the same way). Made from a clean clone of the tag with PHP 8.3.36 (`composer install
--no-dev --optimize-autoloader`: 118 packages, exactly the lock's `packages` block, no dev package) and Node 22.23.4 (`npm ci`, `npm run prod`;
only `public/messages.js` changed and was put back). **Checked:** the zip hash matches the `.sha256` file (also after copying it into the bundle
folder); the 9,386 files listed in `SHA256SUMS.txt` all match and nothing in the zip is unlisted (unzipped with `Expand-Archive`); laid over a fresh
clone of `laravel-13.42-ready` it boots **Laravel 13.42.0** on PHP 8.3.36 with **505 routes**, all views compile (380), `composer
check-platform-reqs --no-dev` is clean, `composer audit --locked --no-dev` finds no advisories, and all 799 files named in `mix-manifest.json`
exist; **PHP 8.2.35 refuses it** with "Your Composer dependencies require a PHP version >= 8.3.0". The same rule as below keeps it valid for
later commits: `git diff --stat a7d7f96..<commit> -- composer.lock package-lock.json webpack.mix.js resources` must print nothing (or the
`packages` block / build-input proof below), otherwise rebuild.

**2026-11-13: `changes_v3` after `a7d7f96` no longer fits this bundle.** On the owner's request `maatwebsite/excel` 3.1.70 → **4.0.6**
with `phpoffice/phpspreadsheet` 1.30.8 → **5.11.0** (and the new `phpoffice/wmf` 0.2.1, no new PHP extension) changed the lock's `packages`
block. The tag `laravel-13.42-ready` with `laravel-13-a7d7f96.zip` stays a valid pair (it holds Excel 3.1); the Excel 4 code has its own pair
above (`laravel-13.42-excel4-ready` + `laravel-13-bcf7ad7.zip`). Never lay `laravel-13-a7d7f96.zip` over the Excel 4 tag, or the reverse.

**The oldest bundle (second fallback), built 2026-11-06:** `storage\app\deploy-bundle\laravel-13-bce72ec.zip` on the laptop
(49,220,893 bytes = 47 MB, 9,329 files; git ignores that folder), with `laravel-13-bce72ec.zip.sha256` beside it (SHA-256
`53E1B828557DE946DE7E26547636CF3122BC9264ED4CFA0EB624E03B718F1832`). It was made from a clean clone of `bce72ec` with PHP 8.3.36
(`composer install --no-dev --optimize-autoloader`) and Node 22.23.4 (`npm ci`, `npm run prod`). **Checked:** the zip hash matches the
`.sha256` file; the 9,328 files listed in `SHA256SUMS.txt` all match and nothing in the zip is unlisted; laid over a fresh clone of `bce72ec` it
boots Laravel 13.40.0 on PHP 8.3.36 with 505 routes, all views compile, `composer check-platform-reqs` is clean, `composer audit --locked`
finds no advisories, and every one of the 799 files named in `mix-manifest.json` exists; **PHP 8.2.35 refuses it** with "Your Composer
dependencies require a PHP version >= 8.3.0". **Copy both files to the USB stick, and after copying check the hash on the PC:**
`Get-FileHash <zip> -Algorithm SHA256`. It stays valid for any later commit that leaves `composer.lock`, `package-lock.json`, `webpack.mix.js`
and `resources\` alone: `git diff --stat bce72ec..<tag> -- composer.lock package-lock.json webpack.mix.js resources` must print nothing,
otherwise rebuild it (the commands are above). **One known exception:** the Boost commit (2026-11-06) added four `packages-dev` entries to `composer.lock`
(`laravel/boost`, `laravel/mcp`, `laravel/roster`, `symfony/yaml`), so that diff is no longer empty, but the bundle is built with `--no-dev` and the
`packages` block (118 packages) is identical to `bce72ec` (checked 2026-11-06): the bundle stays valid. Later `composer.lock` changes need the same check
of the `packages` block, not of `packages-dev`. **Second known exception (2026-11-12 re-verification):** seven Blade views under
`resources\views\document_issuances\` gained `(string)` casts inside `{{ }}` expressions. No build input changed (`resources\assets`, `resources\js`,
`resources\css`, `webpack.mix.js`, `package-lock.json`); the only part of the build that reads views is Tailwind (`@source "../views"` in
`resources\css\app.css`), and Tailwind generates no CSS from the one new token `(string)` (checked with Tailwind 4.1.3 itself), so the built
`css\app.css` in the bundle still covers the views: the bundle stays valid. Any later change under `resources\` needs a rebuild or the same proof.
**2026-11-12 afternoon: this earlier bundle no longer matches `changes_v3`.** The owner's `composer update --with-all-dependencies`
(committed after the full suite and a live check) changed the `packages` block: Laravel 13.40 → **13.42**, Guzzle 7 → **8** (with
`guzzlehttp/psr7` 3 and `promises` 3), Livewire 3.8.11, Reverb 1.13, medialibrary 11.23.12, PhpSpreadsheet 1.30.8, ZipStream 3.2.2 and
a few more (19 packages; `package-lock.json`, `webpack.mix.js` and the build inputs are unchanged). `laravel-13-bce72ec.zip` stays right
**only for the tag `laravel-13-ready` (`cea3775`, Laravel 13.40)**: use that pair together, never this zip with `laravel-13.42-ready` or `laravel-13.42-excel4-ready`.

The Laravel 12 bundle `laravel-12-37f5a62.zip` (SHA-256 `A47BC0CFFB30621A10E3563739661CCA078B4AC1D838035050360DC5DF6716F5`) is **not** for this
window: it holds Laravel 12 `vendor\`. Keep it only for the fallback commit `3c8c434` (section 8).

Carry on the USB stick: `vendor\` and the build output `public\assets, backend, css, fonts, js, web, mix-manifest.json` (the build does not
make `public\vendor`; step 7 publishes the log-viewer assets there). **Never**
carry or overwrite `public\uploads` (the patients' photos and logos live there) or `.env`.

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
| 5 | Code: `git fetch --tags` ; `git status` (must be clean) ; `git checkout laravel-13.42-excel4-ready` (the tag on `changes_v3`; since 2026-11-12 the Laravel 13 code is NOT on `changes_v2`; with a fallback bundle check out its own tag instead, `laravel-13.42-ready` for `laravel-13-a7d7f96.zip` or `laravel-13-ready` for `laravel-13-bce72ec.zip`: tag and bundle always go together). | `git log -1` shows `bcf7ad7` (`a7d7f96` / `cea3775` for the fallback pairs) |
| 5.5 | **Switch the PC to PHP 8.3** (WampServer > PHP > Version, or the PATH entry; section 2.0). Open a **new** terminal. | `php -v` says 8.3.x there, and the web server uses it too. Do not run `artisan` between steps 5 and 6: the new code sits on the old `vendor\`. |
| 6 | Vendor: check the zip hash, unzip it next to the project, then copy `vendor\` from the **Laravel 13 bundle that belongs to the checked-out tag** (`laravel-13-bcf7ad7.zip` for `laravel-13.42-excel4-ready`, `laravel-13-a7d7f96.zip` for `laravel-13.42-ready`) over a deleted old `vendor\` (the Laravel 12 bundle cannot be used; or `composer install --no-dev --optimize-autoloader` with internet on PHP 8.3; **never `--no-scripts`**). Delete `bootstrap\cache\packages.php` and `services.php`, then `php artisan package:discover`. | no "Class ... not found"; `php artisan --version` says Laravel Framework 13.42.0 (13.40.0 for the oldest pair) |
| 7 | Front end: copy the build folders of the bundle into `public\` (not `uploads`), then `php artisan vendor:publish --tag=log-viewer-assets --force`. | `public\mix-manifest.json` exists |
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

The steps above already include the U3 differences (PHP 8.3 installed first, switch at step 5.5, Laravel 13 bundle, `optimize:clear`, firewall,
dispensing / prescription form check, rollback to PHP 8.2). What is left to know:

- Laravel 13 (13.42 in `laravel-13.42-excel4-ready` and `laravel-13.42-ready`, 13.40 in `laravel-13-ready`) needs **PHP 8.3 or newer**; the commits from the U3 hop (`315894a`) on need it, the commits up to `3c8c434` (Laravel 12.71, groundwork) do not.
  The hop adds **no migration** and changes **no `.env` key** (the session cookie and cache names are pinned in `config/`).
- **What is new for the people who run it:** nothing visible. The forgery check has a new name (`PreventRequestForgery`). A browser may send
  `Sec-Fetch-Site: same-origin` on `localhost` pages (Laravel 13 lets that through without a token); Chromium sends no such header on a plain `http://`
  LAN address (checked on the laptop at 192.168.2.11), so there the token still decides, as before. Debugbar 4 has six fewer `_debugbar/*` dev routes.
- **Fallback if PHP 8.3 cannot be installed in time:** deploy the commit `3c8c434` instead (Laravel 12.71 on PHP 8.2; it is the old U0 to U2
  deployment). Use the older bundle `laravel-12-37f5a62.zip` (still valid for it, section 0), skip section 2.0 and step 5.5, name the rollback copy
  `<folder>-before-laravel-12`, and note that step 10's `optimize:clear` is then still a good idea but not required. U3 would then be a second, smaller
  window later: PHP 8.3 first, then section 3 from step 4 with the Laravel 13 bundle.

## 9. The U4 window (Laravel Mix → Vite assets), on a later day than the U0 – U3 window

**What it changes:** only the built files in `public\` (`assets`, `backend`, `css`, `fonts`, `js`, `web`, `mix-manifest.json`) and the code to the
tag `laravel-13.42-vite-dry-ready`, which also holds the fixes of the 2026-11-13 module audit (PHP code, views and scripts: the theme switch
is POST only, adding a patient checks the e-mail like editing does, Front CMS shows its about-us pictures) and of the DRY pairs pass (a nurse can
save a change to a medicine generic, the dispense-record edit and consultation-medicines pages show their headings, a failed password reset shows a
generic error; shared views and helpers that change no page). No
migration, no `.env` key, no new PHP package (`composer.lock` is the same as in `laravel-13.42-excel4-ready`). The pages look and work the same: on the laptop every page loads the same scripts and stylesheets in the same order
(the link crawl, 1,367 pages, 15 kinds of user) and the Mix and Vite builds render every element of 13 pages identically, light and dark.

**The bundle:** `storage\app\deploy-bundle\laravel-13-ae822d9.zip` (50,268,128 bytes = 48 MB, 9,564 files; git ignores that folder) with
`laravel-13-ae822d9.zip.sha256` beside it (SHA-256 `60C551393ADB8CE572888757D1A03B6AF99F7BE0B0414B091AE86E1E47C15A3E`), built 2026-11-14 from a clean clone of the tag the same way as below.
**Checked:** a second clean clone built byte-identical output (800 files, and the same `vendor\`); the zip hash (also after the copy into the bundle folder); all 9,563 `SHA256SUMS.txt` lines match, nothing unlisted, no `.env`, uploads, `storage\` or dev package; against `laravel-13-d67dc6f.zip` its `vendor\` differs only in `vendor\composer\` (the commit hash and the class map) and its `public\` only in `js\pages.js`, `js\front-pages.js`, `js\front-third-party.js` (the three bundles that hold `helper.js`) and `mix-manifest.json`; over a fresh clone of the tag it boots Laravel 13.42.0 with 505 routes, all 381 views compile, `check-platform-reqs` is clean, all 799 files named in `mix-manifest.json` exist and `mix()` resolves through it, no `public\hot`; PHP 8.2.35 refuses it; the Excel 4 code over this `vendor\` (the rollback) boots 13.42.0 with 505 routes and finds its own older classes. `composer audit` could not reach packagist.org (no internet on the laptop that morning); `composer.lock` is the same as at the last clean audit (2026-11-13). Rule: `git diff --stat ae822d9..<commit> -- composer.lock package-lock.json build-assets.mjs resources` must print nothing,
otherwise rebuild. **First fallback pair** (without the DRY pairs pass): `laravel-13.42-vite-audit-ready` with `storage\app\deploy-bundle\laravel-13-d67dc6f.zip` (50,268,051 bytes = 48 MB, 9,564 files; git ignores that folder) with
`laravel-13-d67dc6f.zip.sha256` beside it (SHA-256 `D6A7116C02EBF4356EDC6478403CDF58B9231765A4463777239681E572735287`), built 2026-11-13 evening from a clean clone of the tag the same way as below.
**Checked:** a second clean clone built byte-identical output (800 files); the zip hash (also after the copy into the bundle folder); all 9,563 `SHA256SUMS.txt` lines match, nothing unlisted, no `.env`, uploads, `storage\` or dev package; its `vendor\` equals `laravel-13-32d49d8.zip`'s except `vendor\composer\` (the commit hash and the class map, which lists the audit's two new classes) and its `public\` only in `js\pages.js` and `mix-manifest.json` (the shared account actions, the POST theme switch); over a fresh clone of the tag it boots Laravel 13.42.0 with 505 routes, all 381 views compile, `check-platform-reqs` and `audit` are clean, all 799 files named in `mix-manifest.json` exist and `mix()` resolves through it, no `public\hot`; PHP 8.2.35 refuses it. Rule: `git diff --stat d67dc6f..<commit> -- composer.lock package-lock.json build-assets.mjs resources` must print nothing,
otherwise rebuild. **Second fallback pair** (without the audit's fixes either): `laravel-13.42-vite-ready` with `storage\app\deploy-bundle\laravel-13-32d49d8.zip` (50,268,008 bytes = 48 MB, 9,564 files; git ignores that folder) with
`laravel-13-32d49d8.zip.sha256` beside it (SHA-256 `38ECBB5252F679F17BC90B244574052AB783BE00599A9BBC0D38D790B0EF5705`), built 2026-11-13 from a clean
clone of the tag with PHP 8.3.36 (`composer install --no-dev --optimize-autoloader`: 119 packages = the lock's `packages` block) and Node 22.23.4
(`npm ci ; npm run prod`, which now runs `build-assets.mjs`). **Checked:** a second clean clone built byte-identical output (800 files); the zip hash
(also after the copy into the bundle folder); all 9,563 `SHA256SUMS.txt` lines match, nothing unlisted, no `.env`, uploads, `storage\` or dev package;
over a fresh clone of the tag it boots Laravel 13.42.0 with 505 routes, all 380 views compile, `check-platform-reqs` and `audit` are clean, all 799
files named in `mix-manifest.json` exist and `mix()` resolves through it, no `public\hot`; PHP 8.2.35 refuses it. The same rule keeps it valid:
`git diff --stat 32d49d8..<commit> -- composer.lock package-lock.json build-assets.mjs resources` must print nothing, otherwise rebuild.
**2026-11-13 evening:** the module audit changed `package-lock.json` (`npm audit fix`) and `resources\` (views, scripts) after `32d49d8`, so the newer code needed its own bundle: the pair above (`laravel-13.42-vite-audit-ready` + `laravel-13-d67dc6f.zip`). For the fallback pair use its tag and `32d49d8` in steps 4 and 5. **2026-11-14:** the DRY pairs pass changed `resources\` after `d67dc6f`, so it got its own bundle too: the pair at the top of this section (`laravel-13.42-vite-dry-ready` + `laravel-13-ae822d9.zip`). For a fallback pair use its tag and commit (`d67dc6f` or `32d49d8`) in steps 4 and 5.

| Step | What | Check |
|---|---|---|
| 0 | The U0 – U3 window was kept (section 3 step 13) and the clinic has worked on it for at least a few days. The PC runs PHP 8.3 and the tag `laravel-13.42-excel4-ready` (`git log -1` shows `bcf7ad7`). | as stated |
| 1 | After the 6:00 PM backup, everybody signed out; in the server window **Ctrl+C**. | window closed |
| 2 | Copy the build folders `public\assets, backend, css, fonts, js, web` and `public\mix-manifest.json` to `<folder>-public-before-u4` (this is the rollback). | the copy opens |
| 3 | Check the zip hash (`Get-FileHash`), unzip it next to the project. | hash equals the `.sha256` line |
| 4 | Code: `git fetch --tags` ; `git status` (must be clean) ; `git checkout laravel-13.42-vite-dry-ready`. | `git log -1` shows `ae822d9` |
| 5 | Delete `public\assets, backend, css, fonts, js, web` and `public\mix-manifest.json` (never `uploads`, `webfonts`, `messages.js`, `index.php`), then copy the bundle's `public\` folders in, then `php artisan vendor:publish --tag=log-viewer-assets --force`. Replace `vendor\` from this bundle too (same packages; its class map lists the new classes, which an older `vendor\` would still find by their path). | `public\mix-manifest.json` exists; `public\hot` does **not** |
| 6 | `php artisan optimize:clear`, then start `startup-dev-environment.bat`. | web, Reverb, scheduler `[ok]` |
| 7 | From a second device on the LAN address, every browser tab reloaded with **Ctrl+F5**: sign in; dashboard; a list with its search; the new-patient form (date picker, Select2, phone field); a consultation; Report Generation and one Excel and one PDF download; the queue board; dark mode for one user (switch it on and off); Front CMS shows the three about-us pictures; the Live updates badge. | no broken layout, no error message |
| 8 | **Keep or roll back** within 30 minutes. | |

**Rollback (U4 only):** Ctrl+C ; `git checkout laravel-13.42-excel4-ready` ; delete the six build folders and `mix-manifest.json` from `public\` and copy
back the ones from `<folder>-public-before-u4` ; `php artisan vendor:publish --tag=log-viewer-assets --force` ; `php artisan optimize:clear` ; start. `vendor\` can stay (the same
packages; checked 2026-11-14: the Excel 4 code over this `vendor\` boots with all 505 routes and finds its older classes by their path; the newer classes in the class map are never asked for). No database step: U4 changes no data.
