<?php

namespace Tests\Feature\Regression;

use App\Models\DocumentIssuance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Every real staff designation + station pairing against the module-resolving routes (consultations vs certificates,
 * and the activity log tabs): a route opens exactly when the designation + station policy allows the module that the
 * route resolves to - no more (leak), no less (a button that answers 403).
 */
class StaffModuleRoutingTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private const PAIRS = [
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

    /** @return array<string, array{0: User}> */
    private function staffWithPolicy(): array
    {
        $users = [];
        foreach (self::PAIRS as $i => [$designation, $station]) {
            $users["{$designation}@{$station}"] = $this->makeStaff($designation, $station, ['email' => "route.staff{$i}@test.local"]);
        }

        return $users;
    }

    private function document(string $type, User $creator): DocumentIssuance
    {
        $patient = $this->makePatient(['email' => 'route.pt' . uniqid() . '@test.local']);

        return DocumentIssuance::create([
            'document_type' => $type, 'document_creator_id' => $creator->id, 'user_id' => $patient->user_id,
            'name' => 'Route Patient', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
        ]);
    }

    public function test_document_routes_open_exactly_for_the_module_they_resolve_to(): void
    {
        $head = $this->makeStaff('clinic_head', 'front_desk', ['email' => 'route.head@test.local']);
        $consultation = $this->document('consultation_form', $head);
        $certificate = $this->document('medical_certificate', $head);

        $scenarios = [
            // [label, url, module the route is judged by]
            ['list consultations', route('staff.document-issuances.index', ['module' => 'consultation']), 'consultations'],
            ['list certificates', route('staff.document-issuances.index', ['module' => 'certificates']), 'certificates'],
            ['new consultation form', route('staff.document-issuances.create', ['document_type' => 'consultation_form']), 'consultations'],
            ['new certificate form', route('staff.document-issuances.create', ['document_type' => 'medical_certificate']), 'certificates'],
            ['view a consultation', route('staff.document-issuances.show', $consultation), 'consultations'],
            ['view a certificate', route('staff.document-issuances.show', $certificate), 'certificates'],
            // a front-desk account must not open a consultation by pretending it is a certificate
            ['consultation disguised as a certificate', route('staff.document-issuances.show', $consultation) . '?module=certificates', 'consultations'],
        ];

        $failures = [];
        foreach ($this->staffWithPolicy() as $who => $user) {
            foreach ($scenarios as [$label, $url, $module]) {
                $expected = canStaffAccessModule($module, $user);
                $status = $this->actingAs($user)->get($url)->getStatusCode();
                $allowed = $status !== 403;

                if ($allowed !== $expected) {
                    $failures[] = "{$who}: {$label} -> {$status}, policy for '{$module}' says " . ($expected ? 'allow' : 'deny');
                }
                $this->assertLessThan(500, $status, "{$who}: {$label} crashed");
            }
        }

        $this->assertSame([], $failures);
    }

    public function test_activity_log_tabs_follow_the_reports_and_notifications_modules(): void
    {
        $failures = [];

        foreach ($this->staffWithPolicy() as $who => $user) {
            foreach (['visits' => 'reports', 'accomplishment' => 'reports', 'dispensing' => 'reports', 'appointments' => 'reports',
                'logs' => 'notifications', 'inventory' => 'notifications'] as $tab => $module) {
                $expected = canStaffAccessModule($module, $user);

                $page = $this->actingAs($user)->get(route('staff.activity-logs.index', ['tab' => $tab]));
                $export = $this->get(route('staff.activity-logs.export', ['tab' => in_array($tab, ['accomplishment'], true) ? 'visits' : $tab]));

                $this->assertLessThan(500, $page->getStatusCode(), "{$who}: {$tab} page crashed");
                $this->assertLessThan(500, $export->getStatusCode(), "{$who}: {$tab} export crashed");

                // The CSV export is refused outright when the module is not allowed.
                if (! $expected && $tab !== 'accomplishment' && $export->getStatusCode() !== 403) {
                    $failures[] = "{$who}: export of '{$tab}' answered {$export->getStatusCode()} but '{$module}' is not allowed";
                }
                if ($expected && $export->getStatusCode() === 403) {
                    $failures[] = "{$who}: export of '{$tab}' refused although '{$module}' is allowed";
                }
            }
        }

        $this->assertSame([], $failures);
    }

    public function test_every_designation_can_open_its_own_dashboard_and_nothing_crashes_on_the_shared_helpers(): void
    {
        foreach ($this->staffWithPolicy() as $who => $user) {
            $this->actingAs($user)->get(route('staff.dashboard'))->assertOk();
            $this->get(route('profile.setting'))->assertOk();
            $this->get(route('update-dark-mode'))->assertOk();
        }
    }
}
