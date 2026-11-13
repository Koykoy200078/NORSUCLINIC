@extends('document_issuances.print_layout', ['heading' => 'Medical Certificate', 'orientation' => 'landscape', 'width' => '10.5in', 'badgeClasses' => 'bg-green-100 text-green-700'])
@section('print_styles')
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
@endsection
@section('print_content')
        <div class="certificate-box">
            <!-- Header -->
            @include('document_issuances.letterhead', ['margin' => 'mb-6'])

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
                <div class="underlined-area mt-2 px-4">{{ trim((string) $requestDocument->complaints_diagnosis) }}</div>
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
                <div class="underlined-area px-4">{{ trim((string) $requestDocument->medical_cert_remarks) }}</div>
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
@endsection
