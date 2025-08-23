# Enquiry Routes Issue - RESOLVED

## Problem

Laravel log showed error: `Route [staff.enquiries.index] not defined.`

The error was occurring because the menu.blade.php file was trying to generate routes for staff enquiries management, but these routes were not defined in the staff.php routes file.

## Root Cause

The navigation menu in `resources/views/layouts/menu.blade.php` included logic to show enquiry management links for staff users, attempting to route to:

-   `staff.enquiries.index` (line 306)

However, the `routes/staff.php` file did not include the enquiry routes that were available in the admin routes.

## Solution Applied

### 1. Added Missing Import

Added the EnquiryController import to `routes/staff.php`:

```php
use App\Http\Controllers\Front\EnquiryController;
```

### 2. Added Enquiry Routes

Added the following routes to the staff routes file:

```php
// Enquiry Management (Staff can manage enquiries)
Route::get('enquiries', [EnquiryController::class, 'index'])->name('enquiries.index');
Route::get('enquiries/{enquiry}', [EnquiryController::class, 'show'])->name('enquiries.show');
Route::delete('enquiries/{enquiry}', [EnquiryController::class, 'destroy'])->name('enquiries.destroy');
```

### 3. Route Placement

The routes were added at the end of the staff route group, making them accessible to staff users with the role-based middleware already applied.

## Routes Now Available

Staff users now have access to:

-   **GET** `/staff/enquiries` - View all enquiries
-   **GET** `/staff/enquiries/{enquiry}` - View specific enquiry
-   **DELETE** `/staff/enquiries/{enquiry}` - Delete enquiry

## Verification

-   ✅ Routes cleared and reloaded successfully
-   ✅ `php artisan route:list` shows staff.enquiries.\* routes are registered
-   ✅ Staff dashboard now loads without route errors
-   ✅ Navigation menu links work properly for staff users

## Final Status

**RESOLVED** - Enquiry routes are now properly defined for staff users. The staff dashboard and all navigation links are functional without route errors.

## Total Staff Routes

Staff users now have access to **143 routes** total (including the 3 new enquiry routes).
