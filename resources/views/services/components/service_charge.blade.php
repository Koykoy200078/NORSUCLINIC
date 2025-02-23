<div>
    @if($row->charges == 0 || $row->charges == '0.00')
    Free
    @else
    {{ getCurrencyFormat(getCurrencyCode(), $row->charges) }}
    @endif
</div>