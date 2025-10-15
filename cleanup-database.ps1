# Database Cleanup Script for NORSU Clinic
# This script removes confirmed unused tables and files
# IMPORTANT: Backup your database before running this!

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "   NORSU Clinic Database Cleanup Script" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "WARNING: This will delete files and database tables!" -ForegroundColor Red
Write-Host "Make sure you have a backup before proceeding!" -ForegroundColor Red
Write-Host ""

$confirmation = Read-Host "Have you backed up your database? (yes/no)"
if ($confirmation -ne "yes") {
    Write-Host "Please backup your database first!" -ForegroundColor Yellow
    exit
}

Write-Host ""
Write-Host "What would you like to clean up?" -ForegroundColor Cyan
Write-Host "1. Reviews System (Confirmed Unused)" -ForegroundColor White
Write-Host "2. WebSockets Statistics Table" -ForegroundColor White
Write-Host ""
Write-Host "Note: Options 3-7 have been DISABLED" -ForegroundColor Red
Write-Host "The following systems are REQUIRED and cannot be removed:" -ForegroundColor Yellow
Write-Host "  - Campus/College/Course System" -ForegroundColor Gray
Write-Host "  - Guest System" -ForegroundColor Gray
Write-Host "  - Office System" -ForegroundColor Gray
Write-Host "  - Department System" -ForegroundColor Gray
Write-Host "  - Vaccination System" -ForegroundColor Gray
Write-Host "  - Payment Gateway System" -ForegroundColor Gray
Write-Host ""
Write-Host "8. All Confirmed Unused (Reviews + WebSockets)" -ForegroundColor Yellow
Write-Host "0. Cancel" -ForegroundColor Red
Write-Host ""

$choice = Read-Host "Enter your choice (0, 1, 2, or 8)"

function Remove-ReviewsSystem {
    Write-Host "`nRemoving Reviews System..." -ForegroundColor Yellow
    
    # Remove migration
    if (Test-Path "database\migrations\2021_11_11_130524_create_reviews_table.php") {
        Remove-Item "database\migrations\2021_11_11_130524_create_reviews_table.php" -Force
        Write-Host "  ✓ Removed reviews migration" -ForegroundColor Green
    }
    
    # Remove model
    if (Test-Path "app\Models\Review.php") {
        Remove-Item "app\Models\Review.php" -Force
        Write-Host "  ✓ Removed Review model" -ForegroundColor Green
    }
    
    # Remove controller
    if (Test-Path "app\Http\Controllers\ReviewController.php") {
        Remove-Item "app\Http\Controllers\ReviewController.php" -Force
        Write-Host "  ✓ Removed ReviewController" -ForegroundColor Green
    }
    
    # Remove requests
    if (Test-Path "app\Http\Requests\CreateReviewRequest.php") {
        Remove-Item "app\Http\Requests\CreateReviewRequest.php" -Force
        Write-Host "  ✓ Removed CreateReviewRequest" -ForegroundColor Green
    }
    
    if (Test-Path "app\Http\Requests\UpdateReviewRequest.php") {
        Remove-Item "app\Http\Requests\UpdateReviewRequest.php" -Force
        Write-Host "  ✓ Removed UpdateReviewRequest" -ForegroundColor Green
    }
    
    # Remove views
    if (Test-Path "resources\views\reviews") {
        Remove-Item "resources\views\reviews" -Recurse -Force
        Write-Host "  ✓ Removed reviews views directory" -ForegroundColor Green
    }
    
    Write-Host "`n  Manual Steps Required:" -ForegroundColor Cyan
    Write-Host "  1. Remove review route from routes/patient.php (line 60)" -ForegroundColor Yellow
    Write-Host "  2. Remove review relationships from Patient.php (line 275)" -ForegroundColor Yellow
    Write-Host "  3. Remove review relationships from Doctor.php (line 138)" -ForegroundColor Yellow
    Write-Host "  4. Run: DROP TABLE IF EXISTS reviews;" -ForegroundColor Yellow
}

