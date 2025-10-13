<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_name',
        'description',
    ];

    /**
     * Get all users associated with this office
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
