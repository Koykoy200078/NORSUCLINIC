# Role & Permission — Issues & Action Plan

**Generated:** 2026-04-03 | **Last Verified:** 2026-04-03  
**Reference:** See `ROLE_PERMISSION_AUDIT.md` for full audit details

> **2026-04-03 Fixes Applied:**
>
> - Issue #10 resolved — `manage_staff` removed from staff role (seeder + live DB updated)
> - Issues #1–#9 resolved — `panelRoute()` helper + `window.currentPanel` implemented across all panels
> - Issue #4 resolved — `emailVerified` routes added to staff and doctor panels
> - Issue #5 resolved — Staff delete button guarded with `@if(isRole('clinic_admin'))` + `data-delete-url` attribute pattern
> - Hardcoded `User` type integers fixed in `RequestDocumentTable.php` and `RequestDocumentsController.php`

---

## Severity Legend

- 🔴 **High** — Feature broken / 403 errors for staff or doctor users
- 🟡 **Medium** — Inconsistency / permission assigned but not enforced
- 🟠 **Low** — Cosmetic / unused

---

## Issue #1 ✅ FIXED — Doctor status toggle broken for staff panel

**File:** `resources/assets/js/doctors/doctors.js`  
**Fix applied 2026-04-03:** `route('doctor.status')` → `panelRoute('doctor.status')`

- Admin panel: resolves to `admin/doctor-status` ✅
- Staff panel: resolves to `staff/doctor-status` ✅

---

## Issue #2 ✅ FIXED — Add Qualification broken for staff panel

**File:** `resources/assets/js/doctors/doctors.js`  
**Fix applied 2026-04-03:** `route('add.qualification')` → `panelRoute('add.qualification')`

---

## Issue #3 ✅ FIXED — Resend Email Verification broken for staff/doctor panels

**Files:** `doctors.js`, `staff.js`, `patients.js`  
**Fix applied 2026-04-03:** All three files updated to use `panelRoute('resend.email.verification', id)`.  
`patients.js` also uses `data-verification-url` override — both fallback and override now panel-aware.

---

## Issue #4 ✅ FIXED — Email Verified toggle (staff/doctor routes now exist)

**Files:** `doctors.js`, `staff.js`, `patients.js`  
**Fix applied 2026-04-03:**

1. Added `POST staff/email-verified` route (`staff.emailVerified`) to `routes/staff.php`
2. Added `POST doctors/email-verified` route (`doctors.emailVerified`) to `routes/doctor.php`
3. All three JS files updated to use `panelRoute('emailVerified')`

---

## Issue #5 ✅ FIXED — Staff delete button guarded + data-delete-url added

**File:** `resources/assets/js/staff/staff.js`, `resources/views/staffs/components/action.blade.php`  
**Fix applied 2026-04-03:**

1. `action.blade.php` — Delete button wrapped with `@if(isRole('clinic_admin'))` guard; `data-delete-url="{{ route('staffs.destroy', $row->id) }}"` attribute added
2. `staff.js` — Changed to read `data-delete-url` attribute; returns early if attribute absent (no unsafe route fallback)

---

## Issue #6 ✅ FIXED — Medicine History JS routes

**File:** `resources/assets/js/medicine_history/medicine_bill.js`  
**Fix applied 2026-04-03:** All 6 bare `route()` calls replaced with `panelRoute()`:  
`get-medicine-category`, `get-medicine`, `store.patient`, `medicine-history.destroy`, `medicine-history.update`, `medicine-history.index`

---

## Issue #7 ✅ FIXED — Medicines & Category JS routes

**Files:** `medicines.js`, `category.js`  
**Fix applied 2026-04-03:** All bare `route()` calls replaced with `panelRoute()`:

- `medicines.js`: `check.use.medicine`, `medicines.show.modal`
- `category.js`: `categories.update`, `categories.edit`, `categories.destroy`, `active.deactive`

---

## Issue #8 🟡 — Doctor/Staff `create-edit.js` redirects to admin after save

**File:** `resources/assets/js/doctors/create-edit.js`  
**Fix applied 2026-04-03:**

- `route('doctors.index')` on both cancel (`#ResetForm`) and save success replaced with `panelRoute('doctors.index')`
- Admin → `admin/doctors`, Staff → `staff/doctors`
- Hidden input `#doctorIndexRedirectUrl` override still works as primary redirect.

---

