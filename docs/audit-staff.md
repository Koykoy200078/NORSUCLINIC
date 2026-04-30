# NORSUCLINIC Staff Module — Comprehensive Audit

> **Scope:** Deep analysis of the Staff module, with special focus on the `Role Designation` and `Assigned Station` fields. Covers routes, controllers, requests, models, repositories, database schema, seeders, views, and Livewire tables.

---

## 1. Executive Summary

The Staff module manages clinic staff (receptionists, nurses, pharmacists, etc.). Staff are stored as `users` rows with `type = 3` (`User::STAFF`). Extended staff metadata (role designation, assigned station, shift schedule) is stored in the **one-to-one** `staff_profiles` table.

| Concern | Finding |
|---------|---------|
| **Role Designation** | FK to `staff_designations` (6 seeded values). |
| **Assigned Station** | FK to `clinic_stations` (7 seeded values). |
| **Data integrity** | Both fields are `required` in form requests and guarded by `exists:...` rules. |
| **Bugs / Issues** | Dead-code, N+1 risk, and repository/model architectural inconsistencies remain (see §7). |

---

## 2. High-Level Architecture

```
┌─────────────┐      resource('staffs')      ┌─────────────────┐
│  Browser    │ ───────────────────────────► │ StaffController │
└─────────────┘                              └─────────────────┘
                                                      │
           ┌────────────────────────────────────────┼────────────────────────┐
           ▼                                        ▼                        ▼
   ┌───────────────┐                      ┌──────────────────┐      ┌──────────────────┐
   │ Create/Update │                      │ StaffRepository  │      │ StaffTable       │
   │  Requests     │                      │   (store/update) │      │ (Livewire)       │
   └───────────────┘                      └──────────────────┘      └──────────────────┘
           │                                        │                        │
           ▼                                        ▼                        ▼
   ┌───────────────┐                      ┌─────────┐                 ┌─────────┐
   │ role_designation_id                  │  users  │                 │  users  │
   │ assigned_station_id                  │ (type=3)│                 │ (type=3)│
   │ shift_schedule                       └────┬────┘                 └─────────┘
   │ (staff_profiles)                          │
   └───────────────────────────────────────────┤
                                               │
                                      ┌────────┴────────┐
                                      │ staff_profiles  │
                                      │  role_designation_id  ──► staff_designations
                                      │  assigned_station_id  ──► clinic_stations
                                      │  shift_schedule
                                      └─────────────────┘
```

---

## 3. Database Schema

### 3.1 staff_designations (`database/migrations/2026_04_16_191000_normalize_university_clinic_profiles_schema.php:42-48`)
```sql
CREATE TABLE staff_designations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(60) UNIQUE,
    name VARCHAR(120) UNIQUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```
**Seeded values** (`database/seeders/StaffDesignationSeeder.php:12-18`):
| code | name |
|------|------|
| `clinic_head` | Head of University Health Services |
| `nurse` | Registered Nurse |
| `pharmacist` | Pharmacist |
| `triage_officer` | Triage Officer |
| `clinic_staff` | Clinic Staff / Secretary |
| `records_officer` | Medical Records Officer |

### 3.2 clinic_stations (`database/migrations/...:51-58`)
```sql
CREATE TABLE clinic_stations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(60) UNIQUE,
    name VARCHAR(120) UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```
**Seeded values** (`database/seeders/ClinicStationSeeder.php:12-47`):
| code | name | description |
|------|------|-------------|
| `front_desk` | Front Desk & Receiving | Patient reception, queueing, and NORSU ID verification. |
| `triage_area` | Triage Area | Initial patient assessment, vitals checking (BP, Temp), and basic history taking. |
| `medical_consultation` | Medical Consultation Room | Private physician consultation and physical examination area. |
| `pharmacy` | Clinic Pharmacy | Medicine dispensing, medication counseling, and FEFO inventory management. |
| `records_area` | Records Area | Secure filing of physical medical records and digital data encoding. |
| `observation_room` | Observation & Recovery Room | Rest area with beds for students resting from dysmenorrhea, dizziness, or minor PE injuries. |
| `isolation_room` | Isolation Room | Holding area for patients with suspected contagious illnesses (e.g., flu, chickenpox) pending campus exit. |

