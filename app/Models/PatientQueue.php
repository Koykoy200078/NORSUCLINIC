<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'added_by',
        'room_number',
        'is_priority',
        'status',
        'notes',
        'called_at',
        'completed_at',
    ];

    protected $casts = [
        'is_priority' => 'boolean',
        'called_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_WAITING = 'waiting';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_WAITING => 'Waiting',
        self::STATUS_IN_PROGRESS => 'In Progress',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    /**
     * Get the patient that this queue entry belongs to
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user (nurse/staff) who added this queue entry
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Scope a query to only include waiting patients
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', self::STATUS_WAITING);
    }

    /**
     * Scope a query to only include priority patients
     */
    public function scopePriority($query)
    {
        return $query->where('is_priority', true);
    }

    /**
     * Scope a query to order by priority and creation time
     */
    public function scopeOrderByQueue($query)
    {
        return $query->orderBy('is_priority', 'desc')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get queue number based on position
     */
    public function getQueueNumberAttribute()
    {
        $position = self::where('status', self::STATUS_WAITING)
            ->where(function ($query) {
                $query->where('is_priority', '>', $this->is_priority)
                    ->orWhere(function ($q) {
                        $q->where('is_priority', $this->is_priority)
                            ->where('created_at', '<', $this->created_at);
                    });
            })
            ->count();

        return $position + 1;
    }
}
