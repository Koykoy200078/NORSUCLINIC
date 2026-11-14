<?php

namespace Tests\Feature\Regression;

use App\Models\DocumentIssuance;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * The owner's decision of 2026-11-14 (STATUS section 6, "Vital signs on a prescription"): a vital sign left blank on a
 * prescription stays blank. The new-prescription form still opens with the patient's latest consultation vital signs
 * filled in, but the save used to fill every blank one from that consultation again, on every edit too, so a value the
 * doctor deliberately emptied came back.
 */
class PrescriptionVitalSignsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $doctor;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();

        $this->doctor = $this->makeDoctor();
        $this->patient = $this->makePatient();

        DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $this->doctor->id, 'user_id' => $this->patient->user_id,
            'name' => 'Visit', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete',
            'vital_signs_weight' => '61.5', 'vital_signs_pr' => '72 bpm', 'vital_signs_temp' => '36.8',
            'vital_signs_bp' => '110/70', 'vital_signs_height' => '165',
        ]);
    }

    /**
     * @param  array<string, string>  $vitals
     * @return array<string, mixed>
     */
    private function payload(array $vitals): array
    {
        $medicine = $this->makeMedicine('Paracetamol');
        $this->stockIn($medicine, 20, now()->addMonths(6)->toDateString());

        return [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->doctor->id,
            'consultation_date' => now()->toDateString(),
            'medicines' => [[
                'medicine_id' => $medicine->id, 'dosage' => '500mg', 'route_of_administration' => 'oral',
                'frequency' => 1, 'duration_value' => 2, 'duration_unit' => 'day', 'total_quantity' => 2,
            ]],
        ] + $vitals;
    }

    /** what the form posts for a field the doctor left empty */
    private const BLANK = ['weight_kg' => '', 'pulse_rate' => '', 'body_temperature' => '', 'blood_pressure' => '', 'height_cm' => ''];

    public function test_the_new_prescription_form_still_opens_with_the_latest_consultation_vital_signs(): void
    {
        $this->actingAs($this->doctor)->get(route('doctors.prescriptions.create', ['patientId' => $this->patient->id]))
            ->assertOk()
            ->assertSee('value="61.5"', false)
            ->assertSee('value="72 bpm"', false)
            ->assertSee('value="110/70"', false);
    }

    public function test_vital_signs_left_blank_on_a_new_prescription_stay_blank(): void
    {
        $this->actingAs($this->doctor)
            ->post(route('doctors.prescriptions.store'), $this->payload(['weight_kg' => '60'] + self::BLANK))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $prescription = Prescription::firstOrFail();
        $this->assertSame('60.00', $prescription->weight_kg);
        $this->assertNull($prescription->pulse_rate);
        $this->assertNull($prescription->body_temperature);
        $this->assertNull($prescription->blood_pressure);
        $this->assertNull($prescription->height_cm);
    }

    public function test_a_vital_sign_emptied_on_edit_stays_blank(): void
    {
        $typed = ['weight_kg' => '60', 'pulse_rate' => '80 bpm', 'body_temperature' => '37.2', 'blood_pressure' => '120/80', 'height_cm' => '170'];
        $this->actingAs($this->doctor)->post(route('doctors.prescriptions.store'), $this->payload($typed))->assertRedirect();
        $prescription = Prescription::firstOrFail();

        $this->put(route('doctors.prescriptions.update', $prescription), $this->payload(['pulse_rate' => '', 'blood_pressure' => ''] + $typed))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $prescription->refresh();
        $this->assertNull($prescription->pulse_rate);
        $this->assertNull($prescription->blood_pressure);
        $this->assertSame('60.00', $prescription->weight_kg);
        $this->assertSame('37.2', $prescription->body_temperature);
        $this->assertSame('170.00', $prescription->height_cm);
    }

    /** the consultation lookup that filled the vital signs was also what refused an archived patient; that stays */
    public function test_a_prescription_for_an_archived_patient_is_still_refused(): void
    {
        $payload = $this->payload(self::BLANK);
        $this->patient->delete();

        $this->actingAs($this->doctor)->post(route('doctors.prescriptions.store'), $payload)->assertNotFound();

        $this->assertSame(0, Prescription::count());
    }
}