### 3.3 staff_profiles (`database/migrations/...:60-72`)
```sql
CREATE TABLE staff_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED UNIQUE,
    role_designation_id BIGINT UNSIGNED NULL,
    assigned_station_id BIGINT UNSIGNED NULL,
    shift_schedule TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_designation_id) REFERENCES staff_designations(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_station_id) REFERENCES clinic_stations(id) ON DELETE SET NULL
);
```
> **Note:** `role_designation_id` and `assigned_station_id` are **nullable** at the DB level, but the application enforces them as `required` in form requests.

---

## 4. Request Flow (CRUD)

### 4.1 Routes (`routes/web.php:207-211`)
```php
Route::middleware('permission:manage_staff')->group(function () {
    Route::resource('staffs', StaffController::class);
    Route::post('staffs/{user}/reset-password', [StaffController::class, 'resetPassword'])
        ->name('staffs.reset.password');
});
```

### 4.2 Controller — `StaffController` (`app/Http/Controllers/StaffController.php`)
| Method | Purpose |
|--------|---------|
| `index()` | Returns `staffs.index` view (renders `<livewire:staff-table/>`). |
| `create()` | Passes `$roles`, `$defaultRoleId`, `$staffDesignations`, `$clinicStations` to `staffs.create`. |
| `store(CreateStaffRequest $request)` | Delegates to `StaffRepository::store()`. |
| `show(User $staff)` | Fetches `User::whereType(User::STAFF)->findOrFail($staff->id)` then renders `staffs.show`. |
| `edit(User $staff)` | Same data as `create()` plus the existing `$staff` model. |
| `update(UpdateStaffRequest $request, User $staff)` | Delegates to `StaffRepository::update()`. |
| `destroy(User $staff)` | Calls `$staff->delete()` **directly on User**, bypassing repository. |

### 4.3 Form Requests

**`CreateStaffRequest` (`app/Http/Requests/CreateStaffRequest.php:20-35`)**
```php
'role_designation_id' => 'required|exists:staff_designations,id',
'assigned_station_id' => 'required|exists:clinic_stations,id',
'shift_schedule'      => 'required|string',
```

**`UpdateStaffRequest` (`app/Http/Requests/UpdateStaffRequest.php:20-35`)**
> Same validation rules as `CreateStaffRequest`, with added `ignore` clauses on `email`, `employee_id`, and `contact` for uniqueness.

### 4.4 Repository — `StaffRepository` (`app/Repositories/StaffRepository.php:59-127`)

**`store(array $input): bool`**
1. Wraps in `DB::transaction`.
2. Sets `email` to lowercase via `setEmailLowerCase()` helper.
3. Hashes password.
4. Sets `type = User::STAFF` (constant `3`).
5. Sets `email_verified_at` to Manila timezone.
6. **Extracts staff-profile fields:** `Arr::only($input, ['role_designation_id', 'assigned_station_id', 'shift_schedule'])`.
7. **Creates `User`** from everything else: `Arr::except($input, ['role_designation_id', ...])`.
8. **Creates/updates `staffProfile`** via `updateOrCreate(['user_id' => $staff->id], $staffProfileInput)`.
9. Assigns Spatie role if provided.
10. Handles profile image upload to `Staff::PROFILE` media collection.

**`update(array $input, int $id): bool`**
> Same pattern as `store()`, but uses `$staff->update()` and `$staff->syncRoles()` instead of `assignRole()`.

---

## 5. Models & Relationships

### 5.1 `User` (`app/Models/User.php:174-183`)
```php
const STAFF = 3;
const TYPE = [
    self::ADMIN   => 'Admin',
    self::DOCTOR  => 'Doctor',
    self::PATIENT => 'Patient',
    self::STAFF   => 'Staff',
];
```
**Relevant relations:**
```php
public function staffProfile(): HasOne
{
    return $this->hasOne(StaffProfile::class, 'user_id');
}
```

### 5.2 `StaffProfile` (`app/Models/StaffProfile.php`)
```php
protected $fillable = [
    'user_id',
    'role_designation_id',
    'assigned_station_id',
    'shift_schedule',
];

public function roleDesignation(): BelongsTo
{
    return $this->belongsTo(StaffDesignation::class, 'role_designation_id');
}

public function assignedStation(): BelongsTo
{
    return $this->belongsTo(ClinicStation::class, 'assigned_station_id');
}
```

