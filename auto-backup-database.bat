@echo off
REM ============================================================
REM NORSUCLINIC - Auto Database Backup System
REM This script runs in background and:
REM 1. Checks database for changes every hour
REM 2. Creates backup only if new data detected
REM 3. Uses timestamp in backup filename
REM 4. Maintains backup log
REM ============================================================

setlocal enabledelayedexpansion

REM ============================================================
REM Configuration
REM ============================================================
set BACKUP_DIR=%~dp0database_backups
set LOG_FILE=%~dp0database_backups\backup_log.txt
set LAST_HASH_FILE=%~dp0database_backups\last_hash.txt
set MYSQL_PATH=C:\wamp64\bin\mysql\mysql8.0.39\bin
set MYSQLDUMP_PATH=C:\wamp64\bin\mysql\mysql8.0.39\bin

REM Database credentials (from .env)
set DB_HOST=127.0.0.1
set DB_PORT=3306
set DB_NAME=norsu_clinic
set DB_USER=root
set DB_PASS=Carvs@10072000

REM Backup interval in seconds (3600 = 1 hour)
set BACKUP_INTERVAL=3600

REM ============================================================
REM Create backup directory if not exists
REM ============================================================
if not exist "%BACKUP_DIR%" (
    mkdir "%BACKUP_DIR%"
    echo Database backup directory created: %BACKUP_DIR%
)

REM ============================================================
REM Initialize log file
REM ============================================================
if not exist "%LOG_FILE%" (
    echo Database Backup Log - Created %date% %time% > "%LOG_FILE%"
    echo ============================================================ >> "%LOG_FILE%"
    echo. >> "%LOG_FILE%"
)

echo.
echo ============================================================
echo   NORSUCLINIC - Auto Backup System Started
echo ============================================================
echo.
echo Backup Directory: %BACKUP_DIR%
echo Check Interval: Every %BACKUP_INTERVAL% seconds (1 hour)
echo.
echo Press Ctrl+C to stop the backup service
echo.
echo ============================================================
echo.

REM ============================================================
REM Main backup loop
REM ============================================================
:BACKUP_LOOP

REM Get current timestamp
for /f "tokens=2-4 delims=/ " %%a in ('date /t') do (set BACKUP_DATE=%%c-%%a-%%b)
for /f "tokens=1-2 delims=: " %%a in ('time /t') do (set BACKUP_TIME=%%a-%%b)
set TIMESTAMP=%BACKUP_DATE%_%BACKUP_TIME%
set TIMESTAMP=!TIMESTAMP::=-!
set TIMESTAMP=!TIMESTAMP: =!

echo [%date% %time%] Checking database for changes...

REM ============================================================
REM Calculate current database hash
REM ============================================================
set TEMP_DUMP=%TEMP%\temp_db_check.sql
"%MYSQLDUMP_PATH%\mysqldump.exe" --host=%DB_HOST% --port=%DB_PORT% --user=%DB_USER% --password=%DB_PASS% --quick --single-transaction --skip-comments --skip-extended-insert %DB_NAME% > "%TEMP_DUMP%" 2>nul

if %ERRORLEVEL% NEQ 0 (
    echo [%date% %time%] ERROR: Failed to connect to database! >> "%LOG_FILE%"
    echo ERROR: Failed to connect to database!
    echo Retrying in %BACKUP_INTERVAL% seconds...
    echo.
    timeout /t %BACKUP_INTERVAL% /nobreak >nul
    goto BACKUP_LOOP
)

REM Generate hash of current database state
for /f %%A in ('certutil -hashfile "%TEMP_DUMP%" MD5 ^| findstr /v "hash"') do set CURRENT_HASH=%%A

REM ============================================================
REM Compare with last backup hash
REM ============================================================
set NEEDS_BACKUP=0

if not exist "%LAST_HASH_FILE%" (
    echo [%date% %time%] First backup - no previous hash found
    set NEEDS_BACKUP=1
) else (
    set /p LAST_HASH=<"%LAST_HASH_FILE%"
    
    if "!CURRENT_HASH!" NEQ "!LAST_HASH!" (
        echo [%date% %time%] Database changes detected!
        set NEEDS_BACKUP=1
    ) else (
        echo [%date% %time%] No changes detected - skipping backup
    )
)

REM ============================================================
REM Perform backup if needed
REM ============================================================
if !NEEDS_BACKUP! EQU 1 (
    set BACKUP_FILE=%BACKUP_DIR%\norsuclinic_backup_%TIMESTAMP%.sql
    
    echo [%date% %time%] Creating backup: !BACKUP_FILE!
    
    "%MYSQLDUMP_PATH%\mysqldump.exe" --host=%DB_HOST% --port=%DB_PORT% --user=%DB_USER% --password=%DB_PASS% --routines --triggers --events --single-transaction %DB_NAME% > "!BACKUP_FILE!" 2>nul
    
    if !ERRORLEVEL! EQU 0 (
        REM Compress backup
        echo [%date% %time%] Compressing backup...
        powershell -command "Compress-Archive -Path '!BACKUP_FILE!' -DestinationPath '!BACKUP_FILE!.zip' -Force"
        
        if exist "!BACKUP_FILE!.zip" (
            del "!BACKUP_FILE!"
            set BACKUP_FILE=!BACKUP_FILE!.zip
        )
        
        REM Get backup file size
        for %%A in ("!BACKUP_FILE!") do set BACKUP_SIZE=%%~zA
        set /a BACKUP_SIZE_KB=!BACKUP_SIZE! / 1024
        
        echo [%date% %time%] ✓ Backup created successfully (!BACKUP_SIZE_KB! KB)
        echo [%date% %time%] ✓ Backup created: !BACKUP_FILE! (Size: !BACKUP_SIZE_KB! KB) >> "%LOG_FILE%"
        
        REM Save current hash
        echo !CURRENT_HASH!> "%LAST_HASH_FILE%"
        
        REM Clean old backups (keep last 30 days)
        echo [%date% %time%] Cleaning old backups (keeping last 30 days)...
        forfiles /P "%BACKUP_DIR%" /M *.zip /D -30 /C "cmd /c del @path" 2>nul
        
    ) else (
        echo [%date% %time%] ✗ ERROR: Backup failed!
        echo [%date% %time%] ✗ ERROR: Backup failed! >> "%LOG_FILE%"
    )
)

REM Clean temp file
if exist "%TEMP_DUMP%" del "%TEMP_DUMP%"

echo.
echo Next check in %BACKUP_INTERVAL% seconds (1 hour)...
echo.

REM ============================================================
REM Wait for next check
REM ============================================================
timeout /t %BACKUP_INTERVAL% /nobreak >nul

goto BACKUP_LOOP
