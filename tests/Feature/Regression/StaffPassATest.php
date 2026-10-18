<?php

namespace Tests\Feature\Regression;

use App\Livewire\StaffTable;
use App\Models\ActivityLog;
use App\Models\DocumentIssuance;
use App\Models\LabRequest;
use App\Models\MedicineTransaction;
use App\Models\PatientQueue;
use App\Models\Prescription;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan row 5.12, Settings > Staff (Pass A): who may manage staff accounts, what the forms accept, that a save is
 * all-or-nothing, and what happens to an archived account.
 */
class StaffPassATest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function valid(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Nora', 'last_name' => 'Nurse', 'email' => 'nora.nurse@test.local', 'employee_id' => 'EMP-5120',
            'contact' => '9171234567', 'password' => 'secret12', 'password_confirmation' => 'secret12', 'gender' => 2,
        ], $overrides);
    }

    /** The app shows the first validation message (not one per field): the refusal must be about this field. */
    private function assertRefused(\Illuminate\Testing\TestResponse $response, string $aboutField): void
    {
        $response->assertSessionHasErrors();
        $this->assertStringContainsStringIgnoringCase($aboutField, (string) session('errors')->first(), "the refusal is not about {$aboutField}");
    }

    // ------------------------------------------------------------------ who may manage staff

    public function test_only_the_administrator_manages_staff_accounts(): void
    {
        $target = $this->makeStaff();
        $strangers = ['guest' => null, 'staff' => $this->makeNurse(['email' => 'n@test.local']), 'doctor' => $this->makeDoctor(), 'patient' => $this->makePatient()->user];
        $before = User::count();

        foreach ($strangers as $who => $user) {
            if ($user) {
                $this->actingAs($user);
            }
            $requests = [
                ['get', route('staffs.index')], ['get', route('staffs.create')], ['post', route('staffs.store'), $this->valid()],
                ['get', route('staffs.show', $target->id)], ['get', route('staffs.edit', $target->id)],
                ['put', route('staffs.update', $target->id), $this->valid()], ['delete', route('staffs.destroy', $target->id)],
                ['post', route('staffs.reset.password', $target->id)],
            ];
            foreach ($requests as $request) {
                [$method, $url] = $request;
                $response = $this->{$method}($url, $request[2] ?? []);
                $this->assertTrue(
                    $response->isRedirect() || $response->getStatusCode() === 403,
                    "{$who} reached {$method} {$url} (status {$response->getStatusCode()})"
                );
                $this->assertNotSame(200, $response->getStatusCode(), "{$who} {$method} {$url}");
            }
        }

        $this->assertSame($before, User::count(), 'nobody created an account');
        $this->assertNull($target->fresh()->archived_at, 'nobody archived the account');
        $this->assertFalse(Hash::check('123456', $target->fresh()->password), 'nobody reset the password');
    }

    // ------------------------------------------------------------------ what the forms accept

    public function test_gender_must_be_one_of_the_two_values_of_the_form(): void
    {
        $admin = $this->makeAdmin();
        $before = User::count();

        foreach (['abc', '9', '0', '-1', '1.5'] as $bad) {
            $this->assertRefused($this->actingAs($admin)->post(route('staffs.store'), $this->valid(['gender' => $bad])), 'gender');
        }
        $this->assertSame($before, User::count(), 'a bad gender saves nobody (it used to reach the database)');

        $staff = $this->makeStaff();
        $this->assertRefused($this->actingAs($admin)->put(route('staffs.update', $staff->id), $this->valid(['email' => $staff->email, 'gender' => 'abc'])), 'gender');

        $this->actingAs($admin)->post(route('staffs.store'), $this->valid(['gender' => 1]))->assertSessionHasNoErrors();
        $this->assertSame(1, (int) User::where('email', 'nora.nurse@test.local')->value('gender'));
    }

    public function test_every_field_is_checked_against_its_column(): void
    {
        $admin = $this->makeAdmin();
        $before = User::count();

        $cases = [
            'first_name' => str_repeat('a', 192), 'last_name' => str_repeat('a', 192), 'email' => str_repeat('a', 180) . '@test.local',
            'employee_id' => str_repeat('9', 101), 'password' => 'abc', 'contact' => '123',
        ];
        $words = ['contact' => 'Philippine'];
        foreach ($cases as $field => $value) {
            $data = $this->valid([$field => $value]);
            if ($field === 'password') {
                $data['password_confirmation'] = $value;
            }
            $this->assertRefused($this->actingAs($admin)->post(route('staffs.store'), $data), $words[$field] ?? str_replace('_', ' ', $field));
        }
        // a password that does not match its confirmation
        $this->assertRefused($this->actingAs($admin)->post(route('staffs.store'), $this->valid(['password_confirmation' => 'other-one'])), 'password');
        $this->assertSame($before, User::count());

        // the longest values the columns hold are accepted
        $this->actingAs($admin)->post(route('staffs.store'), $this->valid([
            'first_name' => str_repeat('a', 191), 'last_name' => str_repeat('b', 191), 'employee_id' => str_repeat('9', 100),
        ]))->assertSessionHasNoErrors();
        $this->assertSame($before + 1, User::count());
    }

    public function test_an_email_or_employee_id_already_in_use_is_refused_whatever_its_case(): void
    {
        $admin = $this->makeAdmin();
        $existing = $this->makeStaff('clinic_head', 'front_desk', ['email' => 'taken@test.local', 'employee_id' => 'EMP-TAKEN']);
        $before = User::count();

        $this->assertRefused($this->actingAs($admin)->post(route('staffs.store'), $this->valid(['email' => 'TAKEN@Test.Local'])), 'email');
        $this->assertRefused($this->actingAs($admin)->post(route('staffs.store'), $this->valid(['employee_id' => 'EMP-TAKEN'])), 'employee id');
        $this->assertSame($before, User::count());

        // its own values on its own edit form are fine
        $this->actingAs($admin)->put(route('staffs.update', $existing->id), $this->valid(['email' => 'taken@test.local', 'employee_id' => 'EMP-TAKEN']))
            ->assertSessionHasNoErrors();
    }

    // ------------------------------------------------------------------ saves are all-or-nothing

    public function test_a_staff_account_is_not_left_behind_when_its_role_cannot_be_given(): void
    {
        $admin = $this->makeAdmin();
        Role::where('name', 'staff')->delete();
        $before = User::count();

        $this->actingAs($admin)->post(route('staffs.store'), $this->valid());

        $this->assertSame($before, User::count(), 'no half-created account without a role');
    }

    public function test_leaving_the_password_blank_keeps_it_and_an_edit_always_ends_with_the_staff_role_only(): void
    {
        $admin = $this->makeAdmin();
        $staff = $this->makeStaff();
        $staff->assignRole('doctor'); // a leftover second role
        $hash = $staff->fresh()->password;

        $this->actingAs($admin)->put(route('staffs.update', $staff->id), $this->valid([
            'email' => $staff->email, 'password' => '', 'password_confirmation' => '', 'first_name' => 'Renamed',
        ]))->assertSessionHasNoErrors();

        $fresh = $staff->fresh();
        $this->assertSame('Renamed', $fresh->first_name);
        $this->assertSame($hash, $fresh->password, 'a blank password keeps the old one');
        $this->assertSame(['staff'], $fresh->getRoleNames()->all());
        $this->assertSame(User::STAFF, (int) $fresh->type);
    }

    // ------------------------------------------------------------------ the list, the page and what archiving does

    public function test_the_list_shows_active_staff_only(): void
    {
        $admin = $this->makeAdmin();
        $nurse = $this->makeNurse(['email' => 'shown@test.local', 'first_name' => 'Shown']);
        $archived = $this->makeNurse(['email' => 'gone@test.local', 'first_name' => 'Gone']);
        $archived->delete();
        $this->makeDoctor(['first_name' => 'Docname']);
        $this->makePatient();

        $this->actingAs($admin);
        $html = Livewire::withoutLazyLoading()->test(StaffTable::class)->html();

        $this->assertStringContainsString('Shown', $html);
        $this->assertStringNotContainsString('Gone', $html, 'an archived account is not listed');
        $this->assertStringNotContainsString('Docname', $html, 'a doctor is not staff');
        $this->assertSame([$nurse->id], User::where('type', User::STAFF)->whereNull('archived_at')->pluck('id')->all());
    }

    public function test_a_name_typed_with_html_is_shown_as_text(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->post(route('staffs.store'), $this->valid(['first_name' => '<b>Bold</b>']));
        $staff = User::where('email', 'nora.nurse@test.local')->first();

        $this->assertNotNull($staff);
        $show = $this->get(route('staffs.show', $staff->id))->assertOk()->getContent();
        $edit = $this->get(route('staffs.edit', $staff->id))->assertOk()->getContent();

        foreach ([$show, $edit] as $html) {
            $this->assertStringNotContainsString('<b>Bold</b>', $html);
        }
    }

    public function test_an_archived_staff_member_is_signed_out_at_once_and_cannot_be_managed(): void
    {
        $admin = $this->makeAdmin();
        $staff = $this->makeNurse(['email' => 'leaving@test.local']);

        $this->actingAs($staff)->get(route('staff.dashboard'))->assertOk();

        $this->actingAs($admin)->deleteJson(route('staffs.destroy', $staff->id))->assertOk();
        $this->assertNotNull($staff->fresh()->archived_at, 'archived, not deleted');
        $this->assertSame(1, User::withTrashed()->where('id', $staff->id)->count());

        // the account is gone for the sign-in session too (the user provider no longer finds it), and for the sign-in form
        $this->assertNull(auth()->getProvider()->retrieveById($staff->id), 'an open session of an archived account must not resolve to a user');
        $this->post(route('logout'));
        $this->post('/login', ['email' => 'leaving@test.local', 'password' => 'password']);
        $this->assertGuest();

        $this->actingAs($admin);
        foreach ([['get', 'staffs.edit'], ['get', 'staffs.show'], ['delete', 'staffs.destroy'], ['post', 'staffs.reset.password']] as [$method, $name]) {
            $this->{$method}(route($name, $staff->id))->assertNotFound();
        }
    }

    public function test_a_reset_password_is_the_default_one_for_that_account_only_and_is_kept_on_the_dashboard(): void
    {
        $admin = $this->makeAdmin();
        $staff = $this->makeNurse(['email' => 'reset@test.local']);
        $other = $this->makeNurse(['email' => 'other@test.local']);
        $otherHash = $other->fresh()->password;

        $this->actingAs($admin)->postJson(route('staffs.reset.password', $staff->id))->assertOk()->assertJsonPath('success', true);

        $this->assertTrue(Hash::check('123456', $staff->fresh()->password));
        $this->assertSame($otherHash, $other->fresh()->password);

        // the next page of that nurse is the dashboard until the password is changed (the default-password gate)
        $this->actingAs($staff->fresh());
        $this->get(route('staff.patient-queue.index'))->assertRedirect();
    }

    public function test_a_new_staff_account_can_sign_in_with_the_password_it_was_given(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->post(route('staffs.store'), $this->valid(['password' => 'Corr3ct-h0rse', 'password_confirmation' => 'Corr3ct-h0rse']))
            ->assertRedirect(route('staffs.index'));
        $staff = User::where('email', 'nora.nurse@test.local')->firstOrFail();

        $this->assertSame(['staff'], $staff->getRoleNames()->all());
        $this->assertNotNull($staff->email_verified_at, 'the administrator vouches for the e-mail address');

        $this->post(route('logout'));
        $this->post('/login', ['email' => 'nora.nurse@test.local', 'password' => 'Corr3ct-h0rse'])->assertRedirect();
        $this->assertAuthenticatedAs($staff);
    }

    public function test_every_change_to_a_staff_account_leaves_a_line_in_the_trail_without_a_password(): void
    {
        $admin = $this->makeAdmin();
        $staff = $this->makeNurse(['email' => 'audited@test.local']);
        $this->actingAs($admin);

        $this->post(route('staffs.store'), $this->valid(['password' => 'Sup3r-s3cret', 'password_confirmation' => 'Sup3r-s3cret']));
        $this->put(route('staffs.update', $staff->id), $this->valid(['email' => 'audited@test.local', 'employee_id' => 'EMP-EDITED', 'contact' => '9179876543', 'first_name' => 'Edited']));
        $this->postJson(route('staffs.reset.password', $staff->id));
        $this->deleteJson(route('staffs.destroy', $staff->id));

        $rows = \App\Models\ActivityLog::whereIn('action', ['account_changed', 'password_reset'])->get();
        $this->assertSame(3, $rows->where('action', 'account_changed')->count(), 'create, edit and archive');
        $this->assertSame(1, $rows->where('action', 'password_reset')->count());
        foreach ($rows as $row) {
            $this->assertSame($admin->id, $row->user_id);
            $encoded = json_encode($row->toArray());
            foreach (['Sup3r-s3cret', '123456', 'Corr3ct'] as $secret) {
                $this->assertStringNotContainsString($secret, $encoded);
            }
        }
    }

    public function test_records_keep_the_name_of_the_staff_member_who_made_them_after_the_account_is_archived(): void
    {
        $admin = $this->makeAdmin();
        $nurse = $this->makeNurse(['email' => 'author@test.local', 'first_name' => 'Author', 'last_name' => 'Nurse']);
        $patient = $this->makePatient();
        PatientQueue::create(['patient_id' => $patient->id, 'added_by' => $nurse->id, 'status' => PatientQueue::STATUS_WAITING, 'is_priority' => false]);

        $this->actingAs($admin)->deleteJson(route('staffs.destroy', $nurse->id))->assertOk();
        $this->assertNull(User::find($nurse->id), 'archived: hidden from every normal lookup');

        // the people a record names stay the people it names (the dispensing report, the visits report, the queue and the
        // lab request page print them: they used to show "N/A" / "System" / a dash once the account was archived)
        $relations = [
            [new PatientQueue(), 'added_by', 'addedBy'],
            [new DocumentIssuance(), 'document_creator_id', 'creator'],
            [new DocumentIssuance(), 'nursing_incharged_id', 'nursingInCharge'],
            [new LabRequest(), 'document_creator_id', 'creator'],
            [new Prescription(), 'dispensed_by', 'dispensedBy'],
            [new MedicineTransaction(), 'user_id', 'user'],
            [new ActivityLog(), 'user_id', 'user'],
        ];
        foreach ($relations as [$model, $column, $relation]) {
            $model->forceFill([$column => $nurse->id]);
            $this->assertSame($nurse->id, $model->{$relation}?->id, class_basename($model) . '::' . $relation);
        }

        $html = $this->get(route('patient-queue.index'))->assertOk()->getContent();
        $this->assertStringContainsString('Author Nurse', $html, 'the queue still says who added the patient');
    }
}
