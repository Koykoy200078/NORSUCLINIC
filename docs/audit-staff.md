# NORSUCLINIC Staff Module — Comprehensive Audit

> **Scope:** Deep analysis of the Staff module, with special focus on the `Role Designation` and `Assigned Station` fields. Covers routes, controllers, requests, models, repositories, database schema, seeders, views, and Livewire tables.

---

## 1. Executive Summary

The Staff module manages clinic staff (receptionists, nurses, pharmacists, etc.). Staff are stored as `users` rows with `type = 3` (`User::STAFF`). Extended staff metadata (role designation, assigned station, shift schedule) is stored in the **one-to-one** `staff_profiles` table.

| Concern | Finding |
|---------|---------|
| **Role Designation** | FK to `staff_designations` (8 seeded values). |
| **Assigned Station** | FK to `clinic_stations` (8 seeded values). |
| **Data integrity** | Both fields are `required` in form requests and guarded by `exists:...` rules. |
| **Bugs / Issues** | Routing bug, dead-code, N+1, model-migration mismatch on `clinic_stations.code` (see §6). |

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
**Seeded values** (`database/seeders/StaffDesignationSeeder.php:12-20`):
| code | name |
|------|------|
| `clinic_head` | Head of University Health Services |
| `university_physician` | University Physician |
| `university_dentist` | University Dentist |
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
**Seeded values** (`database/seeders/ClinicStationSeeder.php:12-52`):
| code | name | description |
|------|------|-------------|
| `front_desk` | Front Desk & Receiving | Patient reception, queueing, and NORSU ID verification. |
| `triage_area` | Triage Area | Initial patient assessment, vitals checking (BP, Temp), and basic history taking. |
| `medical_consultation` | Medical Consultation Room | Private physician consultation and physical examination area. |
| `dental_consultation` | Dental Clinic | Dental consultation and extraction/cleaning procedures area. |
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
    Route::post('staffs/{user}/reset-password', [PatientController::class, 'resetPassword'])
        ->name('staffs.reset.password');  // ⚠️ BUG: uses PatientController (see §6)
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
protected $fillable = ['name', 'description'];
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
    return User::with(['roles'])
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
5. `action` — edit, reset-password, delete

> **⚠️ N+1 risk:** `builder()` does **not** eager-load `staffProfile`, `roleDesignation`, or `assignedStation`. If these were added as table columns, each row would trigger extra queries.

---

## 7. Issues & Recommendations

