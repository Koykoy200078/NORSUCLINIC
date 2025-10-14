# PowerShell Script to Rename Appointment Files to Patient Queue
# Run this script from the project root directory

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "APPOINTMENT → PATIENT QUEUE CONVERSION" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Function to rename file and update its contents
function Rename-AndUpdateFile {
    param (
        [string]$OldPath,
        [string]$NewPath,
        [string]$Description
    )
    
    if (Test-Path $OldPath) {
        Write-Host "✓ Renaming: $Description" -ForegroundColor Green
        Write-Host "  From: $OldPath" -ForegroundColor Gray
        Write-Host "  To:   $NewPath" -ForegroundColor Gray
        
        # Read content
        $content = Get-Content $OldPath -Raw
        
        # Replace class names and references
        $content = $content -replace 'class Appointment\b', 'class PatientQueue'
        $content = $content -replace 'Appointment::', 'PatientQueue::'
        $content = $content -replace 'use App\\Models\\Appointment;', 'use App\Models\PatientQueue;'
        $content = $content -replace 'protected \$model = Appointment::class;', 'protected $model = PatientQueue::class;'
        $content = $content -replace "tableName = 'appointments'", "tableName = 'patient_queues'"
        $content = $content -replace 'appointments\.', 'patient_queues.'
        $content = $content -replace 'appointmentsPage', 'patientQueuesPage'
        
        # Write to new file
        Set-Content -Path $NewPath -Value $content
        
        # Remove old file if different from new
        if ($OldPath -ne $NewPath) {
            Remove-Item $OldPath -Force
        }
        
        Write-Host "  ✓ Done`n" -ForegroundColor Green
        return $true
    } else {
        Write-Host "✗ File not found: $OldPath" -ForegroundColor Yellow
        return $false
    }
}

# Create backup first
Write-Host "Creating backup..." -ForegroundColor Yellow
$backupDir = ".\backup_appointment_files_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null

# Backup critical files
$filesToBackup = @(
    ".\app\Models\Appointment.php",
    ".\app\Repositories\AppointmentRepository.php",
    ".\app\Http\Controllers\AppointmentController.php",
    ".\app\Http\Controllers\PatientAppointmentController.php"
)

foreach ($file in $filesToBackup) {
    if (Test-Path $file) {
        $relativePath = $file.Replace(".\", "")
        $backupPath = Join-Path $backupDir $relativePath
        $backupFolder = Split-Path -Parent $backupPath
        New-Item -ItemType Directory -Path $backupFolder -Force | Out-Null
        Copy-Item $file $backupPath -Force
    }
}
Write-Host "✓ Backup created at: $backupDir`n" -ForegroundColor Green

# Step 1: Rename Model (if not done yet)
Write-Host "[1] MODEL FILES" -ForegroundColor Cyan
Write-Host "----------------------------------------"
if (Test-Path ".\app\Models\Appointment.php") {
    Rename-AndUpdateFile -OldPath ".\app\Models\Appointment.php" `
                         -NewPath ".\app\Models\PatientQueue.php" `
                         -Description "Appointment Model → PatientQueue Model"
} else {
    Write-Host "✓ Model already renamed" -ForegroundColor Green
}

# Step 2: Rename Repositories
Write-Host "`n[2] REPOSITORY FILES" -ForegroundColor Cyan
Write-Host "----------------------------------------"
Rename-AndUpdateFile -OldPath ".\app\Repositories\AppointmentRepository.php" `
                     -NewPath ".\app\Repositories\PatientQueueRepository.php" `
                     -Description "Appointment Repository → PatientQueue Repository"

# Step 3: Rename Controllers
Write-Host "`n[3] CONTROLLER FILES" -ForegroundColor Cyan
Write-Host "----------------------------------------"
Rename-AndUpdateFile -OldPath ".\app\Http\Controllers\AppointmentController.php" `
                     -NewPath ".\app\Http\Controllers\PatientQueueController.php" `
                     -Description "Appointment Controller → PatientQueue Controller"

# Step 4: Rename Request Validation Classes
Write-Host "`n[4] REQUEST VALIDATION FILES" -ForegroundColor Cyan
Write-Host "----------------------------------------"
Rename-AndUpdateFile -OldPath ".\app\Http\Requests\CreateAppointmentRequest.php" `
                     -NewPath ".\app\Http\Requests\CreatePatientQueueRequest.php" `
                     -Description "CreateAppointment Request → CreatePatientQueue Request"

Rename-AndUpdateFile -OldPath ".\app\Http\Requests\UpdateAppointmentRequest.php" `
                     -NewPath ".\app\Http\Requests\UpdatePatientQueueRequest.php" `
                     -Description "UpdateAppointment Request → UpdatePatientQueue Request"

# Step 5: Rename Views Directory
Write-Host "`n[5] VIEW DIRECTORIES" -ForegroundColor Cyan
Write-Host "----------------------------------------"
if (Test-Path ".\resources\views\appointments") {
    Write-Host "✓ Renaming appointments views directory..." -ForegroundColor Green
    if (Test-Path ".\resources\views\patient_queues") {
        Write-Host "  ! patient_queues directory already exists, skipping" -ForegroundColor Yellow
    } else {
        Rename-Item ".\resources\views\appointments" "patient_queues"
        Write-Host "  ✓ Done`n" -ForegroundColor Green
    }
} else {
    Write-Host "✓ Views directory already renamed or doesn't exist" -ForegroundColor Green
}

if (Test-Path ".\resources\views\doctor_appointment") {
    Write-Host "✓ Renaming doctor_appointment views directory..." -ForegroundColor Green
    if (Test-Path ".\resources\views\doctor_queue") {
        Write-Host "  ! doctor_queue directory already exists, skipping" -ForegroundColor Yellow
    } else {
        Rename-Item ".\resources\views\doctor_appointment" "doctor_queue"
        Write-Host "  ✓ Done`n" -ForegroundColor Green
    }
} else {
    Write-Host "✓ Doctor views directory already renamed or doesn't exist" -ForegroundColor Green
}

# Step 6: Update Factory
Write-Host "`n[6] FACTORY FILES" -ForegroundColor Cyan
Write-Host "----------------------------------------"
if (Test-Path ".\database\factories\AppointmentFactory.php") {
    Rename-AndUpdateFile -OldPath ".\database\factories\AppointmentFactory.php" `
                         -NewPath ".\database\factories\PatientQueueFactory.php" `
                         -Description "Appointment Factory → PatientQueue Factory"
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "FILE RENAMING COMPLETE!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "NEXT STEPS:" -ForegroundColor Yellow
Write-Host "1. Update route files (web.php, staff.php, doctor.php)" -ForegroundColor White
Write-Host "2. Update menu.blade.php navigation" -ForegroundColor White
Write-Host "3. Update language files" -ForegroundColor White
Write-Host "4. Run migration: php artisan migrate" -ForegroundColor White
Write-Host "5. Clear caches: php artisan cache:clear" -ForegroundColor White
Write-Host "6. Update all blade view references" -ForegroundColor White
Write-Host ""
Write-Host "Backup location: $backupDir" -ForegroundColor Cyan
Write-Host ""
