# NORSUCLINIC Auto-Startup - Quick Reference

## 🎯 Scripts Created

| File                          | Purpose                        | When to Use                               |
| ----------------------------- | ------------------------------ | ----------------------------------------- |
| `startup-dev-environment.bat` | Full startup with console      | ✅ **Recommended** - See what's happening |
| `startup-dev-environment.ps1` | PowerShell version with colors | Advanced users who prefer PowerShell      |
| `startup-silent.vbs`          | Background silent runner       | Silent auto-start, no console             |
| `stop-dev-server.bat`         | Stop all PHP processes         | Quick server shutdown                     |

## ⚡ Quick Start (30 seconds)

### **Option 1: Auto-Start on Windows Login**

1. Press `Win + R`
2. Type: `shell:startup` and press Enter
3. Right-click → New → Shortcut
4. Target: `D:\Projects\NORSUCLINIC\startup-dev-environment.bat`
5. Click Finish
6. ✅ Done! Restarts with Windows

### **Option 2: Desktop Shortcut (Manual)**

1. Right-click Desktop → New → Shortcut
2. Target: `D:\Projects\NORSUCLINIC\startup-dev-environment.bat`
3. Name it: `Start NORSUCLINIC Server`
4. ✅ Done! Double-click to start

## 🚀 What Happens Automatically

```
✓ Starts WAMP Server
✓ Waits 15 seconds for services
✓ Clears all Laravel caches
   - Application cache
   - Configuration cache
   - Route cache
   - View cache
   - Compiled files
   - OPcache (via optimize:clear)
✓ Starts Laravel on http://192.168.180.100:8000
```

## 🌐 Access Your App

-   **On this computer:** http://127.0.0.1:8000
-   **On network devices:** http://192.168.180.100:8000
-   **Admin panel:** http://192.168.180.100:8000/admin
-   **Staff edit:** http://192.168.180.100:8000/admin/staffs/3/edit

## 🛑 Stop the Server

### Method 1: Console Window

```
Press Ctrl+C in the console window
```

### Method 2: Quick Kill Script

```
Double-click: stop-dev-server.bat
```

### Method 3: PowerShell Command

```powershell
Get-Process -Name php | Stop-Process -Force
```

## ⚙️ Customize Settings

Edit `startup-dev-environment.bat`:

```batch
# Change WAMP path (line ~25):
if exist "C:\wamp64\wampmanager.exe" (

# Change server IP (last line):
php artisan serve --host=192.168.180.100

# Change startup delay (line ~43):
timeout /t 15 /nobreak >nul
```

## 🔍 Troubleshooting

| Problem               | Solution                                              |
| --------------------- | ----------------------------------------------------- |
| WAMP doesn't start    | Check path in script matches your WAMP installation   |
| Port 8000 in use      | Change to `--port=8001` in script                     |
| Caches not clearing   | Ensure PHP is in PATH: `php -v`                       |
| PowerShell won't run  | `Set-ExecutionPolicy RemoteSigned -Scope CurrentUser` |
| Task doesn't auto-run | Check "Run with highest privileges" in Task Scheduler |

## 📱 Test Commands

```bash
# Test if WAMP is running
tasklist | findstr "wampmanager httpd mysqld"

# Test Laravel manually
php artisan serve --host=192.168.180.100

# Clear caches manually
php artisan optimize:clear

# Check PHP version
php -v

# Check what's using port 8000
netstat -ano | findstr :8000
```

## 📋 Checklist After Setup

-   [ ] WAMP icon shows green in system tray
-   [ ] Browser opens http://192.168.180.100:8000
-   [ ] No 500 errors on staff pages
-   [ ] Can edit staff without 422 errors
-   [ ] No "display_name" column errors
-   [ ] Caches clear automatically on startup

## 💡 Pro Tips

1. **First Time:** Run the `.bat` file manually first to see if everything works
2. **Delay Start:** In Task Scheduler, add 30-second delay for more reliable startup
3. **Silent Mode:** Use `.vbs` file only after confirming `.bat` works
4. **Network Access:** Other devices on `192.168.180.x` can access your dev server
5. **Stop Quickly:** Keep `stop-dev-server.bat` shortcut on desktop

## 📚 Full Documentation

See `AUTO-STARTUP-GUIDE.md` for:

-   Detailed Task Scheduler setup
-   Advanced configuration options
-   Network access setup
-   Security considerations
-   Full troubleshooting guide

---

**Quick Support Checklist:**

1. ✅ WAMP running? (Check system tray)
2. ✅ PHP working? (`php -v`)
3. ✅ Port free? (`netstat -ano | findstr :8000`)
4. ✅ Caches cleared? (Check console output)
5. ✅ Correct IP? (192.168.180.100 in script)

**Last Updated:** October 8, 2025
