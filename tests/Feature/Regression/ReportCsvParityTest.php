<?php

namespace Tests\Feature\Regression;

use App\Livewire\ReportGeneration;
use App\Models\ActivityLog;
use App\Models\DispenseRecord;
use App\Models\DocumentIssuance;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\User;
use App\Services\MedicineInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * 2026-10-01 report: "Export CSV has no names inside even if filtered data is displayed". For every tab the CSV
 * must contain exactly the rows the screen shows under the same filters, with the patient names, and it must
 * open correctly in Excel (UTF-8 byte-order mark, so "Peña" is not garbled).
 */
class ReportCsvParityTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $admin;

    private User $doctor;

    private Patient $ana;

    private Patient $carlo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();

        $this->admin = $this->makeAdmin();
        $this->doctor = $this->makeDoctor();
        $this->actingAs($this->admin);

        $this->ana = $this->makePatient(['first_name' => 'Ana', 'middle_name' => 'Maria', 'last_name' => 'Peña', 'email' => 'pt.one@test.local']);
        $this->carlo = $this->makePatient(['first_name' => 'Carlo', 'last_name' => 'Dela Cruz', 'email' => 'pt.two@test.local']);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array{0: array<int, string>, 1: array<int, array<int, string>>, 2: string}  [header, rows, raw body]
     */
    private function downloadCsv(string $tab, array $query = []): array
    {
        $response = $this->get(route('activity-logs.export', ['tab' => $tab] + $query))->assertOk();
        $body = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $body, 'the file must start with a UTF-8 byte-order mark');

        $lines = array_values(array_filter(
            array_map('str_getcsv', preg_split('/\R/', substr($body, 3))),
            fn (array $row) => $row !== [null]
        ));

        return [array_shift($lines), $lines, $body];
    }

    private function column(array $header, array $rows, string $name): array
    {
        $index = array_search($name, $header, true);
        $this->assertNotFalse($index, "the CSV has no \"{$name}\" column: " . implode(' | ', $header));

        return array_column($rows, $index);
    }

    private function consultation(Patient $patient, string $name, array $extra = []): DocumentIssuance
    {
        return DocumentIssuance::create(array_merge([
            'document_type' => 'consultation_form', 'document_creator_id' => $this->doctor->id, 'user_id' => $patient->user_id,
            'name' => $name, 'age' => 21, 'gender' => 'Female', 'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
            'complaints' => 'cough', 'assessment' => 'URTI', 'plan' => 'rest',
        ], $extra));
    }

    public function test_visits_csv_matches_the_screen_for_a_search_and_a_date_range_and_keeps_accents(): void
    {
        $this->consultation($this->ana, 'Ana Maria Peña');
        $old = $this->consultation($this->carlo, 'Carlo Dela Cruz', ['assessment' => 'tension headache']);
        // The date of a visit is its consultation date (requested_at), not the day it was typed in.
        $old->forceFill(['requested_at' => now()->subMonths(2)->toDateString(), 'created_at' => now()->subMonths(2)])->save();

        // 1. no filter: both visits, with names
        [$header, $rows, $body] = $this->downloadCsv('visits');
        $this->assertEqualsCanonicalizing(['Ana Maria Peña', 'Carlo Dela Cruz'], $this->column($header, $rows, 'Patient Name'));
        $this->assertStringContainsString('Peña', $body);

        // 2. a full-name search (the screen also searches the assessment text)
        foreach ([['search' => 'ana peña'], ['search' => 'Peña, Ana'], ['search' => 'tension'], ['search' => 'carlo*']] as $filters) {
            [$header, $rows] = $this->downloadCsv('visits', $filters);
            $csvNames = $this->column($header, $rows, 'Patient Name');

            $screen = Livewire::test(ReportGeneration::class)->call('setTab', 'visits')->set('search', $filters['search']);
            foreach (['Ana Maria Peña', 'Carlo Dela Cruz'] as $name) {
                in_array($name, $csvNames, true)
                    ? $screen->assertSee($name, false)
                    : $screen->assertDontSee($name, false);
            }
            $this->assertNotEmpty($csvNames, "searching \"{$filters['search']}\" must not give an empty file");
        }

        // 3. date range: only the recent visit
        $filters = ['date_from' => now()->subDays(7)->toDateString(), 'date_to' => now()->toDateString()];
        [$header, $rows] = $this->downloadCsv('visits', $filters);
        $this->assertSame(['Ana Maria Peña'], $this->column($header, $rows, 'Patient Name'));

        $screen = Livewire::test(ReportGeneration::class)->call('setTab', 'visits')
            ->set('date_from', $filters['date_from'])->set('date_to', $filters['date_to']);
        $screen->assertSee('Ana Maria Peña', false)->assertDontSee('Carlo Dela Cruz', false);
    }

    public function test_logs_csv_respects_user_type_action_search_and_dates(): void
    {
        foreach ([['Ana Maria Peña', 'doctor', 'consultation_record'], ['Carlo Dela Cruz', 'staff', 'patient_record']] as [$name, $type, $action]) {
            ActivityLog::create([
                'user_id' => $this->doctor->id, 'user_type' => $type, 'user_name' => 'Someone', 'action' => $action,
                'description' => 'Record: ' . $name, 'patient_name' => $name, 'date' => now()->toDateString(),
            ]);
        }

        [$header, $rows] = $this->downloadCsv('logs', ['user_type' => 'doctor']);
        $this->assertSame(['Ana Maria Peña'], $this->column($header, $rows, 'Patient Name'));

        [$header, $rows] = $this->downloadCsv('logs', ['action' => 'patient_record']);
        $this->assertSame(['Carlo Dela Cruz'], $this->column($header, $rows, 'Patient Name'));

        [$header, $rows] = $this->downloadCsv('logs', ['search' => 'cruz carlo']);
        $this->assertSame(['Carlo Dela Cruz'], $this->column($header, $rows, 'Patient Name'));

        // The screen agrees with the file.
        $screen = Livewire::test(ReportGeneration::class)->set('user_type', 'doctor');
        $screen->assertSee('Ana Maria Peña', false)->assertDontSee('Carlo Dela Cruz', false);
    }

    public function test_inventory_csv_follows_the_low_stock_filter_like_the_screen(): void
    {
        $low = $this->makeMedicine('Lowmed', ['minimum_stock_alert' => 10]);
        $this->stockIn($low, 3, now()->addMonths(3)->toDateString());
        $healthy = $this->makeMedicine('Healthymed', ['minimum_stock_alert' => 10]);
        $this->stockIn($healthy, 80, now()->addMonths(3)->toDateString());

        [$header, $rows] = $this->downloadCsv('inventory');
        $this->assertEqualsCanonicalizing(['Lowmed', 'Healthymed'], $this->column($header, $rows, 'Medicine Name'));

        // The screen's "Low Stock" filter used to be ignored by the file.
        [$header, $rows] = $this->downloadCsv('inventory', ['status' => 'low_stock']);
        $this->assertSame(['Lowmed'], $this->column($header, $rows, 'Medicine Name'));
        $this->assertSame(['Low Stock'], $this->column($header, $rows, 'Status'));
        $this->assertNotSame('', $this->column($header, $rows, 'Nearest Expiry')[0]);

        $screen = Livewire::test(ReportGeneration::class)->call('setTab', 'inventory')->set('status', 'low_stock');
        $screen->assertSee('Lowmed', false)->assertDontSee('Healthymed', false);

        // Category and generic names are searched on screen, so they must be in the file too.
        [$header, $rows] = $this->downloadCsv('inventory', ['search' => 'lowm*']);
        $this->assertSame(['Lowmed'], $this->column($header, $rows, 'Medicine Name'));
    }

    public function test_dispensing_csv_names_the_patient_and_can_be_searched_by_patient(): void
    {
        $medicine = $this->makeMedicine('Dispmed');
        $this->stockIn($medicine, 50, now()->addMonths(3)->toDateString());
        foreach ([$this->ana, $this->carlo] as $patient) {
            $record = DispenseRecord::create([
                'history_number' => 'HIS' . generateUniqueHistoryNumber(), 'patient_id' => $patient->id, 'doctor_id' => $this->doctor->doctor->id,
                'model_type' => DispenseRecord::class, 'model_id' => $patient->id, 'bill_date' => now(),
            ]);
            app(MedicineInventoryService::class)->deductStockFefo($medicine->id, 2, $this->admin->id, $record, 'Dispense record', 'dispense', '500mg');
        }

        [$header, $rows] = $this->downloadCsv('dispensing');
        $this->assertEqualsCanonicalizing(['Ana Maria Peña', 'Carlo Dela Cruz'], $this->column($header, $rows, 'Patient Name'));

        [$header, $rows] = $this->downloadCsv('dispensing', ['search' => 'carlo cruz']);
        $this->assertSame(['Carlo Dela Cruz'], $this->column($header, $rows, 'Patient Name'));

        // Same on screen: the patient is shown and searchable.
        $screen = Livewire::test(ReportGeneration::class)->call('setTab', 'dispensing');
        $screen->assertSee('Ana Maria Peña', false)->assertSee('Carlo Dela Cruz', false);
        $screen->set('search', 'carlo cruz')->assertSee('Carlo Dela Cruz', false)->assertDontSee('Ana Maria Peña', false);
    }

    public function test_appointments_csv_matches_the_screen_for_a_name_search(): void
    {
        foreach ([$this->ana, $this->carlo] as $patient) {
            $queue = new PatientQueue();
            $queue->forceFill([
                'patient_id' => $patient->id, 'added_by' => $this->admin->id, 'status' => PatientQueue::STATUS_WAITING,
                'scheduled_at' => now()->addDay(),
            ])->save();
        }

        [$header, $rows] = $this->downloadCsv('appointments');
        $this->assertEqualsCanonicalizing(['Ana Maria Peña', 'Carlo Dela Cruz'], $this->column($header, $rows, 'Patient Name'));

        [$header, $rows] = $this->downloadCsv('appointments', ['search' => 'Peña, Ana']);
        $this->assertSame(['Ana Maria Peña'], $this->column($header, $rows, 'Patient Name'));

        $screen = Livewire::test(ReportGeneration::class)->call('setTab', 'appointments')->set('search', 'Peña, Ana');
        $screen->assertSee('Ana Maria Peña', false)->assertDontSee('Carlo Dela Cruz', false);
    }

    public function test_formulas_in_free_text_are_neutralised_and_unknown_tabs_are_refused(): void
    {
        $this->consultation($this->ana, '=HYPERLINK("http://evil")');

        [$header, $rows] = $this->downloadCsv('visits');
        $this->assertSame(["'=HYPERLINK(\"http://evil\")"], $this->column($header, $rows, 'Patient Name'));

        $this->get(route('activity-logs.export', ['tab' => 'nonsense']))->assertNotFound();
    }
}
