<?php

namespace App\Models;

use Database\Factories\StaffFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\Staff
 *
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\StaffFactory factory($count = null, $state = [])
 * @method static Builder|Staff newModelQuery()
 * @method static Builder|Staff newQuery()
 * @method static Builder|Staff permission($permissions)
 * @method static Builder|Staff query()
 * @method static Builder|Staff role($roles, $guard = null)
 * @mixin \Eloquent
 */
class Staff extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasRoles;

    protected $table = 'staff';

    const PROFILE = 'profile';

    public $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'gender',
        'role',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'first_name' => 'string',
        'last_name' => 'string',
        'email' => 'string',
        'phone_number' => 'string',
        'password' => 'string',
        'gender' => 'string',
        'role' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|same:password_confirmation|min:6',
        'contact' => 'nullable|unique:users,contact',
        'gender' => 'required',
        'role' => 'required',
    ];

    /**
     * Relationship with the User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
