# Role-Based Routing Analysis - Complete Workspace Scan

## 📊 Scan Summary

**Date**: October 3, 2025  
**Scan Scope**: Admin, Staff, and Doctor roles (Patient routes excluded)  
**Total Files Scanned**: 150+ files  
**Files Requiring Updates**: 45+ files

---

## ✅ Modules Already Role-Aware (No Action Needed)

These modules already have proper role-based routing implemented:

### 1. **Purchase Medicines** ✅

-   **Controller**: `PurchaseMedicineController` - Already role-aware
-   **Views**: All views updated with role-based routes
-   **Status**: **COMPLETE**

### 2. **Request Documents** ✅

-   **Controller**: `RequestDocumentController` - Already role-aware
-   **Views**: All views use role-based routing
-   **Files**: `view.blade.php`, `edit.blade.php`, `forms/*`
-   **Status**: **COMPLETE**

### 3. **Medicines** ✅

-   **Controller**: `MedicineController` - Has `getMedicineIndexRoute()` method
-   **Redirect**: Already uses role-aware redirect
-   **Status**: **COMPLETE** (Views need update)

### 4. **Services** ✅ (Partial)

-   **Views**: Add button and action already role-aware
-   **Status**: **Controller needs update**

### 5. **Doctor Sessions** ✅ (Partial)

-   **Views**: Most views already role-aware
-   **Status**: **Some views and controller need update**

### 6. **Visits** ✅ (Partial)

-   **Controller**: `VisitController` - Already has role-aware redirects
-   **Views**: Mixed - some role-aware, some hardcoded
-   **Status**: **Views need consistency**

### 7. **Roles** ✅ (Partial)

-   **Views**: Fields already role-aware
-   **Controller**: Needs update
-   **Status**: **Controller needs update**

---

## ❌ Modules Needing Role-Based Routing

### Priority 1: Critical (User-facing CRUD operations)

#### 1. **Brands** ⚠️

**Routes Available**:

-   Admin: `brands.{index|create|store|edit|update|show|destroy}`
-   Staff: `staff.brands.{index|create|store|edit|update|show|destroy}`
-   Doctor: `doctors.brands.{index|create|store|edit|update|show|destroy}`

**Files Needing Updates**:

| File                                          | Line(s)    | Current                                           | Needs Change           |
| --------------------------------------------- | ---------- | ------------------------------------------------- | ---------------------- |
| `app/Http/Controllers/BrandController.php`    | 63, 98     | `route('brands.index')`                           | Role-aware redirect    |
| `resources/views/brands/create.blade.php`     | 12         | `route('brands.index')`                           | Role-aware back button |
| `resources/views/brands/edit.blade.php`       | 12         | `route('brands.index')`                           | Role-aware back button |
| `resources/views/brands/show.blade.php`       | 11, 12     | `route('brands.edit')`, `route('brands.index')`   | Role-aware links       |
| `resources/views/brands/fields.blade.php`     | 27         | `route('brands.index')`                           | Role-aware cancel      |
| `resources/views/brands/create.blade.php`     | 30         | `Form::open(['route' => 'brands.store'])`         | Role-aware form action |
| `resources/views/brands/add-button.blade.php` | 10, 14, 19 | `route('brands.create')`, `route('brands.excel')` | Role-aware buttons     |
| `resources/views/brands/action.blade.php`     | 3          | `route('brands.edit')`                            | Role-aware edit button |

**Total**: 8 files, ~15 changes needed

---

#### 2. **Categories** ⚠️

**Routes Available**:

-   Admin: `categories.{index|create|store|edit|update|show|destroy}`
-   Staff: `staff.categories.{index|create|store|edit|update|show|destroy}`
-   Doctor: `doctors.categories.{index|create|store|edit|update|show|destroy}`

**Files Needing Updates**:

| File                                          | Line(s) | Current                     | Needs Change                 |
| --------------------------------------------- | ------- | --------------------------- | ---------------------------- |
| `app/Http/Controllers/CategoryController.php` | N/A     | Returns JsonResponse only   | ✅ No redirect needed        |
| `resources/views/categories/create.blade.php` | 6, 21   | Breadcrumb + Form           | Role-aware form & breadcrumb |
| `resources/views/categories/edit.blade.php`   | 6       | Breadcrumb                  | Role-aware breadcrumb        |
| `resources/views/categories/show.blade.php`   | 12      | `route('categories.index')` | Role-aware back button       |
| `resources/views/categories/fields.blade.php` | 10      | `route('categories.index')` | Role-aware cancel            |
| `resources/views/categories/index.blade.php`  | 11      | `route('categories.store')` | Role-aware form action       |

