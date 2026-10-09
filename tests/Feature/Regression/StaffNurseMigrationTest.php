<?php

namespace Tests\Feature\Regression;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Plan Phase 3.1: the data migration that merges the `nurse` role into "Staff (Nurse)".
 *
 * These tests start from what `migrate` leaves before any seeding: the older migration
 * `2026_04_22_094900_add_nurse_role_and_permissions` has already created the `nurse` role, nothing else exists. They
 * describe the databases the migration meets that the access-control tests cannot: a fresh install (the seeder creates
 * "Staff (Nurse)" later) and an old clinic database that has the roles but a different label.
 */
class StaffNurseMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): object
    {
        return require database_path('migrations/2026_10_09_170000_unify_staff_nurse_access.php');
    }

    private function insertRole(string $name, ?string $label): int
    {
        return DB::table('roles')->insertGetId([
            'name' => $name, 'display_name' => $label, 'guard_name' => 'web', 'is_default' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function roleId(string $name): ?int
    {
        $id = DB::table('roles')->where('name', $name)->where('guard_name', 'web')->value('id');

        return $id === null ? null : (int) $id;
    }

    private function insertUser(string $email): int
    {
        return DB::table('users')->insertGetId([
            'first_name' => 'Some', 'last_name' => 'One', 'email' => $email, 'password' => 'x', 'type' => User::STAFF,
            'status' => 1, 'gender' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_a_fresh_install_is_left_alone_for_the_seeder(): void
    {
        $this->assertSame(['nurse'], DB::table('roles')->pluck('name')->all(), 'a migrated, unseeded database holds only the older nurse role');

        $this->migration()->up();

        // Creating "staff" here would make DefaultRoleSeeder and every test that creates the role fail with "already exists".
        $this->assertSame(['nurse'], DB::table('roles')->pluck('name')->all());
        $this->assertSame(0, DB::table('model_has_roles')->count());
    }

    public function test_an_old_staff_label_becomes_staff_nurse_and_nurse_accounts_move_over(): void
    {
        $staff = $this->insertRole('staff', 'Staff');
        $nurse = $this->roleId('nurse');
        $nurseUser = $this->insertUser('old.nurse@test.local');
        $staffUser = $this->insertUser('old.staff@test.local');
        $userType = (new User())->getMorphClass();
        DB::table('model_has_roles')->insert([
            ['role_id' => $nurse, 'model_type' => $userType, 'model_id' => $nurseUser],
            ['role_id' => $staff, 'model_type' => $userType, 'model_id' => $staffUser],
        ]);

        $this->migration()->up();

        $this->assertSame('Staff (Nurse)', DB::table('roles')->where('id', $staff)->value('display_name'));
        $this->assertSame(0, DB::table('model_has_roles')->where('role_id', $nurse)->count(), 'nobody holds the retired role');
        $this->assertSame(
            [$nurseUser, $staffUser],
            DB::table('model_has_roles')->where('role_id', $staff)->orderBy('model_id')->pluck('model_id')->map(fn ($id) => (int) $id)->all()
        );
        $this->assertSame(1, (int) DB::table('roles')->where('id', $nurse)->value('is_default'), 'the retired role cannot be deleted from the screen');
    }

    public function test_permissions_that_have_no_label_get_one_so_the_roles_screen_is_not_blank(): void
    {
        // The clinic database has manage_patients / manage_request_documents with an empty label (they were created by an
        // older migration that did not supply one): the Roles screen showed empty badges and empty check boxes.
        DB::table('permissions')->whereIn('name', ['manage_patients', 'manage_request_documents'])->update(['display_name' => '']);
        DB::table('permissions')->where('name', 'manage_medicines')->update(['display_name' => 'Medicines and dispensing']);

        $this->migration()->up();
        $this->migration()->up(); // running it twice changes nothing more

        $labels = DB::table('permissions')->pluck('display_name', 'name');
        $this->assertSame('Manage Patients', $labels['manage_patients']);
        $this->assertSame('Manage Request Documents', $labels['manage_request_documents']);
        $this->assertSame('Medicines and dispensing', $labels['manage_medicines'], 'a label somebody chose is never overwritten');
        $this->assertSame([], $labels->filter(fn ($label) => trim((string) $label) === '')->keys()->all());
    }

    public function test_nurse_accounts_are_not_lost_when_the_staff_role_is_missing(): void
    {
        $nurse = $this->roleId('nurse');
        $nurseUser = $this->insertUser('lonely.nurse@test.local');
        DB::table('model_has_roles')->insert([
            ['role_id' => $nurse, 'model_type' => (new User())->getMorphClass(), 'model_id' => $nurseUser],
        ]);

        $this->migration()->up();

        $staff = DB::table('roles')->where('name', 'staff')->first();
        $this->assertNotNull($staff, 'the accounts need a role to move to');
        $this->assertSame('Staff (Nurse)', $staff->display_name);
        $this->assertSame([$nurseUser], DB::table('model_has_roles')->where('role_id', $staff->id)->pluck('model_id')->map(fn ($id) => (int) $id)->all());
    }
}
