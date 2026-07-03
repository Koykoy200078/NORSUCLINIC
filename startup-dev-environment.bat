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
REM Detect this machine's CURRENT LAN IPv4 dynamically (no hardcoded/static IP)
REM ============================================================
set "SERVER_HOST=127.0.0.1"
powershell -NoProfile -Command "(Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notlike '127.*' -and $_.IPAddress -notlike '169.254.*' -and $_.PrefixOrigin -ne 'WellKnown' } | Sort-Object InterfaceMetric | Select-Object -First 1).IPAddress" > "%TEMP%\norsu_ip.txt" 2>nul
set /p SERVER_HOST=<"%TEMP%\norsu_ip.txt"
del "%TEMP%\norsu_ip.txt" >nul 2>&1
if "%SERVER_HOST%"=="" set "SERVER_HOST=127.0.0.1"
echo Detected server IP: %SERVER_HOST%
echo.

REM ============================================================
REM Step 1: Start WAMP Server
REM ============================================================
echo [1/4] Starting WAMP Server...
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
echo [2/4] Navigating to project directory...
echo.

cd /d "%~dp0"
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
echo [3/4] Clearing all Laravel caches...
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
REM Step 4: Start Laravel development server
REM ============================================================
echo [4/4] Starting Laravel development server...
echo.
echo Server will be accessible at:
echo   - http://%SERVER_HOST%:8000  (LAN - auto-detected IP)
echo   - http://127.0.0.1:8000
echo.
echo Press Ctrl+C to stop the server
echo.
echo ============================================================
echo.

REM Open the default browser to the server once it is up (delayed, minimized helper)
start "" /min cmd /c "timeout /t 4 /nobreak >nul & explorer http://%SERVER_HOST%:8000"

REM Start Laravel server on the auto-detected IP
php artisan serve --host=%SERVER_HOST%

REM This line will only execute if the server is stopped
echo.
echo Development server stopped.
pause