**Total**: 5 files, ~8 changes needed

---

#### 3. **Medicines** ⚠️

**Routes Available**:

-   Admin: `medicines.{index|create|store|edit|update|show|destroy}`
-   Staff: `staff.medicines.{index|create|store|edit|update|show|destroy}`
-   Doctor: `doctors.medicines.{index|create|store|edit|update|show|destroy}`

**Controller Status**: ✅ Already has `getMedicineIndexRoute()` helper

**Files Needing Updates**:

| File                                             | Line(s)    | Current                                                 | Needs Change            |
| ------------------------------------------------ | ---------- | ------------------------------------------------------- | ----------------------- |
| `resources/views/medicines/create.blade.php`     | 9          | `route('medicines.index')`                              | Role-aware back button  |
| `resources/views/medicines/edit.blade.php`       | 9          | `route('medicines.index')`                              | Role-aware back button  |
| `resources/views/medicines/show.blade.php`       | 12, 13     | `route('medicines.edit')`, `route('medicines.index')`   | Role-aware links        |
| `resources/views/medicines/fields.blade.php`     | 66         | `route('medicines.index')`                              | Role-aware cancel       |
| `resources/views/medicines/add-button.blade.php` | 10, 14, 19 | `route('medicines.create')`, `route('medicines.excel')` | Role-aware buttons      |
| `resources/views/medicines/action.blade.php`     | 2          | `route('medicines.edit')`                               | Role-aware edit button  |
| `resources/views/medicines/index.blade.php`      | 10         | `route('medicines.index')`                              | Role-aware hidden field |

**Total**: 7 files, ~12 changes needed

---

#### 4. **Services** ⚠️

**Routes Available**:

-   Admin: `services.{index|create|store|edit|update|destroy}`
-   Staff: `staff.services.{index|create|store|edit|update|destroy}`
-   Doctor: `doctors.services.{index|create|store|edit|update|destroy}`

**Controller Status**: ❌ Needs update

**Files Needing Updates**:

| File                                                       | Line(s) | Current                   | Needs Change           |
| ---------------------------------------------------------- | ------- | ------------------------- | ---------------------- |
| `app/Http/Controllers/ServiceController.php`               | 63, 90  | `route('services.index')` | Role-aware redirect    |
| `resources/views/services/create.blade.php`                | 10      | `route('services.index')` | Role-aware back button |
| `resources/views/services/edit.blade.php`                  | 10      | `route('services.index')` | Role-aware back button |
| `resources/views/services/fields.blade.php`                | 89      | `route('services.index')` | Role-aware cancel      |
| `resources/views/services/components/add_button.blade.php` | -       | ✅ Already role-aware     | No change              |
| `resources/views/services/components/action.blade.php`     | -       | ✅ Already role-aware     | No change              |

**Total**: 4 files, ~6 changes needed

---

#### 5. **Medicine Bills** ⚠️

**Routes Available**:

-   Admin: `medicine-bills.{index|create|store|edit|update|show|destroy}`
-   Staff: `staff.medicine-bills.{index|create|store|edit|update|show|destroy}`
-   Doctor: `doctors.medicine-bills.{index|create|store|edit|update|show|destroy}`

**Files Needing Updates**:

| File                                                      | Line(s) | Current                                                        | Needs Change             |
| --------------------------------------------------------- | ------- | -------------------------------------------------------------- | ------------------------ |
| `app/Http/Controllers/MedicineBillController.php`         | TBD     | Check for redirects                                            | Role-aware redirects     |
| `resources/views/medicine-bills/add-button.blade.php`     | 2       | `route('medicine-bills.create')`                               | Role-aware create button |
| `resources/views/medicine-bills/columns/action.blade.php` | 3, 7    | `route('medicine-bills.show')`, `route('medicine-bills.edit')` | Role-aware links         |

**Total**: 3 files, ~5 changes needed

---

#### 6. **Roles** ⚠️

**Routes Available**:

