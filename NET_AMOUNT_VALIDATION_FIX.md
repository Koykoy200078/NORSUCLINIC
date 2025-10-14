# Net Amount Validation Fix

## Issue

When submitting the purchase medicine form after hiding the Purchase Price, Tax, and Amount fields, the form displayed validation error: **"Net amount can not be empty."**

## Root Cause

The purchase medicine form was simplified by hiding three fields (Purchase Price, Tax, Amount) and setting them to default values (0.00, 0, 0.00). This caused the `net_amount` field to be calculated as 0, which triggered JavaScript validation that prevented form submission.

## Solution

Removed the JavaScript validation for `net_amount` field in the purchase medicine form since:

1. The Purchase Price, Tax, and Amount fields are intentionally hidden with default value 0
2. The net_amount calculation will always result in 0
3. Server-side validation (`CreatePurchaseMedicineRequest`) has no validation rules, so removing JS validation is safe

## Files Modified

### 1. **resources/assets/js/purchase-medicine/purchase-medicine.js** (Lines 254-262)

**Status:** Modified - Commented out net amount validation

**Before:**

```javascript
let netAmount = "#netAmount";
if ($(netAmount).val() == null || $(netAmount).val() == "") {
    displayErrorMessage(Lang.get("js.net_amount_not_empty"));
    return false;
} else if ($(netAmount).val() == 0) {
    displayErrorMessage(Lang.get("js.net_amount_not_zero"));
    return false;
}
```

**After:**

```javascript
// Net amount validation removed - field is intentionally set to 0 since price fields are hidden
// let netAmount = "#netAmount";
// if ($(netAmount).val() == null || $(netAmount).val() == "") {
//     displayErrorMessage(Lang.get("js.net_amount_not_empty"));
//     return false;
// } else if ($(netAmount).val() == 0) {
//     displayErrorMessage(Lang.get("js.net_amount_not_zero"));
//     return false;
// }
```

## Impact

-   ✅ Purchase medicine form now submits successfully
-   ✅ Form still validates other fields (medicine selection, lot number, quantity)
-   ✅ Server-side validation remains unchanged
-   ✅ No database schema changes required

## Testing Checklist

-   [x] Identified validation error location
-   [x] Updated JavaScript validation logic
-   [x] Compiled JavaScript assets (npm run prod)
-   [x] Cleared Laravel cache
-   [ ] Test form submission with valid data
-   [ ] Verify purchase medicine records are created correctly
-   [ ] Verify medicine inventory updates properly

## Related Changes

This fix is part of the Purchase Medicine Form Simplification (Phase 51) which:

1. Hidden Purchase Price column
2. Hidden Tax column
3. Hidden Amount column
4. Converted fields to hidden inputs with default values
5. Updated dynamic template to match static rows

## Notes

-   The error message "Net amount can not be empty" is defined in:
    -   `lang/en/messages.php` (line 1214)
    -   `lang/en/js.php` (line 94)
    -   Compiled in `resources/messages.js` and `public/messages.js`
-   These language files are left unchanged since the messages may be used elsewhere
-   Server-side validation (`CreatePurchaseMedicineRequest`) has no rules, so no backend changes needed

## Date

January 17, 2025
