<?php

namespace Tests\Feature\Regression;

use App\Http\Controllers\DocumentIssuanceController;
use App\Models\DocumentIssuance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use ReflectionClassConstant;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 5, Pass A item 3, for the consultation form (it validates inline, not through a form request): a value
 * longer than its column used to make the whole save fail with "An error occurred while creating the request document".
 */
class ConsultationFieldLimitsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private function payload(array $extra = []): array
    {
        return array_merge([
            'document_type' => 'consultation_form', 'requested_at' => now()->toDateString(), 'consult_mode' => 'physical',
            'complaints' => 'Cough', 'name' => 'Juan Dela Cruz', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'dob' => '2004-02-02',
        ], $extra);
    }

    public function test_every_free_text_box_of_the_consultation_form_has_a_limit_that_fits_its_column(): void
    {
        $rules = (new ReflectionClassConstant(DocumentIssuanceController::class, 'CONSULTATION_TEXT_RULES'))->getValue();
        $columns = collect(DB::select(
            "select column_name n, character_maximum_length l from information_schema.columns where table_schema = ? and table_name = 'document_issuances' and character_maximum_length is not null",
            [DB::getDatabaseName()]
        ))->pluck('l', 'n');

        $problems = [];
        foreach ($rules as $field => $rule) {
            preg_match('/max:(\d+)/', $rule, $found);
            $max = isset($found[1]) ? (int) $found[1] : null;
            $length = $columns[$field] ?? null;
            if ($length === null) {
                $problems[] = "{$field}: no such text column";
            } elseif ($max === null || $max > $length) {
                $problems[] = "{$field}: allows " . ($max ?? 'any length') . ", the column holds {$length}";
            }
        }
        $this->assertSame([], $problems, implode("\n", $problems));

        // No short text column is left without a limit in the form.
        $short = $columns->filter(fn ($l) => (int) $l <= 255)->keys()->diff(['document_type', 'consult_mode', 'nursing_incharged_id'])->values()->all();
        $this->assertSame([], array_values(array_diff($short, array_keys($rules))), 'a short text column has no limit in the form');
    }

    public function test_a_text_too_long_for_its_column_is_refused_with_a_message_and_saves_nothing(): void
    {
        $this->seedAccessControl();
        $admin = $this->makeAdmin();
        $patient = $this->makePatient();

        foreach (['name', 'religion', 'vital_signs_bp', 'covid_vaccination', 'patient_contact'] as $field) {
            $before = DocumentIssuance::count();
            $this->actingAs($admin)->post(route('document-issuances.store'), $this->payload(['user_id' => $patient->user_id, $field => str_repeat('a', 192)]))
                ->assertSessionHasErrors();
            $this->assertStringContainsString(str_replace('_', ' ', $field) . ' field must not be greater than 191', session('errors')->first(), "{$field}: the message names the field");
            $this->assertSame($before, DocumentIssuance::count(), "{$field}: nothing is saved");
        }

        // The longest text that fits is saved.
        $this->actingAs($admin)->post(route('document-issuances.store'), $this->payload(['user_id' => $patient->user_id, 'vital_signs_bp' => str_repeat('1', 191)]))
            ->assertSessionHasNoErrors();
        $this->assertSame(1, DocumentIssuance::where('vital_signs_bp', str_repeat('1', 191))->count());
    }

    public function test_the_same_limits_hold_when_a_consultation_is_edited(): void
    {
        $this->seedAccessControl();
        $admin = $this->makeAdmin();
        $patient = $this->makePatient();
        $document = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $admin->id, 'user_id' => $patient->user_id,
            'name' => 'Juan Dela Cruz', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete',
        ]);

        $this->actingAs($admin)->put(route('document-issuances.update', $document), $this->payload(['user_id' => $patient->user_id, 'name' => str_repeat('b', 300)]))
            ->assertSessionHasErrors();
        $this->assertStringContainsString('name field must not be greater than 191', session('errors')->first());
        $this->assertSame('Juan Dela Cruz', $document->fresh()->name);
    }
}
