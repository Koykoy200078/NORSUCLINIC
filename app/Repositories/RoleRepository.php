<?php

namespace App\Repositories;

use App\Models\Permission;
use App\Models\Role;
use App\Support\ModuleAccess;
use Illuminate\Support\Facades\DB;

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
    public function getPermissions(?Role $role = null): array
    {
        $permissions['permissions'] = Permission::whereIn('name', ModuleAccess::applicablePermissions($role->name ?? 'custom'))->get();
        $permissions['count'] = $permissions['permissions']->count();

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

        $role->syncPermissions($this->filteredPermissions($input, $role));

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
        abort_if($role->isReadOnly(), 403);
        DB::transaction(function () use ($input, $role): void {
            if ($role->display_name !== $input['display_name']) {
                $role->update(['display_name' => $input['display_name']]);
            }
            $role->syncPermissions($this->filteredPermissions($input, $role));
        });

        return $role;
    }

    private function filteredPermissions(array $input, Role $role): array
    {
        return Permission::whereIn('id', $input['permission_id'] ?? [])
            ->whereIn('name', ModuleAccess::applicablePermissions($role->name))->pluck('name')->all();
    }
}
