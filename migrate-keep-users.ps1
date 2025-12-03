# ============================================================
# NORSUCLINIC - Migrate Fresh While Keeping Users
# This script:
# 1. Backs up the users table to a JSON file
# 2. Runs migrate:fresh --seed
# 3. Restores the users back to the database
# 4. Cleans up the backup file
# ============================================================

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  NORSUCLINIC - Migrate Fresh (Keep Users)" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

# ============================================================
# Step 1: Backup Users Table
# ============================================================
Write-Host "[1/4] Backing up users table..." -ForegroundColor Yellow
Write-Host ""

try {
    $backupCommand = "file_put_contents('users_backup.json', DB::table('users')->get()->toJson());"
    php artisan tinker --execute=$backupCommand
    
    if ($LASTEXITCODE -ne 0) {
        throw "Tinker command failed"
    }
    
    if (-not (Test-Path "users_backup.json")) {
        throw "Backup file was not created"
    }
    
    $backupSize = (Get-Item "users_backup.json").Length
    Write-Host "✓ Users backed up successfully ($backupSize bytes)" -ForegroundColor Green
    Write-Host ""
}
catch {
    Write-Host "✗ ERROR: Failed to backup users table!" -ForegroundColor Red
    Write-Host "  $_" -ForegroundColor Red
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

# ============================================================
# Step 2: Confirm before proceeding
# ============================================================
Write-Host "[2/4] Ready to run migrate:fresh --seed" -ForegroundColor Yellow
Write-Host ""
Write-Host "WARNING: This will DROP ALL TABLES except users data!" -ForegroundColor Red
Write-Host ""

$confirm = Read-Host "Are you sure you want to continue? (y/n)"

if ($confirm -ne "y" -and $confirm -ne "Y") {
    Write-Host ""
    Write-Host "Migration cancelled by user." -ForegroundColor Yellow
    Remove-Item "users_backup.json" -ErrorAction SilentlyContinue
    Read-Host "Press Enter to exit"
    exit 0
}

Write-Host ""

# ============================================================
# Step 3: Run migrate:fresh --seed
# ============================================================
Write-Host "[3/4] Running migrate:fresh --seed..." -ForegroundColor Yellow
Write-Host ""

try {
    php artisan migrate:fresh --seed
    
    if ($LASTEXITCODE -ne 0) {
        throw "Migration failed with exit code $LASTEXITCODE"
    }
    
    Write-Host ""
    Write-Host "✓ Migration completed successfully!" -ForegroundColor Green
    Write-Host ""
}
catch {
    Write-Host ""
    Write-Host "✗ ERROR: Migration failed!" -ForegroundColor Red
    Write-Host "  $_" -ForegroundColor Red
    Write-Host ""
    Write-Host "Users backup is still available at users_backup.json" -ForegroundColor Yellow
    Write-Host "You can manually restore it if needed." -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

# ============================================================
# Step 4: Restore Users
# ============================================================
Write-Host "[4/4] Restoring users table..." -ForegroundColor Yellow
Write-Host ""

try {
    $restoreCommand = "DB::table('users')->truncate(); `$users = json_decode(file_get_contents('users_backup.json'), true); DB::table('users')->insert(`$users); echo 'Restored ' . count(`$users) . ' users';"
    php artisan tinker --execute=$restoreCommand
    
    if ($LASTEXITCODE -ne 0) {
        throw "Failed to restore users"
    }
    
    Write-Host ""
    Write-Host "✓ Users restored successfully!" -ForegroundColor Green
}
catch {
    Write-Host ""
    Write-Host "✗ ERROR: Failed to restore users!" -ForegroundColor Red
    Write-Host "  $_" -ForegroundColor Red
    Write-Host ""
    Write-Host "Users backup is still available at users_backup.json" -ForegroundColor Yellow
    Write-Host "You can manually restore it using:" -ForegroundColor Yellow
    Write-Host "  php artisan tinker" -ForegroundColor Cyan
    Write-Host '    $users = json_decode(file_get_contents("users_backup.json"), true);' -ForegroundColor Cyan
    Write-Host '    DB::table("users")->insert($users);' -ForegroundColor Cyan
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

# ============================================================
# Step 5: Cleanup
# ============================================================
Write-Host ""
Write-Host "Cleaning up backup file..." -ForegroundColor Yellow
Remove-Item "users_backup.json" -ErrorAction SilentlyContinue

Write-Host ""
Write-Host "============================================================" -ForegroundColor Green
Write-Host "  Migration Completed Successfully!" -ForegroundColor Green
Write-Host "  All tables refreshed, users preserved." -ForegroundColor Green
Write-Host "============================================================" -ForegroundColor Green
Write-Host ""

Read-Host "Press Enter to exit"
