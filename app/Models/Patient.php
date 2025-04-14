<?php

namespace App\Models;

use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;
use App\Models\SmartPatientCards;


class Patient extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasRoles;

    protected $table = 'patients';

    const PROFILE = 'profile';

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

    const MALE = 1;

    const FEMALE = 2;

    const ALL_PATIENT = 1;

    const ONLY_ONE_PATIENT = 2;

    const REMANING_PATIENT = 3;

    const ALL = 1;

    const TODAY = 2;

    const WEEK = 3;

    const MONTH = 4;

    const YEAR = 5;

    const PATIENT_FILTER = [
        self::ALL => 'All',
        self::TODAY => 'Today',
        self::WEEK => 'This Week',
        self::MONTH => 'This Month',
        self::YEAR => 'This Year',
    ];


    const STATUS = [
        self::ALL_PATIENT => 'All',
        self::ONLY_ONE_PATIENT => 'Active',
        self::REMANING_PATIENT => 'Deactive',
    ];

    public $fillable = [
        'patient_unique_id',
        'user_id',
    ];

    protected $casts = [
        'patient_unique_id' => 'string',
        'user_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'patient_unique_id' => 'required|unique:patients,patient_unique_id|regex:/^\S*$/u',
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'nullable|email|unique:users,email',
        'contact' => 'nullable|unique:users,contact',
        'password' => 'nullable|same:password_confirmation|min:6',
        'postal_code' => 'nullable',
        'profile' => 'nullable|mimes:jpeg,jpg,png|max:2000',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $editRules = [
        'first_name' => 'required',
        'last_name' => 'required',
        'profile' => 'nullable|mimes:jpeg,jpg,png',
    ];

    protected $appends = ['profile'];

    protected $with = ['media'];

    public static function generatePatientUniqueId(): string
    {
        $patientUniqueId = Str::random(8);
        while (true) {
            $isExist = self::wherePatientUniqueId($patientUniqueId)->exists();
            if ($isExist) {
                self::generatePatientUniqueId();
            }
            break;
        }

        return $patientUniqueId;
    }

    public function getProfileAttribute(): string
    {

        /** @var Media $media */
        $media = $this->getMedia(self::PROFILE)->first();

        if ($media) {
            $fullUrl = $media->getFullUrl();
            if (str_starts_with($fullUrl, 'http://localhost')) {
                $fullUrl = request()->getSchemeAndHttpHost() . parse_url($fullUrl, PHP_URL_PATH);
            }
            return $fullUrl;
        }

        $gender = $this->user->gender;
        if ($gender == self::FEMALE) {
            return asset('web/media/avatars/female.png');
        }

        return asset('web/media/avatars/male.png');
    }

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'owner');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function patientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function smartPatientCard(): BelongsTo
    {
        return $this->belongsTo(SmartPatientCards::class, 'template_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function requestDocuments()
    {
        return $this->hasMany(RequestDocuments::class, 'user_id', 'user_id');
    }
}
