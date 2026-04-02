# CLEANUP: Navigation, Menus, Sidebars & Dashboard Components

**Phase:** 6 (after Phases 1–5 — remove feature code before removing nav references)  
**Status:** ⬜ Not started

---

## SCOPE

Remove all navigation items, dashboard statistics, and view-layer references to removed features. Includes `menu.blade.php`, `sub_menu.blade.php`, all 8 dashboard Livewire components, `DashboardRepository`, and the patient/doctor profile show pages.

---

## PART A — MAIN SIDEBAR MENU (`resources/views/layouts/menu.blade.php`)

### Items to Remove

| #   | Item                                       | Approx. Lines | Permission Guard                | Status                                                         |
| --- | ------------------------------------------ | ------------- | ------------------------------- | -------------------------------------------------------------- |
| 1   | Doctor Appointments                        | 54–77         | `@can('manage_appointments')`   | ⬜ Remove                                                      |
| 2   | Doctor Transactions                        | 130–143       | `@can('manage_transactions')`   | ⬜ Remove                                                      |
| 3   | Patient Appointments                       | 164–173       | `@can('manage_appointments')`   | ⬜ Remove                                                      |
| 4   | Patient Transactions                       | 175–182       | `@can('manage_transactions')`   | ⬜ Remove                                                      |
| 5   | Doctor Sessions (sub-item in Doctors menu) | 230–246       | —                               | ⬜ Remove (extract only the doctor-sessions item)              |
| 6   | Admin/Staff Appointments                   | 316–331       | `@can('manage_appointments')`   | ⬜ Remove                                                      |
| 7   | Admin/Staff Transactions                   | 400–416       | `@can('manage_transactions')`   | ⬜ Remove                                                      |
| 8   | Visits                                     | 418–427       | `@can('manage_patient_visits')` | ⬜ Remove (already commented — delete block)                   |
| 9   | Services                                   | 429–450       | `@can('manage_services')`       | ⬜ Remove                                                      |
| 10  | Clinic Schedules (in Settings section)     | 480–505       | —                               | ⬜ Remove (extract only the clinic-schedules search text/link) |

> ⚠️ Items 5 and 10 are nested inside larger menu sections (Doctors menu, Settings menu). Extract and remove only the specific sub-items — do NOT remove the parent menu container.

---

## PART B — TOP HEADER QUICK LINKS (`resources/views/layouts/sub_menu.blade.php`)

### Items to Remove

| #   | Item                             | Approx. Line | Role               | Status                                       |
| --- | -------------------------------- | ------------ | ------------------ | -------------------------------------------- |
| 1   | Doctor Appointments quick link   | 21           | Doctor             | ⬜ Remove                                    |
| 2   | Doctor My Schedule quick link    | 25           | Doctor             | ⬜ Remove                                    |
| 3   | Doctor Transactions quick link   | 29           | Doctor             | ⬜ Remove                                    |
| 4   | Patient Appointments quick link  | 43           | Patient            | ⬜ Remove                                    |
| 5   | Patient Transactions quick link  | 47           | Patient            | ⬜ Remove                                    |
| 6   | Doctor Sessions Management block | 77–93        | Admin/Staff        | ⬜ Remove                                    |
| 7   | Clinic Schedules quick link      | 127          | Admin              | ⬜ Remove                                    |
| 8   | Services quick link              | 200–213      | Admin/Staff/Doctor | ⬜ Remove                                    |
| 9   | Service Categories quick link    | 216–229      | Admin/Staff/Doctor | ⬜ Remove                                    |
| 10  | Admin Appointments quick link    | 236          | Admin/Staff        | ⬜ Remove                                    |
| 11  | Visits quick link                | 242          | Admin/Staff        | ⬜ Remove (already commented — delete block) |
| 12  | Admin Transactions quick link    | 262          | Admin/Staff        | ⬜ Remove                                    |

---

## PART C — HEADER (`resources/views/layouts/header.blade.php`)

| Item                      | Lines | Status                                                      |
| ------------------------- | ----- | ----------------------------------------------------------- |
| Patient Smart Card button | 24–33 | ⬜ Remove (already commented in `{{-- --}}` — delete block) |

