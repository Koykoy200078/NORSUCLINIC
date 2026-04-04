@echo off
REM ============================================================
REM NORSUCLINIC - Management Console Launcher
REM Double-click this file or pass a command:
REM   norsuclinic.bat start | stop | backup | migrate
REM   norsuclinic.bat cache | opcache | scheduler | cleanup
REM ============================================================
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0norsuclinic.ps1" %1
