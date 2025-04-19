<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th colspan="7" style="text-align: center; font-size: 20px; font-weight: bold;">{{ __('Medicine Export Report') }} - {{ now()->format('m/d/Y') }}</th>
        </tr>
        <tr>
            <th style="text-align: center;">No.</th>
            <th style="text-align: center;">{{ __('messages.purchase_medicine.purchase_number') }}</th>
            <th style="text-align: center;">{{ __('messages.purchase_medicine.total') }}</th>
            <th style="text-align: center;">{{ __('messages.purchase_medicine.tax') }}</th>
            <th style="text-align: center;">{{ __('messages.purchase_medicine.discount') }}</th>
            <th style="text-align: center;">{{ __('messages.purchase_medicine.net_amount') }}</th>
            <th style="text-align: center;">{{ __('messages.purchase_medicine.payment_mode') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($purchaseMedicines as $purchaseMedicine)
        <tr>
            <td style="text-align: center;">{{ $loop->iteration }}</td>
            <td style="text-align: center;">{{ '#' . $purchaseMedicine->purchase_no }}</td>
            <td style="text-align: center;">{{ number_format($purchaseMedicine->total, 2) }}</td>
            <td style="text-align: center;">{{ $purchaseMedicine->tax ? number_format($purchaseMedicine->tax, 2) : __('messages.common.n/a') }}</td>
            <td style="text-align: center;">{{ $purchaseMedicine->discount ? number_format($purchaseMedicine->discount, 2) : __('messages.common.n/a') }}</td>
            <td style="text-align: center;">{{ $purchaseMedicine->net_amount ? number_format($purchaseMedicine->net_amount, 2) : __('messages.common.n/a') }}</td>
            <td style="text-align: center;">{{ App\Models\PurchaseMedicine::PAYMENT_METHOD[$purchaseMedicine->payment_type] ?? __('messages.common.n/a') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center">{{ __('messages.common.no_data_available') }}</td>
        </tr>
        @endforelse

        <!-- Add 5 empty rows -->
        @if($purchaseMedicines->count() > 1)
        @for($i = 0; $i < 5; $i++)
            <tr>
            <td colspan="7" style="text-align: center;">&nbsp;</td>
            </tr>
            @endfor
            @endif
    </tbody>
    @if($purchaseMedicines->count() > 1)
    <tfoot>
        <tr>
            <td colspan="2" style="text-align: center; font-weight: bold;">{{ __('Total') }}</td>
            <td style="text-align: center; font-weight: bold;">{{ number_format($purchaseMedicines->sum('total'), 2) }}</td>
            <td style="text-align: center; font-weight: bold;">{{ number_format($purchaseMedicines->sum('tax'), 2) }}</td>
            <td style="text-align: center; font-weight: bold;">{{ number_format($purchaseMedicines->sum('discount'), 2) }}</td>
            <td style="text-align: center; font-weight: bold;">{{ number_format($purchaseMedicines->sum('net_amount'), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>