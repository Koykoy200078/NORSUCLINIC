# Link audit — 2026-10-08

**Trigger:** the public landing page (`/`) has a "Dashboard" button. Signed in as a doctor / staff / nurse, pressing it opened
`/admin/patients` and answered **403 "User does not have the right roles"**. The page was supposed to follow whoever signed in.

This document records the root cause, the runtime audit that was built to "check it again and again", everything it found,
and what is deliberately left open.

---

## 1. Root cause of the reported bug

Three separate copies of "which dashboard is mine?" existed and two of them were wrong:

| Where | What it did | Wrong for |
|---|---|---|
| `resources/views/fronts/medicals/index.blade.php` (hero button) | `route('patients.index')` for **every** signed-in user. `patients.index` is `/admin/patients` (`role:clinic_admin`) | doctor, staff, nurse — the 403 in the screenshot |
| `resources/views/fronts/layouts/header.blade.php` (top-right button) | `if doctor … elseif staff … else admin.dashboard` | nurse-role accounts and any account with no dashboard (patient role, no role) → admin URL → 403 |
| `getDashboardURL()` fallback in `app/helpers.php` | ended in `RouteServiceProvider::HOME` = `admin/dashboard` | role-less / patient-role users: `/login`, e-mail verification and every auth redirect dropped them on a 403 page |

**Fix.** One source of truth, `getDashboardRouteName(?user): ?string` (`app/helpers.php`): admin → `admin.dashboard`,
staff **and nurse** → `staff.dashboard`, doctor → `doctors.dashboard`, patient → `patients.dashboard` *if that route exists*,
otherwise `null`. `getDashboardURL()` is built on it and, for a signed-in user with no dashboard, now returns the public
landing page instead of the admin dashboard. Both landing buttons call the helper and draw **nothing** when it returns `null`.
The helper is still the only place that maps role → dashboard (`ForceAdminDefaultPasswordChange` keeps its own, equivalent
map on purpose: it also keys on `users.type`).

Behaviour change to be aware of: a signed-in account with **no role** used to be sent to `/admin/dashboard` (403); it now lands on `/`.
The two stock Breeze tests that logged in such a user and asserted the old redirect (`AuthenticationTest`, `EmailVerificationTest`)
were updated to assert the landing page.

Regression test: `tests/Feature/Regression/LandingPageDashboardTest.php` (admin, doctor, 12 staff designation/station pairs,
nurse-role, guest, role-less, patient-role — both buttons, then the link is followed and must answer 200).

---

## 2. The audit: "does every link I am shown open for me?"

`tests/Feature/Audit/LinkCrawlTest.php` signs in as every kind of user (guest, admin, doctor, 12 staff pairs, nurse-role),
opens the landing page and every parameter-free page of the user's own panel, **loads the lazy Livewire tables the way the
browser does** (28 table components are `#[Lazy]`, so their rows are not in the page HTML — the first version of the crawler
missed them and under-reported), then follows every `<a href>` two levels deep as the same user. A link answering
403 / 404 / 405 / 419, or a page answering 5xx, is a defect.

It is slow (about 5–8 minutes) so it is **skipped unless asked for**:

```powershell
$env:LINK_CRAWL = '1'; vendor/bin/phpunit --filter LinkCrawlTest; Remove-Item Env:LINK_CRAWL
```

Run it after touching any menu, list or "back / cancel / view / edit" link. Add a fixture row in
`buildActorsAndRows()` when a new screen gets row-level links, otherwise the crawler has nothing to click.

First full run (before the fixes): 16 kinds of user, 1,220 requests, 14 defects (below). After the fixes the same audit,
run as the permanent test: **1,142 requests, 1,092 pages, 0 problems** (2026-10-08). Full suite the same day: 338 tests, 3,858
assertions, all passing, 1 skipped (the opt-in crawler).

Note when running it: it shares the `norsu_clinic_test` schema with the normal suite, so never start it while another phpunit
process is running (another session doing so made one run fail in `migrate:fresh` during this work).

### Defects found and fixed

