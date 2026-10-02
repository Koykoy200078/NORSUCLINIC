<?php

namespace Tests\Feature\Regression;

use App\Models\Campus;
use App\Models\College;
use App\Models\Course;
use App\Models\DocumentIssuance;
use App\Models\Illness;
use App\Models\IllnessSystem;
use App\Models\PatientType;
use App\Models\ServiceType;
use App\Models\Setting;
use App\Models\User;
use App\Models\YearLevel;
use App\Services\Reports\ConsultationSnapshotBackfill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * 2026-10-01 report, milestone 2: a consultation records WHICH illness (from the clinic's list, by body system)
 * and WHICH services were rendered, plus the patient's campus / college / course / year level / type as they
 * were that day, so the ACCOMPLISHMENT REPORT can count them.
 */
class ConsultationClassificationTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function illness(string $name, ?string $system = null): Illness
    {
        $query = Illness::where('name', $name);
        if ($system) {
            $query->whereHas('system', fn ($q) => $q->where('name', $system));
        }

        return $query->firstOrFail();
    }

    private function service(string $name): ServiceType
    {
        return ServiceType::where('name', $name)->firstOrFail();
    }

    /** @return array<string, mixed> */
    private function payload(User $patientUser, User $nurse, array $extra = []): array
    {
        return array_merge([
            'document_type' => 'consultation_form',
            'user_id' => $patientUser->id,
            'name' => $patientUser->full_name,
            'date_of_birth' => '2003-05-01',
            'gender' => 'Male',
            'requested_at' => now()->toDateString(),
            'consult_mode' => 'physical',
            'complaints' => 'Cough',
            'nursing_incharged' => $nurse->id,
        ], $extra);
    }

    public function test_the_clinic_lists_from_the_accomplishment_report_are_available(): void
    {
        $this->assertSame(11, IllnessSystem::count());
        $this->assertSame(
            ['Respiratory System', 'Circulatory System', 'Digestive System', 'Urinary System', 'Immune System', 'Skeletal System',
                'Muscular System', 'Nervous System', 'EENT', 'Reproductive System', 'Integumentary System'],
            IllnessSystem::orderBy('sort_order')->pluck('name')->all()
        );

        $cough = $this->illness('Cough/colds');
        $this->assertSame('Respiratory System', $cough->system->name);
        $this->assertSame('Upper Respiratory Infections', $cough->group_label);
        $this->assertSame('Lower Respiratory Infections', $this->illness('Pneumonia')->group_label);
        $this->assertSame('Joint sprain', $this->illness('Knee')->group_label);

        // Every body system has an "Others" line that takes free text.
        foreach (IllnessSystem::with('illnesses')->get() as $system) {
            $this->assertTrue($system->illnesses->contains('is_other', true), "{$system->name} has no Others line");
        }

        $this->assertSame('clinical_procedure', $this->service('Tetanus injection')->category);
        $this->assertSame('health_promotion', $this->service('Medical certificate issuance')->category);
        $this->assertSame('medical_certificate', $this->service('Medical certificate issuance')->auto_rule);
        $this->assertSame('medicine_given', $this->service('Medicine assistance')->auto_rule);

        $this->assertNotNull(College::where('college_name', 'Graduate School (GS)')->first());
        $this->assertNotNull(Setting::where('key', 'university_physician_name')->first());
        $this->assertNotNull(Setting::where('key', 'university_physician_title')->first());
    }

    public function test_a_doctor_records_illnesses_services_and_the_patients_college_on_a_consultation(): void
    {
        $doctor = $this->makeDoctor();
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $patient = $this->makePatient([], ['patient_type_id' => PatientType::where('code', 'student')->value('id')]);
        $campus = Campus::create(['campus_name' => 'Main Campus 1']);
        $college = College::create(['college_name' => 'College of Test (CTX)']);
        $course = Course::create(['course_name' => 'BS Testing']);
        $yearLevel = YearLevel::create(['year_level_name' => '2nd Year']);
        $other = $this->illness('Others', 'Skeletal System');

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->payload($patient->user, $nurse, [
            'campus_id' => $campus->id, 'college_id' => $college->id, 'course_id' => $course->id, 'year_level_id' => $yearLevel->id,
            'illness_ids' => [$this->illness('Cough/colds')->id, $this->illness('Hypertension')->id, $other->id],
            'illness_other' => [$other->id => 'Lump on right leg'],
            'service_ids' => [$this->service('Tetanus injection')->id, $this->service('Wound treatment/dressing')->id],
        ]))->assertRedirect()->assertSessionMissing('error');

        $document = DocumentIssuance::firstOrFail();

        $this->assertEqualsCanonicalizing(['Cough/colds', 'Hypertension', 'Others'], $document->illnesses->pluck('name')->all());
        $this->assertSame('Lump on right leg', $document->illnesses->firstWhere('name', 'Others')->pivot->other_text);
        $this->assertEqualsCanonicalizing(['Tetanus injection', 'Wound treatment/dressing'], $document->services->pluck('name')->all());

        // The patient's affiliation is saved by id, as it was on the day of the visit.
        $this->assertSame($campus->id, (int) $document->campus_id);
        $this->assertSame($college->id, (int) $document->college_id);
        $this->assertSame($course->id, (int) $document->course_id);
        $this->assertSame($yearLevel->id, (int) $document->year_level_id);
        $this->assertSame((int) PatientType::where('code', 'student')->value('id'), (int) $document->patient_type_id);
    }

    public function test_faculty_and_staff_visits_keep_the_department_or_office_and_their_type(): void
    {
        $doctor = $this->makeDoctor();
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $employee = $this->makePatient();

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->payload($employee->user, $nurse, [
            'informant' => 'Faculty', 'department_id' => 3,
        ]))->assertRedirect();

        $document = DocumentIssuance::firstOrFail();
        $this->assertSame((int) PatientType::where('code', 'faculty')->value('id'), (int) $document->patient_type_id);
        $this->assertSame(3, (int) $document->department_id);
    }

    public function test_a_nurse_can_classify_a_visit_and_editing_replaces_the_picks(): void
    {
        $doctor = $this->makeDoctor();
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $patient = $this->makePatient();

        $this->actingAs($nurse)->post(route('staff.document-issuances.store'), $this->payload($patient->user, $nurse, [
            'illness_ids' => [$this->illness('Headache')->id],
            'service_ids' => [$this->service('BP taking only')->id ?? 0],
        ]))->assertRedirect();

        $document = DocumentIssuance::firstOrFail();
        $this->assertSame(['Headache'], $document->illnesses->pluck('name')->all());

        $this->put(route('staff.document-issuances.update', $document), [
            'name' => $patient->user->full_name, 'complaints' => 'Cough', 'requested_at' => now()->toDateString(),
            'nursing_incharged' => $nurse->id, 'nursing_intervention' => 'ok',
            'illness_ids' => [$this->illness('Migraine')->id, $this->illness('Insomnia')->id],
            'service_ids' => [$this->service('Tetanus injection')->id],
        ])->assertRedirect()->assertSessionMissing('error');

        $document->refresh();
        $this->assertEqualsCanonicalizing(['Migraine', 'Insomnia'], $document->illnesses()->pluck('name')->all());
        $this->assertSame(['Tetanus injection'], $document->services()->pluck('name')->all());

        // The nurse still cannot write the doctor's assessment / plan.
        $this->assertNull($document->assessment);

        // A form that did not carry the picks at all (an old page) cannot wipe what was saved.
        $this->put(route('staff.document-issuances.update', $document), [
            'name' => $patient->user->full_name, 'complaints' => 'Cough', 'requested_at' => now()->toDateString(),
            'nursing_incharged' => $nurse->id, 'nursing_intervention' => 'ok',
        ])->assertRedirect();
        $this->assertSame(2, $document->illnesses()->count());
        $this->assertSame(1, $document->services()->count());

        // The real form always posts its marker, so un-ticking everything (browsers send nothing for an empty
        // checklist) clears the picks.
        $this->put(route('staff.document-issuances.update', $document), [
            'name' => $patient->user->full_name, 'complaints' => 'Cough', 'requested_at' => now()->toDateString(),
            'nursing_incharged' => $nurse->id, 'nursing_intervention' => 'ok', 'classification_submitted' => '1',
        ])->assertRedirect();
        $this->assertSame(0, $document->illnesses()->count());
        $this->assertSame(0, $document->services()->count());
    }

    public function test_unknown_illness_ids_are_refused_and_nothing_is_saved(): void
    {
        $doctor = $this->makeDoctor();
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $patient = $this->makePatient();

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->payload($patient->user, $nurse, [
            'illness_ids' => [999999],
        ]))->assertSessionHasErrors();

        $this->assertSame(0, DocumentIssuance::count());
    }

    public function test_the_edit_page_shows_the_saved_picks_and_the_view_page_lists_them(): void
    {
        $doctor = $this->makeDoctor();
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $patient = $this->makePatient();

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->payload($patient->user, $nurse, [
            'illness_ids' => [$this->illness('Asthma')->id],
            'service_ids' => [$this->service('Nebulization only')->id],
        ]))->assertRedirect();
        $document = DocumentIssuance::firstOrFail();

        $edit = $this->get(route('doctors.document-issuances.edit', $document))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/name="illness_ids\[\]"\s+value="' . $this->illness('Asthma')->id . '"[^>]*checked/', $edit);
        $this->assertMatchesRegularExpression('/name="service_ids\[\]"\s+value="' . $this->service('Nebulization only')->id . '"[^>]*checked/', $edit);
        $this->assertStringContainsString('Cough/colds', $edit, 'the whole list is offered');

        $this->get(route('doctors.document-issuances.show', $document))->assertOk()
            ->assertSee('Asthma')->assertSee('Nebulization only');
    }

    public function test_old_consultations_get_their_ids_from_the_saved_names_or_the_patient_record(): void
    {
        $doctor = $this->makeDoctor();
        $campus = Campus::create(['campus_name' => 'Legacy Campus']);
        $college = College::create(['college_name' => 'Legacy College (LGC)']);
        $course = Course::create(['course_name' => 'Legacy Course']);
        $yearLevel = YearLevel::create(['year_level_name' => '3rd Year']);
        $patient = $this->makePatient();
        $patient->user->update(['college_id' => $college->id, 'campus_id' => $campus->id]);

        $named = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $doctor->id, 'user_id' => $patient->user_id,
            'name' => 'Juan Dela Cruz', 'age' => 21, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
            'campus' => 'Legacy Campus', 'college' => 'Legacy College (LGC)', 'course' => 'Legacy Course', 'year_level' => '3rd Year',
            'informant' => 'Student',
        ]);
        $unknown = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $doctor->id, 'user_id' => $patient->user_id,
            'name' => 'Juan Dela Cruz', 'age' => 21, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
            'campus' => 'Unknown Campus', 'college' => 'Unknown College', 'course' => 'Unknown Course', 'year_level' => 'Unknown Year Level',
            'informant' => 'Faculty',
        ]);

        app(ConsultationSnapshotBackfill::class)->run();

        $named->refresh();
        $this->assertSame([$campus->id, $college->id, $course->id, $yearLevel->id], [(int) $named->campus_id, (int) $named->college_id, (int) $named->course_id, (int) $named->year_level_id]);
        $this->assertSame((int) PatientType::where('code', 'student')->value('id'), (int) $named->patient_type_id);

        // Names that match nothing fall back to what the patient's record says, and the saved type label wins.
        $unknown->refresh();
        $this->assertSame($college->id, (int) $unknown->college_id);
        $this->assertSame($campus->id, (int) $unknown->campus_id);
        $this->assertSame((int) PatientType::where('code', 'faculty')->value('id'), (int) $unknown->patient_type_id);

        // Running it again changes nothing.
        app(ConsultationSnapshotBackfill::class)->run();
        $this->assertSame($college->id, (int) $named->fresh()->college_id);
    }

    public function test_the_university_physician_is_set_in_the_general_settings(): void
    {
        $admin = $this->makeAdmin();
        $this->seed(\Database\Seeders\SettingTableSeeder::class);

        $this->actingAs($admin)->post(route('setting.update'), [
            'sectionName' => 'general',
            'clinic_name' => 'Norsu Clinic', 'email' => 'clinic@test.local', 'contact_no' => '9171234567', 'specialties' => ['1'],
            'university_physician_name' => 'Dr. Michael S. Oliveros',
            'university_physician_title' => 'University Physician',
        ])->assertSessionMissing('errors');

        $this->assertSame('Dr. Michael S. Oliveros', Setting::where('key', 'university_physician_name')->value('value'));
        $this->assertSame('University Physician', Setting::where('key', 'university_physician_title')->value('value'));

        // The saved name is shown back in the form.
        $this->get(route('setting.index'))->assertOk()
            ->assertSee('name="university_physician_name"', false)
            ->assertSee('Dr. Michael S. Oliveros');
    }
}
