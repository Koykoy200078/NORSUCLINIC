<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientType extends Model
{
    use HasFactory;

    protected $table = 'patient_types';

    protected $fillable = [
        'code',
        'name',
    ];

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'patient_type_id');
    }
}
