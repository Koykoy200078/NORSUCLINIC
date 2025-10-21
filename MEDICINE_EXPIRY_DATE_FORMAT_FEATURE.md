# Medicine Purchase Expiry Date Format Enhancement

## Summary

Added the ability to choose between two expiry date formats when purchasing medicines:

1. **Full Date (Y-M-D)**: Year-Month-Day format (e.g., 2025-10-21)
2. **Month Only (Y-M)**: Year-Month format (e.g., 2025-10)

## Changes Made

### 1. View Files Modified

#### `resources/views/purchase-medicines/fields.blade.php`

-   Added new table header column "Date Format" between Expiry Date and Quantity columns
-   Added format selector dropdown for the first medicine row:
    ```html
    <select
        class="form-select expiry-format-selector"
        data-id="1"
        id="expiry_format1"
    >
        <option value="Y-m-d" selected>Full Date (Y-M-D)</option>
        <option value="Y-m">Month Only (Y-M)</option>
    </select>
    ```

#### `resources/views/purchase-medicines/templates/templates.php`

-   Updated the dynamic row template to include format selector
-   Ensured new rows added via "Add" button also have format selection capability

### 2. JavaScript File Modified

#### `resources/assets/js/purchase-medicine/purchase-medicine.js`

Added three new functions and modified the initialization:

**New Functions:**

1. `initializeFlatpickrForElement(element, format)` - Helper to initialize Flatpickr with specific format

    - Supports both "Y-m-d" (full date) and "Y-m" (month-only) formats
    - For month-only format, uses altInput to display friendly format (e.g., "October 2025")

2. `initializeAllFlatpickrs()` - Initializes all existing Flatpickr instances based on their format selectors

3. Updated `loadPurchaseMedicineCreate()`:

    - Calls `initializeAllFlatpickrs()` on page load
    - Added event listener for format selector changes
    - Dynamically reinitializes Flatpickr when format changes

4. Updated dynamic row addition:
    - Initializes Flatpickr for newly added rows based on their format selector value

### 3. Assets Compilation

-   Ran `npm run dev` successfully
-   All JavaScript changes compiled and ready for production

## How It Works

### User Flow:

1. User opens medicine purchase creation page
2. For each medicine row, user can select expiry date format from dropdown
3. Options available:
    - **Full Date (Y-M-D)**: Shows complete date picker with day selection
    - **Month Only (Y-M)**: Shows simplified picker for year and month only

### Technical Implementation:

-   Each row has independent format selection
-   Format selector has `data-id` attribute matching the row number
-   When format changes, the Flatpickr instance is destroyed and recreated with new configuration
-   Works for both initial row and dynamically added rows

## Benefits

1. **Flexibility**: Some medicines expire at end of month (only need Y-M), others have specific day
2. **Data Accuracy**: Users can enter exactly what's printed on medicine packaging
3. **User Experience**: Clear format selection prevents confusion
4. **Consistent Behavior**: Format selection works the same for all rows (initial and dynamic)

## Files Changed

```
d:\Projects\NORSUCLINIC\resources\views\purchase-medicines\fields.blade.php
d:\Projects\NORSUCLINIC\resources\views\purchase-medicines\templates\templates.php
d:\Projects\NORSUCLINIC\resources\assets\js\purchase-medicine\purchase-medicine.js
```

## Testing Recommendations

1. Test format switching on first row
2. Test adding new medicine rows with "Add" button
3. Verify both date formats save correctly to database
4. Test that month-only format displays friendly format (e.g., "October 2025")
5. Verify minimum date validation still works (no past dates)
6. Test form submission with mix of both formats

## Backend Considerations

The Laravel backend should already handle both date formats since:

-   Both formats are valid date strings
-   Laravel's Carbon can parse both "2025-10-21" and "2025-10" formats
-   Database column type (likely DATE or VARCHAR) should accommodate both

If validation is strict, you may need to update validation rules in:

-   `app/Http/Controllers/PurchaseMedicineController.php` (or similar)
-   Add validation: `'expiry_date.*' => 'required|date_format:Y-m-d|nullable|date_format:Y-m'`

## Future Enhancements

1. Add visual indicator showing selected format in the date input
2. Consider adding a global format preference (all rows use same format)
3. Add backend validation to ensure consistent format handling
4. Consider storing format choice in database for audit purposes

---

**Date Implemented**: October 21, 2025  
**Feature Status**: ✅ Complete and Compiled
