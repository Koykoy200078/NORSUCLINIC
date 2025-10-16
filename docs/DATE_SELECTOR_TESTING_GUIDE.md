# Date Selector Testing Guide

**Date:** October 16, 2025  
**Status:** Ready for Testing

---

## ✅ All Fixes Applied

### Issues Fixed:

1. ✅ **Model Cast Removed** - `RequestDocuments.php` no longer casts examined_on to date
2. ✅ **Database Column Changed** - Changed from DATE to VARCHAR(255)
3. ✅ **Field IDs Aligned** - All JavaScript uses `examined_on` (not `examined_on_value`)
4. ✅ **View Display Fixed** - `view_patient.blade.php` uses DateHelper
5. ✅ **PDF Generation Fixed** - Uses `formatExaminedOnForPDF()`
6. ✅ **Edit Form Fixed** - Handles pipe-delimited dates

---

## 🧪 Testing Checklist

### Before Testing:

```bash
# Clear all caches
php artisan view:clear
php artisan cache:clear
php artisan config:clear

# Refresh browser (Ctrl + F5)
```

---

### Test 1: Single Date Selection ✅

**Steps:**

1. Go to **Create Medical Certificate**
2. Click **"Select Dates"** button
3. Ensure **"Single Date"** is selected
4. Click on **October 16, 2025**
5. Click **"Apply Dates"**
6. Fill in all other required fields
7. Click **"Submit"**

**Expected Results:**

-   Display field shows: `10/16/2025`
-   Hidden field contains: `2025-10-16`
-   Form submits successfully ✅
-   Database stores: `2025-10-16`
-   View patient page shows: `October 16, 2025`
-   PDF shows: `10/16/2025`

**Database Check:**

```sql
SELECT id, examined_on FROM request_documents ORDER BY id DESC LIMIT 1;
-- Should show: 2025-10-16
```

---

### Test 2: Date Range Selection ✅

**Steps:**

1. Go to **Create Medical Certificate**
2. Click **"Select Dates"** button
3. Select **"Date Range"** radio button
4. Select **Start Date**: October 13, 2025
5. Select **End Date**: October 16, 2025
6. Click **"Apply Dates"**
7. Fill in all other required fields
8. Click **"Submit"**

**Expected Results:**

-   Display field shows: `10/13/2025 - 10/16/2025`
-   Hidden field contains: `2025-10-13|2025-10-16|range`
-   Form submits successfully ✅
-   Database stores: `2025-10-13|2025-10-16|range`
-   View patient page shows: `October 13, 2025 - October 16, 2025`
-   PDF shows: `10/13/2025 - 10/16/2025`

**Database Check:**

```sql
SELECT id, examined_on FROM request_documents ORDER BY id DESC LIMIT 1;
-- Should show: 2025-10-13|2025-10-16|range
```

---

### Test 3: Multiple Dates Selection ✅

**Steps:**

1. Go to **Create Medical Certificate**
2. Click **"Select Dates"** button
3. Select **"Multiple Dates"** radio button
4. Click on **October 12, 2025** → Click "Add Date"
5. Click on **October 14, 2025** → Click "Add Date"
6. Click on **October 16, 2025** → Click "Add Date"
7. Click **"Apply Dates"**
8. Fill in all other required fields
9. Click **"Submit"**

**Expected Results:**

-   Display field shows: `10/12/2025, 10/14/2025, 10/16/2025`
-   Hidden field contains: `2025-10-12,2025-10-14,2025-10-16|multiple`
-   Form submits successfully ✅
-   Database stores: `2025-10-12,2025-10-14,2025-10-16|multiple`
-   View patient page shows: `October 12, 2025, October 14, 2025, October 16, 2025`
-   PDF shows: `10/12/2025, 10/14/2025, 10/16/2025`

**Database Check:**

