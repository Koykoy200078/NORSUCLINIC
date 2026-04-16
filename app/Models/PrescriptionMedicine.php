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
        'day',
        'time',
        'dose_interval',
        'comment',
    ];

    protected $casts = [
        'prescription_id' => 'integer',
        'medicine'        => 'integer',
        'dosage'          => 'string',
        'day'             => 'string',
        'time'            => 'string',
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
