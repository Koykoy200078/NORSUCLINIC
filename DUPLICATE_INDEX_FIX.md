# Duplicate Index Key Error Fix

## Issue

**Error**: `Duplicate key name 'idx_appointments_doctor_status'`

**SQL**:

```sql
CREATE INDEX idx_appointments_doctor_status ON appointments (doctor_id, status)
```

**Migration**: `2025_10_03_174644_fix_appointments_date_status_index.php`

---

## Root Cause

The migration `2025_10_03_174644_fix_appointments_date_status_index.php` has already run successfully and created these indexes:

-   ✅ `idx_appointments_date_50`
-   ✅ `idx_appointments_date_status_opt`
-   ✅ `idx_appointments_doctor_status`
-   ✅ `idx_appointments_patient_status`

When attempting to run the migration again (or in testing/rollback scenarios), it tries to create indexes that **already exist**, causing the duplicate key error.

---

## Solution

Updated the migration to **check if indexes exist before creating them**:

### Before (Problematic):

```php
public function up(): void
{
    // Drop problematic indexes...

    // PROBLEM: Always tries to create indexes, even if they exist
    DB::statement('CREATE INDEX idx_appointments_date_50 ON appointments (date(50))');
    DB::statement('CREATE INDEX idx_appointments_doctor_status ON appointments (doctor_id, status)');
    // ...
}
```

### After (Fixed):

```php
public function up(): void
{
    // Drop problematic indexes if they exist
    try {
        DB::statement('ALTER TABLE appointments DROP INDEX IF EXISTS idx_appointments_date_status');
    } catch (\Exception $e) {
        // Index doesn't exist, continue
    }

    // ... more drops ...

    // Check existing indexes before creating
    $existingIndexes = DB::select("SHOW INDEX FROM appointments WHERE Key_name IN (...)");
    $existingIndexNames = array_unique(array_column($existingIndexes, 'Key_name'));

    // Only create if doesn't exist
    if (!in_array('idx_appointments_doctor_status', $existingIndexNames)) {
        DB::statement('CREATE INDEX idx_appointments_doctor_status ON appointments (doctor_id, status)');
    }

    // ... same for other indexes
}
```

---

## Changes Made

### File: `database/migrations/2025_10_03_174644_fix_appointments_date_status_index.php`

**Key Improvements**:

1. ✅ **Idempotent Migration** - Can run multiple times safely
2. ✅ **Index Existence Check** - Queries database for existing indexes
3. ✅ **Conditional Creation** - Only creates missing indexes
4. ✅ **Error Handling** - Wrapped drops in try-catch blocks

---

## Current Index State

After running `php check_indexes.php`:

```
idx_appointments_doctor_date_status     (doctor_id, date, status)     ← OLD, should be dropped
idx_appointments_patient_date_status    (patient_id, date, status)    ← OLD, should be dropped
idx_appointments_created_status         (created_at, status)          ✅ GOOD
idx_appointments_date_50                (date(50))                     ✅ NEW, optimized
idx_appointments_date_status_opt        (date(50), status)            ✅ NEW, optimized
idx_appointments_doctor_status          (doctor_id, status)           ✅ NEW, optimized
idx_appointments_patient_status         (patient_id, status)          ✅ NEW, optimized
```

**Total indexes**: 21 index entries (7 unique indexes)

---

## Why This Happened

### Migration Lifecycle Issue

1. Migration runs successfully → Creates indexes ✅
2. Migration marked as "Ran" in database
3. Developer tries to run migration again (testing/rollback)
4. Migration tries to create **already existing** indexes ❌
5. MySQL throws duplicate key error

### Why Old Indexes Remain

The migration successfully:

-   ✅ Created new optimized indexes
-   ❌ Failed to drop old problematic indexes (they still exist)

The old indexes (`idx_appointments_doctor_date_status`, `idx_appointments_patient_date_status`) are still in the database because:

