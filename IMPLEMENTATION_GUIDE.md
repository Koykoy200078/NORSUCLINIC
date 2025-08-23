# IMMEDIATE ACTION PLAN - Critical Fixes

## 🚨 URGENT: SQL Injection Fixes (Do This First!)

### 1. Fix Livewire Tables (CRITICAL)

Replace all vulnerable whereRaw calls in these files:

#### File: app/Livewire/AppointmentTable.php

```php
// BEFORE (Line 148) - VULNERABLE:
$q->whereRaw("TRIM(CONCAT(first_name,' ',last_name,' ')) like '%{$direction}%'");

// AFTER (SECURE):
$q->whereRaw("TRIM(CONCAT(first_name, ?, last_name, ?)) LIKE ?", [' ', ' ', "%{$direction}%"]);
```

#### File: app/Livewire/DoctorScheduleTable.php

#### File: app/Livewire/VisitTable.php

#### File: app/Livewire/TransactionTable.php

#### File: app/Livewire/PatientAppointmentTable.php

#### File: app/Livewire/StaffTable.php

#### File: app/Livewire/PatientVisitTable.php

#### File: app/Livewire/PatientTable.php

Apply the same fix to all these files.

### 2. Enhanced XSS Protection

#### Replace app/Http/Middleware/XSS.php:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use HTMLPurifier;
use HTMLPurifier_Config;

class XSS
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->route()->getName() == 'cms.update') {
            return $next($request);
        }

        $input = $request->all();
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);

        array_walk_recursive($input, function (&$input) use ($purifier) {
            if (is_string($input)) {
                $input = $purifier->purify($input);
            }
        });

        $request->merge($input);
        return $next($request);
    }
}
```

## 🚀 PERFORMANCE: Database Optimization

### 1. Create Performance Migration

Run this command:

```bash
php artisan make:migration add_performance_indexes_to_tables
```

#### Migration Content:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email'], 'idx_users_email');
            $table->index(['status'], 'idx_users_status');
            $table->index(['type'], 'idx_users_type');
            $table->index(['campus_id', 'college_id'], 'idx_users_campus_college');
        });

        // Appointments table indexes
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['patient_id'], 'idx_appointments_patient');
            $table->index(['doctor_id'], 'idx_appointments_doctor');
            $table->index(['date'], 'idx_appointments_date');
            $table->index(['status'], 'idx_appointments_status');
        });

        // Prescriptions table indexes
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->index(['patient_id'], 'idx_prescriptions_patient');
            $table->index(['doctor_id'], 'idx_prescriptions_doctor');
            $table->index(['appointment_id'], 'idx_prescriptions_appointment');
        });

        // Medicine related indexes
        Schema::table('medicines', function (Blueprint $table) {
            $table->index(['category_id'], 'idx_medicines_category');
            $table->index(['name'], 'idx_medicines_name');
        });

        Schema::table('medicine_bills', function (Blueprint $table) {
            $table->index(['patient_id'], 'idx_medicine_bills_patient');
            $table->index(['doctor_id'], 'idx_medicine_bills_doctor');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_status');
            $table->dropIndex('idx_users_type');
            $table->dropIndex('idx_users_campus_college');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('idx_appointments_patient');
            $table->dropIndex('idx_appointments_doctor');
            $table->dropIndex('idx_appointments_date');
            $table->dropIndex('idx_appointments_status');
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropIndex('idx_prescriptions_patient');
            $table->dropIndex('idx_prescriptions_doctor');
            $table->dropIndex('idx_prescriptions_appointment');
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropIndex('idx_medicines_category');
            $table->dropIndex('idx_medicines_name');
        });

        Schema::table('medicine_bills', function (Blueprint $table) {
            $table->dropIndex('idx_medicine_bills_patient');
            $table->dropIndex('idx_medicine_bills_doctor');
        });
    }
};
```

### 2. Create Settings Cache Service

Create: app/Services/SettingsService.php

```php
<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    const CACHE_KEY = 'application_settings';
    const CACHE_TTL = 3600; // 1 hour

    public static function get($key = null)
    {
        $settings = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        return $key ? ($settings[$key] ?? null) : $settings;
    }

    public static function set($key, $value)
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        self::clearCache();
    }

    public static function clearCache()
    {
        Cache::forget(self::CACHE_KEY);
    }
}
```

