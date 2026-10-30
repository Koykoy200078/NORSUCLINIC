<?php

namespace Tests\Feature\Regression;

use App\Livewire\ReportGeneration;
use App\Models\College;
use App\Models\ConsultationMedicine;
use App\Models\Course;
use App\Models\DispenseRecord;
use App\Models\DocumentIssuance;
use App\Models\Illness;
use App\Models\Medicine;
use App\Models\PatientType;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\YearLevel;
use App\Services\MedsOnlyService;
use App\Services\Reports\ReportFilters;
use App\Services\Reports\ReportQueries;
use App\Services\Reports\VisitFeed;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Phase 11 C2: the "Visit type" filter of the Patient Visits tab - Consultations (the default, exactly as before), Meds only
 * (the patients who only came for medicine) or All (both in one list, newest first). The two lists share the filters that
 * both kinds of visit have (dates, campus, college, course, year level, patient type, medicine) and the word search (each
 * kind is searched in its own fields); the clinical filters (illness, service, nurse, pregnancy, chronic, age, gender,
 * department, office, walk-in / virtual) belong to consultations, so they are switched off outside "Consultations".
 *
 * Consultations: c1 2026-03-05 Cons Alpha (student, CTX, Cough/colds), c2 2026-03-09 Cons Beta (student, OTH, Hypertension,
 *   Paracetamol given), c3 2026-04-01 Cons Gamma (faculty, CTX).
 * Meds only: m1 2026-03-07 Meds Delta (student, CTX, BS Biology, 2nd Year, Biogesic), m2 2026-03-09 Meds Epsilon (student, OTH,
 *   Cetirizine), m3 2026-05-01 Meds Zeta (guest, Biogesic).
 */
class VisitTypeFilterTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $admin;

    private User $nurse;

    private User $doctor;

    private College $ctx;

    private College $oth;

    private Medicine $biogesic;

    private Medicine $cetirizine;

    private Medicine $paracetamol;

    private Course $biology;

    private YearLevel $second;

    /** @var array<string, DocumentIssuance|DispenseRecord> */
    private array $v = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->admin = $this->makeAdmin(['first_name' => 'Ada', 'last_name' => 'Admin']);
        $this->nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse']);
        $this->doctor = $this->makeDoctor(['first_name' => 'Dan', 'last_name' => 'Doctor']);

        $this->ctx = College::create(['college_name' => 'College of Test (CTX)']);
        $this->oth = College::create(['college_name' => 'College of Other (OTH)']);
        $this->biogesic = $this->makeMedicine('Biogesic', ['dosage' => '500mg', 'uom' => 'tablet']);
        $this->cetirizine = $this->makeMedicine('Cetirizine', ['dosage' => '10mg', 'uom' => 'tablet']);
        $this->paracetamol = $this->makeMedicine('Paracetamol', ['dosage' => '500mg', 'uom' => 'tablet']);
        $this->stockIn($this->biogesic, 100, now()->addYear()->toDateString(), '500mg', 'B-1');
        $this->stockIn($this->cetirizine, 100, now()->addYear()->toDateString(), '10mg', 'C-1');

        $this->biology = Course::create(['course_name' => 'BS Biology']);
        $this->second = YearLevel::create(['year_level_name' => '2nd Year']);
        $patientUser = $this->makePatient()->user;

        $consult = function (string $name, string $date, string $type, ?College $college, array $illnesses = [], array $attributes = []) use ($patientUser) {
            $visit = DocumentIssuance::create(array_merge([
                'document_type' => 'consultation_form', 'document_creator_id' => $this->nurse->id, 'user_id' => $patientUser->id,
                'name' => $name, 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => $date,
                'consult_mode' => 'physical', 'informant' => ucfirst($type), 'patient_type_id' => $this->type($type), 'college_id' => $college?->id,
            ], $attributes));
            $visit->illnesses()->sync(Illness::whereIn('name', $illnesses)->pluck('id')->all());

            return $visit;
        };
        $this->v['c1'] = $consult('Cons Alpha', '2026-03-05', 'student', $this->ctx, ['Cough/colds']);
        $this->v['c1']->services()->sync(ServiceType::where('name', 'Tetanus injection')->pluck('id')->all());
        $this->v['c2'] = $consult('Cons Beta', '2026-03-09', 'student', $this->oth, ['Hypertension']);
        $this->v['c3'] = $consult('Cons Gamma', '2026-04-01', 'faculty', $this->ctx);
        ConsultationMedicine::create(['request_document_id' => $this->v['c2']->id, 'medicine_id' => $this->paracetamol->id, 'dosage' => '500mg', 'quantity' => 2, 'used_for' => 'nursing_intervention']);

        $this->actingAs($this->nurse);
        $service = app(MedsOnlyService::class);
        $meds = fn (string $name, string $date, string $type, ?College $college, Medicine $medicine, string $strength, array $extra = []) => $service->create(array_merge([
            'bill_date' => $date, 'patient_name' => $name, 'patient_type_id' => $this->type($type), 'college_id' => $college?->id,
            'medicines' => [['medicine_id' => $medicine->id, 'dosage' => $strength, 'quantity' => 2]],
        ], $extra), $this->nurse);
        $this->v['m1'] = $meds('Meds Delta', '2026-03-07', 'student', $this->ctx, $this->biogesic, '500mg', ['course_id' => $this->biology->id, 'year_level_id' => $this->second->id]);
        $this->v['m2'] = $meds('Meds Epsilon', '2026-03-09', 'student', $this->oth, $this->cetirizine, '10mg');
        $this->v['m3'] = $meds('Meds Zeta', '2026-05-01', 'guest', null, $this->biogesic, '500mg');
    }

    private function type(string $code): int
    {
        return (int) PatientType::where('code', $code)->value('id');
    }

    /** The names of the visits, in the order the tab lists them. */
    private function names(array $query): array
    {
        $filters = ReportFilters::fromArray($query);
        $rows = ReportQueries::forTab('visits', $filters)->get();

        return $filters->visitType === 'consultations'
            ? $rows->pluck('name')->all()
            : VisitFeed::hydrate($rows)->map(fn (object $row) => $row->name)->all();
    }

    // ------------------------------------------------------------------ the list

    public function test_without_a_visit_type_the_tab_lists_consultations_exactly_as_before(): void
    {
        $query = ReportQueries::forTab('visits', ReportFilters::fromArray([]));

        $this->assertInstanceOf(EloquentBuilder::class, $query, 'the consultations list is still the consultations query');
        $this->assertSame(['Cons Gamma', 'Cons Beta', 'Cons Alpha'], $this->names([]));
        $this->assertSame(['Cons Gamma', 'Cons Beta', 'Cons Alpha'], $this->names(['visit_type' => 'consultations']));
        $this->assertSame(['Cons Gamma', 'Cons Beta', 'Cons Alpha'], $this->names(['visit_type' => 'nonsense']), 'an unknown value is the default');
    }

    public function test_meds_only_lists_the_patients_who_only_came_for_medicine_newest_first(): void
    {
        $this->assertSame(['Meds Zeta', 'Meds Epsilon', 'Meds Delta'], $this->names(['visit_type' => 'meds_only']));
    }

    public function test_all_lists_both_kinds_newest_first_and_a_consultation_before_a_meds_only_visit_of_the_same_day(): void
    {
        $this->assertSame(
            ['Meds Zeta', 'Cons Gamma', 'Cons Beta', 'Meds Epsilon', 'Meds Delta', 'Cons Alpha'],
            $this->names(['visit_type' => 'all']),
            '2026-03-09 has Cons Beta and Meds Epsilon: the consultation comes first'
        );
    }

    public function test_visits_of_the_same_day_and_kind_come_newest_record_first(): void
    {
        $this->actingAs($this->nurse);
        $make = fn (string $name) => app(MedsOnlyService::class)->create(['bill_date' => '2026-07-01', 'patient_name' => $name, 'patient_type_id' => $this->type('student'),
            'medicines' => [['medicine_id' => $this->biogesic->id, 'dosage' => '500mg', 'quantity' => 1]]], $this->nurse);
        $make('Same Day A');
        $make('Same Day B');

        $this->assertSame(['Same Day B', 'Same Day A'], array_slice($this->names(['visit_type' => 'meds_only']), 0, 2));
        $this->assertSame(['Same Day B', 'Same Day A'], array_slice($this->names(['visit_type' => 'all']), 0, 2));
    }

    public function test_a_page_skips_a_record_that_is_gone_between_the_query_and_the_page(): void
    {
        $rows = collect([
            (object) ['kind' => VisitFeed::CONSULTATION, 'source_id' => 999999, 'visit_date' => '2026-03-05'],
            (object) ['kind' => VisitFeed::MEDS_ONLY, 'source_id' => $this->v['m1']->id, 'visit_date' => '2026-03-07'],
            (object) ['kind' => VisitFeed::MEDS_ONLY, 'source_id' => 999998, 'visit_date' => '2026-03-08'],
        ]);

        $this->assertSame(['Meds Delta'], VisitFeed::hydrate($rows)->pluck('name')->all());
        $this->assertSame([], VisitFeed::hydrate(collect())->all());
    }

    public function test_manual_dispense_records_and_deleted_consultations_are_not_visits(): void
    {
        $manual = DispenseRecord::create(['history_number' => 'HIS-MANUAL', 'patient_id' => $this->makePatient(['first_name' => 'Manual', 'last_name' => 'Person'])->id,
            'model_type' => DispenseRecord::class, 'bill_date' => '2026-03-11']);
        $this->v['c3']->delete();

        $names = $this->names(['visit_type' => 'all']);
        $this->assertNotContains('Cons Gamma', $names);
        $this->assertCount(5, $names);
        $this->assertSame(3, count($this->names(['visit_type' => 'meds_only'])), 'the manual record is not Meds Only: '.$manual->id);
    }

    // ------------------------------------------------------------------ the filters

    public function test_the_filters_both_kinds_have_narrow_both_lists(): void
    {
        $all = ['visit_type' => 'all'];

        $this->assertSame(['Cons Beta', 'Meds Epsilon', 'Meds Delta'], $this->names($all + ['date_from' => '2026-03-06', 'date_to' => '2026-03-31']), 'dates, both ends in');
        $this->assertSame(['Cons Gamma', 'Meds Delta', 'Cons Alpha'], $this->names($all + ['college_id' => $this->ctx->id]));
        $this->assertSame(['Meds Delta'], $this->names($all + ['course_id' => $this->biology->id]));
        $this->assertSame(['Meds Delta'], $this->names($all + ['year_level_id' => $this->second->id]));
        $this->assertSame(['Cons Beta', 'Meds Epsilon', 'Meds Delta', 'Cons Alpha'], $this->names($all + ['patient_type_id' => $this->type('student')]));
        $this->assertSame(['Meds Zeta'], $this->names($all + ['patient_type_id' => $this->type('guest')]));
        // The medicine given: on a consultation it is the medicine of the consultation, on a Meds Only visit the drugs of the record.
        $this->assertSame(['Cons Beta'], $this->names($all + ['medicine_id' => $this->paracetamol->id]));
        $this->assertSame(['Meds Zeta', 'Meds Delta'], $this->names($all + ['medicine_id' => $this->biogesic->id]));
        $this->assertSame(['Meds Zeta', 'Meds Delta'], $this->names(['visit_type' => 'meds_only', 'medicine_id' => $this->biogesic->id]));
        $this->assertSame(['Cons Gamma', 'Meds Delta'], $this->names($all + ['college_id' => $this->ctx->id, 'date_from' => '2026-03-06']), 'filters are combined');
    }

    public function test_the_word_search_looks_in_the_fields_of_each_kind(): void
    {
        $all = ['visit_type' => 'all'];

        $this->assertSame(['Meds Delta'], $this->names($all + ['search' => 'Delta']));
        $this->assertSame(['Cons Alpha'], $this->names($all + ['search' => 'Alpha']));
        $this->assertSame(['Cons Beta'], $this->names($all + ['search' => 'hypertension']), 'an illness picked on the consultation');
        $this->assertSame(['Meds Zeta', 'Meds Delta'], $this->names($all + ['search' => 'biogesic']), 'a drug given to a Meds Only visit');
        $this->assertSame([], $this->names($all + ['search' => 'meds alpha']), 'every word must be found: no record has both');
        $this->assertSame(['Meds Zeta', 'Meds Epsilon', 'Meds Delta'], $this->names(['visit_type' => 'meds_only', 'search' => 'meds']));
        $this->assertSame([], $this->names($all + ['search' => 'nobody has this']));
    }

    public function test_the_clinical_filters_are_switched_off_outside_consultations_and_still_work_inside_it(): void
    {
        $illness = Illness::where('name', 'Cough/colds')->first();
        $clinical = [
            'illness_id' => $illness->id, 'illness_system_id' => $illness->illness_system_id, 'service_id' => ServiceType::first()->id,
            'staff_id' => $this->admin->id, 'pregnancy' => 'pregnant', 'chronic' => 'with', 'chronic_text' => 'asthma', 'age_group' => 'senior',
            'gender' => 'Female', 'department_id' => 1, 'office_id' => 1, 'consult_mode' => 'virtual',
        ];

        foreach (['all', 'meds_only'] as $type) {
            $this->assertSame($this->names(['visit_type' => $type]), $this->names(['visit_type' => $type] + $clinical), "ignored in {$type}");
        }
        // "Not classified yet" is one more way to ask about illnesses.
        foreach (['all', 'meds_only'] as $type) {
            $this->assertSame($this->names(['visit_type' => $type]), $this->names(['visit_type' => $type, 'illness_id' => 'none']), "not classified ignored in {$type}");
        }
        $this->assertSame(['Cons Gamma'], $this->names(['illness_id' => 'none']), 'consultations: only Cons Gamma has no illness');
        // Inside Consultations they narrow the list as before (nothing here is a pregnant Female senior with asthma).
        $this->assertSame([], $this->names($clinical));
        $this->assertSame(['Cons Alpha'], $this->names(['illness_id' => $illness->id]));
    }

    public function test_the_filter_object_knows_which_filters_count_in_each_mode(): void
    {
        $illness = Illness::where('name', 'Cough/colds')->first();
        $consultations = ReportFilters::fromArray(['illness_id' => $illness->id, 'college_id' => $this->ctx->id]);
        $all = ReportFilters::fromArray(['illness_id' => $illness->id, 'college_id' => $this->ctx->id, 'visit_type' => 'all']);

        $this->assertSame(2, $consultations->activeConsultationFilters());
        $this->assertSame(2, $all->activeConsultationFilters(), 'the visit type itself is a filter; the switched-off illness filter is not counted');
        $this->assertSame(['visit_type' => 'all', 'college_id' => $this->ctx->id], array_intersect_key($all->toQuery(), ['visit_type' => 1, 'college_id' => 1]));
        $this->assertArrayNotHasKey('visit_type', $consultations->toQuery(), 'the default is not carried in links');
    }

    // ------------------------------------------------------------------ paging, screen, CSV

    public function test_the_mixed_list_pages_and_counts_like_any_other_tab(): void
    {
        for ($day = 1; $day <= 25; $day++) {
            $this->actingAs($this->nurse);
            app(MedsOnlyService::class)->create(['bill_date' => '2026-06-' . str_pad((string) $day, 2, '0', STR_PAD_LEFT), 'patient_name' => 'Bulk ' . $day,
                'patient_type_id' => $this->type('student'), 'medicines' => [['medicine_id' => $this->biogesic->id, 'dosage' => '500mg', 'quantity' => 1]]], $this->nurse);
        }

        $page = ReportQueries::forTab('visits', ReportFilters::fromArray(['visit_type' => 'all']))->paginate(20);
        $second = ReportQueries::forTab('visits', ReportFilters::fromArray(['visit_type' => 'all']))->paginate(20, ['*'], 'page', 2);

        $this->assertSame(31, $page->total());
        $this->assertCount(20, $page->items());
        $this->assertCount(11, $second->items());
        $this->assertSame('Bulk 25', VisitFeed::hydrate($page->getCollection())->first()->name, 'newest first');
        $this->assertSame('Cons Alpha', VisitFeed::hydrate($second->getCollection())->last()->name, 'oldest last, nothing repeated or lost between the pages');
        $this->assertSame(31, VisitFeed::hydrate($page->getCollection())->merge(VisitFeed::hydrate($second->getCollection()))->pluck('key')->unique()->count());
    }

    public function test_the_screen_has_the_visit_type_filter_and_labels_what_it_lists(): void
    {
        $this->actingAs($this->admin);

        $default = Livewire::withQueryParams(['tab' => 'visits'])->test(ReportGeneration::class)
            ->assertSee('Cons Alpha')->assertDontSee('Meds Delta')
            ->assertSee('Visit type');
        $this->assertSame('consultations', $default->get('visit_type'));

        $all = Livewire::withQueryParams(['tab' => 'visits'])->test(ReportGeneration::class)
            ->set('visit_type', 'all')
            ->assertSee('Cons Alpha')->assertSee('Meds Delta')
            ->assertSeeHtml('<span class="badge bg-warning text-dark">Meds only</span>')
            ->assertSeeHtml('<span class="badge bg-primary">Consultation</span>')
            ->assertSee('6 visits found');
        $this->assertStringContainsString('Biogesic', $all->html(), 'the drugs of a Meds Only visit');

        Livewire::withQueryParams(['tab' => 'visits'])->test(ReportGeneration::class)
            ->set('visit_type', 'meds_only')
            ->assertSee('Meds Delta')->assertDontSee('Cons Alpha')->assertSee('3 visits found')
            ->call('resetFilters')
            ->assertSet('visit_type', 'consultations')
            ->assertSee('Cons Alpha')->assertDontSee('Meds Delta');
    }

    public function test_a_link_is_only_drawn_for_a_module_the_role_may_use(): void
    {
        $this->actingAs($this->nurse);
        $consultationLink = e(route('staff.document-issuances.show', $this->v['c1']->id));
        $medsLink = e(route('staff.meds-only.show', $this->v['m1']->id));

        \App\Models\Role::findByName('staff')->revokePermissionTo('manage_medicines');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $html = Livewire::withQueryParams(['tab' => 'visits', 'visit_type' => 'all'])->test(ReportGeneration::class)->assertSee('Meds Delta')->html();
        $this->assertStringContainsString($consultationLink, $html);
        $this->assertStringNotContainsString($medsLink, $html, 'no Meds Only module for this role, so no link to it');

        \App\Models\Role::findByName('staff')->givePermissionTo('manage_medicines')->revokePermissionTo('manage_request_documents');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $html = Livewire::withQueryParams(['tab' => 'visits', 'visit_type' => 'all'])->test(ReportGeneration::class)->assertSee('Cons Alpha')->html();
        $this->assertStringNotContainsString($consultationLink, $html);
        $this->assertStringContainsString($medsLink, $html);
    }

    public function test_the_clinical_filter_boxes_are_hidden_for_the_mixed_list_and_the_other_tabs_ignore_the_visit_type(): void
    {
        $this->actingAs($this->admin);
        $clinical = ['Nurse in charge / encoder', 'Department (faculty)', 'Office (staff)', 'Chronic condition contains', 'Body system', 'Service rendered', 'Consult mode', 'Age group'];

        $default = Livewire::withQueryParams(['tab' => 'visits'])->test(ReportGeneration::class);
        foreach ($clinical as $label) {
            $default->assertSee($label);
        }
        $default->assertSee('Campus')->assertSee('Medicine given');

        $mixed = Livewire::withQueryParams(['tab' => 'visits', 'visit_type' => 'all'])->test(ReportGeneration::class);
        foreach ($clinical as $label) {
            $mixed->assertDontSee($label);
        }
        $mixed->assertSee('Campus')->assertSee('Medicine given')->assertSee('Patient type')->assertSee('Visit type');

        // The Accomplishment Report and the other tabs are not Patient Visits: the visit type does nothing there.
        $accomplishment = Livewire::withQueryParams(['tab' => 'visits', 'visit_type' => 'all'])->test(ReportGeneration::class)->call('setTab', 'accomplishment');
        $this->assertArrayNotHasKey('visit_type', $accomplishment->viewData('exportQuery'));
        $this->assertSame(1, $accomplishment->viewData('activeFilters'), 'only the walk-in default of the report');
        $this->assertFalse($accomplishment->viewData('mixedVisits'));
    }

    public function test_the_visit_type_is_kept_in_the_address_of_the_page(): void
    {
        $queryString = (new \ReflectionProperty(ReportGeneration::class, 'queryString'))->getDefaultValue();

        $this->assertSame(['except' => 'consultations'], $queryString['visit_type']);
    }

    public function test_the_links_of_a_mixed_row_open_the_consultation_or_the_meds_only_record(): void
    {
        $this->actingAs($this->nurse);

        $html = Livewire::withQueryParams(['tab' => 'visits', 'visit_type' => 'all'])->test(ReportGeneration::class)->html();

        $this->assertStringContainsString(e(route('staff.document-issuances.show', $this->v['c1']->id)), $html);
        $this->assertStringContainsString(e(route('staff.meds-only.show', $this->v['m1']->id)), $html);
    }

    public function test_a_doctor_sees_the_meds_only_visits_as_a_read_only_list(): void
    {
        $this->actingAs($this->doctor);

        $html = Livewire::withQueryParams(['tab' => 'visits', 'visit_type' => 'meds_only'])->test(ReportGeneration::class)->assertSee('Meds Delta')->html();

        $this->assertStringContainsString(e(route('doctors.meds-only.show', $this->v['m1']->id)), $html);
        $this->assertStringNotContainsString('meds-only/' . $this->v['m1']->id . '/edit', $html);
    }

    public function test_the_csv_holds_the_rows_of_the_screen_and_follows_the_visit_type_and_the_filters(): void
    {
        $this->actingAs($this->admin);

        $read = function (array $query) {
            $csv = $this->get(route('activity-logs.export', ['tab' => 'visits'] + $query))->assertOk()->streamedContent();

            return array_map('str_getcsv', preg_split('/\r\n|\n/', trim(substr($csv, 3))));
        };

        // Default: the unchanged consultation CSV (18 columns).
        $default = $read([]);
        $this->assertSame('Patient Name', $default[0][1]);
        $this->assertCount(4, $default, 'header and three consultations');

        $all = $read(['visit_type' => 'all']);
        $this->assertSame(['Date', 'Type', 'Patient Name', 'Patient Type', 'Campus', 'College', 'Course', 'Year Level', 'Illness / Services / Drugs', 'Recorded By'], $all[0]);
        $this->assertSame(['Meds Zeta', 'Cons Gamma', 'Cons Beta', 'Meds Epsilon', 'Meds Delta', 'Cons Alpha'], array_column(array_slice($all, 1), 2), 'the order of the screen');
        $this->assertSame(['Meds only', 'Consultation', 'Consultation', 'Meds only', 'Meds only', 'Consultation'], array_column(array_slice($all, 1), 1));
        $delta = collect($all)->first(fn ($row) => ($row[2] ?? null) === 'Meds Delta');
        $this->assertSame('2026-03-07', $delta[0]);
        $this->assertStringContainsString('Biogesic', $delta[8]);
        $this->assertSame('Nora Nurse', $delta[9]);
        $this->assertSame(['College of Test (CTX)', 'BS Biology', '2nd Year', 'Student'], [$delta[5], $delta[6], $delta[7], $delta[3]], 'the college, course, year level and patient type of the record');
        $alpha = collect($all)->first(fn ($row) => ($row[2] ?? null) === 'Cons Alpha');
        $this->assertSame('Cough/colds; Tetanus injection', $alpha[8], 'the illness and the services of a consultation');
        $this->assertSame(['2026-03-05', 'Student', 'Nora Nurse'], [$alpha[0], $alpha[3], $alpha[9]]);

        $filtered = $read(['visit_type' => 'all', 'college_id' => $this->ctx->id, 'search' => 'biogesic']);
        $this->assertSame(['Meds Delta'], array_column(array_slice($filtered, 1), 2));

        $meds = $read(['visit_type' => 'meds_only']);
        $this->assertSame(['Meds Zeta', 'Meds Epsilon', 'Meds Delta'], array_column(array_slice($meds, 1), 2));
    }

    public function test_a_name_that_looks_like_a_formula_is_not_run_by_excel_in_the_csv_of_the_mixed_list(): void
    {
        $this->actingAs($this->admin);
        $this->actingAs($this->nurse);
        app(MedsOnlyService::class)->create(['bill_date' => '2026-06-30', 'patient_name' => '=HYPERLINK("http://x")', 'patient_type_id' => $this->type('student'),
            'medicines' => [['medicine_id' => $this->biogesic->id, 'dosage' => '500mg', 'quantity' => 1]]], $this->nurse);
        $this->actingAs($this->admin);

        $csv = $this->get(route('activity-logs.export', ['tab' => 'visits', 'visit_type' => 'all']))->assertOk()->streamedContent();

        $this->assertStringContainsString("\"'=HYPERLINK(", $csv);
    }
}
