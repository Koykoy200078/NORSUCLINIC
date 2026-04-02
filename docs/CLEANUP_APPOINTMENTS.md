# CLEANUP: Appointments, Services, Doctor Sessions & Clinic Schedules

**Phase:** 2 (after Phase 1 payment controllers removed)  
**Status:** ⬜ Not started

---

## SCOPE

Remove the full appointment/booking system, service management, doctor session scheduling, and clinic schedule features.  
**Also includes:** Decoupling `Prescription` from `Appointment` (standalone).

---

## PART A — APPOINTMENTS

### Controllers to Delete

| File                                                    | Status    |
| ------------------------------------------------------- | --------- |
| `app/Http/Controllers/AppointmentController.php`        | ⬜ Delete |
| `app/Http/Controllers/PatientAppointmentController.php` | ⬜ Delete |

### Models to Delete

| File                         | Status    |
| ---------------------------- | --------- |
| `app/Models/Appointment.php` | ⬜ Delete |

### Livewire Components to Delete

| File                                               | Status    |
| -------------------------------------------------- | --------- |
| `app/Livewire/AppointmentTable.php`                | ⬜ Delete |
| `app/Livewire/DoctorAppointmentTable.php`          | ⬜ Delete |
| `app/Livewire/PatientAppointmentTable.php`         | ⬜ Delete |
| `app/Livewire/PatientShowPageAppointmentTable.php` | ⬜ Delete |
| `app/Livewire/DoctorPanelAppointmentTable.php`     | ⬜ Delete |

### Repositories to Delete

| File                                         | Status    |
| -------------------------------------------- | --------- |
| `app/Repositories/AppointmentRepository.php` | ⬜ Delete |

### Request Classes to Delete

| File                                                  | Status    |
| ----------------------------------------------------- | --------- |
| `app/Http/Requests/CreateAppointmentRequest.php`      | ⬜ Delete |
| `app/Http/Requests/UpdateAppointmentRequest.php`      | ⬜ Delete |
| `app/Http/Requests/CreateFrontAppointmentRequest.php` | ⬜ Delete |

### View Directories to Delete

| Path                                                    | Status                               |
| ------------------------------------------------------- | ------------------------------------ |
| `resources/views/appointments/`                         | ⬜ Delete entire directory           |
| `resources/views/doctor_appointment/`                   | ⬜ Delete entire directory           |
| `resources/views/patients/appointments/`                | ⬜ Delete entire directory           |
| `resources/views/admin-appointments-calendar.blade.php` | ⬜ Delete (standalone calendar file) |

### Route Blocks to Remove

**`routes/web.php`**  
Remove all blocks using `AppointmentController`, route patterns: `appointments.*`

**`routes/doctor.php`**  
Remove `manage_appointments` permission block (doctor appointments group)

**`routes/staff.php`**  
Remove `manage_appointments` permission block

**`routes/patient.php`**  
Remove `manage_appointments` permission block (patient appointments)

### Seeder Cleanup

- `database/seeders/DefaultPermissionSeeder.php` — Remove `manage_appointments` (line 29)
- `database/seeders/RolePermissionsSeeder.php` — Remove `manage_appointments` from assignments
- `database/seeders/DefaultAssignPermissionSeeder.php` — Remove `manage_appointments`

---

## PART B — SERVICES & SERVICE CATEGORIES

### Controllers to Delete

| File                                                 | Status    |
| ---------------------------------------------------- | --------- |
| `app/Http/Controllers/ServiceController.php`         | ⬜ Delete |
| `app/Http/Controllers/ServiceCategoryController.php` | ⬜ Delete |

### Repositories to Delete

| File                                             | Status    |
| ------------------------------------------------ | --------- |
| `app/Repositories/ServicesRepository.php`        | ⬜ Delete |
| `app/Repositories/ServiceCategoryRepository.php` | ⬜ Delete |

### Request Classes to Delete

