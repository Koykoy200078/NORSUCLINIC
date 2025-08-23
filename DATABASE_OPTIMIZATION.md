# Database Optimization Plan

## 1. Missing Database Indexes

### Performance Critical Indexes Needed:

#### User Table:

```sql
-- Add indexes for frequently queried columns
ALTER TABLE users ADD INDEX idx_users_email (email);
ALTER TABLE users ADD INDEX idx_users_status (status);
ALTER TABLE users ADD INDEX idx_users_type (type);
ALTER TABLE users ADD INDEX idx_users_campus_college (campus_id, college_id);
```

#### Appointments Table:

```sql
ALTER TABLE appointments ADD INDEX idx_appointments_patient_id (patient_id);
ALTER TABLE appointments ADD INDEX idx_appointments_doctor_id (doctor_id);
ALTER TABLE appointments ADD INDEX idx_appointments_date (date);
ALTER TABLE appointments ADD INDEX idx_appointments_status (status);
```

#### Prescriptions Table:

```sql
ALTER TABLE prescriptions ADD INDEX idx_prescriptions_patient_id (patient_id);
ALTER TABLE prescriptions ADD INDEX idx_prescriptions_doctor_id (doctor_id);
ALTER TABLE prescriptions ADD INDEX idx_prescriptions_appointment_id (appointment_id);
```

#### Medicine Related Tables:

```sql
ALTER TABLE medicines ADD INDEX idx_medicines_category_id (category_id);
ALTER TABLE medicines ADD INDEX idx_medicines_name (name);
ALTER TABLE medicine_bills ADD INDEX idx_medicine_bills_patient_id (patient_id);
ALTER TABLE medicine_bills ADD INDEX idx_medicine_bills_doctor_id (doctor_id);
```

## 2. Query Optimization

### Slow Query Issues:

#### 1. Full Name Searches:

Current problematic queries:

```php
// Slow - concatenates on every row
whereRaw("TRIM(CONCAT(first_name,' ',last_name,' ')) like '%{$search}%'")
```

Solution - Add computed column:

```sql
-- Add full_name computed column
ALTER TABLE users ADD COLUMN full_name VARCHAR(255)
GENERATED ALWAYS AS (CONCAT(first_name, ' ', last_name)) STORED;

-- Add index on full_name
ALTER TABLE users ADD INDEX idx_users_full_name (full_name);
```

#### 2. Date Range Queries:

```php
// Instead of using whereRaw with date functions
whereRaw('Date(created_at) = CURDATE()')

// Use proper date comparisons
whereBetween('created_at', [
    Carbon::today()->startOfDay(),
    Carbon::today()->endOfDay()
])
```

## 3. Database Structure Improvements

### Normalization Issues:

#### 1. Settings Table:

Current structure stores key-value pairs. Consider structured approach:

```sql
-- Instead of generic settings table
CREATE TABLE application_settings (
    id BIGINT PRIMARY KEY,
    clinic_name VARCHAR(255),
    logo VARCHAR(255),
    favicon VARCHAR(255),
    email_verification BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### 2. Address Polymorphic Relationship:

Current polymorphic addresses are good, but ensure proper indexes:

```sql
ALTER TABLE addresses ADD INDEX idx_addresses_owner (owner_type, owner_id);
```

## 4. Data Integrity Constraints

### Missing Constraints:

#### Foreign Key Constraints:

```sql
-- Ensure referential integrity
ALTER TABLE patients ADD CONSTRAINT fk_patients_user_id
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE doctors ADD CONSTRAINT fk_doctors_user_id
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE appointments ADD CONSTRAINT fk_appointments_patient_id
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE;

ALTER TABLE appointments ADD CONSTRAINT fk_appointments_doctor_id
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE;
```

#### Check Constraints:

```sql
-- Ensure data validity
ALTER TABLE users ADD CONSTRAINT chk_users_gender
    CHECK (gender IN (1, 2));

ALTER TABLE appointments ADD CONSTRAINT chk_appointments_status
    CHECK (status IN (0, 1, 2, 3, 4));

ALTER TABLE users ADD CONSTRAINT chk_users_email_format
    CHECK (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$');
```

## 5. Migration Best Practices

### Create Proper Migrations:

#### Index Migration:

```php
<?php
// database/migrations/2024_xx_xx_add_performance_indexes.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email']);
            $table->index(['status']);
            $table->index(['type']);
            $table->index(['campus_id', 'college_id']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['patient_id']);
            $table->index(['doctor_id']);
            $table->index(['date']);
            $table->index(['status']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['status']);
            $table->dropIndex(['type']);
            $table->dropIndex(['campus_id', 'college_id']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['patient_id']);
            $table->dropIndex(['doctor_id']);
            $table->dropIndex(['date']);
            $table->dropIndex(['status']);
        });
    }
};
```
