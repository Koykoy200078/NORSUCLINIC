{{-- How to read the YEARLY MEDICINE INVENTORY; the same words under the screen and the PDF. --}}
<div class="inv-note">
    Quantities are in each medicine's own unit. "Left from last year" is the stock on January 1; "Additional" is the stock
    received during {{ $report['year'] }} (stock-in); each quarter shows what remained at its end, and the last quarter's
    figure is the year-end remaining, which is next year's "Left from last year".
    @if(collect($report['quarters'])->contains('to_date', true))
        The quarter still going on shows the stock of today; a quarter that has not started is blank.
    @endif
    Stock past its expiry date is still counted until it is disposed and is noted. Dates are the days the stock movements were recorded.
    Generated {{ $report['generated_at']->format('M d, Y h:i A') }}.
</div>
