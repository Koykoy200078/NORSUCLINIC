# ============================================================
# Medical University Clinic - start the clinic server (PowerShell)
#
# Everything runs in THIS ONE terminal, no extra windows:
#   1. makes sure the database (WAMP MySQL) is running
#   2. clears and rebuilds the Laravel caches
#   3. starts Reverb (real-time updates) in the background of this terminal, its output goes to storage\logs\reverb*.log
#   4. starts the web server on ALL network addresses (0.0.0.0), so the page opens from
#      http://localhost:8000, http://127.0.0.1:8000 and http://<this PC's current LAN address>:8000
#
# The LAN address is detected every time the script runs, so DHCP (dynamic) and static addresses both work.
# Ctrl+C stops the web server and Reverb together.
#
#   .\startup-dev-environment.ps1                 start
#   .\startup-dev-environment.ps1 -OpenBrowser    also open the page in the default browser
#
# There is no scheduler window: the automatic database backup was removed.
# ============================================================
param(
    [switch]$OpenBrowser
)

$ErrorActionPreference = 'Continue'

# The project root is the folder of THIS script (nothing hard-coded).
$ProjectPath = if ($PSScriptRoot) { $PSScriptRoot } else { Split-Path -Parent $MyInvocation.MyCommand.Definition }
Set-Location $ProjectPath

$WampPath = 'C:\wamp64\wampmanager.exe'
$WampMysqlService = 'wampmysqld64'
$AppPort = 8000

# Read ONE non-secret setting from .env (never print the file).
function Get-EnvSetting {
    param([string]$Name, [string]$Default)
    $file = Join-Path $ProjectPath '.env'
    if (Test-Path $file) {
        $line = Select-String -Path $file -Pattern "^\s*$Name\s*=\s*(.+?)\s*$" | Select-Object -First 1
        if ($line) { return $line.Matches[0].Groups[1].Value.Trim('"', "'") }
    }
    return $Default
}

$DbPort = [int](Get-EnvSetting 'DB_PORT' '3306')
$ReverbPort = [int](Get-EnvSetting 'REVERB_PORT' '8080')

# True when something is LISTENING on the TCP port.
function Test-PortInUse {
    param([int]$Port)
    try {
        if (Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue) { return $true }
    } catch { }
    try {
        foreach ($line in (netstat -ano -p tcp 2>$null | Select-String ":$Port\s")) {
            if ($line -match 'LISTENING') { return $true }
        }
    } catch { }
    return $false
}

# The IPv4 addresses of this PC, best first: the adapter that has the default gateway (the real LAN / Wi-Fi) before
# virtual adapters (WSL, Hyper-V, VirtualBox, VMware, Docker ...), which are listed last.
function Get-LanIPv4 {
    $virtualPattern = 'vEthernet|WSL|Hyper-V|VirtualBox|VMware|Docker|Loopback|Bluetooth|Tailscale|ZeroTier|TAP-|TUN'
    $found = @()
    try {
        foreach ($config in (Get-NetIPConfiguration -ErrorAction Stop)) {
            if (-not $config.NetAdapter -or $config.NetAdapter.Status -ne 'Up') { continue }
            foreach ($address in $config.IPv4Address) {
                $ip = $address.IPAddress
                if (-not $ip -or $ip -like '127.*' -or $ip -like '169.254.*') { continue }
                $found += [pscustomobject]@{
                    IP      = $ip
                    Alias   = $config.InterfaceAlias
                    Gateway = [bool]$config.IPv4DefaultGateway
                    Virtual = ($config.InterfaceAlias -match $virtualPattern) -or ($config.InterfaceDescription -match $virtualPattern)
                }
            }
        }
    } catch { }

    if ($found.Count -eq 0) {
        try {
            foreach ($address in [System.Net.Dns]::GetHostAddresses([System.Net.Dns]::GetHostName())) {
                $ip = $address.IPAddressToString
                if ($address.AddressFamily -eq 'InterNetwork' -and $ip -notlike '127.*' -and $ip -notlike '169.254.*') {
                    $found += [pscustomobject]@{ IP = $ip; Alias = 'network'; Gateway = $false; Virtual = $false }
                }
            }
        } catch { }
    }

    return @($found | Sort-Object @{ Expression = { $_.Virtual } }, @{ Expression = { -not $_.Gateway } })
}

function Write-Step {
    param([string]$Message)
    Write-Host "[..] $Message" -ForegroundColor Cyan
}

function Write-Ok {
    param([string]$Message)
    Write-Host "[ok] $Message" -ForegroundColor Green
}

function Write-Warn {
    param([string]$Message)
    Write-Host "[!!] $Message" -ForegroundColor Yellow
}

# Run an artisan command quietly; show only a failure.
function Invoke-ArtisanQuiet {
    param([string[]]$Arguments)
    $output = & php artisan @Arguments 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Warn ("php artisan " + ($Arguments -join ' ') + " failed")
        $output | Select-Object -First 5 | ForEach-Object { Write-Host "     $_" -ForegroundColor DarkYellow }
    }
}

