<?php

namespace Tests\Feature\Regression;

use App\Livewire\RoleTable;
use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

class RolesPassATest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    public function test_creating_a_role_refuses_a_label_that_collides_with_an_internal_identifier(): void
    {
        $this->actingAs($this->makeAdmin());
        Role::create(['name' => 'custom_role', 'display_name' => 'Custom Role', 'guard_name' => 'web']);
        $before = Role::count();

        foreach (['Clinic_Admin', 'CUSTOM_ROLE'] as $label) {
            $response = $this->postJson(route('roles.store'), ['display_name' => $label])->assertUnprocessable();
            $this->assertStringContainsStringIgnoringCase('already', $response->getContent());
            $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
        }
        $this->assertSame($before, Role::count());
    }

    public function test_role_forms_validate_permissions_and_allow_only_their_own_fields(): void
    {
        $this->actingAs($this->makeAdmin());
        foreach ([['display_name' => str_repeat('a', 192)], ['permission_id' => 'bad'], ['permission_id' => [999999]]] as $invalid) {
            $this->postJson(route('roles.store'), array_merge(['display_name' => 'New Label'], $invalid))->assertUnprocessable();
        }
        $permission = Permission::whereName('manage_patients')->firstOrFail();
        $this->post(route('roles.store'), [
            'display_name' => 'Custom Role', 'permission_id' => [$permission->id], 'name' => 'clinic_admin', 'guard_name' => 'api', 'is_default' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect(route('roles.index'));
        $role = Role::whereName('custom_role')->firstOrFail();
        $this->assertSame('web', $role->guard_name);
        $this->assertSame(0, (int) $role->is_default);
        $this->assertSame(['manage_patients'], $role->permissions()->pluck('name')->all());
        $this->put(route('roles.update', $role), ['display_name' => 'Renamed', 'permission_id' => []])->assertSessionHasNoErrors();
        $this->assertSame('custom_role', $role->fresh()->name);
        $this->assertSame(0, $role->permissions()->count());
        $this->assertSame(2, ActivityLog::where('action', 'roles_changed')->count());
    }

    public function test_only_an_admin_can_manage_roles_and_a_role_with_archived_members_cannot_be_deleted(): void
    {
        $admin = $this->makeAdmin();
        $role = Role::create(['name' => 'custom', 'display_name' => 'Custom', 'guard_name' => 'web']);
        $member = $this->makeNurse();
        $member->assignRole($role);
        $member->delete();
        foreach ([$this->makeNurse(), $this->makeDoctor(), $this->makePatient()->user] as $user) {
            $this->actingAs($user)->get(route('roles.index'))->assertForbidden();
            $this->postJson(route('roles.store'), ['display_name' => 'Forbidden'])->assertForbidden();
            $this->putJson(route('roles.update', $role), ['display_name' => 'Forbidden'])->assertForbidden();
            $this->deleteJson(route('roles.destroy', $role))->assertForbidden();
        }
        $this->actingAs($admin)->deleteJson(route('roles.destroy', $role))->assertUnprocessable();
        $this->assertNotNull($role->fresh());
        $member->roles()->detach($role);
        $this->deleteJson(route('roles.destroy', $role))->assertOk();
        $this->assertNull($role->fresh());
    }

    public function test_the_roles_list_searches_escaped_labels_and_orders_ties_by_id(): void
    {
        $this->actingAs($this->makeAdmin());
        $created = [];
        for ($i = 0; $i < 12; $i++) {
            $created[] = Role::create(['name' => 'tie_'.$i, 'display_name' => '<b>Tied '.$i.'</b>', 'guard_name' => 'web', 'created_at' => '2026-10-01 12:00:00'])->id;
        }
        $table = Livewire::withoutLazyLoading()->test(RoleTable::class)->set('search', 'Tied');
        $table->assertDontSeeHtml('<b>Tied');
        $this->assertSame(array_slice(array_reverse($created), 0, 10), $table->instance()->getRows()->pluck('id')->all());
    }
}
