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


/**
 * App\Models\Patient
 *
 * @property int $id
 * @property string $patient_unique_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Address|null $address
 * @property-read string $profile
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\User $patientUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RequestDocuments> $requestDocuments
 * @property-read int|null $request_documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\PatientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Patient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Patient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Patient permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient query()
 * @method static \Illuminate\Database\Eloquent\Builder|Patient role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient wherePatientUniqueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereUserId($value)
 * @mixin \Eloquent
 */
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
        'contact' => 'nullable',
        'password' => 'nullable|same:password_confirmation|min:6',
        'postal_code' => 'nullable',
        'profile' => 'nullable|mimes:jpeg,jpg,png|max:2000',
        'emergency_contact_name' => 'nullable',
        'emergency_contact_no' => 'nullable',
        'emergency_relationship' => 'nullable|string',
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
        'emergency_contact_name' => 'nullable',
        'emergency_contact_no' => 'nullable',
        'emergency_relationship' => 'nullable|string',
    ];

    protected $appends = ['profile'];

    protected $with = ['media'];

    /**
     * Boot the model and set up event listeners for cascade delete
     */
    protected static function boot()
    {
        parent::boot();

        // When a patient is being deleted, delete all related data
        static::deleting(function ($patient) {
            // Delete all patient queue entries
            $patient->queueEntries()->delete();

            // Delete all prescriptions
            $patient->prescriptions()->delete();

            // Delete all medicine bills
            $patient->medicineBills()->delete();

            // Delete all request documents (consultation forms, medical certificates)
            // Use get()->each() to trigger deleting events on each document
            // This ensures images are deleted from storage
            $patient->requestDocuments()->get()->each(function ($document) {
                $document->delete();
            });

            // Delete activity logs related to this patient
            \App\Models\ActivityLog::where('subject_type', 'App\Models\Patient')
                ->where('subject_id', $patient->id)
                ->delete();

            // Delete activity logs related to the patient's user account
            if ($patient->user) {
                \App\Models\ActivityLog::where('subject_type', 'App\Models\User')
                    ->where('subject_id', $patient->user->id)
                    ->delete();

                \App\Models\ActivityLog::where('user_id', $patient->user->id)->delete();

                // Delete user's address
                if ($patient->user->address) {
                    $patient->user->address->delete();
                }

                // Delete the user
                $patient->user->delete();
            }

            // Delete patient's address if exists
            if ($patient->address) {
                $patient->address->delete();
            }

            // Delete media files (profile images, etc.)
            $patient->clearMediaCollection(self::PROFILE);
        });
    }

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

    public function requestDocuments()
    {
        return $this->hasMany(RequestDocuments::class, 'user_id', 'user_id');
    }

    public function queueEntries(): HasMany
    {
        return $this->hasMany(PatientQueue::class);
    }

    public function currentQueue()
    {
        return $this->hasOne(PatientQueue::class)
            ->whereIn('status', [PatientQueue::STATUS_WAITING, PatientQueue::STATUS_IN_PROGRESS])
            ->latest();
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function medicineBills(): HasMany
    {
        return $this->hasMany(MedicineBill::class);
    }
}
