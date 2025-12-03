@echo off
REM ============================================================
REM NORSUCLINIC - Migrate Fresh While Keeping Users
REM This script:
REM 1. Backs up the users table to a JSON file
REM 2. Runs migrate:fresh --seed
REM 3. Restores the users back to the database
REM 4. Cleans up the backup file
REM ============================================================

echo.
echo ============================================================
echo   NORSUCLINIC - Migrate Fresh (Keep Users)
echo ============================================================
echo.

REM ============================================================
REM Step 1: Backup Users Table
REM ============================================================
echo [1/4] Backing up users table...
echo.

php artisan tinker --execute="file_put_contents('users_backup.json', DB::table('users')->get()->toJson());"

if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to backup users table!
    echo Please check your database connection and try again.
    pause
    exit /b 1
)

if not exist "users_backup.json" (
    echo ERROR: Backup file was not created!
    pause
    exit /b 1
)

echo Users backed up successfully to users_backup.json
echo.

REM ============================================================
REM Step 2: Confirm before proceeding
REM ============================================================
echo [2/4] Ready to run migrate:fresh --seed
echo.
echo WARNING: This will DROP ALL TABLES except users data!
echo.
set /p confirm="Are you sure you want to continue? (y/n): "

if /i not "%confirm%"=="y" (
    echo.
    echo Migration cancelled by user.
    del users_backup.json
    pause
    exit /b 0
)

echo.

REM ============================================================
REM Step 3: Run migrate:fresh --seed
REM ============================================================
echo [3/4] Running migrate:fresh --seed...
echo.

php artisan migrate:fresh --seed

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERROR: Migration failed!
    echo Users backup is still available at users_backup.json
    echo You can manually restore it if needed.
    pause
    exit /b 1
)

echo.
echo Migration completed successfully!
echo.

REM ============================================================
REM Step 4: Restore Users
REM ============================================================
echo [4/4] Restoring users table...
echo.

php artisan tinker --execute="DB::table('users')->truncate(); $users = json_decode(file_get_contents('users_backup.json'), true); DB::table('users')->insert($users); echo 'Restored ' . count($users) . ' users';"

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERROR: Failed to restore users!
    echo Users backup is still available at users_backup.json
    echo You can manually restore it using:
    echo php artisan tinker
    echo   $users = json_decode(file_get_contents('users_backup.json'), true);
    echo   DB::table('users')->insert($users);
    pause
    exit /b 1
)

echo.

REM ============================================================
REM Step 5: Cleanup
REM ============================================================
echo Cleaning up backup file...
del users_backup.json

echo.
echo ============================================================
echo   Migration Completed Successfully!
echo   All tables refreshed, users preserved.
echo ============================================================
echo.

pause