function Remove-WebSocketsStatistics {
    Write-Host "`nRemoving WebSockets Statistics Table..." -ForegroundColor Yellow
    
    if (Test-Path "database\migrations\0000_00_00_000000_create_websockets_statistics_entries_table.php") {
        Remove-Item "database\migrations\0000_00_00_000000_create_websockets_statistics_entries_table.php" -Force
        Write-Host "  ✓ Removed websockets statistics migration" -ForegroundColor Green
    }
    
    Write-Host "`n  Manual Steps Required:" -ForegroundColor Cyan
    Write-Host "  1. Run: DROP TABLE IF EXISTS websockets_statistics_entries;" -ForegroundColor Yellow
}

function Remove-CampusSystem {
    Write-Host "`nRemoving Campus/College/Course System..." -ForegroundColor Yellow
    
    # Remove migrations
    $campusMigrations = @(
        "database\migrations\2025_03_30_132831_create_campuses_table.php",
        "database\migrations\2025_03_30_132837_create_colleges_table.php",
        "database\migrations\2025_03_30_132847_create_courses_table.php",
        "database\migrations\2025_03_30_132901_create_year_levels_table.php"
    )
    
    foreach ($migration in $campusMigrations) {
        if (Test-Path $migration) {
            Remove-Item $migration -Force
            Write-Host "  ✓ Removed $(Split-Path $migration -Leaf)" -ForegroundColor Green
        }
    }
    
    # Remove models
    $campusModels = @("Campus", "College", "Course", "YearLevel")
    foreach ($model in $campusModels) {
        if (Test-Path "app\Models\$model.php") {
            Remove-Item "app\Models\$model.php" -Force
            Write-Host "  ✓ Removed $model model" -ForegroundColor Green
        }
    }
    
    # Remove seeders
    $campusSeeders = @("CampusSeeder", "CollegeSeeder", "CourseSeeder", "YearLevelSeeder")
    foreach ($seeder in $campusSeeders) {
        if (Test-Path "database\seeders\$seeder.php") {
            Remove-Item "database\seeders\$seeder.php" -Force
            Write-Host "  ✓ Removed $seeder" -ForegroundColor Green
        }
    }
    
    Write-Host "`n  Manual Steps Required:" -ForegroundColor Cyan
    Write-Host "  1. Run SQL:" -ForegroundColor Yellow
    Write-Host "     DROP TABLE IF EXISTS year_levels;" -ForegroundColor Yellow
    Write-Host "     DROP TABLE IF EXISTS courses;" -ForegroundColor Yellow
    Write-Host "     DROP TABLE IF EXISTS colleges;" -ForegroundColor Yellow
    Write-Host "     DROP TABLE IF EXISTS campuses;" -ForegroundColor Yellow
}

function Remove-GuestSystem {
    Write-Host "`nRemoving Guest System..." -ForegroundColor Yellow
    
    if (Test-Path "database\migrations\2025_10_09_175456_create_guests_table.php") {
        Remove-Item "database\migrations\2025_10_09_175456_create_guests_table.php" -Force
        Write-Host "  ✓ Removed guests migration" -ForegroundColor Green
    }
    
    if (Test-Path "app\Models\Guest.php") {
        Remove-Item "app\Models\Guest.php" -Force
        Write-Host "  ✓ Removed Guest model" -ForegroundColor Green
    }
    
    Write-Host "`n  Manual Steps Required:" -ForegroundColor Cyan
    Write-Host "  1. Run: DROP TABLE IF EXISTS guests;" -ForegroundColor Yellow
}

function Remove-OfficeSystem {
    Write-Host "`nRemoving Office System..." -ForegroundColor Yellow
    
    if (Test-Path "database\migrations\2025_10_09_175512_create_offices_table.php") {
        Remove-Item "database\migrations\2025_10_09_175512_create_offices_table.php" -Force
        Write-Host "  ✓ Removed offices migration" -ForegroundColor Green
    }
    
    if (Test-Path "app\Models\Office.php") {
        Remove-Item "app\Models\Office.php" -Force
        Write-Host "  ✓ Removed Office model" -ForegroundColor Green
    }
    
    if (Test-Path "database\seeders\OfficeSeeder.php") {
        Remove-Item "database\seeders\OfficeSeeder.php" -Force
        Write-Host "  ✓ Removed OfficeSeeder" -ForegroundColor Green
    }
    
    Write-Host "`n  Manual Steps Required:" -ForegroundColor Cyan
    Write-Host "  1. Run: DROP TABLE IF EXISTS offices;" -ForegroundColor Yellow
}

