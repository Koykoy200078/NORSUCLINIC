# NORSU Clinic - Documentation and Debug Files Cleanup Script
# Date: October 16, 2025
# Purpose: Move documentation and debug files to organized folders

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "NORSU Clinic - Cleanup Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Create directories if they don't exist
$docsDir = ".\docs"
$archiveDir = ".\docs\archive"
$debugDir = ".\debug"
$testFilesDir = ".\debug\test-files"

Write-Host "Creating directories..." -ForegroundColor Yellow
New-Item -ItemType Directory -Path $docsDir -Force | Out-Null
New-Item -ItemType Directory -Path $archiveDir -Force | Out-Null
New-Item -ItemType Directory -Path $debugDir -Force | Out-Null
New-Item -ItemType Directory -Path $testFilesDir -Force | Out-Null
Write-Host "✓ Directories created" -ForegroundColor Green
Write-Host ""

# Activity Log Documentation Files to Keep (move to docs folder)
Write-Host "Moving Activity Log documentation to docs folder..." -ForegroundColor Yellow
$keepDocs = @(
    "ACTIVITY_LOG_IMPLEMENTATION.md",
    "ACTIVITY_LOG_QUICK_REFERENCE.md"
)

foreach ($doc in $keepDocs) {
    if (Test-Path $doc) {
        Move-Item -Path $doc -Destination $docsDir -Force
        Write-Host "  ✓ Moved $doc to docs/" -ForegroundColor Green
    }
}
Write-Host ""

# Activity Log Documentation Files to Archive
Write-Host "Archiving old Activity Log documentation..." -ForegroundColor Yellow
$archiveDocs = @(
    "ACTIVITY_LOG_VERIFICATION.md",
    "ACTIVITY_LOG_TESTING_GUIDE.md",
    "ACTIVITY_LOG_SUMMARY.md",
    "ACTIVITY_LOG_ROUTES_MENU.md",
    "ACTIVITY_LOG_QUICK_START.md",
    "ACTIVITY_LOG_FINAL_FIX.md",
    "ACTIVITY_LOG_FIELD_MAPPINGS.md",
    "ACTIVITY_LOG_COMPLETE_SUMMARY.md",
    "ADD_MENU_ITEM_GUIDE.md"
)

foreach ($doc in $archiveDocs) {
    if (Test-Path $doc) {
        Move-Item -Path $doc -Destination $archiveDir -Force
        Write-Host "  ✓ Archived $doc" -ForegroundColor Green
    }
}
Write-Host ""

# Database Cleanup Documentation to Archive
Write-Host "Archiving database cleanup documentation..." -ForegroundColor Yellow
$dbCleanupDocs = @(
    "REVIEW_REMOVAL_COMPLETE.md",
    "PROTECTED_SYSTEMS_SUMMARY.md",
    "DEEP_SCAN_COMPLETE.md",
    "DATABASE_CLEANUP_ANALYSIS_BACKUP.md",
    "DATABASE_CLEANUP_ANALYSIS.md",
    "CLEANUP_QUICK_START.md"
)

foreach ($doc in $dbCleanupDocs) {
    if (Test-Path $doc) {
        Move-Item -Path $doc -Destination $archiveDir -Force
        Write-Host "  ✓ Archived $doc" -ForegroundColor Green
    }
}
Write-Host ""

# Debug/Test PHP Files to Move
Write-Host "Moving debug/test PHP files..." -ForegroundColor Yellow
$debugFiles = @(
    "compare_expiry_display.php",
    "visual_expiry_guide.php",
    "visual_expiry_demo.php",
    "preview_table.php",
    "update_view.php"
)

foreach ($file in $debugFiles) {
    if (Test-Path $file) {
        Move-Item -Path $file -Destination $testFilesDir -Force
        Write-Host "  ✓ Moved $file to debug/test-files/" -ForegroundColor Green
    }
}
Write-Host ""

# Cleanup SQL files (optional - uncomment if you want to move them)
# Write-Host "Moving SQL files..." -ForegroundColor Yellow
# $sqlFiles = @(
#     "cleanup-database.sql"
# )
# foreach ($file in $sqlFiles) {
#     if (Test-Path $file) {
#         Move-Item -Path $file -Destination $debugDir -Force
#         Write-Host "  ✓ Moved $file to debug/" -ForegroundColor Green
#     }
# }
# Write-Host ""

# Summary
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Cleanup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Files organized into:" -ForegroundColor Yellow
Write-Host "  📁 docs/                          - Active documentation" -ForegroundColor White
Write-Host "  📁 docs/archive/                  - Archived documentation" -ForegroundColor White
Write-Host "  📁 debug/test-files/              - Debug and test PHP files" -ForegroundColor White
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "  1. Review files in docs/archive/ and delete if not needed" -ForegroundColor White
Write-Host "  2. Review files in debug/test-files/ and delete if not needed" -ForegroundColor White
Write-Host "  3. Add docs/ and debug/ to .gitignore if desired" -ForegroundColor White
Write-Host ""
