<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Visit
 *
 * @property int $id
 * @property string $visit_date
 * @property int $doctor_id
 * @property int $patient_id
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Doctor $doctor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VisitNote> $notes
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VisitObservation> $observations
 * @property-read int|null $observations_count
 * @property-read \App\Models\Patient $patient
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VisitPrescription> $prescriptions
 * @property-read int|null $prescriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VisitProblem> $problems
 * @property-read int|null $problems_count
 * @property-read \App\Models\Doctor $visitDoctor
 * @property-read \App\Models\Patient $visitPatient
 * @method static \Illuminate\Database\Eloquent\Builder|Visit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Visit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Visit query()
 * @method static \Illuminate\Database\Eloquent\Builder|Visit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Visit whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Visit whereDoctorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Visit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Visit wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Visit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Visit whereVisitDate($value)
 * @mixin \Eloquent
 */
class Visit extends Model
{
    use HasFactory;

    public $table = 'visits';

    public $fillable = [
        'visit_date',
        'doctor_id',
        'patient_id',
        'description',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'visit_date' => 'string',
        'doctor' => 'integer',
        'patient' => 'integer',
        'description' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'visit_date' => 'required',
        'doctor_id' => 'required',
        'patient_id' => 'required',
    ];

    public function visitDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function visitPatient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function problems(): HasMany
    {
        return $this->hasMany(VisitProblem::class, 'visit_id');
    }

    public function observations(): HasMany
    {
        return $this->hasMany(VisitObservation::class, 'visit_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(VisitNote::class, 'visit_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(VisitPrescription::class, 'visit_id');
    }
}
