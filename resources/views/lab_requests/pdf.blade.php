<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Request Form #{{ $labRequest->request_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            padding: 20px 28px;
        }

        /* ── Clinic Header ── */
        .clinic-header {
            display: table;
            width: 100%;
            border-bottom: 3px solid #1a56a3;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .clinic-logo-cell {
            display: table-cell;
            width: 72px;
            vertical-align: middle;
            padding-right: 12px;
        }
        .clinic-logo-cell img { width: 64px; height: 64px; object-fit: contain; }
        .clinic-info-cell {
            display: table-cell;
            vertical-align: middle;
        }
        .clinic-name {
            font-size: 16px;
            font-weight: bold;
            color: #1a56a3;
            letter-spacing: .3px;
        }
        .clinic-sub { font-size: 10px; color: #555; margin-top: 2px; }

        .doc-title-cell {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
        }
        .doc-title {
            font-size: 15px;
            font-weight: bold;
            color: #1a56a3;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .request-no {
            font-size: 12px;
            color: #444;
            margin-top: 4px;
        }

        /* ── Status Badge ── */
        .status-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-top: 4px;
        }
        .status-pending    { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .status-collected  { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .status-processing { background: #e0e7ff; color: #3730a3; border: 1px solid #a5b4fc; }
        .status-completed  { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .status-cancelled  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .status-referred   { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
        .status-rejected   { background: #1f2937; color: #f9fafb; border: 1px solid #374151; }

        /* ── Section Headers ── */
        .section-title {
            background: #1a56a3;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .8px;
            padding: 4px 8px;
            margin: 12px 0 6px;
        }

        /* ── Info Grid ── */
        .info-grid { width: 100%; border-collapse: collapse; }
        .info-grid td { padding: 3px 6px; vertical-align: top; }
        .info-label { color: #666; font-size: 9.5px; text-transform: uppercase; letter-spacing: .3px; white-space: nowrap; width: 130px; }
        .info-value { border-bottom: 1px solid #d1d5db; font-size: 11px; }

        /* ── Tests Table ── */
        .tests-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .tests-table th {
            background: #1a56a3;
            color: #fff;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: .3px;
            padding: 5px 6px;
            border: 1px solid #1a56a3;
            text-align: left;
        }
        .tests-table td { border: 1px solid #d1d5db; padding: 5px 6px; font-size: 10.5px; vertical-align: top; }
        .tests-table tr:nth-child(even) td { background: #f8f9fa; }
        .result-pending  { color: #888; }
        .result-normal   { color: #065f46; font-weight: bold; }
        .result-abnormal { color: #92400e; font-weight: bold; }
        .result-critical { color: #991b1b; font-weight: bold; }

        /* ── Signature Area ── */
        .signature-row { display: table; width: 100%; margin-top: 36px; }
        .sig-cell {
            display: table-cell;
            width: 48%;
            text-align: center;
            padding: 0 12px;
        }
        .sig-line { border-top: 1px solid #333; padding-top: 4px; font-size: 10px; }
        .sig-label { color: #555; font-size: 9px; }

        /* ── Footer ── */
        .footer {
            margin-top: 18px;
            border-top: 1px solid #d1d5db;
            padding-top: 6px;
            font-size: 8.5px;
            color: #888;
            text-align: center;
        }

        @page { margin: 18mm 14mm; }
    </style>
</head>
<body>

    {{-- ================================================================== --}}
    {{-- CLINIC LETTERHEAD --}}
    {{-- ================================================================== --}}
    <div class="clinic-header">
        <div class="clinic-logo-cell">
            @php $logoPath = getSettingValue('logo'); @endphp
            @if($logoPath)
            <img src="{{ public_path(ltrim($logoPath, '/')) }}" alt="Clinic Logo">
            @else
            <img src="{{ public_path('assets/image/norsu_logo.png') }}" alt="Clinic Logo">
            @endif
        </div>

        <div class="clinic-info-cell">
            <div class="clinic-name">{{ getSettingValue('clinic_name') ?? getAppName() }}</div>
            @if(getSettingValue('clinic_address'))
            <div class="clinic-sub">{{ getSettingValue('clinic_address') }}</div>
            @endif
            @if(getSettingValue('clinic_phone'))
            <div class="clinic-sub">Tel: {{ getSettingValue('clinic_phone') }}</div>
            @endif
        </div>

        <div class="doc-title-cell">
            <div class="doc-title">Laboratory<br>Request Form</div>
            <div class="request-no">Request No. <strong>{{ $labRequest->request_number }}</strong></div>
            <div>
                <span class="status-badge status-{{ $labRequest->status }}">
                    {{ strtoupper($labRequest->status) }}
                </span>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- PATIENT INFORMATION --}}
    {{-- ================================================================== --}}
    <div class="section-title">Patient Information</div>
    <table class="info-grid">
        <tr>
            <td class="info-label">Patient Name</td>
            <td class="info-value" style="width:40%;">{{ $labRequest->patient_name }}</td>
            <td class="info-label">Date of Request</td>
            <td class="info-value">{{ $labRequest->requested_at?->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Age</td>
            <td class="info-value">{{ $labRequest->patient_age ?? '&nbsp;' }}</td>
            <td class="info-label">Gender</td>
            <td class="info-value">{{ $labRequest->patient_gender ?? '&nbsp;' }}</td>
        </tr>
        <tr>
            <td class="info-label">Contact No.</td>
            <td class="info-value">{{ $labRequest->patient_contact ?? '&nbsp;' }}</td>
            <td class="info-label">Affiliation</td>
            <td class="info-value">{{ ucfirst($labRequest->status_affiliation ?? '&nbsp;') }}</td>
        </tr>
        <tr>
            <td class="info-label">College</td>
            <td class="info-value">{{ $labRequest->college ?? '&nbsp;' }}</td>
            <td class="info-label">Course / Program</td>
            <td class="info-value">{{ $labRequest->course ?? '&nbsp;' }}</td>
        </tr>
        <tr>
            <td class="info-label">Year Level</td>
            <td class="info-value">{{ $labRequest->year_level ?? '&nbsp;' }}</td>
            <td class="info-label">Campus</td>
            <td class="info-value">{{ $labRequest->campus ?? '&nbsp;' }}</td>
        </tr>
        <tr>
            <td class="info-label">Address</td>
            <td colspan="3" class="info-value">{{ $labRequest->address ?? '&nbsp;' }}</td>
        </tr>
    </table>

    {{-- ================================================================== --}}
    {{-- REQUEST DETAILS --}}
    {{-- ================================================================== --}}
    <div class="section-title">Request Details</div>
    <table class="info-grid">
        <tr>
            <td class="info-label">Clinical Indication</td>
            <td colspan="3" class="info-value">{{ $labRequest->clinical_indication ?? '&nbsp;' }}</td>
        </tr>
        <tr>
            <td class="info-label">Requesting Physician</td>
            <td class="info-value">{{ $labRequest->requesting_physician ?? '&nbsp;' }}</td>
            <td class="info-label">License No.</td>
            <td class="info-value">{{ $labRequest->physician_license_no ?? '&nbsp;' }}</td>
        </tr>
        @if($labRequest->remarks)
        <tr>
            <td class="info-label">Remarks</td>
            <td colspan="3" class="info-value">{{ $labRequest->remarks }}</td>
        </tr>
        @endif
    </table>

    {{-- ================================================================== --}}
    {{-- TESTS REQUESTED --}}
    {{-- ================================================================== --}}
    <div class="section-title">Tests Requested ({{ $labRequest->items->count() }})</div>
    <table class="tests-table">
        <thead>
            <tr>
                <th style="width:4%;">#</th>
                <th style="width:30%;">Test Name</th>
                <th style="width:14%;">Category</th>
                <th style="width:22%;">Result</th>
                <th style="width:10%;">Unit</th>
                <th style="width:14%;">Normal Range</th>
                <th style="width:10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($labRequest->items as $i => $item)
            <tr>
                <td style="text-align:center; color:#888;">{{ $i + 1 }}</td>
                <td><strong>{{ $item->test_name }}</strong></td>
                <td>{{ $item->test_category ?? '—' }}</td>
                <td class="result-{{ $item->result_status }}">
                    {{ $item->result_value ?? '_______________' }}
                    @if($item->notes)<br><span style="font-size:9px; color:#888;">{{ $item->notes }}</span>@endif
                </td>
                <td>{{ $item->unit ?? '—' }}</td>
                <td style="font-size:9px; color:#666;">{{ $item->normal_range ?? '—' }}</td>
                <td class="result-{{ $item->result_status }}">{{ ucfirst($item->result_status) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#888; padding:10px;">No tests listed.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ================================================================== --}}
    {{-- SIGNATURES --}}
    {{-- ================================================================== --}}
    <div class="signature-row">
        <div class="sig-cell">
            <div style="height:40px;">&nbsp;</div>
            <div class="sig-line">
                <strong>{{ $labRequest->requesting_physician ?? '___________________________' }}</strong>
            </div>
            <div class="sig-label">Requesting Physician / Authorized Personnel</div>
            @if($labRequest->physician_license_no)
            <div class="sig-label">Lic. No. {{ $labRequest->physician_license_no }}</div>
            @endif
        </div>
        <div class="sig-cell">
            <div style="height:40px;">&nbsp;</div>
            <div class="sig-line">___________________________</div>
            <div class="sig-label">Laboratory Receiving Personnel</div>
            <div class="sig-label">Date: ________________</div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- FOOTER --}}
    {{-- ================================================================== --}}
    <div class="footer">
        <strong>{{ getSettingValue('clinic_name') ?? getAppName() }}</strong>
        &nbsp;|&nbsp; Lab Request Form
        &nbsp;|&nbsp; Request No.: {{ $labRequest->request_number }}
        &nbsp;|&nbsp; Date Printed: {{ now()->format('F d, Y') }}
        @if($labRequest->status === 'completed')
        &nbsp;|&nbsp; ✓ Completed: {{ $labRequest->completed_at?->format('M d, Y h:i A') }}
        @endif
    </div>

</body>
</html>
