<?php

namespace App\Services\Reports;

use App\Support\SpreadsheetText;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The MEDICINES WITH NOTES report as a flat CSV: one line per counted medicine of a finalized sheet, the same columns in the same
 * order as the screen, the PDF and the Excel sheet (all from InventoryDiscrepancyReportBuilder).
 */
final class InventoryDiscrepancyReportCsv
{
    /**
     * @param  array<string, mixed>  $report  the result of InventoryDiscrepancyReportBuilder::build()
     * @return array<int, array<int, mixed>>  the header followed by the lines
     */
    public static function rows(array $report): array
    {
        $rows = [['Sheet', 'Day counted', 'Medicine', 'Strength', 'Unit', 'System', 'Counted', 'Difference', 'Reason', 'Note', 'Stock corrected']];

        foreach ($report['rows'] as $row) {
            $rows[] = [$row['sheet'], $row['counted_on'], $row['name'], $row['strength'], $row['unit'], $row['system'], $row['counted'], $row['difference'], $row['reason'], $row['note'], $row['corrected']];
        }

        return $rows;
    }

    /** "medicines_with_notes_2026_2027-02-15_091500.csv" */
    public static function fileName(array $report, string $extension): string
    {
        return 'medicines_with_notes_' . ($report['year'] ?? 'all') . '_' . now()->format('Y-m-d_His') . $extension;
    }

    /** @param  array<string, mixed>  $report */
    public static function response(array $report): StreamedResponse
    {
        $filename = self::fileName($report, '.csv');

        return SpreadsheetText::csvDownload($filename, fn () => self::rows($report));
    }
}