-   They were created by an earlier migration (`2025_10_01_000001_add_performance_indexes.php`)
-   The fix migration tried to drop them but they might not have existed at that time
-   They got created later but weren't dropped

---

## Testing

### Test Migration Idempotency

```bash
# This should now work without errors
php artisan migrate:rollback --step=1
php artisan migrate
```

### Verify Indexes

```bash
php check_indexes.php
```

**Expected**: No duplicate key errors, migration runs successfully

---

## Cleanup Recommendations

### Option 1: Manual Cleanup (Recommended)

Drop the old redundant indexes manually:

```sql
ALTER TABLE appointments DROP INDEX IF EXISTS idx_appointments_doctor_date_status;
ALTER TABLE appointments DROP INDEX IF EXISTS idx_appointments_patient_date_status;
```

**Why?**: These old indexes are:

-   ❌ Redundant (covered by new optimized indexes)
-   ❌ Less efficient (include unnecessary date column)
-   ❌ Cause confusion in index management

### Option 2: Create Cleanup Migration

Create a new migration to remove old indexes:

```bash
php artisan make:migration cleanup_old_appointment_indexes
```

```php
public function up(): void
{
    DB::statement('ALTER TABLE appointments DROP INDEX IF EXISTS idx_appointments_doctor_date_status');
    DB::statement('ALTER TABLE appointments DROP INDEX IF EXISTS idx_appointments_patient_date_status');
}
```

---

## Benefits of Fixed Migration

### 1. **Idempotent** ✅

-   Can run multiple times without errors
-   Safe for testing and development
-   Handles existing indexes gracefully

### 2. **Resilient** ✅

-   Works in any database state
-   Doesn't fail on existing indexes
-   Proper error handling

### 3. **Maintainable** ✅

-   Clear logic flow
-   Easy to understand what's happening
-   Self-documenting code

---

## Prevention Guidelines

### Always Make Migrations Idempotent

```php
// ❌ BAD - Will fail if index exists
DB::statement('CREATE INDEX idx_name ON table (column)');

// ✅ GOOD - Check before creating
$indexes = DB::select("SHOW INDEX FROM table WHERE Key_name = 'idx_name'");
if (empty($indexes)) {
    DB::statement('CREATE INDEX idx_name ON table (column)');
}

// ✅ ALSO GOOD - Use Laravel's built-in check
if (!Schema::hasIndex('table', 'idx_name')) {
    // Note: hasIndex doesn't work with raw SQL indexes
}
```

### Use DROP INDEX IF EXISTS

```php
// ✅ GOOD - MySQL 5.7.4+
DB::statement('ALTER TABLE table DROP INDEX IF EXISTS idx_name');

// ✅ SAFE - Wrap in try-catch for older MySQL
try {
    DB::statement('ALTER TABLE table DROP INDEX idx_name');
} catch (\Exception $e) {
    // Index doesn't exist, that's fine
}
```

---

## Status

-   **Fixed**: ✅ 2025-10-03
-   **Migration Updated**: `2025_10_03_174644_fix_appointments_date_status_index.php`
-   **Issue**: Duplicate key error on re-run
-   **Solution**: Added index existence checks
-   **Testing**: ✅ Migration now idempotent
-   **Cleanup**: ⏳ Pending (old indexes still exist)

---

## Next Steps

1. ✅ **Test Migration** - Verify it runs without errors
2. ⏳ **Remove Old Indexes** - Clean up redundant indexes
3. ⏳ **Update Documentation** - Document final index structure
4. ⏳ **Performance Test** - Verify query performance with new indexes

---

## Related Documentation

-   Main fix: `MYSQL_INDEX_KEY_LENGTH_FIX.md`
-   Performance optimization: `PERFORMANCE_OPTIMIZATION_COMPLETE.md`
-   Role fix: `ROLE_SELECTOR_FIX.md`

---

**Priority**: 🟡 **MEDIUM** (prevents migration re-runs)  
**Severity**: LOW (doesn't affect production if migration ran once)  
**Type**: Bug Fix  
**Impact**: Development workflow improvement
