# Migrate Fresh While Keeping Users

This directory contains scripts to run `migrate:fresh --seed` while preserving the users table.

## Available Scripts

### Windows Batch File (`.bat`)

-   **File**: `migrate-keep-users.bat`
-   **Usage**: Double-click the file or run from command prompt
    ```cmd
    migrate-keep-users.bat
    ```

### PowerShell Script (`.ps1`)

-   **File**: `migrate-keep-users.ps1`
-   **Usage**: Right-click and select "Run with PowerShell" or run from PowerShell:
    ```powershell
    .\migrate-keep-users.ps1
    ```

## What These Scripts Do

1. **Backup Users** - Exports all users to `users_backup.json`
2. **Confirm Action** - Asks for confirmation before proceeding
3. **Run Migration** - Executes `php artisan migrate:fresh --seed`
4. **Restore Users** - Imports the users back into the database
5. **Cleanup** - Removes the temporary backup file

## Safety Features

-   ✅ Confirmation prompt before migration
-   ✅ Error checking at each step
-   ✅ Keeps backup file if restoration fails
-   ✅ Clear error messages with recovery instructions
-   ✅ Exit codes for automation

## Manual Recovery

If the script fails to restore users, the backup file `users_backup.json` will remain. You can manually restore it:

```bash
php artisan tinker
```

Then in tinker:

```php
$users = json_decode(file_get_contents('users_backup.json'), true);
DB::table('users')->insert($users);
exit
```

## Requirements

-   PHP CLI available in PATH
-   Laravel project with working database connection
-   Artisan commands functional
-   Users table must exist before running

## Warning

⚠️ **This will drop ALL tables except the users data.** Make sure you have recent backups before running!
