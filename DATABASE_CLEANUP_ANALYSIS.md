# Database Cleanup Analysis - NORSU Clinic
## ⚠️ UPDATED: Systems 3-8 are REQUIRED and must NOT be removed

## Summary
This document identifies unused tables, migrations, and seeders that can be safely removed from the NORSU Clinic database.

---

## 🔴 CONFIRMED UNUSED - Safe to Remove

### 1. Reviews System (Commented Out in Menu)
**Tables:**
- `reviews`

**Migrations:**
- `2021_11_11_130524_create_reviews_table.php`

**Models:**
- `app/Models/Review.php`

**Controllers:**
- `app/Http/Controllers/ReviewController.php`

**Requests:**
- `app/Http/Requests/CreateReviewRequest.php`
- `app/Http/Requests/UpdateReviewRequest.php`

**Views:**
- `resources/views/reviews/` (entire directory)

**Routes:**
- Patient review routes in `routes/patient.php` (line 60)

**Reason:** The review feature is commented out in the menu and not accessible to users.

---

### 2. WebSockets Statistics (Verify First)
**Tables:**
- `websockets_statistics_entries`

**Migrations:**
- `0000_00_00_000000_create_websockets_statistics_entries_table.php`

**Reason:** WebSockets are installed but the statistics table may not be actively used. Check if you're using Laravel WebSockets dashboard before removing.

---

## ✅ REQUIRED SYSTEMS - DO NOT REMOVE

### 3. Campus/College/Course System ⚠️ KEEP
**Tables:** `campuses`, `colleges`, `courses`, `year_levels`
**Reason:** Used for patient academic information tracking
**Status:** ACTIVELY USED - DO NOT REMOVE

### 4. Guest System ⚠️ KEEP
**Tables:** `guests`
**Reason:** Required for visitor management
**Status:** ACTIVELY USED - DO NOT REMOVE

### 5. Office System ⚠️ KEEP
**Tables:** `offices`
**Reason:** Required for office assignments
**Status:** ACTIVELY USED - DO NOT REMOVE

### 6. Department System ⚠️ KEEP
**Tables:** `departments`
**Reason:** Required for department management
**Status:** ACTIVELY USED - DO NOT REMOVE

### 7. Vaccination System ⚠️ KEEP
**Tables:** `vaccinations`
**Reason:** Required for patient vaccination records
**Status:** ACTIVELY USED - DO NOT REMOVE

### 8. Payment Gateway System ⚠️ KEEP
**Tables:** `payment_gateways`
**Reason:** Required for online payment processing
**Status:** ACTIVELY USED - DO NOT REMOVE

---

## 🟢 ACTIVELY USED - Keep These

### Core System Tables (DO NOT REMOVE)
- `users`
- `password_resets` / `password_reset_tokens`
- `failed_jobs`
- `media`
- `settings`
- `doctors`
- `patients`
- `patient_queues` ✅ (newly added)
- `appointments`
- `transactions`
- `visits`
- `visit_problems`, `visit_observations`, `visit_notes`, `visit_prescriptions`
- `prescriptions`, `prescriptions_medicines`
- `medicines`, `medicine_bills`, `purchase_medicines`, `purchased_medicines`, `used_medicines`, `sale_medicines`, `consultation_medicines`
- `categories` (medicine)
- `brands` (medicine)
- `services`, `service_categories`, `service_doctors`
- `specializations`, `doctor_specialization`
- `doctor_sessions`, `session_week_days`
- `clinic_schedules`
- `holidays`
- `countries`, `states`, `cities`
- `addresses`
- `currencies`
- `qualifications`
- `notifications`
- `sliders`, `enquiries`, `subscribes`
- `request_documents`
- `diagnoses`
- `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` (Spatie)

---

## 📊 Cleanup Recommendations

### Priority 1: Safe to Remove (Confirmed Unused)
1. **Reviews System** - Commented out in menu, not being used
   - Remove: migration, model, controller, requests, views

### Priority 2: Verify Before Removing
2. **WebSockets Statistics** - May not be actively monitored
   - Verify usage before removing

### ⚠️ DO NOT REMOVE (Required Systems)
The following systems are REQUIRED and must be kept:
- ✅ **Campus/College/Course System** - Patient academic tracking
- ✅ **Guest System** - Visitor management
- ✅ **Office System** - Office assignments
- ✅ **Department System** - Department management
- ✅ **Vaccination System** - Patient vaccination records
- ✅ **Payment Gateway System** - Online payment processing

---

## 📋 Safe Cleanup Commands

### Step 1: Backup Your Database
```bash
# PowerShell
php artisan db:backup
# or manually export via phpMyAdmin/MySQL
```

### Step 2: Drop Only Confirmed Unused Tables
```sql
-- Reviews system (SAFE TO REMOVE)
DROP TABLE IF EXISTS reviews;

-- WebSockets statistics (Verify first if using dashboard)
-- DROP TABLE IF EXISTS websockets_statistics_entries;
```

### Step 3: Remove Migrations (Reviews Only)
```bash
# PowerShell
Remove-Item database\migrations\2021_11_11_130524_create_reviews_table.php
```

### Step 4: Remove Models (Reviews Only)
```bash
# PowerShell
Remove-Item app\Models\Review.php
```

### Step 5: Remove Controllers/Requests
```bash
# PowerShell
Remove-Item app\Http\Controllers\ReviewController.php
Remove-Item app\Http\Requests\CreateReviewRequest.php
Remove-Item app\Http\Requests\UpdateReviewRequest.php
```

### Step 6: Remove Views
```bash
# PowerShell
Remove-Item -Recurse resources\views\reviews\
```

### Step 7: Clean Up Routes
Remove review routes from `routes/patient.php` (line 60)

### Step 8: Update Models with Review Relationships
Remove review relationships from:
- `app/Models/Patient.php` (line 275)
- `app/Models/Doctor.php` (line 138)

### Step 9: Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
composer dump-autoload
```

---

## ⚠️ Important Notes

1. **Backup First:** Always backup your database before removing anything!

2. **Only Remove Reviews System:** This is the only confirmed unused system

3. **Keep All Other Systems:** Systems 3-8 are REQUIRED and actively used

4. **Foreign Keys:** Check for foreign key constraints before dropping tables

5. **Test After Cleanup:** Run the application and test all features

---

## Verification Checklist (Reviews System Only)

Before removing, verify:
- [x] Reviews feature is commented out in menu
- [x] No active routes point to reviews (route is in patient.php but unused)
- [x] Menu item is commented out
- [ ] Backup database completed
- [ ] Ready to remove files
