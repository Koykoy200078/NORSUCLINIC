# Automation Deep Audit - 2026-05-18

## Scope

- Reviewed repository-level automation and helper scripts.
- Traced script references across source, docs, and config files.
- Validated Laravel scheduler integration points.

## Deep Findings

1. High risk: hardcoded machine-specific paths

- Multiple scripts hardcoded values such as `C:\wamp64` and a user-specific project path.
- This made automation non-portable across developer machines.

2. High risk: scheduler backup command mismatch

- `app/Console/Kernel.php` schedules `db:backup` hourly.
- No `db:backup` command implementation existed before this cleanup.
- Result: scheduled backup automation could silently fail.

3. High risk: duplicated and conflicting automation

- Startup, cache clear, migrate, backup, and scheduler tasks existed in multiple scripts.
- Implementations were inconsistent, including different DB password behavior and flow.

4. Medium risk: stale one-time cleanup scripts

- Old cleanup scripts targeted dated migration/docs cleanup tasks.
- They were no longer referenced and increased accidental-deletion risk.

5. Medium risk: plain-text credential exposure in scripts

- At least one batch script contained a plain `DB_PASSWORD` value.

## Changes Applied

### New consolidated automation

- Added `automation.ps1` as the single entrypoint for local automation.
- Added `automation.bat` wrapper for double-click and cmd users.

Supported commands:

- `start`
- `stop`
- `cache`
- `schedule`
- `backup`
- `migrate`
- `status`
- `help`

### Scheduler backup fix

- Added `app/Console/Commands/DatabaseBackup.php` implementing `php artisan db:backup`.
- This restores the scheduled backup path used by `app/Console/Kernel.php`.
- Backups are written to `storage/app/backups/scheduled` with prune support.

### Legacy automation cleanup

Removed these legacy scripts from repository root:

- `auto-backup-database.bat`
- `auto-backup-database.ps1`
- `cleanup-database.ps1`
- `cleanup-documentation.ps1`
- `cleanup-reviews-only.ps1`
- `fix-opcache-performance.ps1`
- `migrate-keep-users.bat`
- `migrate-keep-users.ps1`
- `norsuclinic.bat`
- `norsuclinic.ps1`
- `run-scheduler.bat`
- `startup-dev-environment.bat`
- `startup-dev-environment.ps1`
- `startup-silent.vbs`
- `stop-dev-server.bat`

## Notes

- Root-level one-off PHP utility files were not removed in this pass.
- If needed, they can be migrated into dedicated Artisan commands in a follow-up cleanup.
