# ============================================================
# Medical University Clinic - start the clinic server (PowerShell)
#
# Everything runs in THIS ONE terminal, no extra windows:
#   1. looks for php and mysql in the Windows PATH (system AND user). When both are there WampServer is NOT needed and
#      is not opened; when one is missing the script uses WampServer (its php and mysql folders are added to the PATH
#      of this terminal only) and starts it
#   2. makes sure MySQL answers, clears and rebuilds the Laravel caches
#   3. starts Reverb (real-time updates) in the background of this terminal, its output goes to storage\logs\reverb*.log
#   4. starts the Laravel scheduler (php artisan schedule:work) the same way: it refreshes the alerts every minute and
#      syncs the expired stock at night; output in storage\logs\scheduler*.log
#   5. starts the web server on ALL network addresses (0.0.0.0), so the page opens from
#      http://localhost:8000, http://127.0.0.1:8000 and http://<this PC's current LAN address>:8000
#   6. opens the page in the DEFAULT browser once the server answers, at the first address that really answers, in this
#      order: the LAN (Ethernet) IPv4 address, then the Wi-Fi IPv4 address, then localhost. When the PC has no default
#      browser, a numbered list of the installed browsers is shown and the one you pick is used
#
# The LAN address is detected every time the script runs, so DHCP (dynamic) and static addresses both work.
# Ctrl+C stops the web server, Reverb and the scheduler together.
#
#   .\startup-dev-environment.ps1                start
#   .\startup-dev-environment.ps1 -NoBrowser     do not open a browser
#   .\startup-dev-environment.ps1 -CheckOnly     only report what was found (PATH, WampServer, browsers), start nothing
#
# There is no extra window and no automatic database backup (take one by hand: Settings > Backups or php artisan db:backup).
# ============================================================
param(
    [switch]$NoBrowser,
    [switch]$CheckOnly,
    [switch]$OpenBrowser   # kept so old shortcuts keep working: the browser opens by default now
)

$ErrorActionPreference = 'Continue'

# The project root is the folder of THIS script (nothing hard-coded).
$ProjectPath = if ($PSScriptRoot) { $PSScriptRoot } else { Split-Path -Parent $MyInvocation.MyCommand.Definition }
Set-Location $ProjectPath

$AppPort = 8000

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

# ------------------------------------------------------------ the Windows PATH (system and user)
# The folders of the saved PATH of the machine (administrator / system) and of the signed-in user, not only the PATH this
# terminal was started with.
function Get-WindowsPathFolders {
    $folders = @()
    foreach ($scope in @('Machine', 'User')) {
        $raw = [Environment]::GetEnvironmentVariable('Path', $scope)
        if ($raw) {
            foreach ($part in ($raw -split ';')) {
                $part = $part.Trim().Trim('"')
                if ($part) { $folders += [Environment]::ExpandEnvironmentVariables($part) }
            }
        }
    }
    return $folders
}

# The first file with one of these names in the Windows PATH, or $null.
function Find-InWindowsPath {
    param([string[]]$FileNames)
    foreach ($folder in (Get-WindowsPathFolders)) {
        foreach ($name in $FileNames) {
            $candidate = Join-Path $folder $name
            if (Test-Path -LiteralPath $candidate) { return $candidate }
        }
    }
    return $null
}

# ------------------------------------------------------------ WampServer
# True when ordinary users may change this file or folder: a program there could be swapped by any account of this PC, and
# the script is about to run it (wampmanager.exe, php, mysql). Compared by SID, so it does not depend on the language of
# Windows: Everyone S-1-1-0, Users S-1-5-32-545, Authenticated Users S-1-5-11, Interactive S-1-5-4.
function Test-WritableByUsers {
    param([string]$Path)
    try {
        $broad = 'S-1-1-0', 'S-1-5-32-545', 'S-1-5-11', 'S-1-5-4'
        $writeRights = [System.Security.AccessControl.FileSystemRights]'Write, Modify, FullControl, CreateFiles, AppendData, WriteData, Delete, DeleteSubdirectoriesAndFiles, ChangePermissions, TakeOwnership'
        foreach ($rule in (Get-Acl -LiteralPath $Path -ErrorAction Stop).Access) {
            if ($rule.AccessControlType -ne 'Allow') { continue }
            $sid = $rule.IdentityReference.Translate([System.Security.Principal.SecurityIdentifier]).Value
            if ($broad -notcontains $sid) { continue }
            if (($rule.FileSystemRights -band $writeRights) -ne 0) { return $true }
        }
        return $false
    } catch {
        return $true   # cannot read the permissions: do not trust it
    }
}

