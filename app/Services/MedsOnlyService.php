<?php

namespace App\Services;

use App\Models\Campus;
use App\Models\College;
use App\Models\Course;
use App\Models\DispenseRecord;
use App\Models\Medicine;
use App\Models\MedicineTransaction;
use App\Models\Patient;
use App\Models\User;
use App\Models\YearLevel;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Meds Only: a patient who only comes for medicine. Recorded as a dispense record (`medicine_bills` + `sale_medicines`,
 * `meds_only` = 1), so the stock leaves through MedicineInventoryService (FEFO, ledger line per batch) and Stock Out, Dispense
 * History and the reports already see it. Every change is one transaction: a drug without enough stock refuses the whole
 * record and nothing stays deducted.
 */
class MedsOnlyService
{
    use LogsActivity;

    public function __construct(private MedicineInventoryService $inventory)
    {
    }

    /**
     * @param  array<string, mixed>  $data  a validated MedsOnlyRequest
     */
    public function create(array $data, User $actor): DispenseRecord
    {
        return DB::transaction(function () use ($data, $actor) {
            $lines = $this->lines($data);

            $record = DispenseRecord::create($this->identity($data) + [
                'history_number' => 'HIS' . generateUniqueHistoryNumber(),
                'model_type' => DispenseRecord::class,
                'meds_only' => true,
                'recorded_by' => $actor->id,
                'note' => $this->note($data),
                'bill_date' => $this->billDate($data['bill_date']),
            ]);
            $record->update(['model_id' => $record->id]);

            $this->giveMedicines($record, $lines, $actor);
            $this->audit('meds_only_created', $record, 'Meds only recorded');

            return $record;
        });
    }

    /**
     * The same record again: every unit of the old lines goes back first, then the new lines come out, so replacing a drug
     * never fails for lack of the stock it is replacing.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(DispenseRecord $record, array $data, User $actor): DispenseRecord
    {
        return DB::transaction(function () use ($record, $data, $actor) {
            $record = DispenseRecord::medsOnly()->whereKey($record->getKey())->lockForUpdate()->firstOrFail();
            $lines = $this->lines($data);

            $this->returnMedicines($record, $actor, 'edit');
            $record->update($this->identity($data) + [
                'note' => $this->note($data),
                'bill_date' => $this->billDate($data['bill_date'], $record),
            ]);
            $this->giveMedicines($record, $lines, $actor);
            $this->audit('meds_only_updated', $record, 'Meds only changed');

            return $record;
        });
    }

    public function delete(DispenseRecord $record, User $actor): void
    {
        DB::transaction(function () use ($record, $actor) {
            $record = DispenseRecord::medsOnly()->whereKey($record->getKey())->lockForUpdate()->firstOrFail();

            // The snapshot first: once the record is gone nothing shows what was removed.
            $this->audit('meds_only_deleted', $record, 'Meds only deleted');
            $this->returnMedicines($record, $actor, 'delete');
            $record->delete();
        });
    }

    // ------------------------------------------------------------------ the lines

    /**
     * The submitted drugs, one per medicine + strength.
     *
     * @return array<int, array{medicine_id: int, dosage: string, quantity: int}>
     */
    private function lines(array $data): array
    {
        $lines = [];
        $seen = [];

        foreach ($data['medicines'] as $line) {
            $dosage = trim((string) ($line['dosage'] ?? ''));
            $key = (int) $line['medicine_id'] . '|' . mb_strtolower($dosage);

            if (isset($seen[$key])) {
                $name = Medicine::find((int) $line['medicine_id'])?->display_name ?? 'A medicine';

                throw ValidationException::withMessages(['medicines' => $name . ($dosage !== '' ? " ({$dosage})" : '') . ' is listed more than once. Combine the quantities into one row.']);
            }
            $seen[$key] = true;
            $lines[] = ['medicine_id' => (int) $line['medicine_id'], 'dosage' => $dosage, 'quantity' => (int) $line['quantity']];
        }

        return $lines;
    }

