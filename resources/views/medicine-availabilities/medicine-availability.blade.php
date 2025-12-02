<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th colspan="3" style="text-align: center; font-size: 20px; font-weight: bold;">{{ __('Medicine Availability Export Report') }} - {{ now()->format('m/d/Y') }}</th>
        </tr>
        <tr>
            <th style="text-align: center;">No.</th>
            <th style="text-align: center;">{{ __('messages.medicine_availability.availability_number') }}</th>
            <th style="text-align: center;">{{ __('messages.common.created_on') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($purchaseMedicines as $purchaseMedicine)
        <tr>
            <td style="text-align: center;">{{ $loop->iteration }}</td>
            <td style="text-align: center;">{{ '#' . $purchaseMedicine->availability_no }}</td>
            <td style="text-align: center;">{{ \Carbon\Carbon::parse($purchaseMedicine->created_at)->format('m/d/Y') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="3" class="text-center">{{ __('messages.common.no_data_available') }}</td>
        </tr>
        @endforelse

        <!-- Add 5 empty rows -->
        @if($purchaseMedicines->count() > 1)
        @for($i = 0; $i < 5; $i++)
            <tr>
            <td colspan="3" style="text-align: center;">&nbsp;</td>
            </tr>
            @endfor
            @endif
    </tbody>
</table>