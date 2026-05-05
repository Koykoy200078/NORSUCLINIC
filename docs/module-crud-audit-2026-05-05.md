# NORSUCLINIC Module Audit (CRUD + Subparents)

Date: 2026-05-05

## Scope Used For This Audit

- resources/views/layouts/menu.blade.php
- resources/views/layouts/sub_menu.blade.php
- routes/web.php
- routes/staff.php
- routes/doctor.php
- routes/patient.php
- storage/app/route-list.json (generated via `php artisan route:list --json`)

## Legend

- CRUD = Create, Read, Update, Delete all available
- C/R/U/D = only the listed operations are available
- R = read-only
-   - = no direct module access routes found for that role

## Parent Module Order (Sidebar Lock)

1. Dashboard
2. Patients / Patient Record Management
3. Patient Queuing
4. Consultation Management
5. Prescription Management
6. Medicine Inventory Tracking
7. Medicine Dispensing Management
8. Laboratory & Medical Request Management
9. Certificate Issuance
10. Report Generation
11. Notifications & Alerts
12. Settings

## Parent -> Subparent Module Tree

### 1) Dashboard

- Dashboard

### 2) Patients / Patient Record Management

- Patients

### 3) Patient Queuing

- Patient Queue

### 4) Consultation Management

- Document Issuances (filtered by `?module=consultation`)

### 5) Prescription Management

- Prescriptions

### 6) Medicine Inventory Tracking

- Categories
- Generics
- Medicines
- Stock In

### 7) Medicine Dispensing Management

- Dispensing Dashboard (`medicine-dispensing.index`)
- Dispense Records

### 8) Laboratory & Medical Request Management

- Lab Requests

### 9) Certificate Issuance

- Document Issuances (filtered by `?module=certificate`)

### 10) Report Generation

- Activity Logs / Reports

### 11) Notifications & Alerts

- Low stocks alert (activity logs tab filter)
- Expiry medicine alert (inventory shortcut)
- System notifications (activity logs tab filter)

### 12) Settings

- Staffs
- Doctors
- Manage User Roles
- Specializations
- Manage System Settings
- Front CMS
- Banner/Sliders
- Countries
- States
- Cities
- Barangays
- Backup Data

## CRUD Audit Matrix (Core Modules)

| Parent Module           | Subparent / Resource                       | Admin | Staff/Nurse | Doctor | Patient | Notes                                                                     |
| ----------------------- | ------------------------------------------ | ----- | ----------- | ------ | ------- | ------------------------------------------------------------------------- |
| Dashboard               | dashboard                                  | R     | R           | R      | R       | Per-role dashboard endpoints                                              |
| Patients / Records      | patients                                   | CRUD  | CRUD        | CRUD   | -       | Patient role has no patient-record CRUD module                            |
| Patient Queuing         | patient-queue                              | CRUD  | CRUD        | R      | -       | Doctor has queue workflow actions, not full CRUD                          |
| Consultation Management | document-issuances (`module=consultation`) | CRUD  | CRUD        | CRUD   | -       | Same resource as certificates, filtered by query                          |
| Prescription Management | prescriptions                              | CRUD  | CRUD        | CRUD   | CRUD    | Patient has no `index`, but has store/show/update/destroy + helper routes |
| Inventory Tracking      | categories                                 | CRUD  | CRUD        | CRUD   | -       |                                                                           |
| Inventory Tracking      | generics                                   | CRUD  | CRUD        | CRUD   | -       |                                                                           |
| Inventory Tracking      | medicines                                  | CRUD  | CRUD        | CRUD   | -       |                                                                           |
| Inventory Tracking      | stock-in                                   | CRUD  | CRUD        | CRUD   | -       |                                                                           |
| Dispensing Management   | medicine-dispensing dashboard              | R     | R           | R      | -       | Dashboard/list route                                                      |
| Dispensing Management   | dispense-records                           | CRUD  | CRUD        | CRUD   | -       |                                                                           |
| Laboratory Requests     | lab-requests                               | CRUD  | CRUD        | CRUD   | C/R     | Patient routes are create/store/index/show/pdf                            |
| Certificate Issuance    | document-issuances (`module=certificate`)  | CRUD  | CRUD        | CRUD   | -       | Same resource as consultations, filtered by query                         |
| Report Generation       | activity-logs                              | R     | R           | R      | -       | index/show/export                                                         |
| Notifications & Alerts  | notification links                         | R     | R           | R      | -       | Shortcut links to reports/inventory                                       |