| # | Severity | Location | Issue | Recommendation |
|---|----------|----------|-------|----------------|
| 1 | **High** | `routes/web.php:210` | Reset-password route uses `PatientController::class` instead of `StaffController::class` (or a shared controller). | Change to `[StaffController::class, 'resetPassword']` or create a dedicated method. |
| 2 | **Medium** | `app/Repositories/StaffRepository.php:55-56` | Dead code: `$roles` variable is returned after an unconditional `return`. | Remove unreachable line `return $roles;`. |
| 3 | **Medium** | `app/Models/Staff.php` | Entire `Staff` model and `staff` table are unused. Repository claims `Staff::class` but works with `User`. | Either remove the dead model/table or refactor repository to use `User` as its formal model. |
| 4 | **Medium** | `StaffTable::builder()` | Missing eager-load of `staffProfile.roleDesignation` and `staffProfile.assignedStation`. | Add `->with(['staffProfile.roleDesignation', 'staffProfile.assignedStation'])`. |
| 5 | **Low** | `StaffTable` columns | Role Designation and Assigned Station are **not displayed** in the staff listing table. | Add two new `Column::make()` entries with custom blade views to surface this data. |
| 6 | **Low** | `StaffController::destroy()` | Directly calls `$staff->delete()` on `User`, bypassing repository and potential soft-delete / cascade logic. | Delegate to repository or handle media/profile cleanup explicitly. |
| 7 | **Low** | `StaffController::show()` | Re-queries `User::whereType(User::STAFF)->findOrFail($staff->id)` after route-model binding already provided a `User`. | Either trust route-model binding or use a custom `StaffUser` binding if type-scoping is required. |
| 8 | **Low** | `database/seeders/DefaultStaffSeeder.php` | Entire file is commented out; no default demo staff are seeded. | Either restore seed data or remove the file from `DatabaseSeeder` call chain. |
| 9 | **Medium** | `app/Models/ClinicStation.php:15-18` | `ClinicStation` model `$fillable` is missing `'code'` even though the migration now defines `code VARCHAR(60) UNIQUE`. The seeder passes `code` but Laravel silently discards it on create/update. | Add `'code'` to `$fillable` array in `ClinicStation` model. |
| 10 | **Medium** | `database/seeders/ClinicStationSeeder.php:56` | `updateOrCreate` is keyed on `name`, but `name` values changed (e.g. *Front Desk* → *Front Desk & Receiving*). Old records won't update; new rows may duplicate if unique constraint is bypassed. | Change key to `['code' => $station['code']]` so idempotency works by stable code instead of changing name. |

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
$this->call(StaffDesignationSeeder::class);   // populates 8 designations
$this->call(ClinicStationSeeder::class);        // populates 8 stations
```
`StaffDesignationSeeder` is idempotent (keyed on `code`). `ClinicStationSeeder` is **not** idempotent because it keys on `name`, and several `name` values were renamed (e.g. *Front Desk* → *Front Desk & Receiving*). Re-seeding will create new rows instead of updating old ones.

---

## 10. Recommended Module Access by Staff Designation

Based on the sidebar gates in `resources/views/layouts/menu.blade.php` and `sub_menu.blade.php`, the following matrix maps each **Staff Designation** to the modules they should realistically access. This is a *business-logic recommendation*; implementation still relies on Spatie permissions (`manage_patients`, `manage_medicines`, `manage_request_documents`, etc.) being assigned to the underlying role (`staff`).

### 10.1 Menu Gate Summary

| Module | Blade Gate (permission / role check) | Route Group |
|--------|----------------------------------------|-------------|
| **Dashboard** | No gate (always visible) | `getDashboardURL()` |
| **Patients** | `@can('manage_patients')` | `patients.index` |
| **Queue** | Inside `@can('manage_patients')` block | `patient-queue.index` |
| **Consultations** | `@can('manage_request_documents')` | `document-issuances.index?module=consultation` |
| **Prescriptions** | `@can('manage_request_documents')` | `prescriptions.index` |
| **Inventory** | `@can('manage_medicines')` | `medicine-inventory.index` |
| **Dispensing** | Inside `@can('manage_medicines')` block | `medicine-dispensing.index` |
| **Lab Requests** | `@can('manage_request_documents')` | `lab-requests.index` |
| **Certificates** | `@can('manage_request_documents')` | `document-issuances.index?module=certificate` |
| **Reports** | `isRole('clinic_admin') \|\| isRole('staff') \|\| isRole('doctor')` | `activity-logs.index` |
| **Notifications** | Same role check as Reports | Sub-menu under `activity-logs` |
| **Settings** | `@canany([manage_settings, manage_staff, manage_doctors, manage_roles, manage_specialties, manage_front_cms, manage_countries, manage_states, manage_cities])` | Various admin routes |
| **Staffs (sub-item)** | `@can('manage_staff')` + `isRole('clinic_admin')` | `staffs.index` |

### 10.2 Recommended Access Matrix

| Staff Designation | Recommended Modules | Rationale |
|-------------------|---------------------|-----------|
| **Head of University Health Services** | **ALL** + Settings / Staffs | Administrative oversight; should view reports, manage staff, and have read access across all clinic operations. |
| **University Physician** | Dashboard, Patients, Queue, Consultations, Prescriptions, Lab Requests, Certificates, Reports | Primary care provider; writes assessments, plans, prescriptions, and issues medical certificates. |
| **University Dentist** | Dashboard, Patients, Queue, Consultations (Dental), Prescriptions, Lab Requests, Certificates, Reports | Dental care provider; parallel to physician but focused on dental consultation room. |
| **Registered Nurse** | Dashboard, Patients, Queue, Triage, Consultations (assisting), Prescriptions (view-only), Lab Requests (assisting), Reports | Assists in triage, vitals, and observation room; may prep patients for physician. |
| **Pharmacist** | Dashboard, Inventory, Dispensing, Prescriptions (read-only to verify), Reports | Manages FEFO inventory, dispenses medicines, checks prescriptions for contraindications. |
| **Triage Officer** | Dashboard, Patients, Queue, Consultations (read-only view for triage notes), Reports | Focused on initial assessment and routing patients to correct station. |
| **Clinic Staff / Secretary** | Dashboard, Patients, Queue, Reports (basic), Certificates (issuance support) | Front desk & records; registers patients, manages queue, schedules appointments. |
| **Medical Records Officer** | Dashboard, Patients, Records/Reports, Certificates | Maintains physical & digital records; audits completeness of consultation forms. |

### 10.3 Station-to-Module Correlation

| Assigned Station | Natural Workflows |
|------------------|-------------------|
| **Front Desk & Receiving** | Patients, Queue, Certificates |
| **Triage Area** | Patients, Queue, Consultations (vitals entry) |
| **Medical Consultation Room** | Consultations, Prescriptions, Lab Requests |
| **Dental Clinic** | Consultations (Dental), Prescriptions |
| **Clinic Pharmacy** | Inventory, Dispensing, Prescriptions (read) |
| **Records Area** | Patients (read/search), Reports, Certificates |
| **Observation & Recovery Room** | Patients (bed monitoring), Queue (status updates) |
| **Isolation Room** | Patients, Queue (flag isolation status), Consultations |

### 10.4 Implementation Notes

1. **Current permission model is role-based**, not designation-based. The app uses `@can('manage_patients')` etc., which are tied to the Spatie `staff` role, not to the `staff_profiles.role_designation_id`.
2. **To enforce the matrix above**, you have two options:
   - **Option A (Quick):** Keep the single `staff` role but add *conditional logic* in controllers/views that checks `$user->staffProfile->roleDesignation->code` before allowing certain actions.
   - **Option B (Robust):** Create granular Spatie permissions such as `dispense_medicines`, `triage_patients`, `issue_certificates`, then assign them to sub-roles (e.g., `pharmacist_role`, `nurse_role`) and map each staff designation to a sub-role on creation.
3. **The `StaffTable` currently does not display** `role_designation_id` or `assigned_station_id`. If you implement option A/B, you should add these columns so admins can verify access levels at a glance.

---

## 11. File Inventory

| File | Role |
|------|------|
| `routes/web.php:207-211` | Route definitions |
| `app/Http/Controllers/StaffController.php` | CRUD controller |
| `app/Http/Requests/CreateStaffRequest.php` | Store validation |
| `app/Http/Requests/UpdateStaffRequest.php` | Update validation |
| `app/Repositories/StaffRepository.php` | Business logic / DB transaction wrapper |
| `app/Models/User.php` | Main entity (type = 3) |
| `app/Models/StaffProfile.php` | Extended profile (designation, station, shift) |
| `app/Models/StaffDesignation.php` | Lookup for Role Designation |
| `app/Models/ClinicStation.php` | Lookup for Assigned Station |
| `app/Models/Staff.php` | **Unused / legacy** model |
| `app/Livewire/StaffTable.php` | Data table component |
| `resources/views/staffs/create.blade.php` | Create wrapper |
| `resources/views/staffs/edit.blade.php` | Edit wrapper |
| `resources/views/staffs/fields.blade.php` | Shared form fields |
| `resources/views/staffs/show.blade.php` | Detail view wrapper |
| `resources/views/staffs/show_fields.blade.php` | Detail fields |
| `resources/views/staffs/index.blade.php` | List view (Livewire mount) |
| `resources/views/staffs/components/*.blade.php` | Table cell renders |
| `database/migrations/2026_04_16_191000_normalize_university_clinic_profiles_schema.php` | Schema creation |
| `database/seeders/StaffDesignationSeeder.php` | Designation seed data |
| `database/seeders/ClinicStationSeeder.php` | Station seed data |
| `database/seeders/DefaultStaffSeeder.php` | **Commented-out** default staff seed |
| `database/seeders/DatabaseSeeder.php` | Orchestrator |