### 3. Update Helper Functions

#### Update app/helpers.php:

```php
// Replace existing getSettingValue function
if (!function_exists('getSettingValue')) {
    function getSettingValue($key)
    {
        return \App\Services\SettingsService::get($key);
    }
}

if (!function_exists('getAppName')) {
    function getAppName()
    {
        return \App\Services\SettingsService::get('clinic_name') ?? config('app.name');
    }
}

if (!function_exists('getAppLogo')) {
    function getAppLogo()
    {
        return \App\Services\SettingsService::get('logo') ?? 'default-logo.png';
    }
}
```

## 🔧 CODE QUALITY: Service Layer Implementation

### 1. Create Prescription Service

Create: app/Services/PrescriptionService.php

```php
<?php

namespace App\Services;

use App\Models\Prescription;
use App\Models\Medicine;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PrescriptionService
{
    public function createPrescription(array $data): Prescription
    {
        return DB::transaction(function () use ($data) {
            $prescription = Prescription::create([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'appointment_id' => $data['appointment_id'],
                'health_checkup' => $data['health_checkup'] ?? null,
                'status' => $data['status'] ?? 1,
            ]);

            if (isset($data['medicines']) && is_array($data['medicines'])) {
                $this->attachMedicines($prescription, $data['medicines']);
            }

            return $prescription->load(['patient.user', 'doctor.user', 'medicines']);
        });
    }

    public function updatePrescription(Prescription $prescription, array $data): Prescription
    {
        return DB::transaction(function () use ($prescription, $data) {
            $prescription->update([
                'health_checkup' => $data['health_checkup'] ?? $prescription->health_checkup,
                'status' => $data['status'] ?? $prescription->status,
            ]);

            if (isset($data['medicines']) && is_array($data['medicines'])) {
                $prescription->medicines()->detach();
                $this->attachMedicines($prescription, $data['medicines']);
            }

            return $prescription->fresh(['patient.user', 'doctor.user', 'medicines']);
        });
    }

    private function attachMedicines(Prescription $prescription, array $medicines): void
    {
        foreach ($medicines as $medicine) {
            $prescription->medicines()->attach($medicine['medicine_id'], [
                'dosage' => $medicine['dosage'],
                'day' => $medicine['day'],
                'time' => $medicine['time'],
                'comment' => $medicine['comment'] ?? null,
            ]);
        }
    }

    public function generatePDF(Prescription $prescription)
    {
        $prescription->load(['patient.user', 'doctor.user', 'medicines']);

        $pdf = Pdf::loadView('prescriptions.pdf', compact('prescription'));
        return $pdf->download("prescription-{$prescription->id}.pdf");
    }
}
```

### 2. Create Patient Service

Create: app/Services/PatientService.php

```php
<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientService
{
    public function createPatient(array $data): Patient
    {
        return DB::transaction(function () use ($data) {
            // Create user first
            $user = User::create([
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'email' => $data['email'] ?? null,
                'contact' => $data['contact'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_no' => $data['emergency_contact_no'] ?? null,
                'dob' => $data['dob'] ?? null,
                'gender' => $data['gender'],
                'blood_type' => $data['blood_type'] ?? null,
                'password' => isset($data['password']) ? Hash::make($data['password']) : null,
                'status' => $data['status'] ?? true,
                'type' => User::PATIENT,
                'campus_id' => $data['campus_id'] ?? null,
                'college_id' => $data['college_id'] ?? null,
                'course_id' => $data['course_id'] ?? null,
                'year_level_id' => $data['year_level_id'] ?? null,
                'vaccination_id' => $data['vaccination_id'] ?? null,
            ]);

            // Assign patient role
            $user->assignRole('patient');

            // Create patient
            $patient = Patient::create([
                'user_id' => $user->id,
                'patient_unique_id' => $this->generateUniqueId(),
            ]);

            // Handle address if provided
            if (isset($data['address'])) {
                $patient->address()->create($data['address']);
            }

            return $patient->load('user');
        });
    }

    private function generateUniqueId(): string
    {
        do {
            $id = 'PT' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Patient::where('patient_unique_id', $id)->exists());

        return $id;
    }

    public function getPatientWithHistory(int $patientId)
    {
        return Patient::with([
            'user',
            'appointments.doctor.user',
            'prescriptions.medicines',
            'medicineBills',
            'visits'
        ])->findOrFail($patientId);
    }
}
```

