<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Medical Certificate PDF</title>
    <style>
        @page {
            size: 8.5in 5.5in landscape;
            margin: 0;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
        }

        .certificate-box {
            max-width: 612px;
            width: 100%;
            margin: 16px auto;
            border: 1px solid #ccc;
            padding: 16px 20px 20px 20px;
            background: #fff;
            box-sizing: border-box;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            font-size: 12px;
        }

        .header {
            text-align: center;
        }

        .header h2,
        .header h3 {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
        }

        .header h3 {
            font-size: 12px;
        }

        .logo {
            height: 80px;
            width: auto;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .section {
            margin-bottom: 14px;
            font-size: 10px;
        }

        .section p {
            font-size: 10px !important;
        }

        .input-line {
            border-bottom: 1px solid #000;
            min-width: 60px;
            text-align: center;
            font-size: 10px;
        }

        .label {
            font-weight: bold;
            font-size: 10px;
        }

        .row {
            margin-bottom: 8px;
            font-size: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
            padding-right: 8px;
        }

        .mb-2 {
            margin-bottom: 6px;
        }

        .mb-4 {
            margin-bottom: 12px;
        }

        .mt-4 {
            margin-top: 12px;
        }

        .font-bold {
            font-weight: bold;
        }

        .underline {
            text-decoration: underline;
        }

        .vital-signs {
            margin-bottom: 10px;
            white-space: nowrap;
            font-size: 10px;
        }

        .vital-signs div {
            display: inline-block;
            margin-right: 24px;
            font-size: 10px;
            text-align: center;
        }

        .underlined-area {
            border-bottom: 1px solid #000;
            min-height: 32px;
            margin-bottom: 6px;
            padding: 3px 0 2px 0;
            width: 100%;
            box-sizing: border-box;
            white-space: pre-line;
            font-size: 10px;
        }

        @media (max-width: 800px) {
            .certificate-box {
                max-width: 98vw;
                padding: 8px 2vw;
            }

            .logo {
                width: 40px;
                height: 40px;
            }

            .vital-signs {
                flex-direction: column;
                /* Stack vertically on small screens */
            }
        }
    </style>
</head>

<body>
    <div class="certificate-box">
        <div class="header mb-4">
            <table style="margin-left: auto; margin-right: auto">
                <tr>
                    <td width="20%" class="text-center">
                        <img src="{{ public_path('assets/image/norsu_logo.png') }}" class="logo" />
                    </td>
                    <td width="60%" class="text-center">
                        <h2 class="font-bold">Negros Oriental State University</h2>
                        <div style="font-size: 10px">University Medical Clinic, CNPAHS Bldg., Kagawasan Ave., Dumaguete City</div>
                        <div style="font-size: 10px">Tel #: 225-9400, then Local # 188, 09263829484</div>
                    </td>
                    <td width="20%" class="text-center">
                        <img src="{{ public_path('assets/image/norsu_clinic_logo.png') }}" class="logo" />
                    </td>
                </tr>
            </table>
            <h3 class="font-bold mb-4" style="margin-top: 10px">MEDICAL CERTIFICATE</h3>
        </div>

        <div class="section">
            <p>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is to certify that Mr./Ms.
                <span class="input-line">{{ $requestDocument->name }}</span>, <span class="input-line" style="min-width: 32px">{{ $requestDocument->age }}</span> yrs old, <span class="input-line" style="min-width: 32px">{{ $requestDocument->gender }}</span> a resident of
                <span class="input-line" style="min-width: 120px">{{ $requestDocument->address }}</span>, was seen and examined at my clinic on
                <span class="input-line" style="min-width: 60px"> {{ $requestDocument->examined_on ? \Carbon\Carbon::parse($requestDocument->examined_on)->format('Y-m-d') : '' }} </span>
                with the following <span class="font-bold">complaints/diagnosis:</span>
            </p>
            <div class="underlined-area">{{ trim($requestDocument->complaints_diagnosis) }}</div>
        </div>

        <div class="vital-signs mb-2" style="display: flex; flex-direction: row; flex-wrap: nowrap; gap: 6px;">
            <div><span class="font-bold">BP:</span> <span class="input-line" style="min-width: 32px">{{ $requestDocument->vital_signs_bp }}</span></div>
            <div><span class="font-bold">P:</span> <span class="input-line" style="min-width: 32px">{{ $requestDocument->vital_signs_pr }}</span></div>
            <div><span class="font-bold">R:</span> <span class="input-line" style="min-width: 32px">{{ $requestDocument->vital_signs_rr }}</span></div>
            <div><span class="font-bold">T:</span> <span class="input-line" style="min-width: 32px">{{ $requestDocument->vital_signs_temp }}</span></div>
            <div><span class="font-bold">Ht:</span> <span class="input-line" style="min-width: 32px">{{ $requestDocument->vital_signs_height }}</span></div>
            <div><span class="font-bold">Wt:</span> <span class="input-line" style="min-width: 32px">{{ $requestDocument->vital_signs_weight }}</span></div>
        </div>

        <div class="section">
            <p class="font-bold mb-2">Remark/s:</p>
            <div class="underlined-area">{{ trim($requestDocument->medical_cert_remarks) }}</div>
        </div>

        <div class="section mb-2">
            <span class="label">Note:</span>
            Please check the original copy of med cert before accepting the photocopied med cert. This medical certificate is <span class="font-bold underline"> not to be used </span> outside school purposes or medico-legal purposes.
        </div>
        <div class="section mb-4">
            This certificate is issued upon the request of
            <span class="input-line" style="min-width: 100px"> {{ $requestDocument->request_of }} </span>
            for your reference.
        </div>

        <div class="text-right mt-4">
            <p class="font-bold" style="margin-bottom: 0; font-size: 10px;">Dr. Michael S. Oliveros</p>
            <p style="margin: 0; font-size: 10px;">Lic #: <span class="input-line" style="min-width: 60px; font-size: 10px;">{{ $requestDocument->doc_lic_no }}</span></p>
            <p style="margin: 0; font-size: 10px;">PTR #: <span class="input-line" style="min-width: 60px; font-size: 10px;">{{ $requestDocument->doc_prt_no }}</span></p>
        </div>
    </div>
</body>

</html>
