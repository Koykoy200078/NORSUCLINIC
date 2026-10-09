# Staff Role Designation + Assigned Station Access Audit (Deep Scan)

> **Superseded (2026-10-09, audit plan Phase 3):** the designation / station / shift layer this audit describes was
> removed. Staff = Nurse is one role, "Staff (Nurse)", and what it may open comes only from its permissions in Settings >
> Manage User roles (`canUseModule()`, see [audit-plan-2026-10-08.md](audit-plan-2026-10-08.md) Phase 3). The three
> `docs/staff-*.json` maps listed below were deleted with it. Kept for history only.

Date: 2026-05-05  
Scope: Audit + enforcement phase applied in this workspace

## 1. Audit Goal

Audit current staff access control based on:

- Staff Role Designation
- Staff Assigned Station

And provide strict recommendations on what modules and submodules each staff profile should access.

---

## 2. Evidence Reviewed

### Access control implementation

- `app/helpers.php`
    - `normalizeStaffModuleKey()`
    - `getStaffDesignationModuleMap()`
    - `getStaffStationModuleMap()`
    - `canStaffAccessModule()`
    - `canStaffAccessAnyModule()`
- `app/Http/Middleware/EnsureStaffModuleAccess.php`
- `app/Http/Kernel.php` (`staff.module` alias)
- `routes/staff.php`
- `resources/views/layouts/menu.blade.php`
- `resources/views/layouts/sub_menu.blade.php`

### Data model / source of truth

- `app/Models/StaffProfile.php`
- `app/Models/StaffDesignation.php`
- `app/Models/ClinicStation.php`
- `database/seeders/StaffDesignationSeeder.php`
- `database/seeders/ClinicStationSeeder.php`
- `database/seeders/RolePermissionsSeeder.php`
- `database/migrations/2026_04_22_094900_add_nurse_role_and_permissions.php`

### Generated evidence files

- `docs/staff-module-submodule-map.json`
- `docs/staff-current-effective-access.json`
- `docs/staff-designation-station-effective-matrix.json`

---

## 3. Current Enforcement Flow (Verified)

1. Staff routes are grouped by `role:staff|nurse` in `routes/staff.php`.
2. Each module route group has `staff.module:*` middleware.
3. `EnsureStaffModuleAccess` resolves module context and calls `canStaffAccessModule()`.
4. `canStaffAccessModule()` logic for staff/nurse:
    - `dashboard` always allowed.
    - `settings` always denied.
    - Must pass designation whitelist.
    - For operational modules (`patients`, `queue`, `consultations`, `prescriptions`, `lab_requests`, `certificates`, `inventory`, `dispensing`), station whitelist must also pass.
    - `clinic_head` bypasses station check after designation check.
5. Menus/submenus also use `canStaffAccessModule()` so UI and route guards are aligned.

Validation result: every `staff/*` route currently has `EnsureStaffModuleAccess` middleware (no unguarded staff-prefixed route found).

---

## 4. Deep Findings

## Finding A (High): Real staff assignment mismatch is already causing over-restriction

From live data (`docs/staff-current-effective-access.json`):

- Staff id 5 is `pharmacist` assigned to `triage_area`.
- Effective modules become only:
    - `dashboard`
    - `reports`
    - `notifications`
- Pharmacist core operational modules (`inventory`, `dispensing`, `prescriptions`) are blocked because station is not `pharmacy`.

Impact:

- Staff can appear "broken" or missing expected modules even if designation is correct.
- This is not a code bug; it is a designation-station pairing issue.

## Finding B (High): Nurse role exists, but actual staff users are all under `staff` role

From DB role distribution:

- `staff`: 6 users
- `nurse`: 0 users

Impact:

- Current access safety relies heavily on `staff.module` middleware and helper maps.
- Permission layer is broad for `staff` role (`manage_doctors`, `manage_medicines`, etc.).
- If a future staff route misses `staff.module`, a `staff` user could inherit broader permission-based access than intended by designation/station.

## Finding C (Medium): Staff routes include admin-like modules that are effectively unreachable

`routes/staff.php` contains module groups for:

- `settings`
- `roles`
- `countries`
- `cms`

But designation map does not include these modules for any staff designation, and `settings` is explicitly denied for all staff/nurse.

Impact:

