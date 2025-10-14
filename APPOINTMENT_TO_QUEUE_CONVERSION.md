# Appointment to Patient Queue System - Conversion Guide

## Overview

Complete conversion of the appointment booking system to a real-time patient queuing system.

## Status: IN PROGRESS

### ✅ Completed Steps

1. **Database Migration Created** (`2025_10_15_000000_convert_appointments_to_patient_queues.php`)

    - Renames `appointments` → `patient_queues`
    - Adds new columns:
        - `room_number` (string, nullable)
        - `priority` (boolean, default: false)
        - `queue_number` (integer)
        - `admitted_by` (user_id)
        - `admitted_at`, `started_at`, `completed_at` (timestamps)
    - Adds indexes for queue operations
    - Auto-generates queue numbers for existing records

2. **PatientQueue Model Created** (`app/Models/PatientQueue.php`)

    - Updated status constants: WAITING, IN_PROGRESS, COMPLETED, CANCELLED
    - Added new relationships: `admittedBy()`
    - Added helper methods: `isPriority()`, `isInProgress()`, `isCompleted()`
    - Added scopes: `waiting()`, `inProgress()`, `priority()`, `today()`, `forDoctor()`
    - Added `generateQueueNumber()` method

3. **Livewire Tables Created**
    - `PatientQueueTable.php` - Main admin/staff queue management
    - `DoctorQueueTable.php` - Doctor's queue view
    - `DoctorPanelQueueTable.php` - Doctor panel queue

### 🔄 Next Steps

#### 4. Complete Remaining Livewire Tables

