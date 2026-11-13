<?php

namespace Tests\Concerns;

use App\Models\Doctor;
use App\Models\DocumentIssuance;
use App\Models\DispenseHistoryEntry;
use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\Diagnose;
use App\Models\Medicine;
use App\Models\MedicineTransaction;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\PurchasedMedicine;
use App\Models\Specialization;
use App\Models\StockIn;
use App\Models\User;
use App\Models\College;
use App\Models\Course;
use App\Models\YearLevel;
use App\Services\MedicineInventoryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportTesting\Testable;

/**
 * The inventory, people and clinical lists of the golden master (see SeedsTableLists). Rows whose id is printed on the
 * screen (RX #, consultation ID, Dispense History numbers) get explicit ids: auto-increment values differ from run to run.
 */
trait SeedsClinicTableLists
{
    protected function seedMedicineTable(): array
    {
        for ($i = 1; $i <= self::ROWS; $i++) {
            $medicine = $this->makeMedicine($this->label('Med', $i), [
                'generic_name' => 'Generic ' . ($i % 3), 'category_name' => 'Category ' . ($i % 4), 'brand_name' => 'Brand ' . ($i % 5),
            ] + $this->stamps($i));
            if ($i <= 6) {
                // in stock, with one or two batches of different expiry dates
                $this->stockIn($medicine, 10 + $i, '2027-0' . $i . '-15', '500mg', 'LOT-' . $i . 'A');
                if ($i % 2 === 0) {
                    $this->stockIn($medicine, 5, '2028-0' . $i . '-20', '500mg', 'LOT-' . $i . 'B');
                }
            }
        }

        return $this->seeded();
    }

    protected function seedStockInTable(): array
    {
        $medicine = $this->makeMedicine('Biogesic', ['generic_name' => 'Paracetamol', 'brand_name' => 'Biogesic'] + $this->stamps(0));
        for ($i = 1; $i <= self::ROWS; $i++) {
            $stockIn = StockIn::create(['availability_no' => $this->label('AV', $i)] + $this->stamps($i));
            if ($i % 4 === 0) {
                foreach (range(1, $i % 3 + 1) as $line) {
                    PurchasedMedicine::create(['medicine_availabilities_id' => $stockIn->id, 'medicine_id' => $medicine->id, 'dosage' => '500mg', 'quantity' => $i + $line, 'expiry_date' => '2027-06-30']);
                }
            }
        }

        return $this->seeded();
    }

    /** @return array{0: array<int, Patient>, 1: User, 2: array<int, Medicine>} */
    private function peopleAndMedicines(): array
    {
        $patients = [];
        foreach (range(1, 10) as $j) {
            $patients[] = $this->makePatient(
                ['first_name' => $this->label('Pat', $j), 'last_name' => 'Cruz', 'email' => "patient{$j}@golden.test"],
                ['patient_unique_id' => 'GOLD-' . $j],
            );
        }
        $doctor = $this->makeDoctor(['first_name' => 'Dora', 'last_name' => 'Doctor', 'email' => 'doctor@golden.test']);
        $medicines = [
            $this->makeMedicine('Biogesic', ['generic_name' => 'Paracetamol']),
            $this->makeMedicine('Neozep', ['generic_name' => 'Phenylephrine']),
            $this->makeMedicine('Amoxil', ['generic_name' => 'Amoxicillin']),
        ];

        return [$patients, $doctor, $medicines];
    }

    /** 35 manual dispense records with one or two lines each: the rows behind Dispense History, Stock Out and Used Medicines. */
    private function dispensedRecords(): void
    {
        [$patients, $doctor, $medicines] = $this->peopleAndMedicines();
        foreach ($medicines as $k => $medicine) {
            $this->stockIn($medicine, 1000, '2027-12-31', '500mg', 'LOT-GOLD-' . $k);
        }

        for ($i = 1; $i <= self::ROWS; $i++) {
            $patient = $patients[$i % 10];
            $record = DispenseRecord::create([
                'id' => $i, 'history_number' => sprintf('HIS%04d', $i), 'patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id,
                'model_type' => DispenseRecord::class, 'model_id' => (string) $patient->id, 'bill_date' => Carbon::parse('2026-02-01')->addDays($i),
            ] + $this->stamps($i));
            // the stock ledger line (what Stock Out and Used Medicines read) is written at the moment of the dispense
            Carbon::setTestNow(Carbon::parse('2026-02-01 08:00:00')->addMinutes($i));
            foreach (range(0, $i % 2) as $line) {
                $medicineLine = $medicines[($i + $line) % 3];
                $quantity = $i % 5 + 1;
                DispenseRecordItem::create(['medicine_bill_id' => $record->id, 'medicine_id' => $medicineLine->id, 'dosage' => '500mg', 'sale_quantity' => $quantity] + $this->stamps($i));
                app(MedicineInventoryService::class)->deductStockFefo($medicineLine->id, $quantity, $doctor->id, $record, 'golden master', MedicineTransaction::TYPE_DISPENSE, '500mg');
            }
        }
        Carbon::setTestNow('2026-03-01 10:00:00');

        // Stock Out / Used Medicines key their rows by the ledger id read as TEXT, so rows that tie on the sorted value come
        // back in the order of those texts ("T95" before "T147"). Give the ledger lines ids of one width so the order does not
        // depend on how many ledger lines earlier tests created.
        foreach (DB::table('medicine_transactions')->orderBy('id')->pluck('id') as $n => $id) {
            DB::table('medicine_transactions')->where('id', $id)->update(['id' => 900001 + $n]);
        }
    }

