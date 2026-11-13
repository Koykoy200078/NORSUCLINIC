<?php

namespace Tests\Feature\Regression;

use App\Models\PatientType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Re-audit 2026-11-13 (owner's decision): adding and editing a patient check the e-mail with the same rule, "email:filter",
 * as staff and doctor accounts do. Adding used Laravel's looser "email" rule, which accepts an address without a proper
 * domain ("ana@clinic") that editing then refused; the two requests are now one set of rules (DRY). The patient pages flash
 * their errors as a plain list (not keyed by field), so the tests check that an error is shown and nothing is saved.
 */
class PatientEmailRuleTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'gender' => User::FEMALE,
            'dob' => '2004-02-02',
            'patient_type_id' => PatientType::where('code', 'guest')->value('id'),
            'nationality_citizenship' => 'Filipino',
            'immunization_record' => 'Complete',
        ], $overrides);
    }

    public function test_adding_a_patient_refuses_an_address_without_a_proper_domain(): void
    {
        $this->actingAs($this->makeAdmin())->post(route('patients.store'), $this->payload(['email' => 'ana@clinic']))
            ->assertSessionHasErrors();
        $this->assertSame(0, User::where('email', 'ana@clinic')->count());

        $this->actingAs($this->makeAdmin())->post(route('patients.store'), $this->payload(['email' => 'ana@clinic.test']))
            ->assertSessionHasNoErrors();
        $this->assertSame(1, User::where('email', 'ana@clinic.test')->count());
    }

    public function test_editing_a_patient_uses_the_same_rule_and_keeps_its_own_address(): void
    {
        $patient = $this->makePatient();
        $patient->user->update(['email' => 'own@clinic.test']);

        $this->actingAs($this->makeAdmin())->put(route('patients.update', $patient), $this->payload(['email' => 'ana@clinic']))
            ->assertSessionHasErrors();

        $this->assertSame('own@clinic.test', $patient->user->fresh()->email, 'the refused address is not saved');

        // its own address is not a duplicate of itself
        $this->actingAs($this->makeAdmin())->put(route('patients.update', $patient), $this->payload(['email' => 'own@clinic.test']))
            ->assertSessionHasNoErrors();
    }
}