    /** Each drug leaves the stock (FEFO) and is written as the lines the engine really took. */
    private function giveMedicines(DispenseRecord $record, array $lines, User $actor): void
    {
        foreach ($lines as $line) {
            try {
                $allocations = $this->inventory->deductStockFefo(
                    $line['medicine_id'],
                    $line['quantity'],
                    $actor->id,
                    $record,
                    'Meds only #' . $record->history_number . ' deduction',
                    MedicineTransaction::TYPE_DISPENSE,
                    $line['dosage']
                );
            } catch (\RuntimeException $e) {
                // "Insufficient stock for X: requested N, only M available (unexpired)." - say it as a form error.
                throw ValidationException::withMessages(['medicines' => $e->getMessage()]);
            }

            DispenseRecordLines::fromAllocations($record->id, $line['medicine_id'], $line['dosage'], null, $line['quantity'], $allocations);
        }
    }

    /** Every line of the record goes back to its batch (or a restoration batch when that one is gone), then the lines are removed. */
    private function returnMedicines(DispenseRecord $record, User $actor, string $why): void
    {
        foreach ($record->dispenseItems()->get() as $item) {
            $this->inventory->restoreStock(
                (int) $item->medicine_id,
                (int) $item->quantity,
                $actor->id,
                $record,
                'Reversed for meds only #' . $record->history_number . ' ' . $why,
                $item->dosage,
                $item->expires_at
            );
        }

        $record->dispenseItems()->delete();
    }

    // ------------------------------------------------------------------ who it was

    /**
     * The name and the college / course / year (ids and the text of the day). A registered patient fills them from the
     * profile; whatever the form chose wins. A typed name has only what the form chose.
     *
     * @return array<string, mixed>
     */
    private function identity(array $data): array
    {
        $patient = ! empty($data['patient_id']) ? Patient::with('user')->find((int) $data['patient_id']) : null;
        $user = $patient?->user;
        $chosen = fn (string $key) => ! empty($data[$key]) ? (int) $data[$key] : null;

        $campusId = $chosen('campus_id') ?? ($user?->campus_id ? (int) $user->campus_id : null);
        $collegeId = $chosen('college_id') ?? ($user?->college_id ? (int) $user->college_id : null);
        $courseId = $chosen('course_id') ?? ($user?->course_id ? (int) $user->course_id : null);
        $yearId = $chosen('year_level_id') ?? ($user?->year_level_id ? (int) $user->year_level_id : null);

        return [
            'patient_id' => $patient?->id,
            'patient_name' => $user ? $user->full_name : trim((string) ($data['patient_name'] ?? '')),
            'patient_type_id' => $chosen('patient_type_id') ?? ($patient?->patient_type_id ? (int) $patient->patient_type_id : null),
            'campus_id' => $campusId,
            'college_id' => $collegeId,
            'course_id' => $courseId,
            'year_level_id' => $yearId,
            'campus' => $campusId ? Campus::find($campusId)?->campus_name : null,
            'college' => $collegeId ? College::find($collegeId)?->college_name : null,
            'course' => $courseId ? Course::find($courseId)?->course_name : null,
            'year_level' => $yearId ? YearLevel::find($yearId)?->year_level_name : null,
        ];
    }

    private function note(array $data): ?string
    {
        $note = trim((string) ($data['note'] ?? ''));

        return $note === '' ? null : $note;
    }

    /** The chosen day, at the time of entry (an edit that keeps the day keeps the time too). */
    private function billDate(string $day, ?DispenseRecord $existing = null): Carbon
    {
        $date = Carbon::parse($day);
        $time = $existing && Carbon::parse($existing->bill_date)->toDateString() === $date->toDateString()
            ? Carbon::parse($existing->bill_date)
            : now();

        return $date->setTime($time->hour, $time->minute, $time->second);
    }

    private function audit(string $action, DispenseRecord $record, string $what): void
    {
        $record->loadMissing('dispenseItems.medicine');

        self::logActivity($action, $what . ' for ' . $record->patient_name, [
            'subject_type' => DispenseRecord::class,
            'subject_id' => $record->id,
            'patient_name' => $record->patient_name,
            'college' => $record->college,
            'course' => $record->course,
            'year_level' => $record->year_level,
            'properties' => [
                'history_number' => $record->history_number,
                'registered_patient_id' => $record->patient_id,
                'medicines' => $record->dispenseItems->map(fn ($item) => [
                    'medicine' => $item->medicine?->name, 'dosage' => $item->dosage, 'quantity' => (int) $item->quantity,
                ])->values()->all(),
            ],
        ]);
    }
}
