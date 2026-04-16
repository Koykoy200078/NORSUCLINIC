<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceProvider extends Model
{
    use HasFactory;

    protected $table = 'insurance_providers';

    protected $fillable = [
        'name',
        'contact_no',
        'email',
        'website',
    ];

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'insurance_provider_id');
    }
}
