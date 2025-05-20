<?php

namespace App\Models;

use Database\Factories\RoleFactory;
use Eloquent as Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role as roleModal;

/**
 * App\Models\Role
 *
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property int $is_default
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\RoleFactory factory($count = null, $state = [])
 * @method static Builder|Role newModelQuery()
 * @method static Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role permission($permissions)
 * @method static Builder|Role query()
 * @method static Builder|Role whereCreatedAt($value)
 * @method static Builder|Role whereDisplayName($value)
 * @method static Builder|Role whereGuardName($value)
 * @method static Builder|Role whereId($value)
 * @method static Builder|Role whereIsDefault($value)
 * @method static Builder|Role whereName($value)
 * @method static Builder|Role whereUpdatedAt($value)
 * @mixin Model
 */
class Role extends roleModal
{
    use HasFactory;

    protected $table = 'roles';

    public static $rules = [
        'display_name' => 'required|unique:roles,display_name',
        'permission_id' => 'required',
    ];
}
