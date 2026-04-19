<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineBatch extends Model
{
    protected $table = 'medicine_batches';

    protected $fillable = [
        'medicine_id',
        'batch_number',
        'dosage',
        'quantity',
        'manufacturing_date',
        'expiration_date',
        'supplier_name',
        'unit_cost',
        'date_received',
    ];

    protected $casts = [
        'medicine_id' => 'integer',
        'dosage' => 'string',
        'quantity' => 'integer',
        'manufacturing_date' => 'date',
        'expiration_date' => 'date',
        'unit_cost' => 'decimal:2',
        'date_received' => 'date',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MedicineTransaction::class, 'batch_id');
    }
}
