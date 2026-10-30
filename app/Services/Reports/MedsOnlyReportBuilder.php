<?php

namespace App\Services\Reports;

use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\Medicine;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Builds the MEDS ONLY report: the patients who only came for medicine, one row per record (date, name, type, campus, college,
 * course, year level, the drugs, who recorded it, the note), and how many records and units of each medicine they took.
 *
 * The rows come from the very query the screen pages (ReportQueries::medsOnly) with the filters of the screen, so the PDF, the Excel
 * sheet and the CSV hold what the screen shows; the screen pages them, the files take all of them. The result is plain arrays.
 */
final class MedsOnlyReportBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(ReportFilters $filters, ?User $preparedBy = null): array
    {
        $rows = ReportQueries::medsOnly($filters)->get()->map(fn (DispenseRecord $record) => self::row($record))->all();
        $medicines = $this->medicines($filters);

        return [
            'rows' => $rows,
            'records' => count($rows),
            'units' => array_sum(array_column($medicines, 'units')),
            'medicines' => $medicines,
            'period_label' => $this->periodLabel($filters),
            'prepared_by' => $this->preparedBy($preparedBy),
            'noted_by' => [
                'name' => (string) getSettingValue('university_physician_name'),
                'title' => (string) (getSettingValue('university_physician_title') ?: 'University Physician'),
            ],
            'generated_at' => Carbon::now(),
        ];
    }

    /**
     * One record as the report shows it (the screen, the CSV, the PDF and the Excel sheet all use this).
     *
     * @return array{id: int, date: string, name: string, type: string, campus: string, college: string, course: string, year_level: string, drugs: string, recorded_by: string, note: string}
     */
    public static function row(DispenseRecord $record): array
    {
        return [
            'id' => (int) $record->id,
            'date' => $record->bill_date ? Carbon::parse($record->bill_date)->format('Y-m-d') : '',
            'name' => (string) $record->patient_name,
            'type' => (string) ($record->patientType?->name ?? ''),
            'campus' => (string) $record->campus,
            'college' => (string) $record->college,
            'course' => (string) $record->course,
            'year_level' => (string) $record->year_level,
            'drugs' => $record->medicineSummary(),
            'recorded_by' => (string) ($record->recorder?->full_name ?? ''),
            'note' => (string) $record->note,
        ];
    }

    /**
     * The records and the units of each medicine (and strength) in the filtered records.
     *
     * @return array<int, array{name: string, strength: string, unit: string, records: int, units: int}>
     */
    public function medicines(ReportFilters $filters): array
    {
        $ids = ReportQueries::medsOnly($filters)->reorder()->select('medicine_bills.id');

        $lines = DispenseRecordItem::query()->whereIn('medicine_bill_id', $ids)
            ->selectRaw('medicine_id, dosage, COUNT(DISTINCT medicine_bill_id) as records, SUM(sale_quantity) as units')
            ->groupBy('medicine_id', 'dosage')->get();

        $names = Medicine::query()->whereIn('id', $lines->pluck('medicine_id'))->get(['id', 'name', 'uom'])->keyBy('id');

        return $lines->map(fn ($line) => [
            'name' => (string) ($names[$line->medicine_id]->name ?? 'Deleted medicine'),
            'strength' => (string) $line->dosage,
            'unit' => (string) ($names[$line->medicine_id]->uom ?? ''),
            'records' => (int) $line->records,
            'units' => (int) $line->units,
        ])->sortBy(fn (array $row) => [mb_strtolower($row['name']), $row['strength']])->values()->all();
    }

    private function periodLabel(ReportFilters $filters): string
    {
        return match (true) {
            (bool) ($filters->dateFrom && $filters->dateTo) => "{$filters->dateFrom} to {$filters->dateTo}",
            (bool) $filters->dateFrom => "from {$filters->dateFrom}",
            (bool) $filters->dateTo => "up to {$filters->dateTo}",
            default => 'all dates',
        };
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
