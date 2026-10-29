{{-- How to read the MEDICINES WITH NOTES report; the same words under the screen and the PDF. --}}
<div class="inv-note">
    This report lists what the finalized count sheets found. "System" is the amount the stock ledger showed for the sheet's period
    (the end of the quarter, or the shelf on the day of a count of everything); "Counted" is what was counted by hand; "Difference"
    is counted minus system. "Stock corrected" is the day the shelf stock was corrected from that line (blank: it was not).
    A draft sheet and a medicine that was not counted are not in it. Generated {{ $report['generated_at']->format('M d, Y h:i A') }}.
</div>