| File                                                 | Status    |
| ---------------------------------------------------- | --------- |
| `app/Http/Requests/CreateServicesRequest.php`        | ⬜ Delete |
| `app/Http/Requests/UpdateServicesRequest.php`        | ⬜ Delete |
| `app/Http/Requests/CreateServiceCategoryRequest.php` | ⬜ Delete |
| `app/Http/Requests/UpdateServiceCategoryRequest.php` | ⬜ Delete |

### Models to Delete

| File                             | Status    |
| -------------------------------- | --------- |
| `app/Models/Service.php`         | ⬜ Delete |
| `app/Models/ServiceCategory.php` | ⬜ Delete |

### Livewire Components to Delete

| File                                    | Status                                                                             |
| --------------------------------------- | ---------------------------------------------------------------------------------- |
| `app/Livewire/ServiceTable.php`         | ⬜ Delete                                                                          |
| `app/Livewire/ServiceCategoryTable.php` | ⬜ Delete (was using smart_patient_cards_skeleton — verify renamed skeleton first) |

### View Directories to Delete

| Path                                  | Status                     |
| ------------------------------------- | -------------------------- |
| `resources/views/services/`           | ⬜ Delete entire directory |
| `resources/views/service_categories/` | ⬜ Delete entire directory |

### Cache Commands to Update

**`app/Console/Commands/WarmUpCache.php`**

- Remove: `use App\Models\Service;` (line 6)
- Remove: `use App\Models\ServiceCategory;` (line 7)
- Remove: Cache block for `active_services` (line 27)
- Remove: Cache block for `service_categories` (line 30)

**`app/Console/Commands/CacheWarmup.php`**

- Remove: `use App\Models\Service;` (line 6)
- Remove: `use App\Models\ServiceCategory;` (line 7–8)
- Remove: Cache blocks for `active_services` and `service_categories` (lines 41–50)

### Seeder Cleanup

- `database/seeders/DefaultPermissionSeeder.php` — Remove `manage_services` (line 49)
- `database/seeders/RolePermissionsSeeder.php` — Remove `manage_services` from doctor and staff

---

## PART C — DOCTOR SESSIONS & CLINIC SCHEDULES

### Controllers to Delete

| File                                                | Status    |
| --------------------------------------------------- | --------- |
| `app/Http/Controllers/DoctorSessionController.php`  | ⬜ Delete |
| `app/Http/Controllers/ClinicScheduleController.php` | ⬜ Delete |

### Repositories to Delete

| File                                           | Status    |
| ---------------------------------------------- | --------- |
| `app/Repositories/DoctorSessionRepository.php` | ⬜ Delete |

### Request Classes to Delete

| File                                               | Status    |
| -------------------------------------------------- | --------- |
| `app/Http/Requests/UpdateDoctorSessionRequest.php` | ⬜ Delete |

### Livewire Components to Delete

| File                                   | Status                                               |
| -------------------------------------- | ---------------------------------------------------- |
| `app/Livewire/DoctorScheduleTable.php` | ⬜ Delete (doctor session schedule management table) |

### Models to Delete

| File                            | Status    |
| ------------------------------- | --------- |
| `app/Models/DoctorSession.php`  | ⬜ Delete |
| `app/Models/SessionWeekDay.php` | ⬜ Delete |
| `app/Models/ClinicSchedule.php` | ⬜ Delete |

### View Directories to Delete

| Path                               | Status                     |
| ---------------------------------- | -------------------------- |
| `resources/views/doctor_sessions/` | ⬜ Delete entire directory |
| `resources/views/clinic_schedule/` | ⬜ Delete entire directory |

### Helpers to Remove

In `app/helpers.php`:

| Function                             | Status    |
| ------------------------------------ | --------- |
| `getSlotByGap()`                     | ⬜ Remove |
| `getSchedulesTimingSlot()`           | ⬜ Remove |
| `getDoctorBookedAppointmentsCount()` | ⬜ Remove |
| `getLoginDoctorSessionUrl()`         | ⬜ Remove |
| `doctorSessionActiveUrl()`           | ⬜ Remove |

### Seeder Cleanup

