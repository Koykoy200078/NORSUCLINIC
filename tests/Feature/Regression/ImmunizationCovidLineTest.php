<?php

namespace Tests\Feature\Regression;

use App\Models\Patient;
use App\Models\PatientType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * The patient's immunization record carries one "COVID-19 Vaccination Status: ..." line, kept in step with the COVID
 * vaccination typed on the patient form and on a consultation. Both paths (PatientRepository and ConsultationPatientService)
 * apply the same rule, now from one place (re-audit 2026-11-13, DRY): an existing line is replaced, a missing one is added
 * at the end, the other lines stay in order, and "unknown" / "n/a" change nothing.
 */
class ImmunizationCovidLineTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function patientWithRecord(string $record): Patient
    {
        $patient = $this->makePatient();
        $patient->update(['immunization_record' => $record]);

        return $patient->fresh();
    }

    private function updatePatient(Patient $patient, string $covid, string $record): void
    {
        $user = $patient->user;
        $this->actingAs($this->makeAdmin())->put(route('patients.update', $patient), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'gender' => User::FEMALE,
            'patient_type_id' => PatientType::where('code', 'guest')->value('id') ?? $patient->patient_type_id,
            'nationality_citizenship' => 'Filipino',
            'immunization_record' => $record,
            'covid_vaccination' => $covid,
        ])->assertSessionHasNoErrors();
    }

    private function consult(Patient $patient, string $covid): void
    {
        $this->actingAs($this->makeAdmin())->post(route('document-issuances.store'), [
            'document_type' => 'consultation_form', 'requested_at' => now()->toDateString(), 'consult_mode' => 'physical',
            'complaints' => 'Cough', 'name' => $patient->user->full_name, 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete',
            'dob' => '2004-02-02', 'user_id' => $patient->user_id, 'covid_vaccination' => $covid,
        ])->assertSessionHasNoErrors();
    }

    public function test_the_patient_form_replaces_the_covid_line_and_keeps_the_rest_in_order(): void
    {
        $patient = $this->patientWithRecord("BCG\nCOVID-19 Vaccination Status: First dose\nHepatitis B");

        $this->updatePatient($patient, 'Fully vaccinated', "BCG\nCOVID-19 Vaccination Status: First dose\nHepatitis B");

        $this->assertSame(['BCG', 'COVID-19 Vaccination Status: Fully vaccinated', 'Hepatitis B'], preg_split('/\R/', (string) $patient->fresh()->immunization_record));
    }

    public function test_the_patient_form_adds_a_missing_covid_line_at_the_end_and_ignores_unknown(): void
    {
        $patient = $this->patientWithRecord('BCG');

        $this->updatePatient($patient, 'Unknown', 'BCG');
        $this->assertSame('BCG', $patient->fresh()->immunization_record, '"Unknown" changes nothing');

        $this->updatePatient($patient, 'Booster', 'BCG');
        $this->assertSame(['BCG', 'COVID-19 Vaccination Status: Booster'], preg_split('/\R/', (string) $patient->fresh()->immunization_record));
    }

    public function test_a_consultation_updates_the_patients_covid_line_the_same_way(): void
    {
        $patient = $this->patientWithRecord("BCG\ncovid vaccination status: old\nCOVID-19 Vaccination Status: older\nMMR");

        $this->consult($patient, 'Second dose');

        // the old lines (any spelling) collapse into one line in the first one's place
        $this->assertSame(['BCG', 'COVID-19 Vaccination Status: Second dose', 'MMR'], preg_split('/\R/', (string) $patient->fresh()->immunization_record));

        $this->consult($patient, 'n/a');
        $this->assertSame(['BCG', 'COVID-19 Vaccination Status: Second dose', 'MMR'], preg_split('/\R/', (string) $patient->fresh()->immunization_record), '"n/a" changes nothing');
    }
}
