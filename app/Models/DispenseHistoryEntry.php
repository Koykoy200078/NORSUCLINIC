<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row of the Dispense History tab: read-only model of the `dispense_history_view` database view, which lists
 * dispense records, dispensed prescriptions and the medicines recorded inside consultations side by side
 * (see the migration for the exact rules). Nothing is written through this model.
 *
 * @property string $id 'B<medicine_bills.id>' or 'C<document_issuances.id>'
 * @property string $source Dispense Record | Prescription | Consultation
 * @property int $record_id id of the row in its own table (medicine_bills or document_issuances)
 * @property string $history_number
 * @property \Illuminate\Support\Carbon|null $dispensed_at
 * @property int|null $patient_id
 * @property string|null $patient_name
 * @property string|null $patient_email
 * @property int|null $doctor_id set only when the person is a doctor
 * @property string|null $given_by doctor, or the person who recorded the consultation
 * @property string|null $given_by_email
 * @property int $quantity total units
 * @property string|null $used_for consultations only: 'plan', 'nursing' or 'nursing,plan'
 */
class DispenseHistoryEntry extends Model
{
    public const SOURCE_DISPENSE_RECORD = 'Dispense Record';

    public const SOURCE_PRESCRIPTION = 'Prescription';

    public const SOURCE_CONSULTATION = 'Consultation';

    public const SOURCES = [
        self::SOURCE_DISPENSE_RECORD,
        self::SOURCE_PRESCRIPTION,
        self::SOURCE_CONSULTATION,
    ];

    protected $table = 'dispense_history_view';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $casts = [
        'dispensed_at' => 'datetime',
        'record_id' => 'integer',
        'patient_id' => 'integer',
        'doctor_id' => 'integer',
        'quantity' => 'integer',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function isConsultation(): bool
    {
        return $this->source === self::SOURCE_CONSULTATION;
    }

    /** Only a manual dispense record can be edited or deleted from the history; the other two belong to their own record. */
    public function isManualDispenseRecord(): bool
    {
        return $this->source === self::SOURCE_DISPENSE_RECORD;
    }
}