### 5.3 `StaffDesignation` (`app/Models/StaffDesignation.php`)
```php
protected $fillable = ['code', 'name'];
public function staffProfiles(): HasMany  // reverse relation
```

### 5.4 `ClinicStation` (`app/Models/ClinicStation.php`)
```php
protected $fillable = ['code', 'name', 'description'];
public function staffProfiles(): HasMany  // reverse relation
```

### 5.5 Legacy `Staff` model (`app/Models/Staff.php`)
> **⚠️ Architectural oddity:** A dedicated `Staff` model exists with its own `$table = 'staff'`, but the application **never actually uses this table**. All staff CRUD operates on the `users` table via `User` models with `type = 3`. The `StaffRepository::model()` returns `Staff::class`, yet `store()` and `update()` instantiate `User` directly. This is dead/abandoned code.

---

## 6. Views & Blade Components

### 6.1 Create / Edit Form (`resources/views/staffs/fields.blade.php:87-105`)

**Role Designation dropdown:**
```blade
{{ Form::label('role_designation_id', __('Role Designation').':', ['class' => 'form-label required']) }}
{{ Form::select('role_designation_id',
    $staffDesignations,
    old('role_designation_id', isset($staff) ? optional($staff->staffProfile)->role_designation_id : null),
    ['class' => 'form-select io-select2', 'data-control' => 'select2',
     'placeholder' => __('Select Role Designation'), 'required']) }}
```
- Data source: `StaffDesignation::pluck('name', 'id')` (controller)
- Value persistence: falls back to old input, then existing `staffProfile->role_designation_id`
- Prepopulation on edit works through `optional($staff->staffProfile)`

**Assigned Station dropdown:**
```blade
{{ Form::label('assigned_station_id', __('Assigned Station').':', ['class' => 'form-label required']) }}
{{ Form::select('assigned_station_id',
    $clinicStations,
    old('assigned_station_id', isset($staff) ? optional($staff->staffProfile)->assigned_station_id : null),
    ['class' => 'form-select io-select2', 'data-control' => 'select2',
     'placeholder' => __('Select Assigned Station'), 'required']) }}
```

**Shift Schedule textarea:**
```blade
{{ Form::textarea('shift_schedule', ..., ['class' => 'form-control', 'rows' => 3,
    'placeholder' => __('e.g. Mon-Fri 8:00 AM - 5:00 PM'), 'required']) }}
```

### 6.2 Show View (`resources/views/staffs/show_fields.blade.php:21-28`)
```blade
<div class="col-md-6 ...">
    <label>Role Designation</label>
    <span>{{ !empty(optional($staff->staffProfile)->roleDesignation)
            ? $staff->staffProfile->roleDesignation->name : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 ...">
    <label>Assigned Station</label>
    <span>{{ !empty(optional($staff->staffProfile)->assignedStation)
            ? $staff->staffProfile->assignedStation->name : __('messages.common.n/a') }}</span>
</div>
```

### 6.3 Livewire Table (`app/Livewire/StaffTable.php`)
```php
public function builder(): Builder
{
    return User::with(['roles', 'staffProfile.roleDesignation:id,code,name', 'staffProfile.assignedStation:id,code,name'])
        ->where('type', User::STAFF)
        ->where('id', '!=', getLogInUserId())
        ->select(['id', 'first_name', 'last_name', 'email', 'email_verified_at', 'type']);
}
```
**Columns rendered:**
1. `staff_name` — avatar + full name + email
2. `email` (hidden)
3. `email` (hidden)
4. `role` — `{{ $row->role_name }}`
5. `designation` — `{{ $row->staffProfile?->roleDesignation?->name ?? 'N/A' }}`
6. `station` — `{{ $row->staffProfile?->assignedStation?->name ?? 'N/A' }}`
7. `action` — edit, reset-password, delete

> **N+1 resolved:** `builder()` now eager-loads `staffProfile.roleDesignation` and `staffProfile.assignedStation` via selective column loading (`id,code,name`).

---

## 7. Issues & Recommendations