-   [ ] Create `PatientQueueHistoryTable.php` (for patient's own queue history)
-   [ ] Create `PatientShowPageQueueTable.php` if exists

#### 5. Controllers

-   [ ] Rename `AppointmentController.php` → `PatientQueueController.php`
-   [ ] Update all methods and add new queue-specific methods:
    -   `admitPatient()` - Nurse admits patient to queue
    -   `startConsultation()` - Doctor starts consultation
    -   `completeConsultation()` - Doctor completes consultation
    -   `setPriority()` - Toggle priority status
    -   `updateRoom()` - Update room assignment

#### 6. Repositories

-   [ ] Rename `AppointmentRepository.php` → `PatientQueueRepository.php`
-   [ ] Update all methods to use `patient_queues` table

#### 7. Request Validation

-   [ ] Rename `CreateAppointmentRequest.php` → `CreatePatientQueueRequest.php`
-   [ ] Rename `UpdateAppointmentRequest.php` → `UpdatePatientQueueRequest.php`
-   [ ] Add validation rules for `room_number` and `priority`

#### 8. Views

-   [ ] Rename directory: `resources/views/appointments/` → `resources/views/patient_queues/`
-   [ ] Create new component views:
    -   `priority_badge.blade.php` - Shows priority status
    -   `queue_number.blade.php` - Displays queue number
    -   `room_number.blade.php` - Shows room assignment
    -   `admitted_by.blade.php` - Shows who admitted the patient
    -   `queue_actions.blade.php` - Action buttons (Start, Complete, Cancel)
-   [ ] Update forms to include room number and priority checkbox
-   [ ] Update status badges (Waiting, In Progress, Completed)

#### 9. Routes

-   [ ] Update `routes/web.php` - Change all `appointments` routes to `patient-queues`
-   [ ] Update `routes/staff.php` - Update staff routes
-   [ ] Update `routes/doctor.php` - Update doctor routes
-   [ ] Update route names: `appointments.*` → `patient-queues.*`

#### 10. Navigation Menu

-   [ ] Update `menu.blade.php`:
    -   Change "Appointments" to "Patient Queue"
    -   Update icons (calendar → list/queue icon)
    -   Update route references
    -   Update badge to show waiting patients count

#### 11. Language Files

-   [ ] Update `lang/en/messages.php`:
    -   Add patient queue translations
    -   Update appointment-related text to queue terminology

#### 12. Permissions

-   [ ] Update permission names:
    -   `manage_appointments` → `manage_patient_queues`
-   [ ] Update in database if permissions are seeded
-   [ ] Update policy files if they exist

#### 13. Other Files to Update

-   [ ] Factory: `AppointmentFactory.php`
-   [ ] Mail classes: `AppointmentBookedMail.php`, etc.
-   [ ] Seeders if they reference appointments
-   [ ] Any API routes/controllers
-   [ ] Frontend patient appointment views (if keeping separate)

#### 14. Testing & Cleanup

-   [ ] Run migration: `php artisan migrate`
-   [ ] Clear all caches
-   [ ] Test nurse flow: admit patient, assign room, set priority
-   [ ] Test doctor flow: view queue, start consultation, complete
-   [ ] Test priority sorting
-   [ ] Verify all relationships working
-   [ ] Check for any remaining "Appointment" references
-   [ ] Delete old Appointment model and related files

## New Features

### For Nurses/Staff:

1. **Admit Patient to Queue**

    - Select patient
    - Assign to doctor
    - Set room number
    - Mark as priority if urgent
    - Auto-generates queue number for the day

2. **Queue Management**
    - View all patients in queue
    - Update room assignments
    - Toggle priority status
    - Cancel queue entries

### For Doctors:

1. **Queue Dashboard**

    - See all assigned patients
    - Priority patients highlighted at top
    - View room numbers
    - Start consultation button
    - Complete consultation button
    - Track consultation time

2. **Queue Filters**
    - Filter by status (Waiting, In Progress, Completed)
    - Filter by priority
    - Filter by room
    - Filter by date

## Database Schema Changes

### New Columns in `patient_queues` table:

```sql
room_number         VARCHAR(255) NULL
priority            BOOLEAN DEFAULT 0
queue_number        INT NULL
admitted_by         BIGINT UNSIGNED NULL
admitted_at         TIMESTAMP NULL
started_at          TIMESTAMP NULL
completed_at        TIMESTAMP NULL
```

### New Indexes:

-   `idx_queue_number_date` - For daily queue ordering
-   `idx_priority_status` - For priority sorting
-   `idx_room_number` - For room filtering
-   `idx_admitted_by` - For tracking who admitted

## Status Mapping

| Old Status | Old Name  | New Status | New Name    |
| ---------- | --------- | ---------- | ----------- |
| 1          | BOOKED    | 1          | WAITING     |
| 2          | ACCEPTED  | 2          | IN_PROGRESS |
| 3          | FINISHED  | 3          | COMPLETED   |
| 4          | CANCELLED | 4          | CANCELLED   |

## Important Notes

1. **Queue Numbers** are generated daily - reset each day
2. **Priority patients** always appear first in queue
3. **Room assignments** are flexible - can be updated anytime
4. **Timestamps** track full patient journey:
    - `admitted_at` - When nurse added to queue
    - `started_at` - When doctor started consultation
    - `completed_at` - When consultation finished
5. **Backward compatibility** - Status values remain same (1,2,3,4)

## File Renaming Checklist

### Models

-   [x] ~~`Appointment.php`~~ → `PatientQueue.php` ✅

### Livewire Tables

-   [x] ~~`AppointmentTable.php`~~ → `PatientQueueTable.php` ✅
-   [x] ~~`DoctorAppointmentTable.php`~~ → `DoctorQueueTable.php` ✅
-   [x] ~~`DoctorPanelAppointmentTable.php`~~ → `DoctorPanelQueueTable.php` ✅
-   [ ] `PatientAppointmentTable.php` → Update or keep for history
-   [ ] `PatientShowPageAppointmentTable.php` → Check if exists

### Controllers

-   [ ] `AppointmentController.php` → `PatientQueueController.php`
-   [ ] `PatientAppointmentController.php` → Update or remove

### Repositories

-   [ ] `AppointmentRepository.php` → `PatientQueueRepository.php`

### Requests

-   [ ] `CreateAppointmentRequest.php` → `CreatePatientQueueRequest.php`
-   [ ] `UpdateAppointmentRequest.php` → `UpdatePatientQueueRequest.php`
-   [ ] `CreateFrontAppointmentRequest.php` → Update if needed

### Views

-   [ ] `resources/views/appointments/` → `resources/views/patient_queues/`
-   [ ] `resources/views/doctor_appointment/` → `resources/views/doctor_queue/`
-   [ ] `resources/views/patients/appointments/` → Keep or update

### Mail

-   [ ] `AppointmentBookedMail.php` → `PatientQueueAdmittedMail.php`
-   [ ] `DoctorAppointmentBookMail.php` → Update
-   [ ] `PatientAppointmentBookMail.php` → Update

---

**Last Updated:** 2025-10-15
**Status:** Migration and models complete, continuing with controllers and views
