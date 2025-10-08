@echo off
REM ============================================================
REM NORSUCLINIC - Stop Development Server Script
REM This script stops all PHP and Laravel processes
REM ============================================================

echo.
echo ============================================================
echo   NORSUCLINIC - Stopping Development Server
echo ============================================================
echo.

echo Stopping all PHP processes...
echo.

REM Kill all PHP processes
taskkill /F /IM php.exe 2>nul

if %ERRORLEVEL% EQU 0 (
    echo SUCCESS: All PHP processes stopped.
) else (
    echo INFO: No PHP processes were running.
)

echo.
echo Development server stopped.
echo.
pause