## Issue #8b ✅ FIXED — Specializations JS routes

**File:** `resources/assets/js/specializations/specializations.js`  
**Fix applied 2026-04-03:** All 4 bare `route()` calls replaced with `panelRoute()`:  
`specializations.store`, `specializations.update`, `specializations.destroy`, `specializations.edit`

---

## Issue #9 ✅ FIXED — Prescriptions delete + prescriptions/create-edit.js

**Files:** `resources/assets/js/prescriptions/prescriptions.js`, `resources/assets/js/prescriptions/create-edit.js`  
**Fix applied 2026-04-03:**

- `prescriptions.js`: `route('prescriptions.destroy', id)` → `panelRoute('prescriptions.destroy', id)`
- `create-edit.js`: `route('prescription.medicine.store')` → `panelRoute('prescription.medicine.store')`, `route('get-medicine', id)` → `panelRoute('get-medicine', id)`

---

## Issue #10 ✅ FIXED — Staff had `manage_staff` permission but no staff management panel routes

**Seeder:** `RolePermissionsSeeder.php`  
**Fixed:** 2026-04-03  
**Action taken:** Removed `manage_staff` from staff role in `RolePermissionsSeeder.php`. Seeder re-ran; permission cache flushed. Staff now has 6 permissions (was 7). Live DB confirmed.

> Staff still has the delete button rendered in staff-panel views — see Issue #5 for Blade fix needed.

---

## Issue #11 🟡 — Staff has `manage_roles` but routes are read-only

**Problem:** Staff has `manage_roles` permission but only 2 read-only routes exist in the staff panel:

- `GET staff/roles`
- `GET staff/roles/{role}`

No create/edit/delete role routes in the staff panel.  
**Fix Options:**

1. **Remove** `manage_roles` from staff role permissions if staff should not modify roles
2. **Add** full CRUD role routes to `routes/staff.php`

---

## Issue #12 🟠 — `manage_staff_dashboard` permission unused on `staff/dashboard` route

**Problem:** Staff has `manage_staff_dashboard` permission but `staff/dashboard` has no permission middleware (only role:staff check).  
**Impact:** All staff users can access dashboard regardless of this permission.  
**Fix Options:**

1. Add `PermissionMiddleware:manage_staff_dashboard` to the `staff/dashboard` route
2. Or accept as-is (not causing broken functionality, just inconsistent)

---

## Recommended Fix Order

| Priority | Issue                                 | Status                                              |
| -------- | ------------------------------------- | --------------------------------------------------- |
| 1        | #6 Medicine History JS routes         | ✅ Fixed 2026-04-03                                 |
| 2        | #7 Medicines & Category JS routes     | ✅ Fixed 2026-04-03                                 |
| 3        | #1 Doctor status toggle               | ✅ Fixed 2026-04-03                                 |
| 4        | #2 Add qualification                  | ✅ Fixed 2026-04-03                                 |
| 5        | #8 Specializations JS routes          | ✅ Fixed 2026-04-03                                 |
| 6        | #9 Prescriptions delete               | ✅ Fixed 2026-04-03                                 |
| 7        | #3 Resend email verification          | ✅ Fixed 2026-04-03                                 |
| 8        | #4 Email verified toggle              | ✅ Fixed 2026-04-03                                 |
| 9        | #5 Staff delete staff                 | ✅ Fixed 2026-04-03                                 |
| 10       | #10 manage_staff cleanup              | ✅ Fixed 2026-04-03                                 |
| 11       | #11 `manage_roles` permission cleanup | Open — decide: remove from staff or add CRUD routes |
| 12       | #12 Dashboard permission              | Open — low priority cosmetic                        |

---

## Suggested Panel-Prefix Helper (Global JS Fix Strategy)

Add this to the shared layout Blade file so all JS files can determine which panel they're in:

```blade
{{-- resources/views/layouts/sidebar.blade.php or equivalent --}}
<script>
    window.currentPanel = '{{ isRole("staff") ? "staff" : (isRole("doctor") ? "doctors" : (isRole("patient") ? "patients" : "admin")) }}';
</script>
```

Then in each JS file that uses bare route names, prefix conditionally:

```js
function panelRoute(name, params) {
    const prefix = window.currentPanel;
    const prefixed = prefix + "." + name;
    // Try prefixed first; fall back to bare name for admin
    return prefix === "admin" ? route(name, params) : route(prefixed, params);
}
```
