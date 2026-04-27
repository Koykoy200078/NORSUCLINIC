<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Medical Certificate - {{ $requestDocument->name }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet" />
    <style>
        @page {
            size: letter landscape;
            margin: 0.3in;
        }

        @media print {
            body {
                background-color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-container {
                width: 100% !important;
                min-width: 100% !important;
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            background-color: #f3f4f6;
            font-family: 'Arial', sans-serif;
        }

        .print-container {
            width: 10.5in;
            margin: 10px auto;
            background: white;
            padding: 0.3in;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .certificate-box {
            border: 2px solid #000;
            padding: 20px;
            position: relative;
        }

        .input-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            padding: 0 4px;
            font-weight: bold;
        }

        .underlined-area {
            border-bottom: 1px solid #000;
            min-height: 40px;
            margin-bottom: 10px;
            padding: 5px 0;
            width: 100%;
            white-space: pre-wrap;
            font-weight: bold;
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Top Action Bar (Hidden when printing) -->
    <div class="no-print bg-white border-b p-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center gap-3">
            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Print Mode</span>
            <h1 class="font-bold text-gray-800">Medical Certificate</h1>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-bold transition-all shadow-md flex items-center gap-2">
                <i class="fas fa-print"></i> Print Now (Ctrl+P)
            </button>
            <button onclick="window.close()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-bold transition-all">
                Close Tab
            </button>
        </div>
    </div>

    <div class="print-container">
        <div class="certificate-box">
            <!-- Header -->
            <table class="w-full mb-6" style="border-collapse: collapse; table-layout: auto;">
                <tr>
                    <td style="width: 100px; text-align: left; vertical-align: middle;">
                        <img src="{{ asset('assets/image/norsu_logo.png') }}" alt="Logo" style="width: 80px; height: 80px; object-fit: contain;">
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <h1 class="text-2xl font-bold uppercase tracking-wider leading-tight" style="margin: 0;">Negros Oriental State University</h1>
                        <h2 class="text-xs font-medium" style="margin: 2px 0;">University Medical Clinic, CNPAHS Bldg., Kagawasan Ave., Dumaguete City</h2>
                        <p class="text-[10px] text-gray-600" style="margin: 0;">Tel #: 225-9400, then Local # 188, 09263829484</p>
                    </td>
                    <td style="width: 100px; text-align: right; vertical-align: middle;">
                        <img src="{{ asset('assets/image/norsu_clinic_logo.png') }}" alt="Logo" style="width: 80px; height: 80px; object-fit: contain;">
                    </td>
                </tr>
            </table>

            <div class="text-center mb-8">
                <h3 class="text-3xl font-black tracking-widest border-t border-b-2 border-black py-2 inline-block px-10">MEDICAL CERTIFICATE</h3>
            </div>

            <div class="text-lg leading-relaxed mb-8">
                <p class="indent-16">
                    This is to certify that Mr./Ms.
                    <span class="input-line min-w-[300px] text-center">{{ $requestDocument->name }}</span>,
                    <span class="input-line min-w-[50px] text-center">{{ $requestDocument->age }}</span> yrs old,
                    <span class="input-line min-w-[80px] text-center">{{ $requestDocument->gender }}</span>
                    a resident of
                    <span class="input-line min-w-[400px] text-center">{{ $requestDocument->address }}</span>,
                    was seen and examined at my clinic on
                    <span class="input-line min-w-[150px] text-center">{{ $requestDocument->examined_on ? formatExaminedOnForPDF($requestDocument->examined_on) : '' }}</span>
                    with the following <span class="font-bold uppercase tracking-tight">complaints/diagnosis:</span>
                </p>
                <div class="underlined-area mt-2 px-4">{{ trim($requestDocument->complaints_diagnosis) }}</div>
            </div>

            <!-- Vital Signs -->
            <div class="mb-8">
                <table class="w-full" style="border-collapse: collapse; table-layout: fixed; font-size: 14px;">
                    <tr>
                        <td><label class="font-bold">BP:</label> <span class="border-b-2 border-black px-2 font-bold">{{ $requestDocument->vital_signs_bp }}</span></td>
                        <td><label class="font-bold">P:</label> <span class="border-b-2 border-black px-2 font-bold">{{ $requestDocument->vital_signs_pr }}</span></td>
                        <td><label class="font-bold">R:</label> <span class="border-b-2 border-black px-2 font-bold">{{ $requestDocument->vital_signs_rr }}</span></td>
                        <td><label class="font-bold">T:</label> <span class="border-b-2 border-black px-2 font-bold">{{ $requestDocument->vital_signs_temp }}</span></td>
                        <td><label class="font-bold">Ht:</label> <span class="border-b-2 border-black px-2 font-bold">{{ $requestDocument->vital_signs_height }}</span></td>
                        <td><label class="font-bold">Wt:</label> <span class="border-b-2 border-black px-2 font-bold">{{ $requestDocument->vital_signs_weight }}</span></td>
                    </tr>
                </table>
            </div>

            <div class="mb-8">
                <label class="font-bold text-lg block mb-2 uppercase tracking-tighter">Remark/s:</label>
                <div class="underlined-area px-4">{{ trim($requestDocument->medical_cert_remarks) }}</div>
            </div>

            <div class="text-sm mb-6 italic leading-snug text-gray-700">
                <span class="font-bold underline text-black uppercase">Note:</span>
                Please check the original copy of med cert before accepting the photocopied med cert. This medical certificate is <span class="font-bold underline text-black">not to be used</span> outside school purposes or medico-legal purposes.
            </div>

            <div class="mb-10 text-lg">
                This certificate is issued upon the request of
                <span class="input-line min-w-[250px] text-center">{{ $requestDocument->request_of }}</span>
                for your reference.
            </div>

            <!-- Footer / Signature -->
            <div class="flex justify-end mt-12">
                <div class="text-center min-w-[300px]">
                    <div class="font-bold text-xl uppercase text-blue-900">
                        DR. {{ $medicalCertificateDoctorName ?? 'UNIVERSITY PHYSICIAN' }}
                    </div>
                    <div class="border-t-2 border-black mt-1"></div>
                    <div class="text-xs font-bold uppercase tracking-widest mt-1 text-gray-500">UNIVERSITY PHYSICIAN</div>
                    <div class="text-sm mt-3 space-y-1 text-gray-600 text-left pl-10">
                        <p><span class="font-bold">Lic #:</span> {{ $requestDocument->doc_lic_no }}</p>
                        <p><span class="font-bold">PTR #:</span> {{ $requestDocument->doc_prt_no }}</p>
                    </div>
                </div>
            </div>

            <div class="absolute bottom-2 right-2 text-[8px] text-gray-300">
                NORSU-CLINIC-FORM-01
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="{{ asset('assets/front/vendor/font-awesome/css/all.min.css') }}">
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>

</html>