- Dead/stale route surface increases maintenance risk and confusion.
- Future edits may mistakenly assume these are intended to be reachable for staff.

## Finding D (Medium): Notifications submenu can link to inventory-only pages

In menu notifications section:

- "Expiry medicine alert" links to inventory page.

If user has `notifications` but lacks `inventory` (example: pharmacist assigned outside pharmacy), the click leads to 403.

Impact:

- UX inconsistency and support noise.

## Finding E (Low): Station map contains non-station-scoped module key (`reports`)

`records_area` station map includes `reports`, but `reports` is not station-scoped in `canStaffAccessModule()`.

Impact:

- No security issue, but can mislead maintainers into thinking reports are station-gated.

---

## 5. Current Data Snapshot (Live)

## Staff designation usage

- clinic_head: 1
- nurse: 1
- pharmacist: 1
- triage_officer: 1
- clinic_staff: 2
- records_officer: 0

## Station usage

- front_desk: 1
- triage_area: 2
- medical_consultation: 0
- pharmacy: 1
- records_area: 1
- observation_room: 0
- isolation_room: 1

## Staff users effective modules (current)

- id 2 (`clinic_staff`, `records_area`): dashboard, patients, certificates, reports
- id 3 (`clinic_head`, `pharmacy`): dashboard, patients, queue, consultations, prescriptions, lab_requests, certificates, inventory, dispensing, reports, notifications, doctors, specializations
- id 4 (`triage_officer`, `triage_area`): dashboard, patients, queue, consultations, reports
- id 5 (`pharmacist`, `triage_area`): dashboard, reports, notifications
- id 6 (`nurse`, `isolation_room`): dashboard, patients, queue, consultations, reports
- id 7 (`clinic_staff`, `front_desk`): dashboard, patients, queue, certificates, reports

---

## 6. Recommended Strict Access Policy (Module + Submodule)

## 6.1 Designation baseline modules (role intent)

- `clinic_head`
    - Modules: dashboard, patients, queue, consultations, prescriptions, lab_requests, certificates, inventory, dispensing, reports, notifications, doctors, specializations
- `nurse`
    - Modules: dashboard, patients, queue, consultations, lab_requests, certificates, reports
- `pharmacist`
    - Modules: dashboard, inventory, dispensing, prescriptions, reports, notifications
- `triage_officer`
    - Modules: dashboard, patients, queue, consultations, reports
- `clinic_staff`
    - Modules: dashboard, patients, queue, certificates, reports
- `records_officer`
    - Modules: dashboard, patients, consultations, certificates, reports

## 6.2 Station operational modules (work area intent)

- `front_desk`: patients, queue, certificates
- `triage_area`: patients, queue, consultations
- `medical_consultation`: consultations, prescriptions, lab_requests
- `pharmacy`: inventory, dispensing, prescriptions
- `records_area`: patients, certificates
- `observation_room`: patients, queue
- `isolation_room`: patients, queue, consultations

Notes:

- Effective access should be intersection of designation + station for operational modules.
- `reports` and `notifications` should remain designation-based.

## 6.3 Recommended designation-station allowed pairings

- `clinic_head` -> any station (by policy)
- `pharmacist` -> pharmacy only
- `records_officer` -> records_area only
- `clinic_staff` -> front_desk, records_area
- `triage_officer` -> triage_area, isolation_room, observation_room
- `nurse` -> triage_area, medical_consultation, isolation_room, observation_room

Optional:

- allow `nurse` at `front_desk` for surge operations only

## 6.4 Submodule whitelist by module (recommended)

- `patients`
    - Submodules: patients index/show/create/edit/update, history, restore, email verification actions
- `queue`
    - Submodules: patient-queue index/create/store/edit/update/show, call-next, complete, refresh
- `consultations`
    - Submodules: document-issuances (consultation mode), search-users, get-last-consultation, consultation pdf export
- `certificates`
    - Submodules: document-issuances (certificate mode), search-users, get-last-medical-certificate, certificate pdf export
- `prescriptions`
    - Submodules: prescriptions index/show/create/edit/update/store, prescription-medicine, prescription-pdf, dispense
- `lab_requests`
    - Submodules: lab-requests index/show/create/edit/update/store, update-status, pdf, search-users
- `inventory`
    - Submodules: categories, generics, medicines, stock-in, export-stock-in, medicine-inventory-tracking, medicine lookups