| # | Severity | Location | Issue | Recommendation |
|---|----------|----------|-------|----------------|
| 1 | ~~Medium~~ | `app/Repositories/StaffRepository.php:55-56` | ~~Dead code: `$roles` variable is returned after an unconditional `return`.~~ | **Resolved** — unreachable `return $roles;` removed. |
| 2 | ~~Medium~~ | `app/Models/Staff.php` | ~~Entire `Staff` model and `staff` table are unused. Repository claims `Staff::class` but works with `User`.~~ | **Resolved** — `StaffRepository::model()` now returns `User::class`; all `Staff::PROFILE` references replaced with `User::PROFILE`. `Staff` model remains only as a legacy relation on `User` (`hasOne(Staff::class)`). |
| 3 | ~~Medium~~ | `StaffTable::builder()` | ~~Missing eager-load of `staffProfile.roleDesignation` and `staffProfile.assignedStation`.~~ | **Resolved** — eager-loads added and two blade columns (`designation.blade.php`, `station.blade.php`) created. |
| 4 | ~~Low~~ | `StaffTable` columns | ~~Role Designation and Assigned Station are **not displayed**.~~ | **Resolved** — `Column::make('Designation')` and `Column::make('Assigned Station')` added to table. |
| 5 | ~~Low~~ | `StaffController::destroy()` | ~~Directly calls `$staff->delete()` on `User`, bypassing repository and potential soft-delete / cascade logic.~~ | **Resolved** — now delegates to `StaffRepository::delete($id)` which cleans up `staffProfile`, media collection, and then deletes the user within a transaction. |
| 6 | ~~Low~~ | `StaffController::show()` | ~~Re-queries `User::whereType(User::STAFF)->findOrFail($staff->id)` after route-model binding already provided a `User`.~~ | **Resolved** — now trusts route-model binding and uses `$staff` directly from the resolved `User` parameter. |
| 7 | ~~Low~~ | `database/seeders/DefaultStaffSeeder.php` | ~~Entire file is commented out; no default demo staff are seeded.~~ | **Resolved** — removed the `$this->call(DefaultStaffSeeder::class)` line from `DatabaseSeeder.php`. |
| 8 | ~~Medium~~ | `routes/staff.php:61` | ~~`doctors.reset.password` route uses `PatientController::resetPassword` instead of `UserController` or `StaffController`.~~ | **Resolved** — route now points to `UserController::resetPassword`; method added to `UserController` with identical logic (reset to `123456`). |
| 9 | **Low** | `app/Http/Controllers/MedicineAvailabilityController.php` | Redirects to `medicine-availability.index` but no route registers `MedicineAvailabilityController` in any route file. | Verify if this controller is dead code or if routes are missing. |

Resolved in current codebase (verified during re-audit):
- `staffs.reset.password` now points to `StaffController` (in `routes/web.php`).
- `ClinicStation` now persists `code` and `ClinicStationSeeder` now keys `updateOrCreate` by `code`.
- `StaffTable` now surfaces designation + station with eager-loaded relations.
- `StaffRepository` now returns `User::class` from `model()` and uses `User::PROFILE` for media collections.
- `StaffRepository::delete()` performs transactional cleanup of `staffProfile` and media before deleting the user.
- `StaffController::show()` no longer re-queries the user after route-model binding.
- `DatabaseSeeder` no longer calls the empty `DefaultStaffSeeder`.
- `routes/staff.php` doctors reset-password route now uses `UserController::resetPassword`.

---

## 8. Permission Gate

All staff routes are wrapped in:
```php
Route::middleware('permission:manage_staff')
```
This relies on **Spatie Laravel-Permission**. The `manage_staff` permission must be assigned to roles (e.g., `clinic_admin`, `admin`) via seeders or the role-management UI.

---

## 9. Seed Execution Order (`database/seeders/DatabaseSeeder.php:44-45`)
```php
$this->call(StaffDesignationSeeder::class);   // populates 6 designations
$this->call(ClinicStationSeeder::class);      // populates 7 stations
```
Both seeders are now idempotent and key on stable `code` values via `updateOrCreate(['code' => ...], ...)`.

---

## 10. Recommended Module Access by Staff Designation

