<?php

namespace Tests\Feature\Regression;

use App\Livewire\ReportGeneration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * STATUS section 6, "The Schedules (appointments) tab of Report Generation" (the owner, 2026-11-14: remove it). Nothing in
 * the system schedules a patient (no form sets a queue entry's scheduled_at), so the tab and its CSV were always empty.
 * Removing it showed an older fault: the screen opened any tab name it was given (in the address or sent by the browser)
 * and then looked for a view of that name, so an unknown name, an old Schedules bookmark included, was a server error.
 */
class ScheduleTabRemovedTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    public function test_the_schedules_tab_is_gone_and_an_old_link_opens_the_activity_log(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->get(route('activity-logs.index', ['tab' => 'appointments']))
            ->assertOk()
            ->assertDontSee('Schedules')
            ->assertDontSee("setTab('appointments')", false);

        $this->get(route('activity-logs.export', ['tab' => 'appointments']))->assertNotFound();
    }

    public function test_an_unknown_tab_from_the_address_or_the_browser_falls_back_instead_of_failing(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->get(route('activity-logs.index', ['tab' => 'no-such-report']))->assertOk();

        Livewire::test(ReportGeneration::class)
            ->set('tab', 'no-such-report')
            ->assertSet('tab', 'logs')
            ->call('setTab', 'appointments')
            ->assertSet('tab', 'logs')
            ->call('setTab', 'dispensing')
            ->assertSet('tab', 'dispensing');
    }
}
