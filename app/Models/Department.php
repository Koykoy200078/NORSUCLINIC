<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_name',
        'college_id',
        'description'
    ];

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
