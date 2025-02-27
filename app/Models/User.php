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
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, InteractsWithMedia, HasRoles, Impersonate;

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
        'vaccination_id'
    ];

    const LANGUAGES = [
        'en' => 'English',
    ];

    const LANGUAGES_IMAGE = [
        'en' => 'web/media/flags/united-states.svg',
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

    const DEACTIVE = 0;

    const STATUS = [
        self::DEACTIVE => 'Deactive',
        self::ACTIVE => 'Active',
        self::ALL => 'All',
    ];

    const TIME_ZONE_ARRAY = [
        'Asia/Manila',
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
        'emergency_contact_name' => 'nullable',
        'emergency_contact_no' => 'nullable',
        'last_name' => 'required',
        'email' => 'required|email|unique:users,email|regex:/(.*)@(.*)\.(.*)/',
        'contact' => 'nullable|unique:users,contact',
        'password' => 'required|same:password_confirmation|min:6',
        'dob' => 'nullable|date',
        'experience' => 'nullable|numeric',
        'specializations' => 'required',
        'gender' => 'required',
        'status' => 'nullable',
        'postal_code' => 'nullable',
        'profile' => 'nullable|mimes:jpeg,png,jpg|max:2000',

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
        'middle_name' => 'string',
        'emergency_contact_name' => 'string',
        'emergency_contact_no' => 'string',
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

        return $this->gender == self::FEMALE
            ? asset('web/media/avatars/female.png')
            : asset('web/media/avatars/male.png');
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

    public function gCredentials(): HasOne
    {
        return $this->hasOne(GoogleCalendarIntegration::class, 'user_id');
    }
}
