<?php

namespace App\Services\Reports;

use App\Models\InventoryCount;
use App\Models\InventoryCountLine;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Builds the MEDICINES WITH NOTES report: what the finalized count sheets found, medicine by medicine, with the system amount,
 * the counted amount, the difference, the reason and the note, and whether the shelf stock was corrected from it.
 *
 * Only a finalized sheet is in it (a draft is still being typed), and only a medicine that was counted. By default only the
 * lines that have a difference or a note; "all" adds every counted line. It can be narrowed to a year and to one quarter, or to
 * the counts of everything made on a day ("day"). Newest count first.
 *
 * The result is plain arrays so the screen, the PDF, the Excel file and the CSV all render the same data.
 */
final class InventoryDiscrepancyReportBuilder
{
    public const SHOW_NOTES = 'notes';

    public const SHOW_ALL = 'all';

    /** @var array<int, string> the periods a report can be narrowed to: a quarter, or the counts of everything made on a day */
    public const PERIODS = ['1', '2', '3', '4', 'day'];

    /**
     * @return array<string, mixed>
     */
    public function build(?int $year = null, ?string $period = null, string $show = self::SHOW_NOTES, ?User $preparedBy = null): array
    {
        $period = in_array($period, self::PERIODS, true) ? $period : null;
        $show = $show === self::SHOW_ALL ? self::SHOW_ALL : self::SHOW_NOTES;

        $lines = InventoryCountLine::query()
            ->join('inventory_counts as s', 's.id', '=', 'inventory_count_lines.inventory_count_id')
            ->where('s.status', InventoryCount::STATUS_FINALIZED)
            ->whereNotNull('inventory_count_lines.counted_quantity')
            ->when($year !== null, fn ($query) => $query->where('s.year', $year))
            ->when($period === 'day', fn ($query) => $query->whereNull('s.quarter'))
            ->when($period !== null && $period !== 'day', fn ($query) => $query->where('s.quarter', (int) $period))
            ->when($show === self::SHOW_NOTES, fn ($query) => $query->where(
                fn ($inner) => $inner->where('inventory_count_lines.difference', '!=', 0)
                    ->orWhere(fn ($note) => $note->whereNotNull('inventory_count_lines.note')->where('inventory_count_lines.note', '!=', ''))
            ))
            ->orderByDesc('s.counted_on')->orderByDesc('s.id')->orderBy('inventory_count_lines.medicine_name')->orderBy('inventory_count_lines.id')
            ->select('inventory_count_lines.*', 's.year as sheet_year', 's.quarter as sheet_quarter', 's.counted_on as sheet_counted_on')
            ->get();

        $rows = $lines->map(fn (InventoryCountLine $line) => [
            'sheet_id' => (int) $line->inventory_count_id,
            'sheet' => InventoryCount::labelFor((int) $line->sheet_year, $line->sheet_quarter === null ? null : (int) $line->sheet_quarter, (string) $line->sheet_counted_on),
            'counted_on' => (string) $line->sheet_counted_on,
            'medicine_id' => (int) $line->medicine_id,
            'name' => (string) $line->medicine_name,
            'strength' => (string) $line->strength,
            'unit' => (string) $line->unit,
            'system' => (int) $line->system_quantity,
            'counted' => (int) $line->counted_quantity,
            'difference' => (int) $line->difference,
            'reason' => (string) $line->reasonLabel(),
            'note' => (string) $line->note,
            'corrected' => $line->adjusted_at?->toDateString() ?? '',
        ])->all();

        return [
            'year' => $year,
            'period' => $period,
            'period_label' => $this->periodLabel($year, $period),
            'show' => $show,
            'rows' => $rows,
            'sheets' => count(array_unique(array_column($rows, 'sheet_id'))),
            'differences' => count(array_filter($rows, fn (array $row) => $row['difference'] !== 0)),
            'net' => array_sum(array_column($rows, 'difference')),
            'corrected' => count(array_filter($rows, fn (array $row) => $row['corrected'] !== '')),
            'prepared_by' => $this->preparedBy($preparedBy),
            'noted_by' => [
                'name' => (string) getSettingValue('university_physician_name'),
                'title' => (string) (getSettingValue('university_physician_title') ?: 'University Physician'),
            ],
            'generated_at' => Carbon::now(),
        ];
    }

    /**
     * The years that have a finalized count sheet, newest first.
     *
     * @return array<int, int>
     */
    public static function years(): array
    {
        return InventoryCount::query()->where('status', InventoryCount::STATUS_FINALIZED)->distinct()->orderByDesc('year')
            ->pluck('year')->map(fn ($year) => (int) $year)->all();
    }

    private function periodLabel(?int $year, ?string $period): string
    {
        $what = match (true) {
            $period === 'day' => 'counts of everything on a day',
            $period !== null => "Q{$period}",
            default => 'every count',
        };

        return $what . ($year !== null ? " of {$year}" : ', every year');
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
