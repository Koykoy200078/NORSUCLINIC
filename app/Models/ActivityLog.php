<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ActivityLog
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $user_type
 * @property string|null $user_name
 * @property string $action
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $date
 * @property string|null $patient_name
 * @property int|null $patient_age
 * @property string|null $patient_gender
 * @property string|null $college
 * @property string|null $address
 * @property string|null $contact_number
 * @property string|null $complaints
 * @property string|null $diagnosis
 * @property string|null $informant
 * @property string|null $consult_mode
 * @property string|null $course_section
 * @property array|null $properties
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'user_type',
        'user_name',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'date',
        'patient_name',
        'patient_age',
        'patient_gender',
        'college',
        'address',
        'contact_number',
        'complaints',
        'diagnosis',
        'informant',
        'consult_mode',
        'course_section',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'date' => 'date',
        'patient_age' => 'integer',
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the subject (polymorphic relation)
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter by user type
     */
    public function scopeByUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Scope to filter by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by subject type
     */
    public function scopeBySubjectType($query, $subjectType)
    {
        return $query->where('subject_type', $subjectType);
    }

    /**
     * Get formatted user type
     */
    public function getFormattedUserTypeAttribute(): string
    {
        return match ($this->user_type) {
            'admin' => 'Clinic Admin',
            'doctor' => 'Doctor',
            'staff' => 'Staff',
            default => $this->user_type ?? 'System'
        };
    }

    /**
     * Get formatted action
     */
    public function getFormattedActionAttribute(): string
    {
        // Friendlier labels for specific actions (keeps the stored action key stable so
        // history and filters are unaffected). "procured_medicine" reads as "Stock In"
        // because that is the operation staff actually perform (adding stock).
        $labelMap = [
            'procured_medicine' => 'Stock In',
            'used_medicine' => 'Medicine Dispensed',
            'medicine_quantity_updated' => 'Stock Adjusted',
        ];

        return $labelMap[$this->action] ?? ucwords(str_replace('_', ' ', $this->action));
    }
}