## 🎨 FRONTEND: Component Refactoring

### 1. Create Action Buttons Component

Create: resources/views/components/crud/action-buttons.blade.php

```php
@props([
    'model',
    'routes' => [],
    'permissions' => [],
    'size' => 'sm'
])

<div class="d-flex align-items-center gap-2">
    @if(isset($routes['show']) && (!isset($permissions['show']) || auth()->user()->can($permissions['show'])))
        <a href="{{ route($routes['show'], $model) }}"
           title="{{ __('messages.common.view') }}"
           class="btn btn-{{ $size }} btn-outline-info">
            <i class="fas fa-eye"></i>
        </a>
    @endif

    @if(isset($routes['edit']) && (!isset($permissions['edit']) || auth()->user()->can($permissions['edit'])))
        <a href="{{ route($routes['edit'], $model) }}"
           title="{{ __('messages.common.edit') }}"
           class="btn btn-{{ $size }} btn-outline-primary">
            <i class="fas fa-edit"></i>
        </a>
    @endif

    @if(isset($routes['delete']) && (!isset($permissions['delete']) || auth()->user()->can($permissions['delete'])))
        <button type="button"
                title="{{ __('messages.common.delete') }}"
                class="btn btn-{{ $size }} btn-outline-danger delete-btn"
                data-url="{{ route($routes['delete'], $model) }}"
                data-name="{{ $model->name ?? $model->id }}">
            <i class="fas fa-trash"></i>
        </button>
    @endif
</div>
```

### 2. Usage in Blade Templates

Replace action button code in all your blade files:

```php
{{-- OLD CODE --}}
<div class="d-flex align-items-center">
    <a href="{{route($showRoute,$row->id)}}" title="<?php echo __('messages.common.view') ?>"
       class="btn px-1 text-info fs-3">
        <i class="fas fa-eye"></i>
    </a>
    <!-- ... more buttons -->
</div>

{{-- NEW CODE --}}
<x-crud.action-buttons
    :model="$row"
    :routes="[
        'show' => $showRoute,
        'edit' => $editRoute,
        'delete' => $deleteRoute
    ]"
    :permissions="[
        'show' => 'view_prescription',
        'edit' => 'edit_prescription',
        'delete' => 'delete_prescription'
    ]" />
```

## 📋 IMPLEMENTATION CHECKLIST

### Phase 1: Critical Security (Week 1)

-   [ ] Fix all SQL injection vulnerabilities in Livewire tables
-   [ ] Update XSS middleware with HTMLPurifier
-   [ ] Add CSRF protection to all forms
-   [ ] Implement proper authorization checks

### Phase 2: Performance (Week 2)

-   [ ] Run database migration for indexes
-   [ ] Implement settings caching service
-   [ ] Add eager loading to all controllers
-   [ ] Optimize database queries

### Phase 3: Code Quality (Week 3)

-   [ ] Create service classes (Prescription, Patient, Medicine)
-   [ ] Refactor controllers to use services
-   [ ] Create reusable Blade components
-   [ ] Implement proper error handling

### Phase 4: Frontend (Week 4)

-   [ ] Optimize asset compilation
-   [ ] Create component library
-   [ ] Implement lazy loading
-   [ ] Add proper JavaScript error handling

## 🚀 DEPLOYMENT COMMANDS

Run these commands in order:

```bash
# 1. Install HTMLPurifier for XSS protection
composer require ezyang/htmlpurifier

# 2. Create and run performance migration
php artisan make:migration add_performance_indexes_to_tables
php artisan migrate

# 3. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 4. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Compile assets
npm run production
```

This plan addresses your main concerns: security vulnerabilities, performance issues, code duplication, and frontend improvements. Start with Phase 1 (security) as it's the most critical.
