<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Renamed from PrescriptionMedicineModal → PrescriptionMedicine.
 * "Modal" suffix was incorrect for a model class.
 * Table: prescriptions_medicines
 */
class PrescriptionMedicine extends Model
{
    use HasFactory;

    public $table = 'prescriptions_medicines';

    public $fillable = [
        'id',
        'prescription_id',
        'medicine',
        'dosage',
        'route_of_administration',
        'frequency',
        'duration_value',
        'duration_unit',
        'total_quantity',
        'instructions',
        'day',
        'time',
        'dose_interval',
        'comment',
    ];

    protected $casts = [
        'prescription_id' => 'integer',
        'medicine'        => 'integer',
        'dosage'          => 'string',
        'route_of_administration' => 'string',
        'frequency' => 'integer',
        'duration_value' => 'integer',
        'duration_unit' => 'string',
        'total_quantity' => 'integer',
        'instructions' => 'string',
        'day'             => 'string',
        'time'            => 'string',
        'dose_interval'   => 'integer',
        'comment'         => 'string',
    ];

    public static $rules = [];

    public function prescription(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function medicines(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine', 'id');
    }
}
