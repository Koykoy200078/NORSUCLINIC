# ============================================================
# NORSUCLINIC Development Environment Startup Script (PowerShell)
# This script automatically:
# 1. Starts WAMP Server
# 2. Waits for services to be ready
# 3. Clears all Laravel caches
# 4. Starts Auto Database Backup System
# 5. Starts Laravel development server
# ============================================================

# Configuration
$WampPath = "C:\wamp64\wampmanager.exe"
$ProjectPath = "D:\Projects\NORSUCLINIC"
$ServerHost = "192.168.180.100"
$ServerPort = "8000"

# Colors
$ErrorColor = "Red"
$SuccessColor = "Green"
$InfoColor = "Cyan"
$WarningColor = "Yellow"

function Write-Header {
    param([string]$Message)
    Write-Host "`n============================================================" -ForegroundColor $InfoColor
    Write-Host "  $Message" -ForegroundColor White
    Write-Host "============================================================`n" -ForegroundColor $InfoColor
}

function Write-Step {
    param([string]$Step, [string]$Message)
    Write-Host "[$Step] " -ForegroundColor $WarningColor -NoNewline
    Write-Host $Message
}

function Write-Success {
    param([string]$Message)
    Write-Host "  ✓ $Message" -ForegroundColor $SuccessColor
}

function Write-Error-Message {
    param([string]$Message)
    Write-Host "  ✗ $Message" -ForegroundColor $ErrorColor
}

# Start script
Clear-Host
Write-Header "NORSUCLINIC - Starting Development Environment"

# ============================================================
# Step 1: Start WAMP Server
# ============================================================
Write-Step "1/4" "Starting WAMP Server..."

$wampProcess = Get-Process -Name "wampmanager" -ErrorAction SilentlyContinue

if ($wampProcess) {
    Write-Success "WAMP Server is already running!"
} else {
    if (Test-Path $WampPath) {
        Start-Process $WampPath
        Write-Success "WAMP Server started successfully!"
        
        Write-Host "`n  Waiting for WAMP services to initialize..." -ForegroundColor $InfoColor
        Start-Sleep -Seconds 15
        Write-Success "WAMP services should now be ready"
    } else {
        Write-Error-Message "WAMP Server not found at: $WampPath"
        Write-Host "`n  Please update the `$WampPath variable in this script." -ForegroundColor $WarningColor
        Read-Host "`nPress Enter to exit"
        exit 1
    }
}

# ============================================================
# Step 2: Navigate to project directory
# ============================================================
Write-Step "`n2/4" "Navigating to project directory..."

if (Test-Path $ProjectPath) {
    Set-Location $ProjectPath
    Write-Success "Current directory: $ProjectPath"
} else {
    Write-Error-Message "Project directory not found: $ProjectPath"
    Read-Host "`nPress Enter to exit"
    exit 1
}

# ============================================================
# Step 3: Clear all Laravel caches
# ============================================================
Write-Step "`n3/4" "Clearing all Laravel caches..."
Write-Host ""

# Clear application cache
Write-Host "  - Clearing application cache..." -ForegroundColor Gray
php artisan cache:clear 2>&1 | Out-Null
if ($LASTEXITCODE -eq 0) { Write-Success "Application cache cleared" }

# Clear configuration cache
Write-Host "  - Clearing configuration cache..." -ForegroundColor Gray
php artisan config:clear 2>&1 | Out-Null
if ($LASTEXITCODE -eq 0) { Write-Success "Configuration cache cleared" }

# Clear route cache
Write-Host "  - Clearing route cache..." -ForegroundColor Gray
php artisan route:clear 2>&1 | Out-Null
if ($LASTEXITCODE -eq 0) { Write-Success "Route cache cleared" }

# Clear view cache
Write-Host "  - Clearing compiled views..." -ForegroundColor Gray
php artisan view:clear 2>&1 | Out-Null
if ($LASTEXITCODE -eq 0) { Write-Success "Compiled views cleared" }

# Clear compiled services
Write-Host "  - Clearing compiled services..." -ForegroundColor Gray
php artisan clear-compiled 2>&1 | Out-Null
if ($LASTEXITCODE -eq 0) { Write-Success "Compiled services cleared" }

# Optional: Clear OPcache via optimize:clear
Write-Host "  - Clearing all optimizations..." -ForegroundColor Gray
php artisan optimize:clear 2>&1 | Out-Null
if ($LASTEXITCODE -eq 0) { Write-Success "All optimizations cleared" }

Write-Host ""
Write-Success "All Laravel caches cleared successfully!"

# ============================================================
# Step 4: Start Auto Database Backup System
# ============================================================
Write-Step "`n4/5" "Starting Auto Database Backup System..."
Write-Host ""

$backupScriptPath = Join-Path $PSScriptRoot "auto-backup-database.ps1"

if (Test-Path $backupScriptPath) {
    # Start backup system in new minimized PowerShell window
    $startProcessArgs = @{
        FilePath = "powershell.exe"
        ArgumentList = @(
            "-NoProfile",
            "-ExecutionPolicy", "Bypass",
            "-WindowStyle", "Minimized",
            "-File", "`"$backupScriptPath`""
        )
        WindowStyle = "Minimized"
    }
    
    Start-Process @startProcessArgs
    
    Write-Success "Auto Backup System started successfully!"
    Write-Host "  - Checking for database changes every hour" -ForegroundColor Gray
    Write-Host "  - Backups saved to: database_backups\" -ForegroundColor Gray
    Write-Host "  - Running in background (minimized window)" -ForegroundColor Gray
}
else {
    Write-Host "  WARNING: Auto backup script not found!" -ForegroundColor $WarningColor
    Write-Host "  Expected location: $backupScriptPath" -ForegroundColor Gray
}

Write-Host ""

# ============================================================
# Step 5: Start Laravel development server
# ============================================================
Write-Step "`n5/5" "Starting Laravel development server..."
Write-Host ""

Write-Host "  Server will be accessible at:" -ForegroundColor $InfoColor
Write-Host "    - http://$ServerHost`:$ServerPort" -ForegroundColor White
Write-Host "    - http://127.0.0.1:$ServerPort" -ForegroundColor White
Write-Host ""
Write-Host "  Press Ctrl+C to stop the server" -ForegroundColor $WarningColor
Write-Host ""
Write-Header "Laravel Development Server Starting..."

# Start Laravel server
php artisan serve --host=$ServerHost --port=$ServerPort

# This line executes only if server is stopped
Write-Host "`n`nDevelopment server stopped." -ForegroundColor $WarningColor
Read-Host "Press Enter to exit"
