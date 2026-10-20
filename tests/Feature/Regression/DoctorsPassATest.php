<?php

namespace Tests\Feature\Regression;

use App\Livewire\DoctorTable;
use App\Models\ActivityLog;
use App\Models\Doctor;
use App\Models\DocumentIssuance;
use App\Models\Role;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan row 5.13, Settings > Doctors (Pass A): what the doctor forms accept, which columns a request can reach, who may
 * manage doctors, and what archiving a doctor leaves behind.
 */
class DoctorsPassATest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private Specialization $specialization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->specialization = Specialization::firstOrCreate(['name' => 'General Medicine']);
    }

    private function valid(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Dora', 'last_name' => 'Doctor', 'email' => 'dora.doctor@test.local', 'employee_id' => 'EMP-D-5130',
            'contact' => '9171234567', 'password' => 'secret12', 'password_confirmation' => 'secret12', 'gender' => 2, 'status' => 1,
            'prc_license_number' => 'PRC-5130', 'ptr_number' => 'PTR-5130', 'consultation_hours' => 'Mon-Fri 8-5',
            'specializations' => [$this->specialization->id],
        ], $overrides);
    }

    /** The app shows the first validation message (not one per field): the refusal must be about this field. */
    private function assertRefused(\Illuminate\Testing\TestResponse $response, string $aboutField, string $label = ''): void
    {
        $this->assertTrue(session()->has('errors') || $response->getStatusCode() === 422, "{$label} was accepted");
        $response->assertSessionHasErrors();
        $this->assertStringContainsStringIgnoringCase($aboutField, (string) session('errors')->first(), "the refusal is not about {$aboutField}");
    }

    private function createDoctor(array $overrides = []): Doctor
    {
        $this->actingAs($this->makeAdmin(['email' => 'admin' . uniqid() . '@test.local']))->post(route('doctors.store'), $this->valid($overrides))->assertSessionDoesntHaveErrors();

        return Doctor::whereHas('user', fn ($q) => $q->where('email', strtolower($overrides['email'] ?? 'dora.doctor@test.local')))->firstOrFail();
    }

    // ------------------------------------------------------------------ the create form

    public function test_a_new_doctor_can_sign_in_with_the_password_it_was_given(): void
    {
        $doctor = $this->createDoctor(['password' => 'Corr3ct-h0rse', 'password_confirmation' => 'Corr3ct-h0rse']);

        $this->assertSame(['doctor'], $doctor->user->getRoleNames()->all());
        $this->assertSame(User::DOCTOR, (int) $doctor->user->type);
        $this->assertSame([$this->specialization->id], $doctor->specializations()->pluck('specializations.id')->all());

        $this->post(route('logout'));
        $this->post('/login', ['email' => 'dora.doctor@test.local', 'password' => 'Corr3ct-h0rse'])->assertRedirect();
        $this->assertAuthenticatedAs($doctor->user);
    }

    public function test_the_create_form_refuses_what_the_columns_cannot_hold_or_the_account_cannot_work_without(): void
    {
        $admin = $this->makeAdmin();
        $before = [User::count(), Doctor::count()];

        $cases = [
            'no e-mail: a doctor signs in with it' => [['email' => ''], 'email'],
            'no password: the account would have an empty one' => [['password' => '', 'password_confirmation' => ''], 'password'],
            'gender' => [['gender' => 'abc'], 'gender'],
            'gender 9' => [['gender' => '9'], 'gender'],
            'specialization that does not exist' => [['specializations' => ['999999']], 'specialization'],
            'specializations not a list' => [['specializations' => 'abc'], 'specialization'],
            'negative experience' => [['experience' => '-3'], 'experience'],
            'absurd experience' => [['experience' => '500'], 'experience'],
            'address line too long' => [['address1' => str_repeat('a', 192)], 'address'],
            'a country that does not exist' => [['country_id' => '999999'], 'country'],
            'a state that does not exist' => [['state_id' => '999999'], 'state'],
            'a city that does not exist' => [['city_id' => '999999'], 'city'],
            'first name too long' => [['first_name' => str_repeat('a', 192)], 'first name'],
            'blood type nobody has' => [['blood_type' => 'zzz'], 'blood'],
        ];
        foreach ($cases as $label => [$override, $about]) {
            $this->assertRefused($this->actingAs($admin)->post(route('doctors.store'), $this->valid($override)), $about, $label);
            $this->assertSame($before, [User::count(), Doctor::count()], "{$label} saved something");
        }
    }

    public function test_only_the_fields_of_the_doctor_form_reach_the_account(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('doctors.store'), $this->valid([
            'language' => 'xx', 'time_zone' => 'Mars/Olympus', 'university_id_number' => 'UID-1', 'dark_mode' => 1, 'email_notification' => 0,
            'campus_id' => 1, 'emergency_contact_name' => 'Intruder', 'is_default' => 1, 'type' => User::ADMIN, 'email_verified_at' => null,
        ]))->assertSessionDoesntHaveErrors();

        $user = User::where('email', 'dora.doctor@test.local')->firstOrFail();
        $this->assertSame('en', $user->language, 'a doctor form cannot set the language');
        $this->assertNull($user->university_id_number);
        $this->assertNull($user->emergency_contact_name);
        $this->assertNull($user->campus_id);
        $this->assertSame(User::DOCTOR, (int) $user->type);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotEquals('Mars/Olympus', $user->time_zone);
    }

    // ------------------------------------------------------------------ the edit form

    public function test_the_edit_form_checks_the_same_fields_and_keeps_to_its_own_columns(): void
    {
        $doctor = $this->createDoctor();
        $admin = $this->makeAdmin(['email' => 'admin-edit@test.local']);
        $payload = $this->valid(['password' => null, 'password_confirmation' => null]);
        $before = $doctor->user->only(['first_name', 'last_name', 'email', 'language', 'university_id_number']);

        $cases = [
            'gender' => ['gender' => 'abc'], 'specializations' => ['specializations' => ['999999']], 'experience' => ['experience' => '-1'],
            'address' => ['address2' => str_repeat('a', 192)],
            'country' => ['country_id' => '999999'], 'blood' => ['blood_type' => 'zzz'],
            'qualification' => ['qualifications' => json_encode([['degree' => str_repeat('d', 192), 'university' => 'U', 'year' => '2001']])],
            'qualification year' => ['qualifications' => json_encode([['degree' => 'MD', 'university' => 'U', 'year' => str_repeat('9', 192)]])],
        ];
        foreach ($cases as $label => $override) {
            $response = $this->actingAs($admin)->putJson(route('doctors.update', $doctor->id), array_merge($payload, $override));
            $this->assertSame(422, $response->getStatusCode(), "{$label} was accepted (status {$response->getStatusCode()})");
            $this->assertStringNotContainsString('SQLSTATE', $response->getContent(), "{$label} reached the database");
        }
        $this->assertSame($before, $doctor->user->fresh()->only(['first_name', 'last_name', 'email', 'language', 'university_id_number']));

        // a good edit works; fields that are not on the form change nothing
        $this->actingAs($admin)->putJson(route('doctors.update', $doctor->id), array_merge($payload, [
            'first_name' => 'Renamed', 'language' => 'xx', 'university_id_number' => 'UID-9', 'password' => 'hacked-pass', 'password_confirmation' => 'hacked-pass',
            'qualifications' => json_encode([['degree' => 'MD', 'university' => 'UST', 'year' => '2005']]),
        ]))->assertOk();
        $user = $doctor->user->fresh();
        $this->assertSame('Renamed', $user->first_name);
        $this->assertSame('en', $user->language);
        $this->assertNull($user->university_id_number);
        $this->assertTrue(Hash::check('secret12', $user->password), 'the password is changed on its own screen, not here');
        $this->assertSame(1, $user->qualifications()->count());
    }

    // ------------------------------------------------------------------ who may manage doctors

    public function test_only_accounts_with_the_doctors_permission_manage_doctors_and_only_doctor_accounts_are_touched(): void
    {
        $target = $this->createDoctor();
        $admin = $this->makeAdmin(['email' => 'admin-pin@test.local']);
        $nurse = $this->makeNurse(['email' => 'nurse-pin@test.local']);

        Role::findByName('staff')->syncPermissions(['manage_patients']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $strangers = ['guest' => null, 'staff without the permission' => $nurse, 'doctor' => $this->makeDoctor(['email' => 'other-doc@test.local']), 'patient' => $this->makePatient()->user];
        $before = User::count();
        foreach ($strangers as $who => $user) {
            if ($user) {
                $this->actingAs($user);
            } else {
                \Illuminate\Support\Facades\Auth::forgetGuards();
                $this->flushSession();
            }
            $routes = [
                ['post', route('doctors.store'), $this->valid(['email' => 'x@test.local'])], ['put', route('doctors.update', $target->id), $this->valid()],
                ['delete', route('doctors.destroy', $target->id)], ['put', route('doctor.status'), ['id' => $target->user_id]],
                ['post', route('doctors.reset.password', $target->user_id)], ['get', route('doctors.index')], ['get', route('doctors.create')],
            ];
            foreach ($routes as $route) {
                $response = $this->{$route[0]}($route[1], $route[2] ?? []);
                $this->assertTrue($response->isRedirect() || $response->getStatusCode() === 403, "{$who} reached {$route[0]} {$route[1]} ({$response->getStatusCode()})");
            }
        }
        $this->assertSame($before, User::count(), 'nobody created an account');
        $this->assertSame(1, (int) $target->user->fresh()->status);
        $this->assertNull($target->user->fresh()->archived_at);
        $this->assertFalse(Hash::check('123456', $target->user->fresh()->password));

        // the endpoints that take any user id only ever act on a doctor account
        $this->actingAs($admin);
        $this->putJson(route('doctor.status'), ['id' => $nurse->id])->assertNotFound();
        $this->postJson(route('doctors.reset.password', $nurse->id))->assertForbidden();
        $this->postJson(route('doctors.reset.password', $admin->id))->assertForbidden();
        $this->assertSame(1, (int) $nurse->fresh()->status);
    }

    public function test_deactivating_a_doctor_keeps_the_account_but_stops_it_signing_in(): void
    {
        $doctor = $this->createDoctor();
        $admin = $this->makeAdmin(['email' => 'admin-status@test.local']);

        $this->actingAs($admin)->putJson(route('doctor.status'), ['id' => $doctor->user_id])->assertOk();
        $this->assertSame(0, (int) $doctor->user->fresh()->status);
        $this->assertNull($doctor->user->fresh()->archived_at);

        // the sign-in form accepts the password, the very next page does not: the doctor is sent back to the login page
        $this->post(route('logout'));
        $this->post('/login', ['email' => 'dora.doctor@test.local', 'password' => 'secret12']);
        $this->get(route('doctors.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();

        $this->actingAs($admin)->putJson(route('doctor.status'), ['id' => $doctor->user_id])->assertOk();
        $this->assertSame(1, (int) $doctor->user->fresh()->status, 'a second toggle activates it again');
    }

    // ------------------------------------------------------------------ the list and the page

    public function test_the_list_shows_doctors_only_and_not_archived_ones(): void
    {
        $shown = $this->createDoctor(['first_name' => 'Shown', 'email' => 'shown@test.local', 'employee_id' => 'E-S', 'prc_license_number' => 'PRC-S', 'contact' => '9170000001']);
        $gone = $this->createDoctor(['first_name' => 'Gone', 'email' => 'gone@test.local', 'employee_id' => 'E-G', 'prc_license_number' => 'PRC-G', 'contact' => '9170000002']);
        $this->makeNurse(['first_name' => 'Nursename']);
        $admin = $this->makeAdmin(['email' => 'admin-list@test.local']);
        $this->actingAs($admin)->deleteJson(route('doctors.destroy', $gone->id))->assertOk();

        $html = Livewire::withoutLazyLoading()->test(DoctorTable::class)->html();

        $this->assertStringContainsString('Shown', $html);
        $this->assertStringNotContainsString('Gone', $html, 'an archived doctor is not listed');
        $this->assertStringNotContainsString('Nursename', $html);
        $this->assertNotNull($shown);
    }

    public function test_a_name_typed_with_html_is_shown_as_text(): void
    {
        $doctor = $this->createDoctor(['first_name' => '<b>Bold</b>']);
        $this->actingAs($this->makeAdmin(['email' => 'admin-html@test.local']));

        foreach ([route('doctors.show', $doctor->id), route('doctors.edit', $doctor->id)] as $url) {
            $this->assertStringNotContainsString('<b>Bold</b>', $this->get($url)->assertOk()->getContent(), $url);
        }
    }

    // ------------------------------------------------------------------ archiving

    public function test_retained_archived_profiles_cannot_be_selected_by_prescription_forms(): void
    {
        $user = $this->makeDoctor();
        $doctorId = $user->doctor->id;
        $requests = [new \App\Http\Requests\CreatePrescriptionRequest(), new \App\Http\Requests\UpdatePrescriptionRequest()];
        $accepts = fn ($request) => \Illuminate\Support\Facades\Validator::make(
            ['doctor_id' => $doctorId], ['doctor_id' => $request->rules()['doctor_id']]
        )->passes();
        foreach ($requests as $request) {
            $this->assertTrue($accepts($request), 'an unarchived doctor is a valid selection');
        }
        $user->delete();
        foreach ($requests as $request) {
            $this->assertFalse($accepts($request), 'a retained archived profile is not a selectable doctor');
        }
        $user->restore();
        foreach ($requests as $request) {
            $this->assertTrue($accepts($request), 'restoration makes the same doctor selectable again');
        }
    }

    public function test_archive_and_restore_keep_the_complete_doctor_profile_and_reserved_licence(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $doctor = $this->createDoctor(['address1' => 'Clinic Road']);
        $user = $doctor->user;
        $addressId = $user->address->id;
        $user->qualifications()->create(['degree' => 'MD', 'university' => 'Test University', 'year' => '2005']);
        $media = $user->addMedia(\Illuminate\Http\UploadedFile::fake()->image('doctor.png'))->toMediaCollection(User::PROFILE, 'public');
        $photoPath = $media->getPathRelativeToRoot();

        $this->deleteJson(route('doctors.destroy', $doctor->id))->assertOk();

        $this->assertNotNull(Doctor::find($doctor->id), 'the doctor profile must survive archiving');
        $this->assertDatabaseHas('addresses', ['id' => $addressId, 'address1' => 'Clinic Road']);
        $this->assertDatabaseHas('media', ['id' => $media->id]);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($photoPath);
        $this->assertSame(1, $doctor->specializations()->count());
        $this->assertSame(1, $user->qualifications()->count());

        $this->assertRefused($this->post(route('doctors.store'), $this->valid([
            'email' => 'replacement@test.local', 'employee_id' => 'EMP-REPLACEMENT', 'contact' => '9170000019',
        ])), 'prc license number');

        $this->artisan('user:restore', ['--id' => $user->id])->assertExitCode(0);
        $restored = User::findOrFail($user->id);
        $this->assertSame($doctor->id, $restored->doctor->id);
        $this->assertSame($addressId, $restored->address->id);
        $this->assertSame($media->id, $restored->getFirstMedia(User::PROFILE)->id);
        $this->get(route('doctors.edit', $doctor->id))->assertOk();
    }

    public function test_retained_profiles_of_archived_accounts_are_hidden_from_lists_and_old_urls(): void
    {
        $user = $this->makeDoctor(['first_name' => 'Archiveddoc']);
        $doctor = $user->doctor;
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $this->assertContains($doctor->id, Livewire::withoutLazyLoading()->test(DoctorTable::class)->instance()->getRows()->pluck('id')->all());
        $user->delete();

        $this->assertNotContains($doctor->id, Livewire::withoutLazyLoading()->test(DoctorTable::class)->instance()->getRows()->pluck('id')->all());
        foreach (['doctors.show', 'doctors.edit'] as $name) {
            $this->get(route($name, $doctor->id))->assertNotFound();
        }
        $this->deleteJson(route('doctors.destroy', $doctor->id))->assertNotFound();
        $this->assertArrayNotHasKey($doctor->id, app(\App\Repositories\PrescriptionRepository::class)->getDoctors()->all());

        $this->artisan('cache:warmup')->assertExitCode(0);
        $this->assertArrayNotHasKey($doctor->id, \Illuminate\Support\Facades\Cache::get('doctors_list'));
        $user->restore();
        $this->assertArrayHasKey($doctor->id, app(\App\Repositories\PrescriptionRepository::class)->getDoctors()->all());
    }

    public function test_archiving_a_doctor_signs_them_out_keeps_their_consultations_named_and_leaves_audit_lines(): void
    {
        $doctor = $this->createDoctor();
        $admin = $this->makeAdmin(['email' => 'admin-archive@test.local']);
        $patient = $this->makePatient();
        $visit = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $doctor->user_id, 'user_id' => $patient->user_id,
            'name' => 'Visit Patient', 'age' => 30, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
        ]);

        $this->actingAs($admin)->deleteJson(route('doctors.destroy', $doctor->id))->assertOk();

        $this->assertNull(User::find($doctor->user_id), 'archived');
        $this->assertNotNull(User::withTrashed()->find($doctor->user_id)->archived_at);
        $this->assertNull(auth()->getProvider()->retrieveById($doctor->user_id), 'an open session of an archived doctor no longer resolves to a user');
        $this->assertSame($doctor->user_id, $visit->fresh()->creator?->id, 'the consultation still names its doctor');

        $this->assertSame(2, ActivityLog::where('action', 'account_changed')->count(), 'the creation and the archive are in the trail');
        $this->assertStringNotContainsString('secret12', json_encode(ActivityLog::all()->toArray()));
    }

    public function test_a_doctor_with_prescriptions_cannot_be_archived_and_nothing_of_them_is_lost(): void
    {
        $doctor = $this->createDoctor();
        $patient = $this->makePatient();
        \App\Models\Prescription::create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id, 'status' => 'pending', 'is_active' => 1]);
        $admin = $this->makeAdmin(['email' => 'admin-rx@test.local']);

        $this->actingAs($admin)->deleteJson(route('doctors.destroy', $doctor->id))->assertStatus(422);

        $this->assertNotNull(User::find($doctor->user_id));
        $this->assertNotNull(Doctor::find($doctor->id));
        $this->assertSame(1, $doctor->specializations()->count());
    }

    public function test_every_change_to_a_doctor_account_leaves_a_line_in_the_trail_without_a_password(): void
    {
        $admin = $this->makeAdmin(['email' => 'admin-trail@test.local']);
        $this->actingAs($admin)->post(route('doctors.store'), $this->valid(['password' => 'Sup3r-s3cret', 'password_confirmation' => 'Sup3r-s3cret']));
        $doctor = Doctor::firstOrFail();
        $this->putJson(route('doctors.update', $doctor->id), $this->valid(['first_name' => 'Edited', 'password' => null, 'password_confirmation' => null]));
        $this->putJson(route('doctor.status'), ['id' => $doctor->user_id]);
        $this->postJson(route('doctors.reset.password', $doctor->user_id));
        $this->deleteJson(route('doctors.destroy', $doctor->id));

        $rows = ActivityLog::whereIn('action', ['account_changed', 'password_reset'])->get();
        $this->assertSame(4, $rows->where('action', 'account_changed')->count(), 'create, edit, status and archive');
        $this->assertSame(1, $rows->where('action', 'password_reset')->count());
        foreach ($rows as $row) {
            $this->assertSame($admin->id, $row->user_id);
            foreach (['Sup3r-s3cret', '123456'] as $secret) {
                $this->assertStringNotContainsString($secret, json_encode($row->toArray()));
            }
        }
    }
}
