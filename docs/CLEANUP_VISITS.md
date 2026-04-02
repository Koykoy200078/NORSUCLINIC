# CLEANUP: Visits / Encounters

**Phase:** 3 (independent — can run in parallel with Phase 2)  
**Status:** ⬜ Not started

---

## SCOPE

Remove the full patient visit/encounter system. This includes all 5 visit models, 2 controllers, 3 Livewire tables, 2 repositories, 4 request classes, all routes, views, and helpers.

> ⚠️ **Duplicate routes exist in `web.php`** — visit routes are defined TWICE (lines ~281–290 AND ~395–404). Both blocks must be removed.

---

## CONTROLLERS TO DELETE

| File                                              | Status    |
| ------------------------------------------------- | --------- |
| `app/Http/Controllers/VisitController.php`        | ⬜ Delete |
| `app/Http/Controllers/PatientVisitController.php` | ⬜ Delete |

---

## MODELS TO DELETE

| File                               | Status    |
| ---------------------------------- | --------- |
| `app/Models/Visit.php`             | ⬜ Delete |
| `app/Models/VisitProblem.php`      | ⬜ Delete |
| `app/Models/VisitObservation.php`  | ⬜ Delete |
| `app/Models/VisitNote.php`         | ⬜ Delete |
| `app/Models/VisitPrescription.php` | ⬜ Delete |

---

## LIVEWIRE COMPONENTS TO DELETE

| File                                 | Status    |
| ------------------------------------ | --------- |
| `app/Livewire/VisitTable.php`        | ⬜ Delete |
| `app/Livewire/DoctorVisitTable.php`  | ⬜ Delete |
| `app/Livewire/PatientVisitTable.php` | ⬜ Delete |

---

## REPOSITORIES TO DELETE

| File                                          | Status    |
| --------------------------------------------- | --------- |
| `app/Repositories/VisitRepository.php`        | ⬜ Delete |
| `app/Repositories/PatientVisitRepository.php` | ⬜ Delete |

---

## REQUEST CLASSES TO DELETE

| File                                                   | Status    |
| ------------------------------------------------------ | --------- |
| `app/Http/Requests/CreateVisitRequest.php`             | ⬜ Delete |
| `app/Http/Requests/UpdateVisitRequest.php`             | ⬜ Delete |
| `app/Http/Requests/CreateVisitPrescriptionRequest.php` | ⬜ Delete |
| `app/Http/Requests/UpdateVisitPrescriptionRequest.php` | ⬜ Delete |

---

## VIEW DIRECTORIES TO DELETE

| Path                              | Status                     |
| --------------------------------- | -------------------------- |
| `resources/views/visits/`         | ⬜ Delete entire directory |
| `resources/views/patient_visits/` | ⬜ Delete entire directory |

---

## ASSET FILES TO DELETE (if exist)

| Path                           | Status                                 |
| ------------------------------ | -------------------------------------- |
| `resources/assets/js/visits/`  | ⬜ Delete entire directory (if exists) |
| `public/js/visits/` (compiled) | ⬜ Delete (if exists)                  |

---

## ROUTE BLOCKS TO REMOVE

> ⚠️ Visit routes appear **TWICE** in `routes/web.php` (around lines 281–290 AND 395–404). Remove both blocks.

### `routes/web.php`

Remove ALL blocks matching:

- `VisitController`
- `PatientVisitController`
- Route patterns: `visits.*`, `patient-visits.*`
- **Check lines ~281–290 AND ~395–404** (duplicate blocks)

### `routes/doctor.php`

Remove `manage_patient_visits` permission group containing doctor visit routes.

### `routes/staff.php`

Remove `manage_patient_visits` permission group containing staff visit routes.

### `routes/patient.php`

Remove `manage_patient_visits` permission group containing patient visit routes.

---

## HELPER TO REMOVE

In `app/helpers.php`:

| Function          | Notes                                     | Status    |
| ----------------- | ----------------------------------------- | --------- |
| `getVisitRoute()` | Returns visit route for current user role | ⬜ Remove |

---

## SEEDER CLEANUP

### `database/seeders/DefaultPermissionSeeder.php`

- Remove: `manage_patient_visits` (line 33)

### `database/seeders/RolePermissionsSeeder.php`

- Remove `manage_patient_visits` from doctor and staff role assignments

### `database/seeders/DefaultAssignPermissionSeeder.php`

- Remove `manage_patient_visits` from patient and doctor assignments

---

## MODEL CLEANUP (remove references in kept models)

### `app/Models/Patient.php`

Remove from `static::deleting()` boot:

```php
$patient->visits()->delete();
```

Remove relationship:

```php
public function visits(): HasMany
{
    return $this->hasMany(Visit::class);
}
```

Remove PHPDoc entry:

```
@property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Visit> $visits
```

---

## SERVICE CLEANUP

### `app/Services/PatientService.php`

**`getPatientWithHistory()` method (lines 172–192):**  
Remove eager-load for visits:

```php
'visits' => function ($query) {
    $query->with(['doctor.user'])...
},
```

---

## NAVIGATION CLEANUP (handled in CLEANUP_NAVIGATION.md)

- `menu.blade.php` lines 418–427 — Visits menu item (already commented, remove the block)
- `sub_menu.blade.php` line 242 — Visits quick link (already commented, remove the block)

---

## DATABASE TABLES TO DROP (handled in CLEANUP_DATABASE.md)

Drop in this order (FK cascade safety):

1. `visit_problems` (FK → visits)
2. `visit_observations` (FK → visits)
3. `visit_notes` (FK → visits)
4. `visit_prescriptions` (FK → visits)
5. `visits` (FK → doctors, patients)

---

## MIGRATION FILES (reference only — do not delete, create new drop migration)

| Migration File                                           | Table Created         | Status                               |
| -------------------------------------------------------- | --------------------- | ------------------------------------ |
| `2021_09_03_070956_create_visits_table.php`              | `visits`              | Referenced in Phase 7 drop migration |
| `2021_09_08_113152_create_visit_problems_table.php`      | `visit_problems`      | Referenced in Phase 7 drop migration |
| `2021_09_08_113357_create_visit_observations_table.php`  | `visit_observations`  | Referenced in Phase 7 drop migration |
| `2021_09_08_113408_create_visit_notes_table.php`         | `visit_notes`         | Referenced in Phase 7 drop migration |
| `2021_09_09_122904_create_visit_prescriptions_table.php` | `visit_prescriptions` | Referenced in Phase 7 drop migration |

---

## COMPLETION CHECKLIST

- [ ] Delete VisitController, PatientVisitController
- [ ] Delete 5 visit models (Visit, VisitProblem, VisitObservation, VisitNote, VisitPrescription)
- [ ] Delete 3 Livewire components (VisitTable, DoctorVisitTable, PatientVisitTable)
- [ ] Delete 2 repositories (VisitRepository, PatientVisitRepository)
- [ ] Delete 4 request classes
- [ ] Delete view directories: visits/, patient_visits/
- [ ] Remove visit route blocks from web.php (BOTH duplicate blocks), doctor.php, staff.php, patient.php
- [ ] Remove `getVisitRoute()` from helpers.php
- [ ] Update DefaultPermissionSeeder (remove manage_patient_visits)
- [ ] Update RolePermissionsSeeder & DefaultAssignPermissionSeeder
- [ ] Patient.php — remove visits() cascade and relationship
- [ ] PatientService.php — remove visits eager-load from getPatientWithHistory()
- [ ] Database: drop 5 visit tables (Phase 7)

---

_Phase 3 — Independent of Phases 1 & 2. Can execute in parallel._
