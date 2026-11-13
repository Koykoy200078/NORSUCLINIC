{{-- The letterhead of the excuse slip and medical certificate pages and prints (re-audit 2026-11-13, DRY: four copies).
     $margin: the space below it ('mb-6' on the prints, 'mb-10' on the pages). --}}
<table class="w-full {{ $margin }}" style="border-collapse: collapse; table-layout: auto;">
    <tr>
        <td style="width: 100px; text-align: left; vertical-align: middle;">
            <img src="{{ asset('assets/image/norsu_logo.png') }}" alt="Logo" style="width: 80px; height: 80px; object-fit: contain;">
        </td>
        <td style="text-align: center; vertical-align: middle;">
            <h1 class="text-2xl font-bold uppercase tracking-wider leading-tight" style="margin: 0;">{{ \App\Support\Letterhead::UNIVERSITY }}</h1>
            <h2 class="text-xs font-medium" style="margin: 2px 0;">{{ \App\Support\Letterhead::ADDRESS }}</h2>
            <p class="text-[10px] text-gray-600" style="margin: 0;">{{ \App\Support\Letterhead::PHONE }}</p>
        </td>
        <td style="width: 100px; text-align: right; vertical-align: middle;">
            <img src="{{ asset('assets/image/norsu_clinic_logo.png') }}" alt="Logo" style="width: 80px; height: 80px; object-fit: contain;">
        </td>
    </tr>
</table>
