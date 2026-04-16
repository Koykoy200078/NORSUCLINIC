<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\Doctor
 *
 * @property int $id
 * @property int $user_id
 * @property float|null $experience
 * @property string|null $prc_license_number
 * @property string|null $ptr_number
 * @property string|null $s2_license_number
 * @property string|null $consultation_hours
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Address|null $address
 * @property-read \App\Models\User $doctorUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Specialization> $specializations
 * @property-read int|null $specializations_count
 * @property-read \App\Models\User $testUser
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor query()
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereExperience($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor wherePrcLicenseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor wherePtrNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereS2LicenseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereConsultationHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereUserId($value)
 * @mixin \Eloquent
 */
class Doctor extends Model
{
    use HasFactory;

    protected $table = 'doctors';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'specialization',
        'experience',
        'prc_license_number',
        'ptr_number',
        's2_license_number',
        'consultation_hours',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'prc_license_number' => 'string',
        'ptr_number' => 'string',
        's2_license_number' => 'string',
        'consultation_hours' => 'string',
    ];

    const O_POSITIVE = 1;

    const A_POSITIVE = 2;

    const B_POSITIVE = 3;

    const AB_POSITIVE = 4;

    const O_NEGATIVE = 5;

    const A_NEGATIVE = 6;

    const B_NEGATIVE = 7;

    const AB_NEGATIVE = 8;

    const BLOOD_TYPE_ARRAY = [
        self::O_POSITIVE => 'O+',
        self::A_POSITIVE => 'A+',
        self::B_POSITIVE => 'B+',
        self::AB_POSITIVE => 'AB+',
        self::O_NEGATIVE => 'O-',
        self::A_NEGATIVE => 'A-',
        self::B_NEGATIVE => 'B-',
        self::AB_NEGATIVE => 'AB-',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function doctorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function testUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class, 'doctor_specialization', 'doctor_id', 'specialization_id');
    }

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'owner');
    }
}