function Remove-DepartmentSystem {
    Write-Host "`nRemoving Department System..." -ForegroundColor Yellow
    
    if (Test-Path "database\migrations\2025_10_09_181649_create_departments_table.php") {
        Remove-Item "database\migrations\2025_10_09_181649_create_departments_table.php" -Force
        Write-Host "  ✓ Removed departments migration" -ForegroundColor Green
    }
    
    if (Test-Path "app\Models\Department.php") {
        Remove-Item "app\Models\Department.php" -Force
        Write-Host "  ✓ Removed Department model" -ForegroundColor Green
    }
    
    if (Test-Path "database\seeders\DepartmentSeeder.php") {
        Remove-Item "database\seeders\DepartmentSeeder.php" -Force
        Write-Host "  ✓ Removed DepartmentSeeder" -ForegroundColor Green
    }
    
    Write-Host "`n  Manual Steps Required:" -ForegroundColor Cyan
    Write-Host "  1. Run: DROP TABLE IF EXISTS departments;" -ForegroundColor Yellow
}

function Remove-VaccinationSystem {
    Write-Host "`nRemoving Vaccination System..." -ForegroundColor Yellow
    
    if (Test-Path "database\migrations\2025_03_30_133435_create_vaccinations_table.php") {
        Remove-Item "database\migrations\2025_03_30_133435_create_vaccinations_table.php" -Force
        Write-Host "  ✓ Removed vaccinations migration" -ForegroundColor Green
    }
    
    if (Test-Path "app\Models\Vaccination.php") {
        Remove-Item "app\Models\Vaccination.php" -Force
        Write-Host "  ✓ Removed Vaccination model" -ForegroundColor Green
    }
    
    if (Test-Path "database\seeders\VaccinationSeeder.php") {
        Remove-Item "database\seeders\VaccinationSeeder.php" -Force
        Write-Host "  ✓ Removed VaccinationSeeder" -ForegroundColor Green
    }
    
    Write-Host "`n  Manual Steps Required:" -ForegroundColor Cyan
    Write-Host "  1. Run: DROP TABLE IF EXISTS vaccinations;" -ForegroundColor Yellow
}

# Execute based on choice
switch ($choice) {
    "1" { Remove-ReviewsSystem }
    "2" { Remove-WebSocketsStatistics }
    "3" { 
        Write-Host "`nCampus system is REQUIRED and cannot be removed." -ForegroundColor Red
        Write-Host "This system is used for patient academic tracking." -ForegroundColor Yellow
        exit
    }
    "4" { 
        Write-Host "`nGuest system is REQUIRED and cannot be removed." -ForegroundColor Red
        Write-Host "This system is used for visitor management." -ForegroundColor Yellow
        exit
    }
    "5" { 
        Write-Host "`nOffice system is REQUIRED and cannot be removed." -ForegroundColor Red
        Write-Host "This system is used for office assignments." -ForegroundColor Yellow
        exit
    }
    "6" { 
        Write-Host "`nDepartment system is REQUIRED and cannot be removed." -ForegroundColor Red
        Write-Host "This system is used for department management." -ForegroundColor Yellow
        exit
    }
    "7" { 
        Write-Host "`nVaccination system is REQUIRED and cannot be removed." -ForegroundColor Red
        Write-Host "This system is used for patient vaccination records." -ForegroundColor Yellow
        exit
    }
    "8" {
        Write-Host "`nRemoving all confirmed unused systems..." -ForegroundColor Yellow
        Remove-ReviewsSystem
        Remove-WebSocketsStatistics
    }
    "0" {
        Write-Host "Cleanup cancelled." -ForegroundColor Yellow
        exit
    }
    default {
        Write-Host "Invalid choice! Please select 0, 1, 2, or 8." -ForegroundColor Red
        exit
    }
}

Write-Host "`n==================================================" -ForegroundColor Cyan
Write-Host "After cleanup, run these commands:" -ForegroundColor Cyan
Write-Host "  php artisan config:clear" -ForegroundColor White
Write-Host "  php artisan cache:clear" -ForegroundColor White
Write-Host "  php artisan view:clear" -ForegroundColor White
Write-Host "  php artisan route:clear" -ForegroundColor White
Write-Host "  composer dump-autoload" -ForegroundColor White
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Cleanup completed!" -ForegroundColor Green
