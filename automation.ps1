param(
    [Parameter(Position = 0)]
    [string]$Command = "help",
    [string]$BindHost = "auto",
    [int]$Port = 8000,
    [switch]$SkipWamp
)

$ErrorActionPreference = "Stop"

$ProjectRoot = $PSScriptRoot
$DotEnvPath = Join-Path $ProjectRoot ".env"
$DefaultWampPath = "C:\wamp64\wampmanager.exe"
$EnvValues = @{}
$DefaultPort = 8000

function Write-Info {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor Cyan
}

function Write-Ok {
    param([string]$Message)
    Write-Host "[ OK ] $Message" -ForegroundColor Green
}

function Write-Warn {
    param([string]$Message)
    Write-Host "[WARN] $Message" -ForegroundColor Yellow
}

function Write-Err {
    param([string]$Message)
    Write-Host "[ERR ] $Message" -ForegroundColor Red
}

function Get-EnvMap {
    param([string]$FilePath)

    $result = @{}

    if (-not (Test-Path $FilePath)) {
        return $result
    }

    foreach ($line in Get-Content $FilePath) {
        $trimmed = $line.Trim()

        if ([string]::IsNullOrWhiteSpace($trimmed) -or $trimmed.StartsWith("#")) {
            continue
        }

        $separatorIndex = $trimmed.IndexOf("=")
        if ($separatorIndex -lt 1) {
            continue
        }

        $key = $trimmed.Substring(0, $separatorIndex).Trim()
        $value = $trimmed.Substring($separatorIndex + 1).Trim()

        if (($value.StartsWith('"') -and $value.EndsWith('"')) -or ($value.StartsWith("'") -and $value.EndsWith("'"))) {
            $value = $value.Substring(1, $value.Length - 2)
        }

        $result[$key] = $value
    }

    return $result
}

function Ensure-ProjectRoot {
    if (-not (Test-Path (Join-Path $ProjectRoot "artisan"))) {
        throw "artisan file not found at project root: $ProjectRoot"
    }

    Set-Location $ProjectRoot
}

function Invoke-Artisan {
    param(
        [string[]]$Arguments,
        [switch]$AllowFailure
    )

    & php artisan @Arguments

    if (-not $AllowFailure -and $LASTEXITCODE -ne 0) {
        throw "php artisan $($Arguments -join ' ') failed with exit code $LASTEXITCODE"
    }
}

function Invoke-CacheClear {
    Ensure-ProjectRoot

    Write-Info "Clearing Laravel caches"
    foreach ($cmd in @("cache:clear", "config:clear", "route:clear", "view:clear", "clear-compiled", "optimize:clear")) {
        Invoke-Artisan -Arguments @($cmd)
        Write-Ok "$cmd"
    }
}

function Start-WampIfAvailable {
    param([switch]$Disabled)

    if ($Disabled) {
        Write-Info "Skipping WAMP startup because -SkipWamp was provided"
        return
    }

    if (-not (Test-Path $DefaultWampPath)) {
        Write-Warn "WAMP launcher not found at $DefaultWampPath. Continuing without starting WAMP."
        return
    }

    $wampProcess = Get-Process -Name "wampmanager" -ErrorAction SilentlyContinue
    if ($wampProcess) {
        Write-Info "WAMP is already running"
        return
    }

    Start-Process $DefaultWampPath | Out-Null
    Write-Ok "WAMP launch requested"
}

function Get-PreferredIPv4 {
    $ipv4Candidates = @(
        [System.Net.Dns]::GetHostAddresses([System.Net.Dns]::GetHostName()) |
            Where-Object { $_.AddressFamily -eq [System.Net.Sockets.AddressFamily]::InterNetwork } |
            ForEach-Object { $_.IPAddressToString } |
            Where-Object { $_ -ne "127.0.0.1" -and -not $_.StartsWith("169.254.") } |
            Select-Object -Unique
    )

    if ($ipv4Candidates.Count -gt 0) {
        return $ipv4Candidates[0]
    }

    return $null
}