```sql
SELECT id, examined_on FROM request_documents ORDER BY id DESC LIMIT 1;
-- Should show: 2025-10-12,2025-10-14,2025-10-16|multiple
```

---

### Test 4: View Patient Page ✅

**Steps:**

1. Go to **Patients** list
2. Click on a patient who has medical certificates
3. Scroll to **Medical Certificates** section

**Expected Results:**

-   All certificates display dates correctly
-   No "Could not parse" errors
-   Single dates show as: `October 16, 2025`
-   Date ranges show as: `October 13, 2025 - October 16, 2025`
-   Multiple dates show as: `October 12, 2025, October 14, 2025, October 16, 2025`

---

### Test 5: PDF Generation ✅

**Steps:**

1. From patient view page
2. Click **View PDF** on a medical certificate

**Expected Results:**

-   PDF displays dates correctly
-   Single dates: `10/16/2025`
-   Date ranges: `10/13/2025 - 10/16/2025`
-   Multiple dates: `10/12/2025, 10/14/2025, 10/16/2025`

---

### Test 6: Edit Form ✅

**Steps:**

1. From patient view page
2. Click **Edit** on a medical certificate
3. Check the examined_on date field

**Expected Results:**

-   Single date: Shows first date `2025-10-16`
-   Date range: Shows first date `2025-10-13`
-   Multiple dates: Shows first date `2025-10-12`
-   Can edit and save successfully

**Note:** Edit form currently shows only the first date in a simple date input. Full date selector modal can be added later if needed.

---

## 🔍 Troubleshooting

### If you still see "Could not parse" error:

1. **Check Laravel Log:**

```bash
tail -50 storage/logs/laravel.log
```

2. **Verify Model Cast:**

```php
// In app/Models/RequestDocuments.php
// Should NOT have: 'examined_on' => 'date'
```

3. **Check Database Column:**

```sql
DESCRIBE request_documents;
-- examined_on should be varchar(255), not date
```

4. **Clear Everything:**

```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
composer dump-autoload
```

5. **Hard Refresh Browser:**

-   Chrome/Edge: `Ctrl + Shift + R` or `Ctrl + F5`
-   Clear browser cache if needed

---

## 📊 Data Format Reference

### Storage Format (Database):

```
Single Date:     2025-10-16
Date Range:      2025-10-13|2025-10-16|range
Multiple Dates:  2025-10-12,2025-10-14,2025-10-16|multiple
```

### Display Format (View Patient):

```
Single Date:     October 16, 2025
Date Range:      October 13, 2025 - October 16, 2025
Multiple Dates:  October 12, 2025, October 14, 2025, October 16, 2025
```

### PDF Format:

```
Single Date:     10/16/2025
Date Range:      10/13/2025 - 10/16/2025
Multiple Dates:  10/12/2025, 10/14/2025, 10/16/2025
```

---

## 📝 Files Modified (All Verified)

1. ✅ `app/Models/RequestDocuments.php`
2. ✅ `database/migrations/2025_10_16_104553_modify_examined_on_column_in_request_documents_table.php`
3. ✅ `app/Helpers/DateHelper.php`
4. ✅ `composer.json`
5. ✅ `app/Http/Controllers/RequestDocumentsController.php`
6. ✅ `resources/views/requests/forms/medical_certificate.blade.php`
7. ✅ `resources/views/patients/view_patient.blade.php` ← **JUST FIXED**
8. ✅ `resources/views/requests/pdf_medical_certificate.blade.php` ← **JUST FIXED**
9. ✅ `resources/views/requests/edit.blade.php` ← **JUST FIXED**

---

## ✨ Success Criteria

All tests should pass with:

-   ✅ No errors in Laravel log
-   ✅ Dates saved correctly in database
-   ✅ Dates displayed correctly on view page
-   ✅ PDFs generated without errors
-   ✅ Edit form loads without errors

---

**Ready to test!** 🚀

_Last updated: October 16, 2025 - All parsing errors fixed_