- `database/seeders/DefaultPermissionSeeder.php` — Remove `manage_doctor_sessions` (line 41)
- `database/seeders/RolePermissionsSeeder.php` — Remove `manage_doctor_sessions` from doctor and staff

---

---

## PART C-2 — DOCTOR HOLIDAY

**Decision: REMOVE** — `DoctorHoliday` is tightly coupled to DoctorSession scheduling. Without DoctorSessions it has no purpose.

### Files to Delete

| File                                         | Status                     |
| -------------------------------------------- | -------------------------- |
| `app/Http/Controllers/HolidayController.php` | ⬜ Delete                  |
| `app/Repositories/HolidayRepository.php`     | ⬜ Delete                  |
| `app/Livewire/DoctorHolidayTable.php`        | ⬜ Delete                  |
| `app/Models/DoctorHoliday.php`               | ⬜ Delete                  |
| `app/Http/Requests/CreateHolidayRequest.php` | ⬜ Delete                  |
| `resources/views/doctor_holiday/`            | ⬜ Delete entire directory |

---

## PART D — PRESCRIPTION DECOUPLING (CRITICAL)

The `Prescription` model is **KEPT** but must be decoupled from `Appointment`.

### app/Models/Prescription.php

**Remove from `$fillable`:**

```php
'appointment_id',
```

**Remove from `$casts`:**

```php
'appointment_id' => 'integer',
```

**Remove relationship method:**

```php
public function appointment(): BelongsTo
{
    return $this->belongsTo(Appointment::class);
}
```

**Remove PHPDoc line:**

```
@property int|null $appointment_id
@property-read \App\Models\Appointment|null $appointment
```

### app/Http/Controllers/PrescriptionController.php

**Method `create($appointmentId)`** → Refactor to `create($patientId = null)`

- Remove: load appointment → `Appointment::with(['doctor.user', 'patient.user', 'service'])->findOrFail($appointmentId)`
- Replace with: load patient directly → `Patient::with(['user'])->findOrFail($patientId)`
- Update view data accordingly

**Method `edit($appointmentId, Prescription)`** → Refactor signature

- Remove appointment-based authorization check
- Replace with patient-based or prescription-owner check

### app/Services/PrescriptionService.php

**`createPrescription()` method (line 22):**

- Remove: `'appointment_id' => $data['appointment_id'] ?? null,`

**`generatePDF()` method (line 72):**

- Remove: `'appointment'` from eager load

**`getPrescriptionWithDetails()` method (line 103):**

- Remove: `'appointment'` from eager load

### Routes to Update

Old routes bind `$appointmentId` as first parameter — update to `$patientId`:

```
GET /appointments/{appointmentId}/prescription-create
GET /appointments/{appointmentId}/prescription-edit/{prescription}
```

→ New routes:

```
GET /patients/{patientId}/prescription-create
GET /prescriptions/{prescription}/edit
```

Update in `routes/web.php`, `routes/doctor.php`, `routes/patient.php`

---

## MODEL CLEANUP (remove references to removed features)

### app/Models/Doctor.php

Remove relationships:

```php
public function doctorSession(): HasMany
{
    return $this->hasMany(DoctorSession::class);
}

public function appointments(): HasMany
{
    return $this->hasMany(Appointment::class);
}
```

Remove from PHPDoc:

- `@property-read \App\Models\DoctorSession` lines
- `@property-read \App\Models\Appointment` lines

### app/Models/Patient.php

Remove from `static::deleting()` boot:

```php
$patient->appointments()->delete();
$patient->visits()->delete();
```

Remove relationships:

```php
public function appointments(): HasMany
{
    return $this->hasMany(Appointment::class, 'patient_id');
}
```

(visits relationship removed in CLEANUP_VISITS.md)

---

## CONTROLLER CLEANUP

### app/Http/Controllers/UserController.php

Remove imports:

```php
use App\Models\Appointment;    // line 11
use App\Models\DoctorSession;  // line 13
use App\Models\Visit;          // line 17
```

