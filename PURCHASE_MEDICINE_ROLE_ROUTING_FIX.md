# Purchase Medicine Role-Based Routing Fix

## ✅ Issue Fixed

**Problem**: Staff users were being redirected to admin routes (`/admin/medicine-purchase/*`) instead of staff routes (`/staff/medicine-purchase/*`)

**Root Cause**: Routes were hardcoded to admin paths instead of being role-aware

---

## 🔧 Files Updated

### 1. **PurchaseMedicineController.php**

**Location**: `app/Http/Controllers/PurchaseMedicineController.php`

**Change**: Updated `store()` method to redirect based on user role after saving

```php
public function store(CreatePurchaseMedicineRequest $request): RedirectResponse
{
    $input = $request->all();
    $this->prchaseMedicineRepository->store($input);
    flash::success(__('messages.purchase_medicine.purchased_medicine_success'));

    // Redirect based on user role
    if (isRole('clinic_admin')) {
        return redirect(route('medicine-purchase.index'));
    } elseif (isRole('staff')) {
        return redirect(route('staff.medicine-purchase.index'));
    } elseif (isRole('doctor')) {
        return redirect(route('doctors.medicine-purchase.index'));
    }

    return redirect(route('medicine-purchase.index'));
}
```

---

### 2. **create.blade.php**

**Location**: `resources/views/purchase-medicines/create.blade.php`

**Changes**:

-   ✅ Updated **Back Button** to use role-aware route
-   ✅ Updated **Form Action** to submit to correct role route

```php
// Back Button
<a href="{{
    isRole('clinic_admin') ? route('medicine-purchase.index') :
    (isRole('staff') ? route('staff.medicine-purchase.index') :
    (isRole('doctor') ? route('doctors.medicine-purchase.index') : route('medicine-purchase.index')))
}}">

// Form Action
{{ Form::open(['route' =>
    isRole('clinic_admin') ? 'medicine-purchase.store' :
    (isRole('staff') ? 'staff.medicine-purchase.store' :
    (isRole('doctor') ? 'doctors.medicine-purchase.store' : 'medicine-purchase.store'))
]) }}
```

---

### 3. **fields.blade.php**

**Location**: `resources/views/purchase-medicines/fields.blade.php`

**Change**: Updated **Cancel Button** to redirect based on role

```php
<a href="{!!
    isRole('clinic_admin') ? route('medicine-purchase.index') :
    (isRole('staff') ? route('staff.medicine-purchase.index') :
    (isRole('doctor') ? route('doctors.medicine-purchase.index') : route('medicine-purchase.index')))
!!}" class="btn btn-secondary">Cancel</a>
```

---

### 4. **action.blade.php**

**Location**: `resources/views/purchase-medicines/action.blade.php`

**Changes**: Updated **Action Dropdown** menu items

-   ✅ Purchase Medicine link
-   ✅ Export to Excel link

```php
// Purchase Medicine Link
<a href="{{
    isRole('clinic_admin') ? route('medicine-purchase.create') :
    (isRole('staff') ? route('staff.medicine-purchase.create') :
    (isRole('doctor') ? route('doctors.medicine-purchase.create') : route('medicine-purchase.create')))
}}">

// Export to Excel Link
<a href="{{
    isRole('clinic_admin') ? route('purchase-medicine.excel') :
    (isRole('staff') ? route('staff.purchase-medicine.excel') :
    (isRole('doctor') ? route('doctors.purchase-medicine.excel') : route('purchase-medicine.excel')))
}}">
```

---

### 5. **show.blade.php**

**Location**: `resources/views/purchase-medicines/show.blade.php`

**Change**: Updated **Back Button** in purchase medicine details page

```php
<a href="{{
    isRole('clinic_admin') ? route('medicine-purchase.index') :
    (isRole('staff') ? route('staff.medicine-purchase.index') :
    (isRole('doctor') ? route('doctors.medicine-purchase.index') : route('medicine-purchase.index')))
}}" class="btn btn-outline-primary ms-2">Back</a>
```

---

### 6. **columns/action.blade.php**

**Location**: `resources/views/purchase-medicines/columns/action.blade.php`

**Change**: Updated **View Button** in table rows

```php
<a href="{{
    isRole('clinic_admin') ? route('medicine-purchase.show', [$row->id]) :
    (isRole('staff') ? route('staff.medicine-purchase.show', [$row->id]) :
    (isRole('doctor') ? route('doctors.medicine-purchase.show', [$row->id]) : route('medicine-purchase.show', [$row->id])))
}}" class='btn px-2 text-primary fs-3 ps-0'>
    <i class="fas fa-eye text-success"></i>
</a>
```