function Resolve-BindHost {
    param([string]$RequestedHost)

    $normalized = if ([string]::IsNullOrWhiteSpace($RequestedHost)) {
        "auto"
    }
    else {
        $RequestedHost.Trim().ToLowerInvariant()
    }

    if ($normalized -in @("auto", "ipv4")) {
        $detectedIPv4 = Get-PreferredIPv4
        if ($detectedIPv4) {
            Write-Info "Using detected IPv4 bind host: $detectedIPv4"
            return $detectedIPv4
        }

        Write-Warn "No non-loopback IPv4 detected. Falling back to 0.0.0.0."
        return "0.0.0.0"
    }

    return $RequestedHost
}

function Stop-ProcessesUsingPort {
    param([int]$TargetPort)

    $processIds = @()

    $tcpConnections = Get-NetTCPConnection -LocalPort $TargetPort -ErrorAction SilentlyContinue
    if ($tcpConnections) {
        $processIds = @($tcpConnections | Select-Object -ExpandProperty OwningProcess -Unique)
    }

    if ($processIds.Count -eq 0) {
        $netstatLines = netstat -ano -p tcp | Select-String ":$TargetPort\s"
        foreach ($line in $netstatLines) {
            $tokens = ($line.ToString().Trim() -split "\s+")
            if ($tokens.Count -lt 5) {
                continue
            }

            $pidToken = $tokens[$tokens.Count - 1]
            $parsedId = 0
            if ([int]::TryParse($pidToken, [ref]$parsedId)) {
                $processIds += $parsedId
            }
        }
    }

    $processIds = @($processIds | Where-Object { $_ -gt 0 } | Select-Object -Unique)

    if ($processIds.Count -eq 0) {
        Write-Info "Port $TargetPort is already free"
        return
    }

    foreach ($processId in $processIds) {
        try {
            $process = Get-Process -Id $processId -ErrorAction Stop
            Stop-Process -Id $processId -Force -ErrorAction Stop
            Write-Ok "Stopped $($process.ProcessName) (PID $processId) using port $TargetPort"
        }
        catch {
            Write-Warn "Failed to stop PID $processId on port $TargetPort. $($_.Exception.Message)"
        }
    }
}

function Invoke-Start {
    Ensure-ProjectRoot

    $targetPort = $DefaultPort
    if ($Port -ne $DefaultPort) {
        Write-Warn "Port is fixed to $DefaultPort for this automation flow. Ignoring -Port $Port."
    }

    $resolvedBindHost = Resolve-BindHost -RequestedHost $BindHost

    Start-WampIfAvailable -Disabled:$SkipWamp
    Stop-ProcessesUsingPort -TargetPort $targetPort
    Invoke-CacheClear

    Write-Info "Starting Laravel server"
    Write-Host ""

    if ($resolvedBindHost -eq "0.0.0.0") {
        Write-Host "  Local URL: http://127.0.0.1:$targetPort" -ForegroundColor White

        $detectedIPv4 = Get-PreferredIPv4
        if ($detectedIPv4) {
            Write-Host "  LAN URL:   http://${detectedIPv4}:$targetPort" -ForegroundColor White
        }

        Write-Host "  Bind Host: 0.0.0.0 (all interfaces)" -ForegroundColor White
    }
    else {
        Write-Host "  Local URL: http://127.0.0.1:$targetPort" -ForegroundColor White
        Write-Host "  LAN URL:   http://${resolvedBindHost}:$targetPort" -ForegroundColor White
        Write-Host "  Bind Host: $resolvedBindHost" -ForegroundColor White
    }

    Write-Host ""

    & php artisan serve --host=$resolvedBindHost --port=$targetPort
}

function Invoke-Stop {
    $phpProcesses = Get-Process -Name "php" -ErrorAction SilentlyContinue

    if (-not $phpProcesses) {
        Write-Info "No php processes found"
        return
    }

    $phpProcesses | Stop-Process -Force
    Write-Ok "Stopped $($phpProcesses.Count) php process(es)"
}

