# 🏥 NORSUCLINIC - Comprehensive Features Documentation

## 📋 Project Overview

A **Laravel 10 PHP Clinic Management System** designed for NORSU (Negros Oriental State University) clinic operations. Built with **Livewire 3**, **Spatie Permissions**, and modern Laravel best practices.

### Tech Stack

- **Framework**: Laravel 10
- **PHP Version**: 8.1+
- **Frontend**: Livewire 3, Bootstrap 5
- **Database**: MySQL
- **Real-time**: Pusher/Laravel WebSockets

---

## 👥 User Roles & Access Control

| Role             | Description                                 |
| ---------------- | ------------------------------------------- |
| **clinic_admin** | Full system access, manages all modules     |
| **doctor**       | Patient visits, prescriptions, appointments |
| **staff**        | Patient registration, queue management      |
| **patient**      | View appointments, medical records          |

---

## 🔑 Core Modules

### 1. Patient Management

- Patient registration with unique IDs (`patient_unique_id`)
- Profile with demographics:
    - Blood type (O+, A+, B+, AB+, O-, A-, B-, AB-)
    - Gender (Male/Female)
    - Contact information
- Campus/College/Course/Year Level integration (university-specific)
- Patient cascade deletion system (auto-cleanup of related data)
- Patient search functionality
- Medical history tracking

### 2. Doctor Management

- Doctor profiles with specializations
- Experience and qualification tracking
- Social media links (Twitter, LinkedIn, Instagram)
- Doctor-service assignments
- Multiple specializations support

### 3. Appointment System

- Appointment booking (online/walk-in)
- Doctor session scheduling
- Calendar view (FullCalendar integration)
- Status tracking:
    - Booked → Accepted → Finished/Cancelled
- Time slot management with configurable gaps
- Appointment PDF generation
- Service-based appointments

### 4. Patient Queue Management

- Real-time queue system
- Priority patients support
- Queue statuses:
    - Waiting → In Progress → Completed → Cancelled
- Room number assignments
- Consultation form integration (auto-attach latest form)
- Doctor call-next functionality
- Staff queue management interface

### 5. Consultation Forms (Request Documents)

- Multiple document types:
    - Consultation Form
    - Medical Certificate
- Comprehensive patient information:
    - Demographics & contact info
    - Medical history (COVID vaccination, comorbidities, allergies)
    - Admissions/surgeries history
    - Maintenance medications
    - Pregnancy status (for females)
- Vital signs recording:
    - Blood Pressure (BP)
    - Pulse Rate (PR)
    - Temperature
    - Respiratory Rate (RR)
    - Oxygen Saturation (O2 Sat)
    - Height & Weight
- Pertinent examination findings
- Assessment and plan
- Nursing intervention
- Image attachments for consultation
- PDF export functionality
- Document creator tracking

### 6. Patient Visits (Encounters)

- Visit date tracking
- Doctor-patient association
- Visit components:
    - **Problems** - Chief complaints
    - **Observations** - Clinical observations
    - **Notes** - Clinical notes
    - **Prescriptions** - Visit prescriptions

### 7. Prescription Management

- Medical history tracking:
    - Food allergies
    - Tendency to bleed
    - Heart disease
    - High blood pressure
    - Diabetes
    - Surgery history
    - Accident history
- Prescription medicines with dosage
- Current medication tracking
- PDF generation
- Status management (active/inactive)
- Plus rate & temperature recording

### 8. Medicine Management

#### Categories

- Organized medicine groupings
- Active/inactive status

#### Generics (formerly Brands)

- Generic medicine names
- Manufacturer information

#### Medicines

- Individual medicine records
- Salt composition
- Description
- Side effects
- Stock quantity tracking
- Available quantity
- Minimum stock alerts
- Stock alert percentage

#### Medicine Availability (Stock Management)

- Batch tracking
- Expiry date management
- Manufacturing date
- Dosage tracking
- Availability number generation

#### Used Medicine

- Consumption tracking
- Patient linkage

#### Medicine History (Bills)

- Distribution records
- Patient medicine history
- PDF generation

### 9. Service Management

- Service categories
- Individual services with:
    - Name
    - Charges
    - Short description
    - Icon
- Doctor-service assignments
- Service status (active/inactive)

### 10. Doctor Sessions & Scheduling

- Weekly session configuration
- Time slot management (AM/PM)
- Session duration and gaps
- Doctor holidays management
- Clinic schedules
- Week day configuration

### 11. Staff Management

- Staff registration
- Permission-based access
- Department assignments
- Profile management

### 12. Activity Logging System

- Comprehensive audit trail
- Tracked actions:
    - Patient creation/update/deletion
    - Consultation form creation
    - Medical certificate creation
    - Medicine procurement
    - Medicine usage
- User action tracking with:
    - IP address
    - User agent
    - Timestamp
- Patient-related activity logging
- CSV export functionality
- Filtering by:
    - User type
    - Action type
    - Date range
    - Search terms

---

## 🌐 Frontend (Public) Features

### Landing Pages

- Medical landing page
- About us page
- Services showcase
- Doctors listing with specializations
- Contact page

### Appointment Booking

- Online appointment booking
- Doctor selection
- Service selection
- Time slot picker
- Patient information form

### Legal Pages

