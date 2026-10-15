<?php

namespace App\Services\Reports;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Builds the YEARLY MEDICINE INVENTORY report: for one year, one row per medicine with
 *
 *   - left from last year : the stock on January 1 (= last year's Q4 remaining);
 *   - additional          : the stock received during the year (stock-in);
 *   - Q1..Q4 remaining    : the stock at the end of March, June, September and December (the quarter in progress
 *                           shows the stock of today, a quarter that has not started is blank);
 *
 * in each medicine's own unit (tablet, box, bottle ...).
 *
 * The figures come from the stock ledger (`medicine_transactions`), never from the batches' present quantities: the
 * stock of a batch at a moment is the `balance_after` of its last ledger line up to that moment, so returns, disposals
 * and corrections are counted exactly as they happened and a past year can be reproduced years later.
 * Stock that has passed its expiry date but was not disposed yet is still counted (it is still on the shelf) and is
 * noted per quarter.
 *
 * As a check, what moved during the year must explain the year-end remaining:
 *   left + additional - used - disposed +/- adjustments = year-end remaining.
 * A medicine for which it does not (a ledger line that was changed by hand) is flagged instead of hidden. For the
 * current year the year-end figure is compared with the stock on the shelf today.
 *
 * The result is plain arrays so the screen, the PDF, the Excel file and the CSV all render the same data.
 */
final class YearlyInventoryReportBuilder
{
    /** @var array<int, array{0: string, 1: string, 2: string, 3: int, 4: int}> key, label, name, last month, last day */
    private const QUARTERS = [
        ['q1', 'Q1', 'End of March', 3, 31],
        ['q2', 'Q2', 'End of June', 6, 30],
        ['q3', 'Q3', 'End of September', 9, 30],
        ['q4', 'Q4', 'End of December', 12, 31],
    ];

    /**
     * @return array<string, mixed>
     */
    public function build(int $year, ?User $preparedBy = null): array
    {
        $now = Carbon::now();
        $quarters = $this->quarters($year, $now);

        // The moments the stock is read at: the end of last year, then the end of each quarter that has started.
        $cuts = ['left' => min($now->copy(), Carbon::create($year - 1, 12, 31, 23, 59, 59))];
        foreach ($quarters as $quarter) {
            if ($quarter['cut'] !== null) {
                $cuts[$quarter['key']] = $quarter['cut'];
            }
        }

        $window = [Carbon::create($year, 1, 1, 0, 0, 0), min($now->copy(), Carbon::create($year, 12, 31, 23, 59, 59))];
        [$snapshots, $movement] = $this->readLedger($cuts, $window);

        $rows = $this->rows($snapshots, $movement, $quarters, $year === (int) $now->year ? $this->stockToday() : null);

        return [
            'year' => $year,
            'quarters' => array_map(fn (array $quarter) => array_diff_key($quarter, ['cut' => 1]) + ['date' => $quarter['end']->toDateString()], $quarters),
            'rows' => $rows,
            'inconsistent' => collect($rows)->where('consistent', false)->count(),
            'prepared_by' => $this->preparedBy($preparedBy),
            'noted_by' => [
                'name' => (string) getSettingValue('university_physician_name'),
                'title' => (string) (getSettingValue('university_physician_title') ?: 'University Physician'),
            ],
            'generated_at' => $now,
        ];
    }

    /**
     * The years of the picker: the year of the first ledger line up to this year, newest first.
     *
     * @return array<int, int>
     */
    public static function years(): array
    {
        $last = (int) Carbon::now()->year;
        $first = MedicineTransaction::query()->whereNotNull('created_at')->min('created_at');

        return range($last, $first ? min((int) Carbon::parse($first)->year, $last) : $last);
    }

    /** @return array<int, array<string, mixed>> */
    private function quarters(int $year, Carbon $now): array
    {
        $quarters = [];

        foreach (self::QUARTERS as [$key, $label, $name, $month, $day]) {
            $start = Carbon::create($year, $month - 2, 1, 0, 0, 0);
            $end = Carbon::create($year, $month, $day, 23, 59, 59);
            $blank = $start->gt($now);
            $toDate = ! $blank && $end->gt($now);

            $quarters[] = [
                'key' => $key, 'label' => $label, 'name' => $name,
                'blank' => $blank, 'to_date' => $toDate,
                'end' => $end, 'cut' => $blank ? null : ($toDate ? $now->copy() : $end),
            ];
        }

        return $quarters;
    }

    /**
     * One pass over the ledger, oldest first: the stock of every batch at every cut, and what moved during the year.
     *
     * @param  array<string, Carbon>  $cuts  name => moment (ascending)
     * @param  array{0: Carbon, 1: Carbon}  $window  first and last moment of the year
     * @return array{0: array<string, array{total: array<int, int>, expired: array<int, int>}>, 1: array<int, array<string, int>>}
     */
    private function readLedger(array $cuts, array $window): array
    {
        $balance = [];
        $batch = [];
        $snapshots = [];
        $movement = [];
        $pending = $cuts;

        $lines = DB::table('medicine_transactions as t')
            ->join('medicine_batches as b', 'b.id', '=', 't.batch_id')
            ->whereNotNull('t.created_at')
            ->where('t.created_at', '<=', max($cuts)->toDateTimeString())
            ->orderBy('t.created_at')->orderBy('t.id')
            ->select('t.batch_id', 't.transaction_type', 't.quantity', 't.balance_after', 't.created_at', 'b.medicine_id', 'b.expiration_date')
            ->cursor();

        foreach ($lines as $line) {
            // Every cut the line is later than is complete: read the stock of that moment before this line changes it.
            foreach ($pending as $name => $cut) {
                if ($line->created_at > $cut->toDateTimeString()) {
                    $snapshots[$name] = $this->snapshot($balance, $batch, $cut);
                    unset($pending[$name]);
                }
            }

            $medicineId = (int) $line->medicine_id;
            $units = (int) $line->quantity;
            $delta = (int) $line->balance_after - ($balance[$line->batch_id] ?? 0);
            $balance[$line->batch_id] = (int) $line->balance_after;
            $batch[$line->batch_id] = [$medicineId, $line->expiration_date];

            if ($line->created_at >= $window[0]->toDateTimeString() && $line->created_at <= $window[1]->toDateTimeString()) {
                $m = &$movement[$medicineId];
                $m ??= ['additional' => 0, 'used' => 0, 'disposed' => 0, 'adjusted' => 0, 'bad' => 0];

                // Each line says how many units moved; the batch balance says which way. They must agree.
                switch ($line->transaction_type) {
                    case MedicineTransaction::TYPE_STOCK_IN:
                        $m['additional'] += $units;
                        $m['bad'] += $delta === $units ? 0 : 1;
                        break;
                    case MedicineTransaction::TYPE_DISPENSE:
                        $m['used'] += $units;
                        $m['bad'] += $delta === -$units ? 0 : 1;
                        break;
                    case MedicineTransaction::TYPE_DISPOSAL:
                        $m['disposed'] += $units;
                        $m['bad'] += $delta === -$units ? 0 : 1;
                        break;
                    default: // adjustment: units back into stock, or taken out by a correction
                        $m['adjusted'] += $delta;
                        $m['bad'] += abs($delta) === $units ? 0 : 1;
                }
                unset($m);
            }
        }

        foreach ($pending as $name => $cut) {
            $snapshots[$name] = $this->snapshot($balance, $batch, $cut);
        }

        return [$snapshots, $movement];
    }

    /**
     * @param  array<int, int>  $balance  batch id => units
     * @param  array<int, array{0: int, 1: ?string}>  $batch  batch id => [medicine id, expiry date]
     * @return array{total: array<int, int>, expired: array<int, int>}
     */
    private function snapshot(array $balance, array $batch, Carbon $cut): array
    {
        $day = $cut->toDateString();
        $total = [];
        $expired = [];

        foreach ($balance as $batchId => $units) {
            if ($units === 0) {
                continue;
            }

            [$medicineId, $expiry] = $batch[$batchId];
            $total[$medicineId] = ($total[$medicineId] ?? 0) + $units;

            // Past its expiry date and still counted: the day after the expiry date it is expired.
            if ($expiry !== null && substr((string) $expiry, 0, 10) < $day) {
                $expired[$medicineId] = ($expired[$medicineId] ?? 0) + $units;
            }
        }

        return ['total' => $total, 'expired' => $expired];
    }

    /** @return array<int, int> medicine id => units on the shelf now, from the batches */
    private function stockToday(): array
    {
        return MedicineBatch::query()->selectRaw('medicine_id, SUM(quantity) as units')->groupBy('medicine_id')
            ->pluck('units', 'medicine_id')->map(fn ($units) => (int) $units)->all();
    }

    /**
     * @param  array<string, array{total: array<int, int>, expired: array<int, int>}>  $snapshots
     * @param  array<int, array<string, int>>  $movement
     * @param  array<int, array<string, mixed>>  $quarters
     * @param  array<int, int>|null  $today  only for the current year
     * @return array<int, array<string, mixed>>
     */
    private function rows(array $snapshots, array $movement, array $quarters, ?array $today): array
    {
        $ids = collect($snapshots)->flatMap(fn ($snapshot) => array_keys($snapshot['total']))->merge(array_keys($movement))->unique()->values();
        $medicines = Medicine::with('generic:id,name')->whereIn('id', $ids)->get()->keyBy('id');
        $rows = [];

        foreach ($ids as $id) {
            $medicine = $medicines->get($id);
            if ($medicine === null) {
                continue;
            }

            $m = $movement[$id] ?? ['additional' => 0, 'used' => 0, 'disposed' => 0, 'adjusted' => 0, 'bad' => 0];
            $left = $snapshots['left']['total'][$id] ?? 0;

            $cells = [];
            $ending = $left;
            foreach ($quarters as $quarter) {
                $snapshot = $snapshots[$quarter['key']] ?? null;
                $cells[$quarter['key']] = $snapshot === null
                    ? ['remaining' => null, 'expired' => 0]
                    : ['remaining' => $snapshot['total'][$id] ?? 0, 'expired' => $snapshot['expired'][$id] ?? 0];
                $ending = $cells[$quarter['key']]['remaining'] ?? $ending;
            }

            // A year with no quarter started yet (a year to come) ends where it began.
            $remainingFigures = array_filter(array_column($cells, 'remaining'), fn ($value) => $value !== null);
            if ($left === 0 && $m === ['additional' => 0, 'used' => 0, 'disposed' => 0, 'adjusted' => 0, 'bad' => 0] && ! array_filter($remainingFigures)) {
                continue;
            }

            $equation = $left + $m['additional'] - $m['used'] - $m['disposed'] + $m['adjusted'] === $ending;
            $stockToday = $today === null ? null : ($today[$id] ?? 0);
            $consistent = $equation && $m['bad'] === 0;

            $rows[] = [
                'medicine_id' => (int) $id,
                'name' => (string) $medicine->name,
                'generic' => (string) ($medicine->generic?->name ?? $medicine->generic_name ?? ''),
                'strength' => (string) ($medicine->dosage ?? ''),
                'unit' => (string) ($medicine->uom ?? ''),
                'left' => $left,
                'additional' => $m['additional'],
                'quarters' => $cells,
                'ending' => $ending,
                'used' => $m['used'],
                'disposed' => $m['disposed'],
                'adjusted' => $m['adjusted'],
                'consistent' => $consistent,
                'stock_today' => $stockToday,
                'matches_today' => $stockToday === null || $stockToday === $ending,
                'notes' => $this->notes($cells, $consistent, $stockToday, $ending),
            ];
        }

        usort($rows, fn (array $a, array $b) => [mb_strtolower($a['name']), $a['medicine_id']] <=> [mb_strtolower($b['name']), $b['medicine_id']]);

        return $rows;
    }

    /** One line of words about what a reader should look at; empty when there is nothing to say. */
    private function notes(array $cells, bool $consistent, ?int $stockToday, int $ending): string
    {
        $notes = [];

        $expired = [];
        foreach ($cells as $key => $cell) {
            if ($cell['expired'] > 0) {
                $expired[] = strtoupper($key) . ' ' . $cell['expired'];
            }
        }
        if ($expired !== []) {
            $notes[] = 'Counted but expired and not disposed yet: ' . implode(', ', $expired);
        }

        if (! $consistent) {
            $notes[] = 'The ledger does not add up for this medicine: check its stock movements in Inventory';
        }

        if ($stockToday !== null && $stockToday !== $ending) {
            $notes[] = "The stock today ({$stockToday}) differs from the ledger ({$ending})";
        }

        return implode(' | ', $notes);
    }

    /** @return array{name: string, title: string} */
    private function preparedBy(?User $user): array
    {
        if ($user === null) {
            return ['name' => '', 'title' => ''];
        }

        $user->loadMissing('roles');

        return ['name' => $user->full_name, 'title' => (string) ($user->roles->first()?->display_name ?? '')];
    }
}
