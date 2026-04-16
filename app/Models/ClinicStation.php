<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicStation extends Model
{
    use HasFactory;

    protected $table = 'clinic_stations';

    protected $fillable = [
        'name',
        'description',
    ];

    public function staffProfiles(): HasMany
    {
        return $this->hasMany(StaffProfile::class, 'assigned_station_id');
    }
}
