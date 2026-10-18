<?php

namespace Tests\Feature\Regression;

use App\Livewire\LabRequestTable;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Phase 5, row 5.8 Lab requests, Pass A: what LabRequestDeleteTest, LabRequestFieldLimitsTest, PdfNastyDataTest and
 * ListQueryCountTest did not reach.
 */
class LabRequestPassATest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    /** @return array<string, mixed> */
    private function payload(int $patientUserId, array $extra = []): array
    {
        return array_merge([
            'patient_user_id' => $patientUserId, 'patient_name' => 'Juan Dela Cruz', 'requested_at' => now()->toDateString(),
            'custom_tests' => ['Vitamin D'],
        ], $extra);
    }

    private function request(User $by, array $attributes = [], string $name = 'Alpha'): LabRequest
    {
        $request = LabRequest::create(array_merge([
            'request_number' => 'LAB-' . random_int(100000, 999999), 'document_creator_id' => $by->id, 'patient_user_id' => $this->makePatient()->user_id,
            'patient_name' => $name, 'requested_at' => now()->toDateString(), 'status' => LabRequest::STATUS_PENDING,
        ], $attributes));
        LabRequestItem::create(['lab_request_id' => $request->id, 'lab_test_id' => null, 'test_name' => 'Test of ' . $name, 'test_category' => 'Other', 'result_status' => LabRequestItem::RESULT_PENDING]);

        return $request;
    }

    // ------------------------------------------------------------------ validation: the date and the results

    public function test_the_request_date_must_be_a_real_date_on_create_and_on_edit(): void
    {
        $admin = $this->makeAdmin();
        $patient = $this->makePatient();
        $this->actingAs($admin);

        foreach (['1066-10-14', now()->addYears(3)->toDateString(), '05/10/2026', 'tomorrow-ish'] as $date) {
            $this->post(route('lab-requests.store'), $this->payload($patient->user_id, ['requested_at' => $date]))->assertSessionHasErrors();
            $this->assertStringContainsString('date of the request', implode(' ', session('errors')->getBag('default')->all()), $date);
            $this->assertSame(0, LabRequest::count(), $date);
            session()->flush();
        }
        $this->post(route('lab-requests.store'), $this->payload($patient->user_id))->assertSessionHasNoErrors();
        $request = LabRequest::firstOrFail();

        $this->put(route('lab-requests.update', $request), $this->payload($patient->user_id, ['requested_at' => '1066-10-14']))->assertSessionHasErrors();
        $this->assertSame(now()->toDateString(), $request->fresh()->requested_at->toDateString());
    }

    public function test_results_typed_on_the_edit_page_are_checked_and_a_refused_edit_changes_nothing(): void
    {
        $admin = $this->makeAdmin();
        $request = $this->request($admin);
        $item = $request->items()->firstOrFail();
        $this->actingAs($admin);

        $edit = fn (array $result, array $extra = []) => $this->put(route('lab-requests.update', $request), $this->payload($request->patient_user_id, $extra + [
            'patient_name' => 'Changed name', 'results' => [$item->id => $result], 'custom_tests' => ['Test of Alpha'],
        ]));

        // a status that is not one of the four, and texts longer than anything sensible
        $edit(['result_value' => '5.4', 'result_status' => 'bogus', 'notes' => 'x'])->assertSessionHasErrors();
        $edit(['result_value' => str_repeat('a', 20001), 'result_status' => 'normal', 'notes' => 'x'])->assertSessionHasErrors();
        $edit(['result_value' => '5.4', 'result_status' => 'normal', 'notes' => str_repeat('a', 20001)])->assertSessionHasErrors();
        $this->assertSame('Alpha', $request->fresh()->patient_name, 'a refused edit changes nothing');
        $this->assertSame(LabRequestItem::RESULT_PENDING, $item->fresh()->result_status);

        $edit(['result_value' => '5.4', 'result_status' => 'abnormal', 'notes' => 'repeat in a week'])->assertSessionHasNoErrors();
        $this->assertSame('abnormal', $item->fresh()->result_status);
        $this->assertSame('5.4', $item->fresh()->result_value);
        $this->assertSame('Changed name', $request->fresh()->patient_name);
    }

    // ------------------------------------------------------------------ the list

    public function test_the_headers_of_the_lab_list_sort_it_and_the_search_finds_a_test_name(): void
    {
        $admin = $this->makeAdmin();
        // created in this order (so the ids run Mia, Zed, Abe) with dates that run Zed, Mia, Abe: the date order, the id order
        // and the name order all differ
        $this->request($admin, ['requested_at' => now()->subDay()->toDateString()], 'Mia');
        $this->request($admin, ['requested_at' => now()->toDateString()], 'Zed');
        $this->request($admin, ['requested_at' => now()->subDays(2)->toDateString()], 'Abe');
        $this->actingAs($admin);

        $names = fn ($component) => $component->instance()->getRows()->pluck('patient_name')->all();
        $table = fn () => Livewire::withoutLazyLoading()->test(LabRequestTable::class);

        $this->assertSame(['Zed', 'Mia', 'Abe'], $names($table()), 'newest request first by default');
        $this->assertSame(['Abe', 'Mia', 'Zed'], $names($table()->call('sortBy', 'patient_name')), 'the Patient Name header sorts');
        $this->assertSame(['Zed', 'Mia', 'Abe'], $names($table()->call('sortBy', 'patient_name')->call('sortBy', 'patient_name')), 'and a second click reverses it');
        $this->assertSame(['Abe'], $names($table()->set('search', 'Test of Abe')), 'a typed test name finds its request');
        $this->assertSame(['Mia'], $names($table()->set('search', 'Mi')), 'a name part still finds it');
    }

    // ------------------------------------------------------------------ the form and the status

    public function test_the_new_request_form_prefills_only_from_a_patient_not_from_any_account(): void
    {
        $admin = $this->makeAdmin(['first_name' => 'Zzadminfirst', 'contact' => '9170000001']);
        $doctor = $this->makeDoctor(['first_name' => 'Zzdoctorfirst']);
        $patient = $this->makePatient(['first_name' => 'Zzpatientfirst']);
        $this->actingAs($admin);

        $this->assertStringContainsString('Zzpatientfirst', $this->get(route('lab-requests.create', ['user_id' => $patient->user_id]))->assertOk()->getContent());
        $html = $this->get(route('lab-requests.create', ['user_id' => $doctor->id]))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/name="patient_name"[^>]*value="([^"]*)"/s', $html, $field) + preg_match('/value="([^"]*)"[^>]*name="patient_name"/s', $html, $field2), 'the name box is on the form');
        $this->assertStringNotContainsString('Zzdoctorfirst', ($field[1] ?? '') . ($field2[1] ?? ''), "a doctor's account is not a patient and is not copied into the form");
        $html = $this->get(route('lab-requests.create', ['user_id' => $admin->id]))->assertOk()->getContent();
        $this->assertStringNotContainsString('value="9170000001"', $html);
    }

    public function test_a_status_change_that_the_workflow_does_not_allow_is_refused_and_a_finished_request_stays_finished(): void
    {
        $admin = $this->makeAdmin();
        $request = $this->request($admin);
        $this->actingAs($admin);

        $this->post(route('lab-requests.update-status', $request), ['status' => 'completed'])->assertSessionHas('error');
        $this->assertSame('pending', $request->fresh()->status);

        foreach (['collected', 'processing', 'completed'] as $status) {
            $this->post(route('lab-requests.update-status', $request), ['status' => $status])->assertSessionHas('success');
        }
        $completed = $request->fresh();
        $this->assertSame('completed', $completed->status);
        $this->assertNotNull($completed->completed_at);

        $this->post(route('lab-requests.update-status', $request), ['status' => 'cancelled'])->assertSessionHas('error');
        $this->post(route('lab-requests.update-status', $request), ['status' => 'not-a-status'])->assertSessionHasErrors();
        $this->assertSame('completed', $request->fresh()->status);
    }

    public function test_a_status_button_pressed_on_a_stale_page_cannot_reopen_a_request_that_has_moved_on(): void
    {
        $admin = $this->makeAdmin();
        $request = $this->request($admin);
        $this->actingAs($admin);

        // Somebody else completed the request after this page was loaded: right after the request is read for the
        // button press, the row in the database is already "completed".
        $flipped = false;
        \Illuminate\Support\Facades\Event::listen('eloquent.retrieved: ' . LabRequest::class, function ($model) use (&$flipped, $request) {
            if (! $flipped && (int) $model->id === (int) $request->id) {
                $flipped = true;
                \Illuminate\Support\Facades\DB::table('lab_requests')->where('id', $request->id)->update(['status' => 'completed']);
            }
        });

        $this->post(route('lab-requests.update-status', $request), ['status' => 'collected'])->assertSessionHas('error');
        $this->assertSame('completed', $request->fresh()->status, 'the finished request stays finished');
    }
}