# Where WampServer is installed, or $null. No drive scan: only places an ordinary account cannot make up.
#   1. the install location WampServer's own installer registered (HKLM: writing there needs an administrator);
#   2. WAMP_ROOT, a folder you set yourself in the Windows environment variables;
#   3. wamp64 / wamp on the Windows drive, used only when ordinary users cannot change files in it.
# Returns [pscustomobject]@{ Path; Source }.
function Find-WampRoot {
    $uninstallKeys = @(
        'HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall',
        'HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall'
    )
    foreach ($base in $uninstallKeys) {
        if (-not (Test-Path $base)) { continue }
        foreach ($key in (Get-ChildItem $base -ErrorAction SilentlyContinue)) {
            try {
                $entry = Get-ItemProperty -Path $key.PSPath -ErrorAction Stop
                if ($entry.DisplayName -like 'Wampserver*' -and $entry.InstallLocation) {
                    $root = $entry.InstallLocation.TrimEnd('\')
                    if (Test-Path -LiteralPath (Join-Path $root 'wampmanager.exe')) { return [pscustomobject]@{ Path = $root; Source = 'installer registry entry'; Trusted = $true } }
                }
            } catch { }
        }
    }

    $fromEnv = [Environment]::GetEnvironmentVariable('WAMP_ROOT')
    if ($fromEnv -and (Test-Path -LiteralPath (Join-Path $fromEnv 'wampmanager.exe'))) {
        return [pscustomobject]@{ Path = $fromEnv.TrimEnd('\'); Source = 'WAMP_ROOT'; Trusted = $true }
    }

    $systemDrive = if ($env:SystemDrive) { $env:SystemDrive } else { 'C:' }
    foreach ($folder in @('wamp64', 'wamp')) {
        $root = Join-Path "$systemDrive\" $folder
        if (-not (Test-Path -LiteralPath (Join-Path $root 'wampmanager.exe'))) { continue }
        if ((Test-WritableByUsers -Path $root) -or (Test-WritableByUsers -Path (Join-Path $root 'wampmanager.exe')) -or (Test-WritableByUsers -Path (Join-Path $root 'bin'))) {
            Write-Warn "$root looks like WampServer, but ordinary users can change files in it and it is not registered by an installer: not used (set WAMP_ROOT to use it)."
            continue
        }
        return [pscustomobject]@{ Path = $root; Source = 'system drive'; Trusted = $false }
    }
    return $null
}

# A setting of WampServer's own configuration (phpVersion, mysqlVersion ...), or $null.
function Get-WampSetting {
    param([string]$Root, [string]$Name)
    $file = Join-Path $Root 'wampmanager.conf'
    if (Test-Path -LiteralPath $file) {
        $line = Select-String -LiteralPath $file -Pattern "^\s*$Name\s*=\s*""?([^""\r\n]+)""?" | Select-Object -First 1
        if ($line) { return $line.Matches[0].Groups[1].Value.Trim() }
    }
    return $null
}

# The folder that holds $Executable (php.exe or mysql.exe) below WampServer's bin\<kind>\ folders: the version that is active in
# WampServer first (its wampmanager.conf), then the newest one. $null when there is none.
function Find-WampBinFolder {
    param([string]$Root, [string]$Kind, [string]$Executable, [string]$SubFolder = '', [string]$ActiveVersion = '')
    $parent = Join-Path $Root "bin\$Kind"
    if (-not (Test-Path -LiteralPath $parent)) { return $null }
    $versions = @(Get-ChildItem -LiteralPath $parent -Directory -ErrorAction SilentlyContinue | Sort-Object Name -Descending)
    if ($ActiveVersion) {
        $active = @($versions | Where-Object { $_.Name -like "*$ActiveVersion" })
        $versions = $active + @($versions | Where-Object { $_.Name -notlike "*$ActiveVersion" })
    }
    foreach ($version in $versions) {
        $folder = if ($SubFolder) { Join-Path $version.FullName $SubFolder } else { $version.FullName }
        if (Test-Path -LiteralPath (Join-Path $folder $Executable)) { return $folder }
    }
    return $null
}

# Start the database through WampServer: its MySQL service when this terminal may start services, otherwise the WAMP
# manager (minimized, it lives in the tray and starts its services itself).
function Start-WampDatabase {
    param([string]$WampRoot)
    $started = $false
    foreach ($service in @('wampmysqld64', 'wampmysqld', 'wampmariadb64', 'wampmariadb')) {
        if (Get-Service -Name $service -ErrorAction SilentlyContinue) {
            try { Start-Service -Name $service -ErrorAction Stop; $started = $true; break } catch { }
        }
    }
    if (-not $started -and $WampRoot) {
        if (-not (Get-Process -Name 'wampmanager' -ErrorAction SilentlyContinue)) {
            Start-Process -FilePath (Join-Path $WampRoot 'wampmanager.exe') -WindowStyle Minimized | Out-Null
        }
    }
}

# ------------------------------------------------------------ browsers
# True when Windows has a default browser set (the http link handler).
function Test-HasDefaultBrowser {
    try {
        $choice = Get-ItemProperty -Path 'HKCU:\Software\Microsoft\Windows\Shell\Associations\UrlAssociations\http\UserChoice' -ErrorAction Stop
        return [bool]$choice.ProgId
    } catch {
        return $false
    }
}

# The browsers installed on this PC: [pscustomobject]@{ Name; Path }. Read from Windows' own list of browsers, plus the
# usual install folders.
function Get-InstalledBrowsers {
    $browsers = @()
    foreach ($hive in @('HKLM:', 'HKCU:')) {
        $base = "$hive\SOFTWARE\Clients\StartMenuInternet"
        if (-not (Test-Path $base)) { continue }
        foreach ($key in (Get-ChildItem $base -ErrorAction SilentlyContinue)) {
            try {
                $name = (Get-ItemProperty -Path $key.PSPath -ErrorAction Stop).'(default)'
                $command = (Get-ItemProperty -Path (Join-Path $key.PSPath 'shell\open\command') -ErrorAction Stop).'(default)'
                if (-not $command) { continue }
                $path = if ($command.StartsWith('"')) { $command.Substring(1, $command.IndexOf('"', 1) - 1) } else { ($command -split ' ')[0] }
                if ($name -and (Test-Path -LiteralPath $path)) { $browsers += [pscustomobject]@{ Name = $name; Path = $path } }
            } catch { }
        }
    }

    $programFiles = $env:ProgramFiles
    $programFilesX86 = ${env:ProgramFiles(x86)}
    $known = @(
        @{ Name = 'Google Chrome'; Path = "$programFiles\Google\Chrome\Application\chrome.exe" },
        @{ Name = 'Google Chrome'; Path = "$programFilesX86\Google\Chrome\Application\chrome.exe" },
        @{ Name = 'Google Chrome'; Path = "$env:LOCALAPPDATA\Google\Chrome\Application\chrome.exe" },
        @{ Name = 'Microsoft Edge'; Path = "$programFilesX86\Microsoft\Edge\Application\msedge.exe" },
        @{ Name = 'Microsoft Edge'; Path = "$programFiles\Microsoft\Edge\Application\msedge.exe" },
        @{ Name = 'Mozilla Firefox'; Path = "$programFiles\Mozilla Firefox\firefox.exe" },
        @{ Name = 'Mozilla Firefox'; Path = "$programFilesX86\Mozilla Firefox\firefox.exe" },
        @{ Name = 'Brave'; Path = "$programFiles\BraveSoftware\Brave-Browser\Application\brave.exe" },
        @{ Name = 'Opera'; Path = "$env:LOCALAPPDATA\Programs\Opera\opera.exe" },
        @{ Name = 'Vivaldi'; Path = "$env:LOCALAPPDATA\Vivaldi\Application\vivaldi.exe" }
    )
    foreach ($item in $known) {
        if ($item.Path -and (Test-Path -LiteralPath $item.Path)) { $browsers += [pscustomobject]@{ Name = $item.Name; Path = $item.Path } }
    }

    # one line per program file
    return @($browsers | Group-Object { $_.Path.ToLowerInvariant() } | ForEach-Object { $_.Group[0] } | Sort-Object Name)
}

# Which browser to open: $null = the default browser of Windows; otherwise the path of the browser the user picked.
# Returns $false when no browser should be opened at all.
function Select-BrowserToOpen {
    if (Test-HasDefaultBrowser) { return $null }

    $installed = Get-InstalledBrowsers
    if ($installed.Count -eq 0) {
        Write-Warn 'No default browser is set and no browser was found: open the address below by hand.'
        return $false
    }

    Write-Host ''
    Write-Host '  Windows has no default browser. Where should the clinic open?' -ForegroundColor White
    for ($i = 0; $i -lt $installed.Count; $i++) {
        Write-Host ("     {0}. {1}" -f ($i + 1), $installed[$i].Name)
    }
    Write-Host '     0. none (do not open a browser)'
    while ($true) {
        try { $answer = Read-Host '  Number' } catch { return $false }
        if ($answer -match '^\d+$') {
            $number = [int]$answer
            if ($number -eq 0) { return $false }
            if ($number -ge 1 -and $number -le $installed.Count) { return $installed[$number - 1].Path }
        }
        Write-Warn 'Type one of the numbers above.'
    }
}

# Open the page once the server answers. It runs on a second THREAD of this very PowerShell process (a runspace), not as a
# job: a job starts another powershell.exe, which would be one more console host. Nothing new is created: one terminal.
# It waits for the port, then opens the first address of $Urls that answers (LAN, Wi-Fi, ..., localhost).
function Start-BrowserWhenReady {
    param([string[]]$Urls, [int]$Port, $BrowserPath)
    $work = {
        param($UrlList, $Port, $BrowserPath)
        $Urls = @($UrlList -split ' ' | Where-Object { $_ })
        $deadline = (Get-Date).AddSeconds(90)
        while ((Get-Date) -lt $deadline) {
            try {
                $client = New-Object System.Net.Sockets.TcpClient
                $client.Connect('127.0.0.1', $Port)
                $client.Close()
                break
            } catch { Start-Sleep -Milliseconds 500 }
        }
        $answers = {
            param($Url, $TimeoutMs, $Attempts)
            for ($try = 0; $try -lt $Attempts; $try++) {
                try {
                    $request = [System.Net.WebRequest]::Create($Url)
                    $request.Timeout = $TimeoutMs
                    $response = $request.GetResponse()
                    $response.Close()
                    return $true
                } catch [System.Net.WebException] {
                    if ($_.Exception.Response) { return $true }
                } catch { }
                Start-Sleep -Milliseconds 500
            }
            return $false
        }
        # The very first request boots Laravel (cold caches) and can take several seconds: wait for it once on the local
        # address, so a slow first answer is never mistaken for "this address does not work".
        [void](& $answers "http://127.0.0.1:$Port" 40000 1)
        $target = $Urls[$Urls.Count - 1]
        foreach ($url in $Urls) {
            if (& $answers $url 6000 3) { $target = $url; break }
        }
        if ($BrowserPath) { Start-Process -FilePath $BrowserPath -ArgumentList $target } else { Start-Process $target }
    }
    $script:browserThread = [powershell]::Create()
    [void]$script:browserThread.AddScript($work.ToString()).AddArgument(($Urls -join ' ')).AddArgument($Port).AddArgument($BrowserPath)
    [void]$script:browserThread.BeginInvoke()
}

# ------------------------------------------------------------ the IPv4 addresses
# The IPv4 addresses of this PC, best first. Kind: LAN (a wired Ethernet adapter), Wi-Fi, Other, Virtual (WSL, Hyper-V,
# VirtualBox, VMware, Docker, VPN ...). Order: LAN, Wi-Fi, Other, Virtual; inside each kind the adapter with a default
# gateway (the one that really reaches the network) first.
function Get-LanIPv4 {
    $virtualPattern = 'vEthernet|WSL|Hyper-V|VirtualBox|VMware|Docker|Loopback|Bluetooth|Tailscale|ZeroTier|TAP-|TUN|VPN|Virtual'
    $kindRank = @{ 'LAN' = 0; 'Wi-Fi' = 1; 'Other' = 2; 'Virtual' = 3 }
    $found = @()
    try {
        foreach ($config in (Get-NetIPConfiguration -ErrorAction Stop)) {
            if (-not $config.NetAdapter -or $config.NetAdapter.Status -ne 'Up') { continue }
            $nic = Get-NetAdapter -InterfaceIndex $config.InterfaceIndex -ErrorAction SilentlyContinue
            $media = if ($nic) { [string]$nic.PhysicalMediaType } else { '' }
            $isVirtual = ($config.InterfaceAlias -match $virtualPattern) -or ($config.InterfaceDescription -match $virtualPattern) -or ($nic -and $nic.Virtual)
            $kind = 'Other'
            if ($isVirtual) { $kind = 'Virtual' }
            elseif ($media -match '802\.11' -or ($nic -and $nic.InterfaceType -eq 71)) { $kind = 'Wi-Fi' }
            elseif ($media -match '802\.3' -or ($nic -and $nic.InterfaceType -eq 6)) { $kind = 'LAN' }
            foreach ($address in $config.IPv4Address) {
                $ip = $address.IPAddress
                if (-not $ip -or $ip -like '127.*' -or $ip -like '169.254.*') { continue }
                $found += [pscustomobject]@{
                    IP      = $ip
                    Alias   = $config.InterfaceAlias
                    Kind    = $kind
                    Rank    = $kindRank[$kind]
                    Gateway = [bool]$config.IPv4DefaultGateway
                    Virtual = ($kind -eq 'Virtual')
                }
            }
        }
    } catch { }

    if ($found.Count -eq 0) {
        try {
            foreach ($address in [System.Net.Dns]::GetHostAddresses([System.Net.Dns]::GetHostName())) {
                $ip = $address.IPAddressToString
                if ($address.AddressFamily -eq 'InterNetwork' -and $ip -notlike '127.*' -and $ip -notlike '169.254.*') {
                    $found += [pscustomobject]@{ IP = $ip; Alias = 'network'; Kind = 'Other'; Rank = 2; Gateway = $false; Virtual = $false }
                }
            }
        } catch { }
    }

    return @($found | Sort-Object @{ Expression = { $_.Rank } }, @{ Expression = { -not $_.Gateway } })
}

# The addresses the browser may open, in order: the LAN IPv4, the Wi-Fi IPv4, any other real adapter, then localhost.
# Virtual adapters (WSL, VirtualBox ...) are never opened.
function Get-BrowserUrls {
    param([int]$Port)
    $urls = @()
    foreach ($lan in (Get-LanIPv4)) {
        if (-not $lan.Virtual) { $urls += "http://$($lan.IP):$Port" }
    }
    $urls += "http://localhost:$Port"
    return $urls
}

# True when the address answers with ANY HTTP reply (a page, a redirect or an error page): the server is there.
function Test-UrlAnswers {
    param([string]$Url, [int]$TimeoutMs = 6000, [int]$Attempts = 3)
    for ($try = 0; $try -lt $Attempts; $try++) {
        try {
            $request = [System.Net.WebRequest]::Create($Url)
            $request.Timeout = $TimeoutMs
            $request.Method = 'GET'
            $response = $request.GetResponse()
            $response.Close()
            return $true
        } catch [System.Net.WebException] {
            if ($_.Exception.Response) { return $true }
        } catch { }
        Start-Sleep -Milliseconds 500
    }
    return $false
}

# Open the first address of the list that answers (the last one, localhost, when none does) in the chosen browser.
function Open-FirstAnsweringUrl {
    param([string[]]$Urls, $BrowserPath)
    $target = $Urls[$Urls.Count - 1]
    foreach ($url in $Urls) {
        if (Test-UrlAnswers -Url $url) { $target = $url; break }
    }
    if ($BrowserPath) { Start-Process -FilePath $BrowserPath -ArgumentList $target | Out-Null } else { Start-Process $target | Out-Null }
    return $target
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

# ------------------------------------------------------------ 1. php and mysql: the Windows PATH or WampServer
$phpInPath = Find-InWindowsPath @('php.exe')
$mysqlInPath = Find-InWindowsPath @('mysql.exe', 'mysqld.exe')
$needWamp = -not ($phpInPath -and $mysqlInPath)
$wampFound = Find-WampRoot
$wampRoot = if ($wampFound) { $wampFound.Path } else { $null }

if ($CheckOnly) {
    Write-Host '  Check only (nothing is started):' -ForegroundColor White
    Write-Host ("     php in the Windows PATH   : " + $(if ($phpInPath) { $phpInPath } else { 'NOT FOUND' }))
    Write-Host ("     mysql in the Windows PATH : " + $(if ($mysqlInPath) { $mysqlInPath } else { 'NOT FOUND' }))
    Write-Host ("     WampServer                : " + $(if ($wampRoot) { $wampRoot + '  (' + $wampFound.Source + ')' } else { 'not found' }))
    Write-Host ("     WampServer needed         : " + $(if ($needWamp) { 'yes' } else { 'no' }))
    Write-Host ("     MySQL answering on port $DbPort : " + $(if (Test-PortInUse -Port $DbPort) { 'yes' } else { 'no' }))
    Write-Host ("     Default browser set       : " + $(if (Test-HasDefaultBrowser) { 'yes' } else { 'no (a list would be shown)' }))
    Write-Host '     Installed browsers        :'
    foreach ($browser in (Get-InstalledBrowsers)) { Write-Host ("        {0}  ({1})" -f $browser.Name, $browser.Path) }
    foreach ($lan in (Get-LanIPv4)) { Write-Host ("     {0,-6} address           : {1}  ({2})" -f $lan.Kind, $lan.IP, $lan.Alias) }
    Write-Host ("     The browser would open    : " + ((Get-BrowserUrls -Port $AppPort) -join '  then  '))
    exit 0
}

if ($needWamp) {
    $missing = @()
    if (-not $phpInPath) { $missing += 'php' }
    if (-not $mysqlInPath) { $missing += 'mysql' }
    Write-Step ("Not in the Windows PATH (system or user): " + ($missing -join ' and ') + " - using WampServer")
    if ($wampRoot) {
        # Only for this terminal: nothing is written to the Windows settings.
        $extra = @()
        if (-not $phpInPath) { $folder = Find-WampBinFolder -Root $wampRoot -Kind 'php' -Executable 'php.exe' -ActiveVersion (Get-WampSetting $wampRoot 'phpVersion'); if ($folder) { $extra += $folder } }
        if (-not $mysqlInPath) {
            $folder = Find-WampBinFolder -Root $wampRoot -Kind 'mysql' -Executable 'mysql.exe' -SubFolder 'bin' -ActiveVersion (Get-WampSetting $wampRoot 'mysqlVersion')
            if (-not $folder) { $folder = Find-WampBinFolder -Root $wampRoot -Kind 'mariadb' -Executable 'mysql.exe' -SubFolder 'bin' -ActiveVersion (Get-WampSetting $wampRoot 'mariadbVersion') }
            if ($folder) { $extra += $folder }
        }
        if ($extra.Count -gt 0) { $env:Path = (($extra -join ';') + ';' + $env:Path) }
        Write-Ok "WampServer found: $wampRoot ($($wampFound.Source))"
        # Everything that is about to run: the manager and the php / mysql programs that were put on this terminal's PATH.
        $toRun = @(Join-Path $wampRoot 'wampmanager.exe') + @($extra | ForEach-Object { $_ })
        $changeable = @($toRun | Where-Object { $_ -and (Test-WritableByUsers -Path $_) })
        if ($changeable.Count -gt 0) {
            Write-Warn ('Any account of this PC can change files in: ' + ($changeable -join ', '))
            Write-Host '     That is how WampServer is installed by default. On a shared PC, restrict these folders to administrators.' -ForegroundColor DarkYellow
        }
    } else {
        Write-Warn 'WampServer was not found (installer registry entry, WAMP_ROOT, wamp64 / wamp on the Windows drive).'
    }
} else {
    Write-Ok 'php and mysql are in the Windows PATH: WampServer is not needed'
}

if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Warn 'php was not found (not in the Windows PATH and not in WampServer). Install WampServer or add php to the PATH.'
    exit 1
}

# ------------------------------------------------------------ guard: already running
if (Test-PortInUse -Port $AppPort) {
    Write-Warn "A server is already running on port $AppPort, not starting another one."
    foreach ($lan in (Get-LanIPv4)) { Write-Host "     http://$($lan.IP):$AppPort   ($($lan.Kind): $($lan.Alias))" }
    Write-Host "     http://localhost:$AppPort"
    if (-not $NoBrowser) {
        $browserPath = Select-BrowserToOpen
        if ($browserPath -ne $false) {
            $opened = Open-FirstAnsweringUrl -Urls (Get-BrowserUrls -Port $AppPort) -BrowserPath $browserPath
            Write-Ok "Opened $opened"
        }
    }
    exit 0
}

# ------------------------------------------------------------ 2. database
Write-Step "Database (MySQL on port $DbPort)"
if (Test-PortInUse -Port $DbPort) {
    Write-Ok 'MySQL is already running'
} else {
    if ($needWamp) {
        Start-WampDatabase -WampRoot $wampRoot
    } else {
        # WampServer is not needed: a MySQL / MariaDB Windows service (when there is one) does the job.
        foreach ($service in (Get-Service -ErrorAction SilentlyContinue | Where-Object { $_.Name -match '^(mysql|mariadb)' -and $_.Status -ne 'Running' })) {
            try { Start-Service -Name $service.Name -ErrorAction Stop; break } catch { }
        }
    }
    # Wait for the port instead of a fixed pause.
    $deadline = (Get-Date).AddSeconds(60)
    while (-not (Test-PortInUse -Port $DbPort) -and (Get-Date) -lt $deadline) { Start-Sleep -Seconds 1 }

    # The PATH has mysql but nothing started it: WampServer, when installed, is the last resort.
    if (-not (Test-PortInUse -Port $DbPort) -and -not $needWamp -and $wampRoot) {
        Write-Warn 'MySQL did not start from its Windows service: trying WampServer'
        Start-WampDatabase -WampRoot $wampRoot
        $deadline = (Get-Date).AddSeconds(60)
        while (-not (Test-PortInUse -Port $DbPort) -and (Get-Date) -lt $deadline) { Start-Sleep -Seconds 1 }
    }

    if (Test-PortInUse -Port $DbPort) {
        Write-Ok 'MySQL is running'
    } else {
        Write-Warn "MySQL did not answer on port $DbPort within 60 seconds. Start it by hand; the site will not work without it."
    }
}

# ------------------------------------------------------------ 3. caches
Write-Step 'Clearing and rebuilding the Laravel caches'
foreach ($command in @('cache:clear', 'config:clear', 'route:clear', 'view:clear', 'clear-compiled', 'optimize:clear', 'optimize')) {
    Invoke-ArtisanQuiet -Arguments @($command)
}
Write-Ok 'Caches ready'

# ------------------------------------------------------------ 4. Reverb (real-time updates), same terminal
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

# ------------------------------------------------------------ 5. scheduler (alerts, expiry sync), same terminal
$scheduler = $null
$schedulerRunning = $false
try {
    $schedulerRunning = [bool](Get-CimInstance Win32_Process -Filter "Name = 'php.exe'" -ErrorAction Stop |
        Where-Object { $_.CommandLine -match 'schedule:(work|run)' } | Select-Object -First 1)
} catch { }
if ($schedulerRunning) {
    Write-Ok 'The scheduler is already running'
} else {
    Write-Step 'Scheduler (alerts every minute, expired stock at night)'
    $scheduler = Start-Process -FilePath 'php' -ArgumentList 'artisan', 'schedule:work' -WorkingDirectory $ProjectPath `
        -NoNewWindow -PassThru `
        -RedirectStandardOutput (Join-Path $logDirectory 'scheduler.log') -RedirectStandardError (Join-Path $logDirectory 'scheduler-error.log')
    Start-Sleep -Seconds 1
    if ($scheduler.HasExited) {
        Write-Warn 'The scheduler did not start (see storage\logs\scheduler-error.log). The alerts still refresh after each stock / queue / consultation change.'
        $scheduler = $null
    } else {
        Write-Ok 'Scheduler is running (output: storage\logs\scheduler.log)'
    }
}

# ------------------------------------------------------------ 6. browser (asked before the server takes over the terminal)
$browserPath = $false
if (-not $NoBrowser) { $browserPath = Select-BrowserToOpen }

# ------------------------------------------------------------ 7. web server on every address
$lanAddresses = Get-LanIPv4
$browserUrls = Get-BrowserUrls -Port $AppPort
Write-Host ''
Write-Host '  Open the clinic from this PC or from any device on the same network:' -ForegroundColor White
foreach ($lan in ($lanAddresses | Where-Object { -not $_.Virtual })) {
    Write-Host "     http://$($lan.IP):$AppPort   ($($lan.Kind): $($lan.Alias))" -ForegroundColor Green
}
Write-Host "     http://localhost:$AppPort" -ForegroundColor Green
Write-Host "     http://127.0.0.1:$AppPort" -ForegroundColor Green
foreach ($lan in ($lanAddresses | Where-Object { $_.Virtual })) {
    Write-Host "     http://$($lan.IP):$AppPort   (virtual adapter: $($lan.Alias), only for this PC's own virtual machines)" -ForegroundColor DarkGreen
}
Write-Host ''
Write-Host "  Other devices need inbound TCP $AppPort and $ReverbPort allowed in this PC's firewall (docs/commands.md)." -ForegroundColor DarkGray
if ($browserPath -ne $false) {
    Write-Host ("  The browser opens the first of these that answers: " + ($browserUrls -join '  then  ')) -ForegroundColor DarkGray
}
Write-Host '  Ctrl+C stops the server, Reverb and the scheduler.' -ForegroundColor DarkGray
Write-Host ''

if ($browserPath -ne $false) {
    Start-BrowserWhenReady -Urls $browserUrls -Port $AppPort -BrowserPath $browserPath
}

try {
    # 0.0.0.0 = every network interface: localhost, 127.0.0.1 and the LAN address all work, whatever the address is today.
    & php artisan serve --host=0.0.0.0 --port=$AppPort
} finally {
    foreach ($background in @($reverb, $scheduler)) {
        if ($background -and -not $background.HasExited) {
            Stop-Process -Id $background.Id -Force -ErrorAction SilentlyContinue
        }
    }
    if ($script:browserThread) { $script:browserThread.Dispose() }
    Write-Host ''
    Write-Host 'Server stopped.' -ForegroundColor Yellow
}
