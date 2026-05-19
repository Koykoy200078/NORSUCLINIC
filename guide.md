Use Windows Task Scheduler so it starts automatically at login.

The start flow in automation.ps1 already does what you asked:

Auto-start WAMP
Resolve IPv4 (or use 0.0.0.0)
Kill process using port 8000
Start Laravel on port 8000
Set it up like this:

Open Task Scheduler
Click Create Task
General tab:
Name: NORSUCLINIC Auto Startup
Check: Run with highest privileges
Triggers tab:
New -> At log on -> Your user
Optional: Delay task for 15-30 seconds
Actions tab:
Program/script: powershell.exe
Add arguments:
-NoProfile -ExecutionPolicy Bypass -File "C:\Projects\NORSUCLINIC\automation.ps1" start -BindHost auto
Start in:
C:\Projects\NORSUCLINIC
Save, then right-click task -> Run (test)
If you want always all interfaces:
Use this instead of auto:
-BindHost 0.0.0.0

Quick command-line create:
schtasks /Create /TN "NORSUCLINIC Auto Startup" /SC ONLOGON /RL HIGHEST /TR "powershell.exe -NoProfile -ExecutionPolicy Bypass -File "C:\Projects\NORSUCLINIC\automation.ps1" start -BindHost auto" /F

Remove later if needed:
schtasks /Delete /TN "NORSUCLINIC Auto Startup" /F

If you want, I can add an install-autostart command directly inside automation.ps1 so you can enable/disable this with one script command.
