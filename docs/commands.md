# Commands — the log of commands added by the audit plan

**Kept by:** whoever adds or changes a command (Claude Code or Codex). **Rule:** add the command here, in the same commit
that adds it (name, what it does, whether it changes data, where and when to run it). Linked from
[AGENTS.md](../AGENTS.md), [Handover.md](../Handover.md), [STATUS.md](STATUS.md) and the
[audit plan](audit-plan-2026-10-08.md).

## How and where to run a command

All commands are **Artisan commands**. They run in a **terminal**, never in the browser; there is no menu item for them.

| | |
|---|---|
| **Where** | A terminal (PowerShell) opened **in the project folder**, the folder that contains the file `artisan`. On the development laptop that is `C:\Projects\NORSUCLINIC`. On the clinic copy it is the folder the system is installed in on the **clinic server PC** (the PC that holds the database), directly or through remote desktop. |
| **How** | `php artisan <command>`. `php` must be found by the terminal (on the laptop it is WAMP's `C:\wamp64\bin\php\php8.3.31\php.exe`). The command uses the database written in that folder's `.env`. |
| **Who** | The person who looks after the server. Not staff, nurses or doctors. |
| **Scheduled?** | No. Only `db:backup` runs by itself (hourly). Everything below is run by hand. |
| **Help** | `php artisan <command> --help` shows the options; `php artisan list db` lists the `db:` commands. |

Example (PowerShell):

```powershell
cd C:\Projects\NORSUCLINIC
php artisan db:integrity
```

## Summary

| Command | What it does | Changes data? | Run it when | Added |
|---|---|---|---|---|
| [`db:integrity`](#dbintegrity) | Checks that the database is what a correct installation looks like; prints one line per check; **exit code 1 if anything is an error** | **No** (read-only). `--compare-fresh` builds and then drops a throwaway schema of its own; it never touches the real one | after every `php artisan migrate`, after importing data, before and after an upgrade, whenever numbers on screen look wrong | Phase 1.5, 2026-10-09 |
| [`db:restore-foreign-keys`](#dbrestore-foreign-keys) | Adds the foreign keys a correct installation has and this database lacks | Changes the **structure** (adds keys); **never changes a row** | after a foreign key was reported missing and you have fixed its cause by hand | Phase 1.4, 2026-10-09 |

Also worth knowing: [what `php artisan migrate` now does](#what-php-artisan-migrate-now-does-phase-1) and the
[existing commands these two work with](#existing-commands-used-together-with-them).

---

## `db:integrity`

```powershell
php artisan db:integrity
php artisan db:integrity --compare-fresh
```

**Purpose.** The clinic's database was created on an old server (MyISAM tables, no foreign keys, zero dates). After the
upgrade this command proves the database is sound, and it keeps proving it afterwards. It is the check that
[audit plan §9 step 4](audit-plan-2026-10-08.md#9-clinic-copy--what-will-change-for-deployment-when-the-phases-are-done)
asks for.

**Options**

| Option | Meaning |
|---|---|
| *(none)* | The 12 checks below. Takes a few seconds. |
| `--compare-fresh` | Also builds a **fresh install in a throwaway schema** (it runs every migration there), compares tables, columns, indexes, foreign keys, views and engines with the real database, and drops the throwaway schema again. Needs the database user to be allowed to create a schema; takes about 10 seconds to a minute. A difference is printed as an error. |

**Exit code.** `0` = no errors (warnings are allowed). `1` = at least one error. A script can stop on it.

**The 12 checks**

| Check | Severity | What a problem means / what to do |
|---|---|---|
| Every table is InnoDB | error | A table is still MyISAM: foreign keys and transactions do not work for it. Run `php artisan migrate`. |
| All 47 foreign keys are in place | error | A key is missing; the line says why (a table is missing, the column types differ, or rows point at a record that no longer exists, with their values). If nothing blocks it: `php artisan db:restore-foreign-keys`. |
| No row points at a record that does not exist | error | A `xxx_id` column (without a foreign key) holds an id that has no parent. Fix the data by hand. |
| No zero dates | error / warning | A date column still holds `0000-00-00`. Error when the column allows NULL (run `migrate`), warning when it does not (fix by hand). |
| No duplicate numbers or keys | error | Two records share a number that must be unique (history number, request number, SKU, e-mail, university id, PRC number, settings key, batch number). |
| Medicine totals match their batches | error | A medicine's recorded quantity differs from the sum of its batches. |
| Batch balances match the stock ledger | error | A batch's quantity differs from the balance after its last ledger entry. |
| Consultation medicines match the stock ledger | error | The medicines on a consultation do not equal what the ledger deducted for it (a deleted consultation must have given everything back). |
| No stock without ledger rows | warning | Stock on the shelf that has no ledger row at all; the yearly inventory report needs one to know when it arrived. |
| No direct permissions on non-admin users | warning | A permission was given straight to a user instead of through the role. |
| No duplicate candidates | warning | Two medicines with the same name and strength, or two patients with the same name and birth date (ids only are printed). Merged later in plan Phase 8. |
| No pending data repairs | warning | What `text:repair-entities` and `phone:normalize` would still change (their dry runs). |

**Example** (the real clinic data on the development laptop, 2026-10-09; 1 error, 3 warnings):

```text
Database integrity - norsu_clinic
OK    Every table is InnoDB (foreign keys and transactions work)
ERROR All 47 foreign keys are in place
        - medicine_batches_medicine_id_foreign: 4 row(s) of medicine_batches.medicine_id point at medicines rows that do not exist (values: 1 (x1), 2 (x1), 28 (x2))
...
WARN  No duplicate candidates (medicines, patients)
        - (warning) medicines #41, #42: same name and strength
        - (warning) medicines #50, #55: same name and strength
WARN  No pending data repairs (encoded text, phone numbers)
        - (warning) phone:normalize would update 8 record(s) - run it with --apply after a backup

1 error(s), 3 warning(s). Errors must be fixed; nothing was changed.
```

**Where it is used in the plan:** Phase 1.5 (built), Phase 1.7 / 1.8 and Phase 9.2 (rehearsals on the clinic dump end with
it; the rehearsal runner calls `db:integrity --compare-fresh` itself), Phase 3.6 (will report direct permissions before they
are cleared), and the clinic deployment checklist ([STATUS §7](STATUS.md#7-to-do-on-the-clinic-copy-deployment-checklist)
step 3a).

**Code:** `app/Console/Commands/DatabaseIntegrity.php`, `app/Services/DatabaseIntegrityChecker.php`,
`app/Services/InventoryConsistency.php`, `app/Support/SchemaInspector.php`, `SchemaParity.php`, `ForeignKeyCatalog.php`.
**Tests:** `tests/Feature/Regression/DatabaseIntegrity*Test.php`, `SchemaParityTest.php`, `ForeignKeyCatalogTest.php`.

---

## `db:restore-foreign-keys`

```powershell
php artisan db:backup                       # first: it changes the structure
php artisan db:restore-foreign-keys
```

**Purpose.** Adds every foreign key that a fresh install has (47) and this database lacks. It is the same step as the
migration `2026_10_08_120000_restore_missing_foreign_keys`, for running **again** once the cause of a skipped key has been
fixed by hand (rows pointing at a record that no longer exists). A migration runs only once; this command can be repeated.

**Rules it follows.** A key is added only when both tables are InnoDB, the two columns have the same type, and no row points
at a parent that does not exist. Otherwise the key is **skipped and the reason is printed**. It never changes, moves or
deletes a row. Running it twice is harmless.

**Output.** `Added N foreign key(s); M were already in place.` followed by one line per skipped key with its reason.
**Exit code:** `0`.

**Where it is used in the plan:** Phase 1.4 (built). Needed after the owner decides what to do with the 4 stock batches of
deleted medicines ([STATUS §6](STATUS.md#6-needs-your-decision-)): once they are resolved, this command adds the last key
(`medicine_batches_medicine_id_foreign`) and `db:integrity` turns green.

**Code:** `app/Console/Commands/RestoreForeignKeys.php`, `app/Support/LegacySchemaUpgrade.php`.
**Tests:** `tests/Feature/Regression/LegacyForeignKeysTest.php`, `DatabaseIntegrityCommandTest.php`.

---

## What `php artisan migrate` now does (Phase 1)

These are not commands of their own; they run inside the normal `php artisan migrate` (take a backup first,
`php artisan db:backup`).

| Migration | Effect |
|---|---|
| `2026_09_30_110000_convert_legacy_tables_to_innodb` | Converts every MyISAM table to InnoDB (rows, indexes, next id kept; views untouched). Runs first. A no-op on a database that is InnoDB already. |
| `2026_09_30_121500_normalize_zero_dates` | Turns zero and half-zero dates into NULL where the column allows NULL; lists the columns that do not. |
| `2026_10_08_120000_restore_missing_foreign_keys` | Adds the missing foreign keys (see above); prints each skipped key and why. |
| `2026_10_02_090000_create_illness_and_service_tables` *(changed)* | Can now be re-run: it creates only the tables that are missing, so a database left half-built by an earlier failed attempt can finish. |

New tables are always InnoDB, whatever the server's default is: `config/database.php` reads `DB_ENGINE` (default `InnoDB`).

## Existing commands used together with them

| Command | What it does | Changes data? |
|---|---|---|
| `php artisan db:backup [--force]` | Dumps the database into `storage/app/backups/scheduled` (hourly by itself; skipped when nothing changed; keeps every backup of the last 2 days, then one per day for 30 days) | No |
| `php artisan inventory:reconcile` | Read-only stock report: totals vs batches, batches vs ledger, "no expiry" placeholder batches, unlinked stock-in lines. Its stock checks are shared with `db:integrity`. | No |
| `php artisan text:repair-entities [--apply]` | Decodes `&amp;` / `&lt;` / `&gt;` left in clinical text. **Without `--apply` it only counts.** Take a backup before `--apply`; run `--apply` once. | Only with `--apply` |
| `php artisan phone:normalize [--apply]` | Stores phone numbers in the Philippine (+63) format. **Without `--apply` it only counts.** Take a backup before `--apply`. | Only with `--apply` |

## Developer tools outside the repository

These are not part of the system. They handle the clinic dump (patient data), so they live **outside** the repository, in
`C:\Users\Franc\.claude\projects\C--Projects-NORSUCLINIC\tools\rehearsal\` ([plan Phase 0.4](audit-plan-2026-10-08.md)).
Run them with `php <that folder>\<file>`.

| File | Use |
|---|---|
| `rehearse.php --label=<name> --engine=MyISAM\|InnoDB\|server` | Drops `norsu_clinic`, re-imports the dump, runs `migrate` under the chosen default engine, compares before / after, runs `db:integrity --compare-fresh`. About 30 seconds. **Replaces the data in `norsu_clinic`.** |
| `create-qa-accounts.php` | (Re)creates the four QA logins `@qa.test` on the clinic data; passwords go only to the git-ignored `storage/app/qa-accounts.json`. Run it again after every re-import. |
| `restore-backup.php <file.sql>` | Drops `norsu_clinic` and restores a backup into it. |
| `inspect-db.php` | Read-only: engines, foreign keys, migrations and row counts of the connected database. |
| `RouteRoleMatrixTest.php`, `compare-matrix.php`, `analyze-matrix.php` | Route × role matrix (run with phpunit; never together with another phpunit process) and its comparison. |

## Change log

| Date | Phase | Added or changed |
|---|---|---|
| 2026-10-09 | 1.4 | `db:restore-foreign-keys` added |
| 2026-10-09 | 1.5 | `db:integrity` (and `--compare-fresh`) added; `inventory:reconcile` now shares its stock checks with it (same output) |
| 2026-10-09 | 1.1 – 1.3 | `DB_ENGINE` setting; migrations `110000`, `121500`, `2026_10_08_120000` added |
