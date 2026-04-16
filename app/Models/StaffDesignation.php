<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffDesignation extends Model
{
    use HasFactory;

    protected $table = 'staff_designations';

    protected $fillable = [
        'code',
        'name',
    ];

    public function staffProfiles(): HasMany
    {
        return $this->hasMany(StaffProfile::class, 'role_designation_id');
    }
}