This section is re-validated against current code in:
- `database/seeders/StaffDesignationSeeder.php`
- `database/seeders/ClinicStationSeeder.php`
- `resources/views/layouts/menu.blade.php`
- `resources/views/layouts/sub_menu.blade.php`
- `routes/web.php`

Current data model and role split:
- Staff designations currently seeded: `clinic_head`, `nurse`, `pharmacist`, `triage_officer`, `clinic_staff`, `records_officer`.
- **University Physician/Doctor is handled in the separate Doctors module** (`manage_doctors`, `doctors.*`) and is not part of `staff_designations`.
- **University Dentist is not present** in the current staff designation seeder, and there is no `dental_clinic` station in the current station seeder.

### 10.1 Menu Gate Summary

| Module | Blade Gate | Route Middleware | Notes |
|--------|------------|------------------|-------|
| **Dashboard** | Always visible | `staff.module:dashboard` | Passes for all. |
| **Patients** | `@can('manage_patients')` + `canStaffAccessModule('patients')` | `staff.module:patients` | All staff designations have `patients`. |
| **Queue** | `@can('manage_patients')` + `canStaffAccessModule('queue')` | `staff.module:queue` | All staff designations have `queue`. |
| **Consultations** | `@can('manage_request_documents')` + `canStaffAccessModule('consultations')` | `staff.module:document_issuances` (resolved) | Nurse, triage, records, clinic_head. |
| **Prescriptions** | `@can('manage_request_documents')` + `canStaffAccessModule('prescriptions')` | `staff.module:prescriptions` | Clinic_head, pharmacist. |
| **Inventory** | `@can('manage_medicines')` + `canStaffAccessModule('inventory')` | `staff.module:inventory` | Clinic_head, pharmacist. |
| **Dispensing** | `@can('manage_medicines')` + `canStaffAccessModule('dispensing')` | `staff.module:dispensing` | Clinic_head, pharmacist. |
| **Lab Requests** | `@can('manage_request_documents')` + `canStaffAccessModule('lab_requests')` | `staff.module:lab_requests` | Clinic_head, nurse. |
| **Certificates** | `@can('manage_request_documents')` + `canStaffAccessModule('certificates')` | `staff.module:document_issuances` (resolved) | Nurse, clinic_staff, records, clinic_head. |
| **Reports** | `isRole('clinic_admin') \|\| isRole('doctor') \|\| (staff/nurse && `canStaffAccessModule('reports')`)` | `staff.module:activity_logs` (resolved) | All staff designations have `reports`. |
| **Notifications** | Same as Reports (uses `notifications`) | `staff.module:activity_logs` (resolved) | Clinic_head, pharmacist only. |
| **Settings** | `@canany(...)` + `canStaffAccessAnyModule(...)` | `staff.module:settings` | **Admin-only** — `canStaffAccessModule('settings')` returns `false` for all staff. |
| **Staffs** | `@can('manage_staff')` + `isRole('clinic_admin')` | `permission:manage_staff` (web.php) | Clinic_admin only. |
| **Doctors** | `@can('manage_doctors')` + `canStaffAccessModule('doctors')` | `staff.module:doctors` | Clinic_head only (added to map). |
| **Specializations** | `@can('manage_specialties')` + `canStaffAccessModule('specializations')` | `staff.module:specializations` | Clinic_head only (added to map). |
| **Roles / Countries / States / Cities / CMS** | `@can(...)` + `canStaffAccessModule(...)` | respective `staff.module:*` | **Admin-only** — not in any staff designation map. |

### 10.2 Recommended Access Matrix

| Staff Designation | Recommended Modules | Rationale |
|-------------------|---------------------|-----------|
| **Head of University Health Services** | Dashboard, Patients, Queue, Consultations, Prescriptions, Lab Requests, Certificates, Inventory, Dispensing, Reports, Notifications, Doctors, Specializations | Leads operations and needs broad oversight; additionally manages doctor accounts and clinic specializations. |
| **Registered Nurse** | Dashboard, Patients, Queue, Consultations (assisting), Lab Requests (assisting), Certificates (support), Reports | Assists patient intake, vitals, care coordination, and clinical workflow support. |
| **Pharmacist** | Dashboard, Inventory, Dispensing, Prescriptions (read/verify), Reports, Notifications | Manages FEFO inventory and medicine dispensing safety checks. |
| **Triage Officer** | Dashboard, Patients, Queue, Consultations (triage notes), Reports | Focused on first-contact assessment and patient routing. |
| **Clinic Staff / Secretary** | Dashboard, Patients, Queue, Certificates, Reports | Handles front desk, registration, queuing, and document release support. |
| **Medical Records Officer** | Dashboard, Patients (read/search), Consultations (records completeness), Certificates, Reports | Maintains records integrity and documentation completeness. |

