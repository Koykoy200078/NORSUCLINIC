<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $first_name
 * @property string|null $middle_name
 * @property string $last_name
 * @property string|null $email
 * @property string|null $contact
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_no
 * @property string|null $dob
 * @property int|null $gender
 * @property bool $status
 * @property string|null $language
 * @property Carbon|null $email_verified_at
 * @property string|null $password
 * @property int|null $type
 * @property string|null $blood_type
 * @property string|null $country_code
 * @property int|null $campus_id
 * @property int|null $college_id
 * @property int|null $course_id
 * @property int|null $year_level_id
 * @property int|null $vaccination_id
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property bool $email_notification
 * @property string $time_zone
 * @property bool $dark_mode
 * @property-read \App\Models\Address|null $address
 * @property-read \App\Models\Campus|null $campus
 * @property-read \App\Models\College|null $college
 * @property-read \App\Models\Course|null $course
 * @property-read \App\Models\Doctor|null $doctor
 * @property-read string $full_name
 * @property-read string $profile_image
 * @property-read mixed $role_display_name
 * @property-read mixed $role_name
 * @property-read MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Patient|null $patient
 * @property-read Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, \App\Models\Qualification> $qualifications
 * @property-read int|null $qualifications_count
 * @property-read Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\Staff|null $staff
 * @property-read \App\Models\Vaccination|null $vaccination
 * @property-read \App\Models\YearLevel|null $yearLevel
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User permission($permissions)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null)
 * @method static Builder|User whereBloodType($value)
 * @method static Builder|User whereCampusId($value)
 * @method static Builder|User whereCollegeId($value)
 * @method static Builder|User whereContact($value)
 * @method static Builder|User whereCountryCode($value)
 * @method static Builder|User whereCourseId($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereDarkMode($value)
 * @method static Builder|User whereDob($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailNotification($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereEmergencyContactName($value)
 * @method static Builder|User whereEmergencyContactNo($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereGender($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLanguage($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User whereMiddleName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereStatus($value)
 * @method static Builder|User whereTimeZone($value)
 * @method static Builder|User whereType($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereVaccinationId($value)
 * @method static Builder|User whereYearLevelId($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, InteractsWithMedia, HasRoles, Impersonate, HasPermissions;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'contact',
        'emergency_contact_name',
        'emergency_contact_no',
        'emergency_relationship',
        'dob',
        'gender',
        'status',
        'password',
        'language',
        'blood_type',
        'type',
        'country_code',
        'email_verified_at',
        'email_notification',
        'time_zone',
        'dark_mode',
        'campus_id',
        'college_id',
        'course_id',
        'year_level_id',
        'vaccination_id',
        'office_id',
        'department_id',
    ];

    const LANGUAGES = [
        'en' => 'English',
    ];

    const LANGUAGES_IMAGE = [
        'en' => 'web/media/flags/philippines.svg',
    ];

    const PROFILE = 'profile';

    const ADMIN = 1;

    const DOCTOR = 2;

    const PATIENT = 3;

    const STAFF = 4;

    const TYPE = [
        self::ADMIN => 'Admin',
        self::DOCTOR => 'Doctor',
        self::PATIENT => 'Patient',
        self::STAFF => 'Staff',
    ];

    const ALL = 2;

    const ACTIVE = 1;

    const DEACTIVATE = 0;

    const STATUS = [
        self::DEACTIVATE => 'Deactivate',
        self::ACTIVE => 'Active',
        self::ALL => 'All',
    ];

    const TIME_ZONE_ARRAY = [
        'Asia/Manila' => 'Asia/Manila',
    ];

    // protected $with = ['media', 'roles'];

    protected $appends = ['full_name', 'profile_image', 'role_name', 'role_display_name'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    const MALE = 1;

    const FEMALE = 2;

    const GENDER = [
        self::MALE => 'Male',
        self::FEMALE => 'Female',
    ];

    public static $rules = [
        'first_name' => 'required',
        'middle_name' => 'nullable',
        'last_name' => 'required',
        'email' => 'nullable|email|unique:users,email|regex:/(.*)@(.*)\.(.*)/',
        'contact' => 'nullable|unique:users,contact',
        'password' => 'nullable|same:password_confirmation|min:6',
        'dob' => 'nullable|date',
        'experience' => 'nullable|numeric',
        'specializations' => 'required',
        'gender' => 'required',
        'status' => 'nullable',
        'postal_code' => 'nullable',
        'profile' => 'nullable|mimes:jpeg,png,jpg|max:2000',

        'emergency_contact_name' => 'nullable',
        'emergency_contact_no' => 'nullable',
        'emergency_relationship' => 'nullable|string',
        'campus_id' => 'nullable',
        'college_id' => 'nullable',
        'course_id' => 'nullable',
        'year_level_id' => 'nullable',
        'vaccination_id' => 'nullable',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'first_name' => 'string',
        'last_name' => 'string',
        'email' => 'string',
        'contact' => 'string',
        'dob' => 'string',
        'gender' => 'integer',
        'status' => 'boolean',
        'password' => 'string',
        'language' => 'string',
        'blood_type' => 'string',
        'type' => 'integer',
        'country_code' => 'string',
        'email_notification' => 'boolean',
        'time_zone' => 'string',
        'dark_mode' => 'boolean',

        'middle_name' => 'string',
        'emergency_contact_name' => 'string',
        'emergency_contact_no' => 'string',
        'emergency_relationship' => 'string',
        'campus_id' => 'integer',
        'college_id' => 'integer',
        'course_id' => 'integer',
        'year_level_id' => 'integer',
        'vaccination_id' => 'integer',
    ];

    public function getProfileImageAttribute(): string
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

        $gender = $this->gender;
        if ($gender == self::FEMALE) {
            return asset('web/media/avatars/female.png');
        }

        return asset('web/media/avatars/male.png');
    }

    public function getRoleNameAttribute()
    {
        $role = $this->roles->first();

        if (! empty($role)) {
            return $role->display_name;
        }
    }

    public function getRoleDisplayNameAttribute()
    {
        $role = $this->roles->first();

        if (! empty($role)) {
            return $role->name;
        }
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'owner');
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class, 'user_id');
    }

    public function qualifications(): HasMany
    {
        return $this->hasMany(Qualification::class, 'user_id');
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class, 'user_id');
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function yearLevel()
    {
        return $this->belongsTo(YearLevel::class, 'year_level_id');
    }

    public function vaccination()
    {
        return $this->belongsTo(Vaccination::class, 'vaccination_id');
    }
}
