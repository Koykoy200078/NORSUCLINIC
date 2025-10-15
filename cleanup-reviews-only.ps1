# SAFE Database Cleanup Script for NORSU Clinic
# This script ONLY removes the confirmed unused Reviews System
# Systems 3-8 are REQUIRED and protected

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "   NORSU Clinic - Reviews System Cleanup" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "This script will remove the REVIEWS SYSTEM ONLY" -ForegroundColor Yellow
Write-Host ""
Write-Host "⚠️  PROTECTED SYSTEMS (Will NOT be removed):" -ForegroundColor Green
Write-Host "  ✓ Campus/College/Course System" -ForegroundColor Gray
Write-Host "  ✓ Guest System" -ForegroundColor Gray
Write-Host "  ✓ Office System" -ForegroundColor Gray
Write-Host "  ✓ Department System" -ForegroundColor Gray
Write-Host "  ✓ Vaccination System" -ForegroundColor Gray
Write-Host "  ✓ Payment Gateway System" -ForegroundColor Gray
Write-Host ""
Write-Host "WARNING: This will delete files!" -ForegroundColor Red
Write-Host "Make sure you have a backup before proceeding!" -ForegroundColor Red
Write-Host ""

$confirmation = Read-Host "Have you backed up your database? (yes/no)"
if ($confirmation -ne "yes") {
    Write-Host "Please backup your database first!" -ForegroundColor Yellow
    exit
}

Write-Host ""
Write-Host "What would you like to clean up?" -ForegroundColor Cyan
Write-Host "1. Remove Reviews System (SAFE)" -ForegroundColor Green
Write-Host "2. Remove WebSockets Statistics (Verify first)" -ForegroundColor Yellow
Write-Host "3. Show Protected Systems" -ForegroundColor White
Write-Host "0. Cancel" -ForegroundColor Red
Write-Host ""

$choice = Read-Host "Enter your choice (0-3)"

function Remove-ReviewsSystem {
    Write-Host "`nRemoving Reviews System..." -ForegroundColor Yellow
    Write-Host "This is the ONLY confirmed unused system." -ForegroundColor Cyan
    Write-Host ""
    
    $filesRemoved = 0
    
    # Remove migration
    if (Test-Path "database\migrations\2021_11_11_130524_create_reviews_table.php") {
        Remove-Item "database\migrations\2021_11_11_130524_create_reviews_table.php" -Force
        Write-Host "  ✓ Removed reviews migration" -ForegroundColor Green
        $filesRemoved++
    }
    
    # Remove model
    if (Test-Path "app\Models\Review.php") {
        Remove-Item "app\Models\Review.php" -Force
        Write-Host "  ✓ Removed Review model" -ForegroundColor Green
        $filesRemoved++
    }
    
    # Remove controller
    if (Test-Path "app\Http\Controllers\ReviewController.php") {
        Remove-Item "app\Http\Controllers\ReviewController.php" -Force
        Write-Host "  ✓ Removed ReviewController" -ForegroundColor Green
        $filesRemoved++
    }
    
    # Remove requests
    if (Test-Path "app\Http\Requests\CreateReviewRequest.php") {
        Remove-Item "app\Http\Requests\CreateReviewRequest.php" -Force
        Write-Host "  ✓ Removed CreateReviewRequest" -ForegroundColor Green
        $filesRemoved++
    }
    
    if (Test-Path "app\Http\Requests\UpdateReviewRequest.php") {
        Remove-Item "app\Http\Requests\UpdateReviewRequest.php" -Force
        Write-Host "  ✓ Removed UpdateReviewRequest" -ForegroundColor Green
        $filesRemoved++
    }
    
    # Remove views directory
    if (Test-Path "resources\views\reviews") {
        Remove-Item "resources\views\reviews" -Recurse -Force
        Write-Host "  ✓ Removed reviews views directory" -ForegroundColor Green
        $filesRemoved++
    }
    
    Write-Host ""
    Write-Host "Files removed: $filesRemoved" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "📋 Manual Steps Required:" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "1. Drop the reviews table:" -ForegroundColor White
    Write-Host "   DROP TABLE IF EXISTS reviews;" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "2. Remove review routes from routes/patient.php (line 60)" -ForegroundColor White
    Write-Host ""
    Write-Host "3. Remove review relationships from models:" -ForegroundColor White
    Write-Host "   - app/Models/Patient.php (line 275)" -ForegroundColor Cyan
    Write-Host "   - app/Models/Doctor.php (line 138)" -ForegroundColor Cyan
}