## CRUD Audit Matrix (Settings Subparents)

| Settings Subparent     | Resource / Route Group         | Admin           | Staff/Nurse | Doctor | Patient | Notes                                                          |
| ---------------------- | ------------------------------ | --------------- | ----------- | ------ | ------- | -------------------------------------------------------------- |
| Staffs                 | staffs                         | CRUD            | -           | -      | -       |                                                                |
| Doctors                | doctors                        | CRUD            | CRUD        | R      | -       | Doctor has detail-only route                                   |
| Manage User Roles      | roles                          | CRUD            | R           | -      | -       | Staff has index/show only                                      |
| Specializations        | specializations                | CRUD            | CRUD        | CRUD   | -       |                                                                |
| Manage System Settings | setting.index / setting.update | R/U             | R           | -      | -       | Staff has view-only settings route                             |
| Front CMS              | cms                            | R/U             | R/U         | -      | -       | No create/delete resource actions                              |
| Banner/Sliders         | banner                         | R/U             | R/U         | -      | -       | Resource excludes create/store/show/destroy                    |
| Countries              | countries                      | CRUD            | R           | -      | -       | Staff has index/show only                                      |
| States                 | states                         | CRUD            | -           | -      | -       |                                                                |
| Cities                 | cities                         | CRUD            | -           | -      | -       |                                                                |
| Barangays              | barangays                      | CRUD            | -           | -      | -       |                                                                |
| Backup Data            | backups                        | C/R/D (+import) | -           | -      | -       | Custom backup endpoints (index/create/download/destroy/import) |

## Non-CRUD Supporting Endpoints By Module

- Patients:
    - `patients.restore`
    - `patients.showMyHistory`
    - `patients.reset.password`
- Patient Queue:
    - `patient-queue.refresh`
    - `patient-queue.call-next`
    - `patient-queue.complete`
    - `patient-queue.view-consultation` (doctor)
- Document Issuances:
    - `document-issuances.search-users`
    - `document-issuances.get-last-consultation`
    - `document-issuances.get-last-medical-certificate`
    - `document-issuances.export-pdf`
- Lab Requests:
    - `lab-requests.search-users`
    - `lab-requests.update-status`
    - `lab-requests.pdf`
- Prescriptions:
    - `prescription.medicine.store`
    - `prescription.status`
    - `prescriptions.dispense`
    - `prescription.medicine.show`
    - `prescriptions.pdf`
- Inventory/Dispensing:
    - `medicine-inventory.index`
    - `medicines.show.modal`
    - `check.use.medicine`
    - `medicines.by.category`
    - `stock-in.excel`
    - `get-medicine`
    - `dispense-records.store-patient`
    - `dispense-records.pdf`
    - `dispense-records.by-category`

## Audit Notes / Gaps

1. Staff module has read-only `countries` and `roles`, but no staff CRUD routes for `states`, `cities`, and `barangays`.
2. Doctor role can access doctor detail route, but no doctor-management CRUD module.
3. `Consultations` and `Certificates` share the same `document-issuances` CRUD resource and are separated by query filtering.
4. Banner management is intentionally partial (index/edit/update only).

## Conclusion

The system has complete CRUD coverage for most core clinical modules (patients, consultations/certificates, prescriptions, inventory resources, dispensing records, lab requests) for admin, with staff/doctor coverage aligned to role boundaries. Settings subparents are intentionally mixed between full CRUD, partial CRUD, and read-only access depending on role and module policy.
