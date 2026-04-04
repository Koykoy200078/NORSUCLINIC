# ============================================================
# NORSUCLINIC - All-in-One Management Script
# ============================================================
# Usage:
#   .\norsuclinic.ps1              -> Interactive menu
#   .\norsuclinic.ps1 start        -> Start dev environment
#   .\norsuclinic.ps1 stop         -> Stop dev server
#   .\norsuclinic.ps1 backup       -> Run database backup now
#   .\norsuclinic.ps1 migrate      -> Migrate fresh (keep users)
#   .\norsuclinic.ps1 cache        -> Clear all caches
#   .\norsuclinic.ps1 opcache      -> Fix OPcache performance
#   .\norsuclinic.ps1 scheduler    -> Run Laravel scheduler once
#   .\norsuclinic.ps1 cleanup      -> Cleanup docs/debug files
# ============================================================

param(
    [Parameter(Position = 0)]
    [string]$Command = ""
)

# ============================================================
# SHARED CONFIGURATION
# ============================================================
$ProjectPath   = "C:\Projects\NORSUCLINIC"
$WampPath      = "C:\wamp64\wampmanager.exe"
$MySQLPath     = "C:\wamp64\bin\mysql\mysql8.0.39\bin"
$ServerHost    = "192.168.1.20"
$ServerPort    = "8000"
$BackupDir     = Join-Path $ProjectPath "database_backups"
$LogFile       = Join-Path $BackupDir "backup_log.txt"
$LastHashFile  = Join-Path $BackupDir "last_hash.txt"

# DB credentials (mirrors .env)
$DBHost = "127.0.0.1"
$DBPort = "3306"
$DBName = "norsu_clinic"
$DBUser = "root"
$DBPass = ""

# ============================================================
# HELPERS
# ============================================================
function Write-Header {
    param([string]$Title)
    Write-Host ""
    Write-Host "============================================================" -ForegroundColor Cyan
    Write-Host "  $Title" -ForegroundColor White
    Write-Host "============================================================" -ForegroundColor Cyan
    Write-Host ""
}

function Write-Ok   { param([string]$M) Write-Host "  + $M" -ForegroundColor Green }
function Write-Warn { param([string]$M) Write-Host "  ! $M" -ForegroundColor Yellow }
function Write-Err  { param([string]$M) Write-Host "  x $M" -ForegroundColor Red }
function Write-Info { param([string]$M) Write-Host "  > $M" -ForegroundColor Cyan }

function Ensure-ProjectDir {
    if (-not (Test-Path $ProjectPath)) {
        Write-Err "Project directory not found: $ProjectPath"
        exit 1
    }
    Set-Location $ProjectPath
}

function Write-Log {
    param([string]$Message, [string]$Color = "White")
    $ts = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $line = "[$ts] $Message"
    Write-Host $line -ForegroundColor $Color
    if (Test-Path $LogFile) { Add-Content -Path $LogFile -Value $line }
}

