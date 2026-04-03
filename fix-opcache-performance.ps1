# fix-opcache-performance.ps1
# Patches WAMP's php.ini to stop PHP from stat()-checking 12,000 vendor files
# every 2 seconds (which caused ~12s page loads on Windows).
#
# Run this ONCE with admin rights. After that, restart php artisan serve.
#
# ============================================================

param(
    [int]$RevalidateFreq = 60,    # seconds between OPcache file-change checks (0 = never)
    [switch]$DisableTimestamps    # if set, disables all timestamp checks (fastest, requires server restart on code change)
)

# ---------------------------------------------------------------------------
# 1. Find the WAMP PHP ini file
# ---------------------------------------------------------------------------
$phpBinary = (Get-Command php -ErrorAction SilentlyContinue)
if (-not $phpBinary) {
    Write-Host "ERROR: 'php' not found in PATH. Is WAMP running?" -ForegroundColor Red
    exit 1
}

$phpIniPath = (php -r "echo php_ini_loaded_file();")
if (-not $phpIniPath -or -not (Test-Path $phpIniPath)) {
    Write-Host "ERROR: Could not locate php.ini at: $phpIniPath" -ForegroundColor Red
    exit 1
}

Write-Host "Found php.ini: $phpIniPath" -ForegroundColor Cyan

# ---------------------------------------------------------------------------
# 2. Backup the original
# ---------------------------------------------------------------------------
$backup = "$phpIniPath.bak"
if (-not (Test-Path $backup)) {
    Copy-Item $phpIniPath $backup
    Write-Host "Backup created: $backup" -ForegroundColor Green
} else {
    Write-Host "Backup already exists: $backup" -ForegroundColor Yellow
}

# ---------------------------------------------------------------------------
# 3. Apply changes
# ---------------------------------------------------------------------------
$content = Get-Content $phpIniPath -Raw

if ($DisableTimestamps) {
    # Disable all file timestamp checks (fastest, but code changes require restarting the server)
    $content = $content -replace '(?m)^[;#]?\s*opcache\.validate_timestamps\s*=.*', 'opcache.validate_timestamps=0'
    $content = $content -replace '(?m)^[;#]?\s*opcache\.revalidate_freq\s*=.*', 'opcache.revalidate_freq=0'
    Write-Host "Set opcache.validate_timestamps=0 (fastest - restart server after code changes)" -ForegroundColor Green
} else {
    # Set a longer revalidation interval (default WAMP is 2s, too aggressive for Windows with 12k files)
    $content = $content -replace '(?m)^[;#]?\s*opcache\.validate_timestamps\s*=.*', 'opcache.validate_timestamps=1'
    $content = $content -replace '(?m)^[;#]?\s*opcache\.revalidate_freq\s*=.*', "opcache.revalidate_freq=$RevalidateFreq"
    Write-Host "Set opcache.revalidate_freq=$RevalidateFreq (code changes visible within $RevalidateFreq seconds)" -ForegroundColor Green
}

$content | Set-Content $phpIniPath -NoNewline

# ---------------------------------------------------------------------------
# 4. Verify
# ---------------------------------------------------------------------------
Write-Host ""
Write-Host "Verifying changes..." -ForegroundColor Cyan
$verify = php -r "echo 'revalidate_freq=' . ini_get('opcache.revalidate_freq') . ', validate_timestamps=' . ini_get('opcache.validate_timestamps');"
Write-Host "  $verify" -ForegroundColor White

Write-Host ""
Write-Host "Done! Restart php artisan serve and page loads should drop from ~12s to <1s." -ForegroundColor Green
Write-Host ""
Write-Host "TIP: Also add your project folder to Windows Defender exclusions for extra speed:" -ForegroundColor Yellow
Write-Host "  Settings -> Windows Security -> Virus & threat protection -> Manage settings" -ForegroundColor Yellow
Write-Host "  -> Exclusions -> Add an exclusion -> Folder -> C:\Projects\NORSUCLINIC" -ForegroundColor Yellow
