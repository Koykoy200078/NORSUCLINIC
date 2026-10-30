@echo off
REM ============================================================
REM Medical University Clinic - start the clinic server (CMD launcher)
REM
REM Runs startup-dev-environment.ps1 in THIS window: no extra windows, one terminal.
REM  - WampServer is opened only when php or mysql is missing from the Windows PATH (system and user)
REM  - the page opens in the default browser; with no default browser a list of the installed ones is shown
REM  - the page opens from http://localhost:8000, http://127.0.0.1:8000 and from the LAN address of this PC
REM    (detected every time, so dynamic and static addresses both work). Ctrl+C stops everything.
REM
REM   startup-dev-environment.bat              start
REM   startup-dev-environment.bat -NoBrowser   do not open a browser
REM   startup-dev-environment.bat -CheckOnly   only report what was found, start nothing
REM   startup-dev-environment.bat -NoBackup    do not take the missed 6:00 PM backup at the start
REM
REM The scheduler (alerts, expired stock) and the DATABASE BACKUP at 6:00 PM Philippine time live in this same window too.
REM ============================================================

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0startup-dev-environment.ps1" %*
if errorlevel 1 pause