### 10.3 Station-to-Module Correlation

| Assigned Station | Natural Workflows |
|------------------|-------------------|
| **Front Desk & Receiving** | Patients, Queue, Certificates |
| **Triage Area** | Patients, Queue, Consultations (vitals entry) |
| **Medical Consultation Room** | Consultations, Prescriptions, Lab Requests |
| **Clinic Pharmacy** | Inventory, Dispensing, Prescriptions (read) |
| **Records Area** | Patients (read/search), Reports, Certificates |
| **Observation & Recovery Room** | Patients (bed monitoring), Queue (status updates) |
| **Isolation Room** | Patients, Queue (flag isolation status), Consultations |

### 10.4 Implementation Notes

1. **Current permission model is role-based**, not designation-based. The app uses `@can('manage_patients')` etc., which are tied to the Spatie `staff` role, not to the `staff_profiles.role_designation_id`.
2. **Doctor/Physician is separate from Staff** in current architecture:
   - Doctor accounts are managed through the Doctors module (`manage_doctors`, `doctors.*`).
   - Staff accounts use `staff_profiles.role_designation_id` with the six staff-only designations listed above.
3. **No University Dentist in current seeders**: avoid assigning dentist-specific access rules until that designation/station is explicitly introduced.
4. **Enforcement now implemented** in codebase:
   - `app/helpers.php` adds `canStaffAccessModule()` using designation + assigned station maps.
   - `app/Http/Middleware/EnsureStaffModuleAccess.php` blocks unauthorized route access.
   - `routes/staff.php` applies `staff.module:*` middleware per module group (UI bypass-safe).
   - `resources/views/layouts/menu.blade.php` and `resources/views/layouts/sub_menu.blade.php` hide unauthorized modules.
5. **`StaffTable` now shows** Designation and Assigned Station columns via `staffs.components.designation` and `staffs.components.station` views, with eager-loaded relations.
6. **Settings is clinic_admin only** — `canStaffAccessModule('settings')` returns `false` for every `staff`/`nurse` role user via an explicit early-return guard. Route middleware `staff.module:settings` and blade `@if(canStaffAccessModule('settings'))` both block access.
7. **Admin-only modules** (not in any staff designation map, therefore blocked for all staff):
   - `settings`, `roles`, `countries`, `states`, `cities`, `cms`
   - These require `clinic_admin` role and use `web.php` / `admin` routes, bypassing `staff.php` entirely.
8. **Controller redirect consistency verified** — `DocumentIssuanceController`, `PatientController`, `PatientQueueController`, `MedicineController`, `StockInController`, `LabRequestController`, and `PrescriptionController` all redirect to role-prefixed routes (`staff.*` for staff role). These redirects target modules that are confirmed present in every relevant designation map, so no post-action 403s occur for legitimate workflows.

---

## 11. File Inventory

