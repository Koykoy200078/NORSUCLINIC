<?php

namespace App\Models;

use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\DispenseRecord;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
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

use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasRoles, SoftDeletes;

    protected $table = 'patients';

    public const DELETED_AT = 'archived_at';

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
        'patient_type_id',
        'allergies',
        'comorbidities',
        'admissions_surgeries',
        'maintenance',
        'covid_vaccination',
        'immunization_record',
        'insurance_provider_id',
        'insurance_policy_number',
        'primary_care_physician_name',
        'primary_care_physician_contact',
        'primary_care_physician_email',
    ];

    protected $casts = [
        'patient_unique_id' => 'string',
        'user_id' => 'integer',
        'patient_type_id' => 'integer',
        'insurance_provider_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'nullable|email|unique:users,email',
        'university_id_number' => 'nullable|string|max:100|unique:users,university_id_number',
        'contact' => 'nullable',
        'nationality_citizenship' => 'nullable|string|max:120',
        'password' => 'nullable|same:password_confirmation|min:6',
        'postal_code' => 'nullable|numeric',
        'patient_type_id' => 'nullable|exists:patient_types,id',
        'immunization_record' => 'nullable|string',
        'insurance_provider_id' => 'nullable|exists:insurance_providers,id',
        'insurance_policy_number' => 'nullable|string|max:120',
        'primary_care_physician_name' => 'nullable|string|max:191',
        'primary_care_physician_contact' => 'nullable|string|max:100',
        'primary_care_physician_email' => 'nullable|email|max:191',
        'profile' => 'nullable|mimes:jpeg,jpg,png|max:2000',
        'emergency_contact_name' => 'nullable',
        'emergency_contact_no' => 'nullable',
        'emergency_relationship' => 'nullable|string',
        'allergies' => 'nullable|string',
        'comorbidities' => 'nullable',
        'comorbidities.*' => 'nullable|string|max:191',
        'admissions_surgeries' => 'nullable|string',
        'maintenance' => 'nullable|string',
        'covid_vaccination' => 'nullable|string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $editRules = [
        'first_name' => 'required',
        'last_name' => 'required',
        'university_id_number' => 'nullable|string|max:100',
        'nationality_citizenship' => 'nullable|string|max:120',
        'patient_type_id' => 'nullable|exists:patient_types,id',
        'immunization_record' => 'nullable|string',
        'insurance_provider_id' => 'nullable|exists:insurance_providers,id',
        'insurance_policy_number' => 'nullable|string|max:120',
        'primary_care_physician_name' => 'nullable|string|max:191',
        'primary_care_physician_contact' => 'nullable|string|max:100',
        'primary_care_physician_email' => 'nullable|email|max:191',
        'profile' => 'nullable|mimes:jpeg,jpg,png',
        'emergency_contact_name' => 'nullable',
        'emergency_contact_no' => 'nullable',
        'emergency_relationship' => 'nullable|string',
        'allergies' => 'nullable|string',
        'comorbidities' => 'nullable',
        'comorbidities.*' => 'nullable|string|max:191',
        'admissions_surgeries' => 'nullable|string',
        'maintenance' => 'nullable|string',
        'covid_vaccination' => 'nullable|string',
        'postal_code' => 'nullable|numeric',
    ];

    protected $appends = ['profile'];

    /**
     * Patient / doctor pick-lists of the prescription and dispensing forms. The repositories cache them
     * for 10 minutes, so they are dropped whenever a patient or user changes: a patient registered at
     * the front desk must be selectable by the doctor straight away.
     */
    private const LOOKUP_CACHE_KEYS = [
        'active_patients_prescription',
        'active_patients_medicine_bill',
        'active_doctors_prescription',
        'active_doctors_medicine_bill',
    ];

    public static function flushLookupCaches(): void
    {
        foreach (self::LOOKUP_CACHE_KEYS as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Boot the model and set up event listeners for cascade delete
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(fn () => static::flushLookupCaches());
        static::deleted(fn () => static::flushLookupCaches());
        static::restored(fn () => static::flushLookupCaches());

        // When a patient is being deleted, delete all related data
        static::deleting(function ($patient) {

            if (!$patient->isForceDeleting()) {
                // An archived patient can no longer be seen by a doctor, so any place still held in
                // the queue is closed (the queue pages cannot show a patient that is not there).
                $patient->queueEntries()
                    ->whereIn('status', [PatientQueue::STATUS_WAITING, PatientQueue::STATUS_IN_PROGRESS])
                    ->update([
                        'status' => PatientQueue::STATUS_CANCELLED,
                        'completed_at' => now(),
                        'updated_at' => now(),
                    ]);

                // If soft deleting, we also soft delete the user
                if ($patient->user) {
                    $patient->user->delete();
                }
                return;
            }

            // Force Deleting cascade operations:
            // Delete all patient queue entries
            $patient->queueEntries()->delete();

            // Delete all prescriptions
            $patient->prescriptions()->delete();

            // Delete all medicine bills
            $patient->medicineBills()->delete();

            // Delete all document issuances (consultation forms, medical certificates)
            $patient->documentIssuances()->get()->each(function ($document) {
                // Force delete the document
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
                $patient->user->forceDelete();
            }

            // Delete patient's address if exists
            if ($patient->address) {
                $patient->address->delete();
            }

            // Delete media files (profile images, etc.)
            $patient->clearMediaCollection(self::PROFILE);
        });

        static::restoring(function ($patient) {
            // Restore user when patient is restored
            if ($patient->user()->withTrashed()->first()) {
                $patient->user()->withTrashed()->first()->restore();
            }
        });
    }

    public static function generatePatientUniqueId(): string
    {
        do {
            // Datetime-based fallback ID reduces collision risk when university ID is not provided.
            $patientUniqueId = 'PT' . now()->format('YmdHisv') . Str::upper(Str::random(3));
        } while (self::wherePatientUniqueId($patientUniqueId)->exists());

        return $patientUniqueId;
    }

    public function getProfileAttribute(): string
    {
        // Guard: only use media if already eager-loaded to avoid N+1 queries
        if ($this->relationLoaded('media')) {
            /** @var Media $media */
            $media = $this->getMedia(self::PROFILE)->first();

            if ($media) {
                return normalizeLocalUrl($media->getFullUrl()) ?? $media->getFullUrl();
            }
        }

        $gender = $this->relationLoaded('user') ? $this->user?->gender : null;
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

    public function patientType(): BelongsTo
    {
        return $this->belongsTo(PatientType::class, 'patient_type_id');
    }

    public function insuranceProvider(): BelongsTo
    {
        return $this->belongsTo(InsuranceProvider::class, 'insurance_provider_id');
    }

    public function requestDocuments(): HasMany
    {
        return $this->documentIssuances();
    }

    public function documentIssuances(): HasMany
    {
        return $this->hasMany(DocumentIssuance::class, 'user_id', 'user_id');
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

    public function dispenseRecords(): HasMany
    {
        return $this->hasMany(DispenseRecord::class);
    }

    /** @deprecated Use dispenseRecords() */
    public function medicineBills(): HasMany
    {
        return $this->dispenseRecords();
    }
}
