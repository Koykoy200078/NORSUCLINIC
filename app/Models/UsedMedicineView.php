<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsedMedicineView extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'used_medicines_view';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'medicine_id',
        'medicine_name',
        'quantity',
        'source',
        'patient_name',
        'nurse_incharged',
        'used_for',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];
}
