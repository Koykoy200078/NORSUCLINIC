@echo off
REM ============================================================
REM NORSUCLINIC - Laravel Task Scheduler Runner
REM This script runs the Laravel scheduler which handles:
REM - Hourly database backups (only if changes detected)
REM - Other scheduled tasks
REM ============================================================

REM Change to project directory
cd /d "%~dp0"

REM Run Laravel scheduler
php artisan schedule:run >> storage/logs/scheduler.log 2>&1

REM Exit
exit /b 0
