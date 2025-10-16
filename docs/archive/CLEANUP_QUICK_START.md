# 🎯 Quick Start - Database Cleanup Guide

## ✅ What You Asked For

You requested to **KEEP** systems 5, 6, 7, 8, and 9:

-   ✅ Campus/College/Course System (System 3)
-   ✅ Guest System (System 4)
-   ✅ Office System (System 5)
-   ✅ Department System (System 6)
-   ✅ Vaccination System (System 7)
-   ✅ Payment Gateway System (System 8)

**All these systems are now PROTECTED and will NOT be removed!**

---

## 🚀 Recommended Cleanup (Reviews System Only)

### Option 1: Use the SAFE Script (RECOMMENDED) ⭐

```powershell
# 1. Backup your database first!
mysqldump -u root -p norsuclinic_db > backup.sql

# 2. Run the SAFE cleanup script
.\cleanup-reviews-only.ps1

# 3. Choose option 1 (Reviews System)
# The script will remove files automatically

# 4. Drop the reviews table manually
# Run in MySQL: DROP TABLE IF EXISTS reviews;

# 5. Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
composer dump-autoload
```

### Option 2: Manual Cleanup

If you prefer to do it manually:

```powershell
# 1. Backup database
mysqldump -u root -p norsuclinic_db > backup.sql

# 2. Remove files
Remove-Item database\migrations\2021_11_11_130524_create_reviews_table.php
Remove-Item app\Models\Review.php
Remove-Item app\Http\Controllers\ReviewController.php
Remove-Item app\Http\Requests\CreateReviewRequest.php
Remove-Item app\Http\Requests\UpdateReviewRequest.php
Remove-Item -Recurse resources\views\reviews\

# 3. Drop table in MySQL
# DROP TABLE IF EXISTS reviews;

# 4. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
composer dump-autoload
```

---

## 📚 Documentation Files

1. **PROTECTED_SYSTEMS_SUMMARY.md** ⭐ START HERE

    - Quick overview of protected systems
    - What can and cannot be removed
    - Step-by-step cleanup instructions

2. **DATABASE_CLEANUP_ANALYSIS.md**

    - Detailed analysis of all tables
    - Complete file listings
    - Updated with protected systems

3. **cleanup-reviews-only.ps1** ⭐ USE THIS SCRIPT

    - SAFE script that only removes Reviews
    - Shows protected systems
    - Interactive menu with safety checks

4. **cleanup-database.ps1** (Original - Updated)

    - Options 3-8 are disabled
    - Shows error messages for protected systems

5. **cleanup-database.sql**
    - SQL commands to drop tables
    - Protected tables are commented out

---

## ⚠️ What's Protected (Will NOT Be Removed)

The cleanup scripts will **NEVER** remove these systems:

| System                | Tables                                   | Purpose               |
| --------------------- | ---------------------------------------- | --------------------- |
| Campus/College/Course | campuses, colleges, courses, year_levels | Academic tracking     |
| Guest                 | guests                                   | Visitor management    |
| Office                | offices                                  | Office assignments    |
| Department            | departments                              | Department management |
| Vaccination           | vaccinations                             | Vaccination records   |
| Payment Gateway       | payment_gateways                         | Payment processing    |

---

## ✅ What Will Be Removed (Reviews System Only)

When you run the cleanup:

**Files Removed:**

-   ✅ `database/migrations/2021_11_11_130524_create_reviews_table.php`
-   ✅ `app/Models/Review.php`
-   ✅ `app/Http/Controllers/ReviewController.php`
-   ✅ `app/Http/Requests/CreateReviewRequest.php`
-   ✅ `app/Http/Requests/UpdateReviewRequest.php`
-   ✅ `resources/views/reviews/` (entire directory)

**Manual Steps:**

-   📝 Drop `reviews` table in MySQL
-   📝 Remove review route from `routes/patient.php`
-   📝 Remove review relationships from Patient/Doctor models

---

## 🔒 Safety Features

All cleanup scripts now include:

-   ✅ Backup verification prompt
-   ✅ Protected systems cannot be selected
-   ✅ Error messages if trying to remove protected systems
-   ✅ Clear warnings and confirmations
-   ✅ List of manual steps required

---

## 📊 Summary

-   **Protected Systems:** 6 (Campus, Guest, Office, Department, Vaccination, Payment)
-   **Safe to Remove:** Reviews System only
-   **Files to Remove:** 6 files + 1 directory
-   **Tables to Drop:** 1 table (`reviews`)

---

## 🎯 Next Steps

1. **Read:** `PROTECTED_SYSTEMS_SUMMARY.md`
2. **Backup:** Your database
3. **Run:** `.\cleanup-reviews-only.ps1`
4. **Choose:** Option 1 (Reviews System)
5. **Execute:** Manual SQL command to drop reviews table
6. **Clear:** Laravel caches
7. **Test:** Your application

---

## ❓ Quick FAQ

**Q: Can I remove the Campus system?**
A: No, it's protected. The script will show an error.

**Q: What about Vaccination system?**
A: Protected. Cannot be removed.

**Q: Is it safe to remove Reviews?**
A: Yes! It's commented out in the menu and not in use.

**Q: What if I make a mistake?**
A: Restore from your database backup.

---

**Status:** ✅ All requested systems (5-8) are protected
**Ready to cleanup:** Reviews System only
**Recommended action:** Run `cleanup-reviews-only.ps1`
