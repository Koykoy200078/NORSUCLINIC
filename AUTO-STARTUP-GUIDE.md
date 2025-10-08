# NORSUCLINIC Development Environment - Auto-Startup Guide

## 📋 Overview

This guide will help you set up automatic startup for your NORSUCLINIC development environment. The scripts will automatically:

1. ✅ Start WAMP Server
2. ✅ Clear all Laravel caches (to avoid OPcache issues)
3. ✅ Start Laravel development server on `192.168.180.100:8000`

---

## 📁 Files Created

### 1. **startup-dev-environment.bat** (Recommended for manual use)

-   Batch file with visual feedback
-   Shows progress in console window
-   Best for troubleshooting

### 2. **startup-dev-environment.ps1** (PowerShell version)

-   Colored output with better error handling
-   More detailed status messages
-   Requires PowerShell execution policy adjustment

### 3. **startup-silent.vbs** (For silent auto-start)

-   Runs the batch file in the background
-   No console window appears
-   Best for Windows Startup folder

---

## 🚀 Setup Methods

### **Method 1: Windows Startup Folder (Easiest)**

This method starts the development environment when you log in to Windows.

#### Steps:

1. **Press `Win + R`** and type:

    ```
    shell:startup
    ```

    Press Enter. This opens your Startup folder.

2. **Create a shortcut:**
    - Right-click in the Startup folder → **New** → **Shortcut**
3. **For VISIBLE console (recommended for testing):**
    - Target: `D:\Projects\NORSUCLINIC\startup-dev-environment.bat`
    - Name: `NORSUCLINIC Dev Server`
4. **For SILENT background startup:**

    - Target: `D:\Projects\NORSUCLINIC\startup-silent.vbs`
    - Name: `NORSUCLINIC Dev Server (Silent)`

5. **Click Finish!**

6. **Test it:**
    - Double-click the shortcut to test
    - Restart your computer to verify auto-start

---

### **Method 2: Task Scheduler (More Control)**

This method gives you more options like delay start, run only on AC power, etc.

#### Steps:

1. **Open Task Scheduler:**

    - Press `Win + R`, type: `taskschd.msc`, press Enter

2. **Create Basic Task:**

    - Click **"Create Basic Task"** in the right panel
    - Name: `NORSUCLINIC Development Server`
    - Description: `Auto-start WAMP and Laravel development server`
    - Click **Next**

3. **Trigger:**

    - Select **"When I log on"**
    - Click **Next**

4. **Action:**

    - Select **"Start a program"**
    - Click **Next**

5. **Program/Script:**

    - **For visible console:**

        ```
        D:\Projects\NORSUCLINIC\startup-dev-environment.bat
        ```

    - **For silent background:**
        ```
        wscript.exe
        ```
        **Add arguments:**
        ```
        "D:\Projects\NORSUCLINIC\startup-silent.vbs"
        ```

6. **Start in (optional):**

    ```
    D:\Projects\NORSUCLINIC
    ```

7. **Click Finish**

8. **Advanced Settings (Optional):**
    - Right-click the task → **Properties**
    - **General tab:**
        - ✅ Run whether user is logged on or not
        - ✅ Run with highest privileges
    - **Triggers tab:**
        - Edit trigger → **Delay task for:** `30 seconds` (gives WAMP time to start)
    - **Conditions tab:**
        - ⬜ Start only if computer is on AC power (uncheck this)

---

### **Method 3: Manual Shortcut on Desktop**

If you don't want auto-start but want easy access:

1. **Right-click on Desktop** → **New** → **Shortcut**

2. **Target:**

    ```
    D:\Projects\NORSUCLINIC\startup-dev-environment.bat
    ```

3. **Name:** `Start NORSUCLINIC Server`

4. **Optional - Custom Icon:**
    - Right-click shortcut → **Properties**
    - Click **Change Icon**
    - Browse to an icon file or use built-in Windows icons

---

## 🔧 Configuration

### Customize the Scripts

Edit `startup-dev-environment.bat` or `startup-dev-environment.ps1` to change:

#### **WAMP Installation Path:**

```batch
REM In .bat file (line ~25):
if exist "C:\wamp64\wampmanager.exe" (

REM In .ps1 file (line ~10):
$WampPath = "C:\wamp64\wampmanager.exe"
```

#### **Server IP Address:**

```batch
REM In .bat file (last line):
php artisan serve --host=192.168.180.100

REM In .ps1 file (line ~11-12):
$ServerHost = "192.168.180.100"
$ServerPort = "8000"
```

