<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LabRequest
 *
 * Tracks laboratory and medical requests issued by the clinic.
 * Status flow: pending → collected → processing → completed
 *              (side exits: cancelled | referred | rejected)
 */
class LabRequest extends Model
{
    use HasFactory;

    protected $table = 'lab_requests';

    // -------------------------------------------------------------------------
    // Status constants
    // -------------------------------------------------------------------------

    const STATUS_PENDING    = 'pending';
    const STATUS_COLLECTED  = 'collected';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED  = 'completed';
    const STATUS_CANCELLED  = 'cancelled';
    const STATUS_REFERRED   = 'referred';
    const STATUS_REJECTED   = 'rejected';

    /**
     * Human-readable status labels.
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING    => 'Pending',
            self::STATUS_COLLECTED  => 'Collected',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED  => 'Completed',
            self::STATUS_CANCELLED  => 'Cancelled',
            self::STATUS_REFERRED   => 'Referred',
            self::STATUS_REJECTED   => 'Rejected',
        ];
    }

    /**
     * Valid next statuses from any given current status.
     */
    public static function allowedTransitions(): array
    {
        return [
            self::STATUS_PENDING    => [self::STATUS_COLLECTED, self::STATUS_CANCELLED, self::STATUS_REFERRED, self::STATUS_REJECTED],
            self::STATUS_COLLECTED  => [self::STATUS_PROCESSING, self::STATUS_CANCELLED, self::STATUS_REFERRED],
            self::STATUS_PROCESSING => [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_REFERRED],
            self::STATUS_COMPLETED  => [], // terminal
            self::STATUS_CANCELLED  => [], // terminal
            self::STATUS_REFERRED   => [self::STATUS_CANCELLED],
            self::STATUS_REJECTED   => [], // terminal
        ];
    }

    // -------------------------------------------------------------------------
    // Fillable
    // -------------------------------------------------------------------------

    protected $fillable = [
        'request_number',
        'document_creator_id',
        'patient_user_id',
        'patient_name',
        'patient_age',
        'patient_gender',
        'patient_dob',
        'patient_contact',
        'address',
        'campus',
        'college',
        'course',
        'year_level',
        'department',
        'office',
        'status_affiliation',
        'requested_at',
        'clinical_indication',
        'remarks',
        'status',
        'collected_at',
        'processed_at',
        'completed_at',
        'cancelled_at',
        'referred_at',
        'rejected_at',
        'status_note',
        'requesting_physician',
        'physician_license_no',
    ];

    protected $casts = [
        'requested_at'  => 'date',
        'collected_at'  => 'datetime',
        'processed_at'  => 'datetime',
        'completed_at'  => 'datetime',
        'cancelled_at'  => 'datetime',
        'referred_at'   => 'datetime',
        'rejected_at'   => 'datetime',
        'patient_dob'   => 'date',
    ];

    // -------------------------------------------------------------------------
    // Boot
    // -------------------------------------------------------------------------

    protected static function boot()
    {
        parent::boot();

        // Cascade-delete items when the request is hard-deleted
        static::deleting(function ($labRequest) {
            $labRequest->items()->delete();
        });
    }

    // -------------------------------------------------------------------------
    // Deleting
    // -------------------------------------------------------------------------

    /**
     * Only a request that is still pending, or was cancelled, can be deleted. Every other status means a
     * specimen was taken or the request holds results / a referral, which are part of the patient record.
     */
    public function isDeletable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CANCELLED], true);
    }

    /**
     * An unfinished request can be deleted by the clinic admin or by the person who created it.
     */
    public function canBeDeletedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasRole('clinic_admin') || (int) $this->document_creator_id === (int) $user->id;
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The patient user record.
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    /**
     * The staff/doctor/admin who created the request.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'document_creator_id');
    }

    /**
     * Individual test line items.
     */
    public function items()
    {
        return $this->hasMany(LabRequestItem::class, 'lab_request_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Badge Bootstrap class for current status.
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING    => 'warning text-dark',
            self::STATUS_COLLECTED  => 'info text-dark',
            self::STATUS_PROCESSING => 'primary',
            self::STATUS_COMPLETED  => 'success',
            self::STATUS_CANCELLED  => 'danger',
            self::STATUS_REFERRED   => 'secondary',
            self::STATUS_REJECTED   => 'dark',
            default                 => 'light text-dark',
        };
    }

    /**
     * Icon class for current status.
     */
    public function getStatusIcon(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING    => 'fa-clock',
            self::STATUS_COLLECTED  => 'fa-vial',
            self::STATUS_PROCESSING => 'fa-microscope',
            self::STATUS_COMPLETED  => 'fa-check-circle',
            self::STATUS_CANCELLED  => 'fa-times-circle',
            self::STATUS_REFERRED   => 'fa-external-link-alt',
            self::STATUS_REJECTED   => 'fa-ban',
            default                 => 'fa-question-circle',
        };
    }

    /**
     * Whether the request is in a terminal state (no further status updates).
     */
    public function isTerminal(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_REJECTED,
        ]);
    }

    /**
     * Get comma-separated list of test names for display.
     */
    public function getTestNamesSummary(int $limit = 3): string
    {
        $names = $this->items->pluck('test_name')->take($limit)->toArray();
        $remaining = $this->items->count() - count($names);

        $summary = implode(', ', $names);
        if ($remaining > 0) {
            $summary .= " +{$remaining} more";
        }

        return $summary ?: '—';
    }
}
