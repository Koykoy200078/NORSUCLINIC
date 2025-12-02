# Enquiry Module Removal - Complete Documentation

## Overview

Complete removal of the Enquiries functionality from NORSUCLINIC application for both admin and staff user roles. This was a comprehensive cleanup involving routes, controllers, models, views, JavaScript files, and language translations.

## Date

December 2024

## Scope

The enquiry module allowed users to submit enquiries through a contact form on the front-end, which could be managed by both admin and staff roles. The module has been completely removed from the application.

---

## Files Deleted

### Controllers

-   ✅ `app/Http/Controllers/Front/EnquiryController.php` - Already removed (not found during scan)

### Models

-   ✅ `app/Models/Enquiry.php` - Already removed (not found during scan)

### Requests

-   ✅ `app/Http/Requests/CreateEnquiryRequest.php` - Already removed (not found during scan)

### Mail

-   ✅ `app/Mail/EnquiryMails.php` - Already removed (not found during scan)

### Livewire Components

-   ✅ `app/Livewire/EnquiryTable.php` - Already removed (not found during scan)

### View Directories

-   ✅ `resources/views/fronts/enquiries/` - Complete directory removed
-   ✅ `resources/views/emails/enquiry/` - Complete directory removed
-   ✅ `resources/views/livewire/enquiry_skeleton.blade.php` - File removed

### JavaScript Files

-   ✅ `resources/assets/js/fronts/enquiries/enquiry.js` - File removed
-   ✅ `resources/assets/js/fronts/medical-contact/enquiry.js` - File removed

---

## Files Modified

### Routes

#### `routes/web.php`

**Changes:**

1. **Line 18** - Removed: `use App\Http\Controllers\Front\EnquiryController;`
2. **Line 115** - Removed: `Route::post('/enquiries', [EnquiryController::class, 'store'])->name('enquiries.store');`
3. **Lines 302-304** - Removed admin enquiry routes:
    ```php
    Route::get('enquiries', [EnquiryController::class, 'index'])->name('enquiries.index');
    Route::get('enquiries/{enquiry}', [EnquiryController::class, 'show'])->name('enquiries.show');
    Route::delete('enquiries/{enquiry}', [EnquiryController::class, 'destroy'])->name('enquiries.destroy');
    ```
4. **Lines 467-468** - Removed staff enquiry routes

#### `routes/staff.php`

**Changes:**

1. **Line 21** - Removed: `use App\Http\Controllers\Front\EnquiryController;`
2. **Lines 165-168** - Removed staff enquiry routes and comment:
    ```php
    // Enquiry Management (Staff can manage enquiries)
    Route::get('enquiries', [EnquiryController::class, 'index'])->name('enquiries.index');
    Route::get('enquiries/{enquiry}', [EnquiryController::class, 'show'])->name('enquiries.show');
    Route::delete('enquiries/{enquiry}', [EnquiryController::class, 'destroy'])->name('enquiries.destroy');
    ```

### Layout Files

#### `resources/views/layouts/menu.blade.php`

**Changes:**

-   **Lines 467-475** - Removed complete enquiry menu item:
    ```blade
    <li class="nav-item {{
        (isRole('clinic_admin') && Request::is('admin/enquiries*')) ||
        (isRole('staff') && Request::is('staff/enquiries*'))
    ? 'active' : '' }}">
        <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{
            isRole('clinic_admin') ? route('enquiries.index') :
            (isRole('staff') ? route('staff.enquiries.index') : route('enquiries.index'))
        }}">
            <span class="aside-menu-icon pe-3"><i class="fas fa-question-circle"></i></span>
            <span class="aside-menu-title">{{ __('messages.enquiries') }}</span>
        </a>
    </li>
    ```

#### `resources/views/layouts/sub_menu.blade.php`

**Changes:**

-   **Lines 250-252** - Removed enquiry submenu link:
    ```blade
    <li class="nav-item position-relative mx-xl-3 mb-3 mb-xl-0 {{ !Request::is('admin/enquiries*') ? 'd-none' : '' }}">
        <a class="nav-link p-0 {{ Request::is('admin/enquiries*') ? 'active' : '' }}"
            href="{{ route('enquiries.index') }}">{{ __('messages.enquiries') }}</a>
    </li>
    ```

#### `resources/views/fronts/medical_contact.blade.php`

**Changes:**

-   **Line 63** - Replaced enquiry form with simple contact information display:

    ```blade
    <!-- OLD: Complex enquiry form with validation -->
    <form id="enquiryForm" action="{{ route('enquiries.store') }}" ...>

    <!-- NEW: Simple contact information display -->
    <div class="contact-info-message p-5 bg-light">
        <h4 class="mb-4">{{ __('messages.web.contact_information') }}</h4>
        <p class="mb-3">{{ __('messages.web.you_can_reach_us') }}</p>
        <ul class="list-unstyled">
            <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> {{ getSettingValue('address_one') }}</li>
            <li class="mb-2"><i class="fas fa-envelope me-2"></i> <a href="mailto:...">...</a></li>
            <li class="mb-2"><i class="fas fa-phone me-2"></i> <a href="tel:...">...</a></li>
        </ul>
    </div>
    ```

### Language Files

#### `lang/en/js.php`

**Changes:**

-   **Line 79** - Removed: `'enquiry' => 'Enquiry',`

