# CLEANUP: Database Schema Cleanup

**Phase:** 7 (after Phase 6 — all code removed before database tables are dropped)  
**Status:** ⬜ Not started

---

## SCOPE

Create and run a new migration to drop all tables and columns associated with removed features. This phase uses a **single new migration file** to perform all drops in the correct FK-safe order.

> ⚠️ **Do NOT delete the original creation migration files.** They form the migration history. Only create new `drop_*` migrations.

---

## PART A — TABLES TO DROP (23 tables)

Drop in this exact order to respect CASCADE FK relationships:

| Order | Table                 | Migration that created it                                | FK Notes                                   |
| ----- | --------------------- | -------------------------------------------------------- | ------------------------------------------ |
| 1     | `visit_problems`      | `2021_09_08_113152_create_visit_problems_table.php`      | FK → visits (CASCADE)                      |
| 2     | `visit_observations`  | `2021_09_08_113357_create_visit_observations_table.php`  | FK → visits (CASCADE)                      |
| 3     | `visit_notes`         | `2021_09_08_113408_create_visit_notes_table.php`         | FK → visits (CASCADE)                      |
| 4     | `visit_prescriptions` | `2021_09_09_122904_create_visit_prescriptions_table.php` | FK → visits (CASCADE)                      |
| 5     | `visits`              | `2021_09_03_070956_create_visits_table.php`              | FK → doctors, patients (CASCADE)           |
| 6     | `service_doctor`      | `2021_08_03_052306_create_service_doctors_table.php`     | FK → services, doctors (CASCADE)           |
| 7     | `services`            | `2021_08_02_120956_create_services_table.php`            | FK → service_categories (CASCADE)          |
| 8     | `service_categories`  | `2021_08_02_071106_create_service_categories_table.php`  | No upstream FK                             |
| 9     | `session_week_days`   | `2021_07_31_065849_create_session_week_days_table.php`   | FK → doctor_sessions, doctors (CASCADE)    |
| 10    | `doctor_sessions`     | `2021_07_31_060412_create_doctor_sessions_table.php`     | FK → doctors (CASCADE)                     |
| 11    | `appointments`        | `2021_08_03_103710_create_appointments_table.php`        | FK → doctors, patients, services (CASCADE) |
| 12    | `transactions`        | `2021_10_12_105009_create_transactions_table.php`        | FK → users (CASCADE)                       |
| 13    | `payment_gateways`    | `2021_11_27_090159_create_payment_gateways_table.php`    | No FK                                      |
| 14    | `currencies`          | `2021_08_26_065726_create_currencies_table.php`          | No FK                                      |
| 15    | `clinic_schedules`    | `2021_09_06_111105_create_clinic_schedules_table.php`    | No FK                                      |
| 16    | `departments`         | `2025_10_09_181649_create_departments_table.php`         | college_id is non-FK integer               |
| 17    | `courses`             | `2025_03_30_132847_create_courses_table.php`             | No FK constraints                          |
| 18    | `year_levels`         | `2025_03_30_132901_create_year_levels_table.php`         | No FK constraints                          |
| 19    | `colleges`            | `2025_03_30_132837_create_colleges_table.php`            | No FK constraints                          |
| 20    | `campuses`            | `2025_03_30_132831_create_campuses_table.php`            | No FK constraints                          |
| 21    | `offices`             | `2025_10_09_175512_create_offices_table.php`             | No FK constraints                          |
| 22    | `guests`              | `2025_10_09_175456_create_guests_table.php`              | No FK constraints                          |

> ⚠️ Items 16–22 (education tables) are now **KEPT** per updated decision. Remove these rows from the migration — do NOT drop campus, college, course, year_levels, offices, departments, guests tables.

---

## ✅ REVISED DROP LIST (education tables excluded)

Drop only these **15 tables**:

| Order | Table                 |
| ----- | --------------------- |
| 1     | `visit_problems`      |
| 2     | `visit_observations`  |
| 3     | `visit_notes`         |
| 4     | `visit_prescriptions` |
| 5     | `visits`              |
| 6     | `service_doctor`      |
| 7     | `services`            |
| 8     | `service_categories`  |
| 9     | `session_week_days`   |
| 10    | `doctor_sessions`     |
| 11    | `appointments`        |
| 12    | `transactions`        |
| 13    | `payment_gateways`    |
| 14    | `currencies`          |
| 15    | `clinic_schedules`    |

---

## PART B — COLUMN CLEANUP ON KEPT TABLES

### `prescriptions` table

| Column           | Notes                                                               | Status         |
| ---------------- | ------------------------------------------------------------------- | -------------- |
| `appointment_id` | `unsignedBigInteger` — **NO FK constraint** — safe to drop directly | ⬜ DROP COLUMN |

Also drop index:

```sql
DROP INDEX idx_prescriptions_appointment ON prescriptions;
```

