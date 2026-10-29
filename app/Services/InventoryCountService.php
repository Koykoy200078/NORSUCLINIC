<?php

namespace App\Services;

use App\Models\InventoryCount;
use App\Models\InventoryCountLine;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\User;
use App\Services\Reports\YearlyInventoryReportBuilder;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Quarterly count sheets: the clinic types the final remaining count of each medicine for a quarter (or for everything on the
 * day it counted), with a reason and a note. A draft can be changed and refreshed; a finalized sheet is frozen and is what the
 * reports read. Nothing here touches the stock: correcting the shelf from a line is a separate, previewed and confirmed step
 * (StockControlRepository), only for a line whose system amount is still the shelf's.
 *
 * The system amount of a quarter sheet is the stock ledger's figure at the end of that quarter, the very figure of the yearly
 * report (so a past quarter can be counted late); for a count of everything it is the shelf today.
 */
class InventoryCountService
{
    use LogsActivity;

    public function create(array $data, User $actor): InventoryCount
    {
        $this->authorize($actor);
        $data = $this->validatedSheet($data);

        return DB::transaction(function () use ($data, $actor): InventoryCount {
            $figures = $this->systemQuantities($data['year'], $data['quarter']);

            $sheet = InventoryCount::create([
                'year' => $data['year'], 'quarter' => $data['quarter'], 'scope' => $data['scope'], 'counted_on' => $data['counted_on'],
                'status' => InventoryCount::STATUS_DRAFT, 'notes' => $data['notes'], 'created_by' => $actor->id,
            ]);

            $medicines = Medicine::query()
                ->when($data['scope'] === InventoryCount::SCOPE_SELECTED, fn ($query) => $query->whereIn('id', $data['medicine_ids']))
                ->orderBy('name')->orderBy('id')->get();

            foreach ($medicines as $medicine) {
                $sheet->lines()->create([
                    'medicine_id' => $medicine->id, 'medicine_name' => (string) $medicine->name, 'strength' => $medicine->dosage, 'unit' => $medicine->uom,
                    'system_quantity' => $figures[$medicine->id] ?? 0,
                ]);
            }

            $this->audit('inventory_count_created', $sheet, 'Count sheet made', ['medicines' => $medicines->count(), 'scope' => $data['scope']]);

            return $sheet;
        });
    }

    /**
     * @param  array<int|string, array{counted?: mixed, reason?: mixed, note?: mixed}>  $rows  by medicine id; only the rows sent change
     */
    public function saveCounts(InventoryCount $sheet, array $rows, User $actor): InventoryCount
    {
        $this->authorize($actor);

        return DB::transaction(function () use ($sheet, $rows, $actor): InventoryCount {
            $sheet = $this->lockedDraft($sheet);
            $lines = $sheet->lines()->get()->keyBy('medicine_id');
            $clean = $this->validatedRows($rows, $lines);

            foreach ($clean as $medicineId => $row) {
                $line = $lines[$medicineId];
                $difference = $row['counted'] === null ? null : $row['counted'] - $line->system_quantity;
                $line->update([
                    'counted_quantity' => $row['counted'],
                    'difference' => $difference,
                    // A count that matches the system, or no count at all, needs no reason.
                    'reason' => $difference === null || $difference === 0 ? null : $row['reason'],
                    'note' => $row['note'],
                ]);
            }

            $this->audit('inventory_count_saved', $sheet, 'Counts saved', $this->summary($sheet));

            return $sheet->fresh();
        });
    }

    public function refreshSystem(InventoryCount $sheet, User $actor): InventoryCount
    {
        $this->authorize($actor);

        return DB::transaction(function () use ($sheet, $actor): InventoryCount {
            $sheet = $this->lockedDraft($sheet);
            $figures = $this->systemQuantities($sheet->year, $sheet->quarter);

            foreach ($sheet->lines()->get() as $line) {
                $system = $figures[$line->medicine_id] ?? 0;
                $difference = $line->counted_quantity === null ? null : $line->counted_quantity - $system;
                $line->update([
                    'system_quantity' => $system,
                    'difference' => $difference,
                    'reason' => $difference === 0 ? null : $line->reason,
                ]);
            }

            $this->audit('inventory_count_refreshed', $sheet, 'System amounts refreshed', $this->summary($sheet));

            return $sheet->fresh();
        });
    }

