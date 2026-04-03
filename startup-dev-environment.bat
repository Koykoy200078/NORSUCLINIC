@echo off
REM ============================================================
REM NORSUCLINIC Development Environment Startup Script
REM This script automatically:
REM 1. Starts WAMP Server
REM 2. Clears all Laravel caches
REM 3. Starts Auto Database Backup System
REM 4. Starts Laravel development server
REM ============================================================

echo.
echo ============================================================
echo   NORSUCLINIC - Starting Development Environment
echo ============================================================
echo.

REM ============================================================
REM Step 1: Start WAMP Server
REM ============================================================
echo [1/5] Starting WAMP Server...
echo.

REM Check if WAMP is already running
tasklist /FI "IMAGENAME eq wampmanager.exe" 2>NUL | find /I /N "wampmanager.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo WAMP Server is already running!
) else (
    REM Start WAMP Server (adjust path if your WAMP is installed elsewhere)
    if exist "C:\wamp64\wampmanager.exe" (
        start "" "C:\wamp64\wampmanager.exe"
        echo WAMP Server started successfully!
    ) else (
        echo ERROR: WAMP Server not found at C:\wamp64\wampmanager.exe
        echo Please update the path in this script.
        pause
        exit /b 1
    )
)

REM Wait for WAMP services to initialize
echo Waiting for WAMP services to start (15 seconds)...
timeout /t 15 /nobreak >nul

echo.
echo ============================================================
REM Step 2: Navigate to project directory
REM ============================================================
echo [2/5] Navigating to project directory...
echo.

cd /d "C:\Users\User\Desktop\Clinic\NORSUCLINIC"
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to navigate to project directory!
    pause
    exit /b 1
)

echo Current directory: %CD%
echo.

echo ============================================================
REM Step 3: Clear all Laravel caches
REM ============================================================
echo [3/5] Clearing all Laravel caches...
echo.

REM Clear application cache
echo - Clearing application cache...
php artisan cache:clear

REM Clear configuration cache
echo - Clearing configuration cache...
php artisan config:clear

REM Clear route cache
echo - Clearing route cache...
php artisan route:clear

REM Clear view cache
echo - Clearing compiled views...
php artisan view:clear

REM Clear compiled services and packages
echo - Clearing compiled services...
php artisan clear-compiled

REM Optimize (optional - uncomment if needed)
echo - Optimizing clear application...
php artisan optimize:clear

echo - Optimizing application...
php artisan optimize

echo.
echo All caches cleared successfully!
echo.

echo ============================================================
REM Step 4: Start Auto Database Backup System
REM ============================================================
echo [4/5] Starting Auto Database Backup System...
echo.

REM Start backup system in minimized window
start "NORSUCLINIC Auto Backup" /min cmd /c "%~dp0auto-backup-database.bat"

if %ERRORLEVEL% EQU 0 (
    echo Auto Backup System started successfully!
    echo - Checking for database changes every hour
    echo - Backups saved to: database_backups\
    echo - Running in background (minimized window)
) else (
    echo WARNING: Failed to start Auto Backup System!
    echo You can start it manually: auto-backup-database.bat
)

echo.

echo ============================================================
REM Step 5: Start Laravel development server
REM ============================================================
echo [5/5] Starting Laravel development server...
echo.
echo Server will be accessible at:
echo   - http://192.168.180.100:8000
echo   - http://127.0.0.1:8000
echo.
echo Press Ctrl+C to stop the server
echo.
echo ============================================================
echo.

REM Start Laravel server with OPcache timestamp revalidation disabled.
REM This prevents PHP from stat()-checking 12,000 vendor files every 2s (was causing ~12s page loads).
REM Restart the server after making PHP file changes to pick up new code.
php -d opcache.revalidate_freq=0 -d opcache.validate_timestamps=0 artisan serve --host=192.168.180.100

REM This line will only execute if the server is stopped
echo.
echo Development server stopped.
pause
