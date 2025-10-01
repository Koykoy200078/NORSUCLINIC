<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\MedicineBill
 *
 * @property int $id
 * @property string $history_number
 * @property int $patient_id
 * @property int|null $doctor_id
 * @property string $model_type
 * @property string $model_id
 * @property float $discount
 * @property float $net_amount
 * @property float $total
 * @property float $tax_amount
 * @property int $payment_status
 * @property int $payment_type
 * @property string|null $note
 * @property string $bill_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Doctor|null $doctor
 * @property-read \App\Models\Patient $patient
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SaleMedicine> $saleMedicine
 * @property-read int|null $sale_medicine_count
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill query()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereBillDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereHistoryNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereDoctorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereNetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MedicineBill extends Model
{
    use HasFactory;

    protected $table = 'medicine_bills';

    protected $fillable = [
        'history_number',
        'patient_id',
        'doctor_id',
        'model_type',
        'model_id',
        'case_id',
        'admission_id',
        'discount',
        'net_amount',
        'payment_status',
        'payment_type',
        'note',
        'tax_amount',
        'total',
        'bill_date',
    ];

    const UNPAID = 0;

    const FULLPAID = 1;

    const PARTIALY_PAID = 2;

    const PAYMENT_STATUS_ARRAY =
    [
        self::UNPAID => 'Unpaid',
        self::FULLPAID => 'Full Paid',
        self::PARTIALY_PAID => 'Partially Paid',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function saleMedicine(): HasMany
    {
        return $this->hasMany(SaleMedicine::class, 'medicine_bill_id');
    }
}
