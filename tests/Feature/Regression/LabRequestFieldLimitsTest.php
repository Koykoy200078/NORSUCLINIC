<?php

namespace Tests\Feature\Regression;

use App\Http\Controllers\LabRequestController;
use App\Models\LabRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use ReflectionClassConstant;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 5, Pass A item 3 for the lab request form (inline validation): the fields it saves must fit their columns,
 * and the ones that were saved unchecked (age, contact, remarks ...) are validated.
 */
class LabRequestFieldLimitsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private function payload(int $patientUserId, array $extra = []): array
    {
        return array_merge([
            'patient_user_id' => $patientUserId, 'patient_name' => 'Juan Dela Cruz', 'requested_at' => now()->toDateString(),
            'custom_tests' => ['Vitamin D'],
        ], $extra);
    }

    public function test_every_text_the_lab_forms_save_fits_its_column(): void
    {
        $rules = (new ReflectionClassConstant(LabRequestController::class, 'TEXT_RULES'))->getValue();
        $columns = collect(DB::select(
            "select column_name n, character_maximum_length l from information_schema.columns where table_schema = ? and table_name in ('lab_requests') and character_maximum_length is not null",
            [DB::getDatabaseName()]
        ))->pluck('l', 'n');

        $problems = [];
        foreach ($rules as $field => $rule) {
            if (! isset($columns[$field])) {
                continue; // a number or a date: not text
            }
            preg_match('/max:(\d+)/', $rule, $found);
            if (! isset($found[1]) || (int) $found[1] > $columns[$field]) {
                $problems[] = "{$field}: " . ($found[1] ?? 'no max') . " vs column {$columns[$field]}";
            }
        }
        $this->assertSame([], $problems, implode("\n", $problems));
        $this->assertArrayHasKey('patient_name', $rules);
        $this->assertSame('required|string|max:191', $rules['patient_name']);
    }

    public function test_a_lab_request_with_a_text_too_long_for_its_column_is_refused_with_a_message(): void
    {
        $this->seedAccessControl();
        $admin = $this->makeAdmin();
        $patient = $this->makePatient();

        foreach (['patient_name', 'patient_contact', 'requesting_physician', 'physician_license_no', 'status_affiliation'] as $field) {
            $this->actingAs($admin)->post(route('lab-requests.store'), $this->payload($patient->user_id, [$field => str_repeat('a', 192)]))
                ->assertSessionHasErrors();
            $this->assertSame(0, LabRequest::count(), "{$field}: nothing is saved");
        }

        $this->actingAs($admin)->post(route('lab-requests.store'), $this->payload($patient->user_id, ['custom_tests' => [str_repeat('t', 192)]]))
            ->assertSessionHasErrors();
        $this->assertSame(0, LabRequest::count(), 'a custom test name longer than its column');

        $this->actingAs($admin)->post(route('lab-requests.store'), $this->payload($patient->user_id, ['patient_age' => 'twenty']))
            ->assertSessionHasErrors();
        $this->assertSame(0, LabRequest::count(), 'the age is a number');

        // The longest text that fits is saved.
        $this->actingAs($admin)->post(route('lab-requests.store'), $this->payload($patient->user_id, ['patient_age' => 20, 'remarks' => str_repeat('r', 500), 'patient_contact' => str_repeat('9', 191)]))
            ->assertSessionHasNoErrors();
        $this->assertSame(1, LabRequest::count());
    }
}
