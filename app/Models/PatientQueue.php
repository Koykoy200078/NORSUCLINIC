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
        'latest_consultation_id',
        'has_consultation_attachment',
        'called_at',
        'completed_at',
        'scheduled_at',
    ];

    protected $casts = [
        'is_priority' => 'boolean',
        'called_at' => 'datetime',
        'completed_at' => 'datetime',
        'scheduled_at' => 'datetime',
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
     * Get the latest consultation form attached to this queue entry
     */
    public function latestConsultation(): BelongsTo
    {
        return $this->belongsTo(RequestDocuments::class, 'latest_consultation_id');
    }

    /** Statuses that still hold a place in today's queue. */
    public const OPEN_STATUSES = [self::STATUS_WAITING, self::STATUS_IN_PROGRESS];

    /**
     * Scope a query to only include waiting patients
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', self::STATUS_WAITING);
    }

    /**
     * Only entries whose patient (and the patient's user) still exist. An archived patient makes the
     * relation null, which the queue screens cannot render.
     */
    public function scopeWithLivePatient($query)
    {
        return $query->whereHas('patient.user');
    }

    /**
     * Close every entry still open from a previous day. The clinic queue is a same-day list: a patient
     * who left yesterday must not block being queued today, show up on the doctor's screen, or keep
     * counting in the sidebar badge.
     */
    public static function closeStaleEntries(): int
    {
        return static::whereIn('status', self::OPEN_STATUSES)
            ->where('created_at', '<', today())
            ->update([
                'status' => self::STATUS_CANCELLED,
                'completed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * The patient's most recent consultation form that is still on file (a deleted one is skipped).
     */
    public static function latestConsultationFor(int $patientId): ?DocumentIssuance
    {
        $userId = Patient::whereKey($patientId)->value('user_id');

        if (! $userId) {
            return null;
        }

        return DocumentIssuance::where('user_id', $userId)
            ->where('document_type', 'consultation_form')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Attach a consultation recorded after the patient was queued, so the doctor can open the form
     * from the queue screen.
     */
    public static function attachConsultation(DocumentIssuance $consultation): void
    {
        if ($consultation->user_id) {
            static::syncAttachment((int) $consultation->user_id);
        }
    }

    /**
     * Point the patient's open queue entry at their latest consultation that is still on file, or at nothing when
     * there is none. Run after a consultation is saved, deleted or restored, so the doctor's screen never shows a
     * form that no longer exists and always shows the newest one.
     */
    public static function syncAttachment(int $userId): void
    {
        $patientId = Patient::where('user_id', $userId)->value('id');
        if (! $patientId) {
            return;
        }

        $latest = static::latestConsultationFor((int) $patientId);

        static::where('patient_id', $patientId)
            ->whereIn('status', self::OPEN_STATUSES)
            ->update([
                'latest_consultation_id' => $latest?->id,
                'has_consultation_attachment' => $latest !== null,
            ]);
    }

    /**
     * True when the attached form was recorded on the same day as this queue entry, i.e. it belongs to today's
     * visit. False when the attached form is an earlier visit's: the doctor can read it, but the nurse has not
     * recorded today's yet.
     */
    public function getAttachedFormIsNewAttribute(): bool
    {
        $form = $this->latestConsultation;

        return $form !== null
            && $form->created_at !== null
            && $form->created_at->isSameDay($this->created_at ?? now());
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
