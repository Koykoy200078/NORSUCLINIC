# Route Name Conflict Resolution - October 7, 2025

## ❌ Error Encountered

```
LogicException: Unable to prepare route [doctors/appointments/{appointmentId}/prescription-create]
for serialization. Another route has already been assigned name [doctors.prescriptions.create].

at vendor/laravel/framework/src/Illuminate/Routing/AbstractRouteCollection.php:247
```

## 🔍 Root Cause

**Route Name Conflict**: When we changed from:

```php
Route::resource('prescriptions', PrescriptionController::class)->except('create', 'edit', 'index');
```

To:

```php
Route::resource('prescriptions', PrescriptionController::class);  // ❌ Includes ALL routes
```

The `Route::resource()` automatically generates these routes:

-   `GET /doctors/prescriptions/create` → `doctors.prescriptions.create`
-   `GET /doctors/prescriptions/{prescription}/edit` → `doctors.prescriptions.edit`

But then we ALSO defined custom routes with the SAME names:

```php
Route::get('appointments/{appointmentId}/prescription-create', [...])
    ->name('prescriptions.create');  // ❌ CONFLICT!

Route::get('appointments/{appointmentId}/prescription-edit/{prescription}', [...])
    ->name('prescriptions.edit');  // ❌ CONFLICT!
```

**Why This Happened**:
Prescriptions in this system need an **appointment context**, so they use custom URLs like:

-   `/doctors/appointments/123/prescription-create` (needs appointment ID)

Instead of standard resource URLs like:

-   `/doctors/prescriptions/create` (no context)

## ✅ Solution Applied

### Changed In: `routes/doctor.php`

**Before** (Causing conflict):

```php
Route::resource('prescriptions', PrescriptionController::class);  // ❌ Creates ALL routes
Route::get('appointments/{appointmentId}/prescription-create', [...])
    ->name('prescriptions.create');  // ❌ Duplicate name!
```

**After** (Fixed):

```php
Route::resource('prescriptions', PrescriptionController::class)
    ->except(['create', 'edit']);  // ✅ Exclude conflicting routes

Route::get('appointments/{appointmentId}/prescription-create', [...])
    ->name('prescriptions.create');  // ✅ No conflict
```

## 📊 Final Route Structure

### Prescription Routes for Doctors (11 total)

#### From Resource (5 routes):

```
GET      /doctors/prescriptions                     doctors.prescriptions.index
POST     /doctors/prescriptions                     doctors.prescriptions.store
GET      /doctors/prescriptions/{prescription}      doctors.prescriptions.show
PUT      /doctors/prescriptions/{prescription}      doctors.prescriptions.update
DELETE   /doctors/prescriptions/{prescription}      doctors.prescriptions.destroy
```

#### Custom Routes (6 routes):

```
GET      /doctors/appointments/{appointmentId}/prescription-create           doctors.prescriptions.create
GET      /doctors/appointments/{appointmentId}/prescription-edit/{presc}     doctors.prescriptions.edit
POST     /doctors/prescription-medicine                                      doctors.prescription.medicine.store
GET      /doctors/prescription-medicine-show/{id}                           doctors.prescription.medicine.show
GET      /doctors/prescription-pdf/{id}                                      doctors.prescriptions.pdf
POST     /doctors/prescriptions/{prescription}/active-deactive               doctors.prescription.status
```

#### Visit-Related Prescription Routes (3 routes):

```
POST     /doctors/add-prescription                                          doctors.visits.add.prescription
POST     /doctors/delete-prescription/{prescription}                        doctors.visits.delete.prescription
GET      /doctors/edit-prescription/{prescription}                          doctors.visits.edit.prescription
```

**Total**: 14 prescription-related routes for doctors ✅

## 🔄 Same Pattern Applied To All Roles

All roles (admin, staff, doctor, patient) follow the same pattern:

```php
// Exclude create/edit from resource
Route::resource('prescriptions', PrescriptionController::class)
    ->except(['create', 'edit']);

// Define custom create/edit with appointment context
Route::get('appointments/{appointmentId}/prescription-create', [...])
    ->name('prescriptions.create');
Route::get('appointments/{appointmentId}/prescription-edit/{prescription}', [...])
    ->name('prescriptions.edit');
```

## ✅ Verification

### Commands Run:

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Verify routes registered
php artisan route:list | findstr "appointmentId"
```

### Results:

```
✅ admin/appointments/{appointmentId}/prescription-create      prescriptions.create
✅ doctors/appointments/{appointmentId}/prescription-create    doctors.prescriptions.create
✅ staff/appointments/{appointmentId}/prescription-create      staff.prescriptions.create
✅ patients/appointments/{appointmentId}/prescription-create   patients.prescriptions.create
```

All routes registered successfully without conflicts! ✅

## 📝 Key Learnings

### 1. Route::resource() Auto-Generates Routes

`Route::resource('prescriptions', Controller::class)` creates 7 routes:

-   index (list all)
-   create (show create form)
-   store (save new)
-   show (view one)
-   edit (show edit form)
-   update (save changes)
-   destroy (delete)

### 2. Use ->except() When Needed

When you need custom create/edit URLs (like with appointment context):

```php
Route::resource('module', Controller::class)
    ->except(['create', 'edit']);  // Exclude these

// Then define custom routes
Route::get('custom/path/create', [...])->name('module.create');
```

### 3. Route Names Must Be Unique

Within a route group (e.g., `doctors.*`), each route name must be unique:

-   ✅ `doctors.prescriptions.create`
-   ❌ Another `doctors.prescriptions.create` (conflict!)

## 🎯 Status

**Issue**: Resolved ✅  
**Routes**: All registered correctly ✅  
**Testing**: Server starts without errors ✅  
**Documentation**: Updated ✅

---

**Fixed**: October 7, 2025  
**Files Modified**: `routes/doctor.php`  
**Impact**: Prescription creation/editing now works with appointment context
