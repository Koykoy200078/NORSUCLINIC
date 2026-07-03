<?php

namespace App\Repositories;

use App\Models\Permission;
use App\Models\Role;

/**
 * Class RoleRepository
 *
 * @version August 5, 2021, 10:43 am UTC
 */
class RoleRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
    ];

    /**
     * Return searchable fields
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Role::class;
    }

    /**
     * @return mixed
     */
    public function getPermissions()
    {
        $permissions['permissions'] = Permission::toBase()->where('name', '!=', 'manage_admin_dashboard')->get();
        $permissions['count'] = Permission::count();

        return $permissions;
    }

    public function store($input): Role
    {
        $displayName = strtolower($input['display_name']);
        $input['name'] = str_replace(' ', '_', $displayName);

        // Allow-list the columns — Spatie's base Role has $guarded=[] so passing the raw
        // request would let a caller inject is_default=1 (making the role undeletable) or a
        // bogus guard_name. CRUD-MA.
        /** @var Role $role */
        $role = Role::create([
            'name' => $input['name'],
            'display_name' => $input['display_name'] ?? null,
            'guard_name' => 'web',
        ]);

        if (isset($input['permission_id']) && ! empty($input['permission_id'])) {
            $role->permissions()->sync($input['permission_id']);
        }

        return $role;
    }

    public function update($input, $id): Role
    {
        $displayName = strtolower($input['display_name']);
        $str = str_replace(' ', '_', $displayName);

        $role = Role::findById($id);
        /** @var Role $role */
        $role->update([
            'name' => $str,
            'display_name' => $input['display_name'],
        ]);
        if (isset($input['permission_id']) && ! empty($input['permission_id'])) {
            $role->permissions()->sync($input['permission_id']);
        }

        return $role;
    }
}
