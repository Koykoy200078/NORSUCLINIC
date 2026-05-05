<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Renamed from UsedMedicineView → StockOutView.
 * Points to the `used_medicines_view` DB view (old workspace).
 * In the recode target this view is named `stock_out_view`.
 */
class StockOutView extends Model
{
    protected $table = 'used_medicines_view';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'medicine_id',
        'medicine_name',
        'dosage',
        'quantity',
        'source',
        'patient_name',
        'nurse_incharged',
        'used_for',
        'expiry_date',
        'created_at',
    ];

    protected $casts = [
        'dosage' => 'string',
        'created_at' => 'datetime',
        'expiry_date' => 'datetime',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
}
