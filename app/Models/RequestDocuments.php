<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class RequestDocuments extends Model
{
    use HasFactory, InteractsWithMedia, HasRoles;

    protected $table = 'request_documents';

    const ALL_PATIENT = 1;
    const ONLY_ONE_PATIENT = 2;
    const REMANING_PATIENT = 3;

    const ALL = 1;
    const TODAY = 2;
    const WEEK = 3;
    const MONTH = 4;
    const YEAR = 5;

    const REQUEST_DOCUMENT_FILTER = [
        self::ALL => 'All',
        self::TODAY => 'Today',
        self::WEEK => 'This Week',
        self::MONTH => 'This Month',
        self::YEAR => 'This Year',
    ];

    const STATUS = [
        self::ALL_PATIENT => 'All',
        self::ONLY_ONE_PATIENT => 'Active',
        self::REMANING_PATIENT => 'Deactive',
    ];

    protected $fillable = [
        'user_id',
        'clinic_id',
        'document_type',
        'description',
        'requested_at',
        'fulfilled_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