-   Admin: `roles.{index|create|store|edit|update|destroy}`
-   Staff: `staff.roles.{index|create|store|edit|update|destroy}`
-   Doctor: `doctors.roles.{index|create|store|edit|update|destroy}`

**Files Needing Updates**:

| File                                      | Line(s) | Current                | Needs Change           |
| ----------------------------------------- | ------- | ---------------------- | ---------------------- |
| `app/Http/Controllers/RoleController.php` | 61, 87  | `route('roles.index')` | Role-aware redirect    |
| `resources/views/roles/create.blade.php`  | 13      | `route('roles.index')` | Role-aware back button |
| `resources/views/roles/edit.blade.php`    | 10      | `route('roles.index')` | Role-aware back button |
| `resources/views/roles/fields.blade.php`  | -       | ✅ Already role-aware  | No change              |

**Total**: 3 files, ~4 changes needed

---

#### 7. **Staffs** ⚠️

**Routes Available**:

-   Admin: `staffs.{index|create|store|edit|update|show|destroy}`

**Note**: Staffs is ADMIN ONLY - No staff/doctor routes

**Files Needing Updates**:

| File                                       | Line(s) | Current                 | Needs Change                    |
| ------------------------------------------ | ------- | ----------------------- | ------------------------------- |
| `app/Http/Controllers/StaffController.php` | 61, 97  | `route('staffs.index')` | ✅ Already correct (admin only) |
| `resources/views/staffs/create.blade.php`  | 10      | `route('staffs.index')` | ✅ Already correct              |
| `resources/views/staffs/edit.blade.php`    | 10      | `route('staffs.index')` | ✅ Already correct              |
| `resources/views/staffs/fields.blade.php`  | 108     | `route('staffs.index')` | ✅ Already correct              |
| `resources/views/staffs/show.blade.php`    | 10      | `route('staffs.edit')`  | ✅ Already correct              |

**Total**: ✅ **No changes needed** (admin-only module)

---

#### 8. **Visits** ⚠️

**Routes Available**:

-   Admin: `visits.{index|create|store|edit|update|show|destroy}`
-   Staff: `staff.visits.{index|create|store|edit|update|show|destroy}`
-   Doctor: `doctors.visits.{index|create|store|edit|update|show|destroy}`

**Controller Status**: ✅ Already has role-aware redirects

**Files Needing Updates**:

| File                                                     | Line(s) | Current                                        | Needs Change              |
| -------------------------------------------------------- | ------- | ---------------------------------------------- | ------------------------- |
| `resources/views/visits/create.blade.php`                | 10, 13  | Mixed role logic                               | Standardize to role-aware |
| `resources/views/visits/edit.blade.php`                  | 10, 13  | Mixed role logic                               | Standardize to role-aware |
| `resources/views/visits/fields.blade.php`                | 30      | ✅ Already role-aware                          | No change                 |
| `resources/views/visits/show.blade.php`                  | 14      | ✅ Already role-aware                          | No change                 |
| `resources/views/visits/components/action.blade.php`     | 2, 7    | `route('visits.show')`, `route('visits.edit')` | Role-aware links          |
| `resources/views/visits/components/add_button.blade.php` | -       | ✅ Already role-aware                          | No change                 |
| `resources/views/visits/doctor_panel/*`                  | -       | ✅ Already role-aware                          | No change                 |

**Total**: 3 files, ~4 changes needed

---

### Priority 2: Admin Configuration (Less Critical)

#### 9. **Doctor Sessions** ⚠️

**Routes Available**:

-   Admin: `doctor-sessions.{index|create|store|edit|update|destroy}`
-   Staff: `staff.doctor-sessions.{index|create|store|edit|update|destroy}`
-   Doctor: `doctors.doctor-sessions.{index|create|store|edit|update|destroy}`

**Files Needing Updates**:

| File                                                              | Line(s)  | Current                          | Needs Change                  |
| ----------------------------------------------------------------- | -------- | -------------------------------- | ----------------------------- |
| `app/Http/Controllers/DoctorSessionController.php`                | 109, 321 | `route('doctor-sessions.index')` | Role-aware redirect           |
| `resources/views/doctor_sessions/create.blade.php`                | 48-52    | ✅ Form already role-aware       | Check back button consistency |
| `resources/views/doctor_sessions/edit.blade.php`                  | 30       | Conditional URL                  | Standardize                   |
| `resources/views/doctor_sessions/components/action.blade.php`     | -        | ✅ Already role-aware            | No change                     |
| `resources/views/doctor_sessions/components/add_button.blade.php` | -        | ✅ Already role-aware            | No change                     |

