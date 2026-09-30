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
        $role = Role::findById($id);
        /** @var Role $role */

        // Only the human-readable label is editable. The internal `name` is an identifier the code
        // authorises against (role:clinic_admin, hasRole('doctor'), hasRole('nurse') ...). It used to
        // be re-derived from the label on every save, so renaming "Clinic Admin" to "Clinic
        // Administrator" changed the name to clinic_administrator and locked every admin out with
        // 403, including from the Roles screen needed to undo it. P2-H1.
        $role->update([
            'display_name' => $input['display_name'],
        ]);

        // The clinic_admin role always keeps every permission, so a bad edit cannot lock the
        // administrators out of the screens needed to repair it.
        if ($role->name !== 'clinic_admin' && isset($input['permission_id']) && ! empty($input['permission_id'])) {
            $role->permissions()->sync($input['permission_id']);
        }

        return $role;
    }
}
