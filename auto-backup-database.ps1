# ============================================================
# NORSUCLINIC - Auto Database Backup System
# This script runs continuously and:
# 1. Checks database for changes every hour
# 2. Creates backup only if new data detected
# 3. Uses timestamp in backup filename
# 4. Maintains backup log
# ============================================================

# Configuration
$BackupDir = Join-Path $PSScriptRoot "database_backups"
$LogFile = Join-Path $BackupDir "backup_log.txt"
$LastHashFile = Join-Path $BackupDir "last_hash.txt"
$MySQLPath = "C:\wamp64\bin\mysql\mysql8.0.39\bin"

# Database credentials (from .env)
$DBHost = "127.0.0.1"
$DBPort = "3306"
$DBName = "norsu_clinic"
$DBUser = "root"
$DBPass = ""

# Backup interval in seconds (3600 = 1 hour)
$BackupInterval = 3600

# ============================================================
# Create backup directory if not exists
# ============================================================
if (-not (Test-Path $BackupDir)) {
    New-Item -ItemType Directory -Path $BackupDir | Out-Null
    Write-Host "Database backup directory created: $BackupDir" -ForegroundColor Green
}

# ============================================================
# Initialize log file
# ============================================================
if (-not (Test-Path $LogFile)) {
    $logHeader = @"
Database Backup Log - Created $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
============================================================

"@
    Set-Content -Path $LogFile -Value $logHeader
}

# ============================================================
# Functions
# ============================================================

function Write-Log {
    param([string]$Message, [string]$Color = "White")
    
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$timestamp] $Message"
    
    Write-Host $logMessage -ForegroundColor $Color
    Add-Content -Path $LogFile -Value $logMessage
}

function Get-DatabaseHash {
    try {
        $tempDump = Join-Path $env:TEMP "temp_db_check.sql"
        
        # Dump database to temp file
        $mysqldumpPath = Join-Path $MySQLPath "mysqldump.exe"
        $arguments = @(
            "--host=$DBHost",
            "--port=$DBPort",
            "--user=$DBUser",
            "--password=$DBPass",
            "--quick",
            "--single-transaction",
            "--skip-comments",
            "--skip-extended-insert",
            $DBName
        )
        
        & $mysqldumpPath $arguments 2>&1 | Out-File -FilePath $tempDump -Encoding UTF8
        
        if ($LASTEXITCODE -ne 0) {
            throw "mysqldump failed with exit code $LASTEXITCODE"
        }
        
        # Calculate MD5 hash
        $hash = Get-FileHash -Path $tempDump -Algorithm MD5
        
        # Clean up temp file
        Remove-Item -Path $tempDump -Force -ErrorAction SilentlyContinue
        
        return $hash.Hash
    }
    catch {
        Write-Log "ERROR calculating database hash: $_" "Red"
        return $null
    }
}

function Backup-Database {
    param([string]$Timestamp)
    
    try {
        $backupFile = Join-Path $BackupDir "norsuclinic_backup_$Timestamp.sql"
        
        Write-Log "Creating backup: $backupFile" "Yellow"
        
        # Perform backup
        $mysqldumpPath = Join-Path $MySQLPath "mysqldump.exe"
        $arguments = @(
            "--host=$DBHost",
            "--port=$DBPort",
            "--user=$DBUser",
            "--password=$DBPass",
            "--routines",
            "--triggers",
            "--events",
            "--single-transaction",
            $DBName
        )
        
        & $mysqldumpPath $arguments 2>&1 | Out-File -FilePath $backupFile -Encoding UTF8
        
        if ($LASTEXITCODE -ne 0) {
            throw "Backup failed with exit code $LASTEXITCODE"
        }
        
        # Compress backup
        Write-Log "Compressing backup..." "Yellow"
        $zipFile = "$backupFile.zip"
        Compress-Archive -Path $backupFile -DestinationPath $zipFile -Force
        
        # Remove uncompressed file
        Remove-Item -Path $backupFile -Force
        
        # Get file size
        $fileSize = (Get-Item $zipFile).Length
        $fileSizeKB = [math]::Round($fileSize / 1KB, 2)
        
        Write-Log "✓ Backup created successfully ($fileSizeKB KB)" "Green"
        
        # Clean old backups (keep last 30 days)
        Write-Log "Cleaning old backups (keeping last 30 days)..." "Yellow"
        $cutoffDate = (Get-Date).AddDays(-30)
        Get-ChildItem -Path $BackupDir -Filter "*.zip" | 
            Where-Object { $_.LastWriteTime -lt $cutoffDate } | 
            ForEach-Object {
                Write-Log "Removing old backup: $($_.Name)" "Gray"
                Remove-Item $_.FullName -Force
            }
        
        return $true
    }
    catch {
        Write-Log "✗ ERROR: Backup failed - $_" "Red"
        return $false
    }
}

# ============================================================
# Main Script
# ============================================================

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  NORSUCLINIC - Auto Backup System Started" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Backup Directory: $BackupDir" -ForegroundColor White
Write-Host "Check Interval: Every $BackupInterval seconds (1 hour)" -ForegroundColor White
Write-Host ""
Write-Host "Press Ctrl+C to stop the backup service" -ForegroundColor Yellow
Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

Write-Log "Auto Backup System Started" "Cyan"

# ============================================================
# Main backup loop
# ============================================================

while ($true) {
    try {
        Write-Log "Checking database for changes..." "White"
        
        # Get current database hash
        $currentHash = Get-DatabaseHash
        
        if ($null -eq $currentHash) {
            Write-Log "ERROR: Failed to connect to database!" "Red"
            Write-Log "Retrying in $BackupInterval seconds..." "Yellow"
            Start-Sleep -Seconds $BackupInterval
            continue
        }
        
        # Compare with last backup hash
        $needsBackup = $false
        
        if (-not (Test-Path $LastHashFile)) {
            Write-Log "First backup - no previous hash found" "Yellow"
            $needsBackup = $true
        }
        else {
            $lastHash = Get-Content -Path $LastHashFile -Raw
            $lastHash = $lastHash.Trim()
            
            if ($currentHash -ne $lastHash) {
                Write-Log "Database changes detected!" "Green"
                $needsBackup = $true
            }
            else {
                Write-Log "No changes detected - skipping backup" "Gray"
            }
        }
        
        # Perform backup if needed
        if ($needsBackup) {
            $timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
            
            if (Backup-Database -Timestamp $timestamp) {
                # Save current hash
                Set-Content -Path $LastHashFile -Value $currentHash -NoNewline
            }
        }
        
        Write-Host ""
        Write-Host "Next check in $BackupInterval seconds (1 hour)..." -ForegroundColor Cyan
        Write-Host ""
        
        # Wait for next check
        Start-Sleep -Seconds $BackupInterval
    }
    catch {
        Write-Log "ERROR in backup loop: $_" "Red"
        Start-Sleep -Seconds 60
    }
}
