@php
$dispensedAt = $row->bill_date ?? $row->created_at;
@endphp

@if ($dispensedAt === null)
N/A
@else
<div class="badge bg-light-primary">
    <div class="mb-2">{{ \Carbon\Carbon::parse($dispensedAt)->format('h:i A')}}
        <div class="mt-2">
            {{ \Carbon\Carbon::parse($dispensedAt)->translatedFormat('jS M, Y')}}
        </div>
    </div>
    @endif