# ============================================================
# COMMAND: START  (start dev environment)
# ============================================================
function Invoke-Start {
    Clear-Host
    Write-Header "NORSUCLINIC - Starting Development Environment"

    # -- WAMP --
    Write-Host "[1/4] Starting WAMP Server..." -ForegroundColor Yellow
    $wamp = Get-Process -Name "wampmanager" -ErrorAction SilentlyContinue
    if ($wamp) {
        Write-Ok "WAMP is already running"
    } elseif (Test-Path $WampPath) {
        Start-Process $WampPath
        Write-Ok "WAMP started — waiting 15s for services..."
        Start-Sleep -Seconds 15
    } else {
        Write-Err "WAMP not found at: $WampPath"
        exit 1
    }

    # -- Project dir --
    Write-Host "[2/4] Navigating to project directory..." -ForegroundColor Yellow
    Ensure-ProjectDir
    Write-Ok "Working directory: $ProjectPath"

    # -- Clear caches --
    Write-Host "[3/4] Clearing Laravel caches..." -ForegroundColor Yellow
    foreach ($cmd in @("cache:clear","config:clear","route:clear","view:clear","clear-compiled","optimize:clear")) {
        php artisan $cmd 2>&1 | Out-Null
        if ($LASTEXITCODE -eq 0) { Write-Ok "$cmd" } else { Write-Warn "$cmd returned non-zero" }
    }

    # -- Backup in background --
    Write-Host "[4/4] Starting auto-backup daemon..." -ForegroundColor Yellow
    $backupScript = Join-Path $ProjectPath "auto-backup-database.ps1"
    if (Test-Path $backupScript) {
        Start-Process powershell.exe -ArgumentList @(
            "-NoProfile", "-ExecutionPolicy", "Bypass",
            "-WindowStyle", "Minimized",
            "-File", "`"$backupScript`""
        ) -WindowStyle Minimized
        Write-Ok "Auto-backup daemon started (minimized)"
    } else {
        Write-Warn "auto-backup-database.ps1 not found — skipping"
    }

    Write-Host ""
    Write-Host "  Server: http://$ServerHost`:$ServerPort" -ForegroundColor White
    Write-Host "  Local:  http://127.0.0.1:$ServerPort"   -ForegroundColor White
    Write-Host "  Press Ctrl+C to stop" -ForegroundColor Yellow
    Write-Host ""

    php -d opcache.revalidate_freq=0 -d opcache.validate_timestamps=0 artisan serve --host=$ServerHost --port=$ServerPort

    Write-Warn "Development server stopped."
    Read-Host "Press Enter to exit"
}

# ============================================================
# COMMAND: STOP  (kill PHP processes)
# ============================================================
function Invoke-Stop {
    Write-Header "NORSUCLINIC - Stopping Development Server"
    $procs = Get-Process -Name "php" -ErrorAction SilentlyContinue
    if ($procs) {
        $procs | Stop-Process -Force
        Write-Ok "All PHP processes stopped ($($procs.Count) killed)"
    } else {
        Write-Info "No PHP processes were running"
    }
}

# ============================================================
# COMMAND: BACKUP  (single immediate backup)
# ============================================================
function Invoke-Backup {
    Write-Header "NORSUCLINIC - Database Backup"
    Ensure-ProjectDir

    if (-not (Test-Path $BackupDir)) {
        New-Item -ItemType Directory -Path $BackupDir | Out-Null
        Write-Ok "Created backup directory"
    }
    if (-not (Test-Path $LogFile)) {
        "Database Backup Log`n$(Get-Date)" | Set-Content $LogFile
    }

    $mysqldump = Join-Path $MySQLPath "mysqldump.exe"
    if (-not (Test-Path $mysqldump)) {
        Write-Err "mysqldump not found at: $mysqldump"
        exit 1
    }

    $ts = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
    $sqlFile = Join-Path $BackupDir "norsuclinic_backup_$ts.sql"
    $zipFile = "$sqlFile.zip"

    Write-Info "Dumping database..."
    & $mysqldump "--host=$DBHost" "--port=$DBPort" "--user=$DBUser" "--password=$DBPass" `
        "--routines" "--triggers" "--events" "--single-transaction" $DBName `
        2>&1 | Out-File -FilePath $sqlFile -Encoding UTF8

    if ($LASTEXITCODE -ne 0) {
        Write-Err "mysqldump failed. Check DB credentials and that WAMP is running."
        exit 1
    }

    Compress-Archive -Path $sqlFile -DestinationPath $zipFile -Force
    Remove-Item $sqlFile -Force
    $kb = [math]::Round((Get-Item $zipFile).Length / 1KB, 1)
    Write-Ok "Backup saved: $(Split-Path $zipFile -Leaf) ($kb KB)"

    # Update hash file
    $tempDump = Join-Path $env:TEMP "temp_hash_check.sql"
    & $mysqldump "--host=$DBHost" "--port=$DBPort" "--user=$DBUser" "--password=$DBPass" `
        "--quick" "--single-transaction" "--skip-comments" "--skip-extended-insert" $DBName `
        2>$null | Out-File $tempDump -Encoding UTF8
    $hash = (Get-FileHash $tempDump -Algorithm MD5).Hash
    Set-Content -Path $LastHashFile -Value $hash
    Remove-Item $tempDump -Force -ErrorAction SilentlyContinue
    Write-Ok "Hash updated"

    Add-Content $LogFile "[$((Get-Date -Format 'yyyy-MM-dd HH:mm:ss'))] Manual backup: $(Split-Path $zipFile -Leaf) ($kb KB)"

    # Prune backups older than 30 days
    $cutoff = (Get-Date).AddDays(-30)
    $old = Get-ChildItem $BackupDir -Filter "*.zip" | Where-Object { $_.LastWriteTime -lt $cutoff }
    if ($old) {
        $old | Remove-Item -Force
        Write-Ok "Removed $($old.Count) old backup(s) (>30 days)"
    }
}

# ============================================================
# COMMAND: MIGRATE  (migrate:fresh keeping users)
# ============================================================
function Invoke-Migrate {
    Write-Header "NORSUCLINIC - Migrate Fresh (Keep Users)"
    Ensure-ProjectDir

    # Step 1: backup users
    Write-Host "[1/4] Backing up users table..." -ForegroundColor Yellow
    $backupCmd = "file_put_contents('users_backup.json', DB::table('users')->get()->toJson());"
    php artisan tinker --execute=$backupCmd
    if ($LASTEXITCODE -ne 0 -or -not (Test-Path "users_backup.json")) {
        Write-Err "Failed to backup users. Aborting."
        exit 1
    }
    Write-Ok "Users backed up ($([math]::Round((Get-Item 'users_backup.json').Length/1KB,1)) KB)"

    # Step 2: confirm
    Write-Host ""
    Write-Warn "WARNING: This will DROP ALL TABLES and re-seed!"
    $confirm = Read-Host "  Type YES to continue"
    if ($confirm -ne "YES") {
        Remove-Item "users_backup.json" -ErrorAction SilentlyContinue
        Write-Warn "Migration cancelled."
        return
    }

    # Step 3: migrate
    Write-Host "[3/4] Running migrate:fresh --seed..." -ForegroundColor Yellow
    php artisan migrate:fresh --seed
    if ($LASTEXITCODE -ne 0) {
        Write-Err "Migration failed! users_backup.json is preserved for manual recovery."
        exit 1
    }
    Write-Ok "Migration complete"

    # Step 4: restore users
    Write-Host "[4/4] Restoring users..." -ForegroundColor Yellow
    $restoreCmd = "DB::table('users')->truncate(); `$u = json_decode(file_get_contents('users_backup.json'), true); DB::table('users')->insert(`$u); echo 'Restored ' . count(`$u) . ' users';"
    php artisan tinker --execute=$restoreCmd
    if ($LASTEXITCODE -ne 0) {
        Write-Err "Restore failed. users_backup.json still available."
        exit 1
    }
    Remove-Item "users_backup.json" -ErrorAction SilentlyContinue
    Write-Ok "Users restored. Backup file removed."

    Write-Header "Migration Completed Successfully"
}

# ============================================================
# COMMAND: CACHE  (clear all Laravel caches)
# ============================================================
function Invoke-Cache {
    Write-Header "NORSUCLINIC - Clear All Caches"
    Ensure-ProjectDir
    foreach ($cmd in @("cache:clear","config:clear","route:clear","view:clear","clear-compiled","optimize:clear")) {
        Write-Host "  php artisan $cmd" -ForegroundColor Gray
        php artisan $cmd 2>&1 | Out-Null
        if ($LASTEXITCODE -eq 0) { Write-Ok $cmd } else { Write-Warn "$cmd returned non-zero" }
    }
    Write-Ok "All caches cleared"
}

# ============================================================
# COMMAND: OPCACHE  (fix OPcache performance in php.ini)
# ============================================================
function Invoke-Opcache {
    Write-Header "NORSUCLINIC - Fix OPcache Performance"

    $phpBin = Get-Command php -ErrorAction SilentlyContinue
    if (-not $phpBin) { Write-Err "'php' not in PATH. Is WAMP running?"; exit 1 }

    $phpIni = (php -r "echo php_ini_loaded_file();")
    if (-not $phpIni -or -not (Test-Path $phpIni)) { Write-Err "Cannot locate php.ini"; exit 1 }
    Write-Info "php.ini: $phpIni"

    # Backup
    $bak = "$phpIni.bak"
    if (-not (Test-Path $bak)) { Copy-Item $phpIni $bak; Write-Ok "Backup: $bak" }
    else { Write-Info "Backup already exists" }

    Write-Host ""
    Write-Host "  1. Balanced  — revalidate every 60s (recommended)" -ForegroundColor White
    Write-Host "  2. Fastest   — disable timestamp checks (restart server after code changes)" -ForegroundColor White
    Write-Host "  0. Cancel" -ForegroundColor Red
    $choice = Read-Host "  Choice"

    $content = Get-Content $phpIni -Raw
    switch ($choice) {
        "1" {
            $content = $content -replace '(?m)^[;#]?\s*opcache\.validate_timestamps\s*=.*', 'opcache.validate_timestamps=1'
            $content = $content -replace '(?m)^[;#]?\s*opcache\.revalidate_freq\s*=.*',    'opcache.revalidate_freq=60'
            $content | Set-Content $phpIni -NoNewline
            Write-Ok "Set revalidate_freq=60, validate_timestamps=1"
        }
        "2" {
            $content = $content -replace '(?m)^[;#]?\s*opcache\.validate_timestamps\s*=.*', 'opcache.validate_timestamps=0'
            $content = $content -replace '(?m)^[;#]?\s*opcache\.revalidate_freq\s*=.*',    'opcache.revalidate_freq=0'
            $content | Set-Content $phpIni -NoNewline
            Write-Ok "Set validate_timestamps=0 (fastest — restart server after code changes)"
        }
        default { Write-Warn "Cancelled"; return }
    }
    Write-Info "Verify: $(php -r "echo 'revalidate_freq=' . ini_get('opcache.revalidate_freq') . ', validate_timestamps=' . ini_get('opcache.validate_timestamps');")"
    Write-Ok "Done. Restart 'php artisan serve' for changes to take effect."
    Write-Host ""
    Write-Warn "TIP: Add C:\Projects\NORSUCLINIC to Windows Defender exclusions for extra speed."
}

# ============================================================
# COMMAND: SCHEDULER  (run Laravel scheduler once)
# ============================================================
function Invoke-Scheduler {
    Write-Header "NORSUCLINIC - Run Laravel Scheduler"
    Ensure-ProjectDir
    Write-Info "Running: php artisan schedule:run"
    php artisan schedule:run 2>&1 | Tee-Object -Append -FilePath (Join-Path $ProjectPath "storage\logs\scheduler.log")
    Write-Ok "Scheduler run complete"
}

# ============================================================
# COMMAND: CLEANUP  (cleanup docs/debug files)
# ============================================================
function Invoke-Cleanup {
    Write-Header "NORSUCLINIC - Cleanup Docs & Debug Files"
    Ensure-ProjectDir

    $docsDir   = Join-Path $ProjectPath "docs"
    $archDir   = Join-Path $ProjectPath "docs\archive"
    $debugDir  = Join-Path $ProjectPath "debug"
    $testDir   = Join-Path $ProjectPath "debug\test-files"

    foreach ($d in @($docsDir, $archDir, $debugDir, $testDir)) {
        if (-not (Test-Path $d)) { New-Item -ItemType Directory -Path $d | Out-Null; Write-Ok "Created: $d" }
    }

    $keepDocs = @("ACTIVITY_LOG_IMPLEMENTATION.md","ACTIVITY_LOG_QUICK_REFERENCE.md")
    $archiveDocs = @(
        "ACTIVITY_LOG_VERIFICATION.md","ACTIVITY_LOG_TESTING_GUIDE.md","ACTIVITY_LOG_SUMMARY.md",
        "ACTIVITY_LOG_ROUTES_MENU.md","ACTIVITY_LOG_QUICK_START.md","ACTIVITY_LOG_FINAL_FIX.md",
        "ACTIVITY_LOG_FIELD_MAPPINGS.md","ACTIVITY_LOG_COMPLETE_SUMMARY.md","ADD_MENU_ITEM_GUIDE.md",
        "REVIEW_REMOVAL_COMPLETE.md","PROTECTED_SYSTEMS_SUMMARY.md","DEEP_SCAN_COMPLETE.md",
        "DATABASE_CLEANUP_ANALYSIS_BACKUP.md","DATABASE_CLEANUP_ANALYSIS.md","CLEANUP_QUICK_START.md"
    )

    foreach ($f in $keepDocs) {
        if (Test-Path $f) { Move-Item $f $docsDir -Force; Write-Ok "Moved to docs/: $f" }
    }
    foreach ($f in $archiveDocs) {
        if (Test-Path $f) { Move-Item $f $archDir -Force; Write-Ok "Archived: $f" }
    }
    Write-Ok "Cleanup complete"
}

# ============================================================
# INTERACTIVE MENU
# ============================================================
function Show-Menu {
    Clear-Host
    Write-Header "NORSUCLINIC - Management Console"
    Write-Host "  1. Start Dev Environment  (WAMP + caches + backup daemon + serve)" -ForegroundColor White
    Write-Host "  2. Stop Dev Server         (kill PHP processes)"                    -ForegroundColor White
    Write-Host "  3. Backup Database Now     (immediate snapshot)"                   -ForegroundColor White
    Write-Host "  4. Migrate Fresh           (keep users)"                           -ForegroundColor White
    Write-Host "  5. Clear All Caches        (cache, config, route, view, compiled)"  -ForegroundColor White
    Write-Host "  6. Fix OPcache             (patch php.ini for performance)"        -ForegroundColor White
    Write-Host "  7. Run Scheduler           (php artisan schedule:run)"             -ForegroundColor White
    Write-Host "  8. Cleanup Files           (move docs/debug files)"                -ForegroundColor White
    Write-Host "  0. Exit"                                                             -ForegroundColor Red
    Write-Host ""
    $choice = Read-Host "  Enter choice"
    switch ($choice) {
        "1" { Invoke-Start }
        "2" { Invoke-Stop; Read-Host "`nPress Enter to return" }
        "3" { Invoke-Backup; Read-Host "`nPress Enter to return" }
        "4" { Invoke-Migrate; Read-Host "`nPress Enter to return" }
        "5" { Invoke-Cache; Read-Host "`nPress Enter to return" }
        "6" { Invoke-Opcache; Read-Host "`nPress Enter to return" }
        "7" { Invoke-Scheduler; Read-Host "`nPress Enter to return" }
        "8" { Invoke-Cleanup; Read-Host "`nPress Enter to return" }
        "0" { return }
        default { Write-Warn "Invalid choice"; Start-Sleep 1; Show-Menu }
    }
}

# ============================================================
# ENTRY POINT
# ============================================================
switch ($Command.ToLower()) {
    "start"     { Invoke-Start }
    "stop"      { Invoke-Stop }
    "backup"    { Invoke-Backup }
    "migrate"   { Invoke-Migrate }
    "cache"     { Invoke-Cache }
    "opcache"   { Invoke-Opcache }
    "scheduler" { Invoke-Scheduler }
    "cleanup"   { Invoke-Cleanup }
    default     { Show-Menu }
}