- Terms & conditions
- Privacy policy

### CMS Features

- Sliders/banners management
- Content management

---

## ⚙️ Settings & Configuration

### General Settings

- Clinic name
- Email address
- Phone number
- Address
- Logo management

### Operational Settings

- Clinic schedules
- Holiday management
- Working hours

### User Preferences

- Dark mode support
- Language/Localization support
- Email notification preferences
- Timezone settings

---

## 🔐 Permissions System

| Permission                 | Description                |
| -------------------------- | -------------------------- |
| `manage_admin_dashboard`   | Admin dashboard access     |
| `manage_doctors`           | Doctor CRUD operations     |
| `manage_patients`          | Patient CRUD operations    |
| `manage_appointments`      | Appointment management     |
| `manage_patient_visits`    | Visit/encounter management |
| `manage_services`          | Services management        |
| `manage_specialties`       | Specializations management |
| `manage_doctor_sessions`   | Session scheduling         |
| `manage_doctors_holiday`   | Holiday management         |
| `manage_medicines`         | Medicine inventory         |
| `manage_transactions`      | Transaction viewing        |
| `manage_request_documents` | Consultation forms         |
| `manage_front_cms`         | CMS/banners management     |
| `manage_settings`          | System settings            |
| `manage_roles`             | Role management            |
| `manage_staff`             | Staff management           |
| `manage_countries`         | Country data               |
| `manage_states`            | State data                 |
| `manage_cities`            | City data                  |

---

## 📊 Dashboard Features

### Admin Dashboard

- Statistics overview
- Appointment charts
- Patient data visualization
- Recent activity

### Doctor Dashboard

- Today's appointments
- Patient visits summary
- Quick actions

### Staff Dashboard

- Queue management
- Appointment overview
- Patient registration shortcuts

### Patient Dashboard

- My appointments
- Medical history
- Prescription history
- Default password change prompt

---

## 🛠️ Technical Features & Integrations

### Real-time Tables

- Livewire DataTables (rappasoft/laravel-livewire-tables)
- Sorting, filtering, pagination
- Export capabilities

### PDF Generation

- DomPDF integration
- Prescription PDFs
- Medical certificate PDFs
- Appointment PDFs

### Media Management

- Spatie MediaLibrary
- Profile pictures
- Consultation images
- Document attachments

### Export Features

- Maatwebsite Excel
- CSV exports
- Activity log exports

### QR Codes

- SimpleSoftwareIO QR code generation
- Patient identification

### Real-time Communication

- Pusher integration
- Laravel WebSockets
- Notifications

### Security Features

- User impersonation (Lab404)
- XSS protection (HTML Purifier)
- Role-based access control
- Email verification

### Development Tools

- Log Viewer (OpCodes)
- Laravel Debugbar
- IDE Helper

---

## 📁 Database Structure

### Core Tables

- `users` - User accounts
- `patients` - Patient records
- `doctors` - Doctor profiles
- `appointments` - Appointment records
- `visits` - Patient visits
- `prescriptions` - Prescription records

### Medicine Tables

- `categories` - Medicine categories
- `generics` - Generic names
- `medicines` - Medicine inventory
- `medicine_availabilities` - Stock records
- `purchased_medicines` - Stock items
- `used_medicines` - Consumption records
- `medicine_bills` - Distribution history

### Support Tables

- `services` - Clinic services
- `service_categories` - Service groupings
- `specializations` - Doctor specialties
- `doctor_sessions` - Schedule sessions
- `clinic_schedules` - Clinic hours
- `holidays` - Holiday calendar

### Location Tables

- `countries` - Country list
- `states` - State/province list
- `cities` - City list
- `addresses` - Address records

### University-Specific Tables

- `campuses` - University campuses
- `colleges` - Colleges
- `courses` - Course programs
- `year_levels` - Year levels

### System Tables

- `activity_logs` - Audit trail
- `notifications` - User notifications
- `settings` - System settings
- `roles` - User roles
- `permissions` - System permissions

---

## 🔄 Key Workflows

### Patient Registration Flow

1. Staff creates patient record
2. System generates unique patient ID
3. Address information captured
4. Activity logged

### Appointment Booking Flow

1. Patient/Staff selects doctor
2. Selects service
3. Picks available time slot
4. Appointment created with "Booked" status
5. Doctor accepts/finishes appointment

### Consultation Flow

1. Patient added to queue
2. Latest consultation form auto-attached
3. Doctor calls next patient
4. Consultation recorded
5. Prescription issued if needed
6. Queue entry completed

### Medicine Dispensing Flow

1. Medicine availability recorded
2. Stock quantities updated
3. Medicine used for patient
4. Usage logged in activity

---

## 📝 Version Information

- **Laravel Version**: 10.x
- **PHP Version**: 8.1+
- **Livewire Version**: 3.x
- **Last Updated**: February 2026

---

## 📚 Related Documentation

- [Activity Log Implementation](ACTIVITY_LOG_IMPLEMENTATION.md)
- [Patient Queue Consultation Integration](PATIENT_QUEUE_CONSULTATION_INTEGRATION.md)
- [Patient Cascade Deletion System](PATIENT_CASCADE_DELETION_SYSTEM.md)
- [Auto Backup System](AUTO_BACKUP_SYSTEM.md)