    public function finalize(InventoryCount $sheet, User $actor): InventoryCount
    {
        $this->authorize($actor);

        return DB::transaction(function () use ($sheet, $actor): InventoryCount {
            $sheet = $this->lockedDraft($sheet);
            $lines = $sheet->lines()->get();
            $counted = $lines->filter->isCounted();

            if ($counted->isEmpty()) {
                throw ValidationException::withMessages(['finalize' => 'Count at least one medicine before finalizing the sheet.']);
            }

            $incomplete = $counted->filter(fn (InventoryCountLine $line) => $line->hasDifference()
                && ($line->reason === null || ($line->reason === 'other' && trim((string) $line->note) === '')));
            if ($incomplete->isNotEmpty()) {
                throw ValidationException::withMessages(['finalize' => 'Give a reason (and a note for "Other") for every difference before finalizing: '
                    . $incomplete->pluck('medicine_name')->implode(', ') . '.']);
            }

            $sheet->update(['status' => InventoryCount::STATUS_FINALIZED, 'finalized_by' => $actor->id, 'finalized_at' => now()]);
            $this->audit('inventory_count_finalized', $sheet, 'Count sheet finalized', $this->summary($sheet));

            return $sheet->fresh();
        });
    }

    public function delete(InventoryCount $sheet, User $actor): void
    {
        $this->authorize($actor);

        DB::transaction(function () use ($sheet): void {
            $sheet = $this->lockedDraft($sheet);

            // The snapshot first: once the sheet is gone nothing shows what was removed.
            $this->audit('inventory_count_deleted', $sheet, 'Count sheet deleted', $this->summary($sheet));
            $sheet->lines()->delete();
            $sheet->delete();
        });
    }

    /**
     * Why the shelf cannot be corrected from this line, or null when it can. The sheet's difference was measured against one
     * system amount; only while the shelf still holds exactly that amount can the difference be applied to it. A sheet of a
     * quarter that has ended is usually a record for the report only.
     */
    public function correctionBlockedReason(InventoryCountLine $line): ?string
    {
        $line->loadMissing('sheet');

        if (! $line->sheet->isFinalized()) {
            return 'Finalize the sheet first.';
        }
        if (! $line->hasDifference()) {
            return 'There is no difference to correct.';
        }
        if ($line->adjusted_at !== null) {
            return 'The stock was already corrected from this line.';
        }

        $shelf = (int) MedicineBatch::query()->where('medicine_id', $line->medicine_id)->sum('quantity');
        if ($shelf !== $line->system_quantity) {
            return "The shelf changed since this count (system {$line->system_quantity}, shelf now {$shelf}), so this line stays a record for the report. Count again to correct today's stock.";
        }

        return null;
    }

    /**
     * The system amount of every medicine: the ledger's remaining stock at the end of the quarter (the quarter in progress: today),
     * or, for a count of everything, the units on the shelf now. A medicine that is not in the result has none.
     *
     * @return array<int, int> medicine id => units
     */
    public function systemQuantities(int $year, ?int $quarter): array
    {
        if ($quarter === null) {
            return MedicineBatch::query()->selectRaw('medicine_id, SUM(quantity) as units')->groupBy('medicine_id')
                ->pluck('units', 'medicine_id')->map(fn ($units) => (int) $units)->all();
        }

        $figures = [];
        foreach (app(YearlyInventoryReportBuilder::class)->build($year)['rows'] as $row) {
            $figures[$row['medicine_id']] = (int) ($row['quarters']['q' . $quarter]['remaining'] ?? 0);
        }

        return $figures;
    }

    // ------------------------------------------------------------------ rules

    private function authorize(User $actor): void
    {
        abort_unless($actor->hasAnyRole(['clinic_admin', 'staff']) && $actor->can('manage_medicines') && $actor->status && ! $actor->trashed(), 403);
    }

    private function lockedDraft(InventoryCount $sheet): InventoryCount
    {
        $sheet = InventoryCount::lockForUpdate()->findOrFail($sheet->id);
        if (! $sheet->isDraft()) {
            throw ValidationException::withMessages(['sheet' => 'This count sheet is finalized and cannot be changed. Make a new sheet to count again.']);
        }

        return $sheet;
    }

