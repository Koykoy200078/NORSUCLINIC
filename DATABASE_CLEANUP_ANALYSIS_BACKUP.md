# Database Cleanup Analysis - NORSU Clinic

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

### 2. WebSockets Statistics
**Tables:**
- `websockets_statistics_entries`

**Migrations:**
- `0000_00_00_000000_create_websockets_statistics_entries_table.php`

**Reason:** WebSockets are installed but the statistics table may not be actively used. Check if you're using Laravel WebSockets dashboard.

---

### 3. Payment Gateways (If Not Used)
**Tables:**
- `payment_gateways`

**Migrations:**
- `2021_11_27_090159_create_payment_gateways_table.php`

**Model:**
- `app/Models/PaymentGateway.php`

**Seeders:**
- `DefaultPaymentGatewaySeeder.php`

**Check:** Are you accepting online payments (PayPal, Stripe, Authorize.net, PayTM)? If not, this can be removed.

---

## 🟡 POTENTIALLY UNUSED - Needs Verification

### 4. Live Consultations (Commented in Menu)
**Check in menu.blade.php:** Lines with "live-consultation" are commented out

If not used, these could be candidates for removal:
- Any live consultation related tables/models
- WebSocket dependencies if only used for live consultations

---

### 5. Campus/College/Course System
**Tables:**
- `campuses`
- `colleges`
- `courses`
- `year_levels`

**Migrations:**
- `2025_03_30_132831_create_campuses_table.php`
- `2025_03_30_132837_create_colleges_table.php`
- `2025_03_30_132847_create_courses_table.php`
- `2025_03_30_132901_create_year_levels_table.php`

**Models:**
- `app/Models/Campus.php`
- `app/Models/College.php`
- `app/Models/Course.php`
- `app/Models/YearLevel.php`

**Seeders:**
- `CampusSeeder.php`
- `CollegeSeeder.php`
- `CourseSeeder.php`
- `YearLevelSeeder.php`

**Check:** Are you tracking student/patient academic information? If NORSU Clinic doesn't need academic tracking, these can be removed.

---

### 6. Guest System
**Tables:**
- `guests`

**Migrations:**
- `2025_10_09_175456_create_guests_table.php`

**Model:**
- `app/Models/Guest.php`

**Check:** Do you allow guest/visitor tracking? If not used, remove.

---

### 7. Offices System
**Tables:**
- `offices`

**Migrations:**
- `2025_10_09_175512_create_offices_table.php`

**Model:**
- `app/Models/Office.php`

**Seeder:**
- `OfficeSeeder.php`

**Check:** Are you tracking office assignments? If not needed, remove.

---

### 8. Departments System
**Tables:**
- `departments`

**Migrations:**
- `2025_10_09_181649_create_departments_table.php`

**Model:**
- `app/Models/Department.php`

**Seeder:**
- `DepartmentSeeder.php`

**Check:** Do you need department management? If patients aren't assigned by department, this might be redundant.

---

### 9. Vaccinations
**Tables:**
- `vaccinations`

**Migrations:**
- `2025_03_30_133435_create_vaccinations_table.php`

**Model:**
- `app/Models/Vaccination.php`

**Seeder:**
- `VaccinationSeeder.php`

**Check:** Are you tracking patient vaccinations? If not actively used, remove.

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

## 📋 Cleanup Commands

### Step 1: Backup Your Database
```bash
# PowerShell
php artisan db:backup
# or manually export via phpMyAdmin/MySQL
```

### Step 2: Drop Unused Tables (Manual)
```sql
-- Reviews system
DROP TABLE IF EXISTS reviews;

-- WebSockets (if not using dashboard)
DROP TABLE IF EXISTS websockets_statistics_entries;

-- Payment gateways (if not accepting online payments)
DROP TABLE IF EXISTS payment_gateways;

-- Campus system (if not tracking academics)
DROP TABLE IF EXISTS campuses;
DROP TABLE IF EXISTS colleges;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS year_levels;

-- Guest/Office/Department (if not used)
DROP TABLE IF EXISTS guests;
DROP TABLE IF EXISTS offices;
DROP TABLE IF EXISTS departments;

-- Vaccinations (if not tracked)
DROP TABLE IF EXISTS vaccinations;
```

### Step 3: Remove Migration Files
Delete the migration files listed above for each unused table.

### Step 4: Remove Model Files
Delete the model files listed above for each unused table.

### Step 5: Remove Seeders
Delete the seeder files listed above for each unused table.

### Step 6: Remove Controllers/Requests/Views
For Reviews:
```bash
# PowerShell
Remove-Item -Recurse app\Http\Controllers\ReviewController.php
Remove-Item -Recurse app\Http\Requests\CreateReviewRequest.php
Remove-Item -Recurse app\Http\Requests\UpdateReviewRequest.php
Remove-Item -Recurse resources\views\reviews\
```

### Step 7: Clean Up Routes
Remove review routes from `routes/patient.php`

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

2. **Foreign Keys:** Check for foreign key constraints before dropping tables

3. **Relationships:** Remove model relationships that reference deleted tables

4. **Seeders:** Update `DatabaseSeeder.php` to remove calls to deleted seeders

5. **Test After Cleanup:** Run the application and test all features to ensure nothing broke

---

## Verification Checklist

Before removing each table, verify:
- [ ] Table is not referenced in any active controller
- [ ] No foreign keys depend on this table
- [ ] No views/components use this table's data
- [ ] No active routes point to this resource
- [ ] No menu items reference this feature
- [ ] Feature is not mentioned in any requirements document

---

## Estimated Space Savings

Removing unused tables, migrations, models, controllers, and views can:
- Reduce database size
- Speed up migrations
- Simplify codebase maintenance
- Reduce autoload files
- Improve application clarity

---

## Recommendation Priority

### High Priority (Remove First)
1. ✅ Reviews system (confirmed unused)
2. ✅ WebSockets statistics (if dashboard not used)

### Medium Priority (Verify First)
3. Campus/College/Course system (if not a university clinic)
4. Guest/Office/Department system (if not used)
5. Vaccinations (if not tracking)

### Low Priority (Keep if Unsure)
6. Payment Gateways (may be needed for future online payments)

