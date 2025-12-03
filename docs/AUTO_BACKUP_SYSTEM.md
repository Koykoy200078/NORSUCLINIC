# Auto Database Backup System

## Overview

The NORSUCLINIC Auto Database Backup System provides **intelligent hourly backups** that only create backup files when database changes are detected.

---

## 🚀 Features

- ✅ **Automatic hourly checks** - Scans database every hour
- ✅ **Smart change detection** - Uses MD5 hash to detect data changes
- ✅ **Skip unchanged backups** - Only backs up when data has changed
- ✅ **Timestamp-based naming** - Backups include date and time in filename
- ✅ **Automatic compression** - Backups saved as `.zip` files
- ✅ **Automatic cleanup** - Removes backups older than 30 days
- ✅ **Detailed logging** - Maintains backup history log
- ✅ **Auto-start with dev environment** - Starts when you run startup script
- ✅ **Runs in background** - Minimized window, doesn't interfere

---

## 📁 Files

### Scripts

1. **`auto-backup-database.bat`** - Windows Batch version
2. **`auto-backup-database.ps1`** - PowerShell version (recommended)

### Backup Directory Structure

```
NORSUCLINIC/
├── database_backups/
│   ├── backup_log.txt              # Detailed backup history
│   ├── last_hash.txt               # MD5 hash of last backup
│   ├── norsuclinic_backup_2025-12-03_09-00-00.sql.zip
│   ├── norsuclinic_backup_2025-12-03_10-00-00.sql.zip
│   └── ...
```

---

## 🔧 Configuration

### Database Credentials

Both scripts read credentials from your `.env` file:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=norsu_clinic
DB_USERNAME=root
DB_PASSWORD=Carvs@10072000
```

### Backup Interval

Default: **3600 seconds (1 hour)**

To change interval, edit the script:

**Batch (.bat):**
```batch
set BACKUP_INTERVAL=3600
```

**PowerShell (.ps1):**
```powershell
$BackupInterval = 3600
```

Common intervals:
- 30 minutes = `1800`
- 1 hour = `3600`
- 2 hours = `7200`
- 6 hours = `21600`

### Backup Retention

Default: **30 days**

Older backups are automatically deleted.

To change, modify the cleanup section:

**Batch:**
```batch
forfiles /P "%BACKUP_DIR%" /M *.zip /D -30 /C "cmd /c del @path"
```

**PowerShell:**
```powershell
$cutoffDate = (Get-Date).AddDays(-30)
```

---

## 🎯 How It Works

### 1. Change Detection

The system uses **MD5 hashing** to detect database changes:

```
1. Dumps database to temporary file
2. Calculates MD5 hash of dump
3. Compares with last saved hash
4. If different → backup needed
5. If same → skip backup
```

### 2. Backup Process

When changes are detected:

```
1. Creates SQL dump with mysqldump
2. Includes routines, triggers, events
3. Compresses to .zip file
4. Saves hash for next comparison
5. Logs backup details
6. Cleans old backups
```

### 3. Filename Format

```
norsuclinic_backup_YYYY-MM-DD_HH-MM-SS.sql.zip
                   └── Timestamp of backup
```

Example: `norsuclinic_backup_2025-12-03_14-30-00.sql.zip`

---

## 📊 Usage

### Auto-Start (Recommended)

The backup system starts automatically when you run:

**Batch:**
```cmd
startup-dev-environment.bat
```

**PowerShell:**
```powershell
.\startup-dev-environment.ps1
```

It runs in a **minimized background window**.

### Manual Start

**Batch:**
```cmd
auto-backup-database.bat
```

**PowerShell:**
```powershell
.\auto-backup-database.ps1
```

### Stop Backup System

1. Find the minimized window titled "NORSUCLINIC Auto Backup"
2. Maximize the window
3. Press `Ctrl+C`

Or use Task Manager to end the process.

---

## 📋 Backup Log

View backup history in `database_backups\backup_log.txt`:

```
Database Backup Log - Created 2025-12-03 08:00:00
============================================================

[2025-12-03 08:00:00] Auto Backup System Started
[2025-12-03 08:00:01] Checking database for changes...
[2025-12-03 08:00:02] First backup - no previous hash found
[2025-12-03 08:00:02] Creating backup: database_backups\norsuclinic_backup_2025-12-03_08-00-02.sql
[2025-12-03 08:00:05] Compressing backup...
[2025-12-03 08:00:06] ✓ Backup created successfully (1,234 KB)
[2025-12-03 09:00:00] Checking database for changes...
[2025-12-03 09:00:01] No changes detected - skipping backup
[2025-12-03 10:00:00] Checking database for changes...
[2025-12-03 10:00:01] Database changes detected!
[2025-12-03 10:00:02] Creating backup: database_backups\norsuclinic_backup_2025-12-03_10-00-02.sql
[2025-12-03 10:00:05] ✓ Backup created successfully (1,456 KB)
```

---

## 🔄 Restoring from Backup

### PowerShell Method (Recommended)

```powershell
# 1. Extract backup
Expand-Archive -Path "database_backups\norsuclinic_backup_2025-12-03_10-00-00.sql.zip" -DestinationPath "temp"