---

## PART D — PATIENT PROFILE PAGE (`resources/views/patients/show.blade.php`)

### Items to Remove

| Element                                                     | Location    | Status    |
| ----------------------------------------------------------- | ----------- | --------- |
| Appointment statistics cards (today / upcoming / completed) | Lines 47–59 | ⬜ Remove |
| Appointments navigation tab                                 | Line 70     | ⬜ Remove |
| `<livewire:patient-show-page-appointment-table>` component  | Line 80     | ⬜ Remove |

---

## PART E — DOCTOR PROFILE PAGE (`resources/views/doctors/show.blade.php`)

### Items to Remove

| Element                                                     | Location    | Status    |
| ----------------------------------------------------------- | ----------- | --------- |
| Appointment statistics cards (today / upcoming / completed) | Lines 47–59 | ⬜ Remove |
| Appointments tab (conditional)                              | Lines 73–80 | ⬜ Remove |
| `<livewire:doctor-appointment-table>` component             | Line 99     | ⬜ Remove |

---

## PART F — DASHBOARD LIVEWIRE COMPONENTS (8 files to edit)

Each component has appointment-related imports, properties, and queries to remove.

### 1. `app/Livewire/Dashboard.php`

- Remove: `use App\Models\Appointment;` (line 5)
- Remove from `mount()`: `$this->todayAppointmentCount = Appointment::...->count();` (line 22)
- Remove property: `$todayAppointmentCount`

### 2. `app/Livewire/AdminDashBoardTable.php`

- Remove: `use App\Models\Appointment;`
- Remove from `mount()`: `Patient::with(['user', 'appointments'])->withCount('appointments')` → change to `Patient::with(['user'])` without appointment count

### 3. `app/Livewire/AdminDashboardSidebarTable.php`

- Remove: `use App\Models\Appointment;`
- Remove from `mount()`:
    - `$this->upcomingAppointmentCount = Appointment::where('date', '>', ...)->count();`
    - `$this->totalAppointmentCount = Appointment::count();`
- Remove properties: `$upcomingAppointmentCount`, `$totalAppointmentCount`

### 4. `app/Livewire/DoctorDashboardTable.php`

- Remove: `use App\Models\Appointment;`
- Remove from `loadStatistics()`:
    - `$this->patientQueuesCount = Appointment::whereDate(...)->...->count();`
    - All other appointment count queries (lines 33–44)
- Remove appointment-related properties

### 5. `app/Livewire/DoctorDashboardSidebarTable.php`

- Remove: `use App\Models\Appointment;`
- Remove from `mount()`: All appointment queries (lines 19–36)
- Remove appointment-related properties

### 6. `app/Livewire/PatientDashboardSidebarTable.php`

- Remove: `use App\Models\Appointment;`
- Remove from `mount()`: All appointment queries (lines 20–49)
- Remove all 6 appointment-related properties

### 7. `app/Livewire/StaffDashboard.php`

- Remove: `use App\Models\Appointment;`
- Remove from `mount()`: `$this->todayAppointmentCount = Appointment::...->count();` (line 23)
- Remove property: `$todayAppointmentCount`

### 8. `app/Livewire/StaffDashBoardTable.php`

- Remove from `mount()`: `Patient::with(['user', 'appointments'])->withCount('appointments')` → change to `Patient::with(['user'])`

### 9. `app/Livewire/DoctorDashboardDataTable.php`

- Remove: `use App\Models\Appointment;` (line 5)
- Remove property: `public $appointments;` (line 15)
- Remove from `mount()`: `$this->appointments['records'] = Appointment::with(['patient.user'])...->whereStatus(Appointment::BOOKED)` (lines 19–21)
- Result: component should return empty data or be simplified to non-appointment doctor stats

### 10. `app/Livewire/StaffDashboardSidebarTable.php`

- Remove: `use App\Models\Appointment;` (line 5)
- Remove from `mount()` (lines 19–20):
    - `$this->upcomingAppointmentCount = Appointment::where('date', '>', $todayDate)->count();`
    - `$this->totalAppointmentCount = Appointment::count();`