Remove from `show()` method (line 111):

```php
$doctorAppointment = Appointment::whereDoctorId($doctor->id)->wherePatientId(...);
```

Remove from `destroy()` method (lines 171–174):

```php
$existAppointment = Appointment::whereDoctorId($doctor->id)->exists();
$existVisit = Visit::whereDoctorId($doctor->id)->exists();
if ($existAppointment || $existVisit) { ... }
```

Remove `sessionData()` method (lines 371–376).

Remove from `editProfile()` method debug log (lines 272–279): office_id, department_id, college_id debug lines.

### app/Http/Controllers/PatientController.php

Remove imports (lines 7, 9, 10):

```php
use App\Models\Visit;
use App\Models\Appointment;
use App\Models\Transaction;
```

Remove from `show()` method (lines 118, 122–135):

- Doctor appointment check
- `$appointmentStatus = Appointment::ALL_STATUS;`
- Appointment count queries

Remove from `destroy()` method:

- Appointment existence check before patient deletion

---

## COMPLETION CHECKLIST

### Appointments

- [ ] Delete AppointmentController, PatientAppointmentController
- [ ] Delete AppointmentRepository
- [ ] Delete Appointment model
- [ ] Delete 5 Livewire appointment table components
- [ ] Delete request classes: CreateAppointmentRequest, UpdateAppointmentRequest, CreateFrontAppointmentRequest
- [ ] Delete view directories: appointments/, doctor_appointment/, patients/appointments/
- [ ] Delete file: admin-appointments-calendar.blade.php
- [ ] Remove route blocks (web.php, doctor.php, staff.php, patient.php)
- [ ] Update DefaultPermissionSeeder (remove manage_appointments)

### Services

- [ ] Delete ServiceController, ServiceCategoryController
- [ ] Delete ServicesRepository, ServiceCategoryRepository
- [ ] Delete Service, ServiceCategory models
- [ ] Delete ServiceTable, ServiceCategoryTable Livewire components
- [ ] Delete request classes: CreateServicesRequest, UpdateServicesRequest, CreateServiceCategoryRequest, UpdateServiceCategoryRequest
- [ ] Delete view directories: services/, service_categories/
- [ ] Update WarmUpCache.php, CacheWarmup.php (remove service caching)
- [ ] Update DefaultPermissionSeeder (remove manage_services)

### Doctor Sessions, Clinic Schedules & Doctor Holiday

- [ ] Delete DoctorSessionController, ClinicScheduleController, HolidayController
- [ ] Delete DoctorSessionRepository, HolidayRepository
- [ ] Delete DoctorSession, SessionWeekDay, ClinicSchedule, DoctorHoliday models
- [ ] Delete DoctorScheduleTable, DoctorHolidayTable Livewire components
- [ ] Delete request classes: UpdateDoctorSessionRequest, CreateHolidayRequest
- [ ] Delete view directories: doctor_sessions/, clinic_schedule/, doctor_holiday/
- [ ] Remove 5 helper functions from helpers.php
- [ ] Update DefaultPermissionSeeder (remove manage_doctor_sessions)

### Prescription Decoupling

- [ ] Remove appointment_id from Prescription fillable/casts/relationship/PHPDoc
- [ ] Refactor PrescriptionController::create() to accept patientId
- [ ] Refactor PrescriptionController::edit() signature
- [ ] Update PrescriptionService::createPrescription() (remove appointment_id)
- [ ] Update PrescriptionService::generatePDF() / getPrescriptionWithDetails()
- [ ] Update prescription routes in web.php, doctor.php, patient.php

### Model & Controller Cleanup

- [ ] Doctor.php — remove doctorSession() and appointments() relationships
- [ ] Patient.php — remove appointments() cascade and relationship
- [ ] UserController.php — remove 3 imports + show/destroy refs + sessionData()
- [ ] PatientController.php — remove 3 imports + show/destroy appointment refs

---

_Phase 2 — Prerequisites: Phase 1 complete. Prescription decoupling can start in parallel._
