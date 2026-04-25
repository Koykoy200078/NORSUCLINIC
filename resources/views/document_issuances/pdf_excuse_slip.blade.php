<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Student Excuse Slip PDF</title>
    <style>
        @page {
            size: 8.5in 11in portrait; /* Changing to portrait to fit the letter-like layout better if needed, but the image looks like it could be landscape too. The user said 'like a letter'. I'll stick to landscape 8.5x11 for now as it fits the image ratio better. */
            margin: 0.3in;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.3;
        }

        .header {
            width: 100%;
            margin-bottom: 5px;
        }

        .header td {
            vertical-align: middle;
        }

        .logo {
            height: 60px;
            width: auto;
        }

        .university-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }

        .clinic-info {
            font-size: 10px;
            text-align: center;
        }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
            text-decoration: underline;
        }

        .form-row {
            margin-bottom: 10px;
            width: 100%;
        }

        .input-label {
            font-weight: bold;
            display: inline-block;
        }

        .input-underline {
            border-bottom: 1px solid #000;
            display: inline-block;
            padding-left: 5px;
            min-height: 14px;
        }

        .checkbox-container {
            margin-top: 10px;
        }

        .checkbox-item {
            display: inline-block;
            width: 30%;
            margin-bottom: 5px;
        }

        .box {
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            display: inline-block;
            margin-right: 5px;
            vertical-align: middle;
            text-align: center;
            line-height: 10px;
        }

        .subject-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .subject-table th, .subject-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }

        .subject-table th {
            font-size: 10px;
            text-transform: uppercase;
        }

        .note-box {
            border: 2px solid #000;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            margin: 20px 0;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        .signature-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }

        .signature-table td {
            text-align: center;
            vertical-align: bottom;
            padding-bottom: 10px;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
            margin-top: 5px;
            font-weight: bold;
            font-size: 10px;
        }

        .footer-remarks {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .remarks-line {
            border-bottom: 1px dotted #000;
            height: 20px;
            width: 100%;
        }

        .text-xs {
            font-size: 8px;
        }
    </style>
</head>

<body>
    <table class="header">
        <tr>
            <td width="15%" style="text-align: left;">
                <img src="{{ $norsuLogoBase64 }}" class="logo" />
            </td>
            <td width="70%">
                <div class="university-name">Negros Oriental State University</div>
                <div class="clinic-info">University Medical Clinic, CNPAHS Bldg., Kagawasan Ave., Dumaguete City</div>
                <div class="clinic-info">Tel #: 225-9400, then Local # 188, 09263829484</div>
            </td>
            <td width="15%" style="text-align: right;">
                <img src="{{ $clinicLogoBase64 }}" class="logo" />
            </td>
        </tr>
    </table>

    <div class="title">STUDENT EXCUSE SLIP</div>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 20px;">
        <tr>
            <td width="58%" style="vertical-align: top; padding-right: 30px;">
                <div class="form-row">
                    <span class="input-label">DATE FILED:</span>
                    <span class="input-underline" style="width: 150px;">{{ $requestDocument->requested_at ? $requestDocument->requested_at->format('M d, Y') : date('M d, Y') }}</span>
                </div>

                <div class="form-row">
                    <span class="input-label">STUDENT'S NAME:</span>
                    <span class="input-underline" style="width: 300px;">{{ strtoupper($requestDocument->name) }}</span>
                </div>

                <div class="form-row">
                    <span class="input-label">SECTION:</span>
                    <span class="input-underline" style="width: 300px;">{{ $requestDocument->course }} {{ $requestDocument->year_level }}</span>
                </div>

                <div class="form-row">
                    <span class="input-label">DATE/S ABSENT:</span>
                    <span class="input-underline" style="width: 250px;">{{ $requestDocument->examined_on ? formatExaminedOnForPDF($requestDocument->examined_on) : '' }}</span>
                </div>

                <div class="checkbox-container" style="margin-top: 15px;">
                    <div class="input-label" style="display: block; margin-bottom: 5px;">REASON (COMPLAINTS/DIAGNOSIS):</div>
                    <div style="border: 1px solid #000; padding: 10px; min-height: 80px; font-size: 11px;">
                        {{ $requestDocument->complaints_diagnosis }}
                    </div>
                </div>

                <div class="text-xs" style="margin-top: 15px; font-style: italic; color: #555;">
                    Note: Form must be accompanied with necessary certificates (Medical/Death/Activity) where applicable.
                </div>
            </td>
            <td width="42%" style="vertical-align: top;">
                <table class="subject-table">
                    <thead>
                        <tr style="background-color: #f9f9f9;">
                            <th width="50%" style="text-align: center;">SUBJECT</th>
                            <th width="50%" style="text-align: center;">TEACHER</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $subjects = isset($requestDocument) && isset($requestDocument->subjects) ? (is_array($requestDocument->subjects) ? $requestDocument->subjects : json_decode($requestDocument->subjects, true)) : array_fill(0, 10, ['subject' => '', 'teacher' => '']);
                        @endphp
                        @for($i=0; $i<10; $i++)
                        <tr>
                            <td style="height: 20px;">{{ $subjects[$i]['subject'] ?? '' }}</td>
                            <td>{{ $subjects[$i]['teacher'] ?? '' }}</td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
                <div style="font-size: 8px; font-style: italic; text-align: center; margin-top: 5px; color: #666;">To be filled by Subject Teachers upon return</div>
            </td>
        </tr>
    </table>

    <div class="form-row" style="margin-top: 20px;">
        <span class="input-label">PARENT'S OR GUARDIAN'S SIGNATURE:</span>
        <span class="input-underline" style="width: 300px;"></span>
    </div>
    <div class="form-row">
        <span class="input-label" style="margin-left: 20px;">ID PRESENTED:</span>
        <span class="input-underline" style="width: 200px;"></span>
        <span class="input-label" style="margin-left: 20px;">ID NO:</span>
        <span class="input-underline" style="width: 150px;"></span>
    </div>

    <div class="note-box">
        Note: This form must be submitted together with a photocopy of the parent's valid ID.
    </div>

    <table class="signature-table">
        <tr>
            <td width="50%">
                <div class="signature-line">&nbsp;</div>
                <div style="font-weight: bold; font-size: 10px;">PROGRAM CHAIR</div>
            </td>
            <td width="50%">
                <div class="signature-line">{{ $medicalCertificateDoctorName ? 'DR. ' . strtoupper($medicalCertificateDoctorName) : 'UNIVERSITY PHYSICIAN' }}</div>
                <div style="font-weight: bold; font-size: 10px;">UNIVERSITY PHYSICIAN</div>
                <div style="font-size: 8px; color: #666; margin-top: 2px;">
                    Lic #: {{ $requestDocument->doc_lic_no }} | PTR #: {{ $requestDocument->doc_prt_no }}
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-remarks">
        <div class="input-label">CLINIC REMARKS:</div>
        <div class="underlined-area" style="min-height: 40px; border-bottom: none;">{{ $requestDocument->medical_cert_remarks }}</div>
        <div class="remarks-line"></div>
        <div class="remarks-line"></div>
        <div class="remarks-line"></div>
    </div>

    <div style="position: absolute; bottom: 0; right: 0; font-size: 8px;">
        NORSU-CLINIC-FORM-02
    </div>
</body>

</html>