- Remove properties: `$upcomingAppointmentCount`, `$totalAppointmentCount`

> ℹ️ `PatientDashboardTable.php` — confirmed **no appointment references**. No changes needed.

---

## PART G — DASHBOARD REPOSITORY (`app/Repositories/DashboardRepository.php`)

This file requires the most significant refactor. It currently has heavy appointment/service/transaction dependencies.

### Imports to Remove (lines 5–12)

```php
use App\Models\Appointment;      // line 5
use App\Models\Service;          // line 8
use App\Models\ServiceCategory;  // line 9
use App\Models\Transaction;      // line 12
```

### Methods to Rebuild

| Method                                    | Current Content                               | Action                                                         |
| ----------------------------------------- | --------------------------------------------- | -------------------------------------------------------------- |
| Admin dashboard data (lines 28–66)        | Appointment counts, service/category caching  | Remove appointment/service queries; keep patient/doctor counts |
| Service caching (lines 57–60)             | `active_services`, `service_categories` cache | Remove entirely                                                |
| Doctor dashboard data (lines 84–112)      | Multiple appointment queries                  | Remove; replace with patient-based stats                       |
| Doctor appointment method (lines 162–205) | All appointment queries                       | Remove entire method                                           |
| Patient data method (lines 207–255)       | Appointment count queries                     | Remove appointment queries                                     |
| Appointment chart data (lines 257–330)    | Appointment + Transaction chart               | Remove entire method                                           |
| Patient all appointments (lines 332–373)  | Appointment + Transaction queries             | Remove entire method                                           |
| Doctor all appointments (lines 375–407)   | Appointment + Transaction queries             | Remove entire method                                           |
| Staff dashboard data (lines 429–468)      | Same as admin — appointment + services        | Remove appointment/service queries                             |

### DashboardController.php Cleanup

After updating repository, remove calls in `app/Http/Controllers/DashboardController.php`:

- `$this->dashboardRepository->getAppointmentChartData(...)` (lines 32, 82)
- `$doctorAllAppointment = $this->dashboardRepository->doctorAllAppointment();` (line 59)
- `$patientAllAppointment = $this->dashboardRepository->patientAllAppointment();` (line 72)
- Pass these variables' data-pass to views accordingly

---

## PART H — ROLES FIELDS VIEW

### `resources/views/roles/fields.blade.php`

Remove option/checkbox entries for removed permissions:

- `manage_appointments`
- `manage_patient_visits`
- `manage_doctor_sessions`
- `manage_services`
- `manage_transactions`

---

## COMPLETION CHECKLIST

### Navigation Files

- [ ] `menu.blade.php` — Remove 10 nav items (items 1–10 above)
- [ ] `sub_menu.blade.php` — Remove 12 quick link items (items 1–12 above)
- [ ] `header.blade.php` — Remove commented smart card button block

### Profile Pages

- [ ] `patients/show.blade.php` — Remove appointment cards, tab, Livewire component
- [ ] `doctors/show.blade.php` — Remove appointment cards, tab, Livewire component

### Dashboard Livewire Components (10 files)

- [ ] Dashboard.php
- [ ] AdminDashBoardTable.php
- [ ] AdminDashboardSidebarTable.php
- [ ] DoctorDashboardTable.php
- [ ] DoctorDashboardSidebarTable.php
- [ ] DoctorDashboardDataTable.php
- [ ] PatientDashboardSidebarTable.php
- [ ] StaffDashboard.php
- [ ] StaffDashBoardTable.php
- [ ] StaffDashboardSidebarTable.php

### Dashboard Repository & Controller

- [ ] DashboardRepository.php — Remove 4 imports, rebuild/strip 8 methods
- [ ] DashboardController.php — Remove 4 method calls, update view data

### Roles View

- [ ] `roles/fields.blade.php` — Remove 5 permission option entries

---

_Phase 6 — Prerequisites: Phases 1–5 complete (so removed feature code is gone before nav items are removed)._
