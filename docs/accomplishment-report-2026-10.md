# ACCOMPLISHMENT REPORT, report filters and search fixes (2026-10)

Branch `changes_v2`. Asked for: filters on the Report Generation screen, the clinic's ACCOMPLISHMENT REPORT (illness by
body system and services, counted per college, as in the clinic's paper form), an Export CSV that carries the patient
names, and a fix for name searches that found nothing.

## What changed

### 1. Search finds names (every module)
* `App\Support\SearchTerm` splits what is typed into words; a row matches when **every word** is found in one of the
  searched columns. Works with `Ana Reyes`, `Reyes, Ana`, a middle name, any case, accents (`Pena` finds `Peña`),
  wildcards (`ana*`, `a%a`, `r?yes`), extra spaces. Limits: 8 words, 100 characters.
* `App\Livewire\Concerns\SearchesByWords` applies it to every Rappasoft table (`LivewireTableComponent`,
  `LabRequestTable`, `DocumentIssuanceTable`). The Consultations list had no searchable patient-name column at all.
* The two `searchUsers` pickers, the report tabs and Global Search use the same helper.

### 2. Export CSV has names and follows the filters
* One query per report tab in `App\Services\Reports\ReportQueries`, used by **both** the screen and the CSV
  (`ReportCsv`, `ActivityLogController::export`). The file starts with a UTF-8 BOM (Excel shows `Peña` correctly) and
  neutralises `= + - @` formulas.
* Dispensing CSV now has the patient name; Patient Visits CSV also carries patient type, campus, college, course, year
  level, illness, services, consult mode, nurse in charge and encoder.

### 3. A consultation records WHICH illness and WHICH services
* Tables `illness_systems`, `illnesses`, `service_types`, `consultation_illnesses`, `consultation_services`; the lists
  of the paper report (11 body systems, ~90 illnesses, 25 services) are loaded by the migration and
  `IllnessSeeder` / `ServiceTypeSeeder`.
* `document_issuances` gets snapshot ids (`campus_id, college_id, course_id, year_level_id, department_id, office_id,
  patient_type_id`) so the report groups a visit by what the patient was **on the day** (`ConsultationSnapshotBackfill`
  fills old rows from the saved names, then from the patient record).
* Consultation form (create and edit, staff/nurse and doctor): searchable illness picker grouped by body system with an
  "Others: ___" text, and a services checklist. Shown on the consultation view page and its PDF.
* Settings > General: **University Physician** name and title (the "Noted by" line). Graduate School (GS) added as a
  college. Settings > General could not be saved at all (the validator required a `language` field the form no longer
  has); fixed.
* Settings > **Report lists** (admin): add, rename, regroup or switch off illnesses and services. Lines are never deleted
  (consultations point at them); a switched-off line leaves the form, keeps its history in the report, and an old
  consultation that has it ticked still shows it ticked when edited.

### 4. Filters and the ACCOMPLISHMENT REPORT
* Report Generation > **Patient Visits** and the new **Accomplishment Report** tab share one filter panel:
  period buttons (this/last month, this/last year), month picker, date range, campus, college, course, year level,
  department (faculty), office (staff), patient type, gender, consult mode, age group, body system, illness (or "not
  classified yet"), service, medicine given, nurse in charge / encoder, pregnancy, chronic condition (any/none/contains).
  All of it lives in `ReportFilters` + `ConsultationFilter`; the screen and every download use them.
* `AccomplishmentReportBuilder` -> the matrix. One screen, one PDF (8.5x13 in portrait), one Excel sheet and one flat CSV,
  all rendered from the builder's result (`activity_logs/reports/accomplishment_*.blade.php`), so they show the same
  numbers. Routes: `activity-logs/accomplishment/{pdf|xlsx|csv}` (admin, `staff.`, `doctors.`).
* "Prepared by" = whoever downloads it (name + staff designation or role). "Noted by" = University Physician in Settings.

## Counting rules (what the numbers mean)
* A visit is dated by its **consultation date** (`requested_at`; the day it was typed in if that is missing).
* One count per consultation x picked illness: a visit with two illnesses adds to both rows, so the illness TOTAL can
  exceed the number of consultations. The page prints "Consultations in this report" and "Unclassified consultations"
  (visits with no illness picked) so the totals reconcile.
* Column: student -> their college that day; faculty and staff -> F&S; guest -> Guests/Others (column only when it has
  data); student without a college -> Unspecified (only when it has data). Graduate School is the last college column.
* The Accomplishment Report starts on **Walk-in** (`consult_mode = physical`); change the Consult mode filter for All or
  Virtual. The other tabs start on All.
* Services are counted once per consultation whether ticked, recognised by the system, or both:
  Medicine assistance = medicines given on the visit; Medical certificate issuance / Request for laboratory exam = one
  made for the same patient on the visit date; Height/Weight/BMI = height or weight recorded; BP / Vital signs / O2
  "only" = that measurement alone was taken and no illness was picked.
* Pregnancy: blank / N/A / No / None / dash = not pregnant, anything else = pregnant. Chronic condition reads the
  comorbidities text the same way.

## Deploy
```
php artisan migrate        # creates the tables, loads the lists, adds Graduate School and the physician settings,
                           # and fills the snapshot ids of existing consultations
```
Then, as administrator: Settings > General > fill **University Physician**. No JavaScript assets changed, so no
`npm run prod`. Old consultations have no illness picked, so the first reports show them as "Unclassified" until new
visits are classified.

## Tests
`SearchTermTest` (unit), `SearchBehaviourTest`, `ReportCsvParityTest`, `ConsultationClassificationTest`,
`AccomplishmentReportTest`, `ReportListsTest` (all in `tests/Feature/Regression`). `phpunit.xml` now sets
`memory_limit=512M`: the single-process suite ran out of the CLI default of 128M around 230 tests.
