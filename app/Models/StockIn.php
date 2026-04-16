<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Renamed from MedicineAvailability → StockIn.
 * Represents a stock-in batch (purchase/receiving of medicines).
 * DB table: medicine_availabilities (unchanged).
 */
class StockIn extends Model
{
    protected $table = 'medicine_availabilities';

    protected $fillable = [
        'availability_no',
    ];

    public function purchasedMedcines(): HasMany
    {
        return $this->hasMany(PurchasedMedicine::class, 'medicine_availabilities_id');
    }
}
