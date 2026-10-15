<?php

namespace Tests\Feature\Regression;

use App\Livewire\ReportGeneration;
use App\Models\College;
use App\Models\DocumentIssuance;
use App\Models\Illness;
use App\Models\PatientType;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\Reports\AccomplishmentReportBuilder;
use App\Services\Reports\ReportFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan item 5.23: in the ACCOMPLISHMENT REPORT the typed names of an "Others" line are listed as sub-rows under it,
 * each with its own count per column. Names that differ only by case, extra spaces or end punctuation are one line.
 * The "Others" row keeps the total of its sub-rows, so the section and grand totals do not change.
 */
class OtherNamesReportTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $admin;

    private User $nurse;

    private User $patientUser;

    private College $ctx;

    private College $oth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();

        $this->admin = $this->makeAdmin(['first_name' => 'Ada', 'last_name' => 'Admin']);
        $this->nurse = $this->makeStaff('nurse', 'triage_area');
        $this->patientUser = $this->makePatient()->user;
        $this->ctx = College::create(['college_name' => 'College of Test (CTX)']);
        $this->oth = College::create(['college_name' => 'College of Other (OTH)']);
    }

    private function skeletalOthers(): Illness
    {
        return Illness::where('is_other', true)->whereHas('system', fn ($q) => $q->where('name', 'Skeletal System'))->firstOrFail();
    }

    private function eentOthers(): Illness
    {
        return Illness::where('is_other', true)->whereHas('system', fn ($q) => $q->where('name', 'EENT'))->firstOrFail();
    }

    private function procedureOthers(): ServiceType
    {
        return ServiceType::where('is_other', true)->where('category', 'clinical_procedure')->firstOrFail();
    }

    private function promotionOthers(): ServiceType
    {
        return ServiceType::where('is_other', true)->where('category', 'health_promotion')->firstOrFail();
    }

    /**
     * @param  array<int, array{0: Illness, 1: ?string}>  $illnesses  illness line + typed name
     * @param  array<int, array{0: ServiceType, 1: ?string}>  $services
     */
    private function visit(College $college, array $illnesses = [], array $services = [], array $attributes = []): DocumentIssuance
    {
        $visit = DocumentIssuance::create(array_merge([
            'document_type' => 'consultation_form', 'document_creator_id' => $this->nurse->id, 'user_id' => $this->patientUser->id,
            'name' => 'Juan Dela Cruz', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete',
            'requested_at' => '2026-03-10', 'consult_mode' => 'physical', 'informant' => 'Student',
            'patient_type_id' => (int) PatientType::where('code', 'student')->value('id'), 'college_id' => $college->id,
        ], $attributes));

        foreach ($illnesses as [$illness, $text]) {
            $visit->illnesses()->attach($illness->id, ['other_text' => $text]);
        }
        foreach ($services as [$service, $text]) {
            $visit->services()->attach($service->id, ['other_text' => $text]);
        }

        return $visit;
    }

    private function scenario(): void
    {
        $skeletal = $this->skeletalOthers();
        $cough = Illness::where('name', 'Cough/colds')->firstOrFail();

        $this->visit($this->ctx, [[$skeletal, 'Lump on leg']], [[$this->procedureOthers(), 'Ear syringing']]);
        $this->visit($this->ctx, [[$skeletal, 'lump on  leg.']], [[$this->procedureOthers(), 'EAR SYRINGING']]);
        $this->visit($this->oth, [[$skeletal, ' LUMP ON LEG ']], [[$this->procedureOthers(), 'ear  syringing.']]);
        $this->visit($this->ctx, [[$skeletal, 'Sprain']], [[$this->procedureOthers(), 'Eye wash']]);
        $this->visit($this->oth, [[$skeletal, null]], [[$this->procedureOthers(), null]]);
        $this->visit($this->ctx, [[$this->eentOthers(), 'Dengue'], [$cough, null]], [[$this->promotionOthers(), 'Talk on hygiene']]);
        $this->visit($this->oth, [[$this->eentOthers(), 'dengue']]);
        $this->visit($this->ctx, [[$this->eentOthers(), 'Dengue']]);
    }

    private function build(array $query = []): array
    {
        return app(AccomplishmentReportBuilder::class)->build(ReportFilters::fromArray($query), $this->nurse);
    }

    /** @return array<string, mixed>|null the "Others" row of an illness body system */
    private function illnessOthersRow(array $report, string $system): ?array
    {
        foreach ($report['illness_sections'] as $section) {
            if ($section['name'] !== $system) {
                continue;
            }
            foreach ($section['groups'] as $group) {
                foreach ($group['rows'] as $row) {
                    if ($row['is_other']) {
                        return $row;
                    }
                }
            }
        }

        return null;
    }

    private function serviceOthersRow(array $report, string $category): ?array
    {
        foreach ($report['service_sections'] as $section) {
            if ($section['name'] === $category) {
                foreach ($section['rows'] as $row) {
                    if ($row['is_other']) {
                        return $row;
                    }
                }
            }
        }

        return null;
    }

    /** @return array<string, array<string, int>> sub-row name => counts + _total */
    private function sub(?array $row): array
    {
        $out = [];
        foreach ($row['others'] ?? [] as $sub) {
            $counts = $sub['counts'];
            ksort($counts);
            $out[$sub['name']] = $counts + ['_total' => $sub['total']];
        }

        return $out;
    }

    public function test_the_typed_illness_names_are_sub_rows_under_others_grouped_and_counted_per_column(): void
    {
        $this->scenario();
        $report = $this->build();
        $row = $this->illnessOthersRow($report, 'Skeletal System');

        $this->assertSame(
            [
                'Lump on leg' => ['c' . $this->ctx->id => 2, 'c' . $this->oth->id => 1, '_total' => 3],
                'Sprain' => ['c' . $this->ctx->id => 1, '_total' => 1],
                '(no name given)' => ['c' . $this->oth->id => 1, '_total' => 1],
            ],
            $this->sub($row),
            'three spellings are one line, highest count first, a record without a name last'
        );
        $this->assertSame(['Lump on leg', 'Sprain', '(no name given)'], array_keys($this->sub($row)));

        // The Others row keeps the total of its sub-rows, so nothing else changes.
        $this->assertSame(5, $row['total']);
        $this->assertSame($row['total'], array_sum(array_column($row['others'], 'total')));
        foreach (['c' . $this->ctx->id, 'c' . $this->oth->id] as $key) {
            $this->assertSame($row['counts'][$key], array_sum(array_map(fn ($sub) => $sub['counts'][$key] ?? 0, $row['others'])));
        }

        // Another body system keeps its own names: "Dengue", "dengue" and "Dengue" are one line of 3.
        $eent = $this->illnessOthersRow($report, 'EENT');
        $this->assertSame(['Dengue' => ['c' . $this->ctx->id => 2, 'c' . $this->oth->id => 1, '_total' => 3]], $this->sub($eent));

        // An ordinary line has no sub-rows.
        $this->assertArrayNotHasKey('others', $this->illnessRowByName($report, 'Cough/colds'));
    }

    private function illnessRowByName(array $report, string $name): array
    {
        foreach ($report['illness_sections'] as $section) {
            foreach ($section['groups'] as $group) {
                foreach ($group['rows'] as $row) {
                    if ($row['name'] === $name) {
                        return $row;
                    }
                }
            }
        }
        $this->fail("no row {$name}");
    }

    public function test_the_typed_service_names_are_sub_rows_too(): void
    {
        $this->scenario();
        $report = $this->build();

        $procedures = $this->serviceOthersRow($report, 'Clinical procedures');
        $this->assertSame(
            [
                'Ear syringing' => ['c' . $this->ctx->id => 2, 'c' . $this->oth->id => 1, '_total' => 3],
                'Eye wash' => ['c' . $this->ctx->id => 1, '_total' => 1],
                '(no name given)' => ['c' . $this->oth->id => 1, '_total' => 1],
            ],
            $this->sub($procedures)
        );
        $this->assertSame(5, $procedures['total']);

        $promotion = $this->serviceOthersRow($report, 'Health promotion and wellness');
        $this->assertSame(['Talk on hygiene' => ['c' . $this->ctx->id => 1, '_total' => 1]], $this->sub($promotion));

        // Section and grand totals are what they were: the sub-rows are not counted again.
        foreach ($report['service_sections'] as $section) {
            $this->assertSame($section['total'], array_sum(array_column($section['rows'], 'total')), $section['name']);
        }
        $this->assertSame(array_sum(array_column($report['service_sections'], 'total')), $report['service_total']['total']);
        $this->assertSame(array_sum(array_column($report['illness_sections'], 'total')), $report['illness_total']['total']);
    }

    public function test_the_most_common_spelling_is_shown_and_a_tie_goes_to_the_first_one_typed(): void
    {
        $skeletal = $this->skeletalOthers();
        $this->visit($this->ctx, [[$skeletal, 'dengue']]);
        $this->visit($this->ctx, [[$skeletal, 'Dengue']]);
        $this->visit($this->ctx, [[$skeletal, 'dengue']]);
        $this->visit($this->ctx, [[$skeletal, 'AA']]);
        $this->visit($this->ctx, [[$skeletal, 'aA']]);

        $names = array_keys($this->sub($this->illnessOthersRow($this->build(), 'Skeletal System')));
        $this->assertSame(['dengue', 'AA'], $names, 'dengue x2 beats Dengue x1; AA and aA tie, the first typed (AA) is shown');
    }

    public function test_the_filters_of_the_report_apply_to_the_sub_rows(): void
    {
        $this->scenario();

        $row = $this->illnessOthersRow($this->build(['college_id' => $this->oth->id]), 'Skeletal System');
        $this->assertSame(['LUMP ON LEG' => ['c' . $this->oth->id => 1, '_total' => 1], '(no name given)' => ['c' . $this->oth->id => 1, '_total' => 1]], $this->sub($row), 'only that college: its own spelling is the one shown');

        $row = $this->illnessOthersRow($this->build(['date_from' => '2026-04-01']), 'Skeletal System');
        $this->assertSame([0, []], [$row['total'], $row['others']], 'no visit after March: the Others row is empty and has no names');

        // An illness filter keeps only that line and its names, also when the same visit has other "Others" lines.
        $this->visit($this->ctx, [[$this->eentOthers(), 'Dengue'], [$this->skeletalOthers(), 'Fracture of the wrist']]);
        $report = $this->build(['illness_id' => $this->eentOthers()->id]);
        $this->assertSame(['Dengue'], array_keys($this->sub($this->illnessOthersRow($report, 'EENT'))));
        $this->assertNull($this->illnessOthersRow($report, 'Skeletal System'));

        // A service filter keeps only that service and its names.
        $report = $this->build(['service_id' => $this->promotionOthers()->id]);
        $this->assertSame(['Talk on hygiene'], array_keys($this->sub($this->serviceOthersRow($report, 'Health promotion and wellness'))));
    }

    public function test_a_college_that_no_longer_exists_is_counted_under_unspecified_also_for_the_names(): void
    {
        $this->visit($this->ctx, [[$this->skeletalOthers(), 'Sprain']], [], ['college_id' => 987654]);

        $row = $this->illnessOthersRow($this->build(), 'Skeletal System');
        $this->assertSame(['Sprain' => ['unspecified' => 1, '_total' => 1]], $this->sub($row));
        $this->assertSame(['unspecified' => 1], $row['counts'], 'the Others row agrees');
    }

    public function test_the_yearly_report_has_the_names_per_quarter(): void
    {
        $skeletal = $this->skeletalOthers();
        $this->visit($this->ctx, [[$skeletal, 'Lump']], [], ['requested_at' => '2026-02-01']);
        $this->visit($this->ctx, [[$skeletal, 'lump.']], [], ['requested_at' => '2026-11-01']);
        $this->visit($this->ctx, [[$skeletal, 'Lump']], [], ['requested_at' => '2025-12-31']);

        $row = $this->illnessOthersRow($this->build(['year' => '2026']), 'Skeletal System');
        $this->assertSame(['Lump' => ['q1' => 1, 'q4' => 1, '_total' => 2]], $this->sub($row));
    }

    public function test_the_screen_shows_the_names_under_others(): void
    {
        $this->scenario();
        $this->actingAs($this->admin);

        Livewire::withQueryParams(['tab' => 'accomplishment', 'date_from' => '2026-03-01', 'date_to' => '2026-03-31'])
            ->test(ReportGeneration::class)
            ->assertSeeInOrder(['Others', 'Lump on leg', 'Sprain', '(no name given)'])
            ->assertSeeInOrder(['Others', 'Ear syringing', 'Eye wash'])
            ->assertSee('Talk on hygiene')
            ->assertDontSee('EAR SYRINGING');
    }

    public function test_the_csv_the_excel_sheet_and_the_pdf_carry_the_names_as_well(): void
    {
        $this->scenario();
        $this->visit($this->ctx, [[$this->skeletalOthers(), '=HYPERLINK("http://x","y")']]);
        $this->actingAs($this->admin);

        // CSV: one line per name, "Others: <name>" in the Item column, the same counts and total.
        $csv = $this->get(route('activity-logs.accomplishment', ['format' => 'csv']))->assertOk()->streamedContent();
        $lines = array_map('str_getcsv', preg_split('/\r\n|\n/', trim(substr($csv, 3))));
        $line = collect($lines)->first(fn ($l) => ($l[3] ?? null) === 'Others: Lump on leg');
        $this->assertNotNull($line, 'a line for the name');
        $this->assertSame('Illness / diagnosis', $line[0]);
        $this->assertSame('Skeletal System', $line[1]);
        $this->assertSame('3', $line[count($line) - 1]);
        $this->assertNotNull(collect($lines)->first(fn ($l) => ($l[3] ?? null) === 'Others: (no name given)' && $l[0] === 'Other services'));
        $this->assertNotNull(collect($lines)->first(fn ($l) => ($l[3] ?? null) === 'Others: Ear syringing' && $l[0] === 'Other services'));
        $this->assertNull(collect($lines)->first(fn ($l) => str_contains((string) ($l[3] ?? ''), 'EAR SYRINGING')), 'one line per name');

        // Excel: the names are in the sheet, a name that looks like a formula is plain text.
        $excel = $this->get(route('activity-logs.accomplishment', ['format' => 'xlsx']))->assertOk();
        $sheet = IOFactory::load($excel->baseResponse->getFile()->getPathname())->getActiveSheet();
        $text = collect($sheet->toArray())->flatten()->filter()->implode('|');
        $this->assertStringContainsString('Lump on leg', $text);
        $this->assertStringContainsString('Ear syringing', $text);
        $this->assertStringContainsString("'=HYPERLINK", $text);
        foreach ($sheet->getCellCollection()->getCoordinates() as $coordinate) {
            $this->assertNotSame('f', $sheet->getCell($coordinate)->getDataType(), "{$coordinate} must not be a formula");
        }

        // PDF: it is produced (names with quotes, equals signs and the like do not break it).
        $pdf = $this->get(route('activity-logs.accomplishment', ['format' => 'pdf']))->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    public function test_very_long_and_special_names_do_not_break_any_output(): void
    {
        $skeletal = $this->skeletalOthers();
        $this->visit($this->ctx, [[$skeletal, str_repeat('Ñandú <b>&</b> "x" ', 7)]]);
        $this->visit($this->ctx, [[$skeletal, "Tab\tand\nnewline"]]);
        $this->actingAs($this->admin);

        foreach (['csv', 'xlsx', 'pdf'] as $format) {
            $this->get(route('activity-logs.accomplishment', ['format' => $format]))->assertOk();
        }
        $row = $this->illnessOthersRow($this->build(), 'Skeletal System');
        $this->assertArrayHasKey('Tab and newline', $this->sub($row), 'a tab or a line break inside a name is one space');
    }
}