(Added by migration `2025_08_08_235715`)

### `users` table

> ℹ️ Education columns (`campus_id`, `college_id`, `course_id`, `year_level_id`, `office_id`, `department_id`) are **KEPT** — do NOT drop these columns.

---

## PART C — PERFORMANCE INDEX MIGRATIONS (reference only)

These migration files added indexes on tables being dropped. Once the tables are dropped, these indexes are automatically gone. No separate action needed.

| Migration                                                           | Indexes Added                                               | Action                                            |
| ------------------------------------------------------------------- | ----------------------------------------------------------- | ------------------------------------------------- |
| `2025_08_08_235715_add_additional_indexes.php`                      | appointments, prescriptions, visits                         | `idx_prescriptions_appointment` removed in Part B |
| `2025_10_01_000001_add_performance_indexes.php`                     | appointments, services, transactions, prescriptions, visits | Auto-dropped with tables                          |
| `2025_10_02_173840_add_additional_performance_indexes_oct_2025.php` | prescriptions, visits                                       | Auto-dropped with tables                          |
| `2025_10_03_174644_*.php`                                           | appointments                                                | Auto-dropped with tables                          |
| `2025_10_03_175423_*.php`                                           | appointments cleanup                                        | Auto-dropped with tables                          |

---

## PART D — NEW MIGRATION TEMPLATE

Create this migration file:

**Filename:** `database/migrations/YYYY_MM_DD_000000_drop_removed_features_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop in FK-safe order
        Schema::dropIfExists('visit_problems');
        Schema::dropIfExists('visit_observations');
        Schema::dropIfExists('visit_notes');
        Schema::dropIfExists('visit_prescriptions');
        Schema::dropIfExists('visits');
        Schema::dropIfExists('service_doctor');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
        Schema::dropIfExists('session_week_days');
        Schema::dropIfExists('doctor_sessions');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('payment_gateways');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('clinic_schedules');

        // Column cleanup on kept tables
        Schema::table('prescriptions', function (Blueprint $table) {
            // Drop index first (added in 2025_08_08 migration)
            $table->dropIndex('idx_prescriptions_appointment');
            $table->dropColumn('appointment_id');
        });
    }

    public function down(): void
    {
        // Restore prescriptions column
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('appointment_id')->nullable()->after('patient_id');
        });

        // Note: Restoring 15 dropped tables would require recreating their full schema.
        // See original migration files for table definitions.
    }
};
```

> ⚠️ **Wrap `dropIndex` in a `try/catch` or check if index exists** in case it's already been dropped or was never created on this environment:
>
> ```php
> if (Schema::hasColumn('prescriptions', 'appointment_id')) {
>     Schema::table('prescriptions', function (Blueprint $table) {
>         try { $table->dropIndex('idx_prescriptions_appointment'); } catch (\Exception $e) {}
>         $table->dropColumn('appointment_id');
>     });
> }
> ```

---

## PART E — MODELS TO DELETE (from removed tables)

After tables are dropped these model files should already be deleted (covered in Phases 1–5), but verify:

| Model                              | Table               | Deleted in Phase |
| ---------------------------------- | ------------------- | ---------------- |
| `app/Models/Appointment.php`       | appointments        | Phase 2          |
| `app/Models/Transaction.php`       | transactions        | Phase 1          |
| `app/Models/PaymentGateway.php`    | payment_gateways    | Phase 1          |
| `app/Models/Currency.php`          | currencies          | Phase 1          |
| `app/Models/Service.php`           | services            | Phase 2          |
| `app/Models/ServiceCategory.php`   | service_categories  | Phase 2          |
| `app/Models/DoctorSession.php`     | doctor_sessions     | Phase 2          |
| `app/Models/SessionWeekDay.php`    | session_week_days   | Phase 2          |
| `app/Models/ClinicSchedule.php`    | clinic_schedules    | Phase 2          |
| `app/Models/Visit.php`             | visits              | Phase 3          |
| `app/Models/VisitProblem.php`      | visit_problems      | Phase 3          |
| `app/Models/VisitObservation.php`  | visit_observations  | Phase 3          |
| `app/Models/VisitNote.php`         | visit_notes         | Phase 3          |
| `app/Models/VisitPrescription.php` | visit_prescriptions | Phase 3          |

---

## COMPLETION CHECKLIST

- [ ] Verify all Phase 1–5 code deletions are complete (no PHP references to removed models)
- [ ] Create new migration file using template above
- [ ] Run: `php artisan migrate`
- [ ] Verify tables dropped: `php artisan db:show` or check DB directly
- [ ] Verify `prescriptions.appointment_id` column removed
- [ ] Run: `php artisan optimize:clear` to clear any cached query/schema data
- [ ] Run: `php artisan route:list` — confirms no route errors

---

_Phase 7 — Prerequisites: All previous phases complete. Run migration last._
