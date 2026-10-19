@echo off
REM ============================================================
REM Medical University Clinic - start the clinic server (CMD launcher)
REM
REM Runs startup-dev-environment.ps1 in THIS window: no extra windows, one terminal.
REM The page opens from http://localhost:8000, http://127.0.0.1:8000 and from the LAN address of this PC
REM (detected every time, so dynamic and static addresses both work). Ctrl+C stops everything.
REM
REM   startup-dev-environment.bat               start
REM   startup-dev-environment.bat -OpenBrowser  also open the page in the default browser
REM ============================================================

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0startup-dev-environment.ps1" %*
if errorlevel 1 pause