| # | Defect | Who got the 403 / 500 | Fixed in |
|---|---|---|---|
| 1 | Landing page hero button → `/admin/patients` | doctor, staff, nurse | `fronts/medicals/index.blade.php` |
| 2 | Landing page header button: nurse / role-less → admin dashboard | nurse-role, role-less | `fronts/layouts/header.blade.php` |
| 3 | `getDashboardURL()` fallback = admin dashboard | role-less, patient-role | `app/helpers.php` |
| 4 | **Editing a medical certificate or excuse slip crashed (500)** — `Undefined variable $initialYearLevelId` (and `$medicinesByCategoryUrl`): the page's script uses variables that only the consultation-form branch defines. Latent since the year-level work (`b76df32`, `5dd2a60`) | everybody | `document_issuances/edit.blade.php` |
| 5 | Queue edit page "Cancel" hard-wired to `staff.patient-queue.index` | admin | `patient_queue/edit.blade.php` |
| 6 | Add-doctor form "Discard" hard-wired to the admin doctor list | clinic head | `doctors/fields.blade.php` |
| 7 | Doctor list: name / photo → `/admin/doctors/{id}` | clinic head | `doctors/components/doctor_name.blade.php` |
| 8 | Prescriptions list: "New Prescription", patient and doctor links shown without the patients / doctors module | pharmacist | `prescriptions/add-button.blade.php`, `prescriptions/columns/patient_name`, `…/doctor_name` |
| 9 | Dispensing history: patient / doctor links, same cause (the `else` fell through to the admin URL) | pharmacist | `medicine-history/columns/patient`, `…/doctor` |
| 10 | Activity logs › Visits: "View" offered on consultations the station cannot open | records, front-desk, observation-room staff | `activity_logs/reports/visits.blade.php` |
| 11 | Staff dashboard "recent patients": every name linked | pharmacist | `livewire/staff-dash-board-table.blade.php` |
| 12 | Patient list: "Create Prescription" and the consultation-count link shown to every staff member | nurse, triage, front desk, records | `patients/components/action.blade.php`, `action_prescription`, `consultation_form_count` |
| 13 | Patient history page: Edit / PDF / Delete on consultations, certificates, excuse slips and "View" on prescriptions shown without the matching module | same | `patients/view_patient.blade.php` |
| 14 | A patient's consultation list: "Create Prescription" | same | `document_issuances/index.blade.php` |

Rule applied for #8–#14: a link or button is drawn **only when `canStaffAccessModule(<module>)` is true** (it is always true
for admin and doctor). Nothing about who may do what was changed — only what is *shown*. Where a count is informative
(consultations on the patient list) it stays visible as plain text.

Regression tests: `tests/Feature/Regression/RoleAwareLinksTest.php` (each item above; the patient list / history test runs all
12 staff pairs against the module policy, so it fails if a button and the policy ever disagree again).

---

## 3. Found but deliberately NOT changed (needs your decision)

1. **Seven pages carry JavaScript that never loads.** They declare `@section('scripts')` (or `@section('page_css')`), but
   `layouts/app.blade.php` only outputs `@yield('page_js')` and `@stack('scripts')`, so the section is silently discarded:
   `patient_queue/create` (the Select2 patient search **and** the "latest consultation form" preview on *Add to queue*),
   `medicines/create`, `medicines/edit`, `lab_requests/edit`, `generics/create`, `generics/edit`, `generics/index`
   (+ `page_css` on generics create / edit). Turning them on (`@push('scripts')`) changes how those pages behave, so it needs a
   browser pass first. Recommended: do it page by page, starting with the queue.
2. Hard-coded root-relative URLs in browser code: `patient_queue/create.blade.php` (`/api/patient/{id}/latest-consultation`),
   `resources/assets/js/custom/custom.js` (`/csrf-token`, `/change-language`). They work at the site root (how the clinic runs);
   they would break under a sub-folder install. Moot for the queue one until item 1 is decided.
3. `AuthenticatedSessionController::store()` sends a default-password administrator to `admin.dashboard` based on
   `users.type` rather than the role; an account with `type = ADMIN` but a different role would get a 403. Not reachable with
   the seeded data (type and role always match); left as is.
4. `/privacy-policy` contains two plain external reference links (cookie guides). They are text links, not assets, so the
   offline rule is not broken, but they cannot open without internet.

## 4. What the audit cannot see

Links created by JavaScript after load (JsRender templates, `wire:click`), POST-only buttons, rows that no fixture creates,
pages needing a parameter nobody links to, and the visual side (a button hidden by CSS still counts as a link). It checks
**status codes**, not whether the page behind a link is the *right* page.
