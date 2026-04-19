<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MedicineTransaction extends Model
{
    public const TYPE_STOCK_IN = 'stock_in';
    public const TYPE_DISPENSE = 'dispense';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_DISPOSAL = 'disposal';

    // Backward-compatible aliases for legacy references.
    public const TYPE_STOCK_OUT = self::TYPE_DISPENSE;
    public const TYPE_RETURN = self::TYPE_DISPOSAL;

    protected $table = 'medicine_transactions';

    protected $fillable = [
        'batch_id',
        'user_id',
        'transaction_type',
        'quantity',
        'balance_after',
        'reference_type',
        'reference_id',
        'remarks',
    ];

    protected $casts = [
        'batch_id' => 'integer',
        'user_id' => 'integer',
        'quantity' => 'integer',
        'balance_after' => 'integer',
        'reference_id' => 'integer',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicineBatch::class, 'batch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