function Invoke-MigrateKeepUsers {
    Ensure-ProjectRoot

    $backupFile = Join-Path $ProjectRoot "users_backup.json"
    $backupCommand = "file_put_contents('users_backup.json', DB::table('users')->get()->toJson());"

    Write-Info "Backing up users table"
    & php artisan tinker --execute=$backupCommand
    if ($LASTEXITCODE -ne 0 -or -not (Test-Path $backupFile)) {
        throw "Failed to backup users table"
    }

    Write-Warn "This will run migrate:fresh --seed and recreate all tables."
    $confirm = Read-Host "Type YES to continue"
    if ($confirm -ne "YES") {
        Remove-Item $backupFile -ErrorAction SilentlyContinue
        Write-Warn "Migration cancelled"
        return
    }

    Invoke-Artisan -Arguments @("migrate:fresh", "--seed")

    $restoreCommand = "DB::table('users')->truncate(); `$rows = json_decode(file_get_contents('users_backup.json'), true); DB::table('users')->insert(`$rows); echo 'Restored ' . count(`$rows) . ' users';"
    & php artisan tinker --execute=$restoreCommand
    if ($LASTEXITCODE -ne 0) {
        throw "migrate:fresh succeeded but user restore failed. users_backup.json was kept for manual recovery."
    }

    Remove-Item $backupFile -ErrorAction SilentlyContinue
    Write-Ok "Migration completed and users restored"
}

function Invoke-ScheduleRun {
    Ensure-ProjectRoot
    Invoke-Artisan -Arguments @("schedule:run")
    Write-Ok "Scheduler run completed"
}

function Show-Status {
    Ensure-ProjectRoot

    $php = Get-Command php -ErrorAction SilentlyContinue
    if ($php) {
        Write-Ok "php found at $($php.Source)"
    } else {
        Write-Warn "php not found in PATH"
    }

    if (Test-Path $DotEnvPath) {
        Write-Ok ".env found"
    } else {
        Write-Warn ".env not found"
    }

    $detectedIPv4 = Get-PreferredIPv4
    if ($detectedIPv4) {
        Write-Ok "Detected IPv4: $detectedIPv4"
    }
    else {
        Write-Warn "No non-loopback IPv4 detected"
    }

    Write-Info "Configured automation port: $DefaultPort"
}

function Show-Help {
    Write-Host ""
    Write-Host "NORSUCLINIC Automation CLI" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Usage:" -ForegroundColor White
    Write-Host "  .\\automation.ps1 <command> [options]"
    Write-Host "  .\\automation.bat <command> [options]"
    Write-Host ""
    Write-Host "Commands:" -ForegroundColor White
    Write-Host "  start                 Auto-start WAMP, free port 8000, clear caches, run artisan serve"
    Write-Host "  stop                  Stop all php processes"
    Write-Host "  cache                 Clear Laravel caches"
    Write-Host "  schedule              Run artisan schedule:run once"
    Write-Host "  migrate               Run migrate:fresh --seed while preserving users"
    Write-Host "  status                Show runtime and tool status"
    Write-Host "  help                  Show this help"
    Write-Host ""
    Write-Host "Options for start:" -ForegroundColor White
    Write-Host "  -BindHost <auto|ipv4|0.0.0.0|IP> Bind host (default: auto)"
    Write-Host "  -Port <number>        Port is fixed to 8000 (other values are ignored)"
    Write-Host "  -SkipWamp             Do not attempt to launch WAMP"
    Write-Host ""
}

try {
    $EnvValues = Get-EnvMap -FilePath $DotEnvPath

    switch ($Command.ToLowerInvariant()) {
        "start" { Invoke-Start }
        "stop" { Invoke-Stop }
        "cache" { Invoke-CacheClear }
        "cache:clear" { Invoke-CacheClear }
        "schedule" { Invoke-ScheduleRun }
        "schedule:run" { Invoke-ScheduleRun }
        "migrate" { Invoke-MigrateKeepUsers }
        "migrate:keep-users" { Invoke-MigrateKeepUsers }
        "status" { Show-Status }
        "help" { Show-Help }
        default {
            Write-Err "Unknown command: $Command"
            Show-Help
            exit 1
        }
    }
}
catch {
    Write-Err $_.Exception.Message
    exit 1
}