| File | Role |
|------|------|
| `routes/web.php` | Main route definitions / route file includes |
| `routes/staff.php` | Staff-prefixed role routes with `staff.module:*` guards |
| `app/Http/Controllers/StaffController.php` | CRUD controller |
| `app/Http/Requests/CreateStaffRequest.php` | Store validation |
| `app/Http/Requests/UpdateStaffRequest.php` | Update validation |
| `app/Http/Middleware/EnsureStaffModuleAccess.php` | Route-level designation/station access enforcement |
| `app/Repositories/StaffRepository.php` | Business logic / DB transaction wrapper |
| `app/Models/User.php` | Main entity (type = 3) |
| `app/Models/StaffProfile.php` | Extended profile (designation, station, shift) |
| `app/Models/StaffDesignation.php` | Lookup for Role Designation |
| `app/Models/ClinicStation.php` | Lookup for Assigned Station |
| `app/Models/Staff.php` | **Unused / legacy** model |
| `app/helpers.php` | Role routing + designation/station module access helpers |
| `app/Livewire/StaffTable.php` | Data table component |
| `resources/views/staffs/create.blade.php` | Create wrapper |
| `resources/views/staffs/edit.blade.php` | Edit wrapper |
| `resources/views/staffs/fields.blade.php` | Shared form fields |
| `resources/views/staffs/show.blade.php` | Detail view wrapper |
| `resources/views/staffs/show_fields.blade.php` | Detail fields |
| `resources/views/staffs/index.blade.php` | List view (Livewire mount) |
| `resources/views/staffs/components/*.blade.php` | Table cell renders |
| `resources/views/layouts/menu.blade.php` | Sidebar module visibility guards |
| `resources/views/layouts/sub_menu.blade.php` | Top sub-menu visibility guards |
| `database/migrations/2026_04_16_191000_normalize_university_clinic_profiles_schema.php` | Schema creation |
| `database/seeders/StaffDesignationSeeder.php` | Designation seed data |
| `database/seeders/ClinicStationSeeder.php` | Station seed data |
| `database/seeders/DefaultStaffSeeder.php` | **Commented-out** default staff seed |
| `database/seeders/DatabaseSeeder.php` | Orchestrator |

---

## 12. Behavior Verification

The following four requirements were explicitly verified during this re-audit:

### 12.1 Staff/Nurse users are restricted by both Role Designation and Assigned Station

**Verification:**
- `canStaffAccessModule()` first checks the user's designation map (`getStaffDesignationModuleMap()`). If the module is absent, it returns `false` immediately.
- For non-`clinic_head` designations, it then checks `getStaffStationModuleMap()`. If the module is absent for the assigned station, it returns `false`.
- **Both gates must pass** for operational modules (`patients`, `queue`, `consultations`, `prescriptions`, `lab_requests`, `certificates`, `inventory`, `dispensing`).

**Result:** PASS — enforced at `routes/staff.php` (middleware) and both menu blades (visibility).

### 12.2 Clinic Head bypasses station restrictions but still respects designation scope

**Verification:**
- In `canStaffAccessModule()`, after the designation map check passes, there is an early return: `if ($designationCode === 'clinic_head') { return true; }`.
- This skips the station-scoped check entirely.
- However, if a module is **not** in `clinic_head`'s designation map (e.g., `settings`, `roles`, `countries`), the designation-map check fails first and returns `false`.

**Result:** PASS — clinic_head gets full station flexibility but is still bounded by the designation whitelist.

### 12.3 Other roles (clinic_admin, doctor, patient) are unaffected

**Verification:**
- `canStaffAccessModule()` has an immediate `if (! ($user->hasRole('staff') || $user->hasRole('nurse'))) { return true; }` guard.
- `EnsureStaffModuleAccess` middleware applies the same check before evaluating module access.
- `clinic_admin` uses `web.php` admin routes (prefix `/admin`), not `staff.php`. `doctor` uses `doctor.php` routes. `patient` uses `patient.php` routes.

**Result:** PASS — non-staff roles bypass all designation/station checks.

### 12.4 Unauthorized direct URL access returns 403 instead of silently showing the module

**Verification:**
- Every module route group in `routes/staff.php` has `staff.module:*` middleware.
- `EnsureStaffModuleAccess::handle()` calls `abort(403, ...)` when `canStaffAccessModule()` returns `false`.
- Blade menus use the same helper, so UI visibility and route enforcement are **strictly aligned**.

**Result:** PASS — direct URL hits to unauthorized modules result in a 403 Forbidden response.

### 12.5 Settings is clinic_admin only

**Verification:**
- `canStaffAccessModule()` has an explicit early-return: `if ($module === 'settings') { return false; }` for all `staff`/`nurse` roles.
- `routes/staff.php` settings group carries `staff.module:settings`, which invokes the same helper.
- Blade gates (`@if(canStaffAccessModule('settings'))`) evaluate to `false` for all staff, hiding the menu item.
- `clinic_admin` accesses settings through `web.php` `/admin/settings` with `role:clinic_admin` middleware, completely outside `staff.php`.

**Result:** PASS — no staff designation or station assignment can access the Settings module.
