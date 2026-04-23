<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LabRequestItem
 */
class LabRequestItem extends Model
{
    use HasFactory;

    protected $table = 'lab_request_items';

    protected $fillable = [
        'lab_request_id',
        'lab_test_id',
        'test_name',
        'test_category',
        'unit',
        'normal_range',
        'result_value',
        'result_status',
        'notes',
    ];

    protected $casts = [
        'result_status' => 'string',
    ];

    // -------------------------------------------------------------------------
    // Result status constants
    // -------------------------------------------------------------------------

    const RESULT_PENDING  = 'pending';
    const RESULT_NORMAL   = 'normal';
    const RESULT_ABNORMAL = 'abnormal';
    const RESULT_CRITICAL = 'critical';

    public static function resultStatuses(): array
    {
        return [
            self::RESULT_PENDING  => 'Pending',
            self::RESULT_NORMAL   => 'Normal',
            self::RESULT_ABNORMAL => 'Abnormal',
            self::RESULT_CRITICAL => 'Critical',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function labRequest()
    {
        return $this->belongsTo(LabRequest::class, 'lab_request_id');
    }

    public function labTest()
    {
        return $this->belongsTo(LabTest::class, 'lab_test_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function getResultStatusBadgeClass(): string
    {
        return match ($this->result_status) {
            'normal'   => 'success',
            'abnormal' => 'warning',
            'critical' => 'danger',
            default    => 'secondary',
        };
    }
}
