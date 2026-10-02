<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Consultation Form</title>
    <style>
        @page {
            size: 8.5in 13in portrait;
            margin: 30px 40px 30px 40px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
        }

        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-top: 18px;
            margin-bottom: 6px;
            border-bottom: 1px solid #aaa;
            padding-bottom: 2px;
        }

        .info-table,
        .info-table th,
        .info-table td {
            border: 1px solid #bbb;
            border-collapse: collapse;
        }

        .info-table {
            width: 100%;
            margin-bottom: 12px;
        }

        .info-table th,
        .info-table td {
            padding: 6px 8px;
            font-size: 12px;
            vertical-align: top;
        }

        .info-table th {
            background: #f4f4f4;
            width: 22%;
        }

        .info-table td {
            width: 28%;
        }

        .row {
            display: flex;
            gap: 24px;
            margin-bottom: 8px;
        }

        .col {
            flex: 1;
        }

        .label {
            font-weight: bold;
        }

        .mb-2 {
            margin-bottom: 12px;
        }

        .mb-4 {
            margin-bottom: 24px;
        }
    </style>
</head>

<body>
    @php
    $cleanValue = static function ($value): string {
    if ($value === null) {
    return '';
    }

    $text = trim((string) $value);
    if ($text === '') {
    return '';
    }

    $normalized = strtolower($text);
    if ($normalized === 'unknown' || str_starts_with($normalized, 'unknown ')) {
    return '';
    }

    if (in_array($normalized, ['n/a', 'na', 'null'], true)) {
    return '';
    }

    return $text;
    };

    $documentUser = $requestDocument->user_id
    ? \App\Models\User::with(['department:id,department_name', 'office:id,office_name'])->find($requestDocument->user_id)
    : null;

    $pdfCampus = $cleanValue($requestDocument->campus);
    $pdfCollege = $cleanValue($requestDocument->college);
    $pdfCourse = $cleanValue($requestDocument->course);
    $pdfYearLevel = $cleanValue($requestDocument->year_level);
    $pdfInformant = $cleanValue($requestDocument->informant);
    $pdfDepartment = $cleanValue($documentUser?->department?->department_name);
    $pdfOffice = $cleanValue($documentUser?->office?->office_name);

    $patientTypeSource = strtolower(trim((string) ($pdfInformant !== '' ? $pdfInformant : $pdfYearLevel)));
    $isFacultyType = $patientTypeSource === 'faculty';
    $isStaffType = $patientTypeSource === 'staff';
    $isGuestType = $patientTypeSource === 'guest';
    $isStudentType = ! $isFacultyType && ! $isStaffType && ! $isGuestType;

    $showCampus = $isStudentType && $pdfCampus !== '';
    $showCollege = ($isStudentType || $isFacultyType) && $pdfCollege !== '';
    $showCourseYear = $isStudentType && ($pdfCourse !== '' || $pdfYearLevel !== '');
    $showDepartment = $isFacultyType && $pdfDepartment !== '';
    $showOffice = $isStaffType && $pdfOffice !== '';
    $hasInstitutionalInfo = $showCampus || $showCollege || $showCourseYear || $showDepartment || $showOffice;

    $pdfEmergencyContact = $cleanValue($requestDocument->emergency_contact);

    $nursingInChargeUser = $requestDocument->nursing_incharged_id
    ? \App\Models\User::find($requestDocument->nursing_incharged_id)
    : null;
    $nursingInCharge = trim((string) (($nursingInChargeUser?->first_name ?? '') . ' ' . ($nursingInChargeUser?->last_name ?? '')));
    $nursingInCharge = $nursingInCharge !== '' ? $nursingInCharge : 'N/A';
    @endphp

    <div class="header">
        <h2>Negros Oriental State University</h2>
        <div>University Medical Clinic, CNPAHS Bldg., Kagawasan Ave., Dumaguete City</div>
        <div>Tel #: 225-9400, then Local # 188, 09263829484</div>
        <h3 style="margin-top: 12px;">CONSULTATION FORM</h3>
    </div>

    <div class="section-title">Personal Information</div>
    <table class="info-table">
        <tr>
            <th>Name</th>
            <td>{{ $requestDocument->name }}</td>
            <th>Age</th>
            <td>{{ $requestDocument->age }}</td>
        </tr>
        <tr>
            <th>Gender</th>
            <td>{{ $requestDocument->gender }}</td>
            <th>Status</th>
            <td>{{ $requestDocument->status }}</td>
        </tr>
        <tr>
            <th>Date of Birth</th>
            <td>{{ $requestDocument->date_of_birth }}</td>
            <th>Address</th>
            <td>{{ $requestDocument->address }}</td>
        </tr>
        <tr>
            <th>Religion</th>
            <td>{{ $requestDocument->religion }}</td>
            <th>Patient Contact</th>
            <td>{{ $requestDocument->patient_contact }}</td>
        </tr>
    </table>

    @if($hasInstitutionalInfo)
    <div class="section-title">Institutional Information</div>
    <table class="info-table">
        @if($showCampus || $showCollege)
        <tr>
            <th>Campus</th>
            <td>{{ $showCampus ? $pdfCampus : '' }}</td>
            <th>College</th>
            <td>{{ $showCollege ? $pdfCollege : '' }}</td>
        </tr>
        @endif
        @if($showCourseYear)
        <tr>
            <th>Course</th>
            <td>{{ $pdfCourse }}</td>
            <th>Year Level</th>
            <td>{{ $pdfYearLevel }}</td>
        </tr>
        @endif
        @if($showDepartment)
        <tr>
            <th>Department</th>
            <td colspan="3">{{ $pdfDepartment }}</td>
        </tr>
        @endif
        @if($showOffice)
        <tr>
            <th>Office</th>
            <td colspan="3">{{ $pdfOffice }}</td>
        </tr>
        @endif
    </table>
    @endif

    @if($pdfInformant !== '' || $pdfEmergencyContact !== '')
    <div class="section-title">Emergency Contact</div>
    <table class="info-table">
        <tr>
            <th>Informant</th>
            <td>{{ $pdfInformant }}</td>
            <th>Contact Person & Number</th>
            <td>{{ $pdfEmergencyContact }}</td>
        </tr>
    </table>
    @endif

    <div class="section-title">Complaints</div>
    <table class="info-table">
        <tr>
            <th>Complaint/s</th>
            <td colspan="3">{{ $requestDocument->complaints }}</td>
        </tr>
    </table>

    <div class="section-title">Subjective Complaints</div>
    <table class="info-table">
        <tr>
            <th>COVID Vaccination</th>
            <td>{{ $requestDocument->covid_vaccination }}</td>
            <th>Comorbidities</th>
            <td>{{ $requestDocument->comorbidities }}</td>
        </tr>
        <tr>
            <th>Allergies</th>
            <td>{{ $requestDocument->allergies }}</td>
            <th>Admissions/Surgeries</th>
            <td>{{ $requestDocument->admissions_surgeries }}</td>
        </tr>
        <tr>
            <th>Maintenance</th>
            <td>{{ $requestDocument->maintenance }}</td>
            <th>Pregnancy Status</th>
            <td>{{ $requestDocument->pregnancy_status }}</td>
        </tr>
        <tr>
            <th>LMP/AOG</th>
            <td>{{ $requestDocument->lmp_aog }}</td>
            <th></th>
            <td></td>
        </tr>
    </table>

    <div class="section-title">Objective Data</div>
    <table class="info-table">
        <tr>
            <th>BP</th>
            <td>{{ $requestDocument->vital_signs_bp }}</td>
            <th>PR</th>
            <td>{{ $requestDocument->vital_signs_pr }}</td>
        </tr>
        <tr>
            <th>Temp</th>
            <td>{{ $requestDocument->vital_signs_temp }}</td>
            <th>RR</th>
            <td>{{ $requestDocument->vital_signs_rr }}</td>
        </tr>
        <tr>
            <th>O2 Sat</th>
            <td>{{ $requestDocument->vital_signs_o2_sat }}</td>
            <th>Weight</th>
            <td>{{ $requestDocument->vital_signs_weight }}</td>
        </tr>
        <tr>
            <th>Pertinent Exam</th>
            <td colspan="3">{{ $requestDocument->pertinent_exam }}</td>
        </tr>
    </table>

    <div class="section-title">Assessment</div>
    <table class="info-table">
        <tr>
            <th>Assessment</th>
            <td colspan="3">{{ $requestDocument->assessment }}</td>
        </tr>
    </table>

    <div class="section-title">Plan</div>
    <table class="info-table">
        <tr>
            <th>Plan</th>
            <td colspan="3">{{ $requestDocument->plan }}</td>
        </tr>
    </table>

    <div class="section-title">Other Details</div>
    <table class="info-table">
        <tr>
            <th>Consult Mode</th>
            <td>{{ ucwords(strtolower($requestDocument->consult_mode)) }}</td>
            <th>Nursing In-Charge</th>
            <td>{{ $nursingInCharge }}</td>
        </tr>
        <tr>
            <th>Illness / diagnosis</th>
            <td colspan="3">{{ implode('; ', $requestDocument->illnessLabels()) ?: 'Not classified' }}</td>
        </tr>
        <tr>
            <th>Services rendered</th>
            <td colspan="3">{{ implode('; ', $requestDocument->serviceLabels()) ?: 'None ticked' }}</td>
        </tr>
        <tr>
            <th>Nursing Intervention</th>
            <td colspan="3">{{ $requestDocument->nursing_intervention }}</td>
        </tr>
    </table>
</body>

</html>