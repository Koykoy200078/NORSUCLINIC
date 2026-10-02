<?php

namespace Tests\Feature\Regression;

use App\Livewire\BarangayTable;
use App\Livewire\CityTable;
use App\Livewire\CountriesTable;
use App\Livewire\MedicineAvailabilityTable;
use App\Livewire\MedicineCategoryDetailsTable;
use App\Livewire\MedicineCategoryTable;
use App\Livewire\MedicineGenericDetailsTable;
use App\Livewire\MedicineGenericTable;
use App\Livewire\ProvinceTable;
use App\Livewire\RoleTable;
use App\Livewire\SpecializationTable;
use App\Livewire\StateTable;
use App\Livewire\StockInTable;
use App\Livewire\UsedMedicineTable;
use App\Livewire\DocumentIssuanceTable;
use App\Livewire\DoctorTable;
use App\Livewire\LabRequestTable;
use App\Livewire\MedicineDispenseTable;
use App\Livewire\MedicineTable;
use App\Livewire\PatientTable;
use App\Livewire\PrescriptionTable;
use App\Livewire\ReportGeneration;
use App\Livewire\StaffTable;
use App\Livewire\StockOutTable;
use App\Models\ActivityLog;
use App\Models\DispenseRecord;
use App\Models\DocumentIssuance;
use App\Models\LabRequest;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use App\Services\MedicineInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Name search on every screen that has a search box (2026-10-01 report: "when I search a name, a full name or a
 * wildcard, nothing is displayed"). The same typed terms are tried on each screen: first name, last name, full
 * name, "Last, First", middle name, upper case, extra spaces and the * ? % wildcards.
 */
class SearchBehaviourTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    /** Everything here must find "Ana Maria Reyes" and not "Carlo Dela Cruz". */
    private const ANA = [
        'Ana', 'Reyes', 'Ana Reyes', 'Reyes, Ana', 'Ana Maria', 'ana maria reyes', 'ANA REYES', '  ana   reyes  ',
        'An*', 'R?yes', 'Re%es', 'a*a rey*',
    ];

    /** Typed names that the stored value may or may not carry a middle name for. */
    private const ANA_WITHOUT_MIDDLE = ['Ana', 'Reyes', 'Ana Reyes', 'Reyes, Ana', 'ANA REYES', '  ana   reyes  ', 'An*', 'R?yes', 'Re%es', 'a*a rey*'];

    /** Must find "Carlo Dela Cruz" and not "Ana Maria Reyes". */
    private const CARLO = ['Carlo', 'Dela Cruz', 'Cruz, Carlo', 'carlo dela', 'Dela*', 'C?rlo'];

    /** Must find nobody. */
    private const NOBODY = ['zzzz', 'Ana Cruz', 'Reyes Carlo', 'qq*'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->actingAs($this->makeAdmin());
    }

    /**
     * @param  \Livewire\Features\SupportTesting\Testable  $component
     * @param  array<int, string>  $terms
     */
    private function assertSearchFinds($component, array $terms, string $see, string $hide, string $screen): void
    {
        foreach ($terms as $term) {
            try {
                $component->set('search', $term)->assertSee($see, false)->assertDontSee($hide, false);
            } catch (\Throwable $e) {
                $this->fail("[{$screen}] searching \"{$term}\" should show \"{$see}\" and not \"{$hide}\": " . $e->getMessage());
            }
        }
    }

    private function assertSearchFindsNobody($component, string $anaMarker, string $carloMarker, string $screen): void
    {
        foreach (self::NOBODY as $term) {
            try {
                $component->set('search', $term)->assertDontSee($anaMarker, false)->assertDontSee($carloMarker, false);
            } catch (\Throwable $e) {
                $this->fail("[{$screen}] searching \"{$term}\" should find nobody: " . $e->getMessage());
            }
        }
    }

    private function ana(): Patient
    {
        // A distinct e-mail: the default one is built from a stock first name and would contain letters of other searches.
        return $this->makePatient(['first_name' => 'Ana', 'middle_name' => 'Maria', 'last_name' => 'Reyes', 'university_id_number' => 'S-ANA-1', 'email' => 'pt.one@test.local']);
    }

    private function carlo(): Patient
    {
        return $this->makePatient(['first_name' => 'Carlo', 'last_name' => 'Dela Cruz', 'university_id_number' => 'S-CARLO-1', 'email' => 'pt.two@test.local']);
    }

    private function document(User $creator, Patient $patient, string $name, string $type = 'consultation_form', array $extra = []): DocumentIssuance
    {
        return DocumentIssuance::create(array_merge([
            'document_type' => $type, 'document_creator_id' => $creator->id, 'user_id' => $patient->user_id, 'name' => $name,
            'age' => 23, 'gender' => 'Female', 'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
        ], $extra));
    }

    public function test_patient_list(): void
    {
        $this->ana();
        $this->carlo();

        $component = Livewire::withoutLazyLoading()->test(PatientTable::class);

        $this->assertSearchFinds($component, self::ANA, 'Reyes', 'Cruz', 'Patients');
        $this->assertSearchFinds($component, self::CARLO, 'Cruz', 'Reyes', 'Patients');
        $this->assertSearchFinds($component, ['S-ANA-1', 'S-ANA*'], 'Reyes', 'Cruz', 'Patients (ID number)');
        $this->assertSearchFindsNobody($component, 'Reyes', 'Cruz', 'Patients');
    }

    public function test_doctor_list(): void
    {
        $this->makeDoctor(['first_name' => 'Hilda', 'middle_name' => 'Santos', 'last_name' => 'Ramos']);
        $this->makeDoctor(['first_name' => 'Gregorio', 'last_name' => 'Magno']);

        $component = Livewire::withoutLazyLoading()->test(DoctorTable::class);

        $this->assertSearchFinds($component, ['Hilda', 'Ramos', 'Hilda Ramos', 'Ramos, Hilda', 'Hilda Santos Ramos', 'HILDA RAMOS', '  hilda   ramos ', 'Hil*', 'R?mos', 'Ra%os', 'h*a ram*'], 'Ramos', 'Magno', 'Doctors');
        $this->assertSearchFinds($component, ['Gregorio Magno', 'Magno, Gregorio', 'greg*'], 'Magno', 'Ramos', 'Doctors');
        $this->assertSearchFindsNobody($component, 'Ramos', 'Magno', 'Doctors');
    }

    public function test_staff_list(): void
    {
        $this->makeStaff('clinic_staff', 'front_desk', ['first_name' => 'Marites', 'middle_name' => 'Lopez', 'last_name' => 'Villanueva']);
        $this->makeStaff('nurse', 'triage_area', ['first_name' => 'Joel', 'last_name' => 'Pacquiao']);

        $component = Livewire::withoutLazyLoading()->test(StaffTable::class);

        $this->assertSearchFinds($component, ['Marites', 'Villanueva', 'Marites Villanueva', 'Villanueva, Marites', 'Marites Lopez Villanueva', 'MARITES VILLANUEVA', ' marites   villa* ', 'Mar*', 'V?llanueva'], 'Villanueva', 'Pacquiao', 'Staff');
        $this->assertSearchFinds($component, ['Joel Pacquiao', 'Pacquiao, Joel'], 'Pacquiao', 'Villanueva', 'Staff');
        $this->assertSearchFindsNobody($component, 'Villanueva', 'Pacquiao', 'Staff');
    }

    public function test_consultation_and_certificate_lists(): void
    {
        $doctor = $this->makeDoctor();
        $ana = $this->ana();
        $carlo = $this->carlo();
        $this->document($doctor, $ana, 'Ana Maria Reyes');
        $this->document($doctor, $carlo, 'Carlo Dela Cruz');
        $this->document($doctor, $ana, 'Ana Maria Reyes', 'medical_certificate');
        $this->document($doctor, $carlo, 'Carlo Dela Cruz', 'medical_certificate');

        foreach (['consultation', 'certificate'] as $module) {
            $component = Livewire::withoutLazyLoading()->test(DocumentIssuanceTable::class, ['module' => $module]);

            $this->assertSearchFinds($component, self::ANA, 'Reyes', 'Cruz', "Documents ({$module})");
            $this->assertSearchFinds($component, self::CARLO, 'Cruz', 'Reyes', "Documents ({$module})");
            $this->assertSearchFindsNobody($component, 'Reyes', 'Cruz', "Documents ({$module})");
        }
    }

    public function test_lab_request_list(): void
    {
        $doctor = $this->makeDoctor();
        foreach ([['Ana Maria Reyes', 'LAB-A-1', $this->ana()], ['Carlo Dela Cruz', 'LAB-C-1', $this->carlo()]] as [$name, $number, $patient]) {
            LabRequest::create([
                'request_number' => $number, 'document_creator_id' => $doctor->id, 'patient_user_id' => $patient->user_id,
                'patient_name' => $name, 'patient_age' => 20, 'patient_gender' => 'Female', 'requested_at' => now()->toDateString(),
                'status' => LabRequest::STATUS_PENDING,
            ]);
        }

        $component = Livewire::withoutLazyLoading()->test(LabRequestTable::class);

        $this->assertSearchFinds($component, self::ANA, 'Reyes', 'Cruz', 'Lab requests');
        $this->assertSearchFinds($component, self::CARLO, 'Cruz', 'Reyes', 'Lab requests');
        $this->assertSearchFinds($component, ['LAB-A-1', 'LAB-A*'], 'Reyes', 'Cruz', 'Lab requests (number)');
        $this->assertSearchFindsNobody($component, 'Reyes', 'Cruz', 'Lab requests');
    }

    public function test_prescription_list_by_patient_and_by_doctor(): void
    {
        $hilda = $this->makeDoctor(['first_name' => 'Hilda', 'last_name' => 'Ramos']);
        $gregorio = $this->makeDoctor(['first_name' => 'Gregorio', 'last_name' => 'Magno']);
        foreach ([[$this->ana(), $hilda], [$this->carlo(), $gregorio]] as [$patient, $doctor]) {
            Prescription::create([
                'patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id, 'status' => Prescription::DISPENSE_STATUS_PENDING,
                'is_active' => 1, 'consultation_date' => now()->toDateString(),
            ]);
        }

        $component = Livewire::withoutLazyLoading()->test(PrescriptionTable::class);

        $this->assertSearchFinds($component, self::ANA, 'Reyes', 'Cruz', 'Prescriptions (patient)');
        $this->assertSearchFinds($component, self::CARLO, 'Cruz', 'Reyes', 'Prescriptions (patient)');
        $this->assertSearchFinds($component, ['Hilda Ramos', 'Ramos, Hilda', 'hil*'], 'Reyes', 'Cruz', 'Prescriptions (doctor)');
        $this->assertSearchFinds($component, ['Gregorio Magno', 'Magno, Gregorio'], 'Cruz', 'Reyes', 'Prescriptions (doctor)');
        $this->assertSearchFindsNobody($component, 'Reyes', 'Cruz', 'Prescriptions');
    }

    public function test_dispense_history_by_patient_and_by_doctor(): void
    {
        $hilda = $this->makeDoctor(['first_name' => 'Hilda', 'last_name' => 'Ramos']);
        $gregorio = $this->makeDoctor(['first_name' => 'Gregorio', 'last_name' => 'Magno']);
        foreach ([[$this->ana(), $hilda], [$this->carlo(), $gregorio]] as [$patient, $doctor]) {
            DispenseRecord::create([
                'history_number' => 'HIS' . generateUniqueHistoryNumber(), 'patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id,
                'model_type' => DispenseRecord::class, 'model_id' => $patient->id, 'bill_date' => now(),
            ]);
        }

        $component = Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class);

        $this->assertSearchFinds($component, self::ANA, 'Reyes', 'Cruz', 'Dispense history (patient)');
        $this->assertSearchFinds($component, self::CARLO, 'Cruz', 'Reyes', 'Dispense history (patient)');
        $this->assertSearchFinds($component, ['Hilda Ramos', 'Ramos, Hilda'], 'Reyes', 'Cruz', 'Dispense history (doctor)');
        $this->assertSearchFindsNobody($component, 'Reyes', 'Cruz', 'Dispense history');
    }

    public function test_stock_out_tab(): void
    {
        $medicine = $this->makeMedicine('Searchmed');
        $this->stockIn($medicine, 50, now()->addMonths(3)->toDateString());
        $doctor = $this->makeDoctor();
        foreach ([$this->ana(), $this->carlo()] as $patient) {
            $record = DispenseRecord::create([
                'history_number' => 'HIS' . generateUniqueHistoryNumber(), 'patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id,
                'model_type' => DispenseRecord::class, 'model_id' => $patient->id, 'bill_date' => now(),
            ]);
            app(MedicineInventoryService::class)->deductStockFefo($medicine->id, 2, null, $record, 'Dispense record', 'dispense', '500mg');
        }

        $component = Livewire::withoutLazyLoading()->test(StockOutTable::class);

        $this->assertSearchFinds($component, self::ANA, 'Reyes', 'Cruz', 'Stock out');
        $this->assertSearchFinds($component, self::CARLO, 'Cruz', 'Reyes', 'Stock out');
        $this->assertSearchFinds($component, ['Searchmed', 'search*', 'S?archmed'], 'Searchmed', 'zzzz-not-there', 'Stock out (medicine)');
    }

    public function test_medicine_list_understands_wildcards(): void
    {
        $this->makeMedicine('Paracetamol');
        $this->makeMedicine('Amoxicillin');

        $component = Livewire::withoutLazyLoading()->test(MedicineTable::class);

        $this->assertSearchFinds($component, ['Paracetamol', 'PARA', 'para*mol', 'p?racetamol', 'Para%'], 'Paracetamol', 'Amoxicillin', 'Medicines');
        $this->assertSearchFindsNobody($component, 'Paracetamol', 'Amoxicillin', 'Medicines');
    }

    public function test_the_patient_pickers_of_the_consultation_and_lab_forms(): void
    {
        $doctor = $this->makeDoctor();
        $ana = $this->ana();
        $this->carlo();
        $this->actingAs($doctor);

        foreach (array_merge(self::ANA, ['S-ANA-1']) as $term) {
            $term = trim($term);
            if (mb_strlen($term) < 2) {
                continue;
            }

            $documents = $this->getJson(route('doctors.document-issuances.search-users', ['query' => $term]))->assertOk()->json();
            $this->assertSame([$ana->id], collect($documents)->pluck('id')->all(), "consultation form picker, searching \"{$term}\"");

            $labs = $this->getJson(route('doctors.lab-requests.search-users', ['query' => $term]))->assertOk()->json();
            $this->assertSame([$ana->user_id], collect($labs)->pluck('id')->all(), "lab request picker, searching \"{$term}\"");
        }

        $this->assertSame([], $this->getJson(route('doctors.document-issuances.search-users', ['query' => 'zzzz']))->json());
    }

    public function test_report_generation_tabs(): void
    {
        $doctor = $this->makeDoctor();
        $ana = $this->ana();
        $carlo = $this->carlo();
        $this->document($doctor, $ana, 'Ana Maria Reyes', 'consultation_form', ['complaints' => 'cough', 'assessment' => 'URTI']);
        $this->document($doctor, $carlo, 'Carlo Dela Cruz', 'consultation_form', ['complaints' => 'headache', 'assessment' => 'tension']);
        foreach ([['Ana Maria Reyes', $ana], ['Carlo Dela Cruz', $carlo]] as [$name, $patient]) {
            ActivityLog::create([
                'user_id' => $doctor->id, 'user_type' => 'doctor', 'user_name' => 'Doc Tester', 'action' => 'consultation_record',
                'description' => 'Consultation form: ' . $name, 'patient_name' => $name, 'date' => now()->toDateString(),
            ]);
        }

        foreach (['visits', 'logs'] as $tab) {
            $component = Livewire::test(ReportGeneration::class)->call('setTab', $tab);

            $this->assertSearchFinds($component, self::ANA, 'Reyes', 'Cruz', "Reports ({$tab})");
            $this->assertSearchFinds($component, self::CARLO, 'Cruz', 'Reyes', "Reports ({$tab})");
        }

        // The visit report also searches what was written about the visit.
        $component = Livewire::test(ReportGeneration::class)->call('setTab', 'visits');
        $this->assertSearchFinds($component, ['cough', 'URTI', 'cou*'], 'Reyes', 'Cruz', 'Reports (visits, complaint / assessment)');

        // Global search lists patients by name too.
        $component = Livewire::test(ReportGeneration::class)->call('setTab', 'global_search');
        $this->assertSearchFinds($component, self::ANA, 'Reyes', 'Cruz', 'Reports (global search)');
    }

    /**
     * Every other search box (locations, roles, specializations, categories, generics, stock-in, ...) uses the
     * shared word-based search. None of them may break on a full name, an accented name or a wildcard.
     */
    public function test_every_other_search_box_accepts_words_wildcards_and_odd_input(): void
    {
        $tables = [
            [CityTable::class, []],
            [BarangayTable::class, []],
            [CountriesTable::class, []],
            [ProvinceTable::class, []],
            [StateTable::class, []],
            [RoleTable::class, []],
            [SpecializationTable::class, []],
            [MedicineCategoryTable::class, []],
            [MedicineGenericTable::class, []],
            [MedicineAvailabilityTable::class, []],
            [StockInTable::class, []],
            [UsedMedicineTable::class, []],
            [MedicineCategoryDetailsTable::class, ['categoryDetails' => '1']],
            [MedicineGenericDetailsTable::class, ['genericDetails' => '1']],
        ];

        $terms = ['a', 'ana reyes', 'Reyes, Ana', 'an*', 'r?yes', 'a%s', '  spaced   out  ', "O'Brien", 'Peña', '50% off', 'back\slash', '"quoted"', '<b>', ','];

        foreach ($tables as [$class, $params]) {
            $component = Livewire::withoutLazyLoading()->test($class, $params);

            foreach ($terms as $term) {
                try {
                    $component->set('search', $term)->assertOk();
                } catch (\Throwable $e) {
                    $this->fail(class_basename($class) . " broke while searching \"{$term}\": " . $e->getMessage());
                }
            }
        }
    }
}