    protected function seedStockOutTable(): array
    {
        $this->dispensedRecords();

        return $this->seeded();
    }

    protected function seedMedicineDispenseTable(): array
    {
        $this->dispensedRecords();

        return $this->seeded() + ['extras' => [
            'source: dispense records only' => fn (Testable $component) => $component->set('sourceFilter', DispenseHistoryEntry::SOURCE_DISPENSE_RECORD),
            'source: consultations only' => fn (Testable $component) => $component->set('sourceFilter', DispenseHistoryEntry::SOURCE_CONSULTATION),
        ]];
    }

    protected function seedStaffTable(): array
    {
        for ($i = 1; $i <= self::ROWS; $i++) {
            $nurse = User::create([
                'first_name' => $this->label('Nurse', $i), 'last_name' => 'Staff', 'email' => "nurse{$i}@golden.test", 'password' => 'password',
                'type' => User::STAFF, 'status' => 1, 'gender' => $i % 2 ? User::MALE : User::FEMALE, 'email_verified_at' => now(),
            ] + $this->stamps($i));
            $nurse->assignRole('staff');
        }

        return $this->seeded();
    }

    protected function seedDoctorTable(): array
    {
        $specialties = [Specialization::create(['name' => 'Dentistry']), Specialization::create(['name' => 'General Medicine'])];
        for ($i = 1; $i <= self::ROWS; $i++) {
            $user = User::create([
                'first_name' => $this->label('Doc', $i), 'last_name' => 'Physician', 'email' => "doc{$i}@golden.test", 'password' => 'password',
                'type' => User::DOCTOR, 'status' => $i % 6 === 0 ? 0 : 1, 'gender' => User::MALE, 'email_verified_at' => now(),
            ] + $this->stamps($i));
            $user->assignRole('doctor');
            $doctor = Doctor::create(['user_id' => $user->id, 'prc_license_number' => 'PRC-GOLD-' . $i] + $this->stamps($i));
            $doctor->specializations()->sync([$specialties[$i % 2]->id]);
        }

        return $this->seeded() + ['extras' => [
            // the header filter dispatches this event (resources/assets/js/doctors/doctors.js)
            'status: active' => fn (Testable $component) => $component->dispatch('changeStatusFilter', value: 1),
            'status: inactive' => fn (Testable $component) => $component->dispatch('changeStatusFilter', value: 0),
        ]];
    }

