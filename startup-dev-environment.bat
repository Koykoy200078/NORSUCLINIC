@echo off
setlocal EnableExtensions
REM ============================================================
REM NORSUCLINIC Development Environment Startup Script
REM This script automatically:
REM 1. Locates project root from this BAT file location
REM 2. Detects LAN IPv4 for network access
REM 3. Starts WAMP Server
REM 4. Clears Laravel caches
REM 5. Starts Laravel development server
REM ============================================================

echo.
echo ============================================================
echo   NORSUCLINIC - Starting Development Environment
echo ============================================================
echo.

set "WAMP_EXE=C:\wamp64\wampmanager.exe"
set "SERVER_HOST="
set "LAN_IP="
set "SERVER_PORT=8000"
set "CHECK_ONLY=0"

if /I "%~1"=="--check" set "CHECK_ONLY=1"

REM ============================================================
REM Step 1: Locate project root using this BAT file location
REM ============================================================
echo [1/5] Locating project root...
echo.

call :findProjectRoot "%~dp0"
if errorlevel 1 (
    echo ERROR: Could not find project root containing artisan.
    echo Checked from: %~dp0
    pause
    exit /b 1
)

echo Script location : %~dp0
echo Project root    : %PROJECT_ROOT%
echo.

cd /d "%PROJECT_ROOT%"
if errorlevel 1 (
    echo ERROR: Failed to navigate to project root.
    pause
    exit /b 1
)

if not exist "artisan" (
    echo ERROR: artisan not found in detected project root: %CD%
    pause
    exit /b 1
)

REM ============================================================
REM Step 2: Detect LAN IPv4 for network access
REM ============================================================
echo [2/5] Detecting LAN IPv4 address...
echo.

set "IP_TMP_FILE=%TEMP%\norsu_lan_ip.txt"
if exist "%IP_TMP_FILE%" del /f /q "%IP_TMP_FILE%" >nul 2>nul

ipconfig > "%IP_TMP_FILE%" 2>nul

for /f "tokens=2 delims=:" %%I in ('findstr /I "IPv4" "%IP_TMP_FILE%"') do if not defined LAN_IP set "LAN_IP=%%I"

if exist "%IP_TMP_FILE%" del /f /q "%IP_TMP_FILE%" >nul 2>nul

if defined LAN_IP set "LAN_IP=%LAN_IP: =%"
if defined LAN_IP for /f "tokens=1 delims=(" %%A in ("%LAN_IP%") do set "LAN_IP=%%A"

if defined LAN_IP if /I "%LAN_IP%"=="0.0.0.0" set "LAN_IP="
if defined LAN_IP if /I "%LAN_IP:~0,9%"=="127.0.0.1" set "LAN_IP="
if defined LAN_IP if /I "%LAN_IP:~0,8%"=="169.254." set "LAN_IP="

if defined LAN_IP goto lanIpResolved

set "SERVER_HOST=0.0.0.0"
echo WARNING: Could not detect LAN IPv4 automatically.
echo          Falling back to 0.0.0.0 ^(all interfaces^).
goto lanIpDetectionDone

:lanIpResolved
set "SERVER_HOST=%LAN_IP%"
echo Detected LAN IPv4: %SERVER_HOST%

:lanIpDetectionDone

echo.
REM ============================================================
REM Step 3: Start WAMP Server
REM ============================================================
echo [3/5] Starting WAMP Server...
echo.

REM Check if WAMP is already running
tasklist /FI "IMAGENAME eq wampmanager.exe" 2>NUL | find /I /N "wampmanager.exe">NUL
if errorlevel 1 (
    if exist "%WAMP_EXE%" (
        start "" "%WAMP_EXE%"
        echo WAMP Server started successfully!
    ) else (
        echo ERROR: WAMP Server not found at %WAMP_EXE%
        echo Please update the path in this script.
        pause
        exit /b 1
    )
) else (
    echo WAMP Server is already running!
)

echo Waiting for WAMP services to initialize (8 seconds)...
ping 127.0.0.1 -n 9 >nul

echo.
echo ============================================================
REM Step 4: Clear Laravel caches
REM ============================================================
echo [4/5] Validating PHP/Laravel runtime...
echo.

where php >nul 2>nul
if errorlevel 1 (
    echo ERROR: PHP command not found in PATH.
    echo Make sure WAMP PHP is available in PATH, then run again.
    pause
    exit /b 1
)

echo Working directory: %CD%
echo.

if "%CHECK_ONLY%"=="1" (
    echo Check mode complete. Project root and runtime look valid.
    echo Use without --check to run Laravel server.
    pause
    exit /b 0
)

echo.
echo ============================================================
echo [4/5] Clearing all Laravel caches...
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

REM Optimize clear then optimize
echo - Optimizing clear application...
php artisan optimize:clear

echo - Optimizing application...
php artisan optimize

echo.
echo All caches cleared successfully!
echo.

echo ============================================================
echo [5/5] Starting Laravel development server...
echo.
echo Starting Laravel development server...
echo Server will be accessible at:
echo   - http://127.0.0.1:%SERVER_PORT%
if /I "%SERVER_HOST%"=="0.0.0.0" (
    echo   - http://YOUR_LAN_IP:%SERVER_PORT%  ^(local network access^)
) else (
    echo   - http://%SERVER_HOST%:%SERVER_PORT%  ^(local network access^)
)
echo.
echo Press Ctrl+C to stop the server
echo.
echo ============================================================
echo.

php artisan serve --host=%SERVER_HOST% --port=%SERVER_PORT%

REM This line will only execute if the server is stopped
echo.
echo Development server stopped.
pause

goto :eof

:findProjectRoot
set "SEARCH_DIR=%~1"
if not defined SEARCH_DIR exit /b 1

:findProjectRootLoop
if exist "%SEARCH_DIR%artisan" (
    set "PROJECT_ROOT=%SEARCH_DIR%"
    exit /b 0
)

for %%I in ("%SEARCH_DIR%..") do set "PARENT_DIR=%%~fI"
if not "%PARENT_DIR:~-1%"=="\" set "PARENT_DIR=%PARENT_DIR%\"

if /I "%PARENT_DIR%"=="%SEARCH_DIR%" exit /b 1

set "SEARCH_DIR=%PARENT_DIR%"
goto findProjectRootLoop