# 2. Restore to database
$mysqlPath = "C:\wamp64\bin\mysql\mysql8.0.39\bin\mysql.exe"
Get-Content "temp\norsuclinic_backup_2025-12-03_10-00-00.sql" | & $mysqlPath -u root -p norsu_clinic

# 3. Clean up
Remove-Item -Path "temp" -Recurse -Force
```

### Command Line Method

```cmd
# 1. Extract backup (right-click → Extract All)

# 2. Restore to database
C:\wamp64\bin\mysql\mysql8.0.39\bin\mysql.exe -u root -p norsu_clinic < norsuclinic_backup_2025-12-03_10-00-00.sql
```

### MySQL Workbench Method

1. Open MySQL Workbench
2. Connect to database
3. Go to **Server** → **Data Import**
4. Select **Import from Self-Contained File**
5. Browse to extracted `.sql` file
6. Click **Start Import**

---

## ⚠️ Important Notes

### Requirements

- ✅ WAMP/MySQL must be running
- ✅ Database must be accessible
- ✅ mysqldump.exe must exist in MySQL bin folder
- ✅ Sufficient disk space for backups

### Backup Size

Typical backup sizes (compressed):
- Empty database: ~50 KB
- Small clinic (100 patients): ~500 KB
- Medium clinic (1000 patients): ~5 MB
- Large clinic (10000+ patients): ~50 MB

### Performance Impact

- Backup process takes: **5-15 seconds**
- CPU usage during backup: **Low**
- Does not lock database (uses `--single-transaction`)
- **No impact on running application**

### Security

⚠️ **Database credentials are stored in scripts!**

**Recommendation:**
- Keep scripts in secure location
- Don't commit to public repositories
- Use environment variables for production

---

## 🛠️ Troubleshooting

### Backup not creating

**Check:**
1. Is MySQL running?
2. Are database credentials correct?
3. Does MySQL bin path exist?
4. Check `backup_log.txt` for errors

### Backup script stops running

**Common causes:**
1. Database connection lost
2. Disk space full
3. MySQL service stopped

**Solution:**
Restart the backup script - it will resume from last state.

### Old backups not deleting

**Batch version:**
- Requires `forfiles` command (Windows 7+)

**PowerShell version:**
- Works on all PowerShell versions

### Backup size too large

**Reduce size:**
1. Clean up old/unused data
2. Archive historical records
3. Optimize database tables

---

## 📊 Monitoring

### Check if Backup System is Running

**Task Manager:**
1. Open Task Manager (`Ctrl+Shift+Esc`)
2. Look for process: `powershell.exe` or `cmd.exe`
3. Window title: "NORSUCLINIC Auto Backup"

**PowerShell:**
```powershell
Get-Process | Where-Object { $_.MainWindowTitle -like "*Auto Backup*" }
```

### View Recent Backups

**PowerShell:**
```powershell
Get-ChildItem database_backups\*.zip | Sort-Object LastWriteTime -Descending | Select-Object -First 10 Name, Length, LastWriteTime
```

### Calculate Total Backup Size

**PowerShell:**
```powershell
$totalSize = (Get-ChildItem database_backups\*.zip | Measure-Object -Property Length -Sum).Sum
[math]::Round($totalSize / 1MB, 2)  # Size in MB
```

---

## 🔐 Best Practices

1. **Monitor Regularly**
   - Check `backup_log.txt` weekly
   - Verify backups are being created

2. **Test Restore Process**
   - Practice restoring from backup monthly
   - Ensure backups are valid

3. **Off-site Backup**
   - Copy `database_backups` folder to cloud storage
   - Use OneDrive, Google Drive, or external drive

4. **Before Major Changes**
   - Create manual backup before migrations
   - Test on backup copy first

5. **Production Environment**
   - Use more frequent backups (every 15-30 min)
   - Store backups on separate server
   - Implement backup verification

---

## 📞 Support

If backup system fails:
1. Check log file: `database_backups\backup_log.txt`
2. Verify MySQL is running
3. Test database connection manually
4. Check disk space

For production use, consider:
- Professional backup solutions
- Database replication
- Point-in-time recovery
- Encrypted backups

---

## ✅ Summary

The Auto Backup System provides **set-and-forget** database protection:

- 🔄 Runs continuously in background
- 🧠 Smart change detection
- 💾 Automatic compression
- 🧹 Automatic cleanup
- 📝 Detailed logging
- 🚀 Auto-starts with dev environment

**Just run your startup script and you're protected!**