#### **Startup Delay:**

```batch
REM In .bat file (line ~43):
timeout /t 15 /nobreak >nul

REM In .ps1 file (line ~66):
Start-Sleep -Seconds 15
```

---

## 🧪 Testing

### Test the Batch File:

```cmd
D:\Projects\NORSUCLINIC\startup-dev-environment.bat
```

### Test the PowerShell Script:

```powershell
cd D:\Projects\NORSUCLINIC
.\startup-dev-environment.ps1
```

### Test Silent VBS:

Double-click `startup-silent.vbs` - nothing should appear, but check:

-   WAMP icon in system tray
-   Open browser: `http://192.168.180.100:8000`

---

## 🐛 Troubleshooting

### **Issue: PowerShell script won't run**

**Solution:** Enable script execution (run as Administrator):

```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### **Issue: WAMP doesn't start**

**Solution:**

1. Check WAMP path in script matches your installation
2. Make sure no other Apache/MySQL is running
3. Run WAMP manually first to check for errors

### **Issue: Cache not clearing**

**Solution:**

1. Make sure PHP is in your PATH environment variable
2. Try running manually: `php artisan cache:clear`
3. Restart WAMP after running the script

### **Issue: Laravel server says "Address already in use"**

**Solution:**

1. Another process is using port 8000
2. Change port in script: `--port=8001`
3. Or kill the process:
    ```powershell
    Get-Process -Name php | Stop-Process -Force
    ```

### **Issue: Task Scheduler task doesn't run**

**Solution:**

1. Right-click task → Run (test manually)
2. Check task history for error messages
3. Make sure "Run with highest privileges" is checked
4. Verify paths are absolute (not relative)

---

## 📊 What Happens When It Runs?

```
[1/4] Starting WAMP Server...
  ✓ WAMP Server started successfully!
  Waiting for services to start (15 seconds)...

[2/4] Navigating to project directory...
  ✓ Current directory: D:\Projects\NORSUCLINIC

[3/4] Clearing all Laravel caches...
  ✓ Application cache cleared
  ✓ Configuration cache cleared
  ✓ Route cache cleared
  ✓ Compiled views cleared
  ✓ Compiled services cleared
  ✓ All optimizations cleared

[4/4] Starting Laravel development server...
  Server will be accessible at:
    - http://192.168.180.100:8000
    - http://127.0.0.1:8000

Laravel Development Server Started!
```

---

## 🔥 Quick Commands Reference

### Manual Cache Clear:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan clear-compiled
php artisan optimize:clear
```

### Start Laravel Server Manually:

```bash
php artisan serve --host=192.168.180.100 --port=8000
```

### Stop All PHP Processes:

```powershell
Get-Process -Name php | Stop-Process -Force
```

### Check if WAMP is Running:

```powershell
Get-Process -Name wampmanager, httpd, mysqld
```

---

## 📝 Notes

-   **First Run:** The script waits 15 seconds for WAMP to fully start. Adjust if needed.
-   **OPcache:** Caches are cleared automatically to prevent the "display_name" error you experienced.
-   **Network Access:** Using `192.168.180.100` allows other devices on your network to access the app.
-   **Console Window:** Use the `.vbs` file if you don't want to see the console window.
-   **Stopping the Server:** Press `Ctrl+C` in the console window or close the window.

---

## ✅ Recommended Setup

**For Development (you see what's happening):**
→ Use **Method 1** with `startup-dev-environment.bat`

**For Production-like (silent background):**
→ Use **Method 2** (Task Scheduler) with `startup-silent.vbs` and 30-second delay

---

## 🎯 Success Checklist

After setup, verify:

-   [ ] WAMP icon appears in system tray (green)
-   [ ] Browser opens `http://192.168.180.100:8000` successfully
-   [ ] No "display_name" errors appear
-   [ ] Staff edit page works: `http://192.168.180.100:8000/admin/staffs/3/edit`
-   [ ] Can update staff without 422 errors

---

## 🆘 Support

If you encounter issues:

1. **Check WAMP status:** Look for green icon in system tray
2. **Check Laravel logs:** `storage/logs/laravel.log`
3. **Verify PHP version:** `php -v` should show 8.1 or higher
4. **Test WAMP manually:** Start it before running script

---

**Created:** October 8, 2025  
**Project:** NORSUCLINIC Clinic Management System  
**Purpose:** Streamline development environment startup and prevent OPcache issues
