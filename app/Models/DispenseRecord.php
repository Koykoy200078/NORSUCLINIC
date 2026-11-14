<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Renamed from MedicineBill -> DispenseRecord.
 * Table remains `medicine_bills` for backward compatibility.
 * Legacy financial semantics are removed in the recode.
 */
class DispenseRecord extends Model
{
    use HasFactory, \App\Traits\ChecksPatientReference;

    protected $table = 'medicine_bills';

    protected $fillable = [
        'history_number',
        'patient_id',
        'doctor_id',
        'model_type',
        'model_id',
        'case_id',
        'admission_id',
        'note',
        'bill_date',
        // Meds Only (2026-10-29): who got the medicine and how the reports group them, as of the day.
        'meds_only',
        'patient_name',
        'campus_id',
        'college_id',
        'course_id',
        'year_level_id',
        'patient_type_id',
        'campus',
        'college',
        'course',
        'year_level',
        'recorded_by',
    ];

    protected $casts = ['meds_only' => 'boolean'];

    /** Only the Meds Only records (patients who came for medicine alone). */
    public function scopeMedsOnly($query)
    {
        return $query->where($query->getModel()->getTable().'.meds_only', true);
    }

    /** Only the manual dispense records: the older "Add Dispense Record" pop-up of Dispense History (not Meds Only, no prescription). */
    public function scopeManual($query)
    {
        $table = $query->getModel()->getTable();

        return $query->where($table.'.model_type', self::class)->where($table.'.meds_only', false);
    }

    /** What the Meds Only list shows: its own records and the manual dispense records (the owner's decision, 2026-11-14). */
    public function scopeInMedsOnlyList($query)
    {
        return $query->where(fn ($records) => $records->medsOnly()->orWhere(fn ($manual) => $manual->manual()));
    }

    public function isManual(): bool
    {
        return ! $this->meds_only && $this->model_type === self::class;
    }

    /**
     * What was handed out as one line of words: "Paracetamol 500mg x 4, Cetirizine 10mg x 2" (one entry per medicine and
     * strength; the stock engine may have taken one entry from several batches).
     */
    public function medicineSummary(): string
    {
        return $this->dispenseItems->groupBy(fn ($item) => $item->medicine_id . '|' . mb_strtolower(trim((string) $item->dosage)))
            ->map(function ($items) {
                $first = $items->first();
                $dosage = trim((string) $first->dosage);

                return ($first->medicine?->name ?? 'Deleted medicine') . ($dosage !== '' ? ' ' . $dosage : '') . ' x ' . (int) $items->sum('quantity');
            })->implode(', ');
    }

    /** The person who handed the medicine out (Meds Only records; a manual dispense record has no author). */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by')->withTrashed();
    }

    /** The patient type (Student, Faculty ...) a Meds Only record was made for. */
    public function patientType(): BelongsTo
    {
        return $this->belongsTo(PatientType::class, 'patient_type_id');
    }

    /** Null for a Meds Only record of a person who is not registered. */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function dispenseItems(): HasMany
    {
        return $this->hasMany(DispenseRecordItem::class, 'medicine_bill_id');
    }

    /** @deprecated Use dispenseItems() */
    public function saleMedicine(): HasMany
    {
        return $this->dispenseItems();
    }
}