    protected function seedPatientTable(): array
    {
        $nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse', 'email' => 'nora@golden.test']);
        for ($i = 1; $i <= self::ROWS; $i++) {
            $patient = $this->makePatient(
                ['first_name' => $this->label('Pat', $i), 'last_name' => 'Cruz', 'email' => "patient{$i}@golden.test"],
                ['patient_unique_id' => 'GOLD-' . $i] + $this->stamps($i),
            );
            if ($i % 7 === 0) {
                DocumentIssuance::create(['document_type' => 'consultation_form', 'document_creator_id' => $nurse->id, 'user_id' => $patient->user_id, 'name' => 'Visit', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete']);
            }
            if ($i % 12 === 0) {
                $patient->delete();
            }
        }

        return $this->seeded() + ['extras' => [
            'status: archived' => fn (Testable $component) => $component->set('statusFilter', 'archived'),
        ]];
    }

    protected function seedPrescriptionTable(): array
    {
        [$patients, $doctor] = $this->peopleAndMedicines();
        $diagnosis = Diagnose::create(['diagnoses' => 'J00 Acute nasopharyngitis']);
        for ($i = 1; $i <= self::ROWS; $i++) {
            Prescription::create([
                'id' => $i, 'patient_id' => $patients[$i % 10]->id, 'doctor_id' => $doctor->doctor->id, 'status' => $i % 4 === 0 ? 'dispensed' : 'pending',
                'is_active' => 1, 'consultation_date' => Carbon::parse('2026-02-01')->addDays($i)->toDateString(),
                'icd10_diagnosis_id' => $i % 3 === 0 ? $diagnosis->id : null,
            ] + $this->stamps($i));
        }

        return $this->seeded();
    }

    protected function seedPrescriptionVerificationTable(): array
    {
        [$patients, $doctor, $medicines] = $this->peopleAndMedicines();
        for ($i = 1; $i <= self::ROWS + 3; $i++) {
            // 35 waiting prescriptions, and three that must never show: dispensed, cancelled, deactivated
            Prescription::create([
                'id' => $i, 'patient_id' => $patients[$i % 10]->id, 'doctor_id' => $doctor->doctor->id,
                'status' => $i === 36 ? 'dispensed' : ($i === 37 ? 'cancelled' : 'pending'), 'is_active' => $i === 38 ? 0 : 1,
                'consultation_date' => Carbon::parse('2026-02-01')->addDays($i)->toDateString(),
            ] + $this->stamps($i));
            if ($i % 3 === 0) {
                PrescriptionMedicine::create(['prescription_id' => $i, 'medicine' => $medicines[$i % 3]->id, 'dosage' => '500mg', 'total_quantity' => $i] + $this->stamps($i));
            }
        }

        return $this->seeded();
    }

    protected function seedDocumentIssuanceTable(): array
    {
        $nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse', 'email' => 'nora@golden.test']);
        [$patients] = $this->peopleAndMedicines();
        for ($i = 1; $i <= self::ROWS; $i++) {
            DocumentIssuance::create([
                'id' => $i, 'document_type' => 'consultation_form', 'document_creator_id' => $nurse->id, 'user_id' => $patients[$i % 10]->user_id,
                'name' => $this->label('Visitor', $i), 'age' => 20 + $i % 5, 'gender' => 'Male', 'address' => 'Dumaguete',
            ] + $this->stamps($i));
        }
        // certificates live in the same table and the same list (module "certificates"); the consultations never show there
        foreach (range(1, 12) as $c) {
            DocumentIssuance::create([
                'id' => 100 + $c, 'document_type' => 'medical_certificate', 'document_creator_id' => $nurse->id, 'user_id' => $patients[$c % 10]->user_id,
                'name' => $this->label('Certificate', $c), 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete',
            ] + $this->stamps($c));
        }

        return $this->seeded() + ['extras' => [
            'one patient only' => ['patientId' => $patients[3]->user_id],
            'certificates module' => ['module' => 'certificates'],
        ]];
    }

    protected function seedLabRequestTable(): array
    {
        [$patients] = $this->peopleAndMedicines();
        $nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse', 'email' => 'nora@golden.test']);
        for ($i = 1; $i <= self::ROWS; $i++) {
            $request = LabRequest::create([
                'request_number' => sprintf('LR%06d', 100000 + $i), 'document_creator_id' => $nurse->id, 'patient_user_id' => $patients[$i % 10]->user_id,
                'patient_name' => $this->label('Lab Patient', $i), 'requested_at' => Carbon::parse('2026-02-01')->addDays($i)->toDateString(),
                'status' => $i % 3 === 0 ? LabRequest::STATUS_COMPLETED : LabRequest::STATUS_PENDING,
            ] + $this->stamps($i));
            foreach (range(1, $i % 3 + 1) as $test) {
                LabRequestItem::create(['lab_request_id' => $request->id, 'lab_test_id' => null, 'test_name' => 'Test ' . $test, 'test_category' => 'Other', 'result_status' => LabRequestItem::RESULT_PENDING]);
            }
        }

        // the status choice of the page and the "this patient only" scope of the patient's history
        return $this->seeded() + ['extras' => [
            // (the screen reads both when it is opened: the status from the page address, the patient from the patient's history)
            'status: completed' => ['status' => LabRequest::STATUS_COMPLETED],
            'status: pending' => ['status' => LabRequest::STATUS_PENDING],
            'one patient only' => ['patientId' => $patients[3]->user_id],
        ]];
    }

    protected function seedMedsOnlyTable(): array
    {
        $medicine = $this->makeMedicine('Biogesic', ['generic_name' => 'Paracetamol', 'brand_name' => 'Brandix']);
        $this->stockIn($medicine, 500, '2027-12-31', '500mg', 'LOT-MO');
        $nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse', 'email' => 'nora@golden.test']);
        $college = College::create(['college_name' => 'College of Arts']);
        $course = Course::create(['course_name' => 'BS Biology']);
        $year = YearLevel::create(['year_level_name' => '2nd Year']);
        $admin = auth()->user();

        for ($i = 1; $i <= self::ROWS; $i++) {
            $this->actingAs($nurse)->post(route('staff.meds-only.store'), [
                'bill_date' => Carbon::parse('2026-01-01')->addDays($i)->toDateString(), 'patient_name' => $this->label('Visitor', $i),
                'college_id' => $college->id, 'course_id' => $course->id, 'year_level_id' => $year->id,
                'medicines' => [['medicine_id' => $medicine->id, 'dosage' => '500mg', 'quantity' => $i % 3 + 1]],
            ])->assertRedirect();
        }
        $this->actingAs($admin);

        return $this->seeded();
    }
}
