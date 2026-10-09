<?php

namespace Tests\Feature\Regression;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * 2026-10-08: the public landing page (`/`) has two "Dashboard" buttons (header and hero). The hero one was
 * hard-wired to `patients.index` (an administrator-only URL) for EVERY signed-in user, so a doctor / staff / nurse who
 * came back to the landing page and pressed it got "403 USER DOES NOT HAVE THE RIGHT ROLES". The header one had its own
 * three-way if/else that sent nurse-role accounts (and anyone unrecognised) to the administrator dashboard.
 *
 * Both buttons must follow the signed-in user's role (getDashboardURL()), and a user who has no dashboard at all must
 * never be handed an administrator URL.
 */
class LandingPageDashboardTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private const STAFF_PAIRS = [
        ['clinic_head', 'front_desk'], ['pharmacist', 'pharmacy'], ['records_officer', 'records_area'],
        ['clinic_staff', 'front_desk'], ['clinic_staff', 'records_area'],
        ['triage_officer', 'triage_area'], ['triage_officer', 'isolation_room'], ['triage_officer', 'observation_room'],
        ['nurse', 'triage_area'], ['nurse', 'medical_consultation'], ['nurse', 'isolation_room'], ['nurse', 'observation_room'],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    /**
     * Every kind of user that can hold a session, with the dashboard route that is theirs.
     *
     * @return array<string, array{0: User, 1: string}>
     */
    private function actors(): array
    {
        $actors = [
            'admin' => [$this->makeAdmin(), 'admin.dashboard'],
            'doctor' => [$this->makeDoctor(), 'doctors.dashboard'],
        ];

        foreach (self::STAFF_PAIRS as $i => [$designation, $station]) {
            $actors["staff {$designation}@{$station}"] = [
                $this->makeStaff($designation, $station, ['email' => "landing.staff{$i}@test.local"]),
                'staff.dashboard',
            ];
        }

        $nurseRole = $this->makeStaff('nurse', 'triage_area', ['email' => 'landing.nurserole@test.local']);
        $nurseRole->syncRoles(['nurse']);
        (require database_path('migrations/2026_10_09_170000_unify_staff_nurse_access.php'))->up();
        $actors['nurse role'] = [$nurseRole->fresh(), 'staff.dashboard'];

        return $actors;
    }

    private function pathOf(string $routeName): string
    {
        return (string) parse_url(route($routeName), PHP_URL_PATH);
    }

    private function linksTo(string $html, string $path): int
    {
        return preg_match_all('#href="[^"]*' . preg_quote($path, '#') . '"#', $html);
    }

    public function test_both_landing_buttons_open_the_signed_in_users_own_dashboard(): void
    {
        $failures = [];

        foreach ($this->actors() as $who => [$user, $dashboardRoute]) {
            $html = $this->actingAs($user)->get(route('medical'))->assertOk()->getContent();

            $own = $this->pathOf($dashboardRoute);
            if ($this->linksTo($html, $own) < 2) {
                $failures[] = "{$who}: header + hero buttons should both link to {$own}";
            }

            // No link to the other roles' landing spots, and never the admin patient list the hero button used.
            foreach (['admin.dashboard', 'doctors.dashboard', 'staff.dashboard', 'patients.index'] as $other) {
                $path = $this->pathOf($other);
                if ($path !== $own && $this->linksTo($html, $path) > 0) {
                    $failures[] = "{$who}: landing page links to {$path}, which is not theirs";
                }
            }

            // And the button has to actually open for them.
            $status = $this->actingAs($user)->get($own)->getStatusCode();
            if ($status !== 200) {
                $failures[] = "{$who}: {$own} answered {$status}";
            }
        }

        $this->assertSame([], $failures);
    }

    public function test_the_dashboard_helper_follows_the_role_for_every_kind_of_user(): void
    {
        foreach ($this->actors() as $who => [$user, $dashboardRoute]) {
            $this->actingAs($user);
            $this->assertSame(route($dashboardRoute), getDashboardURL(), $who);
        }
    }

    public function test_a_guest_is_offered_login_and_no_dashboard(): void
    {
        $html = $this->get(route('medical'))->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(1, $this->linksTo($html, $this->pathOf('login')));
        foreach (['admin.dashboard', 'doctors.dashboard', 'staff.dashboard', 'patients.index'] as $name) {
            $this->assertSame(0, $this->linksTo($html, $this->pathOf($name)), "guest was offered {$name}");
        }
    }

    public function test_a_user_with_no_dashboard_is_never_handed_an_administrator_url(): void
    {
        $roleless = User::create([
            'first_name' => 'No', 'last_name' => 'Role', 'email' => 'landing.norole@test.local', 'password' => 'password',
            'type' => User::STAFF, 'status' => 1, 'gender' => User::MALE, 'email_verified_at' => now(),
        ]);
        $patientRole = $this->makePatient(['email' => 'landing.patientrole@test.local'])->user;

        foreach (['no role' => $roleless, 'patient role' => $patientRole] as $who => $user) {
            $html = $this->actingAs($user)->get(route('medical'))->assertOk()->getContent();

            $this->assertSame(0, $this->linksTo($html, '/admin/'), "{$who}: landing page links into /admin/");
            foreach (['admin.dashboard', 'doctors.dashboard', 'staff.dashboard', 'patients.index'] as $name) {
                $this->assertSame(0, $this->linksTo($html, $this->pathOf($name)), "{$who}: landing page offers {$name}");
            }

            // visiting /login while signed in must not bounce into a 403 either
            $this->get(route('login'))->assertRedirect(route('medical'));
        }
    }
}