- `dispensing`
    - Submodules: medicine-dispensing-management, used-medicine, dispense-records, dispense-records-pdf, medicine-history redirects
- `reports`
    - Submodules: activity-logs index/show/export
- `notifications`
    - Submodules: low-stock alert, system notifications, expiry alerts (inventory-linked)
- `doctors` (clinic_head only)
    - Submodules: doctors CRUD, add-qualification, doctor-status, reset-password
- `specializations` (clinic_head only)
    - Submodules: specializations CRUD

Admin-only for staff/nurse (recommended blocked):

- `settings`
- `roles`
- `countries`
- `states`
- `cities`
- `cms`
- `staffs`

---

## 7. Pre-Implementation Recommendations (Historical)

1. Correct current mismatched assignment immediately:
    - Staff id 5 (`pharmacist`) should be moved to `pharmacy` station.
2. Enforce designation-station pairing at create/update time:
    - reject incompatible combinations in `CreateStaffRequest` / `UpdateStaffRequest`.
3. Keep all staff routes protected by `staff.module` (already true), and add tests to prevent future unguarded route additions.
4. Remove or explicitly document unreachable staff route groups (`settings`, `roles`, `countries`, `cms`) to reduce confusion.
5. In notifications submenu, conditionally show inventory-linked alert only if inventory module is accessible.
6. Consider real use of `nurse` role (currently unused) if you want permission layer and designation intent to align.

---

## 8. Ready-to-Apply Next Step (If Approved)

Status: completed on 2026-05-05.

Implemented in this workspace:

- Designation-station enforcement at request validation:
    - `app/Rules/ValidStaffDesignationStationPair.php` (new)
    - `app/Http/Requests/CreateStaffRequest.php`
    - `app/Http/Requests/UpdateStaffRequest.php`
- Policy helpers for designation-station pairing:
    - `app/helpers.php`
    - Added `getStaffDesignationStationMap()`
    - Added `canStaffDesignationWorkAtStation()`
- Station map cleanup:
    - `app/helpers.php`
    - `records_area` operational map now excludes `reports` (reports is designation-based, not station-scoped)
- Notifications submenu guard fix:
    - `resources/views/layouts/menu.blade.php`
    - "Expiry medicine alert" link is now shown only when `canStaffAccessModule('inventory')` is true
- Route cleanup for unreachable admin-like modules under `staff/*`:
    - `routes/staff.php`
    - Removed staff namespace groups for `settings`, `roles`, `countries`, and `cms/banner`
- Automated policy tests:
    - `tests/Feature/StaffModuleAccessPolicyTest.php` (new)
    - Covers designation/station module behavior, key route middleware guards, staff route guard completeness, and removed admin-only staff routes
    - Test run result: 5 passed

## 9. Post-Enforcement Notes

1. Existing data mismatch still requires admin correction:
    - current pharmacist account assigned to `triage_area` should be reassigned to `pharmacy`.
2. Validation now prevents creating/updating invalid designation-station combinations.
3. Route and UI policy are now tighter and aligned with the access model described in this audit.

## 10. Doctor Consultation Hotfix (May 5, 2026)

Issue observed during doctor edit consultation flow:

- Add Medicines to Plan attempted to call `/admin/medicines-by-category`, returning 403 for doctor accounts.
- Frontend then attempted to parse the 403 HTML response as JSON, causing `Unexpected token '<'`.

Applied fixes:

1. Role-aware medicines endpoint in consultation scripts:
    - `resources/views/document_issuances/edit.blade.php`
    - `resources/views/document_issuances/forms/consultation_form.blade.php`
    - Replaced hardcoded `route("medicines.by.category")` usage with `getRouteByRole("medicines.by.category")`.
2. Added stricter fetch handling with JSON content-type validation to prevent misleading parse errors.
3. Enforced doctor-only plan medicine manipulation:
    - Frontend: Plan medicine add control on edit page is now doctor-only.
    - Backend: `app/Http/Controllers/DocumentIssuanceController.php`
        - non-doctor submissions for `used_for = plan` are ignored in both create (`handleMedicineDeduction`) and edit (`handleMedicineUpdates`).

Result:

- Doctor account now hits doctor-scoped medicine endpoint and can add plan medicines.
- Non-doctor users cannot add or mutate plan medicines through UI or direct payload tampering.
