<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Prescription
 *
 * @property int $id
 * @property int $patient_id
 * @property int|null $doctor_id
 * @property string|null $food_allergies
 * @property string|null $tendency_bleed
 * @property string|null $heart_disease
 * @property string|null $high_blood_pressure
 * @property string|null $diabetic
 * @property string|null $surgery
 * @property string|null $accident
 * @property string|null $others
 * @property string|null $medical_history
 * @property string|null $current_medication
 * @property string|null $female_pregnancy
 * @property string|null $breast_feeding
 * @property string|null $health_insurance
 * @property string|null $low_income
 * @property string|null $reference
 * @property bool|null $status
 * @property string|null $plus_rate
 * @property string|null $temperature
 * @property string|null $problem_description
 * @property string|null $test
 * @property string|null $advice
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Doctor|null $doctor
 * @property-read \App\Models\Patient $patient
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription query()
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereAccident($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereAdvice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereBreastFeeding($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereCurrentMedication($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereDiabetic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereDoctorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereFemalePregnancy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereFoodAllergies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereHealthInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereHeartDisease($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereHighBloodPressure($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereLowIncome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereMedicalHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereOthers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription wherePlusRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereProblemDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereSurgery($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereTemperature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereTendencyBleed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereTest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Prescription extends Model
{
    public $table = 'prescriptions';

    public $fillable = [
        'patient_id',
        'doctor_id',
        'doctor_license_s2_number',
        'consultation_date',
        'icd10_diagnosis_id',
        'next_visit_days',
        'weight_kg',
        'pulse_rate',
        'body_temperature',
        'blood_pressure',
        'height_cm',
        'food_allergies',
        'tendency_bleed',
        'heart_disease',
        'high_blood_pressure',
        'diabetic',
        'current_medication',
        'female_pregnancy',
        'breast_feeding',
        'health_insurance',
        'low_income',
        'reference',
        'is_active',
        'status',
        'dispensed_at',
        'dispensed_by',
        'plus_rate',
        'temperature',
        'problem_description',
        'test',
        'advice',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'patient_id' => 'integer',
        'doctor_id' => 'integer',
        'doctor_license_s2_number' => 'string',
        'consultation_date' => 'date',
        'icd10_diagnosis_id' => 'integer',
        'next_visit_days' => 'integer',
        'weight_kg' => 'decimal:2',
        'pulse_rate' => 'string',
        'body_temperature' => 'decimal:1',
        'blood_pressure' => 'string',
        'height_cm' => 'decimal:2',
        'food_allergies' => 'string',
        'tendency_bleed' => 'string',
        'heart_disease' => 'string',
        'high_blood_pressure' => 'string',
        'diabetic' => 'string',
        'current_medication' => 'string',
        'female_pregnancy' => 'string',
        'breast_feeding' => 'string',
        'health_insurance' => 'string',
        'low_income' => 'string',
        'reference' => 'string',
        'is_active' => 'boolean',
        'status' => 'string',
        'dispensed_at' => 'datetime',
        'dispensed_by' => 'integer',
        'plus_rate' => 'string',
        'temperature' => 'string',
        'problem_description' => 'string',
        'test' => 'string',
        'advice' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'patient_id' => 'required',
    ];

    const STATUS_ALL = 2;

    const ACTIVE = 1;

    const INACTIVE = 0;

    const STATUS_ARR = [
        self::STATUS_ALL => 'All',
        self::ACTIVE => 'Active',
        self::INACTIVE => 'Deactive',
    ];

    public const DISPENSE_STATUS_PENDING = 'pending';
    public const DISPENSE_STATUS_DISPENSED = 'dispensed';
    public const DISPENSE_STATUS_CANCELLED = 'cancelled';

    public const DISPENSE_STATUS_OPTIONS = [
        self::DISPENSE_STATUS_PENDING => 'Pending',
        self::DISPENSE_STATUS_DISPENSED => 'Dispensed',
        self::DISPENSE_STATUS_CANCELLED => 'Cancelled',
    ];

    const DAYS = 0;

    const MONTH = 1;

    const YEAR = 2;

    const TIME_ARR = [
        self::DAYS => 'Days',
        self::MONTH => 'Month',
        self::YEAR => 'Years',
    ];

    const AFETR_MEAL = 0;

    const BEFORE_MEAL = 1;

    const MEAL_ARR = [
        self::AFETR_MEAL => 'After Meal',
        self::BEFORE_MEAL => 'Before Meal',
    ];

    const ONE_TIME = 1;
    const TWO_TIME = 2;
    const THREE_TIME = 3;
    const FOUR_TIME = 4;
    const FIVE_TIME = 5;

    const DOSE_INTERVAL = [
        self::ONE_TIME => 'Every Morning',
        self::TWO_TIME => 'Every Evening',
        self::THREE_TIME => 'Every Morning & Evening',
        self::FOUR_TIME => 'Three times a day',
        self::FIVE_TIME => '4 times a day'
    ];

    const ONE_DAY = 1;
    const THREE_DAY = 3;
    const ONE_WEEK = 7;
    const TWO_WEEK = 14;
    const ONE_MONTH = 30;

    const DOSE_DURATION = [
        self::ONE_DAY => 'One day only',
        self::THREE_DAY => 'For Three days',
        self::ONE_WEEK => 'For One week',
        self::TWO_WEEK => 'For 2 weeks',
        self::ONE_MONTH => 'For 1 Month',
    ];

    const ROUTE_ORAL = 'oral';

    const ROUTE_IV = 'iv';

    const ROUTE_IM = 'im';

    const ROUTE_TOPICAL = 'topical';

    const ROUTE_SUBCUTANEOUS = 'subcutaneous';

    const ROUTE_INHALATION = 'inhalation';

    const ROUTE_OTHER = 'other';

    const DURATION_UNIT_DAY = 'day';

    const DURATION_UNIT_WEEK = 'week';

    const DURATION_UNIT_MONTH = 'month';

    const ROUTE_OPTIONS = [
        self::ROUTE_ORAL => 'Oral',
        self::ROUTE_IV => 'IV',
        self::ROUTE_IM => 'IM',
        self::ROUTE_TOPICAL => 'Topical',
        self::ROUTE_SUBCUTANEOUS => 'Subcutaneous',
        self::ROUTE_INHALATION => 'Inhalation',
        self::ROUTE_OTHER => 'Other',
    ];

    const DURATION_UNIT_OPTIONS = [
        self::DURATION_UNIT_DAY => 'Day(s)',
        self::DURATION_UNIT_WEEK => 'Week(s)',
        self::DURATION_UNIT_MONTH => 'Month(s)',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function diagnosis(): BelongsTo
    {
        return $this->belongsTo(Diagnose::class, 'icd10_diagnosis_id');
    }

    public function getMedicine(): HasMany
    {
        return $this->hasMany(PrescriptionMedicine::class);
    }

    public function dispensedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }
}
