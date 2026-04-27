<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Student Excuse Slip - {{ $requestDocument->name }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet" />
    <style>
        @page {
            size: letter portrait;
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
            width: 8.5in;
            margin: 10px auto;
            background: white;
            padding: 0.3in;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .view-textarea {
            resize: none;
            overflow: hidden;
            min-height: 80px;
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Top Action Bar (Hidden when printing) -->
    <div class="no-print bg-white border-b p-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center gap-3">
            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Print Mode</span>
            <h1 class="font-bold text-gray-800">Student Excuse Slip</h1>
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

        <div class="text-center mb-6 border-t border-b border-black py-4">
            <h3 class="text-3xl font-black tracking-widest" style="margin: 0;">STUDENT EXCUSE SLIP</h3>
        </div>

        <table class="w-full mb-6" style="border-collapse: collapse; table-layout: auto;">
            <tr>
                <!-- Left Side: Student Information -->
                <td style="width: 65%; vertical-align: top; padding-right: 40px;">
                    <div class="space-y-8">
                        <div class="flex items-baseline border-b-2 border-black pb-1 gap-10">
                            <label class="font-bold text-[13px] whitespace-nowrap uppercase tracking-tighter">DATE FILED:</label>
                            <span class="text-base font-bold">{{ $requestDocument->created_at->format('M d, Y') }}</span>
                        </div>

                        <div class="flex items-baseline border-b-2 border-black pb-1 px-1 gap-10">
                            <label class="font-bold text-[13px] whitespace-nowrap uppercase tracking-tighter">STUDENT'S NAME:</label>
                            <span class="flex-1 text-base font-bold uppercase">{{ $requestDocument->name }}</span>
                        </div>

                        <div class="flex items-baseline border-b-2 border-black pb-1 px-1 gap-10">
                            <label class="font-bold text-[13px] whitespace-nowrap uppercase tracking-tighter">SECTION:</label>
                            <span class="flex-1 text-base font-bold">{{ $requestDocument->course }} {{ $requestDocument->year_level }}</span>
                        </div>

                        <div class="flex items-baseline border-b-2 border-black pb-1 px-1 gap-10">
                            <label class="font-bold text-[13px] whitespace-nowrap uppercase tracking-tighter">DATE/S ABSENT:</label>
                            <span class="flex-1 text-base font-bold">{{ $requestDocument->examined_on ? formatExaminedOnForPDF($requestDocument->examined_on) : '' }}</span>
                        </div>

                        <div class="pt-4">
                            <label class="font-bold text-[13px] block mb-3 uppercase tracking-tighter">REASON (COMPLAINTS/DIAGNOSIS):</label>
                            <div class="w-full border-2 border-black p-4 text-base leading-relaxed min-h-[100px] whitespace-pre-wrap">{{ $requestDocument->complaints_diagnosis }}</div>
                        </div>
                    </div>
                </td>

                <!-- Right Side: Subject Table -->
                <td style="width: 35%; vertical-align: top;">
                    <table class="w-full border-collapse border-2 border-black" style="font-size: 10px;">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-black p-2 text-center font-bold" style="width: 50%;">SUBJECT</th>
                                <th class="border border-black p-2 text-center font-bold" style="width: 50%;">TEACHER</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $subjects = isset($requestDocument) && isset($requestDocument->subjects) ? (is_array($requestDocument->subjects) ? $requestDocument->subjects : json_decode($requestDocument->subjects, true)) : array_fill(0, 10, ['subject' => '', 'teacher' => '']);
                            @endphp
                            @for($i=0; $i<10; $i++)
                                <tr>
                                <td class="border border-black h-9 px-2 font-medium text-[11px] align-middle">
                                    {{ $subjects[$i]['subject'] ?? '' }}
                                </td>
                                <td class="border border-black h-9 px-2 font-medium text-[11px] align-middle">
                                    {{ $subjects[$i]['teacher'] ?? '' }}
                                </td>
            </tr>
            @endfor
            </tbody>
        </table>
        <p class="text-[9px] italic mt-2 text-gray-500 text-center uppercase tracking-widest">To be filled by Subject Teachers upon return</p>
        </td>
        </tr>
        </table>

        <!-- Parent Signature Area -->
        <div class="mt-8 bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
            <div class="flex items-baseline border-b-2 border-black/10 pb-2">
                <label class="font-bold text-[11px] mr-2 whitespace-nowrap text-gray-500 uppercase tracking-tighter">PARENT'S OR GUARDIAN'S SIGNATURE:</label>
                <div class="flex-1"></div>
            </div>
        </div>

        <!-- Remarks -->
        <div class="mt-6 border-t border-black pt-4">
            <label class="font-bold text-sm block mb-2">CLINIC REMARKS / RECOMMENDATION:</label>
            <div class="w-full p-0 text-sm leading-relaxed whitespace-pre-wrap min-h-[50px]">{{ $requestDocument->medical_cert_remarks }}</div>
        </div>

        <!-- Vital Signs -->
        <div class="mt-8 pt-4 border-t border-gray-200">
            <table class="w-full" style="border-collapse: collapse; table-layout: fixed; font-size: 11px;">
                <tr>
                    <td style="width: 16%;"><label class="font-bold">BP:</label> <span class="border-b border-black px-1">{{ $requestDocument->vital_signs_bp }}</span></td>
                    <td style="width: 16%;"><label class="font-bold">P:</label> <span class="border-b border-black px-1">{{ $requestDocument->vital_signs_pr }}</span></td>
                    <td style="width: 16%;"><label class="font-bold">R:</label> <span class="border-b border-black px-1">{{ $requestDocument->vital_signs_rr }}</span></td>
                    <td style="width: 16%;"><label class="font-bold">T:</label> <span class="border-b border-black px-1">{{ $requestDocument->vital_signs_temp }}</span></td>
                    <td style="width: 16%;"><label class="font-bold">Ht:</label> <span class="border-b border-black px-1">{{ $requestDocument->vital_signs_height }}</span></td>
                    <td style="width: 16%;"><label class="font-bold">Wt:</label> <span class="border-b border-black px-1">{{ $requestDocument->vital_signs_weight }}</span></td>
                </tr>
            </table>
        </div>

        <!-- Approvals Section -->
        <br>
        <table class="w-full mt-6" style="border-collapse: collapse; table-layout: fixed;">
            <tr>
                <td class="text-center" style="width: 50%; vertical-align: top;">
                    <div style="padding-top: 12px; margin: 0 40px;">
                        <br>
                        <div class="font-bold text-sm tracking-tighter text-gray-200">___________________________________</div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase mt-1 tracking-widest">PROGRAM CHAIR</div>
                    </div>
                </td>
                <td class="text-center" style="width: 50%; vertical-align: top;">
                    <div style="padding-top: 12px; margin: 0 40px;">
                        <div class="font-bold text-sm tracking-tighter text-blue-900 uppercase">
                            DR. {{ $medicalCertificateDoctorName ?? 'UNIVERSITY PHYSICIAN' }}
                        </div>
                        <div class="font-bold text-sm tracking-tighter text-gray-200">___________________________________</div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase mt-1 tracking-widest">UNIVERSITY PHYSICIAN</div>
                        <div class="text-[9px] mt-2 space-y-1 text-gray-500 text-left pl-10">
                            <p>Lic #: {{ $requestDocument->doc_lic_no }}</p>
                            <p>PTR #: {{ $requestDocument->doc_prt_no }}</p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="text-right mt-6 text-[8px] text-gray-400">
            NORSU-CLINIC-FORM-02
        </div>
    </div>

    <link rel="stylesheet" href="{{ asset('assets/front/vendor/font-awesome/css/all.min.css') }}">
    <script>
        window.onload = function() {
            // Give images and styles a brief moment to stabilize
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>

</html>