---

## 📋 Route Mapping

### Admin Routes (clinic_admin role)

```
GET  /admin/medicine-purchase                    → medicine-purchase.index
GET  /admin/medicine-purchase/create             → medicine-purchase.create
POST /admin/medicine-purchase                    → medicine-purchase.store
GET  /admin/medicine-purchase/{id}               → medicine-purchase.show
GET  /admin/export-medicine-purchase             → purchase-medicine.excel
```

### Staff Routes (staff role)

```
GET  /staff/medicine-purchase                    → staff.medicine-purchase.index
GET  /staff/medicine-purchase/create             → staff.medicine-purchase.create
POST /staff/medicine-purchase                    → staff.medicine-purchase.store
GET  /staff/medicine-purchase/{id}               → staff.medicine-purchase.show
GET  /staff/export-medicine-purchase             → staff.purchase-medicine.excel
```

### Doctor Routes (doctor role)

```
GET  /doctors/medicine-purchase                  → doctors.medicine-purchase.index
GET  /doctors/medicine-purchase/create           → doctors.medicine-purchase.create
POST /doctors/medicine-purchase                  → doctors.medicine-purchase.store
GET  /doctors/medicine-purchase/{id}             → doctors.medicine-purchase.show
GET  /doctors/export-medicine-purchase           → doctors.purchase-medicine.excel
```

---

## ✅ Testing Checklist

### Staff User Testing

-   [x] Navigate to medicine purchase list → Should go to `/staff/medicine-purchase`
-   [x] Click "Purchase Medicine" button → Should go to `/staff/medicine-purchase/create`
-   [x] Submit form → Should POST to `/staff/medicine-purchase` and redirect to `/staff/medicine-purchase`
-   [x] Click "Cancel" button → Should redirect to `/staff/medicine-purchase`
-   [x] Click "View" on a record → Should go to `/staff/medicine-purchase/{id}`
-   [x] Click "Back" from details → Should redirect to `/staff/medicine-purchase`
-   [x] Click "Export to Excel" → Should download from `/staff/export-medicine-purchase`

### Doctor User Testing

-   [x] Navigate to medicine purchase list → Should go to `/doctors/medicine-purchase`
-   [x] Click "Purchase Medicine" button → Should go to `/doctors/medicine-purchase/create`
-   [x] Submit form → Should POST to `/doctors/medicine-purchase` and redirect to `/doctors/medicine-purchase`
-   [x] Click "Cancel" button → Should redirect to `/doctors/medicine-purchase`
-   [x] Click "View" on a record → Should go to `/doctors/medicine-purchase/{id}`
-   [x] Click "Back" from details → Should redirect to `/doctors/medicine-purchase`
-   [x] Click "Export to Excel" → Should download from `/doctors/export-medicine-purchase`

### Admin User Testing

-   [x] All routes should use `/admin/medicine-purchase` prefix
-   [x] Form submission should redirect to admin index
-   [x] All navigation should stay within admin routes

---

## 🔍 Pattern Used

All updates follow this consistent pattern:

```php
isRole('clinic_admin') ? route('medicine-purchase.{action}') :
(isRole('staff') ? route('staff.medicine-purchase.{action}') :
(isRole('doctor') ? route('doctors.medicine-purchase.{action}') : route('medicine-purchase.{action}')))
```

This ensures:

-   ✅ **Admin users** use `/admin/*` routes
-   ✅ **Staff users** use `/staff/*` routes
-   ✅ **Doctor users** use `/doctors/*` routes
-   ✅ **Fallback** to admin routes for any other cases

---

## 📝 Notes

1. **Helper Function**: Uses existing `isRole()` helper function already present in the application
2. **Consistent Pattern**: Same pattern applied across all purchase medicine views
3. **All Actions Covered**:
    - Index/List
    - Create
    - Store (form submission)
    - Show/Details
    - Export to Excel
    - Navigation (Back, Cancel buttons)
4. **Controller Updated**: Server-side redirect also updated to match role

---

## 🎯 Result

**Before**: Staff users accessing `/staff/medicine-purchase/create` → Form submits to `/admin/medicine-purchase` ❌

**After**: Staff users accessing `/staff/medicine-purchase/create` → Form submits to `/staff/medicine-purchase` ✅

All users now stay within their respective role routes throughout the entire purchase medicine workflow!

---

**Date**: October 3, 2025  
**Status**: ✅ **COMPLETE**  
**Files Modified**: 6 files  
**Impact**: All purchase medicine routes now role-aware
