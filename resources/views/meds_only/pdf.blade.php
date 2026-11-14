<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Meds Only record {{ $record->history_number }}</title>
    <style>
        @page { margin: 26px 26px 30px 26px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #000; }
        .head { text-align: center; margin-bottom: 10px; }
        .head .university { font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .head .address, .head .phone { font-size: 8px; }
        .head .title { font-size: 13px; font-weight: bold; letter-spacing: 1px; margin-top: 8px; }
        table { width: 100%; border-collapse: collapse; }
        .details td { padding: 2px 3px; vertical-align: top; }
        .details td.label { width: 32%; font-weight: bold; }
        .drugs { margin-top: 10px; }
        .drugs th, .drugs td { border: 0.6px solid #000; padding: 3px; }
        .drugs th { background: #e9e9e9; text-align: left; }
        .drugs td.num { text-align: center; }
        .sign { margin-top: 26px; width: 55%; margin-left: auto; text-align: center; }
        .sign .name { border-top: 0.6px solid #000; padding-top: 2px; font-weight: bold; }
        .foot { margin-top: 14px; font-size: 7px; color: #444; }
    </style>
</head>
<body>
    {{-- The same letterhead words as the clinic's other documents (App\Support\Letterhead), as text: no image to fetch. --}}
    <div class="head">
        <div class="university">{{ \App\Support\Letterhead::UNIVERSITY }}</div>
        <div class="address">{{ \App\Support\Letterhead::ADDRESS }}</div>
        <div class="phone">{{ \App\Support\Letterhead::PHONE }}</div>
        <div class="title">MEDS ONLY RECORD</div>
    </div>

    <table class="details">
        <tr><td class="label">Date</td><td>{{ \Carbon\Carbon::parse($record->bill_date)->format('F j, Y h:i A') }}</td></tr>
        <tr><td class="label">Number</td><td>{{ $record->history_number }}</td></tr>
        <tr><td class="label">Name</td><td>{{ $record->patient_name }}@unless($record->patient_id) (not registered)@endunless</td></tr>
        <tr><td class="label">Patient type</td><td>{{ $record->patientType?->name ?? '—' }}</td></tr>
        <tr><td class="label">Campus</td><td>{{ $record->campus ?: '—' }}</td></tr>
        <tr><td class="label">College</td><td>{{ $record->college ?: '—' }}</td></tr>
        <tr><td class="label">Course</td><td>{{ $record->course ?: '—' }}</td></tr>
        <tr><td class="label">Year</td><td>{{ $record->year_level ?: '—' }}</td></tr>
        <tr><td class="label">Note</td><td>{{ $record->note ?: '—' }}</td></tr>
    </table>

    <table class="drugs">
        <thead><tr><th>Medicine</th><th>Strength</th><th style="text-align:center">Quantity</th></tr></thead>
        <tbody>
            @forelse($record->dispenseItems->groupBy(fn ($item) => $item->medicine_id.'|'.mb_strtolower(trim((string) $item->dosage))) as $items)
                <tr>
                    <td>{{ $items->first()->medicine?->name ?? 'Deleted medicine' }}</td>
                    <td>{{ trim((string) $items->first()->dosage) ?: '—' }}</td>
                    <td class="num">{{ (int) $items->sum('quantity') }}</td>
                </tr>
            @empty
                <tr><td colspan="3">No drug lines.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="sign">
        <div class="name">{{ $record->recorder?->full_name ?? 'N/A' }}</div>
        <div>Given by</div>
    </div>

    <div class="foot">A Meds Only record: a person who came for medicine only (no consultation). Printed {{ $generatedAt->format('M d, Y h:i A') }}.</div>
</body>
</html>