    /** @return array{year: int, quarter: ?int, scope: string, counted_on: string, notes: ?string, medicine_ids: array<int, int>} */
    private function validatedSheet(array $data): array
    {
        $data['quarter'] = ($data['quarter'] ?? '') === '' ? null : $data['quarter'];
        $data['notes'] = isset($data['notes']) && trim((string) $data['notes']) !== '' ? trim((string) $data['notes']) : null;
        // A count of everything belongs to the year of the day it was counted: the year sent with it does not matter.
        if ($data['quarter'] === null && preg_match('/^(\d{4})-\d{2}-\d{2}$/', (string) ($data['counted_on'] ?? ''), $day)) {
            $data['year'] = (int) $day[1];
        }

        $valid = Validator::make($data, [
            'year' => ['required', 'integer', 'min:2000', 'max:' . now()->year],
            'quarter' => ['nullable', 'integer', 'between:1,4'],
            'scope' => ['required', 'in:' . InventoryCount::SCOPE_ALL . ',' . InventoryCount::SCOPE_SELECTED],
            'counted_on' => ['required', 'date_format:Y-m-d', 'after:1999-12-31', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'medicine_ids' => ['exclude_unless:scope,' . InventoryCount::SCOPE_SELECTED, 'required', 'array', 'min:1'],
            'medicine_ids.*' => ['integer', 'distinct', \Illuminate\Validation\Rule::exists('medicines', 'id')->whereNull('merged_into_id')],
        ], [
            'medicine_ids.required' => 'Choose at least one medicine for a sheet of selected medicines.',
            'medicine_ids.min' => 'Choose at least one medicine for a sheet of selected medicines.',
            'counted_on.before_or_equal' => 'The day of the count cannot be in the future.',
        ])->validate();

        $quarter = $valid['quarter'] === null ? null : (int) $valid['quarter'];
        $year = $quarter === null ? (int) Carbon::parse($valid['counted_on'])->year : (int) $valid['year'];

        if ($quarter !== null && Carbon::create($year, $quarter * 3 - 2, 1)->startOfDay()->gt(now())) {
            throw ValidationException::withMessages(['quarter' => "Quarter $quarter of $year has not started yet."]);
        }

        return [
            'year' => $year, 'quarter' => $quarter, 'scope' => $valid['scope'], 'counted_on' => $valid['counted_on'], 'notes' => $valid['notes'],
            'medicine_ids' => array_map('intval', $valid['medicine_ids'] ?? []),
        ];
    }

    /**
     * @param  Collection<int, InventoryCountLine>  $lines  keyed by medicine id
     * @return array<int, array{counted: ?int, reason: ?string, note: ?string}>
     */
    private function validatedRows(array $rows, Collection $lines): array
    {
        $errors = [];
        $clean = [];

        foreach ($rows as $medicineId => $row) {
            $line = $lines->get((int) $medicineId);
            if ($line === null) {
                $errors[] = "Medicine #{$medicineId} is not on this sheet.";

                continue;
            }
            $row = (array) $row;
            $name = $line->medicine_name;

            $counted = isset($row['counted']) ? trim((string) $row['counted']) : '';
            if ($counted !== '' && (! ctype_digit($counted) || (int) $counted > 2147483647 || strlen($counted) > 10)) {
                $errors[] = "{$name}: the count must be a whole number, 0 or more.";
            }

            $reason = isset($row['reason']) ? trim((string) $row['reason']) : '';
            if ($reason !== '' && ! array_key_exists($reason, InventoryCountLine::REASONS)) {
                $errors[] = "{$name}: choose a reason from the list.";
            }

            $note = isset($row['note']) ? trim((string) $row['note']) : '';
            if (mb_strlen($note) > 500) {
                $errors[] = "{$name}: the note can have at most 500 characters.";
            }

            $clean[(int) $medicineId] = [
                'counted' => $counted === '' || ! ctype_digit($counted) ? null : (int) $counted,
                'reason' => $reason === '' ? null : $reason,
                'note' => $note === '' ? null : $note,
            ];
        }

        if ($errors !== []) {
            // One message: the application shows the first message of a refused form, so none may be left behind in a list.
            throw ValidationException::withMessages(['counts' => implode(' ', $errors)]);
        }

        return $clean;
    }

    /** @return array<string, int> */
    private function summary(InventoryCount $sheet): array
    {
        $lines = $sheet->lines()->get();

        return [
            'medicines' => $lines->count(),
            'counted' => $lines->filter->isCounted()->count(),
            'with_difference' => $lines->filter->hasDifference()->count(),
        ];
    }

    private function audit(string $action, InventoryCount $sheet, string $what, array $properties): void
    {
        self::logActivity($action, $what . ': ' . $sheet->label(), [
            'subject_type' => InventoryCount::class,
            'subject_id' => $sheet->id,
            'properties' => ['sheet' => $sheet->label(), 'status' => $sheet->status] + $properties,
        ]);
    }
}