**Total**: 3 files, ~4 changes needed

---

#### 10. **Holidays** ⚠️

**Routes Available**:

-   Admin: `holidays.{index|create|store|edit|update|destroy}`
-   Doctor: `doctors.holiday`, `doctors.holiday-create`, `doctors.holiday-store`

**Note**: Staff routes not found - May be admin/doctor only

**Files Needing Updates**:

| File                                              | Line(s)               | Current                   | Needs Change               |
| ------------------------------------------------- | --------------------- | ------------------------- | -------------------------- |
| `app/Http/Controllers/HolidayController.php`      | 74, 78, 151, 158, 162 | Mixed role redirects      | Verify and standardize     |
| `resources/views/doctor_holiday/create.blade.php` | 18                    | `route('holidays.store')` | Check if role-aware needed |

**Total**: 2 files, ~5 changes needed

---

## 📋 Total Summary

| Priority | Module                 | Files | Changes | Status          |
| -------- | ---------------------- | ----- | ------- | --------------- |
| ✅       | Purchase Medicines     | 0     | 0       | Complete        |
| ✅       | Request Documents      | 0     | 0       | Complete        |
| ✅       | Medicines (Controller) | 0     | 0       | Complete        |
| 1        | **Brands**             | 8     | ~15     | Needs work      |
| 1        | **Categories**         | 5     | ~8      | Needs work      |
| 1        | **Medicines (Views)**  | 7     | ~12     | Needs work      |
| 1        | **Services**           | 4     | ~6      | Needs work      |
| 1        | **Medicine Bills**     | 3     | ~5      | Needs work      |
| 1        | **Roles**              | 3     | ~4      | Needs work      |
| 1        | **Visits**             | 3     | ~4      | Needs work      |
| ✅       | Staffs                 | 0     | 0       | Admin only - OK |
| 2        | **Doctor Sessions**    | 3     | ~4      | Needs work      |
| 2        | **Holidays**           | 2     | ~5      | Needs work      |

**Grand Total**: **38 files**, **~63 changes needed**

---

## 🔧 Implementation Pattern

All updates should follow this consistent pattern:

### For Blade Views (Back/Cancel Buttons):

```php
<a href="{{
    isRole('clinic_admin') ? route('module.index') :
    (isRole('staff') ? route('staff.module.index') :
    (isRole('doctor') ? route('doctors.module.index') : route('module.index')))
}}">Back</a>
```

### For Controllers (Redirects):

```php
// Add helper method
private function getModuleIndexRoute(): string
{
    if (isRole('clinic_admin')) {
        return route('module.index');
    } elseif (isRole('staff')) {
        return route('staff.module.index');
    } elseif (isRole('doctor')) {
        return route('doctors.module.index');
    }
    return route('module.index');
}

// Use in store/update methods
return redirect($this->getModuleIndexRoute());
```

### For Form Actions:

```php
{{ Form::open(['route' =>
    isRole('clinic_admin') ? 'module.store' :
    (isRole('staff') ? 'staff.module.store' :
    (isRole('doctor') ? 'doctors.module.store' : 'module.store'))
]) }}
```

---

## ⚡ Quick Reference - Route Naming Convention

| Role                 | Prefix                  | Example                |
| -------------------- | ----------------------- | ---------------------- |
| Admin (clinic_admin) | `module.action`         | `brands.index`         |
| Staff                | `staff.module.action`   | `staff.brands.index`   |
| Doctor               | `doctors.module.action` | `doctors.brands.index` |

---

## 📝 Next Steps

1. **Review this document** - Confirm all modules listed are correct
2. **Prioritize fixes** - Start with Priority 1 (user-facing CRUD)
3. **Create backup** - Commit current state before changes
4. **Apply fixes systematically** - One module at a time
5. **Test each module** - Login as admin, staff, doctor and verify routing
6. **Document changes** - Update this file with completion status

---

**Generated**: October 3, 2025  
**Scan Tool**: VS Code + GitHub Copilot  
**Confidence**: High (Manual verification recommended)
