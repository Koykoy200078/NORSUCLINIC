<!DOCTYPE html>
<html>

<head>
    <title>Request Document</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .table th,
        .table td {
            text-align: left;
            vertical-align: top;
            padding: 2px;
            font-size: 12px;
        }

        .table th {
            font-weight: bold;
            width: 20px;
            /* Specific width for table headers */
        }

        .table td {
            width: 50px;
            /* Allow natural spacing for table cells */
        }
    </style>
</head>

<body>
    @php $documentTypes = [ 'medical_certificate' => 'Medical Certificate', 'referral_letter' => 'Referral Letter', 'clearance' => 'Clearance', 'other' => 'Other', ]; @endphp

    <div class="header">
        <h1>{{ $documentTypes[$requestDocument->document_type] ?? ucfirst(str_replace('_', ' ', $requestDocument->document_type)) }}</h1>
    </div>

    <!-- Personal Information Section -->
    <div class="section">
        <h3 class="section-title">Personal Information</h3>
        <table class="table">
            <!-- 1st row -->
            <tr>
                <th>Name</th>
                <td>{{ $requestDocument->name }}</td>
                <th>Age</th>
                <td>{{ $requestDocument->age }}</td>
                </td>
                <!-- 2nd row -->
            <tr>
                <th>Gender</th>
                <td>{{ $requestDocument->gender }}</td>
                <th>Status</th>
                <td>{{ $requestDocument->status }}</td>
            </tr>
            <!-- 3rd row -->
            <tr>
                <th>Date of Birth</th>
                <td>{{ $requestDocument->date_of_birth }}</td>
                <th>Address</th>
                <td>{{ $requestDocument->address }}</td>
            </tr>
            <!-- 4th row -->
            <tr>
                <th>Religion</th>
                <td>{{ $requestDocument->religion }}</td>
                <th>Patient's Contact</th>
                <td>{{ $requestDocument->patient_contact }}</td>
            </tr>
        </table>
    </div>

    <!-- Educational Information Section -->
    <div class="section">
        <h3 class="section-title">Educational Information</h3>
        <table class="table">
            <tr>
                <th>Campus</th>
                <td>{{ $requestDocument->campus }}</td>
                <th>College</th>
                <td>{{ $requestDocument->college }}</td>
            </tr>
            <tr>
                <th>Course</th>
                <td>{{ $requestDocument->course }}</td>
                <th>Year Level</th>
                <td>{{ $requestDocument->year_level }}</td>
            </tr>
        </table>
    </div>

    <!-- Emergency Contact Section -->
    <div class="section">
        <h3 class="section-title">Emergency Contact</h3>
        <table class="table">
            <tr>
                <th>Informant</th>
                <td>{{ $requestDocument->informant }}</td>
                <th>Contact Person & Number</th>
                <td>{{ $requestDocument->emergency_contact }}</td>
            </tr>
        </table>
    </div>

    <!-- Complaints Section -->
    <div class="section">
        <h3 class="section-title">Complaints</h3>
        <table class="table">
            <tr>
                <th>Complaint/s</th>
                <td>{{ $requestDocument->complaints }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3 class="section-title">S (Subjective Complaints)</h3>
        <table class="table">
            <tr>
                <th>COVID Vaccination</th>
                <td>{{ $requestDocument->covid_vaccination }}</td>
                <th>Comorbidities/s</th>
                <td>{{ $requestDocument->comorbidities }}</td>
            </tr>

            <tr>
                <th>Allergies/s</th>
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
            </tr>
        </table>
    </div>

    <div class="section">
        <h3 class="section-title">O (Objective Data)</h3>
        <table class="table">
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
                <th>02 Sat</th>
                <td>{{ $requestDocument->vital_signs_o2_sat }}</td>
                <th>Weight</th>
                <td>{{ $requestDocument->vital_signs_weight }}</td>
            </tr>

            <tr>
                <th>Pertinent Exam</th>
                <td>{{ $requestDocument->pertinent_exam }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3 class="section-title">A (Assessment)</h3>
        <table class="table">
            <tr>
                <th>Assessment</th>
                <td>{{ $requestDocument->assessment }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3 class="section-title">P (Plan)</h3>
        <table class="table">
            <tr>
                <th>Plan</th>
                <td>{{ $requestDocument->plan }}</td>
            </tr>
        </table>
    </div>
</body>

</html>