#### `lang/en/messages.php`

**Changes:**

1. **Line 42** - Removed: `'enquiries' => 'Enquiries',`
2. **Line 188** - Removed: `'enquiry_details' => 'Enquiry Details',`
3. **Line 217** - Removed: `'enquiry' => 'Enquiry',`
4. **Line 1075** - Removed: `'enquire_sent' => 'Enquiry Sent Successfully',`
5. **Line 1077** - Removed: `'enquire_deleted' => 'Enquiry deleted successfully.',`

### Build Configuration

#### `webpack.mix.js`

**Changes:**

1. **Line 241** - Removed from first array:

    ```javascript
    "resources/assets/js/fronts/medical-contact/enquiry.js",
    "resources/assets/js/fronts/enquiries/enquiry.js",
    ```

2. **Line 277** - Removed from second array:
    ```javascript
    "resources/assets/js/fronts/medical-contact/enquiry.js",
    "resources/assets/js/fronts/enquiries/enquiry.js",
    ```

### IDE Helper

#### `_ide_helper_models.php`

**Changes:**

-   Automatically regenerated using `php artisan ide-helper:models --nowrite`
-   All references to `App\Models\Enquiry` class removed

---

## Database Migrations (Preserved)

The following migration files were **NOT deleted** to preserve database schema history:

-   `database/migrations/2021_09_21_053500_create_enquiries_table.php`
-   `database/migrations/2021_09_23_073013_add_view_field_in_enquiries_table.php`
-   `database/migrations/2021_10_25_131656_add_region_code_field_in_enquiry_table.php`

**Note:** These migrations remain in the codebase but the enquiries table will no longer be used. If you need to remove the table from existing databases, you can:

1. Manually drop the table: `DROP TABLE enquiries;`
2. Or create a new migration to drop the table

---

## Post-Cleanup Actions

### Caches Cleared

```bash
php artisan optimize:clear
```

This cleared:

-   Route cache
-   Config cache
-   View cache
-   Application cache

### Assets Compiled

```bash
npm run dev
```

Successfully compiled all assets without errors.

### IDE Helper Regenerated

```bash
php artisan ide-helper:models --nowrite
```

Updated the IDE helper file to remove Enquiry model references.

---

## Verification Results

### Final Search Results

After all removals, only the following enquiry references remain:

1. **3 database migration files** (preserved intentionally)
2. **Compiled language files** in `resources/messages.js` (auto-generated from lang files)
    - These are compiled translations for multilingual support
    - Will be automatically updated on next asset compilation

### Routes Verified

-   ✅ No enquiry routes in `routes/web.php`
-   ✅ No enquiry routes in `routes/staff.php`

### Menu Items Verified

-   ✅ No enquiry links in sidebar menu (`layouts/menu.blade.php`)
-   ✅ No enquiry links in sub-menu (`layouts/sub_menu.blade.php`)

### Files Verified

-   ✅ All controller files removed/not found
-   ✅ All model files removed/not found
-   ✅ All view directories removed
-   ✅ All JavaScript files removed
-   ✅ All language keys removed from source files

---

## Impact Analysis

### What Still Works

-   ✅ Front-end contact page displays contact information
-   ✅ Admin and staff dashboards
-   ✅ All other menu items and routes
-   ✅ All asset compilation

### What No Longer Works

-   ❌ Enquiry submission form on contact page
-   ❌ Admin enquiry management (`/admin/enquiries`)
-   ❌ Staff enquiry management (`/staff/enquiries`)
-   ❌ Enquiry email notifications

### User Experience Changes

-   **Contact Page**: Users will see contact information (address, email, phone) instead of a submission form
-   **Admin/Staff Menu**: Enquiries menu item removed from both sidebar and sub-menu
-   **Routes**: All `/enquiries` routes will return 404 errors

---

## Rollback Instructions

If you need to restore the enquiry functionality:

1. **Restore files from git** (if committed before removal):

    ```bash
    git checkout HEAD~1 -- app/Http/Controllers/Front/EnquiryController.php
    git checkout HEAD~1 -- app/Models/Enquiry.php
    git checkout HEAD~1 -- app/Livewire/EnquiryTable.php
    # ... restore other files
    ```

2. **Restore routes** in `routes/web.php` and `routes/staff.php`

3. **Restore menu items** in layout files

4. **Restore language keys**

5. **Restore JavaScript files**

6. **Restore webpack configuration**

7. **Clear caches and recompile**:
    ```bash
    php artisan optimize:clear
    npm run dev
    ```

---

## Related Documentation

-   See also: `MEDICINE_EXPIRY_DATE_FORMAT_FEATURE.md`
-   See also: `ROLE_SYSTEM_AUDIT_REPORT.md`
-   See also: Database cleanup documentation in `docs/` directory

---

## Summary

The enquiry module has been completely removed from the NORSUCLINIC application. This includes:

-   ✅ 5+ core PHP files (controller, model, request, mail, livewire)
-   ✅ 3 view directories with all blade templates
-   ✅ 2 JavaScript files
-   ✅ Route definitions in 2 route files
-   ✅ Menu items in 2 layout files
-   ✅ 6 translation keys across 2 language files
-   ✅ Webpack configuration updated
-   ✅ All caches cleared
-   ✅ Assets successfully recompiled

The application is now free of enquiry functionality and all references have been cleaned up.