function Remove-WebSocketsStatistics {
    Write-Host "`n⚠️  WebSockets Statistics Removal" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Before removing, verify you're NOT using:" -ForegroundColor Red
    Write-Host "  - Laravel WebSockets dashboard" -ForegroundColor White
    Write-Host "  - WebSocket statistics monitoring" -ForegroundColor White
    Write-Host ""
    
    $confirm = Read-Host "Are you SURE you want to remove this? (yes/no)"
    if ($confirm -ne "yes") {
        Write-Host "Cancelled." -ForegroundColor Yellow
        return
    }
    
    Write-Host "`nRemoving WebSockets Statistics..." -ForegroundColor Yellow
    
    if (Test-Path "database\migrations\0000_00_00_000000_create_websockets_statistics_entries_table.php") {
        Remove-Item "database\migrations\0000_00_00_000000_create_websockets_statistics_entries_table.php" -Force
        Write-Host "  ✓ Removed websockets_statistics migration" -ForegroundColor Green
    }
    
    Write-Host ""
    Write-Host "📋 Manual Step Required:" -ForegroundColor Yellow
    Write-Host "   DROP TABLE IF EXISTS websockets_statistics_entries;" -ForegroundColor Cyan
}

function Show-ProtectedSystems {
    Write-Host ""
    Write-Host "==================================================" -ForegroundColor Cyan
    Write-Host "   PROTECTED SYSTEMS (CANNOT BE REMOVED)" -ForegroundColor Yellow
    Write-Host "==================================================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "3. Campus/College/Course System" -ForegroundColor Green
    Write-Host "   Tables: campuses, colleges, courses, year_levels" -ForegroundColor Gray
    Write-Host "   Purpose: Patient academic information tracking" -ForegroundColor Gray
    Write-Host ""
    Write-Host "4. Guest System" -ForegroundColor Green
    Write-Host "   Tables: guests" -ForegroundColor Gray
    Write-Host "   Purpose: Visitor management" -ForegroundColor Gray
    Write-Host ""
    Write-Host "5. Office System" -ForegroundColor Green
    Write-Host "   Tables: offices" -ForegroundColor Gray
    Write-Host "   Purpose: Office assignments" -ForegroundColor Gray
    Write-Host ""
    Write-Host "6. Department System" -ForegroundColor Green
    Write-Host "   Tables: departments" -ForegroundColor Gray
    Write-Host "   Purpose: Department management" -ForegroundColor Gray
    Write-Host ""
    Write-Host "7. Vaccination System" -ForegroundColor Green
    Write-Host "   Tables: vaccinations" -ForegroundColor Gray
    Write-Host "   Purpose: Patient vaccination records" -ForegroundColor Gray
    Write-Host ""
    Write-Host "8. Payment Gateway System" -ForegroundColor Green
    Write-Host "   Tables: payment_gateways" -ForegroundColor Gray
    Write-Host "   Purpose: Online payment processing" -ForegroundColor Gray
    Write-Host ""
    Write-Host "These systems are ACTIVELY USED and must be kept!" -ForegroundColor Red
    Write-Host "==================================================" -ForegroundColor Cyan
    Write-Host ""
    Read-Host "Press Enter to continue"
    return
}

# Execute based on choice
switch ($choice) {
    "1" { Remove-ReviewsSystem }
    "2" { Remove-WebSocketsStatistics }
    "3" { 
        Show-ProtectedSystems
        # Return to menu
        & $PSCommandPath
        exit
    }
    "0" {
        Write-Host "Cleanup cancelled." -ForegroundColor Yellow
        exit
    }
    default {
        Write-Host "Invalid choice!" -ForegroundColor Red
        exit
    }
}

Write-Host ""
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "After cleanup, run these commands:" -ForegroundColor Yellow
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "  php artisan config:clear" -ForegroundColor White
Write-Host "  php artisan cache:clear" -ForegroundColor White
Write-Host "  php artisan view:clear" -ForegroundColor White
Write-Host "  php artisan route:clear" -ForegroundColor White
Write-Host "  composer dump-autoload" -ForegroundColor White
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "✓ Cleanup completed!" -ForegroundColor Green
Write-Host ""