Write-Host ''
Write-Host '  Medical University Clinic - starting the server' -ForegroundColor White
Write-Host ''

# ------------------------------------------------------------ guard: already running
if (Test-PortInUse -Port $AppPort) {
    Write-Warn "A server is already running on port $AppPort, not starting another one."
    foreach ($lan in (Get-LanIPv4)) { Write-Host "     http://$($lan.IP):$AppPort" }
    Write-Host "     http://localhost:$AppPort"
    exit 0
}

# ------------------------------------------------------------ 1. database
Write-Step "Database (MySQL on port $DbPort)"
if (Test-PortInUse -Port $DbPort) {
    Write-Ok 'MySQL is already running'
} else {
    $started = $false
    # Without any window: start the WAMP MySQL service (needs an administrator terminal).
    if (Get-Service -Name $WampMysqlService -ErrorAction SilentlyContinue) {
        try { Start-Service -Name $WampMysqlService -ErrorAction Stop; $started = $true } catch { }
    }
    # Otherwise let WAMP's own manager start it (minimized, it lives in the tray).
    if (-not $started -and (Test-Path $WampPath)) {
        if (-not (Get-Process -Name 'wampmanager' -ErrorAction SilentlyContinue)) {
            Start-Process -FilePath $WampPath -WindowStyle Minimized | Out-Null
        }
    }
    # Wait for the port instead of a fixed pause.
    $deadline = (Get-Date).AddSeconds(60)
    while (-not (Test-PortInUse -Port $DbPort) -and (Get-Date) -lt $deadline) { Start-Sleep -Seconds 1 }
    if (Test-PortInUse -Port $DbPort) {
        Write-Ok 'MySQL is running'
    } else {
        Write-Warn "MySQL did not answer on port $DbPort within 60 seconds. Start WAMP by hand; the site will not work without it."
    }
}

# ------------------------------------------------------------ 2. caches
Write-Step 'Clearing and rebuilding the Laravel caches'
foreach ($command in @('cache:clear', 'config:clear', 'route:clear', 'view:clear', 'clear-compiled', 'optimize:clear', 'optimize')) {
    Invoke-ArtisanQuiet -Arguments @($command)
}
Write-Ok 'Caches ready'

# ------------------------------------------------------------ 3. Reverb (real-time updates), same terminal
$reverb = $null
$logDirectory = Join-Path $ProjectPath 'storage\logs'
if (Test-PortInUse -Port $ReverbPort) {
    Write-Ok "Reverb (real-time updates) is already running on port $ReverbPort"
} else {
    Write-Step "Reverb (real-time updates) on port $ReverbPort"
    $reverb = Start-Process -FilePath 'php' -ArgumentList 'artisan', 'reverb:start' -WorkingDirectory $ProjectPath `
        -NoNewWindow -PassThru `
        -RedirectStandardOutput (Join-Path $logDirectory 'reverb.log') -RedirectStandardError (Join-Path $logDirectory 'reverb-error.log')
    Start-Sleep -Seconds 2
    if ($reverb.HasExited) {
        Write-Warn 'Reverb did not start (see storage\logs\reverb-error.log). The pages still work: they refresh every minute.'
        $reverb = $null
    } else {
        Write-Ok 'Reverb is running (output: storage\logs\reverb.log)'
    }
}

# ------------------------------------------------------------ 4. web server on every address
$lanAddresses = Get-LanIPv4
Write-Host ''
Write-Host '  Open the clinic from this PC or from any device on the same network:' -ForegroundColor White
Write-Host "     http://localhost:$AppPort" -ForegroundColor Green
Write-Host "     http://127.0.0.1:$AppPort" -ForegroundColor Green
foreach ($lan in $lanAddresses) {
    $note = if ($lan.Virtual) { "   ($($lan.Alias), virtual adapter)" } else { "   ($($lan.Alias))" }
    Write-Host "     http://$($lan.IP):$AppPort$note" -ForegroundColor Green
}
Write-Host ''
Write-Host "  Other devices need inbound TCP $AppPort and $ReverbPort allowed in this PC's firewall (docs/commands.md)." -ForegroundColor DarkGray
Write-Host '  Ctrl+C stops the server and Reverb.' -ForegroundColor DarkGray
Write-Host ''

if ($OpenBrowser) {
    Start-Process "http://localhost:$AppPort" | Out-Null
}

try {
    # 0.0.0.0 = every network interface: localhost, 127.0.0.1 and the LAN address all work, whatever the address is today.
    & php artisan serve --host=0.0.0.0 --port=$AppPort
} finally {
    if ($reverb -and -not $reverb.HasExited) {
        Stop-Process -Id $reverb.Id -Force -ErrorAction SilentlyContinue
    }
    Write-Host ''
    Write-Host 'Server stopped.' -ForegroundColor Yellow
}
