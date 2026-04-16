<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfile extends Model
{
    use HasFactory;

    protected $table = 'staff_profiles';

    protected $fillable = [
        'user_id',
        'role_designation_id',
        'assigned_station_id',
        'shift_schedule',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function roleDesignation(): BelongsTo
    {
        return $this->belongsTo(StaffDesignation::class, 'role_designation_id');
    }

    public function assignedStation(): BelongsTo
    {
        return $this->belongsTo(ClinicStation::class, 'assigned_station_id');
    }
}
